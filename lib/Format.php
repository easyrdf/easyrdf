<?php
namespace EasyRdf;

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009-2020 Nicholas J Humfrey.  All rights reserved.
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
 * @copyright  Copyright (c) 2009-2020 Nicholas J Humfrey
 * @license    https://www.opensource.org/licenses/bsd-license.php
 */

/**
 * Class the represents an RDF file format.
 *
 * For each format, the name, label, URIs and associated MIME Types are
 * stored. A single parser and serialiser can also be registered to each
 * format.
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009-2020 Nicholas J Humfrey
 * @license    https://www.opensource.org/licenses/bsd-license.php
 */
class Format
{
    private static $formats = array();

    private $name = array();
    private $label = null;
    private $uri = null;
    private $mimeTypes = array();
    private $extensions = array();
    private $parserClass = null;
    private $serialiserClass = null;

    /** Get a list of format names
     *
     * @return array          An array of formats name
     */
    public static function getNames()
    {
        return array_keys(self::$formats);
    }

    /** Get a list of all the registered formats
     *
     * @return array          An array of format objects
     */
    public static function getFormats()
    {
        return self::$formats;
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
     *
     * @return string              list of supported MIME types
     */
    public static function getHttpAcceptHeader(array $extraTypes = array())
    {
        $accept = $extraTypes;
        foreach (self::$formats as $format) {
            if ($format->parserClass and count($format->mimeTypes) > 0) {
                $accept = array_merge($accept, $format->mimeTypes);
            }
        }

        return self::formatAcceptHeader($accept);
    }

    /**
     * Convert array of types to Accept header value
     * @param array $accepted_types
     * @return string
     */
    public static function formatAcceptHeader(array $accepted_types)
    {
        arsort($accepted_types, SORT_NUMERIC);

        $acceptStr = '';
        foreach ($accepted_types as $type => $q) {
            if ($acceptStr) {
                $acceptStr .= ',';
            }
            if ($q == 1.0) {
                $acceptStr .= $type;
            } else {
                $acceptStr .= sprintf("%s;q=%1.1F", $type, $q);
            }
        }

        return $acceptStr;
    }

    /** Check if a named graph exists
     *
     * @param string $name    the name of the format
     *
     * @return boolean        true if the format exists
     */
    public static function formatExists($name)
    {
        return array_key_exists($name, self::$formats);
    }

    /** Get a EasyRdf\Format from a name, uri or mime type
     *
     * @param string $query  a query string to search for
     *
     * @return self  the first object that matches the query
     * @throws \InvalidArgumentException
     * @throws Exception  if no format is found
     */
    public static function getFormat($query)
    {
        if (!is_string($query) or $query == null or $query == '') {
            throw new \InvalidArgumentException(
                "\$query should be a string and cannot be null or empty"
            );
        }

        foreach (self::$formats as $format) {
            if ($query == $format->name or
                $query == $format->uri or
                array_key_exists($query, $format->mimeTypes) or
                in_array($query, $format->extensions)) {
                return $format;
            }
        }

        # No match
        throw new Exception(
            "Format is not recognised: $query"
        );
    }

    /** Register a new format
     *
     * @param string       $name       The name of the format (e.g. ntriples)
     * @param string       $label      The label for the format (e.g. N-Triples)
     * @param string       $uri        The URI for the format
     * @param array|string $mimeTypes  One or more mime types for the format
     * @param array|string $extensions One or more extensions (file suffix)
     *
     * @throws \InvalidArgumentException
     * @return self  New Format object
     */
    public static function register(
        $name,
        $label = null,
        $uri = null,
        $mimeTypes = array(),
        $extensions = array()
    ) {
        if (!is_string($name) or $name == null or $name == '') {
            throw new \InvalidArgumentException(
                "\$name should be a string and cannot be null or empty"
            );
        }

        if (!array_key_exists($name, self::$formats)) {
            self::$formats[$name] = new self($name);
        }

        self::$formats[$name]->setLabel($label);
        self::$formats[$name]->setUri($uri);
        self::$formats[$name]->setMimeTypes($mimeTypes);
        self::$formats[$name]->setExtensions($extensions);
        return self::$formats[$name];
    }

    /** Remove a format from the registry
     *
     * @param  string  $name      The name of the format (e.g. ntriples)
     */
    public static function unregister($name)
    {
        unset(self::$formats[$name]);
    }

    /** Class method to register a parser class to a format name
     *
     * @param  string  $name   The name of the format (e.g. ntriples)
     * @param  string  $class  The name of the class (e.g. EasyRdf\Parser\Ntriples)
     */
    public static function registerParser($name, $class)
    {
        if (!self::formatExists($name)) {
            self::register($name);
        }
        self::getFormat($name)->setParserClass($class);
    }

    /** Class method to register a serialiser class to a format name
     *
     * @param  string  $name   The name of the format (e.g. ntriples)
     * @param  string  $class  The name of the class (e.g. EasyRdf\Serialiser\Ntriples)
     */
    public static function registerSerialiser($name, $class)
    {
        if (!self::formatExists($name)) {
            self::register($name);
        }
        self::getFormat($name)->setSerialiserClass($class);
    }

    /** Attempt to guess the document format from some content.
     *
     * If $filename is given, then the suffix is first used to guess the format.
     *
     * If the document format is not recognised, null is returned.
     *
     * @param string  $data     The document data
     * @param string  $filename Optional filename
     *
     * @return self  New format object
     */
    public static function guessFormat($data, $filename = null)
    {
        if (is_array($data)) {
            # Data has already been parsed into RDF/PHP
            return self::getFormat('php');
        }

        // First try and identify by the filename
        if ($filename and preg_match('/\.(\w+)$/', $filename, $matches)) {
            foreach (self::$formats as $format) {
                if (in_array($matches[1], $format->extensions)) {
                    return $format;
                }
            }
        }

        // Then try and guess by the first 1024 bytes of content
        $short = substr($data, 0, 1024);
        if (preg_match('/^\s*\{/', $short)) {
            return self::getFormat('json');
        } elseif (preg_match('/<rdf:/i', $short)) {
            return self::getFormat('rdfxml');
        } elseif (preg_match('|http://www.w3.org/2005/sparql-results|', $short)) {
            return self::getFormat('sparql-xml');
        } elseif (preg_match('/\WRDFa\W/i', $short)) {
            return self::getFormat('rdfa');
        } elseif (preg_match('/<!DOCTYPE html|<html/i', $short)) {
            # We don't support any other microformats embedded in HTML
            return self::getFormat('rdfa');
        } elseif (preg_match('/@prefix\s|@base\s/', $short)) {
            return self::getFormat('turtle');
        } elseif (preg_match('/prefix\s|base\s/i', $short)) {
            return self::getFormat('turtle');
        } elseif (preg_match('/^\s*<.+> <.+>/m', $short)) {
            return self::getFormat('ntriples');
        } else {
            return null;
        }
    }

    /**
     * This constructor is for internal use only.
     * To create a new format, use the register method.
     *
     * @param  string  $name    The name of the format
     * @see    Format::register()
     * @ignore
     */
    public function __construct($name)
    {
        $this->name = $name;
        $this->label = $name;  # Only a default
    }

    /** Get the name of a format object
     *
     * @return string The name of the format (e.g. rdfxml)
     */
    public function getName()
    {
        return $this->name;
    }

    /** Get the label for a format object
     *
     * @return string The format label (e.g. RDF/XML)
     */
    public function getLabel()
    {
        return $this->label;
    }

    /** Set the label for a format object
     *
     * @param  string $label  The new label for the format
     *
     * @throws \InvalidArgumentException
     * @return string|null
     */
    public function setLabel($label)
    {
        if ($label) {
            if (!is_string($label)) {
                throw new \InvalidArgumentException(
                    "\$label should be a string"
                );
            }
            return $this->label = $label;
        } else {
            return $this->label = null;
        }
    }

    /** Get the URI for a format object
     *
     * @return string The format URI
     */
    public function getUri()
    {
        return $this->uri;
    }

    /** Set the URI for a format object
     *
     * @param string $uri  The new URI for the format
     *
     * @throws \InvalidArgumentException
     * @return string|null
     */
    public function setUri($uri)
    {
        if ($uri) {
            if (!is_string($uri)) {
                throw new \InvalidArgumentException(
                    "\$uri should be a string"
                );
            }
            return $this->uri = $uri;
        } else {
            return $this->uri = null;
        }
    }

    /** Get the default registered mime type for a format object
     *
     * @return string The default mime type as a string.
     */
    public function getDefaultMimeType()
    {
        $types = array_keys($this->mimeTypes);
        if (isset($types[0])) {
            return $types[0];
        }
    }

    /** Get all the registered mime types for a format object
     *
     * @return array One or more MIME types in an array with
     *               the mime type as the key and q value as the value
     */
    public function getMimeTypes()
    {
        return $this->mimeTypes;
    }

    /** Set the MIME Types for a format object
     *
     * @param string|array $mimeTypes  One or more mime types
     */
    public function setMimeTypes($mimeTypes)
    {
        if ($mimeTypes) {
            if (!is_array($mimeTypes)) {
                $mimeTypes = array($mimeTypes);
            }
            $this->mimeTypes = $mimeTypes;
        } else {
            $this->mimeTypes = array();
        }
    }

    /** Get the default registered file extension (filename suffix) for a format object
     *
     * @return string The default extension as a string.
     */
    public function getDefaultExtension()
    {
        if (isset($this->extensions[0])) {
            return $this->extensions[0];
        }
    }

    /** Get all the registered file extensions (filename suffix) for a format object
     *
     * @return array One or more extensions as an array
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /** Set the file format extensions (filename suffix) for a format object
     *
     * @param mixed $extensions  One or more file extensions
     */
    public function setExtensions($extensions)
    {
        if ($extensions) {
            if (!is_array($extensions)) {
                $extensions = array($extensions);
            }
            $this->extensions = $extensions;
        } else {
            $this->extensions = array();
        }
    }

    /** Set the parser to use for a format
     *
     * @param string $class  The name of the class
     *
     * @throws \InvalidArgumentException
     */
    public function setParserClass($class)
    {
        if ($class) {
            if (!is_string($class)) {
                throw new \InvalidArgumentException(
                    "\$class should be a string"
                );
            }
            $this->parserClass = $class;
        } else {
            $this->parserClass = null;
        }
    }

    /** Get the name of the class to use to parse the format
     *
     * @return string The name of the class
     */
    public function getParserClass()
    {
        return $this->parserClass;
    }

    /** Create a new parser to parse this format
     *
     * @throws Exception
     * @return object The new parser object
     */
    public function newParser()
    {
        $parserClass = $this->parserClass;
        if (!$parserClass) {
            throw new Exception(
                "No parser class available for format: ".$this->getName()
            );
        }
        return (new $parserClass());
    }

    /** Set the serialiser to use for a format
     *
     * @param string $class  The name of the class
     *
     * @throws \InvalidArgumentException
     */
    public function setSerialiserClass($class)
    {
        if ($class) {
            if (!is_string($class)) {
                throw new \InvalidArgumentException(
                    "\$class should be a string"
                );
            }
            $this->serialiserClass = $class;
        } else {
            $this->serialiserClass = null;
        }
    }

    /** Get the name of the class to use to serialise the format
     *
     * @return string The name of the class
     */
    public function getSerialiserClass()
    {
        return $this->serialiserClass;
    }

    /** Create a new serialiser to parse this format
     *
     * @throws Exception
     * @return object The new serialiser object
     */
    public function newSerialiser()
    {
        $serialiserClass = $this->serialiserClass;
        if (!$serialiserClass) {
            throw new Exception(
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
        return $this->name;
    }
}


/*
   Register default set of supported formats
   NOTE: they are ordered by preference
*/

Format::register(
    'php',
    'RDF/PHP',
    'https://www.easyrdf.org/docs/rdf-formats-php',
    array(
        'application/x-httpd-php-source' => 1.0
    ),
    array('phps')
);

Format::register(
    'json',
    'RDF/JSON Resource-Centric',
    'https://www.easyrdf.org/docs/rdf-formats-json',
    array(
        'application/json' => 1.0,
        'text/json' => 0.9,
        'application/rdf+json' => 0.9
    ),
    array('json')
);

Format::register(
    'jsonld',
    'JSON-LD',
    'http://www.w3.org/TR/json-ld/',
    array(
        'application/ld+json' => 1.0
    ),
    array('jsonld')
);

Format::register(
    'ntriples',
    'N-Triples',
    'http://www.w3.org/TR/n-triples/',
    array(
        'application/n-triples' => 1.0,
        'text/plain' => 0.9,
        'text/ntriples' => 0.9,
        'application/ntriples' => 0.9,
        'application/x-ntriples' => 0.9
    ),
    array('nt')
);

Format::register(
    'turtle',
    'Turtle Terse RDF Triple Language',
    'https://www.w3.org/TR/turtle/',
    array(
        'text/turtle' => 0.8,
        'application/turtle' => 0.7,
        'application/x-turtle' => 0.7
    ),
    array('ttl')
);

Format::register(
    'rdfxml',
    'RDF/XML',
    'http://www.w3.org/TR/rdf-syntax-grammar/',
    array(
        'application/rdf+xml' => 0.8,
        'text/xml' => 0.5,
        'application/xml' => 0.5
    ),
    array('rdf', 'xrdf')
);

Format::register(
    'dot',
    'Graphviz',
    'http://www.graphviz.org/doc/info/lang.html',
    array(
        'text/vnd.graphviz' => 0.8
    ),
    array('gv', 'dot')
);

Format::register(
    'json-triples',
    'RDF/JSON Triples'
);

Format::register(
    'n3',
    'Notation3',
    'http://www.w3.org/2000/10/swap/grammar/n3#',
    array(
        'text/n3' => 0.5,
        'text/rdf+n3' => 0.5
    ),
    array('n3')
);

Format::register(
    'rdfa',
    'RDFa',
    'http://www.w3.org/TR/rdfa-core/',
    array(
        'text/html' => 0.4,
        'application/xhtml+xml' => 0.4
    ),
    array('html')
);

Format::register(
    'sparql-xml',
    'SPARQL XML Query Results',
    'http://www.w3.org/TR/rdf-sparql-XMLres/',
    array(
        'application/sparql-results+xml' => 1.0
    )
);

Format::register(
    'sparql-json',
    'SPARQL JSON Query Results',
    'http://www.w3.org/TR/rdf-sparql-json-res/',
    array(
        'application/sparql-results+json' => 1.0
    )
);

Format::register(
    'png',
    'Portable Network Graphics (PNG)',
    'http://www.w3.org/TR/PNG/',
    array(
        'image/png' => 0.3
    ),
    array('png')
);

Format::register(
    'gif',
    'Graphics Interchange Format (GIF)',
    'http://www.w3.org/Graphics/GIF/spec-gif89a.txt',
    array(
        'image/gif' => 0.2
    ),
    array('gif')
);

Format::register(
    'svg',
    'Scalable Vector Graphics (SVG)',
    'http://www.w3.org/TR/SVG/',
    array(
        'image/svg+xml' => 0.3
    ),
    array('svg')
);


/*
   Register default set of parsers and serialisers
*/

Format::registerParser('json', 'EasyRdf\Parser\Json');
Format::registerParser('jsonld', 'EasyRdf\Parser\JsonLd');
Format::registerParser('ntriples', 'EasyRdf\Parser\Ntriples');
Format::registerParser('php', 'EasyRdf\Parser\RdfPhp');
Format::registerParser('rdfxml', 'EasyRdf\Parser\RdfXml');
Format::registerParser('turtle', 'EasyRdf\Parser\Turtle');
Format::registerParser('rdfa', 'EasyRdf\Parser\Rdfa');

Format::registerSerialiser('json', 'EasyRdf\Serialiser\Json');
Format::registerSerialiser('jsonld', 'EasyRdf\Serialiser\JsonLd');
Format::registerSerialiser('n3', 'EasyRdf\Serialiser\Turtle');
Format::registerSerialiser('ntriples', 'EasyRdf\Serialiser\Ntriples');
Format::registerSerialiser('php', 'EasyRdf\Serialiser\RdfPhp');
Format::registerSerialiser('rdfxml', 'EasyRdf\Serialiser\RdfXml');
Format::registerSerialiser('turtle', 'EasyRdf\Serialiser\Turtle');

Format::registerSerialiser('dot', 'EasyRdf\Serialiser\GraphViz');
Format::registerSerialiser('gif', 'EasyRdf\Serialiser\GraphViz');
Format::registerSerialiser('png', 'EasyRdf\Serialiser\GraphViz');
Format::registerSerialiser('svg', 'EasyRdf\Serialiser\GraphViz');
