<?php

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009-2010 Nicholas J Humfrey.  All rights reserved.
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
 * @copyright  Copyright (c) 2009-2010 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 * @version    $Id$
 */

/**
 * Container for collection of EasyRdf_Resources.
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009-2010 Nicholas J Humfrey
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

    /** An HTTP Client object used by graph to fetch data */
    private static $_httpClient = null;


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
            self::$_httpClient = new EasyRdf_Http_Client();
        }
        return self::$_httpClient;
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
     * @param  string  $format  The document type of the data
     * @return object EasyRdf_Graph
     */
    public function __construct($uri=null, $data=null, $format=null)
    {
        if ($uri) {
            $this->_uri = $uri;
            $this->load($uri, $data, $format);
        }
    }

    /** Get or create a resource stored in a graph
     *
     * If the resource did not previously exist, then a new resource will
     * be created. If you provide an RDF type and that type is registered
     * with the EasyRdf_TypeMapper, then the resource will be an instance
     * of the class registered.
     *
     * @param  string  $uri    The URI of the resource
     * @param  mixed   $types  RDF type of a new resouce (e.g. foaf:Person)
     * @return object EasyRdf_Resouce
     */
    public function resource($uri, $types = array())
    {
        if (!is_string($uri) or $uri == null or $uri == '') {
            throw new InvalidArgumentException(
                "\$uri should be a string and cannot be null or empty"
            );
        }

        // Expand the URI if it is shortened
        $uri = EasyRdf_Namespace::expand($uri);

        // Convert types to an array if it isn't one
        if (!$types) {
            $types = array();
        } else if (!is_array($types)) {
            $types = array($types);
        }

        // Create resource object if it doesn't already exist
        if (!array_key_exists($uri, $this->_resources)) {
            $resClass = 'EasyRdf_Resource';
            foreach ($types as $type) {
                if ($type == null or $type == null) continue;
                $class = EasyRdf_TypeMapper::get($type);
                if ($class != null) {
                    $resClass = $class;
                    break;
                }
            }
            $this->_resources[$uri] = new $resClass($uri);
        }

        // Add the rdf:type triples
        foreach ($types as $type) {
            $type = $this->resource($type);
            $this->_resources[$uri]->add('rdf:type', $type);
        }

        return $this->_resources[$uri];
    }

    /**
     * Alias for $graph->resource()
     *
     * @deprecated 0.4
     */
    public function get($uri, $types = array())
    {
        return $this->resource($uri, $types);
    }

    /**
     * Create a new blank node in the graph and return it.
     *
     * If you provide an RDF type and that type is registered
     * with the EasyRdf_TypeMapper, then the resource will be an instance
     * of the class registered.
     *
     * @param  mixed   $types  RDF type of a new blank node (e.g. foaf:Person)
     * @return object EasyRdf_Resouce The new blank node
     */
    public function newBNode($types=array())
    {
        return $this->resource("_:eid".(++$this->_bNodeCount), $types);
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
     * @param  string  $format  The document type of the data
     */
    public function load($uri, $data=null, $format=null)
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
            $client->setHeaders('Accept', EasyRdf_Format::getHttpAcceptHeader());
            $response = $client->request();
            if (!$response->isSuccessful()) {
                throw new EasyRdf_Exception(
                    "HTTP request for $uri failed: ".$response->getMessage()
                );
            }
            $data = $response->getBody();
            if (!$format) {
                $format = $response->getHeader('Content-Type');
                $format = preg_replace('/;(.+)$/', '', $format);
            }
        }

        # Guess the format if it is Unknown
        if (!$format)
            $format = EasyRdf_Format::guessFormat($data);

        if (!$format)
            throw new EasyRdf_Exception(
                "Unable to load data of an unknown format."
            );

        # Parse the data
        $format = EasyRdf_Format::getFormat($format);
        $parser = $format->newParser();
        return $parser->parse($this, $data, $format, $uri);
    }

    /** Get an associative array of all the resources stored in the graph
     *
     * @return array Array of EasyRdf_Resouces
     */
    public function resources()
    {
        return $this->_resources;
    }

    /** Get an arry of resources matching a certain property and value.
     *
     * For example this routine could be used as a way of getting
     * everyone who is male:
     * $people = $graph->resourcesMatching('foaf:gender', 'male');
     *
     * @param  string  $property   The property to check.
     * @param  mixed   $value      The value of the propery to check for.
     * @return array Array of EasyRdf_Resouces
     */
    public function resourcesMatching($property, $value)
    {
        $matched = array();
        foreach ($this->_resources as $resource) {
            if ($resource->matches($property, $value)) {
                array_push($matched, $resource);
            }
        }
        return $matched;
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
        $uri = EasyRdf_Namespace::expand($type);
        $resource = $this->resource($uri);
        return $resource->all('-rdf:type');
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
     *   $res = $graph->resource("http://www.example.com");
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
            $resource = $this->resource($resource);
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

    /** Serialise the graph into RDF
     *
     * @param  string  $format  The format to serialise to
     * @return mixed   The serialised graph
     */
    public function serialise($format)
    {
        $format = EasyRdf_Format::getFormat($format);
        $serialiser = $format->newSerialiser();
        return $serialiser->serialise($this, $format->getName());
    }

    /** Return view of all the resources in the graph
     *
     * This method is intended to be a debugging aid and will
     * return a pretty-print view of  all the resources and their
     * properties.
     *
     * @param  bool  $html  Set to true to format the dump using HTML
     */
    public function dump($html=true)
    {
        $r = array();
        foreach ($this->_resources as $resource) {
            $r[] = $resource->dump($html);
        }
        return join('', $r);
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
            $res = $this->resource($this->_uri);
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
            $res = $this->resource($this->_uri);
            return $res->get('foaf:primaryTopic');
        } else {
            return null;
        }
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
            $res = $this->resource($this->_uri);
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
        return $this->_uri == null ? '' : $this->_uri;
    }
}
