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

class EasyRdf_Serialiser_RdfXmlTest extends EasyRdf_TestCase
{
    protected $serialiser = null;
    protected $graph = null;

    public static function setUpBeforeClass()
    {
        EasyRdf_Namespace::resetNamespaces();
        EasyRdf_Namespace::reset();
    }

    public function setUp()
    {
        $this->graph = new EasyRdf_Graph();
        $this->serialiser = new EasyRdf_Serialiser_RdfXml();
    }

    public function tearDown()
    {
        EasyRdf_Namespace::resetNamespaces();
        EasyRdf_Namespace::reset();
    }

    public function testSerialiseRdfXml()
    {
        $joe = $this->graph->resource('http://www.example.com/joe#me', 'foaf:Person');
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
            $this->serialiser->serialise($this->graph, 'rdfxml')
        );
    }

    public function testSerialiseRdfXmlWithInline()
    {
        $joe = $this->graph->resource('http://www.example.com/joe#me', 'foaf:Person');
        $joe->set('foaf:name', 'Joe Bloggs');
        $homepage = $this->graph->resource('http://www.example.com/joe/');
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
            $this->serialiser->serialise($this->graph, 'rdfxml')
        );
    }

    public function testSerialiseRdfXmlDoubleRefernce()
    {
        $joe = $this->graph->resource('http://www.example.com/joe#me', 'foaf:Person');
        $joe->set('foaf:name', 'Joe Bloggs');
        $homepage = $this->graph->resource('http://www.example.com/joe/');
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
            $this->serialiser->serialise($this->graph, 'rdfxml')
        );
    }

    public function testSerialiseRdfXmlWithInlineBnode()
    {
        $joe = $this->graph->resource('http://www.example.com/joe#me', 'foaf:Person');
        $joe->set('foaf:name', 'Joe Bloggs');
        $project = $this->graph->newBNode('foaf:Project');
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
            $this->serialiser->serialise($this->graph, 'rdfxml')
        );
    }

    public function testSerialiseRdfXmlBnodeMentionedTwice()
    {
        $bob = $this->graph->newBnode('foaf:Person');
        $alice = $this->graph->newBnode('foaf:Person');
        $carol = $this->graph->newBnode('foaf:Person');

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
            $this->serialiser->serialise($this->graph, 'rdfxml')
        );
    }

    public function testSerialiseRdfXmlTwoTypes()
    {
        $joe = $this->graph->resource(
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
            $this->serialiser->serialise($this->graph, 'rdfxml')
        );
    }

    public function testSerialiseRdfXmlWithTwoBNodes()
    {
        $nodeA = $this->graph->newBNode();
        $nodeB = $this->graph->newBNode();
        $this->graph->add($nodeA, 'rdf:foobar', $nodeB);

        $this->assertSame(
            "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n".
            "<rdf:RDF xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\">\n\n".
            "  <rdf:Description rdf:nodeID=\"genid1\">\n".
            "    <rdf:foobar rdf:nodeID=\"genid2\"/>\n".
            "  </rdf:Description>\n\n".
            "</rdf:RDF>\n",
            $this->serialiser->serialise($this->graph, 'rdfxml')
        );
    }

    public function testSerialiseBNodesLast()
    {
        $bnode = $this->graph->newBNode();
        $bnode->add('rdf:label', 'This is a bnode');
        $res1 = $this->graph->resource('http://example.com/1');
        $res1->add('rdf:test', $bnode);
        $res2 = $this->graph->resource('http://example.com/2');
        $res2->add('rdf:test', $bnode);

        $this->assertSame(
            "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n".
            "<rdf:RDF xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\">\n\n".
            "  <rdf:Description rdf:about=\"http://example.com/1\">\n".
            "    <rdf:test rdf:nodeID=\"genid1\"/>\n".
            "  </rdf:Description>\n\n".
            "  <rdf:Description rdf:about=\"http://example.com/2\">\n".
            "    <rdf:test rdf:nodeID=\"genid1\"/>\n".
            "  </rdf:Description>\n\n".
            "  <rdf:Description rdf:nodeID=\"genid1\">\n".
            "    <rdf:label>This is a bnode</rdf:label>\n".
            "  </rdf:Description>\n\n".
            "</rdf:RDF>\n",
            $this->serialiser->serialise($this->graph, 'rdfxml')
        );
    }

    public function testSerialiseRdfXmlWithLang()
    {
        $this->graph->add(
            'http://www.example.com/joe#me',
            'foaf:name',
            new EasyRdf_Literal('Joe', 'en')
        );

        $xml = $this->serialiser->serialise($this->graph, 'rdfxml');
        $this->assertContains(
            '<foaf:name xml:lang="en">Joe</foaf:name>',
            $xml
        );
    }

    public function testSerialiseRdfXmlWithDatatype()
    {
        $this->graph->add(
            'http://www.example.com/joe#me',
            'foaf:age',
            EasyRdf_Literal::create(59, null, 'xsd:int')
        );

        $xml = $this->serialiser->serialise($this->graph, 'rdfxml');
        $this->assertContains(
            "<foaf:age rdf:datatype=\"http://www.w3.org/2001/XMLSchema#int\">59</foaf:age>",
            $xml
        );

    }

    public function testSerialiseRdfXmlWithUnknownProperty()
    {
        $this->graph->add(
            'http://www.example.com/joe#me',
            'http://www.example.com/ns/foo',
            'bar'
        );

        $xml = $this->serialiser->serialise($this->graph, 'rdfxml');
        $this->assertContains("<ns0:foo>bar</ns0:foo>", $xml);
        $this->assertContains("xmlns:ns0=\"http://www.example.com/ns/\"", $xml);
    }

    public function testSerialiseRdfXmlWithUnshortenableProperty()
    {
        $this->graph->add(
            'http://www.example.com/joe#me',
            'http://www.example.com/foo/',
            'bar'
        );

        $this->setExpectedException(
            'EasyRdf_Exception',
            'foo'
        );
        $this->serialiser->serialise($this->graph, 'rdfxml');
    }

    public function testSerialiseRdfXmlWithXMLLiteral()
    {
        $this->graph->add(
            'http://www.example.com/joe#me',
            'foaf:bio',
            EasyRdf_Literal::create("<b>html</b>", null, 'rdf:XMLLiteral')
        );

        $xml = $this->serialiser->serialise($this->graph, 'rdfxml');
        $this->assertContains(
            "<foaf:bio rdf:parseType=\"Literal\"><b>html</b></foaf:bio>",
            $xml
        );
    }

    public function testSerialiseUnsupportedFormat()
    {
        $this->setExpectedException(
            'EasyRdf_Exception',
            'EasyRdf_Serialiser_RdfXml does not support: unsupportedformat'
        );
        $rdf = $this->serialiser->serialise($this->graph, 'unsupportedformat');
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
        $joe = $this->graph->resource('http://www.example.com/joe#me', 'foaf:Person');
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
            $this->serialiser->serialise($this->graph, 'rdfxml')
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
        $this->graph->addLiteral('http://example.com/2', 'rdf:label', 'label');
        $this->graph->addResource('http://example.com/1', 'foaf:homepage', 'http://example.com/2');

        $this->assertSame(
            "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n".
            "<rdf:RDF xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\"\n".
            "         xmlns:foaf=\"http://xmlns.com/foaf/0.1/\">\n\n".
            "  <rdf:Description rdf:about=\"http://example.com/2\">\n".
            "    <rdf:label>label</rdf:label>\n".
            "  </rdf:Description>\n\n".
            "  <rdf:Description rdf:about=\"http://example.com/1\">\n".
            "    <foaf:homepage rdf:resource=\"http://example.com/2\"/>\n".
            "  </rdf:Description>\n\n".
            "</rdf:RDF>\n",
            $this->serialiser->serialise($this->graph, 'rdfxml')
        );
    }

    public function testSerialiseContainer()
    {
        $joe =  $this->graph->resource('http://example.com/joe', 'foaf:Person');
        $pets =  $this->graph->newBnode('rdf:Seq');
        $pets->append('Rat');
        $pets->append('Cat');
        $pets->append('Goat');
        $joe->add('foaf:pets', $pets);

        $this->assertSame(
            "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n".
            "<rdf:RDF xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\"\n".
            "         xmlns:foaf=\"http://xmlns.com/foaf/0.1/\">\n\n".
            "  <foaf:Person rdf:about=\"http://example.com/joe\">\n".
            "    <foaf:pets>\n".
            "      <rdf:Seq>\n".
            "        <rdf:li>Rat</rdf:li>\n".
            "        <rdf:li>Cat</rdf:li>\n".
            "        <rdf:li>Goat</rdf:li>\n".
            "      </rdf:Seq>\n".
            "    </foaf:pets>\n\n".
            "  </foaf:Person>\n\n".
            "</rdf:RDF>\n",
            $this->serialiser->serialise($this->graph, 'rdfxml')
        );
    }
    
    public function testSerialiseTriplesWithoutType()
    {
        $this->graph->add('http://example.com/joe', 'foaf:knows', 'http://example.com/bob');
        $this->graph->addLiteral('http://example.com/joe', 'rdf:label', 'le Joe', 'fr-FR');
        
        $this->assertSame(
            "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n".
            "<rdf:RDF xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\"\n".
            "         xmlns:foaf=\"http://xmlns.com/foaf/0.1/\">\n\n".
            "  <rdf:Description rdf:about=\"http://example.com/joe\">\n".
            "    <foaf:knows>http://example.com/bob</foaf:knows>\n".
            "    <rdf:label xml:lang=\"fr-FR\">le Joe</rdf:label>\n".
            "  </rdf:Description>\n\n".
            "</rdf:RDF>\n",
            $this->serialiser->serialise($this->graph, 'rdfxml')
        );
    }
    
    public function testSerialiseTriplesWithType()
    {
        $this->graph->add('http://example.com/joe', 'rdf:type', 'foaf:Person');
        $this->graph->add('http://example.com/joe', 'foaf:knows', 'http://example.com/bob');
        $this->graph->addLiteral('http://example.com/joe', 'rdf:label', 'le Joe', 'fr-FR');
        
        $this->assertSame(
            "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n".
            "<rdf:RDF xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\"\n".
            "         xmlns:foaf=\"http://xmlns.com/foaf/0.1/\">\n\n".
            "  <rdf:Description rdf:about=\"http://example.com/joe\">\n".
            "    <rdf:type>foaf:Person</rdf:type>\n".
            "    <foaf:knows>http://example.com/bob</foaf:knows>\n".
            "    <rdf:label xml:lang=\"fr-FR\">le Joe</rdf:label>\n".
            "  </rdf:Description>\n\n".
            "</rdf:RDF>\n",
            $this->serialiser->serialise($this->graph, 'rdfxml')
        );
    }

    public function testSerialiseEmptyPrefix()
    {
        \EasyRdf_Namespace::set('', 'http://foo/bar/');

        $joe = $this->graph->resource(
            'http://foo/bar/me',
            'foaf:Person'
        );

        $joe->set('foaf:name', 'Joe Bloggs');
        $joe->set(
            'foaf:homepage',
            $this->graph->resource('http://example.com/joe/')
        );
        $joe->set('http://foo/bar/test', 'test');

        $rdf = $this->serialiser->serialise($this->graph, 'rdfxml');

        $this->assertSame(
            "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n".
            "<rdf:RDF xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\"\n".
            "         xmlns:foaf=\"http://xmlns.com/foaf/0.1/\"\n".
            "         xmlns=\"http://foo/bar/\">\n\n".
            "  <foaf:Person rdf:about=\"http://foo/bar/me\">\n".
            "    <foaf:name>Joe Bloggs</foaf:name>\n".
            "    <foaf:homepage rdf:resource=\"http://example.com/joe/\"/>\n".
            "    <test>test</test>\n".
            "  </foaf:Person>\n\n".
            "</rdf:RDF>\n",
            $rdf
        );
    }

    /**
     * @see https://github.com/njh/easyrdf/issues/209
     */
    public function testIssue209()
    {
        $g = new EasyRdf_Graph();
        $g->add('http://example.com/resource', 'rdf:type', new EasyRdf_Resource('foaf:Person'));
        $g->add('http://example.com/resource', 'rdf:type', new EasyRdf_Resource('http://example.com/TypeA'));
        $xml = $g->serialise('rdfxml');

        $g2 = new EasyRdf_Graph('http://example.com/', $xml, 'rdfxml');
        $types = $g2->resource('http://example.com/resource')->typesAsResources();

        $expected = array('http://example.com/TypeA', 'http://xmlns.com/foaf/0.1/Person');

        $this->assertCount(2, $types);
        $this->assertContains($types[0]->getUri(), $expected);
        $this->assertContains($types[1]->getUri(), $expected);
    }
}
