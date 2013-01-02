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

require_once dirname(dirname(dirname(__FILE__))).
             DIRECTORY_SEPARATOR.'TestHelper.php';

class EasyRdf_Serialiser_TurtleTest extends EasyRdf_TestCase
{
    protected $_serialiser = null;
    protected $_graph = null;

    public function setUp()
    {
        $this->_graph = new EasyRdf_Graph();
        $this->_serialiser = new EasyRdf_Serialiser_Turtle();
    }

    public function tearDown()
    {
        EasyRdf_Namespace::reset();
        EasyRdf_Namespace::delete('example');
    }

    public function testSerialise()
    {
        $joe = $this->_graph->resource(
            'http://example.com/joe#me',
            'foaf:Person'
        );
        $joe->set('foaf:name', 'Joe Bloggs');
        $joe->set(
            'foaf:homepage',
            $this->_graph->resource('http://example.com/joe/')
        );

        $turtle = $this->_serialiser->serialise($this->_graph, 'turtle');
        $this->assertSame(
            "@prefix foaf: <http://xmlns.com/foaf/0.1/> .\n".
            "\n".
            "<http://example.com/joe#me>\n".
            "  a foaf:Person ;\n".
            "  foaf:name \"Joe Bloggs\" ;\n".
            "  foaf:homepage <http://example.com/joe/> .\n\n",
            $turtle
        );
    }

    public function testSerialiseAnonymousSubject()
    {
        $joe = $this->_graph->resource('http://example.com/joe#me');
        $anon =  $this->_graph->newBnode();
        $anon->addLiteral('foaf:name', 'Anon');

        $turtle = $this->_serialiser->serialise($this->_graph, 'turtle');
        $this->assertSame(
            "@prefix foaf: <http://xmlns.com/foaf/0.1/> .\n".
            "\n".
            "[] foaf:name \"Anon\" .\n",
            $turtle
        );
    }

    public function testSerialiseBnode()
    {
        $joe = $this->_graph->resource('http://example.com/joe#me');
        $alice = $this->_graph->resource('http://example.com/alice#me');
        $project =  $this->_graph->newBnode();
        $project->add('foaf:name', 'Amazing Project');
        $joe->add('foaf:currentProject', $project);
        $alice->add('foaf:currentProject', $project);

        $turtle = $this->_serialiser->serialise($this->_graph, 'turtle');
        $this->assertSame(
            "@prefix foaf: <http://xmlns.com/foaf/0.1/> .\n\n".
            "<http://example.com/joe#me> foaf:currentProject _:genid1 .\n".
            "<http://example.com/alice#me> foaf:currentProject _:genid1 .\n".
            "_:genid1 foaf:name \"Amazing Project\" .\n",
            $turtle
        );
    }

    public function testSerialiseNestedBnode1()
    {
        $joe = $this->_graph->resource('http://example.com/joe#me');
        $amy =  $this->_graph->newBnode();
        $amy->addLiteral('foaf:name', 'Amy');
        $joe->add('foaf:knows', $amy);

        $turtle = $this->_serialiser->serialise($this->_graph, 'turtle');
        $this->assertSame(
            "@prefix foaf: <http://xmlns.com/foaf/0.1/> .\n".
            "\n".
            "<http://example.com/joe#me> foaf:knows [ foaf:name \"Amy\" ] .\n",
            $turtle
        );
    }

    public function testSerialiseNestedBnode2()
    {
        $doc = $this->_graph->resource('http://example.com/doc');
        $joe = $this->_graph->newBnode();
        $doc->set('dc:creator', $joe);
        $joe->set('foaf:name', 'Joe');
        $joe->addResource('foaf:homepage', 'http://example.com/joe');

        $turtle = $this->_serialiser->serialise($this->_graph, 'turtle');
        $this->assertSame(
            "@prefix dc: <http://purl.org/dc/terms/> .\n".
            "@prefix foaf: <http://xmlns.com/foaf/0.1/> .\n\n".
            "<http://example.com/doc> dc:creator [\n".
            "    foaf:name \"Joe\" ;\n".
            "    foaf:homepage <http://example.com/joe>\n".
            "  ] .\n",
            $turtle
        );
    }

    public function testSerialiseNestedBnode3()
    {
        $alice = $this->_graph->newBnode();
        $alice->add('foaf:name', 'Alice');
        $bob = $this->_graph->newBnode();
        $bob->add('foaf:name', 'Bob');
        $bob->addResource('foaf:mbox', 'mailto:bob@example.com');
        $eve = $this->_graph->newBnode();
        $eve->add('foaf:name', 'Eve');
        $alice->add('foaf:knows', $bob);
        $bob->add('foaf:knows', $eve);

        $turtle = $this->_serialiser->serialise($this->_graph, 'turtle');
        $this->assertSame(
            "@prefix foaf: <http://xmlns.com/foaf/0.1/> .\n\n".
            "[]\n".
            "  foaf:name \"Alice\" ;\n".
            "  foaf:knows [\n".
            "    foaf:name \"Bob\" ;\n".
            "    foaf:mbox <mailto:bob@example.com> ;\n".
            "    foaf:knows [ foaf:name \"Eve\" ]\n".
            "  ] .\n\n",
            $turtle
        );
    }

