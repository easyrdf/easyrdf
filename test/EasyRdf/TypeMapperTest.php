<?php

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009 Nicholas J Humfrey.  All rights reserved.
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
 * @copyright  Copyright (c) 2009 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 * @version    $Id$
 */

require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'TestHelper.php';

class MyType_Class extends EasyRdf_Resource
{
    public function myMethod()
    {
        return true;
    }
}


class EasyRdf_TypeMapperTest extends PHPUnit_Framework_TestCase
{
    /**
     * Set up the test suite before each test
     */
    public function setUp()
    {
        EasyRdf_TypeMapper::set('my:type', 'MyType_Class');
    }

    public function testGet()
    {
        $this->assertEquals('MyType_Class', EasyRdf_TypeMapper::get('my:type'));
    }

    public function testGetNull()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_TypeMapper::get(null);
    }

    public function testGetEmpty()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_TypeMapper::get('');
    }

    public function testGetNonString()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_TypeMapper::get(array());
    }

    public function testGetUnknown()
    {
        $this->assertEquals(null, EasyRdf_TypeMapper::get('unknown:type'));
    }

    public function testSetTypeNull()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_TypeMapper::set(null, 'MyType_Class');
    }

    public function testSetTypeEmpty()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_TypeMapper::set('', 'MyType_Class');
    }

    public function testSetTypeNonString()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_TypeMapper::set(array(), 'MyType_Class');
    }

    public function testSetClassNull()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_TypeMapper::set('my:type', null);
    }

    public function testSetClassEmpty()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_TypeMapper::set('my:type', '');
    }

    public function testSetClassNonString()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_TypeMapper::set('my:type', array());
    }

    public function testInstantiate()
    {
        EasyRdf_TypeMapper::set('foaf:Person', 'MyType_Class');
        $data = readFixture('foaf.json');
        $graph = new EasyRdf_Graph(
            'http://www.example.com/joe/foaf.rdf', $data
        );
        $joe = $graph->get('http://www.example.com/joe#me');
        $this->assertEquals('MyType_Class', get_class($joe));
        $this->assertTrue($joe->myMethod());
    }
}
