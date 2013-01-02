<?php

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009-2012 Nicholas J Humfrey.  All rights reserved.
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
 * @copyright  Copyright (c) 2009-2012 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 * @version    $Id$
 */

/**
 * Container for collection of EasyRdf_Resources.
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009-2012 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */
class EasyRdf_Graph
{
    /** The URI of the graph */
    private $_uri = null;
    private $_parsedUri = null;

    /** Array of resources contained in the graph */
    private $_resources = array();

    private $_index = array();
    private $_revIndex = array();

    /** Counter for the number of bnodes */
    private $_bNodeCount = 0;

    /** Array of URLs that have been loaded into the graph */
    private $_loaded = array();

    private $_maxRedirects = 10;


    /**
     * Constructor
     *
     * If no URI is given then an unnamed graph is created.
     *
     * The $data parameter is optional and will be parsed into
     * the graph if given.
     *
     * The data format is optional and should be specified if it
     * can't be guessed by EasyRdf.
     *
     * @param  string  $uri     The URI of the graph
     * @param  string  $data    Data for the graph
     * @param  string  $format  The document type of the data (e.g. rdfxml)
     * @return object EasyRdf_Graph
     */
    public function __construct($uri=null, $data=null, $format=null)
    {
        $this->checkResourceParam($uri, true);

        if ($uri) {
            $this->_uri = $uri;
            $this->_parsedUri = new EasyRdf_ParsedUri($uri);
            if ($data)
                $this->parse($data, $format, $this->_uri);
        }
    }

    /**
     * Create a new graph and load RDF data from a URI into it
     *
     * This static function is shorthand for:
     *     $graph = new EasyRdf_Graph($uri);
     *     $graph->load($uri, $format);
     *
     * The document type is optional but should be specified if it
     * can't be guessed or got from the HTTP headers.
     *
     * @param  string  $uri     The URI of the data to load
     * @param  string  $format  Optional format of the data (eg. rdfxml)
     * @return object EasyRdf_Graph    The new the graph object
     */
    public static function newAndLoad($uri, $format=null)
    {
        $graph = new self($uri);
        $graph->load($uri, $format);
        return $graph;
    }

    /** Get or create a resource stored in a graph
     *
     * If the resource did not previously exist, then a new resource will
     * be created. If you provide an RDF type and that type is registered
     * with the EasyRdf_TypeMapper, then the resource will be an instance
     * of the class registered.
     *
     * If URI is null, then the URI of the graph is used.
     *
     * @param  string  $uri    The URI of the resource
     * @param  mixed   $types  RDF type of a new resource (e.g. foaf:Person)
     * @return object EasyRdf_Resource
     */
    public function resource($uri=null, $types=array())
    {
        $this->checkResourceParam($uri, true);
        if (!$uri) {
            throw new InvalidArgumentException(
                '$uri is null and EasyRdf_Graph object has no URI either.'
            );
        }

        // Resolve relative URIs
        if ($this->_parsedUri) {
            $uri = $this->_parsedUri->resolve($uri)->toString();
        }

        // Add the types
        $this->addType($uri, $types);

        // Create resource object if it doesn't already exist
        if (!isset($this->_resources[$uri])) {
            $resClass = $this->classForResource($uri);
            $this->_resources[$uri] = new $resClass($uri, $this);
        }

        return $this->_resources[$uri];
    }

    /** Work out the class to instantiate a resource as
     *  @ignore
     */
    protected function classForResource($uri)
    {
        $resClass = 'EasyRdf_Resource';
        $rdfType = EasyRdf_Namespace::expand('rdf:type');
        if (isset($this->_index[$uri][$rdfType])) {
            foreach ($this->_index[$uri][$rdfType] as $type) {
                if ($type['type'] == 'uri' or $type['type'] == 'bnode') {
                    $class = EasyRdf_TypeMapper::get($type['value']);
                    if ($class != null) {
                        $resClass = $class;
                        break;
                    }
                }

            }
        }
        return $resClass;
    }

    /**
     * Create a new blank node in the graph and return it.
     *
     * If you provide an RDF type and that type is registered
     * with the EasyRdf_TypeMapper, then the resource will be an instance
     * of the class registered.
     *
     * @param  mixed  $types  RDF type of a new blank node (e.g. foaf:Person)
     * @return object EasyRdf_Resource The new blank node
     */
    public function newBNode($types=array())
    {
        return $this->resource($this->newBNodeId(), $types);
    }

    /**
     * Create a new unique blank node identifier and return it.
     *
     * @return string The new blank node identifier (e.g. _:genid1)
     */
    public function newBNodeId()
    {
        return "_:genid".(++$this->_bNodeCount);
    }

    /**
     * Parse some RDF data into the graph object.
     *
     * @param  string  $data    Data to parse for the graph
     * @param  string  $format  Optional format of the data
     * @param  string  $uri     The URI of the data to load
     * @return integer          The number of triples added to the graph
     */
    public function parse($data, $format=null, $uri=null)
    {
        $this->checkResourceParam($uri, true);

        if (empty($format) or $format == 'guess') {
            // Guess the format if it is Unknown
            $format = EasyRdf_Format::guessFormat($data, $uri);
        } else {
            $format = EasyRdf_Format::getFormat($format);
        }

        if (!$format)
            throw new EasyRdf_Exception(
                "Unable to parse data of an unknown format."
            );

        $parser = $format->newParser();
        return $parser->parse($this, $data, $format, $uri);
    }

