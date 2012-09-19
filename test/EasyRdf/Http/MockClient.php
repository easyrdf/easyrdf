<?php

class EasyRdf_Http_MockClient extends EasyRdf_Http_Client
{
    private $_mocks = array();

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
        $n = sizeof($this->_mocks);
        for ($i = 0; $i < $n; $i++) {
            list($m, $response, $once) = $this->_mocks[$i];
            if (isset($m['uri']) && !$this->_matchUri($m['uri'], $uri)) {
                continue;
            } else if (isset($m['method']) && $m['method'] !== $this->getMethod()) {
                continue;
            } else if (isset($m['callback'])) {
                $args = array_merge($m['callbackArgs'], array($this));
                $test = call_user_func_array($m['callback'], $args);
                if (!$test) {
                    continue;
                }
            }
            if ($once) {
                array_splice($this->_mocks, $i, 1);
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

        $this->_mocks[] = array($match, $response, $once);
    }

    public function addMockOnce($method, $uri, $body, $options = array())
    {
        $options = array('once' => true) + $options;
        return $this->addMock($method, $uri, $body, $options);
    }

    public function addMockRedirect($method, $uri, $location, $options = array())
    {
        $options = array('status' => 302, 'headers' => array('Location' => $location)) + $options;
        $body = "Redirecting to $location";
        return $this->addMock($method, $uri, $body, $options);
    }

    protected function _buildUrl($parts)
    {
        $url = $parts['scheme'] . '://';
        $url .= $parts['host'];
        if (isset($parts['port']))
            $url .= ':' . $parts['port'];
        if (isset($parts['path']))
            $url .= $parts['path'];
        else
            $url .= '/';
        if (isset($parts['query']))
            $url .= '?' . $parts['query'];
        return $url;
    }

    private function _matchUri($match, $parts)
    {
        # FIXME: Ugh, this is nasty
        $url = $this->_buildUrl($parts);
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
