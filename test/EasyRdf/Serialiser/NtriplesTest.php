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

require_once dirname(dirname(dirname(__FILE__))).
             DIRECTORY_SEPARATOR.'TestHelper.php';

class EasyRdf_Serialiser_NtriplesTest extends EasyRdf_TestCase
{
    protected $_serialiser = null;
    protected $_graph = null;

    public function setUp()
    {
        $this->_graph = new EasyRdf_Graph();
        $this->_serialiser = new EasyRdf_Serialiser_Ntriples();
    }

    public function testSerialiseNullGraph()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_serialiser->serialise(null, 'ntriples');
    }

    public function testSerialiseNonObjectGraph()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_serialiser->serialise('string', 'ntriples');
    }

    public function testSerialiseNonGraph()
    {
        $nongraph = new EasyRdf_Resource('http://www.example.com/');
        $this->setExpectedException('InvalidArgumentException');
        $this->_serialiser->serialise($nongraph, 'ntriples');
    }

    function testSerialiseUnsupportedFormat()
    {
        $this->setExpectedException('EasyRdf_Exception');
        $rdf = $this->_serialiser->serialise(
            $this->_graph, 'unsupportedformat'
        );
    }

    function testSerialiseNtriples()
    {
        $joe = $this->_graph->resource('http://www.example.com/joe#me');
        $joe->set('foaf:name', 'Joe Bloggs');
        $joe->set('foaf:homepage', $this->_graph->resource('http://www.example.com/joe/'));
        $this->assertEquals(
            "<http://www.example.com/joe#me> ".
                "<http://xmlns.com/foaf/0.1/name> ".
                "\"Joe Bloggs\" .\n".
            "<http://www.example.com/joe#me> ".
                "<http://xmlns.com/foaf/0.1/homepage> ".
                "<http://www.example.com/joe/> .\n",
            $this->_serialiser->serialise($this->_graph,'ntriples')
        );
    }

    function testSerialiseNtriplesQuotes()
    {
        $joe = $this->_graph->resource('http://www.example.com/joe#me');
        $joe->set('foaf:nick', '"Joey"');
        $this->assertEquals(
            "<http://www.example.com/joe#me> ".
                "<http://xmlns.com/foaf/0.1/nick> ".
                '"\"Joey\"" .'."\n",
            $this->_serialiser->serialise($this->_graph,'ntriples')
        );
    }

    function testSerialiseNtriplesBNode()
    {
        $joe = $this->_graph->resource('http://www.example.com/joe#me');
        $this->_graph->add($joe, 'foaf:project', array('foaf:name' => 'Project Name'));

        $this->assertEquals(
            "<http://www.example.com/joe#me> ".
              "<http://xmlns.com/foaf/0.1/project> _:eid1 .\n".
            "_:eid1 <http://xmlns.com/foaf/0.1/name> \"Project Name\" .\n",
            $this->_serialiser->serialise($this->_graph,'ntriples')
        );
    }
}