    /**
     * Parse a file containing RDF data into the graph object.
     *
     * @param  string  $filename The path of the file to load
     * @param  string  $format   Optional format of the file
     * @param  string  $uri      The URI of the file to load
     * @return integer           The number of triples added to the graph
     */
    public function parseFile($filename, $format=null, $uri=null)
    {
        if ($uri === null) {
            $uri = "file://$filename";
        }

        return $this->parse(
            file_get_contents($filename),
            $format,
            $uri
        );
    }

    /**
     * Load RDF data into the graph from a URI.
     *
     * If no URI is given, then the URI of the graph will be used.
     *
     * The document type is optional but should be specified if it
     * can't be guessed or got from the HTTP headers.
     *
     * @param  string  $uri     The URI of the data to load
     * @param  string  $format  Optional format of the data (eg. rdfxml)
     * @return integer          The number of triples added to the graph
     */
    public function load($uri=null, $format=null)
    {
        $this->checkResourceParam($uri, true);

        if (!$uri)
            throw new EasyRdf_Exception(
                "No URI given to load() and the graph does not have a URI."
            );

        // Setup the HTTP client
        $client = EasyRdf_Http::getDefaultHttpClient();
        $client->resetParameters(true);
        $client->setConfig(array('maxredirects' => 0));
        $client->setMethod('GET');
        $client->setHeaders('Accept', EasyRdf_Format::getHttpAcceptHeader());

        $requestUrl = $uri;
        $response = NULL;
        $redirectCounter = 0;
        do {
            // Have we already loaded it into the graph?
            $requestUrl = EasyRdf_Utils::removeFragmentFromUri($requestUrl);
            if (in_array($requestUrl, $this->_loaded)) {
                return 0;
            }

            // Make the HTTP request
            $client->setHeaders('host', null);
            $client->setUri($requestUrl);
            $response = $client->request();

            // Add the URL to the list of URLs loaded
            $this->_loaded[] = $requestUrl;

            if ($response->isRedirect() and $location = $response->getHeader('location')) {
                // Avoid problems with buggy servers that add whitespace
                $location = trim($location);

                // Some servers return relative URLs in the location header
                // resolve it in relation to previous request
                $baseUri = new EasyRdf_ParsedUri($requestUrl);
                $requestUrl = $baseUri->resolve($location)->toString();
                $requestUrl = EasyRdf_Utils::removeFragmentFromUri($requestUrl);

                // If it is a 303 then drop the parameters
                if ($response->getStatus() == 303) {
                    $client->resetParameters();
                }

                ++$redirectCounter;
            } elseif ($response->isSuccessful()) {
                // If we didn't get any location, stop redirecting
                break;
            } else {
                throw new EasyRdf_Exception(
                    "HTTP request for $requestUrl failed: ".$response->getMessage()
                );
            }
        } while ($redirectCounter < $this->_maxRedirects);

        if (!$format or $format == 'guess') {
            list($format, $params) = EasyRdf_Utils::parseMimeType(
                $response->getHeader('Content-Type')
            );
        }

        // Parse the data
        return $this->parse($response->getBody(), $format, $uri);
    }

    /** Get an associative array of all the resources stored in the graph.
     *  The keys of the array is the URI of the EasyRdf_Resource.
     *
     * @return array Array of EasyRdf_Resource
     */
    public function resources()
    {
        foreach ($this->_index as $subject => $properties) {
            $this->resource($subject);
        }

        foreach ($this->_revIndex as $object => $properties) {
            if (!isset($this->_resources[$object])) {
                $this->resource($object);
            }
        }

        return $this->_resources;
    }

    /** Get an arry of resources matching a certain property and optional value.
     *
     * For example this routine could be used as a way of getting
     * everyone who has name:
     * $people = $graph->resourcesMatching('foaf:name')
     *
     * Or everyone who is male:
     * $people = $graph->resourcesMatching('foaf:gender', 'male');
     *
     * Or all homepages:
     * $people = $graph->resourcesMatching('^foaf:homepage');
     *
     * @param  string  $property   The property to check.
     * @param  mixed   $value      Optional, the value of the propery to check for.
     * @return array   Array of EasyRdf_Resource
     */
    public function resourcesMatching($property, $value=null)
    {
        $this->checkSinglePropertyParam($property, $inverse);
        $this->checkValueParam($value);

        if ($inverse) {
            $index = $this->_revIndex;
        } else {
            $index = $this->_index;
        }

        $matched = array();
        foreach ($index as $subject => $props) {
            if (isset($index[$subject][$property])) {
                if (isset($value)) {
                    foreach ($this->_index[$subject][$property] as $v) {
                        if ($v['type'] == $value['type'] and
                            $v['value'] == $value['value']) {
                            $matched[] = $this->resource($subject);
                            break;
                        }
                    }
                } else {
                    $matched[] = $this->resource($subject);
                }
            }
        }
        return $matched;
    }

    /** Get the URI of the graph
     *
     * @return string The URI of the graph
     */
    public function getUri()
    {
        return $this->_uri;
    }

