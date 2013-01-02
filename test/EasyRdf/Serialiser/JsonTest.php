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

class EasyRdf_Serialiser_JsonTest extends EasyRdf_TestCase
{
    protected $_serialiser = null;
    protected $_graph = null;

    public function setUp()
    {
        $this->_graph = new EasyRdf_Graph();
        $this->_serialiser = new EasyRdf_Serialiser_Json();
    }

    public function testSerialiseJson()
    {
        $joe = $this->_graph->resource(
            'http://www.example.com/joe#me', 'foaf:Person'
        );
        $joe->set('foaf:name', new EasyRdf_Literal('Joe Bloggs', 'en'));
        $joe->set('foaf:age', 59);
        $project = $this->_graph->newBNode();
        $project->add('foaf:name', 'Project Name');
        $joe->add('foaf:project', $project);

        $this->assertSame(
            '{"http:\/\/www.example.com\/joe#me":{'.
            '"http:\/\/www.w3.org\/1999\/02\/22-rdf-syntax-ns#type":['.
            '{"type":"uri","value":"http:\/\/xmlns.com\/foaf\/0.1\/Person"}],'.
            '"http:\/\/xmlns.com\/foaf\/0.1\/name":['.
            '{"type":"literal","value":"Joe Bloggs","lang":"en"}],'.
            '"http:\/\/xmlns.com\/foaf\/0.1\/age":['.
            '{"type":"literal","value":"59","datatype":'.
            '"http:\/\/www.w3.org\/2001\/XMLSchema#integer"}],'.
            '"http:\/\/xmlns.com\/foaf\/0.1\/project":['.
            '{"type":"bnode","value":"_:genid1"}]},"_:genid1":{'.
            '"http:\/\/xmlns.com\/foaf\/0.1\/name":['.
            '{"type":"literal","value":"Project Name"}]}}',
            $this->_serialiser->serialise($this->_graph, 'json')
        );
    }

    public function testSerialiseUnsupportedFormat()
    {
        $this->setExpectedException(
            'EasyRdf_Exception',
            'EasyRdf_Serialiser_Json does not support: unsupportedformat'
        );
        $rdf = $this->_serialiser->serialise(
            $this->_graph, 'unsupportedformat'
        );
    }
}
