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
 * Class the represents an RDF file format.
 *
 * For each format, the name, label, URIs and associated MIME Types are
 * stored. A single parser and serialiser can also be registered to each
 * format.
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009-2011 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */
class EasyRdf_Format
{
    private static $_formats = array();

    private $_name = array();
    private $_label = null;
    private $_uri = null;
    private $_mimeTypes = array();
    private $_parserClass = null;
    private $_serialiserClass = null;

    /** Get a list of format names
     *
     * @return array          An array of formats name
     */
    public static function getNames()
    {
        return array_keys(self::$_formats);
    }

    /** Get a list of all the registsed formats
     *
     * @return array          An array of format objects
     */
    public static function getFormats()
    {
        return self::$_formats;
    }

    /** Generates an HTTP Accept header string
     *  
     * The string will contain all of the MIME Types that we
     * are able to parse. 
     *
     * It is also possible to specify additional MIME types 
     * in the form array('text/plain' => 0.5) where 0.5 is the 
     * q value for that type. The types are sorted by q value 
     * before constructing the string.
     *
     * @param array $extraTypes    extra MIME types to add
     * @return string              list of supported MIME types
     */
    public static function getHttpAcceptHeader($extraTypes=array())
    {
        $accept = $extraTypes;
        foreach (self::$_formats as $format) {
            if ($format->_parserClass and count($format->_mimeTypes) > 0) {
                $accept = array_merge($accept, $format->_mimeTypes);
            }
        }
        arsort($accept, SORT_NUMERIC);

        $acceptStr='';
        foreach ($accept as $type => $q) {
            if ($acceptStr)
                $acceptStr .= ',';
            if ($q == 1.0) {
                $acceptStr .= $type;
            } else {
                $acceptStr .= sprintf("%s;q=%1.1f", $type, $q);
            }
        }
        return $acceptStr;
    }

    /** Check if a named graph exists
     *
     * @param string $name    the name of the format
     * @return boolean        true if the format exists
     */
    public static function formatExists($name)
    {
        return array_key_exists($name, self::$_formats);
    }

    /** Get a EasyRdf_Format from a name, uri or mime type
     *
     * @param string $query   a query string to search for
     * @return object         the first EasyRdf_Format that matches the query
     * @throws EasyRdf_Exception  if no format is found
     */
    public static function getFormat($query)
    {
        if (!is_string($query) or $query == null or $query == '') {
            throw new InvalidArgumentException(
                "\$query should be a string and cannot be null or empty"
            );
        }

        foreach (self::$_formats as $format) {
           if ($query == $format->_name or
               $query == $format->_uri or
               array_key_exists($query, $format->_mimeTypes)) {
               return $format;
           }
        }

        # No match
        throw new EasyRdf_Exception(
            "Format is not recognised: $query"
        );
    }

    /** Register a new format
     *
     * @param  string  $name      The name of the format (e.g. ntriples)
     * @param  string  $label     The label for the format (e.g. N-Triples)
     * @param  string  $uri       The URI for the format
     * @param  string  $mimeTypes One or more mime types for the format
     * @return object             The new EasyRdf_Format object
     */
    public static function register($name, $label=null, $uri=null,
                                    $mimeTypes=array())
    {
        if (!is_string($name) or $name == null or $name == '') {
            throw new InvalidArgumentException(
                "\$name should be a string and cannot be null or empty"
            );
        }

        if (!array_key_exists($name, self::$_formats)) {
            self::$_formats[$name] = new EasyRdf_Format($name);
        }

        self::$_formats[$name]->setLabel($label);
        self::$_formats[$name]->setUri($uri);
        self::$_formats[$name]->setMimeTypes($mimeTypes);
        return self::$_formats[$name];
    }

    /** Remove a format from the registry
     *
     * @param  string  $name      The name of the format (e.g. ntriples)
     */
    public static function unregister($name)
    {
        unset(self::$_formats[$name]);
    }

    /** Class method to register a parser class to a format name
     *
     * @param  string  $name   The name of the format (e.g. ntriples)
     * @param  string  $class  The name of the class (e.g. EasyRdf_Parser_Ntriples)
     */
    public static function registerParser($name, $class)
    {
        if (!self::formatExists($name))
            self::register($name);
        self::getFormat($name)->setParserClass($class);
    }