    /** Check that a URI/resource parameter is valid, and convert it to a string
     *  @ignore
     */
    protected function checkResourceParam(&$resource, $allowNull=false)
    {
        if ($allowNull == true) {
            if ($resource === null) {
                if ($this->_uri) {
                    $resource = $this->_uri;
                } else {
                    return;
                }
            }
        } elseif ($resource === null) {
            throw new InvalidArgumentException(
                "\$resource cannot be null"
            );
        }

        if (is_object($resource) and $resource instanceof EasyRdf_Resource) {
            $resource = $resource->getUri();
        } elseif (is_object($resource) and $resource instanceof EasyRdf_ParsedUri) {
            $resource = strval($resource);
        } elseif (is_string($resource)) {
            if ($resource == '') {
                throw new InvalidArgumentException(
                    "\$resource cannot be an empty string"
                );
            } elseif (preg_match("|^<(.+)>$|", $resource, $matches)) {
                $resource = $matches[1];
            } else {
                $resource = EasyRdf_Namespace::expand($resource);
            }
        } else {
            throw new InvalidArgumentException(
                "\$resource should be a string or an EasyRdf_Resource"
            );
        }
    }

    /** Check that a single URI/property parameter (not a property path)
     *  is valid, and expand it if required
     *  @ignore
     */
    protected function checkSinglePropertyParam(&$property, &$inverse)
    {
        if (is_object($property) and $property instanceof EasyRdf_Resource) {
            $property = $property->getUri();
        } elseif (is_object($property) and $property instanceof EasyRdf_ParsedUri) {
            $property = strval($property);
        } elseif (is_string($property)) {
            if ($property == '') {
                throw new InvalidArgumentException(
                    "\$property cannot be an empty string"
                );
            } elseif (substr($property, 0, 1) == '^') {
                $inverse = true;
                $property = EasyRdf_Namespace::expand(substr($property, 1));
            } elseif (substr($property, 0, 2) == '_:') {
                throw new InvalidArgumentException(
                    "\$property cannot be a blank node"
                );
            } else {
                $inverse = false;
                $property = EasyRdf_Namespace::expand($property);
            }
        }

        if ($property === null or !is_string($property)) {
            throw new InvalidArgumentException(
                "\$property should be a string or EasyRdf_Resource and cannot be null"
            );
        }
    }

    /** Check that a value parameter is valid, and convert it to an associative array if needed
     *  @ignore
     */
    protected function checkValueParam(&$value)
    {
        if (isset($value)) {
            if (is_object($value)) {
                if (method_exists($value, 'toArray')) {
                    $value = $value->toArray();
                } else {
                    throw new InvalidArgumentException(
                        "\$value should respond to the method toArray()"
                    );
                }
            } elseif (is_array($value)) {
                if (!isset($value['type'])) {
                    throw new InvalidArgumentException(
                        "\$value is missing a 'type' key"
                    );
                }

                if (!isset($value['value'])) {
                    throw new InvalidArgumentException(
                        "\$value is missing a 'value' key"
                    );
                }

                // Fix ordering and remove unknown keys
                $value = array(
                    'type' => strval($value['type']),
                    'value' => strval($value['value']),
                    'lang' => isset($value['lang']) ? strval($value['lang']) : null,
                    'datatype' => isset($value['datatype']) ? strval($value['datatype']) : null
                );
            } else {
                $value = array(
                    'type' => 'literal',
                    'value' => strval($value),
                    'datatype' => EasyRdf_Literal::getDatatypeForValue($value)
                );
            }
            if (!in_array($value['type'], array('uri', 'bnode', 'literal'), true)) {
                throw new InvalidArgumentException(
                    "\$value does not have a valid type (".$value['type'].")"
                );
            }
            if (empty($value['datatype']))
                unset($value['datatype']);
            if (empty($value['lang']))
                unset($value['lang']);
            if (isset($value['lang']) and isset($value['datatype'])) {
                throw new InvalidArgumentException(
                    "\$value cannot have both and language and a datatype"
                );
            }
        }
    }

    /** Get a single value for a property of a resource
     *
     * If multiple values are set for a property then the value returned
     * may be arbitrary.
     *
     * If $property is an array, then the first item in the array that matches
     * a property that exists is returned.
     *
     * This method will return null if the property does not exist.
     *
     * @param  string    $resource       The URI of the resource (e.g. http://example.com/joe#me)
     * @param  string    $propertyPath   A valid property path
     * @param  string    $type           The type of value to filter by (e.g. literal or resource)
     * @param  string    $lang           The language to filter by (e.g. en)
     * @return mixed                     A value associated with the property
     */
    public function get($resource, $propertyPath, $type=null, $lang=null)
    {
        $this->checkResourceParam($resource);

        if (is_object($propertyPath) and $propertyPath instanceof EasyRdf_Resource) {
            return $this->getSingleProperty($resource, $propertyPath->getUri(), $type, $lang);
        } elseif (is_string($propertyPath) and preg_match('|^(\^?)<(.+)>|', $propertyPath, $matches)) {
            return $this->getSingleProperty($resource, "$matches[1]$matches[2]", $type, $lang);
        } elseif ($propertyPath === null or !is_string($propertyPath)) {
            throw new InvalidArgumentException(
                "\$propertyPath should be a string or EasyRdf_Resource and cannot be null"
            );
        } elseif ($propertyPath === '') {
            throw new InvalidArgumentException(
                "\$propertyPath cannot be an empty string"
            );
        }

        // Loop through each component in the path
        foreach (explode('/', $propertyPath) as $part) {
            // Stop if we come to a literal
            if ($resource instanceof EasyRdf_Literal)
                return NULL;

            // Try each of the alternative paths
            foreach (explode('|', $part) as $p) {
                $res = $this->getSingleProperty($resource, $p, $type, $lang);
                if ($res) break;
            }

            // Stop if nothing was found
            $resource = $res;
            if (!$resource)
                break;
        }

        return $resource;
    }

