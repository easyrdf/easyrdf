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

class EasyRdf_Serialiser_TurtleTest extends EasyRdf_TestCase
{
    protected $_serialiser = null;
    protected $_graph = null;

    public function setUp()
    {
        $this->_graph = new EasyRdf_Graph();
        $this->_serialiser = new EasyRdf_Serialiser_Turtle();
    }

    function testSerialise()
    {
        $joe = $this->_graph->resource(
            'http://example.com/joe#me',
            'foaf:Person'
        );
        $joe->set('foaf:name', 'Joe Bloggs');
        $joe->set(
            'foaf:homepage',
            $this->_graph->resource('http://example.com/joe/')
        );

        $turtle = $this->_serialiser->serialise($this->_graph, 'turtle');
        $this->assertContains(
            "@prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> .",
            $turtle
        );
        $this->assertContains(
            "@prefix foaf: <http://xmlns.com/foaf/0.1/> .",
            $turtle
        );
        $this->assertContains(
            "a foaf:Person ;",
            $turtle
        );
        $this->assertContains(
            "foaf:name \"Joe Bloggs\" ;",
            $turtle
        );
        $this->assertContains(
            "foaf:homepage <http://example.com/joe/> .",
            $turtle
        );
    }

    function testSerialiseBnode()
    {
        $joe = $this->_graph->resource('http://example.com/joe#me');
        $this->_graph->add(
            $joe,
            array('foaf:knows' => array('foaf:name' => 'Amy'))
        );

        $turtle = $this->_serialiser->serialise($this->_graph, 'turtle');
        $this->assertContains(
            "<http://example.com/joe#me> foaf:knows _:eid1 .",
            $turtle
        );
        $this->assertContains("_:eid1 foaf:name \"Amy\" .", $turtle);

        // FIXME: should really output this instead:
        // <http://example.com/joe#me>
        //     foaf:knows [
        //         foaf:name "Amy"
        //     ] .
    }

    function testSerialiseLang()
    {
        $joe = $this->_graph->resource('http://example.com/joe#me');
        $joe->set('foaf:name', new EasyRdf_Literal('Joe', 'en'));

        $turtle = $this->_serialiser->serialise($this->_graph, 'turtle');
        $this->assertContains(
            "<http://example.com/joe#me> foaf:name \"Joe\"@en .",
            $turtle
        );
    }

    function testSerialiseBooleanDatatype()
    {
        $joe = $this->_graph->resource('http://example.com/joe#me');
        $joe->set('foaf:truth', new EasyRdf_Literal(true, null, 'xsd:boolean'));

        $turtle = $this->_serialiser->serialise($this->_graph, 'turtle');
        $this->assertContains(
            "<http://example.com/joe#me> foaf:truth true^^xsd:boolean .",
            $turtle
        );
    }

    function testSerialiseDecimalDatatype()
    {
        $joe = $this->_graph->resource('http://example.com/joe#me');
        $joe->set('foaf:age', new EasyRdf_Literal(1.5, null, 'xsd:decimal'));

        $turtle = $this->_serialiser->serialise($this->_graph, 'turtle');
        $this->assertContains(
            "<http://example.com/joe#me> foaf:age 1.5^^xsd:decimal .",
            $turtle
        );
    }

    function testSerialiseDoubleDatatype()
    {
        $joe = $this->_graph->resource('http://example.com/joe#me');
        $joe->set('foaf:age', new EasyRdf_Literal(1.5, null, 'xsd:double'));

        $turtle = $this->_serialiser->serialise($this->_graph, 'turtle');
        $this->assertContains(
            "<http://example.com/joe#me> foaf:age 1.500000e+0^^xsd:double .",
            $turtle
        );
    }

    function testSerialiseIntegerDatatype()
    {
        $joe = $this->_graph->resource('http://example.com/joe#me');
        $joe->set('foaf:age', new EasyRdf_Literal(49, null, 'xsd:integer'));

        $turtle = $this->_serialiser->serialise($this->_graph, 'turtle');
        $this->assertContains(
            "<http://example.com/joe#me> foaf:age 49^^xsd:integer .",
            $turtle
        );
    }

    function testSerialiseOtherDatatype()
    {
        $joe = $this->_graph->resource('http://example.com/joe#me');
        $joe->set('foaf:foo', new EasyRdf_Literal('foobar', null, 'xsd:other'));

        $turtle = $this->_serialiser->serialise($this->_graph, 'turtle');
        $this->assertContains(
            "<http://example.com/joe#me> foaf:foo \"foobar\"^^xsd:other .",
            $turtle
        );
    }

    function testSerialiseInvalidObject()
    {
        $joe = $this->_graph->resource('http://www.example.com/joe#me');
        $joe->set('rdf:foo', $this);
        $this->setExpectedException('EasyRdf_Exception');
        $this->_serialiser->serialise($this->_graph, 'turtle');
    }

    function testSerialiseUnsupportedFormat()
    {
        $this->setExpectedException('EasyRdf_Exception');
        $rdf = $this->_serialiser->serialise(
            $this->_graph, 'unsupportedformat'
        );
    }
}
