<?php

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2011-2013 Nicholas J Humfrey.  All rights reserved.
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
 * @copyright  Copyright (c) 2009-2013 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */

require_once realpath(dirname(__FILE__) . '/../../') . '/TestHelper.php';
require_once 'EasyRdf/Http/MockClient.php';


class EasyRdf_Http_MockClientTest extends EasyRdf_TestCase
{

    public function setUp()
    {
        $this->client = new EasyRdf_Http_MockClient();
    }

    protected function get($uri)
    {
        $this->client->setUri($uri);
        return $this->client->request('GET');
    }

    public function testUrlMatch()
    {
        $this->client->addMock('GET', 'http://foo.com/test', 'A');
        $this->client->addMock('GET', 'http://www.bar.com/test', 'B');

        $response = $this->get('http://foo.com/test');
        $this->assertSame('A', $response->getBody());
        $response = $this->get('http://www.bar.com/test');
        $this->assertSame('B', $response->getBody());
    }

    public function testPathMatch()
    {
        $this->client->addMock('GET', '/testA', 'A');
        $this->client->addMock('GET', '/testB', 'B');
        $response = $this->get('http://example.com/testA');
        $this->assertSame('A', $response->getBody());
        $response = $this->get('http://example.org/testB');
        $this->assertSame('B', $response->getBody());
    }

    public function testPathAndQueryMatch()
    {
        $this->client->addMock('GET', '/testA', '10');
        $this->client->addMock('GET', '/testA?foo=bar', '20');
        $this->client->addMock('GET', '/testA?bar=foo', '30');
        $response = $this->get('http://example.com/testA?foo=bar');
        $this->assertSame('20', $response->getBody());
        $response = $this->get('http://example.org/testA');
        $this->assertSame('10', $response->getBody());
    }

    public function testSettingGetParameter()
    {
        $this->client->addMock('GET', '/testA', '10');
        $this->client->addMock('GET', '/testA?a=1', '20');
        $this->client->addMock('GET', '/testA?a=1&b=2', '30');

        $this->client->setParameterGet('a', '1');
        $this->client->setParameterGet('b', '2');
        $response = $this->get('http://example.org/testA');
        $this->assertSame('30', $response->getBody());

        $this->client->resetParameters();
        $this->client->setParameterGet('b', '2');
        $response = $this->get('http://example.com/testA?a=1');
        $this->assertSame('30', $response->getBody());
    }

    public function testUnknownUrl()
    {
        $this->setExpectedException(
            'EasyRdf_Exception',
            'Unexpected request: GET http://example.com/test'
        );
        $this->get('http://example.com/test');
    }

    public function testMethodUnknownMatch()
    {
        $this->setExpectedException(
            'EasyRdf_Exception',
            'Unexpected request: GET http://example.com/test'
        );
        $this->client->addMock('PUT', '/test', '10');
        $this->get('http://example.com/test');
    }

    public function testWildcardMethod()
    {
        $this->client->addMock(null, '/test', 'testWildcardMethod');
        $response = $this->get('http://example.com/test');
        $this->assertSame('testWildcardMethod', $response->getBody());
    }

    public function testWildcardUrl()
    {
        $this->client->addMock('GET', null, 'testWildcardUrl');
        $response = $this->get('http://example.com/foo');
        $this->assertSame('testWildcardUrl', $response->getBody());
    }

    public function testSetStatus()
    {
        $this->client->addMock('GET', '/test', 'x', array('status' => 404));
        $response = $this->get('http://example.com/test');
        $this->assertSame(404, $response->getStatus());
    }

    public function testSetHeaders()
    {
        $h = array('Content-Type' => 'text/html');
        $this->client->addMock('GET', '/test', 'x', array('headers' => $h));
        $response = $this->get('http://example.com/test');
        $this->assertSame('text/html', $response->getHeader('Content-Type'));
    }

    public function testSetResponse()
    {
        $response = new EasyRdf_Http_Response(234, array('Foo' => 'bar'), 'x');
        $this->client->addMock('GET', '/test', $response);
        $r = $this->get('http://example.com/test', array('throw' => false));
        $this->assertSame(234, $r->getStatus());
        $this->assertSame('bar', $r->getHeader('Foo'));
        $this->assertSame('x', $r->getBody());
    }