    /** Get a single value for a property of a resource
     *
     * @param  string    $resource The URI of the resource (e.g. http://example.com/joe#me)
     * @param  string    $property The name of the property (e.g. foaf:name)
     * @param  string    $type     The type of value to filter by (e.g. literal or resource)
     * @param  string    $lang     The language to filter by (e.g. en)
     * @return mixed               A value associated with the property
     *
     * @ignore
     */
    protected function getSingleProperty($resource, $property, $type=null, $lang=null)
    {
        $this->checkResourceParam($resource);
        $this->checkSinglePropertyParam($property, $inverse);

        // Get an array of values for the property
        $values = $this->propertyValuesArray($resource, $property, $inverse);
        if (!isset($values)) {
            return null;
        }

        // Filter the results
        $result = null;
        if ($type) {
            foreach ($values as $value) {
                if ($type == 'literal' and $value['type'] == 'literal') {
                    if ($lang == null or (isset($value['lang']) and $value['lang'] == $lang)) {
                        $result = $value;
                        break;
                    }
                } elseif ($type == 'resource') {
                    if ($value['type'] == 'uri' or $value['type'] == 'bnode') {
                        $result = $value;
                        break;
                    }
                }
            }
        } else {
            $result = $values[0];
        }

        // Convert the internal data structure into a PHP object
        return $this->arrayToObject($result);
    }

    /** Get a single literal value for a property of a resource
     *
     * If multiple values are set for a property then the value returned
     * may be arbitrary.
     *
     * This method will return null if there is not literal value for the
     * property.
     *
     * @param  string       $resource The URI of the resource (e.g. http://example.com/joe#me)
     * @param  string|array $property The name of the property (e.g. foaf:name)
     * @param  string       $lang     The language to filter by (e.g. en)
     * @return object EasyRdf_Literal Literal value associated with the property
     */
    public function getLiteral($resource, $property, $lang=null)
    {
        return $this->get($resource, $property, 'literal', $lang);
    }

    /** Get a single resource value for a property of a resource
     *
     * If multiple values are set for a property then the value returned
     * may be arbitrary.
     *
     * This method will return null if there is not resource for the
     * property.
     *
     * @param  string       $resource The URI of the resource (e.g. http://example.com/joe#me)
     * @param  string|array $property The name of the property (e.g. foaf:name)
     * @return object EasyRdf_Resource Resource associated with the property
     */
    public function getResource($resource, $property)
    {
        return $this->get($resource, $property, 'resource');
    }

    /** Return all the values for a particular property of a resource
     *  @ignore
     */
    protected function propertyValuesArray($resource, $property, $inverse=false)
    {
        // Is an inverse property being requested?
        if ($inverse) {
            if (isset($this->_revIndex[$resource]))
                $properties = $this->_revIndex[$resource];
        } else {
            if (isset($this->_index[$resource]))
                $properties = $this->_index[$resource];
        }

        if (isset($properties[$property])) {
            return $properties[$property];
        } else {
            return null;
        }
    }

    /** Get an EasyRdf_Resource or EasyRdf_Literal object from an associative array.
     *  @ignore
     */
    protected function arrayToObject($data)
    {
        if ($data) {
            if ($data['type'] == 'uri' or $data['type'] == 'bnode') {
                return $this->resource($data['value']);
            } else {
                return EasyRdf_Literal::create($data);
            }
        } else {
            return null;
        }
    }

    /** Get all values for a property path
     *
     * This method will return an empty array if the property does not exist.
     *
     * @param  string  $resource      The URI of the resource (e.g. http://example.com/joe#me)
     * @param  string  $propertyPath  A valid property path
     * @param  string  $type          The type of value to filter by (e.g. literal)
     * @param  string  $lang          The language to filter by (e.g. en)
     * @return array                  An array of values associated with the property
     */
    public function all($resource, $propertyPath, $type=null, $lang=null)
    {
        $this->checkResourceParam($resource);

        if (is_object($propertyPath) and $propertyPath instanceof EasyRdf_Resource) {
            return $this->allForSingleProperty($resource, $propertyPath->getUri(), $type, $lang);
        } elseif (is_string($propertyPath) and preg_match('|^(\^?)<(.+)>|', $propertyPath, $matches)) {
            return $this->allForSingleProperty($resource, "$matches[1]$matches[2]", $type, $lang);
        } elseif ($propertyPath === null or !is_string($propertyPath)) {
            throw new InvalidArgumentException(
                "\$propertyPath should be a string or EasyRdf_Resource and cannot be null"
            );
        } elseif ($propertyPath === '') {
            throw new InvalidArgumentException(
                "\$propertyPath cannot be an empty string"
            );
        }

        $objects = array($resource);

        // Loop through each component in the path
        foreach (explode('/', $propertyPath) as $part) {

            $results = array();
            foreach (explode('|', $part) as $p) {
                foreach ($objects as $o) {
                    // Ignore literals found earlier in path
                    if ($o instanceof EasyRdf_Literal)
                        continue;

                    $results = array_merge(
                        $results, $this->allForSingleProperty($o, $p, $type, $lang)
                    );
                }
            }

            // Stop if we don't have anything
            if (empty($objects))
                break;

            // Use the results as the input to the next iteration
            $objects = $results;
        }

        return $objects;
    }

