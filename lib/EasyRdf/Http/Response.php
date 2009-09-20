<?php


class EasyRdf_Http_Response
{
    protected $_status;
    protected $_message;
    protected $_headers = array();
    protected $_body;
 
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
        list ($headerLines, $body) = preg_split(
            '|(?:\r?\n){2}|m',
            $responseStr, 2
        );
        # FIXME: throw exception if something is wrong
        
        // Split headers part to lines
        $headerLines = preg_split('|[\r\n]+|m', $headerLines);
        $status = array_shift($headerLines);
        if (preg_match("|^HTTP/([\d\.x]+) (\d+) ([^\r\n]+)|", $status, $m)) {
            $version = $m[1];
            $status = $m[2];
            $message = $m[3];
        } else {
            # FIXME: throw exception
            return null;
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
                # FIXME: throw exception            
            }
        }

        return $decBody;
    }
}
