<?php

require_once realpath(dirname(__FILE__) . '/../../') . '/TestHelper.php';


class EasyRdf_Http_ClientTest extends EasyRdf_TestCase
{
    /**
     * Common HTTP client
     *
     * @var EasyRdf_Http_Client
     */
    protected $_client = null;

    /**
     * Set up the test suite before each test
     *
     */
    public function setUp()
    {
        $this->_client = new EasyRdf_Http_Client('http://www.example.com/');
    }

    public function testConstructWithConfig()
    {
        $client = new EasyRdf_Http_Client(
            'http://www.example.com/',
            array('foo' => 'bar')
        );
        $this->assertEquals('EasyRdf_Http_Client', get_class($client));
    }

    /**
     * Test we can SET and GET a URI as string
     *
     */
    public function testSetGetUriString()
    {
        $uristr = 'http://www.bbc.co.uk:80/';
        $this->_client->setUri($uristr);

        $this->assertEquals(
            $uristr,
            $this->_client->getUri(),
            'Returned Uri object does not hold the expected URI'
        );

        $uri = $this->_client->getUri(true);
        $this->assertTrue(
            is_string($uri),
            'Returned value expected to be a string, ' .
            gettype($uri) . ' returned'
        );
        $this->assertEquals(
            $uri, $uristr,
            'Returned string is not the expected URI'
        );
    }

    public function testSetGetUriHttpsString()
    {
        $uristr = 'https://example.com/';
        $this->_client->setUri($uristr);

        $this->assertStringEquals(
            $uristr,
            $this->_client->getUri(),
            'Returned Uri object does not hold the expected URI'
        );
    }

    public function testSetGopherUri()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            "EasyRdf_Http_Client only supports the 'http' and 'https' schemes."
        );
        $uristr = 'gopher://g.example.com/';
        $this->_client->setUri($uristr);
    }

    /**
     * Test we can SET and GET a URI as object
     *
     */
    public function testSetGetUriObject()
    {
        $uristr = 'http://www.bbc.co.uk:80/';
        $obj = new EasyRdf_Resource($uristr);
        $this->_client->setUri($obj);

        $uri = $this->_client->getUri();
        $this->assertEquals($uristr, $uri);
    }

    public function testSetConfig()
    {
        $result = $this->_client->setConfig(array('foo' => 'bar'));
        $this->assertEquals('EasyRdf_Http_Client', get_class($result));
    }

    public function testSetConfigNull()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$config should be an array and cannot be null'
        );
        $result = $this->_client->setConfig(null);
    }

    public function testSetConfigNonArray()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$config should be an array and cannot be null'
        );
        $result = $this->_client->setConfig('foo');
    }

    /**
     * Test we can get already set headers
     *
     */
    public function testGetHeader()
    {
        $this->_client->setHeaders('Accept-language', 'en,de,*');
        $this->assertEquals(
            $this->_client->getHeader('Accept-language'),
            'en,de,*',
            'Returned value of header is not as expected'
        );
        $this->assertEquals(
            $this->_client->getHeader('X-Fake-Header'),
            null,
            'Non-existing header should not return a value'
        );
    }

    public function testUnsetHeader()
    {
        $this->_client->setHeaders('Accept-Encoding', 'gzip,deflate');
        $this->_client->setHeaders('Accept-Encoding', null);
        $this->assertNull(
            $this->_client->getHeader('Accept-encoding'),
            'Returned value of header is expected to be null'
        );
    }

    public function testSetGetMethod()
    {
        $this->_client->setMethod('POST');
        $method = $this->_client->getMethod();
        $this->assertEquals('POST', $method);
    }

    public function testSetNonStringMethod()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Invalid HTTP request method.'
        );
        $this->_client->setMethod($this);
    }

    public function testSetNumericMethod()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Invalid HTTP request method.'
        );
        $this->_client->setMethod(1234);
    }

    public function testRedirectionCounterShouldStartAtZero()
    {
        $this->_client->setHeaders('Accept-Encoding', null);
        $this->assertEquals(0, $this->_client->getRedirectionsCount());
    }

    public function testSetParameterGet()
    {
        $this->_client->setParameterGet('key1', 'value1');
        $this->assertEquals('value1', $this->_client->getParameterGet('key1'));
    }

    public function testUnSetParameterGet()
    {
        $this->_client->setParameterGet('key1', 'value1');
        $this->_client->setParameterGet('key2', 'value2');
        $this->_client->setParameterGet('key1', null);
        $this->assertEquals(null, $this->_client->getParameterGet('key1'));
        $this->assertEquals('value2', $this->_client->getParameterGet('key2'));
    }

    public function testSetRawData()
    {
        $this->_client->setRawData('Foo Bar');
        $this->assertEquals('Foo Bar', $this->_client->getRawData());
    }

    public function testRequestNoUri()
    {
        $client = new EasyRdf_Http_Client();
        $this->setExpectedException(
            'EasyRdf_Exception',
            'Set URI before calling EasyRdf_Http_Client->request()'
        );
        $client->request();
    }

    public function testResetParameters()
    {
        $this->_client->setMethod('POST');
        $this->_client->setRawData('Foo Bar');
        $this->_client->setHeaders('Content-Length', 7);
        $this->_client->setHeaders('Content-Type', 'text/plain');
        $this->_client->setHeaders('Accept-Language', 'en');
        $this->_client->resetParameters();
        $this->assertEquals('GET', $this->_client->getMethod());
        $this->assertEquals(null, $this->_client->getRawData());
        $this->assertEquals(null, $this->_client->getHeader('Content-Length'));
        $this->assertEquals(null, $this->_client->getHeader('Content-Type'));
        $this->assertEquals('en', $this->_client->getHeader('Accept-Language'));
    }

    public function testResetParametersClearAll()
    {
        $this->_client->setMethod('POST');
        $this->_client->setRawData('Foo Bar');
        $this->_client->setHeaders('Content-Length', 7);
        $this->_client->setHeaders('Content-Type', 'text/plain');
        $this->_client->setHeaders('Accept-Language', 'en');
        $this->_client->resetParameters(true);
        $this->assertEquals('GET', $this->_client->getMethod());
        $this->assertEquals(null, $this->_client->getRawData());
        $this->assertEquals(null, $this->_client->getHeader('Content-Length'));
        $this->assertEquals(null, $this->_client->getHeader('Content-Type'));
        $this->assertEquals(null, $this->_client->getHeader('Accept-Language'));
    }

}
