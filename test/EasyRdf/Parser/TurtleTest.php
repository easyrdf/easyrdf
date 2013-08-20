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

require_once dirname(dirname(dirname(__FILE__))).
             DIRECTORY_SEPARATOR.'TestHelper.php';

require_once 'EasyRdf/Parser/Turtle.php';
require_once 'EasyRdf/Serialiser/NtriplesArray.php';

class EasyRdf_Parser_TurtleTest extends EasyRdf_TestCase
{
    protected $parser = null;

    public function setUp()
    {
        $this->turtleParser = new EasyRdf_Parser_Turtle();
        $this->ntriplesParser = new EasyRdf_Parser_Ntriples();
        $this->baseUri = 'http://www.w3.org/2001/sw/DataAccess/df1/tests/';
    }

    public function testParseFoaf()
    {
        $graph = new EasyRdf_Graph();
        $count = $this->turtleParser->parse(
            $graph,
            readFixture('foaf.ttl'),
            'turtle',
            $this->baseUri
        );
        $this->assertSame(14, $count);

        $joe = $graph->resource('http://www.example.com/joe#me');
        $this->assertNotNull($joe);
        $this->assertClass('EasyRdf_Resource', $joe);
        $this->assertSame('http://www.example.com/joe#me', $joe->getUri());

        $name = $joe->get('foaf:name');
        $this->assertNotNull($name);
        $this->assertClass('EasyRdf_Literal', $name);
        $this->assertStringEquals('Joe Bloggs', $name);
        $this->assertSame('en', $name->getLang());
        $this->assertSame(null, $name->getDatatype());

        $foaf = $graph->resource('http://www.example.com/joe/foaf.rdf');
        $this->assertNotNull($foaf);
        $this->assertStringEquals("Joe Bloggs' FOAF File", $foaf->label());
    }

    public function testParseCollection()
    {
        $graph = new EasyRdf_Graph();
        $count = $this->turtleParser->parse(
            $graph,
            "<http://example.com/s> <http://example.com/p> ('A' 'B' 'C' 'D') .",
            'turtle',
            $this->baseUri
        );
        $this->assertSame(9, $count);

        $array = array();
        $collection = $graph->resource('http://example.com/s')->get('<http://example.com/p>');
        foreach ($collection as $item) {
            $array[] = strval($item);
        }

        $this->assertEquals(
            array('A', 'B', 'C', 'D'),
            $array
        );
    }

    public function testParseUnsupportedFormat()
    {
        $this->setExpectedException(
            'EasyRdf_Exception',
            'EasyRdf_Parser_Turtle does not support: unsupportedformat'
        );
        $this->turtleParser->parse(
            new EasyRdf_Graph(),
            'data',
            'unsupportedformat',
            null
        );
    }

    /* The rest of this script is runs the Turtle Test Suite
       from the files here:
       http://www.w3.org/TeamSubmission/turtle/tests/
     */

    protected function parseTurtle($filename)
    {
        $graph = new EasyRdf_Graph();
        $this->turtleParser->parse(
            $graph,
            readFixture($filename),
            'turtle',
            $this->baseUri . basename($filename)
        );
        return $graph->serialise('ntriples-array');
    }

    protected function parseNtriples($filename)
    {
        $graph = new EasyRdf_Graph();
        $this->ntriplesParser->parse(
            $graph,
            readFixture($filename),
            'ntriples',
            $this->baseUri . basename($filename)
        );
        return $graph->serialise('ntriples-array');
    }

    protected function turtleTestCase($name)
    {
        $this->assertSame(
            $this->parseNtriples("turtle/$name.out"),
            $this->parseTurtle("turtle/$name.ttl")
        );
    }

    public function testCase00()
    {
        # Blank subject
        $this->turtleTestCase('test-00');
    }

    public function testCase01()
    {
        # @prefix and qnames
        $this->turtleTestCase('test-01');
    }

