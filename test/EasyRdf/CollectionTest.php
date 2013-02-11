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
 * @version    $Id$
 */

require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'TestHelper.php';

class EasyRdf_CollectionTest extends EasyRdf_TestCase
{
    public function setUp()
    {
        $this->graph = new EasyRdf_Graph();
        EasyRdf_Namespace::set('ex', 'http://example.org/');
    }

    public function tearDown()
    {
        EasyRdf_Namespace::delete('ex');
    }

    public function testParseCollection()
    {
        $count = $this->graph->parse(readFixture('rdf-collection.rdf'), 'rdfxml');

        $owner = $this->graph->resource('ex:owner');
        $pets = $owner->get('ex:pets');
        $this->assertClass('EasyRdf_Collection', $pets);

        $this->assertTrue($pets->valid());
        $this->assertSame(1, $pets->key());
        $this->assertStringEquals('http://example.org/rat', $pets->current());

        $pets->next();

        $this->assertTrue($pets->valid());
        $this->assertSame(2, $pets->key());
        $this->assertStringEquals('http://example.org/cat', $pets->current());

        $pets->next();

        $this->assertTrue($pets->valid());
        $this->assertSame(3, $pets->key());
        $this->assertStringEquals('http://example.org/goat', $pets->current());

        $pets->next();

        $this->assertFalse($pets->valid());
        $this->assertSame(4, $pets->key());
        $this->assertSame(null, $pets->current());

        $pets->next();

        $this->assertFalse($pets->valid());
        $this->assertSame(5, $pets->key());
        $this->assertSame(null, $pets->current());

        $pets->rewind();

        $this->assertTrue($pets->valid());
        $this->assertSame(1, $pets->key());
        $this->assertStringEquals('http://example.org/rat', $pets->current());
    }

    public function testForeach()
    {
        $count = $this->graph->parse(readFixture('rdf-collection.rdf'), 'rdfxml');

        $owner = $this->graph->resource('ex:owner');
        $pets = $owner->get('ex:pets');

        $list = array();
        foreach ($pets as $pet) {
            $list[] = $pet->getUri();
        }

        $this->assertEquals(
            array(
                'http://example.org/rat',
                'http://example.org/cat',
                'http://example.org/goat'
            ),
            $list
        );
    }
}