    /** Class method to register a serialiser class to a format name
     *
     * @param  string  $name   The name of the format (e.g. ntriples)
     * @param  string  $class  The name of the class (e.g. EasyRdf_Serialiser_Ntriples)
     */
    public static function registerSerialiser($name, $class)
    {
        if (!self::formatExists($name))
            self::register($name);
        self::getFormat($name)->setSerialiserClass($class);
    }

    /** Attempt to guess the document format from some content.
     *
     * If the document format is not recognised, null is returned.
     *
     * @param  string $data The document data
     * @return EasyRdf_Format The format object
     */
    public static function guessFormat($data)
    {
        if (is_array($data)) {
            # Data has already been parsed into RDF/PHP
            return self::getFormat('php');
        }

        $short = substr(trim($data), 0, 255);
        if (preg_match("/^\{/", $short)) {
            return self::getFormat('json');
        } else if (
            preg_match("/<!DOCTYPE html/", $short) or
            preg_match("/^<html/", $short)
        ) {
            # FIXME: might be erdf or something instead...
            return self::getFormat('rdfa');
        } else if (preg_match("/<rdf/", $short)) {
            return self::getFormat('rdfxml');
        } else if (preg_match("/^@prefix /", $short)) {
            # FIXME: this could be improved
            return self::getFormat('turtle');
        } else if (preg_match("/^<.+> <.+>/", $short)) {
            return self::getFormat('ntriples');
        } else if (preg_match("|http://www.w3.org/2005/sparql-results|", $short)) {
            return self::getFormat('sparql-xml');
        } else if (preg_match("/^<\?xml /", $short)) {
            # FIXME: this could be improved
            return self::getFormat('rdfxml');
        } else {
            return null;
        }
    }

    /**
     * This constructor is for internal use only.
     * To create a new format, use the register method.
     *
     * @param  string  $name    The name of the format
     * @see    EasyRdf_Format::register()
     * @ignore
     */
    public function __construct($name)
    {
        $this->_name = $name;
        $this->_label = $name;  # Only a default
    }

    /** Get the name of a format object
     *
     * @return string The name of the format (e.g. rdfxml)
     */
    public function getName()
    {
        return $this->_name;
    }

    /** Get the label for a format object
     *
     * @return string The format label (e.g. RDF/XML)
     */
    public function getLabel()
    {
        return $this->_label;
    }

    /** Set the label for a format object
     *
     * @param  string $label  The new label for the format
     */
    public function setLabel($label)
    {
        if ($label) {
            if (!is_string($label)) {
                throw new InvalidArgumentException(
                    "\$label should be a string"
                );
            }
            return $this->_label = $label;
        } else {
            return $this->_label = null;
        }
    }

    /** Get the URI for a format object
     *
     * @return string The format URI
     */
    public function getUri()
    {
        return $this->_uri;
    }

    /** Set the URI for a format object
     *
     * @param string $uri  The new URI for the format
     */
    public function setUri($uri)
    {
        if ($uri) {
            if (!is_string($uri)) {
                throw new InvalidArgumentException(
                    "\$uri should be a string"
                );
            }
            return $this->_uri = $uri;
        } else {
            return $this->_uri = null;
        }
    }

    /** Get the default registered mime type for a format object
     *
     * @return string The default mime type as a string.
     */
    public function getDefaultMimeType()
    {
        $types = array_keys($this->_mimeTypes);
        return $types[0];
    }

    /** Get all the registered mime types for a format object
     *
     * @return array One or more MIME types in an array with
     *               the mime type as the key and q value as the value
     */
    public function getMimeTypes()
    {
        return $this->_mimeTypes;
    }

    /** Set the MIME Types for a format object
     *
     * @param array $mimeTypes  One or more mime types
     */
    public function setMimeTypes($mimeTypes)
    {
        if ($mimeTypes) {
            if (!is_array($mimeTypes)) {
                $mimeTypes = array($mimeTypes);
            }
            $this->_mimeTypes = $mimeTypes;
        } else {
            $this->_mimeTypes = array();
        }
    }

