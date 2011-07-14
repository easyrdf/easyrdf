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
 * @copyright  Copyright (c) 2009-2011 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 * @version    $Id$
 */


/**
 * Class containing static utility functions
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009-2010 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */
class EasyRdf_Utils
{

    /**
     * Convert a string into CamelCase
     *
     * A capital letter is inserted for any non-letter (including userscore).
     * For example:
     * 'hello world' becomes HelloWorld
     * 'rss-tag-soup' becomes RssTagSoup
     * 'FOO//BAR' becomes FooBar
     *
     * @param string The input string
     * @return string The input string coverteted to CamelCase
     */
    public static function camelise($str)
    {
        $cc = '';
        foreach (preg_split("/[\W_]+/", $str) as $part) {
            $cc .= ucfirst(strtolower($part));
        }
        return $cc;
    }

    /**
     * Check if something is an associative array
     *
     * Note: this method only checks the key of the first value in the array.
     *
     * @param mixed $param The variable to check
     * @return bool true if the variable is an associative array
     */
    public static function is_associative_array($param)
    {
        if (is_array($param)) {
            $keys = array_keys($param);
            if ($keys[0] === 0) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }

    }

    /**
     * Resolve a URI to a base URI.
     *
     * @param  string $baseUri      The base URI
     * @param  string $referenceUri The URI to resolve
     * @return string The newly resolved URI as a string.
     */
    public static function resolveUriReference($baseUri, $referenceUri)
    {
        /* quick check */
        if (preg_match("/^[a-z0-9\_]+\:/i", $referenceUri)) {/* abs path or bnode */
            return $referenceUri;
        }
        if (preg_match('/^\$\{.*\}/', $referenceUri)) {/* placeholder, assume abs URI */
           return $referenceUri;
        }
        if (preg_match("/^\/\//", $referenceUri)) {/* net path, assume http */
           return 'http:' . $referenceUri;
        }

        /* other URIs */
        $baseUri = preg_replace('/\#.*$/', '', $baseUri);
        if ($referenceUri === true) {/* empty (but valid) URIref via turtle parser: <> */
            return $baseUri;
        }
        $referenceUri = preg_replace("/^\.\//", '', $referenceUri);

        /* w/o trailing slash */
        $root = preg_match('/(^[a-z0-9]+\:[\/]{1,3}[^\/]+)[\/|$]/i', $baseUri, $m) ? $m[1] : $baseUri;
        $baseUri .= ($baseUri == $root) ? '/' : '';
        if (preg_match('/^\//', $referenceUri)) {
            /* leading slash */
            return $root . $referenceUri;
        }
        if (!$referenceUri) {
            return $baseUri;
        }
        if (preg_match('/^([\#\?])/', $referenceUri, $m)) {
            return preg_replace('/\\' .$m[1]. '.*$/', '', $baseUri) . $referenceUri;
        }
        if (preg_match('/^(\&)(.*)$/', $referenceUri, $m)) {/* not perfect yet */
            return preg_match('/\?/', $baseUri) ? $baseUri . $m[1] . $m[2] : $baseUri . '?' . $m[2];
        }
        if (preg_match("/^[a-z0-9]+\:/i", $referenceUri)) {/* abs path */
            return $referenceUri;
        }

        /* rel path: remove stuff after last slash */
        $baseUri = substr($baseUri, 0, strrpos($baseUri, '/')+1);

        /* resolve ../ */
        while (preg_match('/^(\.\.\/)(.*)$/', $referenceUri, $m)) {
            $referenceUri = $m[2];
            $baseUri = ($baseUri == $root.'/') ? $baseUri : preg_replace('/^(.*\/)[^\/]+\/$/', '\\1', $baseUri);
        }
        return $baseUri . $referenceUri;
    }

    /** Return pretty-print view of a resource URI
     *
     * This method is mainly intended for internal use and is used by
     * EasyRdf_Graph and EasyRdf_Sparql_Result to format a resource
     * for display.
     *
     * @param  mixed $resource An EasyRdf_Resource object or an associative array
     * @param  bool  $html     Set to true to format the dump using HTML
     * @param  string $color   The colour of the text
     * @return string
     */
    public static function dumpResourceValue($resource, $html=true, $color='blue')
    {
        if (is_object($resource)) {
            $resource = strval($resource);
        } else if (is_array($resource)) {
            $resource = $resource['value'];
        }

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

    /** Return pretty-print view of a literal
     *
     * This method is mainly intended for internal use and is used by
     * EasyRdf_Graph and EasyRdf_Sparql_Result to format a literal
     * for display.
     *
     * @param  mixed $resource An EasyRdf_Literal object or an associative array
     * @param  bool  $html     Set to true to format the dump using HTML
     * @param  string $color   The colour of the text
     * @return string
     */
    public static function dumpLiteralValue($literal, $html=true, $color='black')
    {
        if (is_object($literal)) {
            $literal = $literal->toArray();
        } else if (!is_array($literal)) {
            $literal = array('value' => $literal);
        }

        $text = '"'.$literal['value'].'"';
        if (isset($literal['lang'])) {
            $text .= '@' . $literal['lang'];
        }
        if (isset($literal['datatype'])) {
            $datatype = EasyRdf_Namespace::shorten($literal['datatype']);
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

}
