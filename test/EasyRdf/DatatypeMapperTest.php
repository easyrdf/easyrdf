<?php

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009-2010 Nicholas J Humfrey.  All rights reserved.
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
 * @copyright  Copyright (c) 2009-2010 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 * @version    $Id$
 */

require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'TestHelper.php';

class MyDatatype_Class
{
    public $value = null;

    public function __construct($value)
    {
        $this->_value = $value;
    }

    public function __toString()
    {
        return "!".strval($this->_value)."!";
    }
}


class EasyRdf_DatatypeMapperTest extends EasyRdf_TestCase
{
    public function setUp()
    {
        EasyRdf_Namespace::set('ex', 'http://www.example.com/');
        EasyRdf_DatatypeMapper::set('ex:mytype', 'MyDatatype_Class');
    }

    public function tearDown()
    {
        EasyRdf_DatatypeMapper::delete('ex:mytype');
        EasyRdf_DatatypeMapper::delete('ex:mytype2');
        EasyRdf_Namespace::delete('ex');
    }

    public function testDatatypeForClass()
    {
        $this->assertEquals(
            'http://www.example.com/mytype',
            EasyRdf_DatatypeMapper::datatypeForClass('MyDatatype_Class')
        );
    }

    public function testDatatypeForClassNull()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$class should be a string and cannot be null or empty'
        );
        EasyRdf_DatatypeMapper::datatypeForClass(null);
    }

    public function testDatatypeForClassEmpty()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$class should be a string and cannot be null or empty'
        );
        EasyRdf_DatatypeMapper::datatypeForClass('');
    }

    public function testDatatypeForClassNonString()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$class should be a string and cannot be null or empty'
        );
        EasyRdf_DatatypeMapper::datatypeForClass(array());
    }

    public function testDatatypeForClassUnknown()
    {
        $this->assertEquals(null, EasyRdf_DatatypeMapper::datatypeForClass('unknown:type'));
    }


    public function testClassForDatatype()
    {
        $this->assertEquals(
            'MyDatatype_Class',
            EasyRdf_DatatypeMapper::classForDatatype('ex:mytype')
        );
    }

    public function testClassForDatatypeUri()
    {
        $this->assertEquals(
            'MyDatatype_Class',
            EasyRdf_DatatypeMapper::classForDatatype(
                'http://www.example.com/mytype'
            )
        );
    }

    public function testClassForDatatypeNull()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$datatype should be a string and cannot be null or empty'
        );
        EasyRdf_DatatypeMapper::classForDatatype(null);
    }

    public function testClassForDatatypeEmpty()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$datatype should be a string and cannot be null or empty'
        );
        EasyRdf_DatatypeMapper::classForDatatype('');
    }

    public function testClassForDatatypeNonString()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$datatype should be a string and cannot be null or empty'
        );
        EasyRdf_DatatypeMapper::classForDatatype(array());
    }

    public function testClassForDatatypeUnknown()
    {
        $this->assertEquals(null, EasyRdf_DatatypeMapper::classForDatatype('unknown:type'));
    }

    public function testSetUri()
    {
        EasyRdf_DatatypeMapper::set(
            'http://www.example.com/mytype2',
            'MyDatatype_Class'
        );

        $this->assertEquals(
            'MyDatatype_Class',
            EasyRdf_DatatypeMapper::classForDatatype('ex:mytype2')
        );
    }

    public function testSetTypeNull()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$datatype should be a string and cannot be null or empty'
        );
        EasyRdf_DatatypeMapper::set(null, 'MyDatatype_Class');
    }

    public function testSetTypeEmpty()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$datatype should be a string and cannot be null or empty'
        );
        EasyRdf_DatatypeMapper::set('', 'MyDatatype_Class');
    }

    public function testSetTypeNonString()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$datatype should be a string and cannot be null or empty'
        );
        EasyRdf_DatatypeMapper::set(array(), 'MyDatatype_Class');
    }

    public function testSetClassNull()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$class should be a string and cannot be null or empty'
        );
        EasyRdf_DatatypeMapper::set('ex:mytype', null);
    }

    public function testSetClassEmpty()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$class should be a string and cannot be null or empty'
        );
        EasyRdf_DatatypeMapper::set('ex:mytype', '');
    }

    public function testSetClassNonString()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$class should be a string and cannot be null or empty'
        );
        EasyRdf_DatatypeMapper::set('ex:mytype', array());
    }

    public function testDelete()
    {
        $this->assertEquals('MyDatatype_Class', EasyRdf_DatatypeMapper::classForDatatype('ex:mytype'));
        EasyRdf_DatatypeMapper::delete('ex:mytype');
        $this->assertEquals(null, EasyRdf_DatatypeMapper::classForDatatype('ex:mytype'));
    }

    public function testDeleteTypeNull()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$datatype should be a string and cannot be null or empty'
        );
        EasyRdf_DatatypeMapper::delete(null);
    }

    public function testDeleteTypeEmpty()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$datatype should be a string and cannot be null or empty'
        );
        EasyRdf_DatatypeMapper::delete('');
    }

    public function testDeleteTypeNonString()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$datatype should be a string and cannot be null or empty'
        );
        EasyRdf_DatatypeMapper::delete(array());
    }

    public function testLiteralDatatypeMatching()
    {
        $mine = new MyDatatype_Class('text');
        $literal = new EasyRdf_Literal($mine);
        $this->assertEquals($mine, $literal->getValue());
        $this->assertStringEquals('!text!', $literal);
        $this->assertEquals('ex:mytype', $literal->getDatatype());
        $this->assertEquals(null, $literal->getLang());
    }

    public function testLiteralTypeConversion()
    {
        $literal = new EasyRdf_Literal('foobar', null, 'ex:mytype');
        $this->assertType('MyDatatype_Class', $literal->getValue());
        $this->assertStringEquals('!foobar!', $literal);
        $this->assertEquals('ex:mytype', $literal->getDatatype());
        $this->assertEquals(null, $literal->getLang());
    }
}