    public function testCase02()
    {
        # , operator
        $this->turtleTestCase('test-02');
    }

    public function testCase03()
    {
        # ; operator
        $this->turtleTestCase('test-03');
    }

    public function testCase04()
    {
        # empty [] as subject and object
        $this->turtleTestCase('test-04');
    }

    public function testCase05()
    {
        # non-empty [] as subject and object
        $this->turtleTestCase('test-05');
    }

    public function testCase06()
    {
        # 'a' as predicate
        $this->turtleTestCase('test-06');
    }

    public function testCase07()
    {
        # simple collection
        $this->turtleTestCase('test-07');
    }

    public function testCase08()
    {
        # empty collection
        $this->turtleTestCase('test-08');
    }

    public function testCase09()
    {
        # integer datatyped literal
        $this->turtleTestCase('test-09');
    }

    public function testCase10()
    {
        # decimal integer canonicalization
        $this->turtleTestCase('test-10');
    }

    public function testCase11()
    {
        # - and _ in names and qnames
        $this->turtleTestCase('test-11');
    }

    public function testCase12()
    {
        # tests for rdf:_<numbers> and other qnames starting with _
        $this->turtleTestCase('test-12');
    }

    public function testCase13()
    {
        # bare : allowed
        $this->turtleTestCase('test-13');
    }

    // Removed tests 14-16 because they take a long time to run

    public function testCase17()
    {
        # simple long literal
        $this->turtleTestCase('test-17');
    }

    public function testCase18()
    {
        # long literals with escapes
        $this->turtleTestCase('test-18');
    }

    public function testCase19()
    {
        # floating point number
        $this->turtleTestCase('test-19');
    }

    public function testCase20()
    {
        # empty literals, normal and long variant
        $this->turtleTestCase('test-20');
    }

    public function testCase21()
    {
        # positive integer, decimal and doubles
        $this->turtleTestCase('test-21');
    }

    public function testCase22()
    {
        # negative integer, decimal and doubles
        $this->turtleTestCase('test-22');
    }

    public function testCase23()
    {
        # long literal ending in double quote
        $this->turtleTestCase('test-23');
    }

    public function testCase24()
    {
        # boolean literals
        $this->turtleTestCase('test-24');
    }

    public function testCase25()
    {
        # comments
        $this->turtleTestCase('test-25');
    }

    public function testCase26()
    {
        # no final newline
        $this->turtleTestCase('test-26');
    }

    public function testCase27()
    {
        # duplicate prefix
        $this->turtleTestCase('test-27');
    }

    public function testTurtleSyntaxPnameEsc01()
    {
        $this->turtleTestCase('turtle-syntax-pname-esc-01');
    }

    public function testTurtleSyntaxPnameEsc02()
    {
        $this->turtleTestCase('turtle-syntax-pname-esc-02');
    }

    public function testTurtleSyntaxPnameEsc03()
    {
        $this->turtleTestCase('turtle-syntax-pname-esc-03');
    }

    public function testBase1()
    {
        # Resolution of a relative URI against an absolute base.
        $this->turtleTestCase('base1');
    }

    public function testRdfSchema()
    {
        $this->turtleTestCase('rdf-schema');
    }

    public function testQuotes1()
    {
        $this->turtleTestCase('quotes1');
    }

    public function testBad00()
    {
        # prefix name must end in a :
        $this->setExpectedException(
            'EasyRdf_Parser_Exception',
            "Turtle Parse Error: expected ':', found '<' on line 2, column 12"
        );
        $this->parseTurtle("turtle/bad-00.ttl");
    }

    public function testBad01()
    {
        # Forbidden by RDF - predicate cannot be blank
        $this->setExpectedException(
            'EasyRdf_Parser_Exception',
            "Turtle Parse Error: expected an RDF value here, found '[' on line 3, column 4"
        );
        $this->parseTurtle("turtle/bad-01.ttl");
    }

