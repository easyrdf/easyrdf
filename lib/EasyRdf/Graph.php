<?php

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009-2011 Nicholas J Humfrey.  All rights reserved.
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

    private $_index = array();
    private $_revIndex = array();


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
     * @param  mixed   $types  RDF type of a new resource (e.g. foaf:Person)
     * @return object EasyRdf_Resource
     */
    public function resource($uri, $types=array())
    {
        if (!is_string($uri) or $uri == null or $uri == '') {
            throw new InvalidArgumentException(
                "\$uri should be a string and cannot be null or empty"
            );
        }

        // Expand the URI if it is shortened
        $uri = EasyRdf_Namespace::expand($uri);

        // Add the types
        $this->addType($uri, $types);

        // Create resource object if it doesn't already exist
        if (!array_key_exists($uri, $this->_resources)) {
            $resClass = $this->classForResource($uri);
            $this->_resources[$uri] = new $resClass($this, $uri);
        }

        return $this->_resources[$uri];
    }

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

    /** Get or create a resource stored in a graph
     *
     * If the resource did not previously exist, then a new resource will
     * be created. If you provide an RDF type and that type is registered
     * with the EasyRdf_TypeMapper, then the resource will be an instance
     * of the class registered.
     *
     * @param  string $baseUri      The base URI
     * @param  string $referenceUri The URI to resolve
     * @param  mixed   $types  RDF type of a new resource (e.g. foaf:Person)
     * @return object The newly resolved URI as an EasyRdf_Resource
     */
    public function resolveResource($baseUri, $referenceUri, $types = array())
    {
        $uri = EasyRdf_Utils::resolveUriReference($baseUri, $referenceUri);
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
     * @return object EasyRdf_Resource The new blank node
     */
    public function newBNode($types=array())
    {
        return $this->resource($this->newBNodeId(), $types);
    }

    public function newBNodeId()
    {
        return "_:eid".(++$this->_bNodeCount);
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
     * @return array Array of EasyRdf_Resource
     */
    public function resources()
    {
        # FIXME: could this be more efficient?
        foreach ($this->_index as $subject => $properties) {
            $this->resource($subject);
        }

        foreach ($this->_revIndex as $object => $properties) {
            $this->resource($object);
        }

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
     * @return array Array of EasyRdf_Resource
     */
    public function resourcesMatching($property, $value)
    {
        $this->checkPropertyParam($property, $inverse);
        $this->checkValueParam($value);

        $matched = array();
        foreach ($this->_index as $subject => $props) {
            if (isset($this->_index[$subject][$property])) {
                foreach ($this->_index[$subject][$property] as $v) {
                    if ($v['type'] == $value['type'] and $v['value'] == $value['value'])
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

    protected function checkResourceParam(&$resource, $allowNull=false)
    {
        if ($allowNull == true) {
            if ($resource == null) {
                if ($this->_uri) {
                    $resource = $this->_uri;
                } else {
                    return;
                }
            }
        } else if ($resource == null or $resource == '') {
            throw new InvalidArgumentException(
                "\$resource cannot be null or empty"
            );
        }

        if (is_object($resource) and $resource instanceof EasyRdf_Resource) {
            $resource = strval($resource);
        } else if (is_string($resource)) {
            $resource = EasyRdf_Namespace::expand($resource);
        } else {
            throw new InvalidArgumentException(
                "\$resource should a string or an EasyRdf_Resource"
            );
        }
    }

    protected function checkPropertyParam(&$property, &$inverse)
    {
        if (is_object($property) and $property instanceof EasyRdf_Resource) {
            $property = strval($property);
        } else if (is_string($property)) {
            if (substr($property, 0, 1) == '^') {
                $inverse = true;
                $property = EasyRdf_Namespace::expand(substr($property, 1));
            } else {
                $inverse = false;
                $property = EasyRdf_Namespace::expand($property);
            }
        }

        if (!is_string($property) or $property == null or $property == '') {
            throw new InvalidArgumentException(
                "\$property should a string or EasyRdf_Resource and cannot be null or empty"
            );
        }
    }

    protected function checkValueParam(&$value)
    {
        if ($value) {
            if (is_object($value)) {
                if (method_exists($value, 'toRdfPhp')) {
                    $value = $value->toRdfPhp();
                } else {
                    throw new InvalidArgumentException(
                        "\$value should response to toRdfPhp()"
                    );
                }
            } else if (!is_array($value)) {
                $value = array('type' => 'literal', 'value' => $value);
            }
        }
    }

    public function get($resource, $property, $type=null, $lang=null)
    {
        if (is_array($property)) {
            foreach ($property as $p) {
                $value = $this->get($resource, $p, $type, $lang);
                if ($value)
                    return $value;
            }
            return null;
        }

        $this->checkResourceParam($resource);
        $this->checkPropertyParam($property, $inverse);

        // Get an array of values for the property
        $values = $this->propertyValuesArray($resource, $property, $inverse);
        if (!isset($values)) {
            return null;
        }

        # FIXME: better variable name?
        $data = null;
        if ($type) {
            foreach ($values as $value) {
                if ($value['type'] == $type) {
                    if ($lang == null or (isset($value['lang']) and $value['lang'] == $lang)) {
                        $data = $value;
                        break;
                    }
                }
            }
        } else {
            $data = $values[0];
        }

        return $this->arrayToObject($data);
    }

    public function getLiteral($resource, $property, $lang=null)
    {
        return $this->get($resource, $property, 'literal', $lang);
    }

    # FIXME: implement this
//     public function getResource($resource, $property)
//     {
//         return $this->get($resource, $property, 'resource', $lang);
//     }

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

    # FIXME: better function name?
    protected function arrayToObject($data)
    {
        if ($data) {
            if ($data['type'] == 'uri' or $data['type'] == 'bnode') {
                return $this->resource($data['value']);
            } else {
                return new EasyRdf_Literal($data);
            }
        } else {
            return null;
        }
    }


    public function all($resource, $property, $type=null, $lang=null)
    {
        $this->checkResourceParam($resource);
        $this->checkPropertyParam($property, $inverse);
        $this->checkValueParam($value);

        // Get an array of values for the property
        $values = $this->propertyValuesArray($resource, $property, $inverse);
        if (!isset($values)) {
            return array();
        }

        $objects = array();
        if ($type) {
            foreach ($values as $value) {
                if ($value['type'] == $type and ($lang == null or (isset($value['lang']) and $value['lang'] == $lang)))
                    $objects[] = $this->arrayToObject($value);
            }
        } else {
            foreach ($values as $value) {
                $objects[] = $this->arrayToObject($value);
            }
        }
        return $objects;
    }

    public function allLiterals($resource, $property, $lang=null)
    {
        return $this->all($resource, $property, 'literal', $lang);
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

    public function join($resource, $property, $glue=' ', $lang=null)
    {
        return join($glue, $this->all($resource, $property, 'literal', $lang));
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
     * Example:
     *   $graph->add("http://www.example.com", 'dc:title', 'Title of Page');
     *
     * @param  mixed $resource   The resource to add data to
     * @param  mixed $properties The properties or property names
     * @param  mixed $value      The new value for the property
     */
    public function add($resource, $property, $value)
    {
        $this->checkResourceParam($resource);
        $this->checkPropertyParam($property, $inverse);
        $this->checkValueParam($value);

        // No value given?
        if ($value === null)
            return;

        # FIXME: re-factor this back into a $this->matches() function?
        // Check that the value doesn't already exist
        if (isset($this->_index[$resource][$property])) {
            foreach ($this->_index[$resource][$property] as $v) {
                if ($v == $value)
                    return;
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
    }

    # FIXME: better name for this method?
    public function addLiteral($resource, $property, $value)
    {
        if (is_string($value)) {
            $value = array('type' => 'literal', 'value' => $value);
        } else if (is_array($value)) {
            foreach ($value as $v) {
                $this->addLiteral($resource, $property, $v);
            }
            return;
        }

        return $this->add($resource, $property, $value);
    }

    # FIXME: better name for this method?
    public function addResource($resource, $property, $resource2)
    {
        $this->checkResourceParam($resource);
        $this->checkPropertyParam($property, $inverse);
        $this->checkResourceParam($resource2);

        return $this->add(
            $resource, $property, array(
                'type' => substr($resource2, 0, 2) == '_:' ? 'bnode' : 'uri',
                'value' => $resource2
            )
        );
    }

    /** Set value(s) for a property
     *
     * The new value(s) will replace the existing values for the property.
     * The name of the property should be a string.
     * If you set a property to null or an empty array, then the property
     * will be deleted.
     *
     * @param  string  $property The name of the property (e.g. foaf:name)
     * @param  mixed   $values   The value(s) for the property.
     * @return array             Array of new values for this property.
     */
    public function set($resource, $property, $value)
    {
        $this->checkResourceParam($resource);
        $this->checkPropertyParam($property, $inverse);
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
     * @return null
     */
    public function delete($resource, $property, $value=null)
    {
        $this->checkResourceParam($resource);
        $this->checkPropertyParam($property, $inverse);
        $this->checkValueParam($value);

        $property = EasyRdf_Namespace::expand($property);
        if (isset($this->_index[$resource][$property])) {
            foreach ($this->_index[$resource][$property] as $k => $v) {
                if (!$value or $v == $value) {
                    unset($this->_index[$resource][$property][$k]);
                    if ($v['type'] == 'uri' or $v['type'] == 'bnode') {
                        $this->deleteInverse($v['value'], $property, $resource);
                    }
                }
            }
            if (count($this->_index[$resource][$property]) == 0) {
                unset($this->_index[$resource][$property]);
            }
        }

        return null;
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
            if (count($this->_revIndex[$resource][$property]) == 0) {
                unset($this->_revIndex[$resource][$property]);
            }
        }
    }

    public function isEmpty()
    {
        return count($this->_index) == 0;
    }

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

    /** Get a list of the full URIs for the properties of this resource.
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

    /** Check to see if a property exists for this resource.
     *
     * This method will return true if the property exists.
     *
     * @param  string  $property The name of the property (e.g. foaf:gender)
     * @return bool              True if value the property exists.
     */
    public function hasProperty($resource, $property)
    {
        $this->checkResourceParam($resource);
        $this->checkPropertyParam($property, $inverse);

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
        foreach ($this->_index as $resource => $properties) {
            $r[] = $this->dumpResource($resource, $html);
        }
        return join('', $r);
    }

    public function dumpResource($resource, $html=true)
    {
        # FIXME: check arguments

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
                  $olist []= $this->dumpLiteralValue($value, $html);
                } else {
                  $olist []= $this->dumpResourceValue($value['value'], $html, 'red');
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
                   "<div>".$this->dumpResourceValue($resource, true, 'blue')." ".
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

    protected function dumpResourceValue($resource, $html=true, $color='blue')
    {
        $short = EasyRdf_Namespace::shorten($resource);
        if ($html) {
            $escaped = htmlentities($resource);
            if (substr($resource, 0, 2) == '_:') {
                $href = '#' . $escaped;
            } else {
                $href = $escaped;
            }
            if ($short) {
                return "<a href='$href' style='text-decoration:none;color:$color'>$short</a>";
            } else {
                return "<a href='$href' style='text-decoration:none;color:$color'>$escaped</a>";
            }
        } else {
            if ($short) {
                return $short;
            } else {
                return $resource;
            }
        }
    }

    protected function dumpLiteralValue($value, $html=true, $color='blue')
    {
        $text = '"'.$value['value'].'"';
        if (isset($value['lang'])) {
            $text .= '@' . $value['lang'];
        }
        if (isset($value['datatype'])) {
            $datatype = EasyRdf_Namespace::shorten($value['datatype']);
            $text .= "^^$datatype";
        }

        if ($html) {
            return "<span style='color:$color'>".
                       htmlentities($text, ENT_COMPAT, "UTF-8").
                       "</span>";
        } else {
            return $text;
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
            $type = $this->get($resource, 'rdf:type');
            if ($type)
                return EasyRdf_Namespace::shorten($type);
        }

        return null;
    }

    public function typeAsResource($resource=null)
    {
        $this->checkResourceParam($resource, true);

        if ($resource) {
            return $this->get($resource, 'rdf:type');
        }

        return null;
    }

    public function types($resource=null)
    {
        $this->checkResourceParam($resource, true);

        $types = array();
        if ($resource) {
            foreach ($this->all($resource, 'rdf:type') as $type) {
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
        foreach ($this->all($resource, 'rdf:type') as $t) {
            if ($t->getUri() == $type) {
                return true;
            }
        }
        return false;
    }

    public function addType($resource, $types)
    {
        $this->checkResourceParam($resource, true);

        if (!is_array($types))
            $types = array($types);

        foreach ($types as $type) {
            $type = EasyRdf_Namespace::expand($type);
            $this->add($resource, 'rdf:type', array('type' => 'uri', 'value' => $type));
        }
    }

    public function setType($resource, $type)
    {
        $this->checkResourceParam($resource, true);

        $this->delete($resource, 'rdf:type');
        return $this->addType($resource, $type);
    }

    public function label($resource=null, $lang=null)
    {
        $this->checkResourceParam($resource, true);

        if ($resource) {
            return $this->get(
                $resource,
                array('skos:prefLabel', 'rdfs:label', 'foaf:name', 'dc:title', 'dc11:title'),
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
                $resource, array('foaf:primaryTopic', '^foaf:isPrimaryTopicOf')
            );
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
