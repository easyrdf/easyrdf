<?php

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009-2013 Nicholas J Humfrey.  All rights reserved.
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

require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'TestHelper.php';


class EasyRdf_GraphStoreTest extends EasyRdf_TestCase
{
    /** @var  EasyRdf_GraphStore */
    private $graphStore;

    public function setUp()
    {
        EasyRdf_Http::setDefaultHttpClient(
            $this->client = new EasyRdf_Http_MockClient()
        );
        $this->graphStore = new EasyRdf_GraphStore('http://localhost:8080/data/');

        // Ensure that the built-in n-triples parser is used
        EasyRdf_Format::registerSerialiser('ntriples', 'EasyRdf_Serialiser_Ntriples');
    }

    public function testGetUri()
    {
        $this->assertSame(
            'http://localhost:8080/data/',
            $this->graphStore->getUri()
        );
    }

    public function testGetDirect()
    {
        $this->client->addMock(
            'GET',
            'http://localhost:8080/data/foaf.rdf',
            readFixture('foaf.json')
        );
        $graph = $this->graphStore->get('foaf.rdf');
        $this->assertClass('EasyRdf_Graph', $graph);
        $this->assertSame('http://localhost:8080/data/foaf.rdf', $graph->getUri());
        $this->assertStringEquals(
            'Joe Bloggs',
            $graph->get('http://www.example.com/joe#me', 'foaf:name')
        );
    }

    public function testGetIndirect()
    {
        $this->client->addMock(
            'GET',
            'http://localhost:8080/data/?graph=http%3A%2F%2Ffoo.com%2Fbar.rdf',
            readFixture('foaf.json')
        );
        $graph = $this->graphStore->get('http://foo.com/bar.rdf');
        $this->assertClass('EasyRdf_Graph', $graph);
        $this->assertSame('http://foo.com/bar.rdf', $graph->getUri());
        $this->assertStringEquals(
            'Joe Bloggs',
            $graph->get('http://www.example.com/joe#me', 'foaf:name')
        );
    }

    public function testGetDefault()
    {
        $this->client->addMock(
            'GET',
            'http://localhost:8080/data/?default',
            readFixture('foaf.json')
        );
        $graph = $this->graphStore->getDefault();
        $this->assertClass('EasyRdf_Graph', $graph);
        $this->assertSame(null, $graph->getUri());
        $this->assertStringEquals(
            'Joe Bloggs',
            $graph->get('http://www.example.com/joe#me', 'foaf:name')
        );
    }

    public function testDeleteDirect()
    {
        $this->client->addMock(
            'DELETE',
            'http://localhost:8080/data/foaf.rdf',
            'OK'
        );
        $response = $this->graphStore->delete('foaf.rdf');
        $this->assertSame(200, $response->getStatus());
    }

    public function testDeleteIndirect()
    {
        $this->client->addMock(
            'DELETE',
            'http://localhost:8080/data/?graph=http%3A%2F%2Ffoo.com%2Fbar.rdf',
            'OK'
        );
        $response = $this->graphStore->delete('http://foo.com/bar.rdf');
        $this->assertSame(200, $response->getStatus());
    }

    public function testDeleteHttpError()
    {
        $this->client->addMock(
            'DELETE',
            'http://localhost:8080/data/filenotfound',
            'Graph not found.',
            array('status' => 404)
        );
        $this->setExpectedException(
            'EasyRdf_Exception',
            'HTTP request to delete http://localhost:8080/data/filenotfound failed'
        );
        $response = $this->graphStore->delete('filenotfound');
    }

    public function checkNtriplesRequest($client)
    {
        $this->assertSame(
            "<urn:subject> <urn:predicate> \"object\" .\n",
            $client->getRawData()
        );
        $this->assertSame("application/n-triples", $client->getHeader('Content-Type'));
        return true;
    }

