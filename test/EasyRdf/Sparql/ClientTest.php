<?php
namespace EasyRdf\Sparql;

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009-2020 Nicholas J Humfrey.  All rights reserved.
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
 * @copyright  Copyright (c) 2009-2020 Nicholas J Humfrey
 * @license    https://www.opensource.org/licenses/bsd-license.php
 */

use EasyRdf\Http;
use EasyRdf\Http\MockClient;
use EasyRdf\Literal;
use EasyRdf\Resource;
use EasyRdf\TestCase;

require_once realpath(__DIR__ . '/../../') . '/TestHelper.php';


class ClientTest extends TestCase
{
    /** @var  MockClient */
    private $client;

    /** @var  Client */
    private $sparql;

    public function setUp()
    {
        Http::setDefaultHttpClient(
            $this->client = new MockClient()
        );
        $this->sparql = new Client('http://localhost:8080/sparql');
    }

    # FIXME: this is deprecated
    public function testGetUri()
    {
        $this->assertSame(
            'http://localhost:8080/sparql',
            $this->sparql->getUri()
        );
    }

    public function testGetQueryUri()
    {
        $this->assertSame(
            'http://localhost:8080/sparql',
            $this->sparql->getQueryUri()
        );
    }

    public function testGetUpdateUri()
    {
        $this->assertSame(
            'http://localhost:8080/sparql',
            $this->sparql->getUpdateUri()
        );
    }

    public function testGetDifferentUpdateUri()
    {
        $sparql = new Client(
            'http://localhost/query',
            'http://localhost/update'
        );
        $this->assertSame(
            'http://localhost/query',
            $sparql->getQueryUri()
        );
        $this->assertSame(
            'http://localhost/update',
            $sparql->getUpdateUri()
        );
    }

    public function testQuerySelectAll()
    {
        $this->client->addMock(
            'GET',
            '/sparql?query=SELECT+%2A+WHERE+%7B%3Fs+%3Fp+%3Fo%7D',
            readFixture('sparql_select_all.xml'),
            array(
                'headers' => array('Content-Type' => 'application/sparql-results+xml')
            )
        );
        $result = $this->sparql->query("SELECT * WHERE {?s ?p ?o}");
        $this->assertCount(14, $result);
        $this->assertEquals(
            new Resource('_:genid1'),
            $result[0]->s
        );
        $this->assertEquals(
            new Resource('http://xmlns.com/foaf/0.1/name'),
            $result[0]->p
        );
        $this->assertEquals(
            new Literal("Joe's Current Project"),
            $result[0]->o
        );
    }

    public function testQuerySelectAllJsonWithCharset()
    {
        $this->client->addMock(
            'GET',
            '/sparql?query=SELECT+%2A+WHERE+%7B%3Fs+%3Fp+%3Fo%7D',
            readFixture('sparql_select_all.json'),
            array(
                'headers' => array('Content-Type' => 'application/sparql-results+json; charset=utf-8')
            )
        );
        $result = $this->sparql->query("SELECT * WHERE {?s ?p ?o}");
        $this->assertCount(14, $result);
        $this->assertSame(3, $result->numFields());
        $this->assertSame(array('s','p','o'), $result->getFields());
        $this->assertEquals(
            new Literal("Joe's Current Project"),
            $result[0]->o
        );
    }

    public function testQuerySelectAllUnsupportedFormat()
    {
        $this->client->addMock(
            'GET',
            '/sparql?query=SELECT+%2A+WHERE+%7B%3Fs+%3Fp+%3Fo%7D',
            readFixture('sparql_select_all.json'),
            array(
                'headers' => array('Content-Type' => 'unsupported/format')
            )
        );
        $this->setExpectedException(
            'EasyRdf\Exception',
            'Format is not recognised: unsupported/format'
        );
        $this->sparql->query("SELECT * WHERE {?s ?p ?o}");
    }