    /** Get all values for a single property of a resource
     *
     * @param  string  $resource The URI of the resource (e.g. http://example.com/joe#me)
     * @param  string  $property The name of the property (e.g. foaf:name)
     * @param  string  $type     The type of value to filter by (e.g. literal)
     * @param  string  $lang     The language to filter by (e.g. en)
     * @return array             An array of values associated with the property
     *
     * @ignore
     */
    protected function allForSingleProperty($resource, $property, $type=null, $lang=null)
    {
        $this->checkResourceParam($resource);
        $this->checkSinglePropertyParam($property, $inverse);

        // Get an array of values for the property
        $values = $this->propertyValuesArray($resource, $property, $inverse);
        if (!isset($values)) {
            return array();
        }

        $objects = array();
        if ($type) {
            foreach ($values as $value) {
                if ($type == 'literal' and $value['type'] == 'literal') {
                    if ($lang == null or (isset($value['lang']) and $value['lang'] == $lang))
                        $objects[] = $this->arrayToObject($value);
                } elseif ($type == 'resource') {
                    if ($value['type'] == 'uri' or $value['type'] == 'bnode')
                        $objects[] = $this->arrayToObject($value);
                }
            }
        } else {
            foreach ($values as $value) {
                $objects[] = $this->arrayToObject($value);
            }
        }
        return $objects;
    }

    /** Get all literal values for a property of a resource
     *
     * This method will return an empty array if the resource does not
     * has any literal values for that property.
     *
     * @param  string  $resource The URI of the resource (e.g. http://example.com/joe#me)
     * @param  string  $property The name of the property (e.g. foaf:name)
     * @param  string  $lang     The language to filter by (e.g. en)
     * @return array             An array of values associated with the property
     */
    public function allLiterals($resource, $property, $lang=null)
    {
        return $this->all($resource, $property, 'literal', $lang);
    }

    /** Get all resources for a property of a resource
     *
     * This method will return an empty array if the resource does not
     * has any resources for that property.
     *
     * @param  string  $resource The URI of the resource (e.g. http://example.com/joe#me)
     * @param  string  $property The name of the property (e.g. foaf:name)
     * @return array             An array of values associated with the property
     */
    public function allResources($resource, $property)
    {
        return $this->all($resource, $property, 'resource');
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
        return $this->all($type, '^rdf:type');
    }

    /** Count all values for a property of a resource
     *
     * @param  string  $resource The URI of the resource (e.g. http://example.com/joe#me)
     * @param  string  $property The name of the property (e.g. foaf:name)
     * @param  string  $type     The type of value to filter by (e.g. literal)
     * @param  string  $lang     The language to filter by (e.g. en)
     * @return integer           The number of values for this property
     */
    public function count($resource, $property, $type=null, $lang=null)
    {
        return count($this->all($resource, $property, $type, $lang));
    }

    /** Concatenate all values for a property of a resource into a string.
     *
     * The default is to join the values together with a space character.
     * This method will return an empty string if the property does not exist.
     *
     * @param  string  $property The name of the property (e.g. foaf:name)
     * @param  string  $glue     The string to glue the values together with.
     * @param  string  $lang     The language to filter by (e.g. en)
     * @return string            Concatenation of all the values.
     */
    public function join($resource, $property, $glue=' ', $lang=null)
    {
        return join($glue, $this->all($resource, $property, 'literal', $lang));
    }

    /** Add data to the graph
     *
     * The resource can either be a resource or the URI of a resource.
     *
     * Example:
     *   $graph->add("http://www.example.com", 'dc:title', 'Title of Page');
     *
     * @param  mixed $resource   The resource to add data to
     * @param  mixed $property   The property name
     * @param  mixed $value      The new value for the property
     * @return integer           The number of values added (1 or 0)
     */
    public function add($resource, $property, $value)
    {
        $this->checkResourceParam($resource);
        $this->checkSinglePropertyParam($property, $inverse);
        $this->checkValueParam($value);

        // No value given?
        if ($value === null)
            return 0;

        // Check that the value doesn't already exist
        if (isset($this->_index[$resource][$property])) {
            foreach ($this->_index[$resource][$property] as $v) {
                if ($v == $value)
                    return 0;
            }
        }
        $this->_index[$resource][$property][] = $value;

        // Add to the reverse index if it is a resource
        if ($value['type'] == 'uri' or $value['type'] == 'bnode') {
            $uri = $value['value'];
            $this->_revIndex[$uri][$property][] = array(
                'type' => substr($resource, 0, 2) == '_:' ? 'bnode' : 'uri',
                'value' => $resource
            );
        }

        // Success
        return 1;
    }

    /** Add a literal value as a property of a resource
     *
     * The resource can either be a resource or the URI of a resource.
     * The value can either be a single value or an array of values.
     *
     * Example:
     *   $graph->add("http://www.example.com", 'dc:title', 'Title of Page');
     *
     * @param  mixed  $resource  The resource to add data to
     * @param  mixed  $property  The property name
     * @param  mixed  $value     The value or values for the property
     * @param  string $lang      The language of the literal
     * @return integer           The number of values added
     */
    public function addLiteral($resource, $property, $value, $lang=null)
    {
        $this->checkResourceParam($resource);
        $this->checkSinglePropertyParam($property, $inverse);

        if (is_array($value)) {
            $added = 0;
            foreach ($value as $v) {
               $added += $this->addLiteral($resource, $property, $v, $lang);
            }
            return $added;
        } else {
            if ($lang) {
                $value = array(
                    'type' => 'literal',
                    'value' => $value,
                    'lang' => $lang
                );
            } else {
                $value = array(
                    'type' => 'literal',
                    'value' => $value,
                    'datatype' => EasyRdf_Literal::getDatatypeForValue($value)
                );
                if (empty($value['datatype']))
                    unset($value['datatype']);
            }
            return $this->add($resource, $property, $value);
        }
    }

