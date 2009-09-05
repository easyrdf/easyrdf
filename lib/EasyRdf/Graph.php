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
	   */
    public function getResource($uri, $types = array())
    {
        # FIXME: allow URI to be shortened?
        # FIXME: throw exception if parameters are bad?
        if (!$uri) {
            return null;
        }
    
        # Convert types to an array if it isn't one
        # FIXME: shorten types if not already short
        if (!is_array($types)) {
            $types = array($types);
        }
    
        # Create resource object if it doesn't already exist
        if (!array_key_exists($uri, $this->resources)) {
            $res_class = 'EasyRdf_Resource';
            foreach ($types as $type) {
                $class = EasyRDF_TypeMapper::get($type);
                if ($class != null) {
                    $res_class = $class;
                    break;
                }
            }
            $this->resources[$uri] = new $res_class($uri);
        }

        # Add resource to the type index
        $resource = $this->resources[$uri];
        foreach ($types as $type) {
            if (!isset($this->type_index[$type])) {
                $this->type_index[$type] = array();
            }
            if (!in_array($resource, $this->type_index[$type])) {
                array_push($this->type_index[$type], $resource);
            }
        }

        return $resource;
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
            case 'text/html':
            case 'application/xhtml+xml':
                # FIXME: might be erdf or something instead...
                return 'rdfa';
            default:
                # FIXME: throw exception?
                return '';
        }
    }
    
    public static function guessDocType($data)
    {
        if (is_array($data)) {
            # Data has already been parsed into RDF/PHP
            return 'php';
        }
        
        # FIXME: could /etc/magic help here?
        $short = substr( trim($data), 0, 255 );
        if (ereg("^\{", $short)) {
            return 'json';
        } else if (ereg("^---", $short)) {
            return 'yaml';
        } else if (ereg("<!DOCTYPE html", $short) or ereg("^<html", $short)) {
            # FIXME: might be erdf or something instead...
            return 'rdfa';
        } else if (ereg("<rdf", $short)) {
            return 'rdfxml';
        } else if (ereg("^@prefix ", $short)) {
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
            # FIXME: prevent loading the same URI multiple times
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
    
    public function allOfType($type)
    {
        # FIXME: shorten if $type is a URL
        if ($this->type_index[$type]) {
            return $this->type_index[$type];
        } else {
            return array();
        }
    }
    
    public function firstOfType($type)
    {
        $objs = $this->allOfType($type);
        if ($objs and is_array($objs)) {
            return $objs[0];
        }
    }
    
    public function allTypes()
    {
        return array_keys( $this->type_index );
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
    
    public function type()
    {
        $res = $this->getResource($this->uri);
        // FIXME: check $res isn't null
        return $res->type();
    }
    
    public function primaryTopic()
    {
        $res = $this->getResource($this->uri);
        // FIXME: check $res isn't null
        return $res->first('foaf_primaryTopic');
    }

    
    // BEWARE! Magic below
    
    public function __toString()
    {
        return $this->uri;
    }

    public function __call($name, $arguments)
    {
        $res = $this->getResource($this->uri);
        // FIXME: check $res isn't null
        return call_user_func_array( array($res, $name), $arguments );
    }

    public function __get($name)
    {
        $res = $this->getResource($this->uri);
        // FIXME: check $res isn't null
        return $res->$name;
    }
	
}
