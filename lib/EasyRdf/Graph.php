<?php

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009 Nicholas J Humfrey.  All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without 
 * modification, are permitted provided that the following conditions are met:
 * 1. Redistributions of source code must retain the above copyright 
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 * 3. The name of the author 'Nicholas J Humfrey" may be used to endorse or 
 *    promote products derived from this software without specific prior 
 *    written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" 
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE 
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE 
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE 
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR 
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF 
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS 
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN 
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE 
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 * @version    $Id$
 */

/**
 * @see EasyRdf_Exception
 */
require_once "EasyRdf/Exception.php";

/**
 * @see EasyRdf_Namespace
 */
require_once "EasyRdf/Namespace.php";

/**
 * @see EasyRdf_Resource
 */
require_once "EasyRdf/Resource.php";

/**
 * @see EasyRdf_TypeMapper
 */
require_once "EasyRdf/TypeMapper.php";

/**
 * @see EasyRdf_Utils
 */
require_once "EasyRdf/Utils.php";


/**
 * Class to 
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */
class EasyRdf_Graph
{
    /** The URI of the graph */
    private $_uri = null;
    
    /** Array of resources contained in the graph */
    private $_resources = array();
    
    /** Counter for the number of bnodes */
    private $_bNodeCount = 0;
    
    /** Index of resources organised by type */
    private $_typeIndex = array();
    
    /** An HTTP Client object used by graph to fetch data */
    private static $_httpClient = null;
    
    /** An RDF Parser object used by graph to parse RDF */
    private static $_rdfParser = null;
    
    /** A constant for the RDF Type property URI */
    const RDF_TYPE_URI = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type';


    /** Set the HTTP Client object used to fetch RDF data
     *
     * @param  object mixed $httpClient The new HTTP client object
     * @return object mixed The new HTTP client object
     */
    public static function setHttpClient($httpClient)
    {
        if (!is_object($httpClient) or $httpClient == null) {
            throw new InvalidArgumentException(
                "\$httpClient should be an object and cannot be null"
            );
        }
        return self::$_httpClient = $httpClient;
    }
    
    /** Get the HTTP Client object used to fetch RDF data
     *
     * If no HTTP Client has previously been set, then a new
     * default (EasyRdf_Http_Client) client will be created.
     *
     * @return object mixed The HTTP client object
     */
    public static function getHttpClient()
    {
        if (!self::$_httpClient) {
            require_once "EasyRdf/Http/Client.php";
            self::$_httpClient = new EasyRdf_Http_Client();
        }
        return self::$_httpClient;
    }

    /** Set the RDF parser object used to parse RDF data
     *
     * @param  object mixed $httpClient The new RDF parser object
     * @return object mixed The new RDF parser object
     */
    public static function setRdfParser($rdfParser)
    {
        if (!is_object($rdfParser) or $rdfParser == null) {
            throw new InvalidArgumentException(
                "\$rdfParser should be an object and cannot be null"
            );
        }
        self::$_rdfParser = $rdfParser;
    }
    
    /** Get the RDF parser object used to parse RDF data
     *
     * If no RDF Parser has previously been set, then a new
     * default (EasyRdf_RapperParser) parser will be created.
     *
     * @return object mixed The RDF parser object
     */
    public static function getRdfParser()
    {
        if (!self::$_rdfParser) {
            require_once "EasyRdf/RapperParser.php";
            self::$_rdfParser = new EasyRdf_RapperParser();
        }
        return self::$_rdfParser;
    }

    /** Convert a mime type into a simplier document type name
     *
     * If the mime type is not recognised, null is returned.
     *
     * @param  string $mimeType The mime type (e.g. application/rdf+xml)
     * @return string The document type name (e.g. rdfxml)
     */
    public static function simplifyMimeType($mimeType)
    {
        switch($mimeType) {
            case 'application/json':
            case 'text/json':
                return 'json';
            case 'application/rdf+xml':
                return 'rdfxml';
            case 'application/turtle':
            case 'text/turtle':
                return 'turtle';
            case 'application/n-triples':
                return 'ntriples';
            case 'text/html':
            case 'application/xhtml+xml':
                # FIXME: might be erdf or something instead...
                return 'rdfa';
            default:
                return null;
                break;
        }
    }
    
