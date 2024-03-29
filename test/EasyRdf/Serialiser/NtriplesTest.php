<?php
namespace EasyRdf\Serialiser;

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

use EasyRdf\Graph;
use EasyRdf\Literal;
use EasyRdf\RdfNamespace;
use EasyRdf\Resource;
use EasyRdf\TestCase;

require_once dirname(dirname(dirname(__FILE__))).
             DIRECTORY_SEPARATOR.'TestHelper.php';

class NtriplesTest extends TestCase
{
    /** @var Ntriples */
    protected $serialiser = null;
    /** @var Graph */
    protected $graph = null;

    public function setUp(): void
    {
        $this->graph = new Graph();
        $this->serialiser = new Ntriples();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        RdfNamespace::resetNamespaces();
        RdfNamespace::reset();
    }

    public function testSerialiseValueUriResource()
    {
        $this->assertSame(
            "<http://example.com/>",
            $this->serialiser->serialiseValue(
                new Resource("http://example.com/")
            )
        );
    }

    public function testSerialiseValueUriArray()
    {
        $this->assertSame(
            "<http://example.com/>",
            $this->serialiser->serialiseValue(
                array('type' => 'uri', 'value' => 'http://example.com/')
            )
        );
    }

    public function testSerialiseValueBnodeArray()
    {
        $this->assertSame(
            "_:one",
            $this->serialiser->serialiseValue(
                array('type' => 'bnode', 'value' => '_:one')
            )
        );
    }

    public function testSerialiseValueBnodeResource()
    {
        $this->assertSame(
            "_:two",
            $this->serialiser->serialiseValue(
                new Resource("_:two")
            )
        );
    }

    public function testSerialiseValueLiteralArray()
    {
        $this->assertSame(
            '"foo"',
            $this->serialiser->serialiseValue(
                array('type' => 'literal', 'value' => 'foo')
            )
        );
    }

    public function testSerialiseValueLiteralObject()
    {
        $this->assertSame(
            '"Hello"',
            $this->serialiser->serialiseValue(
                new Literal("Hello")
            )
        );
    }

    public function testSerialiseValueLiteralObjectWithDatatype()
    {
        $this->assertSame(
            '"10"^^<http://www.w3.org/2001/XMLSchema#integer>',
            $this->serialiser->serialiseValue(
                Literal::create(10)
            )
        );
    }

    public function testSerialiseValueLiteralObjectWithLang()
    {
        $this->assertSame(
            '"Hello World"@en',
            $this->serialiser->serialiseValue(
                new Literal('Hello World', 'en')
            )
        );
    }

    public function testSerialiseBadValue()
    {
        $this->setExpectedException(
            'EasyRdf\Exception',
            "Unable to serialise object of type 'chipmonk' to ntriples"
        );
        $this->serialiser->serialiseValue(
            array('type' => 'chipmonk', 'value' => 'yes?')
        );
    }

    public function testSerialise()
    {
        $joe = $this->graph->resource(
            'http://www.example.com/joe#me',
            'foaf:Person'
        );
        $joe->set('foaf:name', 'Joe Bloggs');
        $joe->set(
            'foaf:homepage',
            $this->graph->resource('http://www.example.com/joe/')
        );
        $this->assertSame(
            "<http://www.example.com/joe#me> ".
            "<http://www.w3.org/1999/02/22-rdf-syntax-ns#type> ".
            "<http://xmlns.com/foaf/0.1/Person> .\n".
            "<http://www.example.com/joe#me> ".
            "<http://xmlns.com/foaf/0.1/name> ".
            "\"Joe Bloggs\" .\n".
            "<http://www.example.com/joe#me> ".
            "<http://xmlns.com/foaf/0.1/homepage> ".
            "<http://www.example.com/joe/> .\n",
            $this->serialiser->serialise($this->graph, 'ntriples')
        );
    }

