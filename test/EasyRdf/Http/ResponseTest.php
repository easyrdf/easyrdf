<?php

/**
 * Test helper
 */
require_once realpath(dirname(__FILE__) . '/../../') . '/TestHelper.php';

/**
 * PHPUnit test case
 */
require_once 'PHPUnit/Framework/TestCase.php';

class EasyRdf_Http_ResponseTest extends EasyRdf_TestCase
{
    public function testGetVersion()
    {
        $response = EasyRdf_Http_Response::fromString(
            readFixture('http_response_200')
        );
        $this->assertEquals(
            1.1,
            $response->getVersion(),
            'Version is expected to be 1.1'
        );
    }

    public function testGetMessage()
    {
        $response = EasyRdf_Http_Response::fromString(
            readFixture('http_response_200')
        );
        $this->assertEquals(
            'OK',
            $response->getMessage(),
            'Message is expected to be OK'
        );
    }

    public function testGetBody()
    {
        $response = EasyRdf_Http_Response::fromString(
            readFixture('http_response_200')
        );
        $this->assertEquals(
            "Hello World\n",
            $response->getBody()
        );
    }

    public function testInvalidResponse()
    {
        $this->setExpectedException(
            'EasyRdf_Exception',
            'Failed to parse HTTP response.'
        );
        $response = EasyRdf_Http_Response::fromString('foobar');
    }

    public function testInvalidStatusLine()
    {
        $this->setExpectedException(
            'EasyRdf_Exception',
            'Failed to parse HTTP response status line.'
        );
        $response = EasyRdf_Http_Response::fromString(
            "HTTP1.0 200 OK\r\nConnection: close\r\n\r\nBody"
        );
    }

    public function testGetBodyChunked()
    {
        $response = EasyRdf_Http_Response::fromString(
            readFixture('http_response_200_chunked')
        );
        $this->assertEquals(
            "Hello World",
            $response->getBody()
        );
    }

    public function testInvalidChunkedBody()
    {
        $this->setExpectedException(
            'EasyRdf_Exception',
            'Failed to decode chunked body in HTTP response.'
        );
        $response = EasyRdf_Http_Response::fromString(
            "HTTP/1.1 200 OK\r\nTransfer-Encoding: chunked\r\n\r\nINVALID"
        );
        $response->getBody();
    }

    public function test200Ok()
    {
        $response = EasyRdf_Http_Response::fromString(
            readFixture('http_response_200')
        );

        $this->assertEquals(
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

    public function test404IsError()
    {
        $response = EasyRdf_Http_Response::fromString(
            readFixture('http_response_404')
        );

        $this->assertEquals(
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
        $response = EasyRdf_Http_Response::fromString(
            readFixture('http_response_500')
        );

        $this->assertEquals(
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
        $response = EasyRdf_Http_Response::fromString(
            readFixture('http_response_302')
        );

        $this->assertEquals(
            302,
            $response->getStatus(),
            'Response code is expected to be 302, but it\'s not.'
        );
        $this->assertEquals(
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
        $response = EasyRdf_Http_Response::fromString(
            readFixture('http_response_200')
        );

        $this->assertEquals(
            8,
            count($response->getHeaders()),
            'Header count is not as expected'
        );
        $this->assertEquals(
            'Apache/2.2.9 (Unix) PHP/5.2.6',
            $response->getHeader('Server'), 'Server header is not as expected'
        );
        $this->assertEquals(
            'text/plain',
            $response->getHeader('Content-Type'),
            'Content-type header is not as expected'
        );
        $this->assertEquals(
            array('foo','bar'),
            $response->getHeader('X-Multiple'),
            'Header with multiple values is not as expected'
        );
    }

}