    public function checkHugeQuerySelect($client)
    {
        $this->assertRegExp('/^query=/', $client->getRawData());
        $this->assertSame("application/x-www-form-urlencoded", $client->getHeader('Content-Type'));
        return true;
    }

    public function testHugeQuerySelect()
    {
        $this->client->addMock(
            'POST',
            '/sparql',
            readFixture('sparql_select_all.json'),
            array(
                'headers' => array('Content-Type' => 'application/sparql-results+json'),
                'callback' => array($this, 'checkHugeQuerySelect')
            )
        );

        // Add extra 2k+ of comment to start of query
        $padding = str_repeat("# comment 012345678901234567890123456789\n", 50);
        $result = $this->sparql->query("$padding SELECT * WHERE {?s ?p ?o}");
        $this->assertCount(14, $result);
    }

    public function testQueryAddPrefix()
    {
        $this->client->addMock(
            'GET',
            '/sparql?query=PREFIX+rdf%3A+%3Chttp%3A%2F%2Fwww.w3.org%2F'.
            '1999%2F02%2F22-rdf-syntax-ns%23%3E%0ASELECT+%3Ft+WHERE+'.
            '%7B%3Fs+rdf%3Atype+%3Ft%7D',
            readFixture('sparql_select_all_types.xml'),
            array(
                'headers' => array('Content-Type' => 'application/sparql-results+xml')
            )
        );
        $result = $this->sparql->query("SELECT ?t WHERE {?s rdf:type ?t}");
        $this->assertCount(3, $result);
        $this->assertEquals(
            new Resource('http://xmlns.com/foaf/0.1/Project'),
            $result[0]->t
        );
        $this->assertEquals(
            new Resource('http://xmlns.com/foaf/0.1/PersonalProfileDocument'),
            $result[1]->t
        );
        $this->assertEquals(
            new Resource('http://xmlns.com/foaf/0.1/Person'),
            $result[2]->t
        );
    }

    public function testQueryAskTrue()
    {
        $this->client->addMock(
            'GET',
            '/sparql?query=ASK+WHERE+%7B%3Fs+%3Fp+%3Fo%7D',
            readFixture('sparql_ask_true.xml'),
            array(
                'headers' => array('Content-Type' => 'application/sparql-results+xml')
            )
        );
        $result = $this->sparql->query("ASK WHERE {?s ?p ?o}");
        $this->assertSame(true, $result->getBoolean());
    }

    public function testQueryAskFalse()
    {
        $this->client->addMock(
            'GET',
            '/sparql?query=ASK+WHERE+%7B%3Fs+%3Fp+%3Cfalse%3E%7D',
            readFixture('sparql_ask_false.xml'),
            array(
                'headers' => array('Content-Type' => 'application/sparql-results+xml')
            )
        );
        $result = $this->sparql->query("ASK WHERE {?s ?p <false>}");
        $this->assertSame(false, $result->getBoolean());
    }

    public function testQueryConstructJson()
    {
        $this->client->addMock(
            'GET',
            '/sparql?query=CONSTRUCT+%7B%3Fs+%3Fp+%3Fo%7D+WHERE+%7B%3Fs+%3Fp+%3Fo%7D',
            readFixture('foaf.json'),
            array(
                'headers' => array('Content-Type' => 'application/json')
            )
        );
        $graph = $this->sparql->query("CONSTRUCT {?s ?p ?o} WHERE {?s ?p ?o}");
        $this->assertClass('EasyRdf\Graph', $graph);
        $name = $graph->get('http://www.example.com/joe#me', 'foaf:name');
        $this->assertStringEquals('Joe Bloggs', $name);
    }

