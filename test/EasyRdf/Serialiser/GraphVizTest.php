<?php

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009-2012 Nicholas J Humfrey.  All rights reserved.
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
 * @copyright  Copyright (c) 2009-2012 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 * @version    $Id$
 */

require_once dirname(dirname(dirname(__FILE__))).
             DIRECTORY_SEPARATOR.'TestHelper.php';

class EasyRdf_Serialiser_GraphVizTest extends EasyRdf_TestCase
{
    public function setUp()
    {
        exec('which dot', $output, $retval);
        if ($retval == 0) {
            $this->_graph = new EasyRdf_Graph();
            $this->_serialiser = new EasyRdf_Serialiser_GraphViz();

            // Put some data in the graph
            $joe = $this->_graph->resource('http://www.example.com/joe#me');
            $joe->set('foaf:name', 'Joe Bloggs');
            $project = $this->_graph->newBNode();
            $project->add('foaf:name', 'Project Name');
            $joe->add('foaf:project', $project);

            parent::setUp();
        } else {
            $this->markTestSkipped(
                "The 'dot' command is not available on this system."
            );
        }
    }

    function testSetDotCommand()
    {
        $this->_serialiser->setDotCommand('/usr/bin/dot');
        $this->assertSame('/usr/bin/dot', $this->_serialiser->getDotCommand());
    }

    function testSetUseLabelsTrue()
    {
        $this->_serialiser->setUseLabels(true);
        $this->assertTrue($this->_serialiser->getUseLabels());
    }

    function testSetUseLabelsFalse()
    {
        $this->_serialiser->setUseLabels(false);
        $this->assertFalse($this->_serialiser->getUseLabels());
    }

    function testSetOnlyLabelledTrue()
    {
        $this->_serialiser->setOnlyLabelled(true);
        $this->assertTrue($this->_serialiser->getOnlyLabelled());
    }

    function testSetOnlyLabelledFalse()
    {
        $this->_serialiser->setOnlyLabelled(false);
        $this->assertFalse($this->_serialiser->getOnlyLabelled());
    }

    function testGetAtrributeCharset()
    {
        $this->assertSame(
            'utf-8',
            $this->_serialiser->getAttribute('charset')
        );
    }

    function testSetAtrribute()
    {
        $this->_serialiser->setAttribute('rankdir', 'LR');
        $this->assertSame('LR', $this->_serialiser->getAttribute('rankdir'));
        $this->_serialiser->setAttribute('rankdir', 'RL');
        $this->assertSame('RL', $this->_serialiser->getAttribute('rankdir'));
    }

    function testSerialiseDot()
    {
        $this->_serialiser->setUseLabels(false);
        $this->_serialiser->setOnlyLabelled(false);
        $dot = $this->_serialiser->serialise($this->_graph, 'dot');
        $this->assertSame(
            array(
                'digraph {',
                '  charset="utf-8";',
                '',
                '  // Edges',
                '  "Rhttp://www.example.com/joe#me" -> "LJoe Bloggs" [label="foaf:name"];',
                '  "Rhttp://www.example.com/joe#me" -> "B_:genid1" [label="foaf:project"];',
                '  "B_:genid1" -> "LProject Name" [label="foaf:name"];',
                '',
                '  // Nodes',
                '  "B_:genid1" [label="",shape=circle,color=green];',
                '  "LJoe Bloggs" [label="Joe Bloggs",shape=record];',
                '  "LProject Name" [label="Project Name",shape=record];',
                '  "Rhttp://www.example.com/joe#me" [URL="http://www.example.com/joe#me",'.
                'label="http://www.example.com/joe#me",shape=ellipse,color=blue];',
                '}',
                ''
            ), explode("\n", $dot)
        );
    }

    function testSerialiseDotUseLabels()
    {
        $this->_serialiser->setUseLabels(true);
        $this->_serialiser->setOnlyLabelled(false);
        $dot = $this->_serialiser->serialise($this->_graph, 'dot');

        $this->assertSame(
            array(
                'digraph {',
                '  charset="utf-8";',
                '',
                '  // Edges',
                '  "Rhttp://www.example.com/joe#me" -> "LJoe Bloggs" [label="foaf:name"];',
                '  "Rhttp://www.example.com/joe#me" -> "B_:genid1" [label="foaf:project"];',
                '  "B_:genid1" -> "LProject Name" [label="foaf:name"];',
                '',
                '  // Nodes',
                '  "B_:genid1" [label="Project Name",shape=circle,color=green];',
                '  "LJoe Bloggs" [label="Joe Bloggs",shape=record];',
                '  "LProject Name" [label="Project Name",shape=record];',
                '  "Rhttp://www.example.com/joe#me" [URL="http://www.example.com/joe#me",'.
                'label="Joe Bloggs",shape=ellipse,color=blue];',
                '}',
                ''
            ), explode("\n", $dot)
        );
    }

