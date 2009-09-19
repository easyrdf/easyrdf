<?php

require_once "EasyRdf/Http/Response.php";


class EasyRdf_Http_Client
{
    protected $_uri = null;
    protected $_config = array(
        'maxredirects'    => 5,
        'useragent'       => 'EasyRdf_Http_Client',
        'timeout'         => 10
    );
    protected $_headers = array();
    protected $_redirectCounter = 0;

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
        if (! is_array($config)) {
            // FIXME: throw exception
            return null;
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
            // FIXME: throw exception
            return null;
        }

        $this->_redirectCounter = 0;
        $response = null;

        // Send the first request. If redirected, continue.
        do {
            // Clone the URI and add the additional GET parameters to it
            $uri = parse_url($this->_uri);
            $host = $uri['host'];
            $port = $uri['port'];
            if (!$port) $port = 80;
            $headers = $this->_prepareHeaders($host, $port);

            // Open socket to remote server
            $socket = fsockopen(
                $host, $port, $errno, $errstr, $this->_config['timeout']
            );
            if (!$socket) {
                // FIXME: throw exception            
                return null;
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
                    // FIXME: throw exception?
                    break;
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
