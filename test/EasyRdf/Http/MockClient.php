<?php

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2011-2013 Nicholas J Humfrey.  All rights reserved.
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
 * @copyright  Copyright (c) 2009-2013 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */

class EasyRdf_Http_MockClient extends EasyRdf_Http_Client
{
    private $mocks = array();

    public function request($method = null)
    {
        if ($method) {
            $this->setMethod($method);
        }

        $uri = parse_url($this->getUri());
        $params = $this->getParametersGet();
        if (!empty($params)) {
            if (!empty($uri['query'])) {
                $uri['query'] .= '&';
            } else {
                $uri['query'] = '';
            }
            $uri['query'] .= http_build_query($params, null, '&');
        }

        # Try and find a matching response
        $n = sizeof($this->mocks);
        for ($i = 0; $i < $n; $i++) {
            list($m, $response, $once) = $this->mocks[$i];
            if (isset($m['uri']) && !$this->matchUri($m['uri'], $uri)) {
                continue;
            } elseif (isset($m['method']) && $m['method'] !== $this->getMethod()) {
                continue;
            } elseif (isset($m['callback'])) {
                $args = array_merge($m['callbackArgs'], array($this));
                $test = call_user_func_array($m['callback'], $args);
                if (!$test) {
                    continue;
                }
            }
            if ($once) {
                array_splice($this->mocks, $i, 1);
            }
            return $response;
        }

        # FIXME: change to a different type of exception?
        throw new EasyRdf_Exception(
            'Unexpected request: ' . $this->getMethod() . ' ' . $this->getUri()
        );
    }

    public function addMock($method, $uri, $body, $options = array())
    {
        $match = array();
        $match['method'] = $method;
        $match['uri'] = array();
        if (isset($options['callback'])) {
            $match['callback'] = $options['callback'];
            if (isset($options['callbackArgs'])) {
                $match['callbackArgs'] = $options['callbackArgs'];
            } else {
                $match['callbackArgs'] = array();
            }
        }
        if (!isset($uri)) {
            $match['uri'] = null;
        } else {
            $match['uri'] = strval($uri);
        }

        if ($body instanceof EasyRdf_Http_Response) {
            $response = $body;
        } else {
            if (isset($options['status'])) {
                $status = $options['status'];
            } else {
                $status = 200;
            }
            if (isset($options['headers'])) {
                $headers = $options['headers'];
            } else {
                $headers = array();
                $format = EasyRdf_Format::guessFormat($body);
                if (isset($format)) {
                    $headers['Content-Type'] = $format->getDefaultMimeType();
                }
                if (isset($body)) {
                    $headers['Content-Length'] = strlen($body);
                }
            }
            $response = new EasyRdf_Http_Response($status, $headers, $body);
        }
        $once = isset($options['once']) ? $options['once'] : false;

        $this->mocks[] = array($match, $response, $once);
    }

    public function addMockOnce($method, $uri, $body, $options = array())
    {
        $options = array('once' => true) + $options;
        return $this->addMock($method, $uri, $body, $options);
    }

    public function addMockRedirect($method, $uri, $location, $status = 302, $options = array())
    {
        $options = array('status' => $status, 'headers' => array('Location' => $location)) + $options;
        $body = "$status redirect to $location";
        return $this->addMock($method, $uri, $body, $options);
    }

    protected function buildUrl($parts)
    {
        $url = $parts['scheme'] . '://';
        $url .= $parts['host'];
        if (isset($parts['port'])) {
            $url .= ':' . $parts['port'];
        }
        if (isset($parts['path'])) {
            $url .= $parts['path'];
        } else {
            $url .= '/';
        }
        if (isset($parts['query'])) {
            $url .= '?' . $parts['query'];
        }
        return $url;
    }

    private function matchUri($match, $parts)
    {
        # FIXME: Ugh, this is nasty
        $url = $this->buildUrl($parts);
        if ($match == $url) {
            return true;
        } else {
            if (isset($parts['query'])) {
                return ($match == $parts['path'].'?'.$parts['query']);
            } else {
                return ($match == $parts['path']);
            }
        }
    }
}