    /** Add a resource as a property of another resource
     *
     * The resource can either be a resource or the URI of a resource.
     *
     * Example:
     *   $graph->add("http://example.com/bob", 'foaf:knows', 'http://example.com/alice');
     *
     * @param  mixed $resource   The resource to add data to
     * @param  mixed $property   The property name
     * @param  mixed $resource2  The resource to be value of the property
     * @return integer           The number of values added
     */
    public function addResource($resource, $property, $resource2)
    {
        $this->checkResourceParam($resource);
        $this->checkSinglePropertyParam($property, $inverse);
        $this->checkResourceParam($resource2);

        return $this->add(
            $resource, $property, array(
                'type' => substr($resource2, 0, 2) == '_:' ? 'bnode' : 'uri',
                'value' => $resource2
            )
        );
    }

    /** Set a value for a property
     *
     * The new value will replace the existing values for the property.
     *
     * @param  string  $resource The resource to set the property on
     * @param  string  $property The name of the property (e.g. foaf:name)
     * @param  mixed   $value    The value for the property
     * @return integer           The number of values added (1 or 0)
     */
    public function set($resource, $property, $value)
    {
        $this->checkResourceParam($resource);
        $this->checkSinglePropertyParam($property, $inverse);
        $this->checkValueParam($value);

        // Delete the old values
        $this->delete($resource, $property);

        // Add the new values
        return $this->add($resource, $property, $value);
    }

    /** Delete a property (or optionally just a specific value)
     *
     * @param  string  $property The name of the property (e.g. foaf:name)
     * @param  object  $value The value to delete (null to delete all values)
     * @return integer The number of values deleted
     */
    public function delete($resource, $property, $value=null)
    {
        $this->checkResourceParam($resource);
        $this->checkSinglePropertyParam($property, $inverse);
        $this->checkValueParam($value);

        $count = 0;
        $property = EasyRdf_Namespace::expand($property);
        if (isset($this->_index[$resource][$property])) {
            foreach ($this->_index[$resource][$property] as $k => $v) {
                if (!$value or $v == $value) {
                    unset($this->_index[$resource][$property][$k]);
                    $count++;
                    if ($v['type'] == 'uri' or $v['type'] == 'bnode') {
                        $this->deleteInverse($v['value'], $property, $resource);
                    }
                }
            }

            // Clean up the indexes - remove empty properties and resources
            if ($count) {
                if (count($this->_index[$resource][$property]) == 0)
                    unset($this->_index[$resource][$property]);
                if (count($this->_index[$resource]) == 0)
                    unset($this->_index[$resource]);
            }
        }

        return $count;
    }

    /** Delete a resource from a property of another resource
     *
     * The resource can either be a resource or the URI of a resource.
     *
     * Example:
     *   $graph->delete("http://example.com/bob", 'foaf:knows', 'http://example.com/alice');
     *
     * @param  mixed $resource   The resource to delete data from
     * @param  mixed $property   The property name
     * @param  mixed $resource2  The resource value of the property to be deleted
     */
    public function deleteResource($resource, $property, $resource2)
    {
        $this->checkResourceParam($resource);
        $this->checkSinglePropertyParam($property, $inverse);
        $this->checkResourceParam($resource2);

        return $this->delete(
            $resource, $property, array(
                'type' => substr($resource2, 0, 2) == '_:' ? 'bnode' : 'uri',
                'value' => $resource2
            )
        );
    }

    /** Delete a literal value from a property of a resource
     *
     * Example:
     *   $graph->delete("http://www.example.com", 'dc:title', 'Title of Page');
     *
     * @param  mixed  $resource  The resource to add data to
     * @param  mixed  $property  The property name
     * @param  mixed  $value     The value of the property
     * @param  string $lang      The language of the literal
     */
    public function deleteLiteral($resource, $property, $value, $lang=null)
    {
        $this->checkResourceParam($resource);
        $this->checkSinglePropertyParam($property, $inverse);
        $this->checkValueParam($value);

        if ($lang) {
            $value['lang'] = $lang;
        }

        return $this->delete($resource, $property, $value);
    }

    /** This function is for internal use only.
     *
     * Deletes an inverse property from a resource.
     *
     * @ignore
     */
    protected function deleteInverse($resource, $property, $value)
    {
        if (isset($this->_revIndex[$resource])) {
            foreach ($this->_revIndex[$resource][$property] as $k => $v) {
                if ($v['value'] === $value) {
                    unset($this->_revIndex[$resource][$property][$k]);
                }
            }
            if (count($this->_revIndex[$resource][$property]) == 0)
                unset($this->_revIndex[$resource][$property]);
            if (count($this->_revIndex[$resource]) == 0)
                unset($this->_revIndex[$resource]);
        }
    }

    /** Check if the graph contains any statements
     *
     * @return boolean True if the graph contains no statements
     */
    public function isEmpty()
    {
        return count($this->_index) == 0;
    }

    /** Get a list of all the shortened property names (qnames) for a resource.
     *
     * This method will return an empty array if the resource has no properties.
     *
     * @return array            Array of shortened URIs
     */
    public function properties($resource)
    {
        $this->checkResourceParam($resource);

        $properties = array();
        if (isset($this->_index[$resource])) {
            foreach ($this->_index[$resource] as $property => $value) {
                $short = EasyRdf_Namespace::shorten($property);
                if ($short)
                    $properties[] = $short;
            }
        }
        return $properties;
    }

