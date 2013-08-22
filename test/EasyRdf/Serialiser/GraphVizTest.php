<?php

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009-2013 Nicholas J Humfrey.  All rights reserved.
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
 * @copyright  Copyright (c) 2009-2013 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */

require_once dirname(dirname(dirname(__FILE__))).
             DIRECTORY_SEPARATOR.'TestHelper.php';

class EasyRdf_Serialiser_GraphVizTest extends EasyRdf_TestCase
{
    public function setUp()
    {
        exec('which dot', $output, $retval);
        if ($retval == 0) {
            $this->graph = new EasyRdf_Graph();
            $this->serialiser = new EasyRdf_Serialiser_GraphViz();

            // Put some data in the graph
            $joe = $this->graph->resource('http://www.example.com/joe#me');
            $joe->set('foaf:name', 'Joe Bloggs');
            $project = $this->graph->newBNode();
            $project->add('foaf:name', 'Project Name');
            $joe->add('foaf:project', $project);

            parent::setUp();
        } else {
            $this->markTestSkipped(
                "The 'dot' command is not available on this system."
            );
        }
    }

    public function testSetDotCommand()
    {
        $this->serialiser->setDotCommand('/usr/bin/dot');
        $this->assertSame('/usr/bin/dot', $this->serialiser->getDotCommand());
    }

    public function testSetUseLabelsTrue()
    {
        $this->serialiser->setUseLabels(true);
        $this->assertTrue($this->serialiser->getUseLabels());
    }

    public function testSetUseLabelsFalse()
    {
        $this->serialiser->setUseLabels(false);
        $this->assertFalse($this->serialiser->getUseLabels());
    }

    public function testSetOnlyLabelledTrue()
    {
        $this->serialiser->setOnlyLabelled(true);
        $this->assertTrue($this->serialiser->getOnlyLabelled());
    }

    public function testSetOnlyLabelledFalse()
    {
        $this->serialiser->setOnlyLabelled(false);
        $this->assertFalse($this->serialiser->getOnlyLabelled());
    }

    public function testGetAtrributeCharset()
    {
        $this->assertSame(
            'utf-8',
            $this->serialiser->getAttribute('charset')
        );
    }

    public function testSetAtrribute()
    {
        $this->serialiser->setAttribute('rankdir', 'LR');
        $this->assertSame('LR', $this->serialiser->getAttribute('rankdir'));
        $this->serialiser->setAttribute('rankdir', 'RL');
        $this->assertSame('RL', $this->serialiser->getAttribute('rankdir'));
    }

    public function testSerialiseDot()
    {
        $this->serialiser->setUseLabels(false);
        $this->serialiser->setOnlyLabelled(false);
        $dot = $this->serialiser->serialise($this->graph, 'dot');
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
            ),
            explode("\n", $dot)
        );
    }

    public function testSerialiseDotUseLabels()
    {
        $this->serialiser->setUseLabels(true);
        $this->serialiser->setOnlyLabelled(false);
        $dot = $this->serialiser->serialise($this->graph, 'dot');

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
            ),
            explode("\n", $dot)
        );
    }

    public function testSerialiseDotOnlyLabelled()
    {
        $this->graph->set('foaf:project', 'rdfs:label', 'project');
        $this->serialiser->setUseLabels(true);
        $this->serialiser->setOnlyLabelled(true);
        $dot = $this->serialiser->serialise($this->graph, 'dot');

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
            ),
            explode("\n", $dot)
        );
    }

    public function testSerialisePng()
    {
        $this->serialiser->setUseLabels(false);
        $this->serialiser->setOnlyLabelled(false);
        $img = $this->serialiser->serialise($this->graph, 'png');
        $info = getimagesize(
            'data:application/octet-stream;base64,'.base64_encode($img)
        );

        $this->assertSame('image/png', $info['mime']);
        $this->assertTrue(500 > $info[0], 'Image width is less than 500');  # width=469
        $this->assertTrue(350 < $info[0], 'Image width is greater than 350');
        $this->assertTrue(350 > $info[1], 'Image height is less than 350');  # height=299
        $this->assertTrue(250 < $info[1], 'Image height is greater than 250');
    }

    public function testSerialiseGif()
    {
        $this->serialiser->setUseLabels(false);
        $this->serialiser->setOnlyLabelled(false);
        $img = $this->serialiser->serialise($this->graph, 'gif');
        $info = getimagesize(
            'data:application/octet-stream;base64,'.base64_encode($img)
        );

        $this->assertSame('image/gif', $info['mime']);
        $this->assertTrue(500 > $info[0], 'Image width is less than 500');  # width=469
        $this->assertTrue(350 < $info[0], 'Image width is greater than 350');
        $this->assertTrue(350 > $info[1], 'Image height is less than 350');  # height=304
        $this->assertTrue(250 < $info[1], 'Image height is greater than 250');
    }

    public function testSerialiseSvg()
    {
        $this->serialiser->setUseLabels(false);
        $this->serialiser->setOnlyLabelled(false);
        $svg = $this->serialiser->serialise($this->graph, 'svg');

        $this->assertContains(
            'class="node"><title>Rhttp://www.example.com/joe#me</title>',
            $svg
        );
        $this->assertContains(
            'class="node"><title>LJoe Bloggs</title>',
            $svg
        );
        $this->assertContains(
            'class="edge"><title>Rhttp://www.example.com/joe#me&#45;&gt;LJoe Bloggs</title>',
            $svg
        );
        $this->assertContains(
            'class="node"><title>B_:genid1</title>',
            $svg
        );
        $this->assertContains(
            'class="edge"><title>Rhttp://www.example.com/joe#me&#45;&gt;B_:genid1</title>',
            $svg
        );
        $this->assertContains(
            'class="node"><title>LProject Name</title>',
            $svg
        );
        $this->assertContains(
            'class="edge"><title>B_:genid1&#45;&gt;LProject Name</title>',
            $svg
        );
    }

    public function testDotNotFound()
    {
        $this->setExpectedException(
            'EasyRdf_Exception',
            'Error while executing command does/not/exist'
        );
        $this->serialiser->setDotCommand('does/not/exist');
        $this->serialiser->renderImage($this->graph);
    }

    public function testSerialiseUnsupportedFormat()
    {
        $this->setExpectedException(
            'EasyRdf_Exception',
            'EasyRdf_Serialiser_GraphViz does not support: unsupportedformat'
        );
        $rdf = $this->serialiser->serialise(
            $this->graph,
            'unsupportedformat'
        );
    }
}
