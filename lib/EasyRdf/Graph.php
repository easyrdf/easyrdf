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

    public static function setRdfParser($parser)
    {
        self::$parser = $parser;
    }
    
    public function __construct($uri, $data='', $doc_type='guess')
    {
        $this->uri = $uri;
        $this->load($uri, $data, $doc_type);
    }

    /**
     * Convert RDF/PHP into a graph of objects
     */
    public function load($uri, $data='', $doc_type='guess')
    {

        // FIXME: validate the URI

        if (!$data) {
            $client = self::getHttpClient();
            $client->setUri($uri);
            # FIXME: set the accept header to a list of formats we are able to parse
            $response = $client->request();
            # FIXME: make use of the 'content type' header
            $data = $response->getBody();
            $doc_type = $response->getHeader('Content-Type');
        }
        
        # Guess the document type if not given
        if ($doc_type == 'guess') {
          $doc_type = self::getRdfParser()->guessDocType( $data );
        }
        
        if ($doc_type != 'php') {
          
          # Parse the RDF data if it isn't PHP
          $data = self::getRdfParser()->parse( $uri, $data, $doc_type );
          if (!$data) {
              # FIXME: parse error
              return null;
          }
        }

        # Convert into an object graph
        foreach ($data as $subj => $touple) {
          $type = $this->getResouceType($data, $subj);
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
                  $type = $this->getResouceType($data, $obj['value']);
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
    
    private function getResouceType( $data, $uri )
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
