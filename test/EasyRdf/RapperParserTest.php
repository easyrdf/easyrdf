<?php

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009 Nicholas J Humfrey.  All rights reserved.
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
 * @copyright  Copyright (c) 2009 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 * @version    $Id$
 */

require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'TestHelper.php';
require_once 'EasyRdf/RapperParser.php';

class EasyRdf_RapperParserTest extends PHPUnit_Framework_TestCase
{
    protected $_parser = null;

    public function setUp()
    {
        exec('which rapper', $output, $retval);
        if ($retval == 0) {
            $this->_parser = new EasyRdf_RapperParser();
        } else {
            $this->markTestSkipped(
                "The rapper command is not available on this system."
            );
        }
    }
    
    function testRapperNotFound()
    {
        $this->setExpectedException('EasyRdf_Exception');
        new EasyRdf_RapperParser('random_command_that_doesnt_exist');
    }

    public function testParseNullUri()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_parser->parse(null, '<rdf:RDF></rdf:RDF>', 'rdfxml');
    }
    
    public function testParseEmptyUri()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_parser->parse('', '<rdf:RDF></rdf:RDF>', 'rdfxml');
    }
    
    public function testParseNonStringUri()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_parser->parse(array(), '<rdf:RDF></rdf:RDF>', 'rdfxml');
    }

    public function testParseNullData()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_parser->parse('file://valid.rdf', null, 'rdfxml');
    }
    
    public function testParseEmptyData()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_parser->parse('file://valid.rdf', '', 'rdfxml');
    }
    
    public function testParseNonStringData()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_parser->parse('file://valid.rdf', array(), 'rdfxml');
    }

    public function testParseNullDocType()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_parser->parse('file://valid.rdf', '<rdf:RDF></rdf:RDF>', null);
    }
    
    public function testParseEmptyDocType()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_parser->parse('file://valid.rdf', '<rdf:RDF></rdf:RDF>', '');
    }
    
    public function testParseNonStringDocType()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_parser->parse(
            'file://valid.rdf', '<rdf:RDF></rdf:RDF>',
            array()
        );
    }
    
    function testParseRdfXml()
    {
        $data = readFixture('foaf.rdf');
        $rdf = $this->_parser->parse(
            'http://www.example.com/joe/foaf.rdf',
            $data, 'rdfxml'
        );

        $joe = $rdf['http://www.example.com/joe#me'];
        $name = $joe['http://xmlns.com/foaf/0.1/name'][0];
        $this->assertEquals('literal', $name['type']);
        $this->assertEquals('Joe Bloggs', $name['value']);

        $current_project = $joe['http://xmlns.com/foaf/0.1/currentProject'][0];
        $this->assertEquals('bnode', $current_project['type']);
        $this->assertEquals('_:genid1', $current_project['value']);
        
        $project = $rdf['_:genid1'];
        $project_name = $project['http://xmlns.com/foaf/0.1/name'][0];
        $this->assertEquals('literal', $project_name['type']);
        $this->assertEquals("Joe's Current Project", $project_name['value']);

        $homepage = $joe['http://xmlns.com/foaf/0.1/homepage'][0];
        $this->assertEquals('uri', $homepage['type']);
        $this->assertEquals('http://www.example.com/joe/', $homepage['value']);
    }
    
    function testParseInvalidRdfXml()
    {
        $this->setExpectedException('EasyRdf_Exception');
        $rdf = $this->_parser->parse(
            'http://www.example.com/joe/foaf.rdf',
            "<rdf></xml>", 'rdfxml'
        );
    }
    
    function testParseTurtle()
    {
        $data = readFixture('foaf.ttl');
        $rdf = $this->_parser->parse(
            'http://www.example.com/joe/foaf.rdf',
            $data, 'turtle'
        );

        $joe = $rdf['http://www.example.com/joe#me'];
        $name = $joe['http://xmlns.com/foaf/0.1/name'][0];
        $this->assertEquals('literal', $name['type']);
        $this->assertEquals('Joe Bloggs', $name['value']);

        $current_project = $joe['http://xmlns.com/foaf/0.1/currentProject'][0];
        $this->assertEquals('bnode', $current_project['type']);
        $this->assertEquals('_:genid1', $current_project['value']);
        
        $project = $rdf['_:genid1'];
        $project_name = $project['http://xmlns.com/foaf/0.1/name'][0];
        $this->assertEquals('literal', $project_name['type']);
        $this->assertEquals("Joe's Current Project", $project_name['value']);

        $homepage = $joe['http://xmlns.com/foaf/0.1/homepage'][0];
        $this->assertEquals('uri', $homepage['type']);
        $this->assertEquals('http://www.example.com/joe/', $homepage['value']);
    }
    
    function testRapperExecError()
    {
        # FIXME: how can we cause proc_open() to fail?
        $this->markTestIncomplete();
    }
}
