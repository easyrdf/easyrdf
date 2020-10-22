<?php
namespace EasyRdf\Http;

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009-2014 Nicholas J Humfrey.  All rights reserved.
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
 * @copyright  Copyright (c) 2009-2014 Nicholas J Humfrey
 * @license    https://www.opensource.org/licenses/bsd-license.php
 */

use EasyRdf\Resource;
use EasyRdf\TestCase;

require_once realpath(dirname(__FILE__) . '/../../') . '/TestHelper.php';


class ClientTest extends TestCase
{
    /**
     * Common HTTP client
     *
     * @var Client
     */
    protected $client = null;

    /**
     * Set up the test suite before each test
     *
     */
    public function setUp()
    {
        $this->client = new Client('http://www.example.com/');
    }

    public function testConstructWithConfig()
    {
        $client = new Client(
            'http://www.example.com/',
            array('foo' => 'bar')
        );
        $this->assertClass('EasyRdf\Http\Client', $client);
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
            "Client only supports the 'http' and 'https' schemes."
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
        $obj = new Resource($uristr);
        $this->client->setUri($obj);

        $uri = $this->client->getUri();
        $this->assertSame($uristr, $uri);
    }

    public function testSetConfig()
    {
        $result = $this->client->setConfig(array('foo' => 'bar'));
        $this->assertClass('EasyRdf\Http\Client', $result);
    }

    public function testSetConfigNull()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$config should be an array and cannot be null'
        );
        $this->client->setConfig(null);
    }

    public function testSetConfigNonArray()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$config should be an array and cannot be null'
        );
        $this->client->setConfig('foo');
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
        $client = new Client();
        $this->setExpectedException(
            'EasyRdf\Exception',
            'Set URI before calling Client->request()'
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

    /**
     * Test for issue #271
     *
     * Test approach was to first trigger the error with the following code and
     * deploy the fix afterwards.
     *
     * Error:
     *    Undefined index: path in vendor\easyrdf\easyrdf\lib\Http\Client.php
     *
     * @see https://github.com/easyrdf/easyrdf/issues/271
     */
    public function testIssue271()
    {
        $this->client = new Client('https://query.wikidata.org?query=');
        $response = $this->client->request('GET');
        $this->assertTrue($response->isSuccessful());
    }
}
