<?php

require_once realpath(dirname(__FILE__) . '/../../') . '/TestHelper.php';


class EasyRdf_Http_ClientTest extends EasyRdf_TestCase
{
    /**
     * Common HTTP client
     *
     * @var EasyRdf_Http_Client
     */
    protected $client = null;

    /**
     * Set up the test suite before each test
     *
     */
    public function setUp()
    {
        $this->client = new EasyRdf_Http_Client('http://www.example.com/');
    }

    public function testConstructWithConfig()
    {
        $client = new EasyRdf_Http_Client(
            'http://www.example.com/',
            array('foo' => 'bar')
        );
        $this->assertClass('EasyRdf_Http_Client', $client);
    }

    /**
     * Test we can SET and GET a URI as string
     *
     */
    public function testSetGetUriString()
    {
        $uristr = 'http://www.bbc.co.uk:80/';
        $this->client->setUri($uristr);

        $this->assertSame(
            $uristr,
            $this->client->getUri(),
            'Returned Uri object does not hold the expected URI'
        );

        $uri = $this->client->getUri(true);
        $this->assertTrue(
            is_string($uri),
            'Returned value expected to be a string, ' .
            gettype($uri) . ' returned'
        );
        $this->assertSame(
            $uri,
            $uristr,
            'Returned string is not the expected URI'
        );
    }

    public function testSetGetUriHttpsString()
    {
        $uristr = 'https://example.com/';
        $this->client->setUri($uristr);

        $this->assertStringEquals(
            $uristr,
            $this->client->getUri(),
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
        $this->client->setUri($uristr);
    }

    /**
     * Test we can SET and GET a URI as object
     *
     */
    public function testSetGetUriObject()
    {
        $uristr = 'http://www.bbc.co.uk:80/';
        $obj = new EasyRdf_Resource($uristr);
        $this->client->setUri($obj);

        $uri = $this->client->getUri();
        $this->assertSame($uristr, $uri);
    }

    public function testSetConfig()
    {
        $result = $this->client->setConfig(array('foo' => 'bar'));
        $this->assertClass('EasyRdf_Http_Client', $result);
    }

    public function testSetConfigNull()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$config should be an array and cannot be null'
        );
        $result = $this->client->setConfig(null);
    }

    public function testSetConfigNonArray()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$config should be an array and cannot be null'
        );
        $result = $this->client->setConfig('foo');
    }

    /**
     * Test we can get already set headers
     *
     */
    public function testGetHeader()
    {
        $this->client->setHeaders('Accept-language', 'en,de,*');
        $this->assertSame(
            $this->client->getHeader('Accept-language'),
            'en,de,*',
            'Returned value of header is not as expected'
        );
        $this->assertSame(
            $this->client->getHeader('X-Fake-Header'),
            null,
            'Non-existing header should not return a value'
        );
    }

    public function testUnsetHeader()
    {
        $this->client->setHeaders('Accept-Encoding', 'gzip,deflate');
        $this->client->setHeaders('Accept-Encoding', null);
        $this->assertNull(
            $this->client->getHeader('Accept-encoding'),
            'Returned value of header is expected to be null'
        );
    }

    public function testSetGetMethod()
    {
        $this->client->setMethod('POST');
        $method = $this->client->getMethod();
        $this->assertSame('POST', $method);
    }

    public function testSetNonStringMethod()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Invalid HTTP request method.'
        );
        $this->client->setMethod($this);
    }

    public function testSetNumericMethod()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Invalid HTTP request method.'
        );
        $this->client->setMethod(1234);
    }

    public function testRedirectionCounterShouldStartAtZero()
    {
        $this->client->setHeaders('Accept-Encoding', null);
        $this->assertSame(0, $this->client->getRedirectionsCount());
    }

    public function testSetParameterGet()
    {
        $this->client->setParameterGet('key1', 'value1');
        $this->assertSame('value1', $this->client->getParameterGet('key1'));
    }

    public function testUnSetParameterGet()
    {
        $this->client->setParameterGet('key1', 'value1');
        $this->client->setParameterGet('key2', 'value2');
        $this->client->setParameterGet('key1', null);
        $this->assertSame(null, $this->client->getParameterGet('key1'));
        $this->assertSame('value2', $this->client->getParameterGet('key2'));
    }

    public function testSetRawData()
    {
        $this->client->setRawData('Foo Bar');
        $this->assertSame('Foo Bar', $this->client->getRawData());
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
        $this->client->setMethod('POST');
        $this->client->setRawData('Foo Bar');
        $this->client->setHeaders('Content-Length', 7);
        $this->client->setHeaders('Content-Type', 'text/plain');
        $this->client->setHeaders('Accept-Language', 'en');
        $this->client->resetParameters();
        $this->assertSame('GET', $this->client->getMethod());
        $this->assertSame(null, $this->client->getRawData());
        $this->assertSame(null, $this->client->getHeader('Content-Length'));
        $this->assertSame(null, $this->client->getHeader('Content-Type'));
        $this->assertSame('en', $this->client->getHeader('Accept-Language'));
    }

    public function testResetParametersClearAll()
    {
        $this->client->setMethod('POST');
        $this->client->setRawData('Foo Bar');
        $this->client->setHeaders('Content-Length', 7);
        $this->client->setHeaders('Content-Type', 'text/plain');
        $this->client->setHeaders('Accept-Language', 'en');
        $this->client->resetParameters(true);
        $this->assertSame('GET', $this->client->getMethod());
        $this->assertSame(null, $this->client->getRawData());
        $this->assertSame(null, $this->client->getHeader('Content-Length'));
        $this->assertSame(null, $this->client->getHeader('Content-Type'));
        $this->assertSame(null, $this->client->getHeader('Accept-Language'));
    }
}
