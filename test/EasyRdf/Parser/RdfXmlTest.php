<?php
namespace EasyRdf\Parser;

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

use EasyRdf\Graph;
use EasyRdf\TestCase;

require_once dirname(dirname(__DIR__)).
             DIRECTORY_SEPARATOR.'TestHelper.php';

class RdfXmlTest extends TestCase
{
    /** @var RdfXml */
    protected $parser = null;
    /** @var Graph */
    protected $graph = null;
    protected $rdf_data = null;

    public function setUp()
    {
        $this->graph = new Graph();
        $this->parser = new RdfXml();
        $this->rdf_data = readFixture('foaf.rdf');
    }

    public function testParseRdfXml()
    {
        $count = $this->parser->parse(
            $this->graph,
            $this->rdf_data,
            'rdfxml',
            'http://www.example.com/joe/foaf.rdf'
        );
        $this->assertSame(14, $count);

        $joe = $this->graph->resource('http://www.example.com/joe#me');
        $this->assertNotNull($joe);
        $this->assertClass('EasyRdf\Resource', $joe);
        $this->assertSame('http://www.example.com/joe#me', $joe->getUri());

        $name = $joe->get('foaf:name');
        $this->assertNotNull($name);
        $this->assertClass('EasyRdf\Literal', $name);
        $this->assertStringEquals('Joe Bloggs', $name);
        $this->assertSame('en', $name->getLang());
        $this->assertSame(null, $name->getDatatype());

        $foaf = $this->graph->resource('http://www.example.com/joe/foaf.rdf');
        $this->assertNotNull($foaf);
        $this->assertStringEquals("Joe Bloggs' FOAF File", $foaf->label());
    }

    public function testParseSeq()
    {
        $count = $this->parser->parse(
            $this->graph,
            readFixture('rdf-seq.rdf'),
            'rdfxml',
            'http://www.w3.org/TR/REC-rdf-syntax/'
        );
        $this->assertSame(5, $count);

        $favourites = $this->graph->resource('http://example.org/favourite-fruit');
        $this->assertSame('rdf:Seq', $favourites->type());
        $this->assertStringEquals('http://example.org/banana', $favourites->get('rdf:_1'));
        $this->assertStringEquals('http://example.org/apple', $favourites->get('rdf:_2'));
        $this->assertStringEquals('http://example.org/pear', $favourites->get('rdf:_3'));
        $this->assertStringEquals('http://example.org/pear', $favourites->get('rdf:_4'));
    }

    public function testXMLParseError()
    {
        $this->setExpectedException(
            'EasyRdf\Parser\Exception',
            'XML error: "Mismatched tag" on line 4, column 21'
        );
        $this->parser->parse(
            $this->graph,
            "<rdf:RDF xmlns:rdf='http://www.w3.org/1999/02/22-rdf-syntax-ns#'>\n".
            "  <rdf:Description rdf:about='http://example.org/foo'>\n".
            "    <rdf:foo>Hello World\n".
            "  </rdf:Description>\n".
            "</rdf:RDF>",
            'rdfxml',
            'http://example.org/'
        );
    }

    public function testParseUnsupportedFormat()
    {
        $this->setExpectedException(
            'EasyRdf\Exception',
            'EasyRdf\Parser\RdfXml does not support: unsupportedformat'
        );
        $this->parser->parse(
            $this->graph,
            $this->rdf_data,
            'unsupportedformat',
            null
        );
    }

    /**
     * Check that the RDF/XML parser is capable of parsing files more than 10MB
     *
     * @see https://github.com/easyrdf/easyrdf/issues/350
     */
    public function testLargeFile()
    {
        // Genrate a large RDF/XML file
        $large = "<rdf:RDF xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\"\n";
        $large .= "         xmlns:owl=\"http://www.w3.org/2002/07/owl#\"\n";
        $large .= "         xmlns:rdfs=\"http://www.w3.org/2000/01/rdf-schema#\">\n";
        for ($i = 1; $i < 25000; $i++) {
            $large .= "<owl:Thing rdf:about=\"http://www.example.com/resource$i\">\n";
            $large .= "  <rdfs:label>This is item $i</rdfs:label>\n";
            $large .= "  <rdfs:comment>";
            $large .= str_repeat('This is a long piece of text. ', 20);
            $large .= "  </rdfs:comment>\n";
            $large .= "</owl:Thing>\n";
        }
        $large .= "</rdf:RDF>\n";

        // Check it is more than 10Mb
        $this->assertGreaterThan(10485760, strlen($large));

        // Now parse it into a graph
        $count = $this->parser->parse(
            $this->graph,
            $large,
            'rdfxml',
            null
        );

        $this->assertEquals(25000, count($this->graph->resources()));
    }

    /**
     * @see https://github.com/easyrdf/easyrdf/issues/74
     */
    public function testIssue74()
    {
        $this->markTestIncomplete('fix for bug #74 is not implemented yet');
        $filename = 'rdfxml/gh74-bio.rdf';

        $graph = new Graph();
        $this->parser->parse(
            $graph,
            readFixture($filename),
            'rdfxml',
            'http://vocab.org/bio/0.1/'
        );

        foreach ($graph->resources() as $resource) {
            /** @var \EasyRdf\Resource $resource */
            if ($resource->isBnode() and $resource->hasProperty('rdfs:comment')) {
                $comment = trim($resource->getLiteral('rdfs:comment'));
                $this->assertStringStartsWith('<pre><code>', $comment);
            }
        }
    }

    /**
     * @see https://github.com/easyrdf/easyrdf/issues/157
     */
    public function testIssue157()
    {
        $filename = 'rdfxml/gh157-base.rdf';

        $graph = new Graph();
        $this->parser->parse(
            $graph,
            readFixture($filename),
            'rdfxml',
            null
        );

        foreach ($graph->toRdfPhp() as $iri => $properies) {
            $this->assertEquals('http://www.example.org/base#foo', $iri);
        }
    }
}
