<?php

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009-2012 Nicholas J Humfrey.  All rights reserved.
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
 * @copyright  Copyright (c) 2009-2012 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 * @version    $Id$
 */

require_once realpath(dirname(__FILE__) . '/../../') . '/TestHelper.php';


class EasyRdf_Sparql_ClientTest extends EasyRdf_TestCase
{
    public function setUp()
    {
        EasyRdf_Http::setDefaultHttpClient(
            $this->_client = new EasyRdf_Http_MockClient()
        );
        $this->_sparql = new EasyRdf_Sparql_Client('http://localhost:8080/sparql');
    }

    public function testGetUri()
    {
        $this->assertEquals(
            'http://localhost:8080/sparql',
            $this->_sparql->getUri()
        );
    }

    public function testQuerySelectAll()
    {
        $this->_client->addMock(
            'GET', '/sparql?query=SELECT+%2A+WHERE+%7B%3Fs+%3Fp+%3Fo%7D',
            readFixture('sparql_select_all.xml'),
            array(
                'headers' => array('Content-Type' => 'application/sparql-results+xml')
            )
        );
        $result = $this->_sparql->query("SELECT * WHERE {?s ?p ?o}");
        $this->assertEquals(14, count($result));
        $this->assertEquals(
            new EasyRdf_Resource('_:genid1'), $result[0]->s
        );
        $this->assertEquals(
            new EasyRdf_Resource('http://xmlns.com/foaf/0.1/name'), $result[0]->p
        );
        $this->assertEquals(
            new EasyRdf_Literal("Joe's Current Project"), $result[0]->o
        );
    }

    public function testQuerySelectAllJsonWithCharset()
    {
        $this->_client->addMock(
            'GET', '/sparql?query=SELECT+%2A+WHERE+%7B%3Fs+%3Fp+%3Fo%7D',
            readFixture('sparql_select_all.json'),
            array(
                'headers' => array('Content-Type' => 'application/sparql-results+json; charset=utf-8')
            )
        );
        $result = $this->_sparql->query("SELECT * WHERE {?s ?p ?o}");
        $this->assertEquals(14, count($result));
        $this->assertEquals(3, $result->numFields());
        $this->assertEquals(array('s','p','o'), $result->getFields());
        $this->assertEquals(
            new EasyRdf_Literal("Joe's Current Project"), $result[0]->o
        );
    }

    public function testQuerySelectAllUnsupportedFormat()
    {
        $this->_client->addMock(
            'GET', '/sparql?query=SELECT+%2A+WHERE+%7B%3Fs+%3Fp+%3Fo%7D',
            readFixture('sparql_select_all.json'),
            array(
                'headers' => array('Content-Type' => 'unsupported/format')
            )
        );
        $this->setExpectedException(
            'EasyRdf_Exception',
            'Format is not recognised: unsupported/format'
        );
        $result = $this->_sparql->query("SELECT * WHERE {?s ?p ?o}");
    }

    public function testQueryAddPrefix()
    {
        $this->_client->addMock(
            'GET',
            '/sparql?query=PREFIX+rdf%3A+%3Chttp%3A%2F%2Fwww.w3.org%2F'.
            '1999%2F02%2F22-rdf-syntax-ns%23%3E%0ASELECT+%3Ft+WHERE+'.
            '%7B%3Fs+rdf%3Atype+%3Ft%7D',
            readFixture('sparql_select_all_types.xml'),
            array(
                'headers' => array('Content-Type' => 'application/sparql-results+xml')
            )
        );
        $result = $this->_sparql->query("SELECT ?t WHERE {?s rdf:type ?t}");
        $this->assertEquals(3, count($result));
        $this->assertEquals(
            new EasyRdf_Resource('http://xmlns.com/foaf/0.1/Project'), $result[0]->t
        );
        $this->assertEquals(
            new EasyRdf_Resource('http://xmlns.com/foaf/0.1/PersonalProfileDocument'), $result[1]->t
        );
        $this->assertEquals(
            new EasyRdf_Resource('http://xmlns.com/foaf/0.1/Person'), $result[2]->t
        );
    }

    public function testQueryAskTrue()
    {
        $this->_client->addMock(
            'GET', '/sparql?query=ASK+WHERE+%7B%3Fs+%3Fp+%3Fo%7D',
            readFixture('sparql_ask_true.xml'),
            array(
                'headers' => array('Content-Type' => 'application/sparql-results+xml')
            )
        );
        $result = $this->_sparql->query("ASK WHERE {?s ?p ?o}");
        $this->assertEquals(true, $result->getBoolean());
    }

    public function testQueryAskFalse()
    {
        $this->_client->addMock(
            'GET', '/sparql?query=ASK+WHERE+%7B%3Fs+%3Fp+%3Cfalse%3E%7D',
            readFixture('sparql_ask_false.xml'),
            array(
                'headers' => array('Content-Type' => 'application/sparql-results+xml')
            )
        );
        $result = $this->_sparql->query("ASK WHERE {?s ?p <false>}");
        $this->assertEquals(false, $result->getBoolean());
    }

    public function testQueryConstructJson()
    {
        $this->_client->addMock(
            'GET', '/sparql?query=CONSTRUCT+%7B%3Fs+%3Fp+%3Fo%7D+WHERE+%7B%3Fs+%3Fp+%3Fo%7D',
            readFixture('foaf.json'),
            array(
                'headers' => array('Content-Type' => 'application/json')
            )
        );
        $graph = $this->_sparql->query("CONSTRUCT {?s ?p ?o} WHERE {?s ?p ?o}");
        $this->assertType('EasyRdf_Graph', $graph);
        $name = $graph->get('http://www.example.com/joe#me', 'foaf:name');
        $this->assertStringEquals('Joe Bloggs', $name);
    }

    public function testQueryConstructJsonWithCharset()
    {
        $this->_client->addMock(
            'GET', '/sparql?query=CONSTRUCT+%7B%3Fs+%3Fp+%3Fo%7D+WHERE+%7B%3Fs+%3Fp+%3Fo%7D',
            readFixture('foaf.json'),
            array(
                'headers' => array('Content-Type' => 'application/json; charset=utf-8')
            )
        );
        $graph = $this->_sparql->query("CONSTRUCT {?s ?p ?o} WHERE {?s ?p ?o}");
        $this->assertInstanceOf('EasyRdf_Graph', $graph);
        $name = $graph->get('http://www.example.com/joe#me', 'foaf:name');
        $this->assertStringEquals('Joe Bloggs', $name);
    }

    public function testQueryInvalid()
    {
        $this->_client->addMock(
            'GET', '/sparql?query=FOOBAR',
            "There was an error while executing the query.\nSPARQL syntax error at 'F', line 1",
            array(
                'status' => 500,
                'headers' => array('Content-Type' => 'text/plain')
            )
        );
        $this->setExpectedException(
            'EasyRdf_Exception',
            'HTTP request for SPARQL query failed: There was an error while executing the query.'
        );
        $response = $this->_sparql->query("FOOBAR");
    }

    public function testToString()
    {
        $this->assertStringEquals(
            'http://localhost:8080/sparql',
            $this->_sparql
        );
    }

}
