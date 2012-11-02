<?php

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2012 Nicholas J Humfrey.  All rights reserved.
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
 * @copyright  Copyright (c) 2012 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 * @version    $Id$
 */

require_once dirname(dirname(dirname(__FILE__))).
             DIRECTORY_SEPARATOR.'TestHelper.php';

require_once 'EasyRdf/Parser/Rdfa.php';
require_once 'EasyRdf/Serialiser/NtriplesArray.php';

class EasyRdf_Parser_RdfaTest extends EasyRdf_TestCase
{
    protected $_parser = null;
    protected $_graph = null;
    protected $_data = null;

    public function setUp()
    {
        $this->_rdfaParser = new EasyRdf_Parser_Rdfa();
        $this->_ntriplesParser = new EasyRdf_Parser_Ntriples();
        $this->_baseUri = 'http://rdfa.info/test-suite/test-cases/xhtml1/rdfa1.0/';
    }


    protected function parseRdfa($filename)
    {
        $graph = new EasyRdf_Graph();
        $this->_rdfaParser->parse(
            $graph,
            readFixture($filename),
            'rdfa',
            $this->_baseUri . basename($filename)
        );
        return $graph->serialise('ntriples-array');
    }

    protected function parseNtriples($filename)
    {
        $graph = new EasyRdf_Graph();
        $this->_ntriplesParser->parse(
            $graph,
            readFixture($filename),
            'ntriples',
            $this->_baseUri . basename($filename)
        );
        return $graph->serialise('ntriples-array');
    }

    protected function rdfaTestCase($name, $message)
    {
        $this->assertEquals(
            $this->parseNtriples("rdfa/$name.out"),
            $this->parseRdfa("rdfa/$name.xml"),
            $message
        );
    }

    public function testCase0001()
    {
        $this->rdfaTestCase('0001', 'Predicate establishment with @property');
    }

    public function testCase0006()
    {
        $this->rdfaTestCase('0006', '@rel and @rev');
    }

    public function testCase0007()
    {
        $this->rdfaTestCase('0007', '@rel, @rev, @property, @content');
    }

    public function testCase0008()
    {
        $this->rdfaTestCase('0008', 'empty string @about');
    }

    public function testCase0009()
    {
        $this->rdfaTestCase('0009', '@rev');
    }

    public function testCase0010()
    {
        $this->rdfaTestCase('0010', '@rel, @rev, @href');
    }

    public function testCase0014()
    {
        $this->rdfaTestCase('0014', '@datatype, xsd:integer');
    }

    public function testCase0015()
    {
        $this->rdfaTestCase('0015', 'meta and link');
    }

    public function testCase0017()
    {
        $this->rdfaTestCase('0017', 'Related blanknodes');
    }

    public function testCase0018()
    {
        $this->rdfaTestCase('0018', '@rel for predicate');
    }

    public function testCase0019()
    {
        $this->rdfaTestCase('0019', '@about for subject');
    }

    public function testCase0020()
    {
        $this->rdfaTestCase('0020', 'Inheriting @about for subject');
    }

    public function testCase0021()
    {
        $this->rdfaTestCase('0021', 'Subject inheritance with no @about');
    }

    public function testCase0023()
    {
        $this->rdfaTestCase('0023', '@id does not generate subjects');
    }

    public function testCase0025()
    {
        $this->rdfaTestCase('0025', 'simple chaining test');
    }

    public function testCase0026()
    {
        $this->rdfaTestCase('0026', '@content');
    }

    public function testCase0027()
    {
        $this->rdfaTestCase('0027', '@content, ignore element content');
    }

    public function testCase0029()
    {
        $this->rdfaTestCase('0029', 'markup stripping with @datatype');
    }

    public function testCase0030()
    {
        $this->rdfaTestCase('0030', 'omitted @about');
    }

    public function testCase0031()
    {
        $this->rdfaTestCase('0031', 'simple @resource');
    }

    public function testCase0032()
    {
        $this->rdfaTestCase('0032', '@resource overrides @href');
    }

    public function testCase0033()
    {
        $this->rdfaTestCase('0033', 'simple chaining test with bNode');
    }

    public function testCase0034()
    {
        $this->rdfaTestCase('0034', 'simple img[@src] test');
    }

    public function testCase0035()
    {
        $this->rdfaTestCase('0035', '@src/@href test');
    }

    public function testCase0036()
    {
        $this->rdfaTestCase('0036', '@src/@resource test');
    }

    public function testCase0037()
    {
        $this->rdfaTestCase('0037', '@src/@href/@resource test');
    }

    public function testCase0038()
    {
        $this->rdfaTestCase('0038', '@rev - img[@src] test');
    }

    public function testCase0039()
    {
        $this->rdfaTestCase('0039', '@rev - @src/@href test');
    }

    public function testCase0041()
    {
        $this->rdfaTestCase('0041', '@rev - @src/@href/@resource test');
    }

    public function testCase0048()
    {
        $this->rdfaTestCase('0048', '@typeof with @about and @rel present, no @resource');
    }

    public function testCase0049()
    {
        $this->rdfaTestCase('0049', '@typeof with @about, no @rel or @resource');
    }

    public function testCase0050()
    {
        $this->rdfaTestCase('0050', '@typeof without anything else');
    }

    public function testCase0051()
    {
        $this->rdfaTestCase('0051', '@typeof with a single @property');
    }

    public function testCase0052()
    {
        $this->rdfaTestCase('0052', '@typeof with @resource and nothing else');
    }

    public function testCase0053()
    {
        $this->rdfaTestCase('0053', '@typeof with @resource and nothing else, with a subelement');
    }

    public function testCase0054()
    {
        $this->rdfaTestCase('0054', 'multiple @property');
    }

    public function testCase0055()
    {
        $this->rdfaTestCase('0055', 'multiple @rel');
    }

    public function testCase0056()
    {
        $this->rdfaTestCase('0056', '@typeof applies to @about on same element with hanging rel');
    }

    public function testCase0057()
    {
        $this->rdfaTestCase('0057', 'hanging @rel creates multiple triples');
    }

    public function testParseUnsupportedFormat()
    {
        $this->setExpectedException(
            'EasyRdf_Exception',
            'EasyRdf_Parser_Rdfa does not support: unsupportedformat'
        );
        $graph = new EasyRdf_Graph();
        $this->_rdfaParser->parse(
            $graph, 'data', 'unsupportedformat', null
        );
    }
}
