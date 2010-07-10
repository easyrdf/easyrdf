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


class EasyRdf_SerialiserTest extends EasyRdf_TestCase
{
    /**
     * Set up the test suite before each test
     */
    public function setUp()
    {
        EasyRdf_Serialiser::register('MySerialiser_Class', 'myname', 'my/mime');
    }

    public function testGetByName()
    {
        $this->assertEquals(
            'MySerialiser_Class',
            EasyRdf_Serialiser::getByName('myname')
        );
    }

    public function testGetByNameNull()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Serialiser::getByName(null);
    }

    public function testGetByNameEmpty()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Serialiser::getByName('');
    }

    public function testGetByNameNonString()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Serialiser::getByName(array());
    }

    public function testGetByNameUnknown()
    {
        $this->assertEquals(null, EasyRdf_Serialiser::getByName('unknown'));
    }

    public function testGetByMimeType()
    {
        $this->assertEquals(
            'MySerialiser_Class',
            EasyRdf_Serialiser::getByMimeType('my/mime')
        );
    }

    public function testGetByMimeTypeNull()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Serialiser::getByMimeType(null);
    }

    public function testGetByMimeTypeEmpty()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Serialiser::getByMimeType('');
    }

    public function testGetByMimeTypeNonString()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Serialiser::getByMimeType(array());
    }

    public function testGetByMimeTypeUnknown()
    {
        $this->assertEquals(null, EasyRdf_Serialiser::getByMimeType('unknown'));
    }

    public function testRegisterMultipleMimeTypes()
    {
        EasyRdf_Serialiser::register(
            'MySerialiser_Class2',
            'myname2',
            array('my1/mime1', 'my2/mime2')
        );
        $this->assertEquals(
            'MySerialiser_Class2',
            EasyRdf_Serialiser::getByMimeType('my1/mime1')
        );
        $this->assertEquals(
            'MySerialiser_Class2',
            EasyRdf_Serialiser::getByMimeType('my2/mime2')
        );
    }

    public function testRegisterNameNull()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Serialiser::register('MySerialiser_Class', null);
    }

    public function testRegisterNameEmpty()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Serialiser::register('MySerialiser_Class', '');
    }

    public function testRegisterNameNonString()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Serialiser::register('MySerialiser_Class', array());
    }

    public function testRegisterClassNull()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Serialiser::register(null, 'myname');
    }

    public function testRegisterClassEmpty()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Serialiser::register('', 'myname');
    }

    public function testRegisterClassNonString()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Serialiser::register(array(), 'myname');
    }
    
    public function testGetNames()
    {
        $names = EasyRdf_Serialiser::getNames();
        $this->assertTrue(is_array($names));
        $this->assertTrue(in_array('ntriples', $names));
    }
}
