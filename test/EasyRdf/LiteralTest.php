<?php

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009-2011 Nicholas J Humfrey.  All rights reserved.
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
 * @copyright  Copyright (c) 2009-2011 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 * @version    $Id$
 */

require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'TestHelper.php';

class MyDatatype_Class extends EasyRdf_Literal
{
    public function __toString()
    {
        return "!".strval($this->_value)."!";
    }
}

class EasyRdf_LiteralTest extends EasyRdf_TestCase
{
    public function setUp()
    {
        EasyRdf_Namespace::set('ex', 'http://www.example.com/');
    }

    public function tearDown()
    {
        EasyRdf_Literal::deleteDatatypeMapping('ex:mytype');
        EasyRdf_Namespace::delete('ex');
    }

    public function testCreate()
    {
        $literal = EasyRdf_Literal::create('Rat');
        $this->assertClass('EasyRdf_Literal', $literal);
        $this->assertEquals('Rat', $literal->getValue());
        $this->assertEquals(null, $literal->getLang());
        $this->assertEquals(null, $literal->getDatatype());
    }

    public function testCreateWithLanguage()
    {
        $literal = new EasyRdf_Literal('Rat', 'en');
        $this->assertClass('EasyRdf_Literal', $literal);
        $this->assertEquals('Rat', $literal->getValue());
        $this->assertEquals('en', $literal->getLang());
        $this->assertEquals(null, $literal->getDatatype());
    }

    public function testCreateWithDatatype()
    {
        $literal = EasyRdf_Literal::create(1, null, 'xsd:integer');
        $this->assertEquals('EasyRdf_Literal_Integer', get_class($literal));
        $this->assertEquals(1, $literal->getValue());
        $this->assertEquals(null, $literal->getLang());
        $this->assertEquals('xsd:integer', $literal->getDatatype());
    }

    public function testCreateWithLanguageAndDatatype()
    {
        $literal = EasyRdf_Literal::create('Rat', 'en', 'http://www.w3.org/2001/XMLSchema#string');
        $this->assertClass('EasyRdf_Literal', $literal);
        $this->assertEquals('Rat', $literal->getValue());
        $this->assertEquals(null, $literal->getLang());
        $this->assertEquals('xsd:string', $literal->getDatatype());
    }

    public function testCreateIntegerLiteralWithLanguage()
    {
        $literal = EasyRdf_Literal::create(10, 'en');
        $this->assertClass('EasyRdf_Literal', $literal);
        $this->assertEquals(10, $literal->getValue());
        $this->assertEquals('en', $literal->getLang());
        $this->assertEquals(null, $literal->getDatatype());
    }

    public function testCreateWithUriDatatype()
    {
        $literal = EasyRdf_Literal::create(
            1, null, 'http://www.w3.org/2001/XMLSchema#integer'
        );
        $this->assertClass('EasyRdf_Literal_Integer', $literal);
        $this->assertEquals(1, $literal->getValue());
        $this->assertEquals(null, $literal->getLang());
        $this->assertEquals('xsd:integer', $literal->getDatatype());
    }

    public function testCreateWithUnshortenableUriDatatype()
    {
        $literal = EasyRdf_Literal::create(
            1, null, 'http://example.com/integer'
        );
        $this->assertClass('EasyRdf_Literal', $literal);
        $this->assertEquals('1', $literal->getValue());
        $this->assertEquals(null, $literal->getLang());
        $this->assertEquals(null, $literal->getDatatype());
    }

    public function testCreateWithAssociativeArray()
    {
        $literal = EasyRdf_Literal::create(array('value' => 'Rat'));
        $this->assertClass('EasyRdf_Literal', $literal);
        $this->assertEquals('Rat', $literal->getValue());
        $this->assertEquals(null, $literal->getLang());
        $this->assertEquals(null, $literal->getDatatype());
    }

    public function testCreateWithAssociativeArrayWithLang()
    {
        $literal = EasyRdf_Literal::create(array( 'value' => 'Rat', 'lang' => 'en'));
        $this->assertClass('EasyRdf_Literal', $literal);
        $this->assertEquals('Rat', $literal->getValue());
        $this->assertEquals(null, $literal->getDatatype());
        $this->assertEquals('en', $literal->getLang());
    }

    public function testCreateWithAssociativeArrayWithDatatype()
    {
        $literal = EasyRdf_Literal::create(array('value' => 'Rat','datatype' => 'xsd:string'));
        $this->assertClass('EasyRdf_Literal', $literal);
        $this->assertEquals('Rat', $literal->getValue());
        $this->assertEquals('xsd:string', $literal->getDatatype());
        $this->assertEquals(null, $literal->getLang());
    }

