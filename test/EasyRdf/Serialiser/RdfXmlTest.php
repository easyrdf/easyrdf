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
 * @copyright  Copyright (c) 2009-2010 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 * @version    $Id$
 */

require_once dirname(dirname(dirname(__FILE__))).
             DIRECTORY_SEPARATOR.'TestHelper.php';

class EasyRdf_Serialiser_RdfXmlTest extends EasyRdf_TestCase
{
    protected $_serialiser = null;
    protected $_graph = null;

    public function setUp()
    {
        $this->_graph = new EasyRdf_Graph();
        $this->_serialiser = new EasyRdf_Serialiser_RdfXml();
    }

    public function tearDown()
    {
        EasyRdf_Namespace::reset();
    }

    public function testSerialiseRdfXml()
    {
        $joe = $this->_graph->resource('http://www.example.com/joe#me', 'foaf:Person');
        $joe->set('foaf:name', 'Joe Bloggs');
        $joe->addResource('foaf:homepage', 'http://www.example.com/joe/');

        $this->assertSame(
            "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n".
            "<rdf:RDF xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\"\n".
            "         xmlns:foaf=\"http://xmlns.com/foaf/0.1/\">\n\n".
            "  <foaf:Person rdf:about=\"http://www.example.com/joe#me\">\n".
            "    <foaf:name>Joe Bloggs</foaf:name>\n".
            "    <foaf:homepage rdf:resource=\"http://www.example.com/joe/\"/>\n".
            "  </foaf:Person>\n\n".
            "</rdf:RDF>\n",
            $this->_serialiser->serialise($this->_graph, 'rdfxml')
        );
    }

    public function testSerialiseRdfXmlWithInline()
    {
        $joe = $this->_graph->resource('http://www.example.com/joe#me', 'foaf:Person');
        $joe->set('foaf:name', 'Joe Bloggs');
        $homepage = $this->_graph->resource('http://www.example.com/joe/');
        $homepage->add('foaf:name', "Joe's Homepage");
        $joe->set('foaf:homepage', $homepage);

        $this->assertSame(
            "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n".
            "<rdf:RDF xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\"\n".
            "         xmlns:foaf=\"http://xmlns.com/foaf/0.1/\">\n\n".
            "  <foaf:Person rdf:about=\"http://www.example.com/joe#me\">\n".
            "    <foaf:name>Joe Bloggs</foaf:name>\n".
            "    <foaf:homepage>\n".
            "      <rdf:Description rdf:about=\"http://www.example.com/joe/\">\n".
            "        <foaf:name>Joe's Homepage</foaf:name>\n".
            "      </rdf:Description>\n".
            "    </foaf:homepage>\n\n".
            "  </foaf:Person>\n\n".
            "</rdf:RDF>\n",
            $this->_serialiser->serialise($this->_graph, 'rdfxml')
        );
    }

    public function testSerialiseRdfXmlDoubleRefernce()
    {
        $joe = $this->_graph->resource('http://www.example.com/joe#me', 'foaf:Person');
        $joe->set('foaf:name', 'Joe Bloggs');
        $homepage = $this->_graph->resource('http://www.example.com/joe/');
        $homepage->add('foaf:name', "Joe's Homepage");
        $joe->set('foaf:homepage', $homepage);
        $joe->set('foaf:made', $homepage);

        $this->assertSame(
            "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n".
            "<rdf:RDF xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\"\n".
            "         xmlns:foaf=\"http://xmlns.com/foaf/0.1/\">\n\n".
            "  <foaf:Person rdf:about=\"http://www.example.com/joe#me\">\n".
            "    <foaf:name>Joe Bloggs</foaf:name>\n".
            "    <foaf:homepage rdf:resource=\"http://www.example.com/joe/\"/>\n".
            "    <foaf:made rdf:resource=\"http://www.example.com/joe/\"/>\n".
            "  </foaf:Person>\n\n".
            "  <rdf:Description rdf:about=\"http://www.example.com/joe/\">\n".
            "    <foaf:name>Joe's Homepage</foaf:name>\n".
            "  </rdf:Description>\n\n".
            "</rdf:RDF>\n",
            $this->_serialiser->serialise($this->_graph, 'rdfxml')
        );
    }