    /** Set the parser to use for a format
     *
     * @param string $class  The name of the class
     */
    public function setParserClass($class)
    {
        if ($class) {
            if (!is_string($class)) {
                throw new InvalidArgumentException(
                    "\$class should be a string"
                );
            }
            $this->_parserClass = $class;
        } else {
            $this->_parserClass = null;
        }
    }

    /** Get the name of the class to use to parse the format
     *
     * @return string The name of the class
     */
    public function getParserClass()
    {
        return $this->_parserClass;
    }

    /** Create a new parser to parse this format
     *
     * @return object The new parser object
     */
    public function newParser()
    {
        $parserClass = $this->_parserClass;
        if (!$parserClass) {
            throw new EasyRdf_Exception(
                "No parser class available for format: ".$this->getName()
            );
        }
        return (new $parserClass());
    }

    /** Set the serialiser to use for a format
     *
     * @param string $class  The name of the class
     */
    public function setSerialiserClass($class)
    {
        if ($class) {
            if (!is_string($class)) {
                throw new InvalidArgumentException(
                    "\$class should be a string"
                );
            }
            $this->_serialiserClass = $class;
        } else {
            $this->_serialiserClass = null;
        }
    }

    /** Get the name of the class to use to serialise the format
     *
     * @return string The name of the class
     */
    public function getSerialiserClass()
    {
        return $this->_serialiserClass;
    }

    /** Create a new serialiser to parse this format
     *
     * @return object The new serialiser object
     */
    public function newSerialiser()
    {
        $serialiserClass = $this->_serialiserClass;
        if (!$serialiserClass) {
            throw new EasyRdf_Exception(
                "No serialiser class available for format: ".$this->getName()
            );
        }
        return (new $serialiserClass());
    }

    /** Magic method to return the name of the format when casted to string
     *
     * @return string The name of the format
     */
    public function __toString()
    {
        return $this->_name;
    }
}


/*
   Register default set of supported formats
   NOTE: they are ordered by preference
*/

EasyRdf_Format::register(
    'php',
    'RDF/PHP',
    'http://n2.talis.com/wiki/RDF_PHP_Specification'
);

EasyRdf_Format::register(
    'json',
    'RDF/JSON Resource-Centric',
    'http://n2.talis.com/wiki/RDF_JSON_Specification',
    array(
        'application/json' => 1.0,
        'text/json' => 0.9,
        'application/rdf+json' => 0.9
    )
);

EasyRdf_Format::register(
    'ntriples',
    'N-Triples',
    'http://www.w3.org/TR/rdf-testcases/#ntriples',
    array(
        'text/plain' => 1.0,
        'text/ntriples' => 0.9,
        'application/ntriples' => 0.9,
        'application/x-ntriples' => 0.9
    )
);

EasyRdf_Format::register(
    'turtle',
    'Turtle Terse RDF Triple Language',
    'http://www.dajobe.org/2004/01/turtle',
    array(
        'text/turtle' => 0.8,
        'application/turtle' => 0.7,
        'application/x-turtle' => 0.7
    )
);

EasyRdf_Format::register(
    'rdfxml',
    'RDF/XML',
    'http://www.w3.org/TR/rdf-syntax-grammar',
    array(
        'application/rdf+xml' => 0.8
    )
);

EasyRdf_Format::register(
    'n3',
    'Notation3',
    'http://www.w3.org/2000/10/swap/grammar/n3#',
    array(
        'text/n3' => 0.5,
        'text/rdf+n3' => 0.5
    )
);

EasyRdf_Format::register(
    'rdfa',
    'RDF/A',
    'http://www.w3.org/TR/rdfa/',
    array(
        'text/html' => 0.4,
        'application/xhtml+xml' => 0.4
    )
);

EasyRdf_Format::register(
    'sparql-xml',
    'SPARQL XML Query Results',
    'http://www.w3.org/TR/rdf-sparql-XMLres/',
    array(
        'application/sparql-results+xml' => 1.0
    )
);

EasyRdf_Format::register(
    'sparql-json',
    'SPARQL JSON Query Results',
    'http://www.w3.org/TR/rdf-sparql-json-res/',
    array(
        'application/sparql-results+json' => 1.0
    )
);
