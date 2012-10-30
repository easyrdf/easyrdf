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

    protected function rdfaTestCase($name)
    {
        $this->assertEquals(
            $this->parseNtriples("rdfa/$name.out"),
            $this->parseRdfa("rdfa/$name.xml")
        );
    }

    public function testCase0001()
    {
        $this->rdfaTestCase('0001');
    }

    public function testCase0006()
    {
        $this->rdfaTestCase('0006');
    }

    public function testCase0007()
    {
        $this->rdfaTestCase('0007');
    }

    public function testCase0008()
    {
        $this->rdfaTestCase('0008');
    }

    public function testCase0009()
    {
        $this->rdfaTestCase('0009');
    }

    public function testCase0010()
    {
        $this->rdfaTestCase('0010');
    }

    public function testCase0012()
    {
        $this->rdfaTestCase('0012');
    }

    public function testCase0013()
    {
        $this->rdfaTestCase('0013');
    }

    public function testCase0014()
    {
        $this->rdfaTestCase('0014');
    }

    public function testCase0015()
    {
        $this->rdfaTestCase('0015');
    }

    public function testCase0017()
    {
        $this->rdfaTestCase('0017');
    }

    public function testCase0018()
    {
        $this->rdfaTestCase('0018');
    }

    public function testCase0019()
    {
        $this->rdfaTestCase('0019');
    }

    public function testCase0020()
    {
        $this->rdfaTestCase('0020');
    }

    public function testCase0021()
    {
        $this->rdfaTestCase('0021');
    }

    public function testCase0023()
    {
        $this->rdfaTestCase('0023');
    }

    public function testCase0025()
    {
        $this->rdfaTestCase('0025');
    }

    public function testCase0026()
    {
        $this->rdfaTestCase('0026');
    }

    public function testCase0027()
    {
        $this->rdfaTestCase('0027');
    }

    public function testCase0029()
    {
        $this->rdfaTestCase('0029');
    }

    public function testCase0030()
    {
        $this->rdfaTestCase('0030');
    }

    public function testCase0031()
    {
        $this->rdfaTestCase('0031');
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