    public function testSerialiseRdfXmlWithInlineBnode()
    {
        $joe = $this->_graph->resource('http://www.example.com/joe#me', 'foaf:Person');
        $joe->set('foaf:name', 'Joe Bloggs');
        $project = $this->_graph->newBNode('foaf:Project');
        $project->set('foaf:name', "Joe's Project");
        $joe->set('foaf:currentProject', $project);

        $this->assertSame(
            "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n".
            "<rdf:RDF xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\"\n".
            "         xmlns:foaf=\"http://xmlns.com/foaf/0.1/\">\n\n".
            "  <foaf:Person rdf:about=\"http://www.example.com/joe#me\">\n".
            "    <foaf:name>Joe Bloggs</foaf:name>\n".
            "    <foaf:currentProject>\n".
            "      <foaf:Project>\n".
            "        <foaf:name>Joe's Project</foaf:name>\n".
            "      </foaf:Project>\n".
            "    </foaf:currentProject>\n\n".
            "  </foaf:Person>\n\n".
            "</rdf:RDF>\n",
            $this->_serialiser->serialise($this->_graph, 'rdfxml')
        );
    }

    public function testSerialiseRdfXmlBnodeMentionedTwice()
    {
        $bob = $this->_graph->newBnode('foaf:Person');
        $alice = $this->_graph->newBnode('foaf:Person');
        $carol = $this->_graph->newBnode('foaf:Person');

        $bob->add('foaf:knows', $alice);
        $bob->add('foaf:knows', $carol);
        $alice->add('foaf:knows', $bob);
        $alice->add('foaf:knows', $carol);

        $this->assertSame(
            "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n".
            "<rdf:RDF xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\"\n".
            "         xmlns:foaf=\"http://xmlns.com/foaf/0.1/\">\n\n".
            "  <foaf:Person rdf:nodeID=\"genid1\">\n".
            "    <foaf:knows>\n".
            "      <foaf:Person>\n".
            "        <foaf:knows rdf:nodeID=\"genid1\"/>\n".
            "        <foaf:knows rdf:nodeID=\"genid3\"/>\n".
            "      </foaf:Person>\n".
            "    </foaf:knows>\n\n".
            "    <foaf:knows rdf:nodeID=\"genid3\"/>\n".
            "  </foaf:Person>\n\n".
            "  <foaf:Person rdf:nodeID=\"genid3\">\n".
            "  </foaf:Person>\n\n".
            "</rdf:RDF>\n",
            $this->_serialiser->serialise($this->_graph, 'rdfxml')
        );
    }

    public function testSerialiseRdfXmlTwoTypes()
    {
        $joe = $this->_graph->resource(
            'http://www.example.com/joe#me',
            array('foaf:Person', 'foaf:Mammal')
        );
        $joe->set('foaf:name', 'Joe Bloggs');

        $this->assertSame(
            "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n".
            "<rdf:RDF xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\"\n".
            "         xmlns:foaf=\"http://xmlns.com/foaf/0.1/\">\n\n".
            "  <foaf:Person rdf:about=\"http://www.example.com/joe#me\">\n".
            "    <rdf:type rdf:resource=\"http://xmlns.com/foaf/0.1/Mammal\"/>\n".
            "    <foaf:name>Joe Bloggs</foaf:name>\n".
            "  </foaf:Person>\n\n".
            "</rdf:RDF>\n",
            $this->_serialiser->serialise($this->_graph, 'rdfxml')
        );
    }

    public function testSerialiseRdfXmlWithTwoBNodes()
    {
        $nodeA = $this->_graph->newBNode();
        $nodeB = $this->_graph->newBNode();
        $this->_graph->add($nodeA, 'rdf:foobar', $nodeB);

        $this->assertSame(
            "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n".
            "<rdf:RDF xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\">\n\n".
            "  <rdf:Description rdf:nodeID=\"genid1\">\n".
            "    <rdf:foobar rdf:nodeID=\"genid2\"/>\n".
            "  </rdf:Description>\n\n".
            "</rdf:RDF>\n",
            $this->_serialiser->serialise($this->_graph, 'rdfxml')
        );
    }

    public function testSerialiseRdfXmlWithLang()
    {
        $this->_graph->add(
            'http://www.example.com/joe#me',
            'foaf:name',
            new EasyRdf_Literal('Joe', 'en')
        );

        $xml = $this->_serialiser->serialise($this->_graph, 'rdfxml');
        $this->assertContains(
            '<foaf:name xml:lang="en">Joe</foaf:name>', $xml
        );
    }

