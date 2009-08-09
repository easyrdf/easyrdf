<?php

require_once "EasyRdf/Resource.php";
require_once "EasyRdf/Namespace.php";

class EasyRdf_Graph
{
    private $uri = null;
    private $resources = array();
    private $type_index = array();
    private static $http_client = null;
    private static $parser = null;

    /**
	   * Get a Resource object for a specific URI
	   * @return EasyRdf_Resource returns a Resource (or null if it does not exist)
	   */
    public function getResource($uri)
    {
        # Create resource object if it doesn't already exist
        if (!array_key_exists($uri, $this->resources)) {
            $this->resources[$uri] = new EasyRdf_Resource($uri);
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
         $this->http_client = $http_client;
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
        $this->parser = $parser;
    }
    
    public function __construct($uri, $data='', $doc_type='guess')
    {
        $this->uri = $uri;
        $this->load($uri, $data, $doc_type);
    }

/*
    public function load()
    {
        $args = array();
        $http_proxy = getenv('http_proxy');
        if ($http_proxy) {
            $proxy = parse_url($http_proxy);
            $args = array('proxy_host' => $proxy['host'], 'proxy_port' => $proxy['port']);
        }
        $parser = ARC2::getRDFXMLParser($args);
        $parser->parse($this->uri);

        $this->construct_resources($parser);
    }
*/

	/**
	 * Convert RDF/PHP into a graph of objects
	 */
    public function load($uri, $data='', $doc_type='guess')
    {

        // FIXME: validate the URI

        if (!$data) {
            $client = self::getHttpClient();
            $client->setUri($uri);
            $response = $client->request();
            # FIXME: make use of the 'content type' header
            $data = $response->getBody();
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
          $res = $this->getResource($subj);
          foreach ($touple as $property => $objs) {
            $property = EasyRdf_Namespace::shorten($property);
            if (isset($property)) {
              foreach ($objs as $obj) {
                if ($obj['type'] == 'literal') {
                  $res->set($property, $obj['value']);
                } else if ($obj['type'] == 'uri' or $obj['type'] == 'bnode') {
                  $objres = $this->getResource($obj['value']);
                  $res->set($property, $objres);
                  if ($property == 'rdf_type') {
                      $this->addToTypeIndex( $res, $obj['value'] );
                  }
                } else {
                  # FIXME: thow exception?
                }
              }
            }
          }
        
        }
    }
    
    private function addToTypeIndex($resource, $type)
    {
        $type = EasyRdf_Namespace::shorten($type);
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
    
    public function primaryTopic()
    {
        $res = $this->getResource($this->uri);
        return $res->first('foaf_primaryTopic');
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