    public function testQueryConstructJsonWithCharset()
    {
        $this->client->addMock(
            'GET',
            '/sparql?query=CONSTRUCT+%7B%3Fs+%3Fp+%3Fo%7D+WHERE+%7B%3Fs+%3Fp+%3Fo%7D',
            readFixture('foaf.json'),
            array(
                'headers' => array('Content-Type' => 'application/json; charset=utf-8')
            )
        );
        $graph = $this->sparql->query("CONSTRUCT {?s ?p ?o} WHERE {?s ?p ?o}");
        $this->assertClass('EasyRdf\Graph', $graph);
        $name = $graph->get('http://www.example.com/joe#me', 'foaf:name');
        $this->assertStringEquals('Joe Bloggs', $name);
    }

    public function testQueryInvalid()
    {
        $body = "There was an error while executing the query.\nSPARQL syntax error at 'F', line 1";

        $this->client->addMock(
            'GET',
            '/sparql?query=FOOBAR',
            $body,
            array(
                'status' => 500,
                'headers' => array('Content-Type' => 'text/plain')
            )
        );

        try {
            $this->sparql->query("FOOBAR");
            $this->fail('Invalid query should have resulted in an exception');
        } catch (Http\Exception $e) {
            $this->assertEquals('HTTP request for SPARQL query failed', $e->getMessage());
            $this->assertEquals($body, $e->getBody());
        }
    }

    public function testCountTriples()
    {
        $this->client->addMock(
            'GET',
            '/sparql?query=SELECT+%28COUNT%28%2A%29+AS+%3Fcount%29+%7B%3Fs+%3Fp+%3Fo%7D',
            readFixture('sparql_select_count.json'),
            array(
                'headers' => array('Content-Type' => 'application/sparql-results+json; charset=utf-8')
            )
        );
        $count = $this->sparql->countTriples();
        $this->assertSame(143, $count);
    }

    public function testCountTriplesWithCondition()
    {
        $this->client->addMock(
            'GET',
            '/sparql?query=PREFIX+foaf%3A+%3Chttp%3A%2F%2Fxmlns.com%2Ffoaf%2F0.1%2F%3E%0A'.
            'SELECT+%28COUNT%28%2A%29+AS+%3Fcount%29+%7B%3Fs+a+foaf%3APerson%7D',
            readFixture('sparql_select_count_zero.json'),
            array(
                'headers' => array('Content-Type' => 'application/sparql-results+json; charset=utf-8')
            )
        );
        $count = $this->sparql->countTriples('?s a foaf:Person');
        $this->assertSame(0, $count);
    }

    public function testListNamedGraphs()
    {
        $this->client->addMock(
            'GET',
            '/sparql?query=SELECT+DISTINCT+%3Fg+WHERE+%7BGRAPH+%3Fg+%7B%3Fs+%3Fp+%3Fo%7D%7D',
            readFixture('sparql_select_named_graphs.json'),
            array(
                'headers' => array('Content-Type' => 'application/sparql-results+json; charset=utf-8')
            )
        );
        $list = $this->sparql->listNamedGraphs();
        $this->assertCount(3, $list);
        $this->assertEquals(new Resource('http://example.org/0'), $list[0]);
        $this->assertEquals(new Resource('http://example.org/1'), $list[1]);
        $this->assertEquals(new Resource('http://example.org/2'), $list[2]);
    }

    public function testListNamedGraphsWithLimit()
    {
        $this->client->addMock(
            'GET',
            '/sparql?query=SELECT+DISTINCT+%3Fg+WHERE+%7BGRAPH+%3Fg+%7B%3Fs+%3Fp+%3Fo%7D%7D+LIMIT+10',
            readFixture('sparql_select_named_graphs.json'),
            array(
                'headers' => array('Content-Type' => 'application/sparql-results+json; charset=utf-8')
            )
        );
        $list = $this->sparql->listNamedGraphs(10);
        $this->assertCount(3, $list);
        $this->assertEquals(new Resource('http://example.org/0'), $list[0]);
        $this->assertEquals(new Resource('http://example.org/1'), $list[1]);
        $this->assertEquals(new Resource('http://example.org/2'), $list[2]);
    }