    public function testCreateWithInteger()
    {
        $literal = EasyRdf_Literal::create(10);
        $this->assertClass('EasyRdf_Literal_Integer', $literal);
        $this->assertEquals(10, $literal->getValue());
        $this->assertEquals('xsd:integer', $literal->getDatatype());
        $this->assertEquals(null, $literal->getLang());
    }

    public function testCreateWithFloat()
    {
        $literal = EasyRdf_Literal::create(1.5);
        $this->assertClass('EasyRdf_Literal_Decimal', $literal);
        $this->assertEquals(1.5, $literal->getValue());
        $this->assertEquals('xsd:decimal', $literal->getDatatype());
        $this->assertEquals(null, $literal->getLang());
    }

    public function testCreateWithBooleanTrue()
    {
        $literal = EasyRdf_Literal::create(true);
        $this->assertClass('EasyRdf_Literal_Boolean', $literal);
        $this->assertEquals(true, $literal->getValue());
        $this->assertEquals('xsd:boolean', $literal->getDatatype());
        $this->assertEquals(null, $literal->getLang());
    }

    public function testCreateWithBooleanFalse()
    {
        $literal = EasyRdf_Literal::create(false);
        $this->assertClass('EasyRdf_Literal_Boolean', $literal);
        $this->assertEquals(false, $literal->getValue());
        $this->assertEquals('xsd:boolean', $literal->getDatatype());
        $this->assertEquals(null, $literal->getLang());
    }

    public function testCreateConvertToBooleanTrue()
    {
        $literal = EasyRdf_Literal::create(1, null, 'xsd:boolean');
        $this->assertClass('EasyRdf_Literal_Boolean', $literal);
        $this->assertInternalType('bool', $literal->getValue());
        $this->assertEquals(true, $literal->getValue());
        $this->assertEquals('xsd:boolean', $literal->getDatatype());
        $this->assertEquals(null, $literal->getLang());
    }

    public function testCreateConvertToBooleanFalse()
    {
        $literal = EasyRdf_Literal::create(0, null, 'xsd:boolean');
        $this->assertClass('EasyRdf_Literal_Boolean', $literal);
        $this->assertInternalType('bool', $literal->getValue());
        $this->assertEquals(false, $literal->getValue());
        $this->assertEquals('xsd:boolean', $literal->getDatatype());
        $this->assertEquals(null, $literal->getLang());
    }

    public function testCreateConvertToInteger()
    {
        $literal = EasyRdf_Literal::create('100.00', null, 'xsd:integer');
        $this->assertClass('EasyRdf_Literal_Integer', $literal);
        $this->assertInternalType('integer', $literal->getValue());
        $this->assertEquals(100, $literal->getValue());
        $this->assertEquals('xsd:integer', $literal->getDatatype());
        $this->assertEquals(null, $literal->getLang());
    }

    public function testCreateConvertToDecimal()
    {
        $literal = EasyRdf_Literal::create('1', null, 'xsd:decimal');
        $this->assertClass('EasyRdf_Literal_Decimal', $literal);
        $this->assertInternalType('float', $literal->getValue());
        $this->assertEquals(1.0, $literal->getValue());
        $this->assertEquals('xsd:decimal', $literal->getDatatype());
        $this->assertEquals(null, $literal->getLang());
    }

    public function testCreateConvertToString()
    {
        $literal = EasyRdf_Literal::create(true, null, 'xsd:string');
        $this->assertClass('EasyRdf_Literal', $literal);
        $this->assertInternalType('string', $literal->getValue());
        # Hmm, not sure about this, but PHP does the conversion not me:
        $this->assertEquals('1', $literal->getValue());
        $this->assertEquals('xsd:string', $literal->getDatatype());
        $this->assertEquals(null, $literal->getLang());
    }

    public function testConstruct()
    {
        $literal = new EasyRdf_Literal('Rat');
        $this->assertEquals('Rat', $literal->getValue());
        $this->assertEquals(null, $literal->getLang());
        $this->assertEquals(null, $literal->getDatatype());
    }

    public function testGetDatatypeUri()
    {
        $literal = EasyRdf_Literal::create(1);
        $this->assertEquals(
            'http://www.w3.org/2001/XMLSchema#integer',
            $literal->getDatatypeUri()
        );
    }

    public function testToString()
    {
        $literal = EasyRdf_Literal::create('Rat');
        $this->assertEquals('Rat', strval($literal));
    }

    public function testToArray()
    {
        $literal = EasyRdf_Literal::create('Rat');
        $this->assertEquals(
            array(
               'type' => 'literal',
               'value' => 'Rat'
            ), $literal->toArray()
        );
    }