    /** Get a list of the full URIs for the properties of a resource.
     *
     * This method will return an empty array if the resource has no properties.
     *
     * @return array            Array of full URIs
     */
    public function propertyUris($resource)
    {
        $this->checkResourceParam($resource);

        if (isset($this->_index[$resource])) {
            return array_keys($this->_index[$resource]);
        } else {
            return array();
        }
    }

    /** Get a list of the full URIs for the properties that point to a resource.
     *
     * @return array   Array of full property URIs
     */
    public function reversePropertyUris($resource)
    {
        $this->checkResourceParam($resource);

        if (isset($this->_revIndex[$resource])) {
            return array_keys($this->_revIndex[$resource]);
        } else {
            return array();
        }
    }

    /** Check to see if a property exists for a resource.
     *
     * This method will return true if the property exists.
     *
     * @param  string  $property The name of the property (e.g. foaf:gender)
     * @return boolean           True if value the property exists.
     */
    public function hasProperty($resource, $property)
    {
        $this->checkResourceParam($resource);
        $this->checkSinglePropertyParam($property, $inverse);

        if (!$inverse) {
            if (isset($this->_index[$resource][$property]))
                return true;
        } else {
            if (isset($this->_revIndex[$resource][$property]))
                return true;
        }

        return false;
    }

    /** Serialise the graph into RDF
     *
     * The $format parameter can be an EasyRdf_Format object, a
     * format name, a mime type or a file extension.
     *
     * Example:
     *   $turtle = $graph->serialise('turtle');
     *
     * @param  mixed  $format  The format to serialise to
     * @return mixed  The serialised graph
     */
    public function serialise($format)
    {
        if (!$format instanceof EasyRdf_Format) {
            $format = EasyRdf_Format::getFormat($format);
        }
        $serialiser = $format->newSerialiser();
        return $serialiser->serialise($this, $format->getName());
    }

    /** Return a human readable view of all the resources in the graph
     *
     * This method is intended to be a debugging aid and will
     * return a pretty-print view of all the resources and their
     * properties.
     *
     * @param  boolean  $html  Set to true to format the dump using HTML
     * @return string
     */
    public function dump($html=true)
    {
        $result = '';
        if ($html) {
            $result .= "<div style='font-family:arial; font-weight: bold; padding:0.5em; ".
                   "color: black; background-color:lightgrey;border:dashed 1px grey;'>".
                   "Graph: ". $this->_uri . "</div>\n";
        } else {
            $result .= "Graph: ". $this->_uri . "\n";
        }

        foreach ($this->_index as $resource => $properties) {
            $result .= $this->dumpResource($resource, $html);
        }
        return $result;
    }

