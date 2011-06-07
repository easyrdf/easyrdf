<?php

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009-2011 Nicholas J Humfrey.  All rights reserved.
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
 * @copyright  Copyright (c) 2009-2011 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 * @version    $Id$
 */

require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'TestHelper.php';


class EasyRdf_GraphStoreTest extends EasyRdf_TestCase
{
    public function setUp()
    {
        EasyRdf_Http::setDefaultHttpClient(
            $this->_client = new EasyRdf_Http_MockClient()
        );
        $this->_graphStore = new EasyRdf_GraphStore('http://localhost:8080/data/');
    }

    public function testGetUri()
    {
        $this->assertEquals(
            'http://localhost:8080/data/',
            $this->_graphStore->getUri()
        );
    }

    public function testUriForGraphDirect()
    {
        $this->assertEquals(
            'http://localhost:8080/data/foobar.rdf',
            $this->_graphStore->urlForGraph('http://localhost:8080/data/foobar.rdf')
        );
    }

    public function testUriForGraphIndirect()
    {
        $this->assertEquals(
            'http://localhost:8080/data/?graph=http%3A%2F%2Fexample.com%2Ffoobar.rdf',
            $this->_graphStore->urlForGraph('http://example.com/foobar.rdf')
        );
    }

    public function testGetDirect()
    {
        $this->_client->addMock('GET', '/data/foaf.rdf', readFixture('foaf.json'));
        $graph = $this->_graphStore->get('foaf.rdf');
        $this->assertType('EasyRdf_Graph', $graph);
        $this->assertEquals('http://localhost:8080/data/foaf.rdf', $graph->getUri());
        $this->assertStringEquals(
            'Joe Bloggs',
            $graph->get('http://www.example.com/joe#me', 'foaf:name')
        );
    }

    public function testGetIndirect()
    {
        $this->_client->addMock(
            'GET', '/data/?graph=http%3A%2F%2Ffoo.com%2Fbar.rdf',
            readFixture('foaf.json')
        );
        $graph = $this->_graphStore->get('http://foo.com/bar.rdf');
        $this->assertType('EasyRdf_Graph', $graph);
        $this->assertEquals('http://foo.com/bar.rdf', $graph->getUri());
        $this->assertStringEquals(
            'Joe Bloggs',
            $graph->get('http://www.example.com/joe#me', 'foaf:name')
        );
    }

    public function testDeleteDirect()
    {
        $this->_client->addMock('DELETE', '/data/foaf.rdf', 'OK');
        $response = $this->_graphStore->delete('foaf.rdf');
        $this->assertEquals('200', $response->getStatus());
    }

    public function testDeleteIndirect()
    {
        $this->_client->addMock('DELETE', '/data/?graph=http%3A%2F%2Ffoo.com%2Fbar.rdf', 'OK');
        $response = $this->_graphStore->delete('http://foo.com/bar.rdf');
        $this->assertEquals('200', $response->getStatus());
    }
    
    public function checkNtriplesRequest($client)
    {
        $this->assertEquals(
            "<urn:subject> <urn:predicate> \"object\" .\n",
            $client->getRawData()
        );
        $this->assertEquals("text/plain", $client->getHeader('Content-Type'));
        $this->assertEquals(41, $client->getHeader('Content-Length'));
        return true;
    }

    public function testInsertDirect()
    {
        $graph = new EasyRdf_Graph('http://localhost:8080/data/new.rdf');
        $graph->add('urn:subject', 'urn:predicate', 'object');
        $this->_client->addMock(
            'POST', 'http://localhost:8080/data/new.rdf', 'OK',
            array('callback' => array($this, 'checkNtriplesRequest'))
        );
        $response = $this->_graphStore->insert($graph);
        $this->assertEquals('200', $response->getStatus());
    }

    public function testInsertIndirect()
    {
        $data = "<urn:subject> <urn:predicate> \"object\" .\n";
        $this->_client->addMock(
            'POST', '/data/?graph=http%3A%2F%2Ffoo.com%2Fbar.rdf', 'OK',
            array('callback' => array($this, 'checkNtriplesRequest'))
        );
        $response = $this->_graphStore->insert($data, "http://foo.com/bar.rdf");
        $this->assertEquals('200', $response->getStatus());
    }

    public function testReplaceIndirect()
    {
        $data = "<urn:subject> <urn:predicate> \"object\" .\n";
        $this->_client->addMock(
            'PUT', '/data/?graph=http%3A%2F%2Ffoo.com%2Fbar.rdf', 'OK',
            array('callback' => array($this, 'checkNtriplesRequest'))
        );
        $response = $this->_graphStore->replace($data, "http://foo.com/bar.rdf");
        $this->assertEquals('200', $response->getStatus());
    }
    
    public function checkTurtleRequest($client)
    {
        $this->assertEquals(
            '{"urn:subject":{"urn:predicate":[{"type":"literal","value":"object"}]}}',
            $client->getRawData()
        );
        $this->assertEquals("application/json", $client->getHeader('Content-Type'));
        $this->assertEquals(71, $client->getHeader('Content-Length'));
        return true;
    }

    public function testReplaceDirectJson()
    {
        $graph = new EasyRdf_Graph('http://localhost:8080/data/new.rdf');
        $graph->add('urn:subject', 'urn:predicate', 'object');
        $this->_client->addMock(
            'PUT', '/data/?graph=http%3A%2F%2Ffoo.com%2Fbar.rdf', 'OK',
            array('callback' => array($this, 'checkTurtleRequest'))
        );
        $response = $this->_graphStore->replace($graph, "http://foo.com/bar.rdf", 'json');
        $this->assertEquals('200', $response->getStatus());
    }

    public function testToString()
    {
        $this->assertStringEquals(
            'http://localhost:8080/data/',
            $this->_graphStore
        );
    }

}