    public function testOnce()
    {
        $this->client->addMock('GET', '/test', '10', array('once' => true));
        $this->client->addMockOnce('GET', '/test', '20');
        $this->client->addMock('GET', '/test', '30');
        $r = $this->get('http://example.com/test');
        $this->assertSame('10', $r->getBody());
        $r = $this->get('http://example.com/test');
        $this->assertSame('20', $r->getBody());
        $r = $this->get('http://example.com/test');
        $this->assertSame('30', $r->getBody());
        $r = $this->get('http://example.com/test');
        $this->assertSame('30', $r->getBody());
    }

    public function testRedirect()
    {
        $this->client->addMockRedirect('GET', '/', 'http://example.com/test');
        $r = $this->get('http://example.com/');
        $this->assertSame('302 redirect to http://example.com/test', $r->getBody());
        $this->assertSame(302, $r->getStatus());
        $this->assertSame('http://example.com/test', $r->getHeader('Location'));
    }

    public function testRedirectSeeOther()
    {
        $this->client->addMockRedirect('GET', '/', 'http://example.com/test', 303);
        $r = $this->get('http://example.com/');
        $this->assertSame('303 redirect to http://example.com/test', $r->getBody());
        $this->assertSame(303, $r->getStatus());
        $this->assertSame('http://example.com/test', $r->getHeader('Location'));
    }

    public function testCallbackWithoutArgs()
    {
        $alwaysTrue = array('callback' => array($this, 'alwaysTrue'));
        $alwaysFalse = array('callback' => array($this, 'alwaysFalse'));

        $this->client->addMock('GET', '/test1', '10', $alwaysTrue);
        $r = $this->get('http://example.com/test1');
        $this->assertSame('10', $r->getBody());

        $this->client->addMock('GET', '/test2', '10', $alwaysFalse);
        $this->client->addMock('GET', '/test2', '20', $alwaysFalse);
        $this->client->addMock('GET', '/test2', '30', $alwaysTrue);
        $r = $this->get('http://example.com/test2');
        $this->assertSame('30', $r->getBody());
    }

    public function alwaysTrue()
    {
        return true;
    }

    public function alwaysFalse()
    {
        return false;
    }

    public function testCallbackWithArgs()
    {
        $echoTrue = array('callback' => array($this, 'echoValue'),
                          'callbackArgs' => array(true));
        $echoFalse = array('callback' => array($this, 'echoValue'),
                           'callbackArgs' => array(false));

        $this->client->addMock('GET', '/test1', '10', $echoTrue);
        $r = $this->get('http://example.com/test1');
        $this->assertSame('10', $r->getBody());

        $this->client->addMock('GET', '/test2', '10', $echoFalse);
        $this->client->addMock('GET', '/test2', '20', $echoTrue);
        $this->client->addMock('GET', '/test2', '30', $echoFalse);
        $r = $this->get('http://example.com/test2');
        $this->assertSame('20', $r->getBody());
    }

    public function echoValue($value)
    {
        return $value;
    }

    public function testCallbackWithMockHttp()
    {
        $isMockHttp = array('callback' => array($this, 'isMockHttp'));
        $this->client->addMock('GET', '/test1', '10', $isMockHttp);
        $r = $this->get('http://example.com/test1');
        $this->assertSame('10', $r->getBody());
    }

    public function isMockHttp($value)
    {
        return ($value instanceof EasyRdf_Http_MockClient);
    }

    public function testGuessJsonContentType()
    {
        $this->client->addMockOnce('GET', null, '{"a":1}');
        $r = $this->get('http://example.com');
        $this->assertSame('application/json', $r->getHeader('Content-Type'));

        $this->client->addMockOnce('GET', null, "\n{\"a\"\n:\n1\n}\n");
        $r = $this->get('http://example.com');
        $this->assertSame('application/json', $r->getHeader('Content-Type'));
    }
}