    public function testBad02()
    {
        # Forbidden by RDF - predicate cannot be blank
        $this->setExpectedException(
            'EasyRdf_Parser_Exception',
            "Turtle Parse Error: expected an RDF value here, found '[' on line 3, column 4"
        );
        $this->parseTurtle("turtle/bad-02.ttl");
    }

    public function testBad03()
    {
        # 'a' only allowed as a predicate
        $this->setExpectedException(
            'EasyRdf_Parser_Exception',
            "Turtle Parse Error: expected ':', found ' ' on line 3, column 3"
        );
        $this->parseTurtle("turtle/bad-03.ttl");
    }

    public function testBad04()
    {
        # No comma is allowed in collections
        $this->setExpectedException(
            'EasyRdf_Parser_Exception',
            "Turtle Parse Error: expected an RDF value here, found ',' on line 3, column 16"
        );
        $this->parseTurtle("turtle/bad-04.ttl");
    }

    public function testBad05()
    {
        # N3 {}s are not in Turtle
        $this->setExpectedException(
            'EasyRdf_Parser_Exception',
            "Turtle Parse Error: expected an RDF value here, found '{' on line 3, column 1"
        );
        $this->parseTurtle("turtle/bad-05.ttl");
    }

    public function testBad06()
    {
        # is and of are not in turtle
        $this->setExpectedException(
            'EasyRdf_Parser_Exception',
            "Turtle Parse Error: expected ':', found ' ' on line 3, column 7"
        );
        $this->parseTurtle("turtle/bad-06.ttl");
    }

    public function testBad07()
    {
        # paths are not in turtle
        $this->setExpectedException(
            'EasyRdf_Parser_Exception',
            "Turtle Parse Error: object for statement missing on line 3, column 5"
        );
        $this->parseTurtle("turtle/bad-07.ttl");
    }

    public function testBad08()
    {
        # @keywords is not in turtle
        $this->setExpectedException(
            'EasyRdf_Parser_Exception',
            'Turtle Parse Error: unknown directive "@keywords" on line 1, column 10'
        );
        $this->parseTurtle("turtle/bad-08.ttl");
    }

    public function testBad09()
    {
        # implies is not in turtle
        $this->setExpectedException(
            'EasyRdf_Parser_Exception',
            "Turtle Parse Error: expected an RDF value here, found '=' on line 3, column 4"
        );
        $this->parseTurtle("turtle/bad-09.ttl");
    }

    public function testBad10()
    {
        # equivalence is not in turtle
        $this->setExpectedException(
            'EasyRdf_Parser_Exception',
            "Turtle Parse Error: expected an RDF value here, found '=' on line 3, column 4"
        );
        $this->parseTurtle("turtle/bad-10.ttl");
    }

    public function testBad11()
    {
        # @forAll is not in turtle
        $this->setExpectedException(
            'EasyRdf_Parser_Exception',
            "Turtle Parse Error: unknown directive \"@forall\" on line 3, column 8"
        );
        $this->parseTurtle("turtle/bad-11.ttl");
    }

    public function testBad12()
    {
        # @forSome is not in turtle
        $this->setExpectedException(
            'EasyRdf_Parser_Exception',
            "Turtle Parse Error: unknown directive \"@forsome\" on line 3, column 9"
        );
        $this->parseTurtle("turtle/bad-12.ttl");
    }

    public function testBad13()
    {
        # <= is not in turtle
        $this->setExpectedException(
            'EasyRdf_Parser_Exception',
            "Turtle Parse Error: unexpected end of file while reading URI on line 4, column 1"
        );
        $this->parseTurtle("turtle/bad-13.ttl");
    }

    public function testBad14()
    {
        # Test long literals with missing end
        $this->setExpectedException(
            'EasyRdf_Parser_Exception',
            "Turtle Parse Error: unexpected end of file while reading long string on line 7, column 1"
        );
        $this->parseTurtle("turtle/bad-14.ttl");
    }
}
