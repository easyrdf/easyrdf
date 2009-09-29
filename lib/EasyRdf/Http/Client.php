<?php

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009 Nicholas J Humfrey.  All rights reserved.
 * Copyright (c) 2005-2009 Zend Technologies USA Inc.
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
 *             Copyright (c) 2005-2009 Zend Technologies USA Inc.
 * @license    http://www.opensource.org/licenses/bsd-license.php
 * @version    $Id$
 */

/**
 * @see EasyRdf_Exception
 */
require_once "EasyRdf/Exception.php";

/**
 * @see EasyRdf_Http_Response
 */
require_once "EasyRdf/Http/Response.php";

/**
 * This class is an implemetation of an HTTP client in PHP.
 * It supports basic HTTP 1.0 and 1.1 requests. For a more complete 
 * implementation try Zend_Http_Client.
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */
class EasyRdf_Http_Client
{
    private $_uri = null;
    private $_config = array(
        'maxredirects'    => 5,
        'useragent'       => 'EasyRdf_Http_Client',
        'timeout'         => 10
    );
    private $_headers = array();
    private $_redirectCounter = 0;

    public function __construct($uri = null, $config = null)
    {
        if ($uri !== null) {
            $this->setUri($uri);
        }
        if ($config !== null) {
            $this->setConfig($config);
        }
    }

    public function setUri($uri)
    {
        if (!is_string($uri)) {
            $uri = strval($uri);
        }

        $this->_uri = $uri;

        return $this;
    }

    public function getUri($asString = true)
    {
        return $this->_uri;
    }

    public function setConfig($config = array())
    {
        if ($config == null or !is_array($config)) {
            throw new EasyRdf_Exception(
                "\$config should be an array and cannot be null"
            );
        }

        foreach ($config as $k => $v)
            $this->_config[strtolower($k)] = $v;

        return $this;
    }
    
    public function setHeaders($name, $value = null)
    {
        $normalizedName = strtolower($name);

        // If $value is null or false, unset the header
        if ($value === null || $value === false) {
            unset($this->_headers[$normalizedName]);

        // Else, set the header
        } else {
            $this->_headers[$normalizedName] = array($name, $value);
        }

        return $this;
    }

    public function getHeader($key)
    {
        $key = strtolower($key);
        if (isset($this->_headers[$key])) {
            return $this->_headers[$key][1];
        } else {
            return null;
        }
    }

    /**
     * Get the number of redirections done on the last request
     *
     * @return int
     */
    public function getRedirectionsCount()
    {
        return $this->_redirectCounter;
    }

    public function request($method = 'GET')
    {
        if (!$this->_uri) {
            throw new EasyRdf_Exception(
                "Set URI before calling EasyRdf_Http_Client->request()"
            );
        }

        $this->_redirectCounter = 0;
        $response = null;

        // Send the first request. If redirected, continue.
        do {
            // Clone the URI and add the additional GET parameters to it
            $uri = parse_url($this->_uri);
            $host = $uri['host'];
            if (isset($uri['port'])) {
                $port = $uri['port'];
            } else {
                $port = 80;
            }
            $headers = $this->_prepareHeaders($host, $port);

            // Open socket to remote server
            $socket = fsockopen(
                $host, $port, $errno, $errstr, $this->_config['timeout']
            );
            if (!$socket) {
                throw new EasyRdf_Exception($errstr);
            }

            // Write the request
            fwrite($socket, "{$method} {$uri['path']} HTTP/1.1\r\n");
            foreach ($headers as $k => $v) {
                if (is_string($k)) $v = ucfirst($k) . ": $v";
                fwrite($socket, "$v\r\n");
            }
            fwrite($socket, "\r\n");


            // Read in the response
            $content = '';
            while (!feof($socket)) {
                $content .= fgets($socket);
            }
            
            // FIXME: support HTTP/1.1 100 Continue
            
            // Close the socket
            fclose($socket);

            // Parse the response string
            $response = EasyRdf_Http_Response::fromString($content);
 
            // If we got redirected, look for the Location header
            if ($response->isRedirect() && 
                   ($location = $response->getHeader('location'))
               ) {

                // If we got a well formed absolute URI
                if (parse_url($location)) {
                    $this->setHeaders('host', null);
                    $this->setUri($location);
                } else {
                    throw new EasyRdf_Exception(
                        "Failed to parse Location header returned by ".
                        $this->_uri
                    );
                }
                ++$this->_redirectCounter;

            } else {
                // If we didn't get any location, stop redirecting
                break;
            }


        } while ($this->_redirectCounter < $this->_config['maxredirects']);
        
        return $response;
    }



    /**
     * Prepare the request headers
     *
     * @return array
     */
    protected function _prepareHeaders($host, $port)
    {
        $headers = array();

        // Set the host header
        if (! isset($this->_headers['host'])) {
            // If the port is not default, add it
            if ($port != 80) {
                $host .= ':' . $port;
            }
            $headers[] = "Host: {$host}";
        }

        // Set the connection header
        if (! isset($this->_headers['connection'])) {
            $headers[] = "Connection: close";
        }

        // Set the user agent header
        if (! isset($this->_headers['user-agent'])) {
            $headers[] = "User-Agent: {$this->_config['useragent']}";
        }

        // Add all other user defined headers
        foreach ($this->_headers as $header) {
            list($name, $value) = $header;
            if (is_array($value)) {
                $value = implode(', ', $value);
            }

            $headers[] = "$name: $value";
        }

        return $headers;
    }
}