    /** Attempt to guess the document type from some content.
     *
     * If the document type is not recognised, null is returned.
     *
     * @param  string $data The document data
     * @return string The document type (e.g. rdfxml)
     */
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
        } else if (preg_match("/^<.+> <.+>/", $short)) {
            return 'ntriples';
        } else {
            return null;
        }
    }
    
    /**
     * Constructor
     *
     * If no URI is given then an empty graph is created.
     *
     * If a URI is supplied, but no data then the data will
     * be fetched from the URI.
     *
     * The document type is optional and can be specified if it
     * can't be guessed or got from the HTTP headers.
     *
     * @param  string  $uri     The URI of the graph
     * @param  string  $data    Data for the graph
     * @param  string  $docType The document type of the data
     * @return object EasyRdf_Graph
     */
    public function __construct($uri=null, $data=null, $docType=null)
    {
        if ($uri) {
            $this->_uri = $uri;
            $this->load($uri, $data, $docType);
        }
    }

    /** Get or create a resource stored in a graph
     *
     * If the resource did not previously exist, then a new resource will 
     * be created. If you provide an RDF type and that type is registered
     * with the EasyRDF_TypeMapper, then the resource will be an instance
     * of the class registered.
     *
     * @param  string  $uri    The URI of the resource
     * @param  mixed   $types  RDF type of a new resouce (e.g. foaf:Person)
     * @return object EasyRdf_Resouce
     */
    public function get($uri, $types = array())
    {
        # FIXME: allow URI to be shortened?
        if (!is_string($uri) or $uri == null or $uri == '') {
            throw new InvalidArgumentException(
                "\$uri should be a string and cannot be null or empty"
            );
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
                if ($type == null or $type == null) continue;
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
                if ($type != null and $type != '') {
                    $resource->add('rdf:type', $type);
                    if (!isset($this->_typeIndex[$type])) {
                        $this->_typeIndex[$type] = array();
                    }
                    if (!in_array($resource, $this->_typeIndex[$type])) {
                        array_push($this->_typeIndex[$type], $resource);
                    }
                }
            }
        }

        return $this->_resources[$uri];
    }
    
    /**
     * Create a new blank node in the graph and return it.
     *
     * If you provide an RDF type and that type is registered
     * with the EasyRDF_TypeMapper, then the resource will be an instance
     * of the class registered.
     *
     * @param  mixed   $types  RDF type of a new blank node (e.g. foaf:Person)
     * @return object EasyRdf_Resouce The new blank node
     */
    public function newBNode($types=array())
    {
        return $this->get("_:eid".(++$this->_bNodeCount), $types);
    }

    /**
     * Load RDF data into the graph.
     *
     * If a URI is supplied, but no data then the data will
     * be fetched from the URI.
     *
     * The document type is optional and can be specified if it
     * can't be guessed or got from the HTTP headers.
     *
     * @param  string  $uri     The URI of the graph
     * @param  string  $data    Data for the graph
     * @param  string  $docType The document type of the data
     */
    public function load($uri, $data=null, $docType=null)
    {
        if (!is_string($uri) or $uri == null or $uri == '') {
            throw new InvalidArgumentException(
                "\$uri should be a string and cannot be null or empty"
            );
        }

        if (!$data) {
            # No data was given - try and fetch data from URI
            # FIXME: prevent loading the same URI multiple times
            $client = self::getHttpClient();
            $client->setUri($uri);
            # FIXME: set the accept header to formats we are able to parse
            $client->setHeaders('Accept', 'application/rdf+xml');
            $response = $client->request();
            if (!$response->isSuccessful()) {
                throw new EasyRdf_Exception(
                    "HTTP request for $uri failed: ".$response->getMessage()
                );
            }
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
                throw new EasyRdf_Exception(
                    "Failed to parse data for URI: $uri (\$docType = $docType)"
                );
            }
        }

        # Convert into an object graph
        $bnodeMap = array();
        foreach ($data as $subj => $touple) {
            $type = $this->getResourceType($data, $subj);
            
            # Is this a bnode?
            if (substr($subj, 0, 2) == '_:') {
                if (!isset($bnodeMap[$subj])) {
                    $bnodeMap[$subj] = $this->newBNode($type);
                }
                $res = $bnodeMap[$subj];
            } else {
                $res = $this->get($subj, $type);
            }
              
            foreach ($touple as $property => $objs) {
                $property = EasyRdf_Namespace::shorten($property);
                if (isset($property)) {
                    foreach ($objs as $obj) {
                        if ($property == 'rdf:type') {
                            # Type has already been set
                        } else if ($obj['type'] == 'literal') {
                            $res->add($property, $obj['value']);
                        } else if ($obj['type'] == 'uri') {
                            $type = $this->getResourceType(
                                $data, $obj['value']
                            );
                            $objres = $this->get($obj['value'], $type);
                            $res->add($property, $objres);
                        } else if ($obj['type'] == 'bnode') {
                            $objuri = $obj['value'];
                            if (!isset($bnodeMap[$objuri])) {
                                $type = $this->getResourceType(
                                    $data, $obj['value']
                                );
                                $bnodeMap[$objuri] = $this->newBNode($type);
                            }
                            $res->add($property, $bnodeMap[$objuri]);
                        } else {
                            # FIXME: thow exception or silently ignore?
                        }
                    }
                }
            }
        
        }
    }
    
    /**
     * Get the type of a resource from some RDF/PHP
     * (http://n2.talis.com/wiki/RDF_PHP_Specification)
     */
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

    /** Get an associative arry of all the resouces stored in the graph
     *
     * @return array Array of EasyRdf_Resouces
     */
    public function resources()
    {
        return $this->_resources;
    }
    
    /** Get all the resources in the graph of a certain type
     *
     * If no resources of the type are available and empty
     * array is returned.
     *
     * @param  string  $type   The type of the resource (e.g. foaf:Person)
     * @return array The array of resources
     */
    public function allOfType($type)
    {
        # FIXME: shorten if $type is a URL
        if (isset($this->_typeIndex[$type])) {
            return $this->_typeIndex[$type];
        } else {
            return array();
        }
    }
    
    /** Get a list of the types of resources in the graph
     *
     * @return array Array of types
     */
    public function allTypes()
    {
        return array_keys($this->_typeIndex);
    }
    
    /** Get the URI of the graph
     *
     * @return string The URI of the graph
     */
    public function getUri()
    {
        return $this->_uri;
    }
    
    /** Add data to the graph
     *
     * The resource can either be a resource or the URI of a resource.
     *
     * The properties can either be a single property name or an
     * associate array of property names and values.
     *
     * The value can either be a single value or an array of values.
     *
     * Examples:
     *   $res = $graph->get("http://www.example.com");
     *   $graph->add($res, 'prefix:property', 'value');
     *   $graph->add($res, 'prefix:property', array('value1',value2'));
     *   $graph->add($res, array('prefix:property' => 'value1'));
     *   $graph->add($res, 'foaf:knows', array( 'foaf:name' => 'Name'));
     *   $graph->add($res, array('foaf:knows' => array( 'foaf:name' => 'Name'));
     *
     * @param  mixed $resource   The resource to add data to
     * @param  mixed $properties The properties or property names
     * @param  mixed $value      The new value for the property
     */
    public function add($resource, $properties, $value=null)
    {
        if (!is_object($resource)) {
            $resource = $this->get($resource);
        } else if (!$resource instanceof EasyRdf_Resource) {
            throw new InvalidArgumentException(
                "\$resource should be an instance of the EasyRdf_Resource class"
            );
        }
        
        if (EasyRdf_Utils::is_associative_array($properties)) {
            foreach ($properties as $property => $value) {
                $this->add($resource, $property, $value);
            }
            return;
        } else {
            if (EasyRdf_Utils::is_associative_array($value)) {
                if (isset($value['rdf:type'])) {
                    $bnode = $this->newBNode($value['rdf:type']);
                } else {
                    $bnode = $this->newBNode();
                }
                $bnode->add($value);
                $value = $bnode;
            }
            $resource->add($properties, $value);
        }
    }

    /** Display all the resources in the graph
     *
     * This method is intended to be a debugging aid and will
     * print all the resources and their properties to the screen.
     *
     * @param  bool  $html  Set to true to format the dump using HTML
     */
    public function dump($html=true)
    {
        # FIXME: display some information about the graph
        foreach ($this->_resources as $resource) {
            $resource->dump($html, 1);
        }
    }
    
    /** Get the resource type of the graph
     *
     * The type will be a shortened URI as a string.
     * If the graph has multiple types then the type returned 
     * may be arbitrary.
     * This method will return null if the resource has no type.
     *
     * @return string A type assocated with the resource (e.g. foaf:Document)
     */
    public function type()
    {
        if ($this->_uri) {
            $res = $this->get($this->_uri);
            return $res->type();
        } else {
            return null;
        }
    }
    
    /** Get the primary topic of the graph
     *
     * @return EasyRdf_Resource The primary topic of the document.
     */
    public function primaryTopic()
    {
        if ($this->_uri) {
            $res = $this->get($this->_uri);
            return $res->get('foaf:primaryTopic');
        } else {
            return null;
        }
    }
    
    public function toTurtle()
    {
        // FIXME: should this be here?
        // Reduces overhead if fearure not used?
        require_once "EasyRdf/Serialiser/Turtle.php";
        return EasyRdf_Serialiser_Turtle::serialise($this);
    }

    
    // BEWARE! Magic below

    /** Magic method to give access to properties using method calls
     *
     * Calls are passed on to the correspoding resource for the graph.
     *
     * @see EasyRdf_Resource::__call()
     * @return mixed The value(s) of the properties requested.
     */
    public function __call($name, $arguments)
    {
        if ($this->_uri) {
            $res = $this->get($this->_uri);
            return call_user_func_array(array($res, $name), $arguments);
        } else {
            return null;
        }
    }
    
    /** Magic method to return URI of resource when casted to string
     *
     * @return string The URI of the resource
     */
    public function __toString()
    {
        return $this->_uri;
    }
}
