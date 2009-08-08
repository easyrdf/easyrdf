<?php

require_once "EasyRdf/Resource.php";
require_once "EasyRdf/Namespace.php";

# FIXME: only require these if/when they are used?
require_once "EasyRdf/RapperParser.php";

class EasyRdf_Graph
{
    private $_uri = NULL;
    private $_resources = array();
    private $_type_index = array();
    private static $_http_client = NULL;
    private static $_parser = NULL;

    /**
	   * Get a Resource object for a specific URI
	   * @return EasyRdf_Resource returns a Resource (or NULL if it does not exist)
	   */
    public function getResource($uri)
    {
        # Create resource object if it doesn't already exist
        if (!array_key_exists($uri, $this->_resources)) {
            $this->_resources[$uri] = new EasyRdf_Resource($uri);
        }
        return $this->_resources[$uri];
    }
    
	  /**
     * Return all known resources
     */
    public function resources()
    {
        return array_values($this->_resources);
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
         $this->_http_client = $http_client;
   }
    
    public static function getHttpClient()
    {
    }

    public static function getRdfParser()
    {
        if (!self::$_parser) {
            self::$_parser = new EasyRdf_RapperParser();
        }
        return self::$_parser;
    }

    public static function setRdfParser($parser)
    {
        $this->_parser = $parser;
    }
    
    public function __construct($uri, $data='', $doc_type='guess')
    {
        $this->_uri = $uri;
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
        $parser->parse($this->_uri);

        $this->_construct_resources($parser);
    }
*/

	/**
	 * Convert RDF/PHP into a graph of objects
	 */
    public function load($uri, $data='', $doc_type='guess')
    {

        // FIXME: validate the URI

        if (!$data) {
        
          # FIXME: fetch data from the URI
          
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
              return NULL;
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
            if (!isset($this->_type_index[$type])) {
                $this->_type_index[$type] = array();
            }
            if (!in_array($resource, $this->_type_index[$type])) {
                array_push($this->_type_index[$type], $resource);
            }
        }
    }
    
    public function allByType($type)
    {
        # FIXME: shorten if $type is a URL
        return $this->_type_index[$type];
    }
    
    public function allTypes()
    {
        return array_keys( $this->_type_index );
    }
    
    public function primaryTopic()
    {
        $res = $this->getResource($this->_uri);
        return $res->first('foaf_primaryTopic');
    }
    
    public function addTriples($resource, $dict)
    {
        # FIXME: implement this
    }
	
    
    public function dump($html=true)
    {
        # FIXME: display some information about the graph
        foreach ($this->_resources as $resource) {
            $resource->dump($html,1);
        }
    }
	
}