    public function testInsertDirect()
    {
        $graph = new EasyRdf_Graph('http://localhost:8080/data/new.rdf');
        $graph->add('urn:subject', 'urn:predicate', 'object');
        $this->client->addMock(
            'POST',
            'http://localhost:8080/data/new.rdf',
            'OK',
            array('callback' => array($this, 'checkNtriplesRequest'))
        );
        $response = $this->graphStore->insert($graph);
        $this->assertSame(200, $response->getStatus());
    }

    public function testInsertIndirect()
    {
        $data = "<urn:subject> <urn:predicate> \"object\" .\n";
        $this->client->addMock(
            'POST',
            '/data/?graph=http%3A%2F%2Ffoo.com%2Fbar.rdf',
            'OK',
            array('callback' => array($this, 'checkNtriplesRequest'))
        );
        $response = $this->graphStore->insert($data, "http://foo.com/bar.rdf");
        $this->assertSame(200, $response->getStatus());
    }

    public function testInsertIntoDefault()
    {
        $data = "<urn:subject> <urn:predicate> \"object\" .\n";
        $this->client->addMock(
            'POST',
            '/data/?default',
            'OK',
            array('callback' => array($this, 'checkNtriplesRequest'))
        );
        $response = $this->graphStore->insertIntoDefault($data);
        $this->assertSame(200, $response->getStatus());
    }

    public function testInsertHttpError()
    {
        $data = "<urn:subject> <urn:predicate> \"object\" .\n";
        $this->client->addMock(
            'POST',
            '/data/new.rdf',
            'Internal Server Error',
            array('status' => 500)
        );
        $this->setExpectedException(
            'EasyRdf_Exception',
            'HTTP request for http://localhost:8080/data/new.rdf failed'
        );
        $response = $this->graphStore->insert($data, 'new.rdf');
    }

    public function testReplaceIndirect()
    {
        $data = "<urn:subject> <urn:predicate> \"object\" .\n";
        $this->client->addMock(
            'PUT',
            '/data/?graph=http%3A%2F%2Ffoo.com%2Fbar.rdf',
            'OK',
            array('callback' => array($this, 'checkNtriplesRequest'))
        );
        $response = $this->graphStore->replace($data, "http://foo.com/bar.rdf");
        $this->assertSame(200, $response->getStatus());
    }

    public function testReplaceDefault()
    {
        $data = "<urn:subject> <urn:predicate> \"object\" .\n";
        $this->client->addMock(
            'PUT',
            '/data/?default',
            'OK',
            array('callback' => array($this, 'checkNtriplesRequest'))
        );
        $response = $this->graphStore->replaceDefault($data);
        $this->assertSame(200, $response->getStatus());
    }

    public function checkJsonRequest($client)
    {
        $this->assertSame(
            '{"urn:subject":{"urn:predicate":[{"type":"literal","value":"object"}]}}',
            $client->getRawData()
        );
        $this->assertSame("application/json", $client->getHeader('Content-Type'));
        return true;
    }

    public function testReplaceDirectJson()
    {
        $graph = new EasyRdf_Graph('http://localhost:8080/data/new.rdf');
        $graph->add('urn:subject', 'urn:predicate', 'object');
        $this->client->addMock(
            'PUT',
            '/data/?graph=http%3A%2F%2Ffoo.com%2Fbar.rdf',
            'OK',
            array('callback' => array($this, 'checkJsonRequest'))
        );
        $response = $this->graphStore->replace($graph, "http://foo.com/bar.rdf", 'json');
        $this->assertSame(200, $response->getStatus());
    }

    public function testReplaceHttpError()
    {
        $data = "<urn:subject> <urn:predicate> \"object\" .\n";
        $this->client->addMock(
            'PUT',
            '/data/existing.rdf',
            'Internal Server Error',
            array('status' => 500)
        );
        $this->setExpectedException(
            'EasyRdf_Exception',
            'HTTP request for http://localhost:8080/data/existing.rdf failed'
        );
        $response = $this->graphStore->replace($data, 'existing.rdf');
    }

    public function testToString()
    {
        $this->assertStringEquals(
            'http://localhost:8080/data/',
            $this->graphStore
        );
    }
}
