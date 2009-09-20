<?php

require_once "EasyRdf/Resource.php";
require_once "EasyRdf/Namespace.php";
require_once "EasyRdf/TypeMapper.php";

class EasyRdf_Graph
{
    private $_uri = null;
    private $_resources = array();
    private $_typeIndex = array();
    private static $_httpClient = null;
    private static $_parser = null;
    
    const RDF_TYPE_URI = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type';

    /**
       * Get a Resource object for a specific URI
       */
    public function get($uri, $types = array())
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
        if (!array_key_exists($uri, $this->_resources)) {
            $resClass = 'EasyRdf_Resource';
            foreach ($types as $type) {
                $class = EasyRDF_TypeMapper::get($type);
                if ($class != null) {
                    $resClass = $class;
                    break;
                }
            }
            $this->_resources[$uri] = new $resClass($uri);

            # Add resource to the type index
            $resource = $this->_resources[$uri];
            foreach ($types as $type) {
                $resource->add('rdf_type', $type);
                if (!isset($this->_typeIndex[$type])) {
                    $this->_typeIndex[$type] = array();
                }
                if (!in_array($resource, $this->_typeIndex[$type])) {
                    array_push($this->_typeIndex[$type], $resource);
                }
            }
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

    public static function setHttpClient($httpClient)
    {
        self::$_httpClient = $httpClient;
    }
    
    public static function getHttpClient()
    {
        if (!self::$_httpClient) {
            require_once "EasyRdf/Http/Client.php";
            self::$_httpClient = new EasyRdf_Http_Client();
        }
        return self::$_httpClient;
    }

    public static function getRdfParser()
    {
        if (!self::$_parser) {
            require_once "EasyRdf/RapperParser.php";
            self::$_parser = new EasyRdf_RapperParser();
        }
        return self::$_parser;
    }

    public static function simplifyMimeType($mimeType)
    {
        switch($mimeType) {
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
                return null;
                break;
        }
    }
    
    public static function guessDocType($data)
    {
        if (is_array($data)) {
            # Data has already been parsed into RDF/PHP
            return 'php';
        }
        
        # FIXME: could /etc/magic help here?
        $short = substr(trim($data), 0, 255);
        if (preg_match("/^\{/", $short)) {
            return 'json';
        } else if (preg_match("/^---/", $short)) {
            return 'yaml';
        } else if (
            preg_match("/<!DOCTYPE html/", $short) or
            preg_match("/^<html/", $short)
        ) {
            # FIXME: might be erdf or something instead...
            return 'rdfa';
        } else if (preg_match("/<rdf/", $short)) {
            return 'rdfxml';
        } else if (preg_match("/^@prefix /", $short)) {
            # FIXME: this could be improved
            return 'turtle';
        } else {
            # FIXME: throw exception?
            return null;
        }
    }

    public static function setRdfParser($parser)
    {
        self::$_parser = $parser;
    }
    
    public function __construct($uri='', $data='', $docType=null)
    {
        if ($uri) {
            $this->_uri = $uri;
            $this->load($uri, $data, $docType);
        }
    }

    /**
     * Convert RDF/PHP into a graph of objects
     */
    public function load($uri, $data='', $docType=null)
    {
        // FIXME: validate the URI?

        if (!$data) {
            # No data was given - try and fetch data from URI
            # FIXME: prevent loading the same URI multiple times
            $client = self::getHttpClient();
            $client->setUri($uri);
            # FIXME: set the accept header to formats we are able to parse
            $client->setHeaders('Accept', 'application/rdf+xml');
            $response = $client->request();
            $data = $response->getBody();
            if ($docType == null) {
                $docType = self::simplifyMimeType(
                    $response->getHeader('Content-Type')
                );
            }
        }
        
        # Guess the document type if not given
        if ($docType == null) {
            $docType = self::guessDocType($data);
        }
        
        # Parse the document
        if ($docType == 'php') {
            # FIXME: validate the data?
        } else if ($docType == 'json') {
            # Parse the RDF/JSON into RDF/PHP
            $data = json_decode($data, true);
        } else {
            # Parse the RDF data
            $data = self::getRdfParser()->parse($uri, $data, $docType);
            if (!$data) {
                # FIXME: parse error - throw exception?
                return null;
            }
        }

        # Convert into an object graph
        foreach ($data as $subj => $touple) {
          $type = $this->getResourceType($data, $subj);
          $res = $this->get($subj, $type);
          foreach ($touple as $property => $objs) {
            $property = EasyRdf_Namespace::shorten($property);
            if (isset($property)) {
              foreach ($objs as $obj) {
                if ($property == 'rdf_type') {
                  # Type has already been set
                } else if ($obj['type'] == 'literal') {
                  $res->add($property, $obj['value']);
                } else if ($obj['type'] == 'uri' or $obj['type'] == 'bnode') {
                  $type = $this->getResourceType($data, $obj['value']);
                  $objres = $this->get($obj['value'], $type);
                  $res->add($property, $objres);
                } else {
                  # FIXME: thow exception or silently ignore?
                }
              }
            }
          }
        
        }
    }
    
    private function getResourceType($data, $uri)
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
        if ($this->_typeIndex[$type]) {
            return $this->_typeIndex[$type];
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
        return array_keys($this->_typeIndex);
    }
    
    public function getUri()
    {
        return $this->_uri;
    }
    
    public function add($resource, $properties, $value=null)
    {
        if (!is_object($resource)) {
            # FIXME: check object type
            # FIXME: allow shortened URIs?
            $resource = $this->get($resource);
        }
        
        if (is_array($properties)) {
            foreach ($properties as $property => $value) {
                # FIXME: check if value is a URI?
                $resource->add($property, $value);
            }
        } else {
            $resource->add($properties, $value);
        }
    }

    public function dump($html=true)
    {
        # FIXME: display some information about the graph
        foreach ($this->_resources as $resource) {
            $resource->dump($html, 1);
        }
    }
    
    public function type()
    {
        $res = $this->get($this->_uri);
        // FIXME: check $res isn't null
        return $res->type();
    }
    
    public function primaryTopic()
    {
        $res = $this->get($this->_uri);
        // FIXME: check $res isn't null
        return $res->get('foaf_primaryTopic');
    }

    
    // BEWARE! Magic below
    
    public function __toString()
    {
        return $this->_uri;
    }

    public function __call($name, $arguments)
    {
        $res = $this->get($this->_uri);
        // FIXME: check $res isn't null
        return call_user_func_array(array($res, $name), $arguments);
    }
    
}
