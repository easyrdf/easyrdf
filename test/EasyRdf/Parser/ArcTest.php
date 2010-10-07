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

require_once 'EasyRdf/Parser/Arc.php';

class EasyRdf_Parser_ArcTest extends EasyRdf_TestCase
{
    protected $_parser = null;
    protected $_graph = null;
    protected $_data = null;

    public function setUp()
    {
        if (requireExists('arc/ARC2.php')) {
            $this->_parser = new EasyRdf_Parser_Arc();
            $this->_graph = new EasyRdf_Graph();
            $this->_data = readFixture('foaf.rdf');
        } else {
            $this->markTestSkipped(
                "ARC2 library is not available."
            );
        }
    }

    public function testParseRdfXml()
    {
        $this->_parser->parse(
            $this->_graph,
            $this->_data,
            'rdfxml',
            'http://www.example.com/joe/foaf.rdf'
        );

        $joe = $this->_graph->resource('http://www.example.com/joe#me');
        $this->assertNotNull($joe);
        $this->assertEquals('EasyRdf_Resource', get_class($joe));
        $this->assertEquals('http://www.example.com/joe#me', $joe->getUri());

        $name = $joe->get('foaf:name');
        $this->assertNotNull($name);
        $this->assertEquals('EasyRdf_Literal', get_class($name));
        $this->assertStringEquals('Joe Bloggs', $name);
        $this->assertEquals('en', $name->getLang());
        $this->assertEquals(null, $name->getDatatype());
        
        $foaf = $this->_graph->resource('http://www.example.com/joe/foaf.rdf');
        $this->assertNotNull($foaf);
        $this->assertStringEquals("Joe Bloggs' FOAF File", $foaf->label());
    }

    function testParseUnsupportedFormat()
    {
        $this->setExpectedException('EasyRdf_Exception');
        $rdf = $this->_parser->parse(
            $this->_graph, $this->_data, 'unsupportedformat', null
        );
    }
}
