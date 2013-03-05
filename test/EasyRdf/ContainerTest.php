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

class EasyRdf_ContainerTest extends EasyRdf_TestCase
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

    public function testParseSeq()
    {
        $count = $this->graph->parse(
            readFixture('rdf-seq.rdf'),
            'rdfxml',
            'http://www.w3.org/TR/REC-rdf-syntax/'
        );

        $favourites = $this->graph->resource('ex:favourite-fruit');
        $this->assertSame('rdf:Seq', $favourites->type());
        $this->assertClass('EasyRdf_Container', $favourites);

        $this->assertSame(true, $favourites->valid());
        $this->assertSame(1, $favourites->key());
        $this->assertStringEquals('http://example.org/banana', $favourites->current());

        $favourites->next();

        $this->assertSame(true, $favourites->valid());
        $this->assertSame(2, $favourites->key());
        $this->assertStringEquals('http://example.org/apple', $favourites->current());

        $favourites->next();

        $this->assertSame(true, $favourites->valid());
        $this->assertSame(3, $favourites->key());
        $this->assertStringEquals('http://example.org/pear', $favourites->current());

        $favourites->next();

        $this->assertSame(true, $favourites->valid());
        $this->assertSame(4, $favourites->key());
        $this->assertStringEquals('http://example.org/pear', $favourites->current());

        $favourites->next();

        $this->assertSame(false, $favourites->valid());

        $favourites->rewind();

        $this->assertSame(true, $favourites->valid());
        $this->assertSame(1, $favourites->key());
        $this->assertStringEquals('http://example.org/banana', $favourites->current());
    }

    public function testForeach()
    {
        $this->graph->parse(
            readFixture('rdf-seq.rdf'),
            'rdfxml',
            'http://www.w3.org/TR/REC-rdf-syntax/'
        );

        $favourites = $this->graph->resource('ex:favourite-fruit');

        $list = array();
        foreach ($favourites as $fruit) {
            $list[] = $fruit->getUri();
        }

        $this->assertEquals(
            array(
                'http://example.org/banana',
                'http://example.org/apple',
                'http://example.org/pear',
                'http://example.org/pear'
            ),
            $list
        );
    }

    public function testSeek()
    {
        $this->graph->parse(
            readFixture('rdf-seq.rdf'),
            'rdfxml',
            'http://www.w3.org/TR/REC-rdf-syntax/'
        );

        $favourites = $this->graph->resource('ex:favourite-fruit');

        $favourites->seek(1);
        $this->assertTrue($favourites->valid());
        $this->assertStringEquals('http://example.org/banana', $favourites->current());

        $favourites->seek(2);
        $this->assertTrue($favourites->valid());
        $this->assertStringEquals('http://example.org/apple', $favourites->current());

        $favourites->seek(3);
        $this->assertTrue($favourites->valid());
        $this->assertStringEquals('http://example.org/pear', $favourites->current());

        $favourites->seek(4);
        $this->assertTrue($favourites->valid());
        $this->assertStringEquals('http://example.org/pear', $favourites->current());
    }

    public function testSeekInvalid()
    {
        $this->setExpectedException(
            'OutOfBoundsException',
            'Unable to seek to position 2 in the container'
        );
        $seq = $this->graph->newBnode('rdf:Seq');
        $seq->add('rdf:_1', 'Item 1');
        $seq->seek(2);
    }

    public function testSeekZero()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Container position must be a positive integer'
        );
        $seq = $this->graph->newBnode('rdf:Seq');
        $seq->seek(0);
    }

    public function testSeekMinusOne()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Container position must be a positive integer'
        );
        $seq = $this->graph->newBnode('rdf:Seq');
        $seq->seek(-1);
    }

    public function testSeekNonInteger()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Container position must be a positive integer'
        );
        $seq = $this->graph->newBnode('rdf:Seq');
        $seq->seek('foo');
    }

    public function testCountEmpty()
    {
        $seq = $this->graph->newBnode('rdf:Seq');
        $this->assertSame(0, count($seq));
    }

    public function testCountOne()
    {
        $seq = $this->graph->newBnode('rdf:Seq');
        $seq->add('rdf:_1', 'Item');
        $this->assertSame(1, count($seq));
    }

    public function testCountTwo()
    {
        $seq = $this->graph->newBnode('rdf:Seq');
        $seq->add('rdf:_1', 'Item 1');
        $seq->add('rdf:_2', 'Item 2');
        $this->assertSame(2, count($seq));
    }

    public function testArrayOffsetExists()
    {
        $seq = $this->graph->newBnode('rdf:Seq');
        $seq->add('rdf:_1', 'Item');
        $this->assertTrue(isset($seq[1]));
    }

    public function testArrayOffsetDoesntExist()
    {
        $seq = $this->graph->newBnode('rdf:Seq');
        $seq->add('rdf:_1', 'Item');
        $this->assertFalse(isset($seq[2]));
    }

    public function testArrayOffsetExistsZero()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Container position must be a positive integer'
        );
        $seq = $this->graph->newBnode('rdf:Seq');
        isset($seq[0]);
    }

    public function testArrayOffsetExistsMinusOne()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Container position must be a positive integer'
        );
        $seq = $this->graph->newBnode('rdf:Seq');
        isset($seq[-1]);
    }

    public function testArrayOffsetExistsNonInteger()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Container position must be a positive integer'
        );
        $seq = $this->graph->newBnode('rdf:Seq');
        isset($seq['foo']);
    }

    public function testArrayOffsetGet()
    {
        $this->graph->parse(
            readFixture('rdf-seq.rdf'),
            'rdfxml',
            'http://www.w3.org/TR/REC-rdf-syntax/'
        );

        $favourites = $this->graph->resource('ex:favourite-fruit');
        $this->assertStringEquals('http://example.org/banana', $favourites[1]);
        $this->assertStringEquals('http://example.org/apple', $favourites[2]);
        $this->assertStringEquals('http://example.org/pear', $favourites[3]);
        $this->assertStringEquals('http://example.org/pear', $favourites[4]);
    }

    public function testArrayOffsetGetNonexistent()
    {
        $seq = $this->graph->newBnode('rdf:Seq');
        $this->assertNull($seq[5]);
    }

    public function testArrayOffsetGetZero()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Container position must be a positive integer'
        );
        $seq = $this->graph->newBnode('rdf:Seq');
        $seq[0];
    }

    public function testArrayOffsetGetMinusOne()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Container position must be a positive integer'
        );
        $seq = $this->graph->newBnode('rdf:Seq');
        $seq[-1];
    }

    public function testArrayOffsetGetNonInteger()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Container position must be a positive integer'
        );
        $seq = $this->graph->newBnode('rdf:Seq');
        $seq['foo'];
    }

    public function testArrayOffsetSet()
    {
        $seq = $this->graph->newBnode('rdf:Seq');

        $seq[1] = 'Item 1';
        $this->assertStringEquals('Item 1', $seq->get('rdf:_1'));

        $seq[2] = 'Item 2';
        $this->assertStringEquals('Item 2', $seq->get('rdf:_2'));

        $seq[3] = 'Item 3';
        $this->assertStringEquals('Item 3', $seq->get('rdf:_3'));
    }

    public function testArrayOffsetSetReplace()
    {
        $seq = $this->graph->newBnode('rdf:Seq');

        $seq->add('rdf:_1', 'Item 1');
        $seq[1] = 'Replace';
        $this->assertStringEquals('Replace', $seq->get('rdf:_1'));
    }

    public function testArrayOffsetAppend()
    {
        $seq = $this->graph->newBnode('rdf:Seq');

        $seq[] = 'Item 1';
        $seq[] = 'Item 2';
        $seq[] = 'Item 3';

        $this->assertStringEquals('Item 1', $seq->get('rdf:_1'));
        $this->assertStringEquals('Item 2', $seq->get('rdf:_2'));
        $this->assertStringEquals('Item 3', $seq->get('rdf:_3'));
    }

    public function testArrayOffsetSetZero()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Container position must be a positive integer'
        );
        $seq = $this->graph->newBnode('rdf:Seq');
        $seq[0] = 'Item 1';
    }

    public function testArrayOffsetSetMinusOne()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Container position must be a positive integer'
        );
        $seq = $this->graph->newBnode('rdf:Seq');
        $seq[-1] = 'Item 1';
    }

    public function testArrayOffsetSetNonInteger()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Container position must be a positive integer'
        );
        $seq = $this->graph->newBnode('rdf:Seq');
        $seq['foo'] = 'Item 1';
    }

    public function testArrayOffsetUnset()
    {
        $seq = $this->graph->newBnode('rdf:Seq');

        $seq->add('rdf:_1', 'Item 1');
        $this->assertStringEquals('Item 1', $seq->get('rdf:_1'));
        unset($seq[1]);
        $this->assertNull($seq->get('rdf:_1'));
    }

    public function testArrayOffsetUnsetZero()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Container position must be a positive integer'
        );
        $seq = $this->graph->newBnode('rdf:Seq');
        unset($seq[0]);
    }

    public function testArrayOffsetUnsetMinusOne()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Container position must be a positive integer'
        );
        $seq = $this->graph->newBnode('rdf:Seq');
        unset($seq[-1]);
    }

    public function testArrayOffsetUnsetNonInteger()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Container position must be a positive integer'
        );
        $seq = $this->graph->newBnode('rdf:Seq');
        unset($seq['foo']);
    }

    public function testAppend()
    {
        $animals = $this->graph->newBnode('rdf:Seq');
        $this->assertSame('rdf:Seq', $animals->type());
        $this->assertClass('EasyRdf_Container', $animals);

        $this->assertEquals(1, $animals->append('Cat'));
        $this->assertEquals(1, $animals->append('Dog'));
        $this->assertEquals(1, $animals->append('Rat'));

        $this->assertEquals('Cat', $animals[1]);
        $this->assertEquals('Dog', $animals[2]);
        $this->assertEquals('Rat', $animals[3]);
    }
}