    public function testToArrayWithLang()
    {
        $literal = new EasyRdf_Literal('Chat', 'fr');
        $this->assertEquals(
            array(
               'type' => 'literal',
               'value' => 'Chat',
               'lang' => 'fr'
            ), $literal->toArray()
        );
    }

    public function testToArrayWithDatatype()
    {
        $literal = EasyRdf_Literal::create(44);
        $this->assertEquals(
            array(
               'type' => 'literal',
               'value' => '44',
               'datatype' => 'http://www.w3.org/2001/XMLSchema#integer'
            ), $literal->toArray()
        );
    }

    public function testDumpValue()
    {
        $literal = EasyRdf_Literal::create("hello & world");
        $this->assertEquals(
            '"hello & world"',
            $literal->dumpValue(false)
        );
        $this->assertEquals(
            "<span style='color:black'>&quot;hello &amp; world&quot;</span>",
            $literal->dumpValue(true)
        );
    }

    public function testDumpValueWithLanguage()
    {
        $literal = new EasyRdf_Literal('Nick', 'en');
        $this->assertEquals(
            '"Nick"@en',
            $literal->dumpValue(false)
        );
        $this->assertEquals(
            "<span style='color:black'>&quot;Nick&quot;@en</span>",
            $literal->dumpValue(true)
        );
    }

    public function testDumpValueWithDatatype()
    {
        $literal = EasyRdf_Literal::create(1, null, 'xsd:integer');
        $this->assertEquals(
            '"1"^^xsd:integer',
            $literal->dumpValue(false)
        );
        $this->assertEquals(
            "<span style='color:black'>&quot;1&quot;^^xsd:integer</span>",
            $literal->dumpValue(true)
        );
    }

    public function testConstructCustomClass()
    {
        EasyRdf_Literal::setDatatypeMapping('ex:mytype', 'MyDatatype_Class');
        $literal = new MyDatatype_Class('foobar');
        $this->assertClass('MyDatatype_Class', $literal);
        $this->assertStringEquals('!foobar!', $literal);
        $this->assertEquals('foobar', $literal->getValue());
        $this->assertEquals('ex:mytype', $literal->getDatatype());
        $this->assertEquals(null, $literal->getLang());
    }

    public function testCreateCustomClass()
    {
        EasyRdf_Literal::setDatatypeMapping('ex:mytype', 'MyDatatype_Class');
        $literal = EasyRdf_Literal::create('foobar', null, 'ex:mytype');
        $this->assertClass('MyDatatype_Class', $literal);
        $this->assertStringEquals('!foobar!', $literal);
        $this->assertEquals('foobar', $literal->getValue());
        $this->assertEquals('ex:mytype', $literal->getDatatype());
        $this->assertEquals(null, $literal->getLang());
    }

    public function testSetDatatypeMappingNull()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$datatype should be a string and cannot be null or empty'
        );
        EasyRdf_Literal::setDatatypeMapping(null, 'MyDatatype_Class');
    }

    public function testSetDatatypeMappingEmpty()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$datatype should be a string and cannot be null or empty'
        );
        EasyRdf_Literal::setDatatypeMapping('', 'MyDatatype_Class');
    }

    public function testSetDatatypeMappingNonString()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$datatype should be a string and cannot be null or empty'
        );
        EasyRdf_Literal::setDatatypeMapping(array(), 'MyDatatype_Class');
    }

    public function testSetDatatypeMappingClassNull()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$class should be a string and cannot be null or empty'
        );
        EasyRdf_Literal::setDatatypeMapping('ex:mytype', null);
    }

    public function testSetDatatypeMappingClassEmpty()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$class should be a string and cannot be null or empty'
        );
        EasyRdf_Literal::setDatatypeMapping('ex:mytype', '');
    }

    public function testSetDatatypeMappingClassNonString()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$class should be a string and cannot be null or empty'
        );
        EasyRdf_Literal::setDatatypeMapping('ex:mytype', array());
    }

    public function testDeleteDatatypeMappingNull()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$datatype should be a string and cannot be null or empty'
        );
        EasyRdf_Literal::deleteDatatypeMapping(null);
    }

    public function testDeleteDatatypeMappingEmpty()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$datatype should be a string and cannot be null or empty'
        );
        EasyRdf_Literal::deleteDatatypeMapping('');
    }

    public function testDeleteDatatypeMappingNonString()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$datatype should be a string and cannot be null or empty'
        );
        EasyRdf_Literal::deleteDatatypeMapping(array());
    }
}
