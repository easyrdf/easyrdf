<?php

require_once realpath(dirname(__FILE__) . '/../../') . '/TestHelper.php';

require_once 'EasyRdf/Http/Client.php';


class EasyRdf_Http_ClientTest extends PHPUnit_Framework_TestCase
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
        $this->_client = new EasyRdf_Http_Client('http://www.example.com');
    }

    /**
     * URI Tests
     */

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

    /**
     * Test we can SET and GET a URI as object
     *
     */
    public function testSetGetUriObject()
    {
      $uristr = 'http://www.bbc.co.uk:80/';
        $this->_client->setUri('http://www.bbc.co.uk:80/');

        $uri = $this->_client->getUri();
        $this->assertEquals($uristr, $uri);
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

}
