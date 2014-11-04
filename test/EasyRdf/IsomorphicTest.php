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
 * @copyright  Copyright (c) 2013 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */

require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'TestHelper.php';

class EasyRdf_IsomorphicTest extends EasyRdf_TestCase
{
    /**
     * Set up the test suite before each test
     */
    public function setUp()
    {
        $this->graphA = new EasyRdf_Graph();
        $this->graphB = new EasyRdf_Graph();
    }

    public function checkTestCase($filename, $successful = true)
    {
        $this->graphA->parseFile(fixturePath("isomorphic/$filename-a.nt"));
        $this->graphB->parseFile(fixturePath("isomorphic/$filename-b.nt"));
        if ($successful) {
            self::assertTrue(EasyRdf_Isomorphic::isomorphic($this->graphA, $this->graphB));
        } else {
            self::assertFalse(EasyRdf_Isomorphic::isomorphic($this->graphA, $this->graphB));
        }
    }

    public function testGood01()
    {
        // One triple with renamed bnode
        $this->checkTestCase('good-01');
    }

    public function testGood02()
    {
        // Two triples with renamed bnodes
        $this->checkTestCase('good-02');
    }

    public function testGood03()
    {
        // Two related triples with renamed bnodes
        $this->checkTestCase('good-03');
    }

    public function testGood04()
    {
        // Circular bnode reference with renamed bnodes
        $this->checkTestCase('good-04');
    }

    public function testGood05()
    {
        $this->markTestIncomplete("FIXME: Three triple chain with renamed bnodes is not implemented yet");
        // Three triple chain with renamed bnodes
        $this->checkTestCase('good-05');
    }

    public function testGood06()
    {
        // Single identical triple without bnodes
        $this->checkTestCase('good-06');
    }

    public function testGood07()
    {
        // 31 triple result-set with renamed bnodes
        $this->checkTestCase('good-07');
    }

    public function testBad01()
    {
        // Subject and object swapped with single bnode
        $this->checkTestCase('bad-01', false);
    }

    public function testBad02()
    {
        // Unequal bnode chain with single grounding
        $this->checkTestCase('bad-02', false);
    }

    public function testBad03()
    {
        // Subject and object swapped with all URIs
        $this->checkTestCase('bad-03', false);
    }

    public function testBad04()
    {
        // Reversed subject and object as extra triple
        $this->checkTestCase('bad-04', false);
    }

    public function testBad05()
    {
        // URI changed to literal
        $this->checkTestCase('bad-05', false);
    }

    public function testBad06()
    {
        // Differing datatypes
        $this->checkTestCase('bad-06', false);
    }
}
