<?php

require_once "EasyRdf/Http/Response.php";


class EasyRdf_Http_Client
{
    protected $uri = null;
    protected $config = array(
        'maxredirects'    => 5,
        'useragent'       => 'EasyRdf_Http_Client',
        'timeout'         => 10
    );
    protected $headers = array();
    protected $redirectCounter = 0;

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

        $this->uri = $uri;

        return $this;
    }

    public function getUri($as_string = true)
    {
        return $this->uri;
    }

    public function setConfig($config = array())
    {
        if (! is_array($config)) {
            // FIXME: throw exception
            return null;
        }

        foreach ($config as $k => $v)
            $this->config[strtolower($k)] = $v;

        return $this;
    }
    
    public function setHeaders($name, $value = null)
    {
        $normalized_name = strtolower($name);

        // If $value is null or false, unset the header
        if ($value === null || $value === false) {
            unset($this->headers[$normalized_name]);

        // Else, set the header
        } else {
            $this->headers[$normalized_name] = array($name, $value);
        }

        return $this;
    }

    public function getHeader($key)
    {
        $key = strtolower($key);
        if (isset($this->headers[$key])) {
            return $this->headers[$key][1];
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
        return $this->redirectCounter;
    }

    public function request($method = 'GET')
    {
        if (!$this->uri) {
            // FIXME: throw exception
            return null;
        }

        $this->redirectCounter = 0;
        $response = null;

        // Send the first request. If redirected, continue.
        do {
            // Clone the URI and add the additional GET parameters to it
            $uri = parse_url($this->uri);
            $host = $uri['host'];
            $port = $uri['port'];
            if (!$port) $port = 80;
            $headers = $this->_prepareHeaders($host, $port);

            // Open socket to remote server
            $socket = fsockopen( $host, $port, $errno, $errstr, $this->config['timeout'] );
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
            $response = '';
            while (!feof($socket)) {
                $response .= fgets($socket);
            }
            
            // Close the socket
            fclose($socket);

            // Parse the response string
            $response = EasyRdf_Http_Response::fromString($response);
 
            // If we got redirected, look for the Location header
            if ($response->isRedirect() && ($location = $response->getHeader('location'))) {

                // If we got a well formed absolute URI
                if (parse_url($location)) {
                    $this->setHeaders('host', null);
                    $this->setUri($location);
                } else {
                    // FIXME: throw exception?
                    break;
                }
                ++$this->redirectCounter;

            } else {
                // If we didn't get any location, stop redirecting
                break;
            }


        } while ($this->redirectCounter < $this->config['maxredirects']);

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
        if (! isset($this->headers['host'])) {
            // If the port is not default, add it
            if ($port != 80) {
                $host .= ':' . $port;
            }
            $headers[] = "Host: {$host}";
        }

        // Set the accept header
        if (! isset($this->headers['accept'])) {
            $headers[] = "Accept: application/rdf+xml";
        }

        // Set the connection header
        if (! isset($this->headers['connection'])) {
            $headers[] = "Connection: close";
        }

        // Set the user agent header
        if (! isset($this->headers['user-agent'])) {
            $headers[] = "User-Agent: {$this->config['useragent']}";
        }

        // Add all other user defined headers
        foreach ($this->headers as $header) {
            list($name, $value) = $header;
            if (is_array($value)) {
                $value = implode(', ', $value);
            }

            $headers[] = "$name: $value";
        }

        return $headers;
    }
}
