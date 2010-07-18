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
 * A namespace registry and manipulation class.
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009-2010 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */
class EasyRdf_Namespace
{
    /** Namespace registery */
    private static $_namespaces = array(
      'cc' => 'http://creativecommons.org/ns#',
      'dc' => 'http://purl.org/dc/terms/',
      'dc11' => 'http://purl.org/dc/elements/1.1/',
      'doap' => 'http://usefulinc.com/ns/doap#',
      'exif' => 'http://www.w3.org/2003/12/exif/ns#',
      'foaf' => 'http://xmlns.com/foaf/0.1/',
      'http' => 'http://www.w3.org/2006/http#',
      'owl' => 'http://www.w3.org/2002/07/owl#',
      'rdf' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
      'rdfs' => 'http://www.w3.org/2000/01/rdf-schema#',
      'rss' => 'http://purl.org/rss/1.0/',
      'sioc' => 'http://rdfs.org/sioc/ns#',
      'skos' => 'http://www.w3.org/2004/02/skos/core#',
      'synd' => 'http://purl.org/rss/1.0/modules/syndication/',
      'wot' => 'http://xmlns.com/wot/0.1/',
      'xhtml' => 'http://www.w3.org/1999/xhtml/vocab#',
      'xsd' => 'http://www.w3.org/2001/XMLSchema#'
    );

    /**
      * Return all the namespaces registered
      *
      * @return array Associative array of all the namespaces.
      */
    public static function namespaces()
    {
        return self::$_namespaces;
    }

    /**
      * Return a namespace given its prefix.
      *
      * @param string $prefix The namespace prefix (eg 'foaf')
      * @return string The namespace URI (eg 'http://xmlns.com/foaf/0.1/')
      */
    public static function get($prefix)
    {
        if (!is_string($prefix) or $prefix == null or $prefix == '') {
            throw new InvalidArgumentException(
                "\$prefix should be a string and cannot be null or empty"
            );
        }

        $prefix = strtolower($prefix);
        if (array_key_exists($prefix, self::$_namespaces)) {
            return self::$_namespaces[$prefix];
        } else {
            return null;
        }
    }

    /**
      * Register a new namespace.
      *
      * @param string $prefix The namespace prefix (eg 'foaf')
      * @param string $long The namespace URI (eg 'http://xmlns.com/foaf/0.1/')
      */
    public static function set($prefix, $long)
    {
        if (!is_string($prefix) or $prefix == null or $prefix == '') {
            throw new InvalidArgumentException(
                "\$prefix should be a string and cannot be null or empty"
            );
        }

        if (!is_string($long) or $long == null or $long == '') {
            throw new InvalidArgumentException(
                "\$long should be a string and cannot be null or empty"
            );
        }

        $prefix = strtolower($prefix);
        self::$_namespaces[$prefix] = $long;
    }

    /**
      * Return the prefix namespace that a URI belongs to.
      *
      * @param string $uri A full URI (eg 'http://xmlns.com/foaf/0.1/name')
      * @return string The prefix namespace that it is a part of(eg 'foaf')
      */
    public static function prefixOfUri($uri)
    {
        if (!is_string($uri) or $uri == null or $uri == '') {
            throw new InvalidArgumentException(
                "\$uri should be a string and cannot be null or empty"
            );
        }

        foreach (self::$_namespaces as $prefix => $long) {
            if (strpos($uri, $long) === 0) {
                return $prefix;
            }
        }
        return null;
    }

    /**
      * Shorten a URI by substituting in the namespace prefix.
      *
      * @param string $uri The full URI (eg 'http://xmlns.com/foaf/0.1/name')
      * @return string The shortened URI (eg 'foaf:name')
      */
    public static function shorten($uri)
    {
        if ($uri == null or $uri == '') {
            throw new InvalidArgumentException(
                "\$uri cannot be null or empty"
            );
        }

        if (is_object($uri) and get_class($uri) == 'EasyRdf_Resource') {
            $uri = $uri->getUri();
        } else if (!is_string($uri)) {
            throw new InvalidArgumentException(
                "\$uri should be a string or EasyRdf_Resource"
            );
        }

        foreach (self::$_namespaces as $prefix => $long) {
            if (strpos($uri, $long) === 0) {
                return $prefix . ':' . substr($uri, strlen($long));
            }
        }
        return null;
    }

    /**
      * Expand a shortened URI back into a full URI.
      *
      * @param string $shortUri The short URI (eg 'foaf:name')
      * @return string The full URI (eg 'http://xmlns.com/foaf/0.1/name')
      */
    public static function expand($shortUri)
    {
        if (preg_match("/^(\w+?):(.+)$/", $shortUri, $matches)) {
            $long = self::get($matches[1]);
            if ($long) {
                return $long . $matches[2];
            } else {
                return null;
            }
        } else {
            throw new InvalidArgumentException(
                "\$shortUri should be in the form prefix:suffix"
            );
        }
    }
}