    public function checkUpdate(Http\Client $client)
    {
        $this->assertSame('INSERT DATA { <a> <p> <b> }', $client->getRawData());
        $this->assertSame("application/sparql-update", $client->getHeader('Content-Type'));
        return true;
    }

    public function testUpdate()
    {
        $this->client->addMock(
            'POST',
            '/sparql',
            '',
            array(
                'status' => 204,
                'callback' => array($this, 'checkUpdate')
            )
        );

        $result = $this->sparql->update('INSERT DATA { <a> <p> <b> }');
        $this->assertSame(204, $result->getStatus());
    }

    public function testQueryEndpointWithParameters()
    {
        $this->sparql = new Client('http://localhost:8080/sparql?a=b');

        $this->client->addMock(
            'GET',
            '/sparql?a=b&query=SELECT+%2A+WHERE+%7B%3Fs+%3Fp+%3Fo%7D',
            readFixture('sparql_select_all.xml'),
            array(
                'headers' => array('Content-Type' => 'application/sparql-results+xml')
            )
        );
        $result = $this->sparql->query("SELECT * WHERE {?s ?p ?o}");
        $this->assertCount(14, $result);
        $this->assertEquals(
            new Resource('_:genid1'),
            $result[0]->s
        );
        $this->assertEquals(
            new Resource('http://xmlns.com/foaf/0.1/name'),
            $result[0]->p
        );
        $this->assertEquals(
            new Literal("Joe's Current Project"),
            $result[0]->o
        );
    }

    /**
     * Make sure, that different queries have different Accept headers
     * This is important for compatibility with real-world triplestores
     * @see https://github.com/easyrdf/easyrdf/issues/226
     * @see https://github.com/easyrdf/easyrdf/issues/231
     */
    public function testAcceptHeaders()
    {
        // Graph queries
        $this->client->addMock(null, null, null);  // we do not care about request-details here
        $this->sparql->query('CONSTRUCT {?s ?p ?o} WHERE {?s ?p ?o}');

        $types = self::parseAcceptHeader($this->client->getHeader('Accept'));
        $this->assertContains('text/turtle', $types);
        $this->assertNotContains('application/sparql-results+json', $types);

        $this->client->addMock(null, null, null);  // we do not care about request-details here
        $this->sparql->query('DESCRIBE <http://www.example.org/example>');

        $types = self::parseAcceptHeader($this->client->getHeader('Accept'));
        $this->assertContains('text/turtle', $types);
        $this->assertNotContains('application/sparql-results+json', $types);

        // Tabular queries
        $this->client->addMock(null, null, null);  // we do not care about request-details here
        $this->sparql->query('SELECT * WHERE {?s ?p ?o}');

        $types = self::parseAcceptHeader($this->client->getHeader('Accept'));
        $this->assertContains('application/sparql-results+json', $types);
        $this->assertNotContains('text/turtle', $types);

        $this->client->addMock(null, null, null);  // we do not care about request-details here
        $this->sparql->query('ASK {<http://example.com/foo> <http://example.com/bar> true}');

        $types = self::parseAcceptHeader($this->client->getHeader('Accept'));
        $this->assertContains('application/sparql-results+json', $types);
        $this->assertNotContains('text/turtle', $types);

        // Update requests
        $this->client->addMock(null, null, null);  // we do not care about request-details here
        $this->sparql->update('INSERT DATA {<http://example.com/foo> <http://example.com/bar> true}');

        $types = self::parseAcceptHeader($this->client->getHeader('Accept'));
        $this->assertContains('application/sparql-results+json', $types);
        $this->assertContains('text/turtle', $types);
    }

    private static function parseAcceptHeader($accept_str)
    {
        $types = array();

        $pieces = explode(',', $accept_str);
        foreach ($pieces as $piece) {
            list($type,) = explode(';', $piece, 2);
            $types[] = $type;
        }

        return $types;
    }
}
