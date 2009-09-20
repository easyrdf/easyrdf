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
 * A namespace registry and manipulation class.
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */
class EasyRdf_Namespace
{
    private static $_namespaces = array(
      'dc' => 'http://purl.org/dc/elements/1.1/',
      'foaf' => 'http://xmlns.com/foaf/0.1/',
      'owl' => 'http://www.w3.org/2002/07/owl#',
      'rdf' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
      'rdfs' => 'http://www.w3.org/2000/01/rdf-schema#',
      'xhtml' => 'http://www.w3.org/1999/xhtml/vocab#',
      'xsd' => 'http://www.w3.org/2001/XMLSchema#'
    );

    /**
      * Return a namespace given its prefix.
      *
      * @param string $short The namespace prefix (eg 'foaf')
      * @return string The namespace URI (eg 'http://xmlns.com/foaf/0.1/')
      */
    public static function get($short)
    {
        $short = strtolower($short);
        if (array_key_exists($short, self::$_namespaces)) {
            return self::$_namespaces[$short];
        } else {
            return null;
        }
    }

    /**
      * Register a new namespace.
      *
      * @param string $short The namespace prefix (eg 'foaf')
      * @param string $long The namespace URI (eg 'http://xmlns.com/foaf/0.1/')
      */
    public static function add($short, $long)
    {
        $short = strtolower($short);
        self::$_namespaces[$short] = $long;
    }

    /**
      * Return the short namespace that a URI belongs to.
      *
      * @param string $uri A full URI (eg 'http://xmlns.com/foaf/0.1/name')
      * @return string The short namespace that it is a part of(eg 'foaf')
      */
    public static function namespaceOfUri($uri)
    {
        foreach (self::$_namespaces as $short => $long) {
            if (strpos($uri, $long) === 0) {
                return $short;
            }
        }
        return null;
    }

    /**
      * Shorten a URI by substituting in the namespace prefix.
      *
      * @param string $uri The full URI (eg 'http://xmlns.com/foaf/0.1/name')
      * @return string The shortened URI (eg 'foaf_name')
      */
    public static function shorten($uri)
    {
        foreach (self::$_namespaces as $short => $long) {
            if (strpos($uri, $long) === 0) {
                return $short . '_' . substr($uri, strlen($long));
            }
        }
        return null;
    }

    /**
      * Expand a shortened URI back into a full URI.
      *
      * @param string $shortUri The short URI (eg 'foaf_name')
      * @return string The full URI (eg 'http://xmlns.com/foaf/0.1/name')
      */
    public static function expand($shortUri)
    {
        if (preg_match("/^(\w+?)_(.+)$/", $shortUri, $matches)) {
            $long = self::get($matches[1]);
            if ($long) {
                return $long . $matches[2];
            } else {
                return null;
            }
        } else {
            return null;
        }
    }
}
