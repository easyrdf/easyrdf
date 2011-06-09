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


class EasyRdf_SparqlClientTest extends EasyRdf_TestCase
{
    public function setUp()
    {
        EasyRdf_Http::setDefaultHttpClient(
            $this->_client = new EasyRdf_Http_MockClient()
        );
        $this->_graph = new EasyRdf_Graph('http://example.com/graph');
        $this->_sparql = new EasyRdf_SparqlClient('http://localhost:8080/sparql');
    }

    public function testGetUri()
    {
        $this->assertEquals(
            'http://localhost:8080/sparql',
            $this->_sparql->getUri()
        );
    }

    public function testQuerySelectAllXml()
    {
        $this->_client->addMock(
            'GET', '/sparql?query=SELECT+%2A+WHERE+%7B%3Fs+%3Fp+%3Fo%7D',
            readFixture('sparql_select_all.xml'),
            array(
                'headers' => array('Content-Type' => 'application/sparql-results+xml'
            )
        ));
        $result = $this->_sparql->query("SELECT * WHERE {?s ?p ?o}");
        $this->assertEquals(14, count($result));
        $this->assertEquals(
            new EasyRdf_Resource('_:genid1'), $result[0]['s']
        );
        $this->assertEquals(
            new EasyRdf_Resource('http://xmlns.com/foaf/0.1/name'), $result[0]['p']
        );
        $this->assertEquals(
            new EasyRdf_Literal("Joe's Current Project"), $result[0]['o']
        );
    }

    public function testQuerySelectAllJson()
    {
        $this->_client->addMock(
            'GET', '/sparql?query=SELECT+%2A+WHERE+%7B%3Fs+%3Fp+%3Fo%7D',
            readFixture('sparql_select_all.json'),
            array(
                'headers' => array('Content-Type' => 'application/sparql-results+json'
            )
        ));
        $result = $this->_sparql->query("SELECT * WHERE {?s ?p ?o}");
        $this->assertEquals(14, count($result));
        $this->assertEquals(
            new EasyRdf_Resource('_:genid1'), $result[0]['s']
        );
        $this->assertEquals(
            new EasyRdf_Resource('http://xmlns.com/foaf/0.1/name'), $result[0]['p']
        );
        $this->assertEquals(
            new EasyRdf_Literal("Joe's Current Project"), $result[0]['o']
        );
    }

    public function testQueryAskTrueJson()
    {
        $this->_client->addMock(
            'GET', '/sparql?query=ASK+WHERE+%7B%3Fs+%3Fp+%3Fo%7D',
            readFixture('sparql_ask_true.json'),
            array(
                'headers' => array('Content-Type' => 'application/sparql-results+json'
            )
        ));
        $result = $this->_sparql->query("ASK WHERE {?s ?p ?o}");
        $this->assertEquals(true, $result);
    }

    public function testQueryAskFalseJson()
    {
        $this->_client->addMock(
            'GET', '/sparql?query=ASK+WHERE+%7B%3Fs+%3Fp+%3Cfalse%3E%7D',
            readFixture('sparql_ask_false.json'),
            array(
                'headers' => array('Content-Type' => 'application/sparql-results+json'
            )
        ));
        $result = $this->_sparql->query("ASK WHERE {?s ?p <false>}");
        $this->assertEquals(false, $result);
    }

    public function testQueryAskTrueXml()
    {
        $this->_client->addMock(
            'GET', '/sparql?query=ASK+WHERE+%7B%3Fs+%3Fp+%3Fo%7D',
            readFixture('sparql_ask_true.xml'),
            array(
                'headers' => array('Content-Type' => 'application/sparql-results+xml'
            )
        ));
        $result = $this->_sparql->query("ASK WHERE {?s ?p ?o}");
        $this->assertEquals(true, $result);
    }

    public function testQueryAskFalseXml()
    {
        $this->_client->addMock(
            'GET', '/sparql?query=ASK+WHERE+%7B%3Fs+%3Fp+%3Cfalse%3E%7D',
            readFixture('sparql_ask_false.xml'),
            array(
                'headers' => array('Content-Type' => 'application/sparql-results+xml'
            )
        ));
        $result = $this->_sparql->query("ASK WHERE {?s ?p <false>}");
        $this->assertEquals(false, $result);
    }

    public function testQueryConstructJson()
    {
        $this->_client->addMock(
            'GET', '/sparql?query=CONSTRUCT+%7B%3Fs+%3Fp+%3Fo%7D+WHERE+%7B%3Fs+%3Fp+%3Fo%7D',
            readFixture('foaf.json')
        );
        $graph = $this->_sparql->query("CONSTRUCT {?s ?p ?o} WHERE {?s ?p ?o}");
        $this->assertType('EasyRdf_Graph', $graph);
        $name = $graph->get('http://www.example.com/joe#me', 'foaf:name');
        $this->assertStringEquals('Joe Bloggs', $name);
    }

    public function testToString()
    {
        $this->assertStringEquals(
            'http://localhost:8080/sparql',
            $this->_sparql
        );
    }

}
