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
require_once 'EasyRdf/Resource.php';
require_once 'EasyRdf/TypeMapper.php';

class EasyRdf_TypeMapperTest extends PHPUnit_Framework_TestCase
{
    /**
     * Set up the test suite before each test
     */
    public function setUp()
    {
        EasyRdf_TypeMapper::add('mytype', 'MyType_Class');
    }

    public function testGet()
    {
        $this->assertEquals('MyType_Class', EasyRdf_TypeMapper::get('mytype'));
    }

    public function testGetNull()
    {
        $this->assertEquals(null, EasyRdf_TypeMapper::get(null));
    }

    public function testGetEmpty()
    {
        $this->assertEquals(null, EasyRdf_TypeMapper::get(''));
    }

    public function testGetUnknown()
    {
        $this->assertEquals(null, EasyRdf_TypeMapper::get('unknown_type'));
    }

    public function testAddTypeNull()
    {
        $this->setExpectedException('EasyRdf_Exception');
        EasyRdf_TypeMapper::add(null, 'MyType_Class');
    }

    public function testAddTypeEmpty()
    {
        $this->setExpectedException('EasyRdf_Exception');
        EasyRdf_TypeMapper::add('', 'MyType_Class');
    }

    public function testAddTypeNonString()
    {
        $this->setExpectedException('EasyRdf_Exception');
        EasyRdf_TypeMapper::add(array(), 'MyType_Class');
    }

    public function testAddClassNull()
    {
        $this->setExpectedException('EasyRdf_Exception');
        EasyRdf_TypeMapper::add('mytype', null);
    }

    public function testAddClassEmpty()
    {
        $this->setExpectedException('EasyRdf_Exception');
        EasyRdf_TypeMapper::add('mytype', '');
    }

    public function testAddClassNonString()
    {
        $this->setExpectedException('EasyRdf_Exception');
        EasyRdf_TypeMapper::add('mytype', array());
    }
}
