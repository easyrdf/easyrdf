<?php
namespace EasyRdf\Http;

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) Nicholas J Humfrey.  All rights reserved.
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
 * @copyright  Copyright (c) Nicholas J Humfrey
 * @license    https://www.opensource.org/licenses/bsd-license.php
 */

/**
 * Test helper
 */
use EasyRdf\TestCase;

require_once realpath(__DIR__ . '/../../') . '/TestHelper.php';

class ResponseTest extends TestCase
{
    public function testGetVersion()
    {
        $response = Response::fromString(
            readFixture('http_response_200')
        );
        $this->assertSame(
            '1.1',
            $response->getVersion(),
            'Version is expected to be 1.1'
        );
    }

    public function testGetMessage()
    {
        $response = Response::fromString(
            readFixture('http_response_200')
        );
        $this->assertSame(
            'OK',
            $response->getMessage(),
            'Message is expected to be OK'
        );
    }

    public function testGetBody()
    {
        $response = Response::fromString(
            readFixture('http_response_200')
        );
        $this->assertSame(
            "Hello World\n",
            $response->getBody()
        );
    }

    public function testInvalidResponse()
    {
        $this->setExpectedException(
            'EasyRdf\Exception',
            'Failed to parse HTTP response.'
        );
        Response::fromString('foobar');
    }

    public function testInvalidStatusLine()
    {
        $this->setExpectedException(
            'EasyRdf\Exception',
            'Failed to parse HTTP response status line.'
        );
        Response::fromString(
            "HTTP1.0 200 OK\r\nConnection: close\r\n\r\nBody"
        );
    }

    public function testGzipResponse()
    {
        $res = Response::fromString(readFixture('response_gzip'));

        $this->assertEquals('gzip', strtolower($res->getHeader('content-encoding')));
        $this->assertEquals('0b13cb193de9450aa70a6403e2c9902f', md5($res->getBody()));
        $this->assertEquals('f24dd075ba2ebfb3bf21270e3fdc5303', md5($res->getRawBody()));
    }

    public function testDeflateResponse()
    {
        $res = Response::fromString(readFixture('response_deflate'));

        $this->assertEquals('deflate', strtolower($res->getHeader('content-encoding')));
        $this->assertEquals('0b13cb193de9450aa70a6403e2c9902f', md5($res->getBody()));
        $this->assertEquals('ad62c21c3aa77b6a6f39600f6dd553b8', md5($res->getRawBody()));
    }

    /**
     * Make sure wer can handle non-RFC compliant "deflate" responses.
     *
     * Unlike a standard 'deflate' response, those do not contain the zlib header
     * and trailer. Unfortunately some buggy servers (read: IIS) send those and
     * we need to support them.
     *
     * @link http://framework.zend.com/issues/browse/ZF-6040
     */
    public function testNonStandardDeflateResponseZF6040()
    {
        $this->markTestSkipped('Not correctly handling non-RFC compliant "deflate" responses');

        $res = Response::fromString(readFixture('response_deflate_iis'));

        $this->assertEquals('deflate', strtolower($res->getHeader('content-encoding')));
        $this->assertEquals('d82c87e3d5888db0193a3fb12396e616', md5($res->getBody()));
        $this->assertEquals('c830dd74bb502443cf12514c185ff174', md5($res->getRawBody()));
    }

    public function testGetBodyChunked()
    {
        $response = Response::fromString(
            readFixture('http_response_200_chunked')
        );
        $this->assertSame(
            "Hello World",
            $response->getBody()
        );
    }

    public function testInvalidChunkedBody()
    {
        $this->setExpectedException(
            'EasyRdf\Exception',
            'Error parsing body - doesn\'t seem to be a chunked message'
        );
        $response = Response::fromString(
            "HTTP/1.1 200 OK\r\nTransfer-Encoding: chunked\r\n\r\nINVALID"
        );
        $response->getBody();
    }

