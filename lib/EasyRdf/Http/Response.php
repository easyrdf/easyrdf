<?php

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009 Nicholas J Humfrey.
 * Copyright (c) 2005-2009 Zend Technologies USA Inc.
 * All rights reserved.
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
 * Class that represents an HTTP 1.0 / 1.1 response message.
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */
class EasyRdf_Http_Response
{
    private $_status;
    private $_message;
    private $_headers = array();
    private $_body;
 
    public function __construct(
        $status, $headers, $body = null,
        $version = '1.1', $message = null
    )
    {
        $this->_status = $status;
        $this->_headers = $headers;
        $this->_body = $body;
        $this->_version = $version;
        $this->_message = $message;
    }

    public function isSuccessful()
    {
        return ($this->_status >= 200 && $this->_status < 300);
    }
    
    public function isError()
    {
        return ($this->_status >= 400 && $this->_status < 600);
    }
    
    public function isRedirect()
    {
        return ($this->_status >= 300 && $this->_status < 400);
    }
    
    public function getStatus()
    {
        return $this->_status;
    }
    
    public function getMessage()
    {
        return $this->_message;
    }
    
    public function getBody()
    {
        // Decode the body if it was transfer-encoded
        switch (strtolower($this->getHeader('transfer-encoding'))) {
            // Handle chunked body
            case 'chunked':
                return self::decodeChunkedBody($this->_body);
                break;

            // No transfer encoding, or unknown encoding extension:
            // return body as is
            default:
                return $this->_body;
                break;
        }
    }
    
    public function getVersion()
    {
        return $this->_version;
    }
    
    public function getHeaders()
    {
        return $this->_headers;
    }
    
    public function getHeader($header)
    {
        $header = ucwords(strtolower($header));
        if (array_key_exists($header, $this->_headers)) {
            return $this->_headers[$header];
        } else {
            return null;
        }
    }

    /**
     * Create an EasyRdf_Http_Response object from a HTTP response string
     *
     * @param string $responseStr
     * @return EasyRdf_Http_Response
     */
    public static function fromString($responseStr)
    {
        // First, split body and headers
        $matches = preg_split(  '|(?:\r?\n){2}|m', $responseStr, 2);
        if ($matches and sizeof($matches) == 2) {
            list ($headerLines, $body) = $matches;
        } else {
            throw new EasyRdf_Exception(
                "Failed to parse HTTP response."
            );
        }
        
        // Split headers part to lines
        $headerLines = preg_split('|[\r\n]+|m', $headerLines);
        $status = array_shift($headerLines);
        if (preg_match("|^HTTP/([\d\.x]+) (\d+) ([^\r\n]+)|", $status, $m)) {
            $version = $m[1];
            $status = $m[2];
            $message = $m[3];
        } else {
            throw new EasyRdf_Exception(
                "Failed to parse HTTP response status line."
            );
        }
        
        // Process the rest of the header lines
        $headers = array();
        foreach ($headerLines as $line) {
            if (preg_match("|^([\w-]+):\s+(.+)$|", $line, $m)) {
                $hName = ucwords(strtolower($m[1]));
                $hValue = $m[2];

                if (isset($headers[$hName])) {
                    if (! is_array($headers[$hName])) {
                        $headers[$hName] = array($headers[$hName]);
                    }
                    $headers[$hName][] = $hValue;
                } else {
                    $headers[$hName] = $hValue;
                }
            }
        }

        return new EasyRdf_Http_Response(
            $status, $headers, $body, $version, $message
        );
    }


    /**
     * Decode a "chunked" transfer-encoded body and return the decoded text
     *
     * @param string $body
     * @return string
     */
    public static function decodeChunkedBody($body)
    {
        $decBody = '';
        
        while (trim($body)) {
            if (preg_match("/^([\da-fA-F]+)[^\r\n]*\r\n/sm", $body, $m)) {
                $length = hexdec(trim($m[1]));
                $cut = strlen($m[0]);
                $decBody .= substr($body, $cut, $length);
                $body = substr($body, $cut + $length + 2);
            } else {
                throw new EasyRdf_Exception(
                    "Failed to decode chunked body in HTTP response."
                );
            }
        }

        return $decBody;
    }
}
