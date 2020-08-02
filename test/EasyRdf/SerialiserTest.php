<?php
namespace EasyRdf;

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009-2016 Nicholas J Humfrey.  All rights reserved.
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
 * @copyright  Copyright (c) 2009-2016 Nicholas J Humfrey
 * @license    https://www.opensource.org/licenses/bsd-license.php
 */

require_once dirname(__DIR__).DIRECTORY_SEPARATOR.'TestHelper.php';

class MockSerialiser extends Serialiser
{
    public function serialise(Graph $graph, $format, array $options = array())
    {
        parent::checkSerialiseParams($format);
        // Serialising goes here
        return true;
    }
}

class SerialiserTest extends TestCase
{
    /** @var Graph */
    private $graph;
    /** @var Resource */
    private $resource;
    /** @var Serialiser */
    private $serialiser;

    /**
     * Set up the test suite before each test
     */
    public function setUp()
    {
        $this->graph = new Graph();
        $this->resource = $this->graph->resource('http://www.example.com/');
        $this->serialiser = new MockSerialiser();
    }

    public function testSerialise()
    {
        $this->assertTrue(
            $this->serialiser->serialise($this->graph, 'php')
        );
    }

    public function testSerialiseFormatObject()
    {
        $format = Format::getFormat('json');
        $this->assertTrue(
            $this->serialiser->serialise($this->graph, $format)
        );
    }

    public function testSerialiseNullFormat()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$format cannot be null or empty'
        );
        $this->serialiser->serialise($this->graph, null);
    }

    public function testSerialiseEmptyFormat()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$format cannot be null or empty'
        );
        $this->serialiser->serialise($this->graph, '');
    }

    public function testSerialiseBadObjectFormat()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$format should be a string or an EasyRdf\Format object'
        );
        $this->serialiser->serialise($this->graph, $this);
    }

    public function testSerialiseIntegerFormat()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$format should be a string or an EasyRdf\Format object'
        );
        $this->serialiser->serialise($this->graph, 1);
    }
}
