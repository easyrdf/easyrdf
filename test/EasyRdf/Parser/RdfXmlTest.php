<?php

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009-2010 Nicholas J Humfrey.  All rights reserved.
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

require_once 'EasyRdf/Parser/RdfXml.php';

class EasyRdf_Parser_RdfXmlTest extends EasyRdf_TestCase
{
    protected $_parser = null;
    protected $_graph = null;
    protected $_data = null;

    public function setUp()
    {
        $this->_graph = new EasyRdf_Graph();
        $this->_parser = new EasyRdf_Parser_RdfXml();
        $this->_data = readFixture('foaf.rdf');
    }

    public function testParseRdfXml()
    {
        $count = $this->_parser->parse(
            $this->_graph,
            $this->_data,
            'rdfxml',
            'http://www.example.com/joe/foaf.rdf'
        );
        $this->assertSame(14, $count);

        $joe = $this->_graph->resource('http://www.example.com/joe#me');
        $this->assertNotNull($joe);
        $this->assertClass('EasyRdf_Resource', $joe);
        $this->assertSame('http://www.example.com/joe#me', $joe->getUri());

        $name = $joe->get('foaf:name');
        $this->assertNotNull($name);
        $this->assertClass('EasyRdf_Literal', $name);
        $this->assertStringEquals('Joe Bloggs', $name);
        $this->assertSame('en', $name->getLang());
        $this->assertSame(NULL, $name->getDatatype());

        $foaf = $this->_graph->resource('http://www.example.com/joe/foaf.rdf');
        $this->assertNotNull($foaf);
        $this->assertStringEquals("Joe Bloggs' FOAF File", $foaf->label());
    }

    public function testParseSeq()
    {
        $data = "<rdf:RDF xmlns:rdf='http://www.w3.org/1999/02/22-rdf-syntax-ns#'>\n";
        $data .= "  <rdf:Seq rdf:about='http://example.org/favourite-fruit'>\n";
        $data .= "    <rdf:li rdf:resource='http://example.org/banana'/>\n";
        $data .= "    <rdf:li rdf:resource='http://example.org/apple'/>\n";
        $data .= "    <rdf:li rdf:resource='http://example.org/pear'/>\n";
        $data .= "    <rdf:li rdf:resource='http://example.org/pear'/>\n";
        $data .= "  </rdf:Seq>\n";
        $data .= "</rdf:RDF>\n";

        $count = $this->_parser->parse(
            $this->_graph, $data, 'rdfxml',
            'http://www.w3.org/TR/REC-rdf-syntax/'
        );
        $this->assertSame(5, $count);

        $favourites = $this->_graph->resource('http://example.org/favourite-fruit');
        $this->assertSame('rdf:Seq', $favourites->type());
        $this->assertStringEquals('http://example.org/banana', $favourites->get('rdf:_1'));
        $this->assertStringEquals('http://example.org/apple', $favourites->get('rdf:_2'));
        $this->assertStringEquals('http://example.org/pear', $favourites->get('rdf:_3'));
        $this->assertStringEquals('http://example.org/pear', $favourites->get('rdf:_4'));
    }

    public function testParseUnsupportedFormat()
    {
        $this->setExpectedException(
            'EasyRdf_Exception',
            'EasyRdf_Parser_RdfXml does not support: unsupportedformat'
        );
        $this->_parser->parse(
            $this->_graph, $this->_data, 'unsupportedformat', null
        );
    }
}
