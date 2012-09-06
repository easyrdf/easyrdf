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
 * A RFC3986 compliant URI parser
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009-2012 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 * @link       http://www.ietf.org/rfc/rfc3986.txt
 */
class EasyRdf_ParsedUri
{
    // For all URIs:
    private $_scheme;
    private $_fragment;
    
    // For hierarchical URIs:
    private $_authority;
    private $_path;
    private $_query;

    const URI_REGEX = "|^(([^:/?#]+):)?(//([^/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?|";

    /** Constructor for creating a new parsed URI
     *
     * @param  string $uristr    The URI as a string
     * @return object EasyRdf_ParsedUri
     */
    public function __construct($uristr)
    {
        if (preg_match(self::URI_REGEX, $uristr, $matches)) {
            $this->_scheme = isset($matches[2]) ? $matches[2] : null;
            $this->_authority = isset($matches[4]) ? $matches[4] : null;
            $this->_path = isset($matches[5]) ? $matches[5] : null;
            $this->_query = isset($matches[7]) ? $matches[7] : null;
            $this->_fragment = isset($matches[9]) ? $matches[9] : null;
            
            // FIXME: parse the authority
        }
        
        // FIXME: throw exception if failed to parse?
    }


    /** Returns true if this is an absolute (complete) URI
     * @return boolean
     */
    public function isAbsolute() {
        return $this->_scheme != null;
    }
    
    /** Returns true if this is an relative (partial) URI
     * @return boolean
     */
    public function isRelative() {
        return $this->_scheme == null;
    }

    /** Returns the scheme of the URI (e.g. http)
     * @return string
     */
    public function getScheme() {
        return $this->_scheme;
    }
    
    /** Returns the authority of the URI (e.g. www.example.com:8080)
     * @return string
     */
    public function getAuthority() {
        return $this->_authority;
    }
    
    /** Returns the path of the URI (e.g. /foo/bar)
     * @return string
     */
    public function getPath() {
        return $this->_path;
    }
    
    /** Returns the query string part of the URI (e.g. foo=bar)
     * @return string
     */
    public function getQuery() {
        return $this->_query;
    }
    
    /** Returns the fragment part of the URI (i.e. after the #)
     * @return string
     */
    public function getFragment() {
        return $this->_fragment;
    }


    /**
     * Normalises the path of this URI if it has one. Normalising a path means
     * that any unnecessary '.' and '..' segments are removed. For example, the
     * URI http://example.com/a/b/../c/./d would be normalised to
     * http://example.com/a/c/d
     *
     * @return object EasyRdf_ParsedUri
     */
    public function normalise() {
        if (empty($this->_path))
            return $this;

        // Remove ./ from the start
        if (substr($this->_path, 0, 2) == './') {
            // Remove both characters
            $this->_path = substr($this->_path, 2);
        }

        // Remove /. from the end
        if (substr($this->_path, -2) == '/.') {
            // Remove only the last dot, not the slash!
            $this->_path = substr($this->_path, 0, -1);
        }

        // Split the path into its segments
        $segments = explode('/', $this->_path);
        $newSegments = array();
        
        // Remove all unnecessary '.' and '..' segments
        foreach($segments as $segment) {
            if ($segment == '..') {
                // Remove the previous part of the path
                $count = count($newSegments);
                if ($count > 0 && $newSegments[$count-1])
                    array_pop($newSegments);
            } else if ($segment == '.') {
                // Ignore
                continue;
            } else {
                array_push($newSegments, $segment);
            }
        }
        
        // Construct the new normalised path
        $this->_path = implode($newSegments, '/');

        // Allow easy chaining of methods
        return $this;
    }
    

    /** Magic method to convert the URI, when casted, back to a string 
     *
     * @return string The URI as a string
     */
    public function __toString()
    {
        $str = '';
        if ($this->_scheme != null)
            $str .= $this->_scheme . ':';
        if ($this->_authority != null)
            $str .= '//' . $this->_authority;
        $str .= $this->_path;
        if ($this->_query != null)
            $str .= '?' . $this->_query;
        if ($this->_fragment != null)
            $str .= '#' . $this->_fragment;
        return $str;
    }

}