    public function testSerialiseQuotes()
    {
        $joe = $this->graph->resource('http://www.example.com/joe#me');
        $joe->set('foaf:nick', '"Joey"');
        $this->assertSame(
            "<http://www.example.com/joe#me> ".
            "<http://xmlns.com/foaf/0.1/nick> ".
            '"\"Joey\"" .'."\n",
            $this->serialiser->serialise($this->graph, 'ntriples')
        );
    }

    public function testSerialiseBackslash()
    {
        $joe = $this->graph->resource('http://www.example.com/joe#me');
        $joe->set('foaf:nick', '\\backslash');
        $this->assertSame(
            "<http://www.example.com/joe#me> ".
            "<http://xmlns.com/foaf/0.1/nick> ".
            '"\\\\backslash" .'."\n",
            $this->serialiser->serialise($this->graph, 'ntriples')
        );
    }

    public function testSerialiseBNode()
    {
        $joe = $this->graph->resource('http://www.example.com/joe#me');
        $project = $this->graph->newBNode();
        $project->add('foaf:name', 'Project Name');
        $joe->add('foaf:project', $project);

        $this->assertSame(
            "_:genid1 <http://xmlns.com/foaf/0.1/name> \"Project Name\" .\n".
            "<http://www.example.com/joe#me> ".
            "<http://xmlns.com/foaf/0.1/project> _:genid1 .\n",
            $this->serialiser->serialise($this->graph, 'ntriples')
        );
    }
    public function testSerialiseLang()
    {
        $joe = $this->graph->resource('http://example.com/joe#me');
        $joe->set('foaf:name', new Literal('Joe', 'en'));

        $turtle = $this->serialiser->serialise($this->graph, 'ntriples');
        $this->assertStringEquals(
            "<http://example.com/joe#me> ".
            "<http://xmlns.com/foaf/0.1/name> ".
            "\"Joe\"@en .\n",
            $turtle
        );
    }

    public function testSerialiseDatatype()
    {
        $joe = $this->graph->resource('http://example.com/joe#me');
        $joe->set('foaf:foo', Literal::create(1, null, 'xsd:integer'));

        $ntriples = $this->serialiser->serialise($this->graph, 'ntriples');
        $this->assertStringEquals(
            "<http://example.com/joe#me> ".
            "<http://xmlns.com/foaf/0.1/foo> ".
            "\"1\"^^<http://www.w3.org/2001/XMLSchema#integer> .\n",
            $ntriples
        );
    }

    public function testSerialiseEmptyPrefix()
    {
        RdfNamespace::set('', 'http://foo/bar/');

        $joe = $this->graph->resource(
            'http://foo/bar/me'
        );

        $joe->set('foaf:name', 'Joe Bloggs');
        $joe->set(
            'foaf:homepage',
            $this->graph->resource('http://example.com/joe/')
        );

        $ntriples = $this->serialiser->serialise($this->graph, 'ntriples');

        $this->assertSame(
            "<http://foo/bar/me> <http://xmlns.com/foaf/0.1/name> \"Joe Bloggs\" .\n" .
            "<http://foo/bar/me> <http://xmlns.com/foaf/0.1/homepage> <http://example.com/joe/> .\n",
            $ntriples
        );
    }

    public function testSerialiseUnsupportedFormat()
    {
        $this->setExpectedException(
            'EasyRdf\Exception',
            'EasyRdf\Serialiser\Ntriples does not support: unsupportedformat'
        );
        $this->serialiser->serialise(
            $this->graph,
            'unsupportedformat'
        );
    }

    /**
     * @see https://github.com/easyrdf/easyrdf/issues/219
     * @see https://phabricator.wikimedia.org/T76854
     */
    public function testIssue219Unicode()
    {
        $pairs = array(
            '位' => '"\u4F4D"',
            "Дуглас Адамс" => '"\u0414\u0443\u0433\u043B\u0430\u0441 \u0410\u0434\u0430\u043C\u0441"',
        );

        $serializer = new Ntriples();

        foreach ($pairs as $string => $expected) {
            $literal = new Literal($string);
            $actual = $serializer->serialiseValue($literal);

            $this->assertEquals($expected, $actual);
        }
    }
}