    public function testSerialiseNestedBnode4()
    {
        $joe =  $this->_graph->newBnode();
        $alice =  $this->_graph->newBnode();
        $joe->add('foaf:name', 'Joe');
        $alice->add('foaf:name', 'Alice');
        $joe->add('foaf:knows', $alice);
        $alice->add('foaf:knows', $joe);

        $turtle = $this->_serialiser->serialise($this->_graph, 'turtle');
        $this->assertSame(
            "@prefix foaf: <http://xmlns.com/foaf/0.1/> .\n\n".
            "_:genid1\n".
            "  foaf:name \"Joe\" ;\n".
            "  foaf:knows [\n".
            "    foaf:name \"Alice\" ;\n".
            "    foaf:knows _:genid1\n".
            "  ] .\n\n",
            $turtle
        );
    }

    public function testSerialiseLang()
    {
        $joe = $this->_graph->resource('http://example.com/joe#me');
        $joe->set('foaf:name', new EasyRdf_Literal('Joe', 'en'));

        $turtle = $this->_serialiser->serialise($this->_graph, 'turtle');
        $this->assertSame(
            "@prefix foaf: <http://xmlns.com/foaf/0.1/> .\n\n".
            "<http://example.com/joe#me> foaf:name \"Joe\"@en .\n",
            $turtle
        );
    }

    public function testSerialiseEscaped()
    {
        $joe = $this->_graph->resource('http://example.com/joe#me');
        $joe->set('foaf:name', '\n');

        $turtle = $this->_serialiser->serialise($this->_graph, 'turtle');
        $this->assertSame(
            "@prefix foaf: <http://xmlns.com/foaf/0.1/> .\n\n".
            "<http://example.com/joe#me> foaf:name \"\\\\n\" .\n",
            $turtle
        );
    }

    public function testSerialiseEscaped2()
    {
        $joe = $this->_graph->resource('http://example.com/joe#me');
        $joe->set('foaf:name', '"Joe"');

        $turtle = $this->_serialiser->serialise($this->_graph, 'turtle');
        $this->assertSame(
            "@prefix foaf: <http://xmlns.com/foaf/0.1/> .\n\n".
            "<http://example.com/joe#me> foaf:name \"\\\"Joe\\\"\" .\n",
            $turtle
        );
    }

    public function testSerialiseMultiLineEscaped()
    {
        $joe = $this->_graph->resource('http://example.com/joe#me');
        $joe->set('foaf:name', "Line 1\nLine 2");

        $turtle = $this->_serialiser->serialise($this->_graph, 'turtle');
        $this->assertSame(
            "@prefix foaf: <http://xmlns.com/foaf/0.1/> .\n\n".
            "<http://example.com/joe#me> foaf:name \"\"\"Line 1\nLine 2\"\"\" .\n",
            $turtle
        );
    }

    public function testSerialiseMultiLineEscaped2()
    {
        $joe = $this->_graph->resource('http://example.com/joe#me');
        $joe->set('foaf:name', "\t".'"""'."\t");

        $turtle = $this->_serialiser->serialise($this->_graph, 'turtle');
        $this->assertSame(
            "@prefix foaf: <http://xmlns.com/foaf/0.1/> .\n\n".
            "<http://example.com/joe#me> foaf:name \"\"\"\t\\\"\"\"\t\"\"\" .\n",
            $turtle
        );
    }

    public function testSerialiseBooleanDatatype()
    {
        $joe = $this->_graph->resource('http://example.com/joe#me');
        $joe->set('foaf:truth', EasyRdf_Literal::create(true));

        $turtle = $this->_serialiser->serialise($this->_graph, 'turtle');
        $this->assertSame(
            "@prefix foaf: <http://xmlns.com/foaf/0.1/> .\n".
            "@prefix xsd: <http://www.w3.org/2001/XMLSchema#> .\n\n".
            "<http://example.com/joe#me> foaf:truth true .\n",
            $turtle
        );
    }

    public function testSerialiseDecimalDatatype()
    {
        $joe = $this->_graph->resource('http://example.com/joe#me');
        $joe->set('foaf:age', new EasyRdf_Literal_Decimal(1.5));

        $turtle = $this->_serialiser->serialise($this->_graph, 'turtle');
        $this->assertSame(
            "@prefix foaf: <http://xmlns.com/foaf/0.1/> .\n".
            "@prefix xsd: <http://www.w3.org/2001/XMLSchema#> .\n\n".
            "<http://example.com/joe#me> foaf:age 1.5 .\n",
            $turtle
        );
    }

    public function testSerialiseDoubleDatatype()
    {
        $joe = $this->_graph->resource('http://example.com/joe#me');
        $joe->set('foaf:age', EasyRdf_Literal::create(1.5, null, 'xsd:double'));

        $turtle = $this->_serialiser->serialise($this->_graph, 'turtle');
        $this->assertSame(
            "@prefix foaf: <http://xmlns.com/foaf/0.1/> .\n".
            "@prefix xsd: <http://www.w3.org/2001/XMLSchema#> .\n\n".
            "<http://example.com/joe#me> foaf:age 1.500000e+0 .\n",
            $turtle
        );
    }

