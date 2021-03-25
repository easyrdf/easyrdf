<?php
namespace EasyRdf\Http;

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) Nicholas J Humfrey.
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
 * @copyright  Copyright (c) Nicholas J Humfrey
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc.
 * @license    https://www.opensource.org/licenses/bsd-license.php
 */
use Psr\Http\Message\ResponseInterface;

/**
 * Class that represents an HTTP 1.0 / 1.1 response message.
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) Nicholas J Humfrey
 *             Copyright (c) 2005-2009 Zend Technologies USA Inc.
 * @license    https://www.opensource.org/licenses/bsd-license.php
 */
class Response
{

    /**
     * The wrapped ResponseInterface
     *
     * @var Psr\Http\Message\ResponseInterface
     */
    private $response;

    /**
     * Wraps a ResponseInterface, for convenience in internal API.
     *
     * @param  Psr\Http\Message\ResponseInterface  $response HTTP response
     */
    public function __construct($response) {
        $this->response = $response;
    }

    /**
     * Check whether the response in successful
     *
     * @return boolean
     */
    public function isSuccessful()
    {
        return ($this->getStatus() >= 200 && $this->getStatus() < 300);
    }

    /**
     * Check whether the response is an error
     *
     * @return boolean
     */
    public function isError()
    {
        return ($this->getStatus() >= 400 && $this->getStatus() < 600);
    }

    /**
     * Check whether the response is a redirection
     *
     * @return boolean
     */
    public function isRedirect()
    {
        return ($this->getStatus() >= 300 && $this->getStatus() < 400);
    }

    /**
     * Get the HTTP response status code
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->response->getStatusCode();
    }

    /**
     * Return a message describing the HTTP response code
     * (Eg. "OK", "Not Found", "Moved Permanently")
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->response->getReasonPhrase();
    }

    /**
     * Get the response body as string
     *
     * @return string
     */
    public function getBody()
    {
        return (string) $this->response->getBody();
    }

    /**
     * Get the HTTP version of the response
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->response->getProtocolVersion();
    }

    /**
     * Get the response headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->response->getHeaders();
    }

    /**
     * Get a specific header as string, or null if it is not set
     *
     * @param string $header
     *
     * @return string|array|null
     */
    public function getHeader($header)
    {
        if ($this->response->hasHeader($header)) {
            return $this->response->getHeaderLine($header);
        }

        return null;
    }

    /**
     * Get all headers as string
     *
     * @param boolean $statusLine Whether to return the first status line (ie "HTTP 200 OK")
     * @param string  $br         Line breaks (eg. "\n", "\r\n", "<br />")
     *
     * @return string
     */
    public function getHeadersAsString($statusLine = true, $br = "\n")
    {
        $str = '';

        if ($statusLine) {
            $str = "HTTP/{$this->getVersion()} {$this->getStatus()} {$this->getMessage()}{$br}";
        }

        // Iterate over the headers and stringify them
        foreach ($this->getHeaders() as $name => $value) {
            if (is_string($value)) {
                $str .= "{$name}: {$value}{$br}";
            } elseif (is_array($value)) {
                foreach ($value as $subval) {
                    $str .= "{$name}: {$subval}{$br}";
                }
            }
        }

        return $str;
    }

    /**
     * Get the entire response as string
     *
     * @param string $br Line breaks (eg. "\n", "\r\n", "<br />")
     *
     * @return string
     */
    public function asString($br = "\n")
    {
        return $this->getHeadersAsString(true, $br) . $br . $this->getBody();
    }

    /**
     * Implements magic __toString()
     *
     * @return string
     */
    public function __toString()
    {
        return $this->asString();
    }
}
