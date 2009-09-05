<?php


class EasyRdf_Http_Response
{
    protected $status;
    protected $message;
    protected $headers = array();
    protected $body;
 
    public function __construct($status, $headers, $body = null, $version = '1.1', $message = null)
    {
        $this->status = $status;
        $this->headers = $headers;
        $this->body = $body;
        $this->version = $version;
        $this->message = $message;
    }

    public function isSuccessful()
    {
        return ($this->status >= 200 && $this->status < 300);
    }
    
    public function isError()
    {
        return ($this->status >= 400 && $this->status < 600);
    }
    
    public function isRedirect()
    {
        return ($this->status >= 300 && $this->status < 400);
    }
    
    public function getStatus()
    {
        return $this->status;
    }
    
    public function getMessage()
    {
        return $this->message;
    }
    
    public function getBody()
    {
        // Decode the body if it was transfer-encoded
        switch (strtolower($this->getHeader('transfer-encoding'))) {
            // Handle chunked body
            case 'chunked':
                return self::decodeChunkedBody($this->body);

            // No transfer encoding, or unknown encoding extension:
            // return body as is
            default:
                return $this->body;
        }
    }
    
    public function getVersion()
    {
        return $this->version;
    }
    
    public function getHeaders()
    {
        return $this->headers;
    }
    
    public function getHeader($header)
    {
        $header = ucwords(strtolower($header));
        return $this->headers[$header];
    }

    /**
     * Create an EasyRdf_Http_Response object from a HTTP response string
     *
     * @param string $response_str
     * @return EasyRdf_Http_Response
     */
    public static function fromString($response_str)
    {
        // First, split body and headers
        list ($header_lines, $body) = preg_split('|(?:\r?\n){2}|m', $response_str, 2);
        # FIXME: throw exception if something is wrong
        
        // Split headers part to lines
        $header_lines = preg_split('|[\r\n]+|m', $header_lines);
        $status_line = array_shift( $header_lines );
        if (preg_match("|^HTTP/([\d\.x]+) (\d+) ([^\r\n]+)|", $status_line, $m)) {
            $version = $m[1];
            $status = $m[2];
            $message = $m[3];
        } else {
            # FIXME: throw exception
            return null;
        }
        
        // Process the rest of the header lines
        $headers = array();
        foreach($header_lines as $line) {
            if (preg_match("|^([\w-]+):\s+(.+)$|", $line, $m)) {
                $h_name = ucwords(strtolower($m[1]));
                $h_value = $m[2];

                if (isset($headers[$h_name])) {
                    if (! is_array($headers[$h_name])) {
                        $headers[$h_name] = array($headers[$h_name]);
                    }
                    $headers[$h_name][] = $h_value;
                } else {
                    $headers[$h_name] = $h_value;
                }
            }
        }

        return new EasyRdf_Http_Response($status, $headers, $body, $version, $message);
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
                # FIXME: throw exception            
            }
        }

        return $decBody;
    }
}