    /** Return a human readable view of the resource and its properties
     *
     * This method is intended to be a debugging aid and will
     * print a resource and its properties.
     *
     * @param  boolean  $html  Set to true to format the dump using HTML
     * @return string
     */
    public function dumpResource($resource, $html=true)
    {
        $this->checkResourceParam($resource, true);

        if (isset($this->_index[$resource])) {
            $properties = $this->_index[$resource];
        } else {
            return '';
        }

        $plist = array();
        foreach ($properties as $property => $values) {
            $olist = array();
            foreach ($values as $value) {
                if ($value['type'] == 'literal') {
                  $olist []= EasyRdf_Utils::dumpLiteralValue($value, $html, 'black');
                } else {
                  $olist []= EasyRdf_Utils::dumpResourceValue($value['value'], $html, 'blue');
                }
            }

            $pstr = EasyRdf_Namespace::shorten($property);
            if ($pstr == null)
                $pstr = $property;
            if ($html) {
                $plist []= "<span style='font-size:130%'>&rarr;</span> ".
                           "<span style='text-decoration:none;color:green'>".
                           htmlentities($pstr) . "</span> ".
                           "<span style='font-size:130%'>&rarr;</span> ".
                           join(", ", $olist);
            } else {
                $plist []= "  -> $pstr -> " . join(", ", $olist);
            }
        }

        if ($html) {
            return "<div id='".htmlentities($resource)."' " .
                   "style='font-family:arial; padding:0.5em; ".
                   "background-color:lightgrey;border:dashed 1px grey;'>\n".
                   "<div>".EasyRdf_Utils::dumpResourceValue($resource, true, 'blue')." ".
                   "<span style='font-size: 0.8em'>(".
                   $this->classForResource($resource).")</span></div>\n".
                   "<div style='padding-left: 3em'>\n".
                   "<div>".join("</div>\n<div>", $plist)."</div>".
                   "</div></div>\n";
        } else {
            return $resource." (".$this->classForResource($resource).")\n" .
                   join("\n", $plist) . "\n\n";
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
    public function type($resource=null)
    {
        $this->checkResourceParam($resource, true);

        if ($resource) {
            $type = $this->get($resource, 'rdf:type', 'resource');
            if ($type)
                return EasyRdf_Namespace::shorten($type);
        }

        return null;
    }

    /** Get the resource type of the graph as a EasyRdf_Resource
     *
     * If the graph has multiple types then the type returned
     * may be arbitrary.
     * This method will return null if the resource has no type.
     *
     * @return object EasyRdf_Resource  A type assocated with the resource
     */
    public function typeAsResource($resource=null)
    {
        $this->checkResourceParam($resource, true);

        if ($resource) {
            return $this->get($resource, 'rdf:type', 'resource');
        }

        return null;
    }

    /** Get a list of types for a resource.
     *
     * The types will each be a shortened URI as a string.
     * This method will return an empty array if the resource has no types.
     *
     * If $resource is null, then it will get the types for the URI of the graph.
     *
     * @return array All types assocated with the resource (e.g. foaf:Person)
     */
    public function types($resource=null)
    {
        $this->checkResourceParam($resource, true);

        $types = array();
        if ($resource) {
            foreach ($this->all($resource, 'rdf:type', 'resource') as $type) {
                $types[] = EasyRdf_Namespace::shorten($type);
            }
        }

        return $types;
    }

    /** Check if a resource is of the specified type
     *
     * @param  string  $type The type to check (e.g. foaf:Person)
     * @return boolean       True if resource is of specified type.
     */
    public function is_a($resource, $type)
    {
        $this->checkResourceParam($resource, true);

        $type = EasyRdf_Namespace::expand($type);
        foreach ($this->all($resource, 'rdf:type', 'resource') as $t) {
            if ($t->getUri() == $type) {
                return true;
            }
        }
        return false;
    }

    /** Add one or more rdf:type properties to a resource
     *
     * @param  string  $resource The resource to add the type to
     * @param  string  $type     The new type (e.g. foaf:Person)
     * @return integer           The number of types added
     */
    public function addType($resource, $types)
    {
        $this->checkResourceParam($resource, true);

        if (!is_array($types))
            $types = array($types);

        $count = 0;
        foreach ($types as $type) {
            $type = EasyRdf_Namespace::expand($type);
            $count += $this->add($resource, 'rdf:type', array('type' => 'uri', 'value' => $type));
        }

        return $count;
    }

    /** Change the rdf:type property for a resource
     *
     * Note that if the resource object has already previously
     * been created, then the PHP class of the resource will not change.
     *
     * @param  string  $resource The resource to change the type of
     * @param  string  $type     The new type (e.g. foaf:Person)
     * @return integer           The number of types added
     */
    public function setType($resource, $type)
    {
        $this->checkResourceParam($resource, true);

        $this->delete($resource, 'rdf:type');
        return $this->addType($resource, $type);
    }

    /** Get a human readable label for a resource
     *
     * This method will check a number of properties for a resource
     * (in the order: skos:prefLabel, rdfs:label, foaf:name, dc:title)
     * and return an approriate first that is available. If no label
     * is available then it will return null.
     *
     * @return string A label for the resource.
     */
    public function label($resource=null, $lang=null)
    {
        $this->checkResourceParam($resource, true);

        if ($resource) {
            return $this->get(
                $resource,
                'skos:prefLabel|rdfs:label|foaf:name|dc:title|dc11:title',
                'literal',
                $lang
            );
        } else {
            return null;
        }
    }

    /** Get the primary topic of the graph
     *
     * @return EasyRdf_Resource The primary topic of the document.
     */
    public function primaryTopic($resource=null)
    {
        $this->checkResourceParam($resource, true);

        if ($resource) {
            return $this->get(
                $resource, 'foaf:primaryTopic|^foaf:isPrimaryTopicOf', 'resource'
            );
        } else {
            return null;
        }
    }

    /** Returns the graph as a RDF/PHP associative array
     *
     * @return array The contents of the graph as an array.
     */
    public function toArray()
    {
        return $this->_index;
    }

    /** Calculates the number of triples in the graph
     *
     * @return integer The number of triples in the graph.
     */
    public function countTriples()
    {
        $count = 0;
        foreach ($this->_index as $resource) {
            foreach ($resource as $property => $values) {
                $count += count($values);
            }
        }
        return $count;
    }

    /** Magic method to return URI of resource when casted to string
     *
     * @return string The URI of the resource
     */
    public function __toString()
    {
        return $this->_uri == null ? '' : $this->_uri;
    }

    /** Magic method to get a property of the graph
     *
     * Note that only properties in the default namespace can be accessed in this way.
     *
     * Example:
     *   $value = $graph->title;
     *
     * @see EasyRdf_Namespace::setDefault()
     * @param  string $name The name of the property
     * @return string       A single value for the named property
     */
    public function __get($name)
    {
        return $this->get($this->_uri, $name);
    }

    /** Magic method to set the value for a property of the graph
     *
     * Note that only properties in the default namespace can be accessed in this way.
     *
     * Example:
     *   $graph->title = 'Title';
     *
     * @see EasyRdf_Namespace::setDefault()
     * @param  string $name The name of the property
     * @param  string $value The value for the property
     */
    public function __set($name, $value)
    {
        return $this->set($this->_uri, $name, $value);
    }

    /** Magic method to check if a property exists
     *
     * Note that only properties in the default namespace can be accessed in this way.
     *
     * Example:
     *   if (isset($graph->title)) { blah(); }
     *
     * @see EasyRdf_Namespace::setDefault()
     * @param string $name The name of the property
     */
    public function __isset($name)
    {
        return $this->hasProperty($this->_uri, $name);
    }

    /** Magic method to delete a property of the graph
     *
     * Note that only properties in the default namespace can be accessed in this way.
     *
     * Example:
     *   unset($graph->title);
     *
     * @see EasyRdf_Namespace::setDefault()
     * @param string $name The name of the property
     */
    public function __unset($name)
    {
        return $this->delete($this->_uri, $name);
    }
}
