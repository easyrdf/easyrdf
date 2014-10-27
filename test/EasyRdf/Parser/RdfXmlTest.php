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

require_once 'EasyRdf/Parser/RdfXml.php';

class EasyRdf_Parser_RdfXmlTest extends EasyRdf_TestCase
{
    /** @var EasyRdf_Parser_RdfXml */
    protected $parser = null;
    protected $graph = null;
    protected $data = null;

    public function setUp()
    {
        $this->graph = new EasyRdf_Graph();
        $this->parser = new EasyRdf_Parser_RdfXml();
        $this->data = readFixture('foaf.rdf');
    }

    public function testParseRdfXml()
    {
        $count = $this->parser->parse(
            $this->graph,
            $this->data,
            'rdfxml',
            'http://www.example.com/joe/foaf.rdf'
        );
        $this->assertSame(14, $count);

        $joe = $this->graph->resource('http://www.example.com/joe#me');
        $this->assertNotNull($joe);
        $this->assertClass('EasyRdf_Resource', $joe);
        $this->assertSame('http://www.example.com/joe#me', $joe->getUri());

        $name = $joe->get('foaf:name');
        $this->assertNotNull($name);
        $this->assertClass('EasyRdf_Literal', $name);
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
            'EasyRdf_Parser_Exception',
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
            'EasyRdf_Exception',
            'EasyRdf_Parser_RdfXml does not support: unsupportedformat'
        );
        $this->parser->parse(
            $this->graph,
            $this->data,
            'unsupportedformat',
            null
        );
    }

    /**
     * @see https://github.com/njh/easyrdf/issues/74
     */
    public function testIssue74()
    {
        $this->markTestIncomplete('fix for bug #74 is not implemented yet');
        $filename = 'rdfxml/gh74-bio.rdf';

        $graph = new EasyRdf_Graph();
        $triple_count = $this->parser->parse(
            $graph,
            readFixture($filename),
            'rdfxml',
            'http://vocab.org/bio/0.1/'
        );

        foreach ($graph->resources() as $resource) {
            /** @var EasyRdf_Resource $resource */
            if ($resource->isBnode() and $resource->hasProperty('rdfs:comment')) {
                $comment = trim($resource->getLiteral('rdfs:comment'));
                $this->assertStringStartsWith('<pre><code>', $comment);
            }
        }
    }

    /**
     * @see https://github.com/njh/easyrdf/issues/157
     */
    public function testIssue157()
    {
        $filename = 'rdfxml/gh157-base.rdf';

        $graph = new EasyRdf_Graph();
        $triple_count = $this->parser->parse(
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
