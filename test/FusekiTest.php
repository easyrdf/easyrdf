<?php

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2013 Nicholas J Humfrey.  All rights reserved.
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

require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'TestHelper.php';

class FusekiTest extends EasyRdf_TestCase
{
    private static $port = null;
    private static $proc = null;
    
    // Starting up Fuseki is slow - we re-use the same instance for every test
    public static function setUpBeforeClass()
    {
        $descriptorspec = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w')
        );
    
        # Start fuseki on a random port number
        self::$port = rand(10000, 60000);
        $cmd = "fuseki-server --port=".self::$port." --update --mem /ds";
        $dir = sys_get_temp_dir();
        self::$proc = proc_open($cmd, $descriptorspec, $pipes, $dir);
    
        # FIXME: timeout
        while ($line = fgets($pipes[1])) {
            if (preg_match('/Started (.+) on port (\d+)/', $line, $matches)) {
                break;
            }
        }

        // Fuseki needs a little bit of extra time before it can acctually recieve requests
        sleep(3);
    }

    public static function tearDownAfterClass()
    {
        if (self::$proc) {
            // Cause the fuseki server process to terminate
            proc_terminate(self::$proc);
    
            // Close the process resource
            proc_close(self::$proc);
        }
    }
    
    public function setUp()
    {
        $this->gs = new EasyRdf_GraphStore("http://localhost:".self::$port."/ds/data");
    }
    
    public function testGraphStoreReplace()
    {
        $graph1 = new EasyRdf_Graph();
        $graph1->set('http://example.com/test', 'rdfs:label', 'Test 0');
        $result = $this->gs->replace($graph1, 'easyrdf-graphstore-test.rdf');
        $this->assertSame(201, $result->getStatus());

        $graph1->set('http://example.com/test', 'rdfs:label', 'Test 1');
        $result = $this->gs->replace($graph1, 'easyrdf-graphstore-test.rdf');
        $this->assertSame(204, $result->getStatus());

        $graph2 = $this->gs->get('easyrdf-graphstore-test.rdf');
        $this->assertEquals(
            array(new EasyRdf_Literal('Test 1')),
            $graph2->all('http://example.com/test', 'rdfs:label')
        );
    }
    
    public function testGraphStoreInsert()
    {
        $graph1 = new EasyRdf_Graph();
        $graph1->set('http://example.com/test', 'rdfs:label', 'Test 2');
        $result = $this->gs->insert($graph1, 'easyrdf-graphstore-test2.rdf');
        $this->assertSame(201, $result->getStatus());

        $graph1->set('http://example.com/test', 'rdfs:label', 'Test 3');
        $result = $this->gs->insert($graph1, 'easyrdf-graphstore-test2.rdf');
        $this->assertSame(204, $result->getStatus());

        $graph2 = $this->gs->get('easyrdf-graphstore-test2.rdf');
        $labels = $graph2->all('http://example.com/test', 'rdfs:label');
        sort($labels);
        $this->assertEquals(
            array(
                new EasyRdf_Literal('Test 2'),
                new EasyRdf_Literal('Test 3')
            ),
            $labels
        );
    }
    
    public function testGraphStoreDelete()
    {
        $graph1 = new EasyRdf_Graph();
        $graph1->set('http://example.com/test', 'rdfs:label', 'Test 4');
        $result = $this->gs->insert($graph1, 'easyrdf-graphstore-test3.rdf');
        $this->assertSame(201, $result->getStatus());

        $graph2 = $this->gs->get('easyrdf-graphstore-test3.rdf');
        $this->assertEquals(1, $graph2->countTriples());

        $result = $this->gs->delete('easyrdf-graphstore-test3.rdf');
        $this->assertSame(204, $result->getStatus());
    }
}