    public function testSerialiseRdfXmlWithDatatype()
    {
        $this->_graph->add(
            'http://www.example.com/joe#me',
            'foaf:age',
            EasyRdf_Literal::create(59, null, 'xsd:int')
        );

        $xml = $this->_serialiser->serialise($this->_graph, 'rdfxml');
        $this->assertContains(
            "<foaf:age rdf:datatype=\"http://www.w3.org/2001/XMLSchema#int\">59</foaf:age>", $xml
        );

    }

    public function testSerialiseRdfXmlWithUnknownProperty()
    {
        $this->_graph->add(
            'http://www.example.com/joe#me',
            'http://www.example.com/ns/foo',
            'bar'
        );

        $xml = $this->_serialiser->serialise($this->_graph, 'rdfxml');
        $this->assertContains("<ns0:foo>bar</ns0:foo>", $xml);
        $this->assertContains("xmlns:ns0=\"http://www.example.com/ns/\"", $xml);
    }

    public function testSerialiseRdfXmlWithUnshortenableProperty()
    {
        $this->_graph->add(
            'http://www.example.com/joe#me',
            'http://www.example.com/foo/',
            'bar'
        );

        $this->setExpectedException(
            'EasyRdf_Exception',
            'foo'
        );
        $this->_serialiser->serialise($this->_graph, 'rdfxml');
    }

    public function testSerialiseRdfXmlWithXMLLiteral()
    {
        $this->_graph->add(
            'http://www.example.com/joe#me',
            'foaf:bio',
            EasyRdf_Literal::create("<b>html</b>", null, 'rdf:XMLLiteral')
        );

        $xml = $this->_serialiser->serialise($this->_graph, 'rdfxml');
        $this->assertContains(
            "<foaf:bio rdf:parseType=\"Literal\"><b>html</b></foaf:bio>", $xml
        );
    }

    public function testSerialiseUnsupportedFormat()
    {
        $this->setExpectedException(
            'EasyRdf_Exception',
            'EasyRdf_Serialiser_RdfXml does not support: unsupportedformat'
        );
        $rdf = $this->_serialiser->serialise($this->_graph, 'unsupportedformat');
    }

    /**
     * testSerialiseRdfTypeAddsPrefix
     *
     * A test to assert that serialising a resource with a certain rdf:type
     * adds the correct namespace prefix, even if there are no properties tied
     * to that particular namespace.
     */
    public function testSerialiseRdfTypeAddsPrefix()
    {
        $joe = $this->_graph->resource('http://www.example.com/joe#me', 'foaf:Person');
        $joe->set('dc:creator', 'Max Bloggs');

        $this->assertSame(
            "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n".
            "<rdf:RDF xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\"\n".
            "         xmlns:foaf=\"http://xmlns.com/foaf/0.1/\"\n".
            "         xmlns:dc=\"http://purl.org/dc/terms/\">\n\n" .
            "  <foaf:Person rdf:about=\"http://www.example.com/joe#me\">\n".
            "    <dc:creator>Max Bloggs</dc:creator>\n".
            "  </foaf:Person>\n\n".
            "</rdf:RDF>\n",
            $this->_serialiser->serialise($this->_graph, 'rdfxml')
        );
    }

    /**
     * testSerialiseReferenceAlreadyOutput
     *
     * Test referencing a resource with a single property that
     * has already been output.
     */
    public function testSerialiseReferenceAlreadyOutput()
    {
        $graph = new EasyRdf_Graph();

        $graph->addLiteral('http://example.com/2', 'rdf:label', 'label');
        $graph->addResource('http://example.com/1', 'foaf:homepage', 'http://example.com/2');

        $this->assertSame(
            "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n".
            "<rdf:RDF xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\"\n".
            "         xmlns:foaf=\"http://xmlns.com/foaf/0.1/\">\n\n" .
            "  <rdf:Description rdf:about=\"http://example.com/2\">\n".
            "    <rdf:label>label</rdf:label>\n".
            "  </rdf:Description>\n\n".
            "  <rdf:Description rdf:about=\"http://example.com/1\">\n".
            "    <foaf:homepage rdf:resource=\"http://example.com/2\"/>\n".
            "  </rdf:Description>\n\n".
            "</rdf:RDF>\n",
            $this->_serialiser->serialise($graph, 'rdfxml')
        );
    }
}