    public function test200Ok()
    {
        $response = Response::fromString(
            readFixture('http_response_200')
        );

        $this->assertSame(
            200,
            $response->getStatus(),
            'Response code is expected to be 200, but it\'s not.'
        );
        $this->assertFalse(
            $response->isError(),
            'Response is OK, but isError() returned true'
        );
        $this->assertTrue(
            $response->isSuccessful(),
            'Response is OK, but isSuccessful() returned false'
        );
        $this->assertFalse(
            $response->isRedirect(),
            'Response is OK, but isRedirect() returned true'
        );
    }

    public function test200OkNoMessage()
    {
        $response = Response::fromString(
            readFixture('http_response_200_message')
        );

        $this->assertSame(
            200,
            $response->getStatus(),
            'Response code is expected to be 200, but it\'s not.'
        );
        $this->assertNull(
            $response->getMessage(),
            'Response message is expected to be null, but it\'s not.'
        );
        $this->assertFalse(
            $response->isError(),
            'Response is OK, but isError() returned true'
        );
        $this->assertTrue(
            $response->isSuccessful(),
            'Response is OK, but isSuccessful() returned false'
        );
        $this->assertFalse(
            $response->isRedirect(),
            'Response is OK, but isRedirect() returned true'
        );
    }

    public function test404IsError()
    {
        $response = Response::fromString(
            readFixture('http_response_404')
        );

        $this->assertSame(
            404,
            $response->getStatus(),
            'Response code is expected to be 404, but it\'s not.'
        );
        $this->assertTrue(
            $response->isError(),
            'Response is an error, but isError() returned false'
        );
        $this->assertFalse(
            $response->isSuccessful(),
            'Response is an error, but isSuccessful() returned true'
        );
        $this->assertFalse(
            $response->isRedirect(),
            'Response is an error, but isRedirect() returned true'
        );
    }

    public function test500isError()
    {
        $response = Response::fromString(
            readFixture('http_response_500')
        );

        $this->assertSame(
            500,
            $response->getStatus(),
            'Response code is expected to be 500, but it\'s not.'
        );
        $this->assertTrue(
            $response->isError(),
            'Response is an error, but isError() returned false'
        );
        $this->assertFalse(
            $response->isSuccessful(),
            'Response is an error, but isSuccessful() returned true'
        );
        $this->assertFalse(
            $response->isRedirect(),
            'Response is an error, but isRedirect() returned true'
        );
    }

    public function test300isRedirect()
    {
        $response = Response::fromString(
            readFixture('http_response_302')
        );

        $this->assertSame(
            302,
            $response->getStatus(),
            'Response code is expected to be 302, but it\'s not.'
        );
        $this->assertSame(
            'http://localhost/new/location',
            $response->getHeader('Location'),
            'Response code is expected to be 302, but it\'s not.'
        );
        $this->assertTrue(
            $response->isRedirect(),
            'Response is a redirection, but isRedirect() returned false'
        );
        $this->assertFalse(
            $response->isError(),
            'Response is a redirection, but isError() returned true'
        );
        $this->assertFalse(
            $response->isSuccessful(),
            'Response is a redirection, but isSuccessful() returned true'
        );
    }

    public function testGetHeaders()
    {
        $response = Response::fromString(
            readFixture('http_response_200')
        );

        $this->assertCount(
            8,
            $response->getHeaders(),
            'Header count is not as expected'
        );
        $this->assertSame(
            'Apache/2.2.9 (Unix) PHP/5.2.6',
            $response->getHeader('Server'),
            'Server header is not as expected'
        );
        $this->assertSame(
            'text/plain',
            $response->getHeader('Content-Type'),
            'Content-type header is not as expected'
        );
        $this->assertSame(
            array('foo','bar'),
            $response->getHeader('X-Multiple'),
            'Header with multiple values is not as expected'
        );
    }


    public function testAsString()
    {
        $responseStr = readFixture('http_response_404');
        $response = Response::fromString($responseStr);

        $this->assertSame(
            strtolower($responseStr),
            strtolower($response->asString()),
            'Response convertion to string does not match original string'
        );
        $this->assertSame(
            strtolower($responseStr),
            strtolower((string)$response),
            'Response convertion to string does not match original string'
        );
    }
}
