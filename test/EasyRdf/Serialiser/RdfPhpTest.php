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

class EasyRdf_Serialiser_RdfPhpTest extends EasyRdf_TestCase
{
    protected $_serialiser = null;
    protected $_graph = null;

    public function setUp()
    {
        $this->_graph = new EasyRdf_Graph();
        $this->_serialiser = new EasyRdf_Serialiser_RdfPhp();
    }

    function testSerialisePhp()
    {
        $joe = $this->_graph->resource(
            'http://www.example.com/joe#me', 'foaf:Person'
        );
        $joe->set('foaf:name', new EasyRdf_Literal('Joe Bloggs', 'en'));
        $joe->set('foaf:age', 59);
        $project = $this->_graph->newBNode();
        $project->add('foaf:name', 'Project Name');
        $joe->add('foaf:project', $project);

        $php = $this->_serialiser->serialise($this->_graph, 'php');
        $this->assertType('array', $php);
        $subject = $php['http://www.example.com/joe#me'];
        $this->assertType('array', $subject);
        $type = $subject['http://www.w3.org/1999/02/22-rdf-syntax-ns#type'][0];
        $this->assertEquals('uri', $type['type']);
        $this->assertEquals('http://xmlns.com/foaf/0.1/Person', $type['value']);
        $name = $subject['http://xmlns.com/foaf/0.1/name'][0];
        $this->assertType('array', $name);
        $this->assertEquals('literal', $name['type']);
        $this->assertEquals('Joe Bloggs', $name['value']);
        $this->assertFalse(isset($name['datatype']));
        $this->assertEquals('en', $name['lang']);
        $age = $subject['http://xmlns.com/foaf/0.1/age'][0];
        $this->assertType('array', $age);
        $this->assertEquals('literal', $age['type']);
        $this->assertEquals('59', $age['value']);
        $this->assertEquals(
            'http://www.w3.org/2001/XMLSchema#integer',
            $age['datatype']
        );
        $this->assertFalse(isset($age['lang']));

        $nodeid = $subject['http://xmlns.com/foaf/0.1/project'][0]['value'];
        $this->assertType('array', $php[$nodeid]);
        $projectName = $php[$nodeid]['http://xmlns.com/foaf/0.1/name'][0];
        $this->assertEquals('Project Name', $projectName['value']);
        $this->assertFalse(isset($projectName['lang']));
        $this->assertFalse(isset($projectName['datatype']));
    }

    function testSerialiseUnsupportedFormat()
    {
        $this->setExpectedException('EasyRdf_Exception');
        $rdf = $this->_serialiser->serialise(
            $this->_graph, 'unsupportedformat'
        );
    }
}