    public function testSerialiseIntegerDatatype()
    {
        $joe = $this->_graph->resource('http://example.com/joe#me');
        $joe->set('foaf:age', new EasyRdf_Literal_Integer(49));

        $turtle = $this->_serialiser->serialise($this->_graph, 'turtle');
        $this->assertSame(
            "@prefix foaf: <http://xmlns.com/foaf/0.1/> .\n".
            "@prefix xsd: <http://www.w3.org/2001/XMLSchema#> .\n\n".
            "<http://example.com/joe#me> foaf:age 49 .\n",
            $turtle
        );
    }

    public function testSerialiseDateTimeDatatype()
    {
        $doc = $this->_graph->resource('http://example.com/');
        $doc->set('dc:date', new EasyRdf_Literal_DateTime('2012-11-04T13:01:26+01:00'));

        $turtle = $this->_serialiser->serialise($this->_graph, 'turtle');
        $this->assertSame(
            "@prefix dc: <http://purl.org/dc/terms/> .\n".
            "@prefix xsd: <http://www.w3.org/2001/XMLSchema#> .\n\n".
            "<http://example.com/> dc:date \"2012-11-04T13:01:26+01:00\"^^xsd:dateTime .\n",
            $turtle
        );
    }

    public function testSerialiseOtherDatatype()
    {
        $joe = $this->_graph->resource('http://example.com/joe#me');
        $joe->set('foaf:foo', EasyRdf_Literal::create('foobar', null, 'xsd:other'));

        $turtle = $this->_serialiser->serialise($this->_graph, 'turtle');
        $this->assertSame(
            "@prefix foaf: <http://xmlns.com/foaf/0.1/> .\n".
            "@prefix xsd: <http://www.w3.org/2001/XMLSchema#> .\n\n".
            "<http://example.com/joe#me> foaf:foo \"foobar\"^^xsd:other .\n",
            $turtle
        );
    }

    public function testSerialiseUnknownDatatype()
    {
        $joe = $this->_graph->resource('http://example.com/joe#me');
        $joe->set(
            'foaf:foo',
            EasyRdf_Literal::create('foobar', null, 'http://example.com/ns/type')
        );

        $turtle = $this->_serialiser->serialise($this->_graph, 'turtle');
        $this->assertSame(
            "@prefix foaf: <http://xmlns.com/foaf/0.1/> .\n".
            "@prefix ns0: <http://example.com/ns/> .\n\n".
            "<http://example.com/joe#me> foaf:foo \"foobar\"^^ns0:type .\n",
            $turtle
        );
    }

    public function testSerialiseShortenableResource()
    {
        EasyRdf_Namespace::set("example", 'http://example.com/');
        $joe = $this->_graph->resource('http://example.com/joe#me');
        $joe->add('rdf:type', 'foaf:Person');

        $turtle = $this->_serialiser->serialise($this->_graph, 'turtle');
        $this->assertSame(
            "@prefix example: <http://example.com/> .\n\n".
            "example:joe#me a \"foaf:Person\" .\n",
            $turtle
        );
    }

    public function testSerialiseUnshortenableDatatype()
    {
        $joe = $this->_graph->resource('http://example.com/joe#me');
        $joe->set(
            'foaf:foo',
            EasyRdf_Literal::create('foobar', null, 'http://example.com/datatype/')
        );

        $turtle = $this->_serialiser->serialise($this->_graph, 'turtle');
        $this->assertSame(
            "@prefix foaf: <http://xmlns.com/foaf/0.1/> .\n\n".
            "<http://example.com/joe#me> foaf:foo \"foobar\"^^<http://example.com/datatype/> .\n",
            $turtle
        );
    }

    public function testSerialisePropertyWithUnknownNamespace()
    {
        $joe = $this->_graph->resource('http://www.example.com/joe#me');
        $joe->set('http://example.com/ns/prop', 'bar');

        $turtle = $this->_serialiser->serialise($this->_graph, 'turtle');
        $this->assertContains(
            "@prefix ns0: <http://example.com/ns/> .", $turtle
        );
        $this->assertSame(
            "@prefix ns0: <http://example.com/ns/> .\n\n".
            "<http://www.example.com/joe#me> ns0:prop \"bar\" .\n",
            $turtle
        );
    }

    public function testSerialiseUnshortenableProperty()
    {
        $joe = $this->_graph->resource('http://www.example.com/joe#me');
        $joe->set('http://example.com/property/', 'bar');

        $turtle = $this->_serialiser->serialise($this->_graph, 'turtle');
        $this->assertSame(
            "<http://www.example.com/joe#me> <http://example.com/property/> \"bar\" .\n",
            $turtle
        );
    }

    public function testSerialiseUnsupportedFormat()
    {
        $this->setExpectedException(
            'EasyRdf_Exception',
            'EasyRdf_Serialiser_Turtle does not support: unsupportedformat'
        );
        $rdf = $this->_serialiser->serialise(
            $this->_graph, 'unsupportedformat'
        );
    }
}