    function testSerialiseDotOnlyLabelled()
    {
        $this->_graph->set('foaf:project', 'rdfs:label', 'project');
        $this->_serialiser->setUseLabels(true);
        $this->_serialiser->setOnlyLabelled(true);
        $dot = $this->_serialiser->serialise($this->_graph, 'dot');

        $this->assertSame(
            array(
                'digraph {',
                '  charset="utf-8";',
                '',
                '  // Edges',
                '  "Rhttp://www.example.com/joe#me" -> "B_:genid1" [label=project];',
                '',
                '  // Nodes',
                '  "B_:genid1" [label="Project Name",shape=circle,color=green];',
                '  "Rhttp://www.example.com/joe#me" [URL="http://www.example.com/joe#me",'.
                'label="Joe Bloggs",shape=ellipse,color=blue];',
                '}',
                ''
            ), explode("\n", $dot)
        );
    }

    function testSerialisePng()
    {
        $this->_serialiser->setUseLabels(false);
        $this->_serialiser->setOnlyLabelled(false);
        $img = $this->_serialiser->serialise($this->_graph, 'png');
        $info = getimagesize(
            'data:application/octet-stream;base64,'.base64_encode($img)
        );

        $this->assertSame('image/png', $info['mime']);
        $this->assertTrue(450 > $info[0], 'Image width is less than 500');  # width=469
        $this->assertTrue(350 < $info[0], 'Image width is greater than 350');
        $this->assertTrue(350 > $info[1], 'Image height is less than 350');  # height=299
        $this->assertTrue(250 < $info[1], 'Image height is greater than 250');
    }

    function testSerialiseGif()
    {
        $this->_serialiser->setUseLabels(false);
        $this->_serialiser->setOnlyLabelled(false);
        $img = $this->_serialiser->serialise($this->_graph, 'gif');
        $info = getimagesize(
            'data:application/octet-stream;base64,'.base64_encode($img)
        );

        $this->assertSame('image/gif', $info['mime']);
        $this->assertTrue(450 > $info[0], 'Image width is less than 500');  # width=469
        $this->assertTrue(350 < $info[0], 'Image width is greater than 350');
        $this->assertTrue(350 > $info[1], 'Image height is less than 350');  # height=304
        $this->assertTrue(250 < $info[1], 'Image height is greater than 250');
    }

    function testSerialiseSvg()
    {
        $this->_serialiser->setUseLabels(false);
        $this->_serialiser->setOnlyLabelled(false);
        $svg = $this->_serialiser->serialise($this->_graph, 'svg');

        $this->assertContains(
            '<g id="node1" class="node"><title>Rhttp://www.example.com/joe#me</title>', $svg
        );
        $this->assertContains(
            '<g id="node3" class="node"><title>LJoe Bloggs</title>', $svg
        );
        $this->assertContains(
            '<g id="edge2" class="edge"><title>Rhttp://www.example.com/joe#me&#45;&gt;LJoe Bloggs</title>', $svg
        );
        $this->assertContains(
            '<g id="node5" class="node"><title>B_:genid1</title>', $svg
        );
        $this->assertContains(
            '<g id="edge4" class="edge"><title>Rhttp://www.example.com/joe#me&#45;&gt;B_:genid1</title>', $svg
        );
        $this->assertContains(
            '<g id="node7" class="node"><title>LProject Name</title>', $svg
        );
        $this->assertContains(
            '<g id="edge6" class="edge"><title>B_:genid1&#45;&gt;LProject Name</title>', $svg
        );
    }

    function testDotNotFound()
    {
        $this->setExpectedException(
            'EasyRdf_Exception',
            'Error while executing command does/not/exist'
        );
        $this->_serialiser->setDotCommand('does/not/exist');
        $this->_serialiser->renderImage($this->_graph);
    }

    function testSerialiseUnsupportedFormat()
    {
        $this->setExpectedException(
            'EasyRdf_Exception',
            'EasyRdf_Serialiser_GraphViz does not support: unsupportedformat'
        );
        $rdf = $this->_serialiser->serialise(
            $this->_graph, 'unsupportedformat'
        );
    }
}
