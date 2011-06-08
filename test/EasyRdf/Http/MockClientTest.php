<?php

require_once realpath(dirname(__FILE__) . '/../../') . '/TestHelper.php';
require_once 'PHPUnit/Framework.php';
require_once 'EasyRdf/Http/MockClient.php';


class EasyRdf_Http_MockClientTest extends EasyRdf_TestCase
{

    public function setUp()
    {
        $this->_client = new EasyRdf_Http_MockClient();
    }

    protected function get($uri)
    {
        $this->_client->setUri($uri);
        return $this->_client->request('GET');
    }

    public function testUrlMatch()
    {
        $this->_client->addMock('GET', 'http://foo.com/test', 'A');
        $this->_client->addMock('GET', 'http://www.bar.com/test', 'B');

        $response = $this->get('http://foo.com/test');
        $this->assertEquals('A', $response->getBody());
        $response = $this->get('http://www.bar.com/test');
        $this->assertEquals('B', $response->getBody());
    }

    public function testPathMatch()
    {
        $this->_client->addMock('GET', '/testA', 'A');
        $this->_client->addMock('GET', '/testB', 'B');
        $response = $this->get('http://example.com/testA');
        $this->assertEquals('A', $response->getBody());
        $response = $this->get('http://example.org/testB');
        $this->assertEquals('B', $response->getBody());
    }

    public function testPathAndQueryMatch()
    {
        $this->_client->addMock('GET', '/testA', '10');
        $this->_client->addMock('GET', '/testA?foo=bar', '20');
        $this->_client->addMock('GET', '/testA?bar=foo', '30');
        $response = $this->get('http://example.com/testA?foo=bar');
        $this->assertEquals('20', $response->getBody());
        $response = $this->get('http://example.org/testA');
        $this->assertEquals('10', $response->getBody());
    }

    public function testSettingGetParameter()
    {
        $this->_client->addMock('GET', '/testA', '10');
        $this->_client->addMock('GET', '/testA?a=1', '20');
        $this->_client->addMock('GET', '/testA?a=1&b=2', '30');

        $this->_client->setParameterGet('a', '1');
        $this->_client->setParameterGet('b', '2');
        $response = $this->get('http://example.org/testA');
        $this->assertEquals('30', $response->getBody());

        $this->_client->resetParameters();
        $this->_client->setParameterGet('b', '2');
        $response = $this->get('http://example.com/testA?a=1');
        $this->assertEquals('30', $response->getBody());
    }

    public function testUnknownUrl()
    {
        $this->setExpectedException('EasyRdf_Exception');
        $this->get('http://example.com/test');
    }

    public function testMethodUnknownMatch()
    {
        $this->setExpectedException('EasyRdf_Exception');
        $this->_client->addMock('PUT', '/test', '10');
        $this->get('http://example.com/test');
    }

    public function testWildcardMethod()
    {
        $this->_client->addMock(null, '/test', 'testWildcardMethod');
        $response = $this->get('http://example.com/test');
        $this->assertEquals('testWildcardMethod', $response->getBody());
    }

    public function testWildcardUrl()
    {
        $this->_client->addMock('GET', null, 'testWildcardUrl');
        $response = $this->get('http://example.com/foo');
        $this->assertEquals('testWildcardUrl', $response->getBody());
    }

    public function testSetStatus()
    {
        $this->_client->addMock('GET', '/test', 'x', array('status' => 404));
        $response = $this->get('http://example.com/test');
        $this->assertEquals(404, $response->getStatus());
    }

    public function testSetHeaders()
    {
        $h = array('Content-Type' => 'text/html');
        $this->_client->addMock('GET', '/test', 'x', array('headers' => $h));
        $response = $this->get('http://example.com/test');
        $this->assertEquals('text/html', $response->getHeader('Content-Type'));
    }

    public function testSetResponse()
    {
        $response = new EasyRdf_Http_Response(234, array('Foo' => 'bar'), 'x');
        $this->_client->addMock('GET', '/test', $response);
        $r = $this->get('http://example.com/test', array('throw' => false));
        $this->assertEquals(234, $r->getStatus());
        $this->assertEquals('bar', $r->getHeader('Foo'));
        $this->assertEquals('x', $r->getBody());
    }

    public function testOnce()
    {
        $this->_client->addMock('GET', '/test', '10', array('once' => true));
        $this->_client->addMockOnce('GET', '/test', '20');
        $this->_client->addMock('GET', '/test', '30');
        $r = $this->get('http://example.com/test');
        $this->assertEquals(10, $r->getBody());
        $r = $this->get('http://example.com/test');
        $this->assertEquals(20, $r->getBody());
        $r = $this->get('http://example.com/test');
        $this->assertEquals(30, $r->getBody());
        $r = $this->get('http://example.com/test');
        $this->assertEquals(30, $r->getBody());
    }

    public function testCallbackWithoutArgs()
    {
        $alwaysTrue = array('callback' => array($this, 'alwaysTrue'));
        $alwaysFalse = array('callback' => array($this, 'alwaysFalse'));

        $this->_client->addMock('GET', '/test1', '10', $alwaysTrue);
        $r = $this->get('http://example.com/test1');
        $this->assertEquals(10, $r->getBody());

        $this->_client->addMock('GET', '/test2', '10', $alwaysFalse);
        $this->_client->addMock('GET', '/test2', '20', $alwaysFalse);
        $this->_client->addMock('GET', '/test2', '30', $alwaysTrue);
        $r = $this->get('http://example.com/test2');
        $this->assertEquals(30, $r->getBody());
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

        $this->_client->addMock('GET', '/test1', '10', $echoTrue);
        $r = $this->get('http://example.com/test1');
        $this->assertEquals('10', $r->getBody());

        $this->_client->addMock('GET', '/test2', '10', $echoFalse);
        $this->_client->addMock('GET', '/test2', '20', $echoTrue);
        $this->_client->addMock('GET', '/test2', '30', $echoFalse);
        $r = $this->get('http://example.com/test2');
        $this->assertEquals('20', $r->getBody());
    }

    public function echoValue($value)
    {
        return $value;
    }

    public function testCallbackWithMockHttp()
    {
        $isMockHttp = array('callback' => array($this, 'isMockHttp'));
        $this->_client->addMock('GET', '/test1', '10', $isMockHttp);
        $r = $this->get('http://example.com/test1');
        $this->assertEquals('10', $r->getBody());
    }

    public function isMockHttp($value)
    {
        return ($value instanceof EasyRdf_Http_MockClient);
    }

    public function testGuessJsonContentType()
    {
        $this->_client->addMockOnce('GET', null, '{"a":1}');
        $r = $this->get('http://example.com');
        $this->assertEquals('application/json', $r->getHeader('Content-Type'));

        $this->_client->addMockOnce('GET', null, "\n{\"a\"\n:\n1\n}\n");
        $r = $this->get('http://example.com');
        $this->assertEquals('application/json', $r->getHeader('Content-Type'));
    }

}
