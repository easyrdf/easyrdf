<?php

require_once "EasyRdf/Resource.php";
require_once "EasyRdf/Namespace.php";
require_once "EasyRdf/TypeMapper.php";

class EasyRdf_Graph
{
    private $uri = null;
    private $resources = array();
    private $type_index = array();
    private static $http_client = null;
    private static $parser = null;
    
    const RDF_TYPE_URI = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type';

    /**
	   * Get a Resource object for a specific URI
	   * @return EasyRdf_Resource returns a Resource (or null if it does not exist)
	   */
    public function getResource($uri, $types = array())
    {
        # FIXME: throw exception if parameters are bad?
        if (!$uri) {
            return null;
        }
    
        # Convert types to an array if it isn't one
        if (!is_array($types)) {
            $types = array($types);
        }
    
        # Create resource object if it doesn't already exist
        if (!array_key_exists($uri, $this->resources)) {
            $res_class = EasyRdf_Resource;
            foreach ($types as $type) {
                $class = EasyRDF_TypeMapper::get($type);
                if ($class != null) {
                    $res_class = $class;
                    break;
                }
            }
            $this->resources[$uri] = new $res_class($uri);
        }
        return $this->resources[$uri];
    }
    
	  /**
     * Return all known resources
     */
    public function resources()
    {
        return array_values($this->resources);
    }

    /**
     * Delete the contents of a graph (all the resources)
     */
    public function deleteAll()
    {
        // FIXME: implement this
    }


    public static function setHttpClient($http_client)
    {
        self::$http_client = $http_client;
   }
    
    public static function getHttpClient()
    {
        if (!self::$http_client) {
            require_once "EasyRdf/Http/Client.php";
            self::$http_client = new EasyRdf_Http_Client();
        }
        return self::$http_client;
    }

    public static function getRdfParser()
    {
        if (!self::$parser) {
            require_once "EasyRdf/RapperParser.php";
            self::$parser = new EasyRdf_RapperParser();
        }
        return self::$parser;
    }

    public static function simplifyMimeType($mime_type)
    {
        switch($mime_type) {
            case 'application/json':
            case 'text/json':
                return 'json';
            case 'application/x-yaml':
            case 'application/yaml':
            case 'text/x-yaml':
            case 'text/yaml':
                return 'yaml';
            case 'application/rdf+xml':
                return 'rdfxml';
            case 'text/turtle':
                return 'turtle';
            default:
                # FIXME: throw exception?
                return '';
        }
    }
    
    public static function guessDocType($data)
    {
        # FIXME: could /etc/magic help here?
        if (is_array($data)) {
            # Data has already been parsed into RDF/PHP
            return 'php';
        } else if (ereg("^[ \n\r\t]*\{", $data)) {
            return 'json';
        } else if (ereg("^[ \n\r\t]*---", $data)) {
            return 'yaml';
        } else if (ereg("^[ \n\r\t]*<\?xml", $data) or ereg("^[ \n\r\t]*<rdf:RDF", $data)) {
            return 'rdfxml';
        } else if (ereg("^[ \n\r\t]*@prefix ", $data)) {
            # FIXME: this could be improved
            return 'turtle';
        } else {
            # FIXME: throw exception?
            return '';
        }
    }

    public static function setRdfParser($parser)
    {
        self::$parser = $parser;
    }
    
    public function __construct($uri, $data='', $doc_type='')
    {
        $this->uri = $uri;
        $this->load($uri, $data, $doc_type);
    }

    /**
     * Convert RDF/PHP into a graph of objects
     */
    public function load($uri, $data='', $doc_type='')
    {
        // FIXME: validate the URI?

        if (!$data) {
            # No data was given - try and fetch data from URI
            $client = self::getHttpClient();
            $client->setUri($uri);
            # FIXME: set the accept header to a list of formats we are able to parse
            $client->setHeaders('Accept', 'application/rdf+xml');
            $response = $client->request();
            $data = $response->getBody();
            if ($doc_type == '') {
                $doc_type = self::simplifyMimeType(
                    $response->getHeader('Content-Type')
                );
            }
        }
        
        # Guess the document type if not given
        if ($doc_type == '') {
            $doc_type = self::guessDocType( $data );
        }
        
        # Parse the document
        if ($doc_type == 'php') {
            # FIXME: validate the data?
        } else if ($doc_type == 'json') {
            # Parse the RDF/JSON into RDF/PHP
            $data = json_decode( $data, true );
        } else {
            # Parse the RDF data
            $data = self::getRdfParser()->parse( $uri, $data, $doc_type );
            if (!$data) {
                # FIXME: parse error - throw exception?
                return null;
            }
        }

        # Convert into an object graph
        foreach ($data as $subj => $touple) {
          $type = $this->getResourceType($data, $subj);
          $res = $this->getResource($subj, $type);
          foreach ($touple as $property => $objs) {
            $property = EasyRdf_Namespace::shorten($property);
            if (isset($property)) {
              foreach ($objs as $obj) {
                if ($property == 'rdf_type') {
                  $type = EasyRdf_Namespace::shorten($obj['value']);
                  $this->addToTypeIndex( $res, $type );
                  $res->set($property, $type);
                } else if ($obj['type'] == 'literal') {
                  $res->set($property, $obj['value']);
                } else if ($obj['type'] == 'uri' or $obj['type'] == 'bnode') {
                  $type = $this->getResourceType($data, $obj['value']);
                  $objres = $this->getResource($obj['value'], $type);
                  $res->set($property, $objres);
                } else {
                  # FIXME: thow exception or silently ignore?
                }
              }
            }
          }
        
        }
    }
    
    private function getResourceType( $data, $uri )
    {
       if (array_key_exists($uri, $data)) {
            $subj = $data[$uri];
            if (array_key_exists(self::RDF_TYPE_URI, $subj)) {
                $types = array();
                foreach ($subj[self::RDF_TYPE_URI] as $type) {
                    if ($type['type'] == 'uri') {
                        $type = EasyRdf_Namespace::shorten($type['value']);
                        if ($type) {
                            array_push($types, $type);
                        }
                    }
                }
                if (count($types) > 0) {
                    return $types;
                }
            }
        }
        return null;
    }
    
    private function addToTypeIndex($resource, $type)
    {
        # FIXME: shorten type if it isn't already short
        if ($type) {
            if (!isset($this->type_index[$type])) {
                $this->type_index[$type] = array();
            }
            if (!in_array($resource, $this->type_index[$type])) {
                array_push($this->type_index[$type], $resource);
            }
        }
    }
    
    public function allByType($type)
    {
        # FIXME: shorten if $type is a URL
        return $this->type_index[$type];
    }
    
    public function allTypes()
    {
        return array_keys( $this->type_index );
    }
    
    public function type()
    {
        $res = $this->getResource($this->uri);
        if ($res) {
            return $res->type();
        } else {
            return null;
        }
    }
    
    public function primaryTopic()
    {
        $res = $this->getResource($this->uri);
        return $res->first('foaf_primaryTopic');
    }
    
    public function getUri()
    {
        return $this->uri;
    }
    
    public function addTriples($resource, $dict)
    {
        # FIXME: implement this
    }
	
    
    public function dump($html=true)
    {
        # FIXME: display some information about the graph
        foreach ($this->resources as $resource) {
            $resource->dump($html,1);
        }
    }
	
}
