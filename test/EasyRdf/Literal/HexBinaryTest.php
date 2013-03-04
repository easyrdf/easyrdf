<?php

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2012-2013 Nicholas J Humfrey.  All rights reserved.
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

require_once realpath(dirname(__FILE__) . '/../../') . '/TestHelper.php';


class EasyRdf_Literal_HexBinaryTest extends EasyRdf_TestCase
{
    public function setup()
    {
        // Reset to built-in parsers
        EasyRdf_Format::registerParser('ntriples', 'EasyRdf_Parser_Ntriples');
        EasyRdf_Format::registerParser('rdfxml', 'EasyRdf_Parser_RdfXml');
        EasyRdf_Format::registerParser('turtle', 'EasyRdf_Parser_Turtle');
    }

    public function testConstruct()
    {
        $literal = new EasyRdf_Literal_HexBinary('48656C6C6F');
        $this->assertStringEquals('48656C6C6F', $literal);
        $this->assertInternalType('string', $literal->getValue());
        $this->assertSame('48656C6C6F', $literal->getValue());
        $this->assertSame(null, $literal->getLang());
        $this->assertSame('xsd:hexBinary', $literal->getDatatype());
        $this->assertSame('Hello', $literal->toBinary());
    }

    public function testConstructLowercase()
    {
        $literal = new EasyRdf_Literal_HexBinary('48656c6C6f');
        $this->assertSame('48656C6C6F', $literal->getValue());
        $this->assertStringEquals('48656C6C6F', $literal);
        $this->assertSame('Hello', $literal->toBinary());
    }

    public function testContructInvalid()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Literal of type xsd:hexBinary contains non-hexadecimal characters'
        );
        $literal = new EasyRdf_Literal_HexBinary('48FZ');
    }

    public function testFromBinary()
    {
        $literal = EasyRdf_Literal_HexBinary::fromBinary(
            '<?xml version="1.0" encoding="UTF-8"?>'
        );
        $this->assertSame('xsd:hexBinary', $literal->getDatatype());
        $this->assertStringEquals(
            '3C3F786D6C2076657273696F6E3D22312E302220656E636F64696E673D225554462D38223F3E',
            $literal
        );
    }

    public function testToRdfPhp()
    {
        $literal = new EasyRdf_Literal_HexBinary('48656C6C6F');
        $this->assertSame(
            array(
                'type' => 'literal',
                'value' => '48656C6C6F',
                'datatype' => 'http://www.w3.org/2001/XMLSchema#hexBinary'
            ),
            $literal->toRdfPhp()
        );
    }

    public function testDumpValue()
    {
        $literal = new EasyRdf_Literal_HexBinary('48656C6C6F');
        $this->assertSame(
            '"48656C6C6F"^^xsd:hexBinary',
            $literal->dumpValue('text')
        );
    }

    public function testParseWebId()
    {
        $graph = new EasyRdf_Graph();
        $graph->parseFile(fixturePath('webid.ttl'), 'turtle');
        $me = $graph->resource('http://www.example.com/myfoaf#me');
        $modulus = $me->get('cert:key')->get('cert:modulus');
        $this->assertStringEquals(
            'CB24ED85D64D794B69C701C186ACC059501E856000F661C93204D8380E07191C'.
            '5C8B368D2AC32A428ACB970398664368DC2A867320220F755E99CA2EECDAE62E'.
            '8D15FB58E1B76AE59CB7ACE8838394D59E7250B449176E51A494951A1C366C62'.
            '17D8768D682DDE78DD4D55E613F8839CF275D4C8403743E7862601F3C49A6366'.
            'E12BB8F498262C3C77DE19BCE40B32F89AE62C3780F5B6275BE337E2B3153AE2'.
            'BA72A9975AE71AB724649497066B660FCF774B7543D980952D2E8586200EDA41'.
            '58B014E75465D91ECF93EFC7AC170C11FC7246FC6DED79C37780000AC4E079F6'.
            '71FD4F207AD770809E0E2D7B0EF5493BEFE73544D8E1BE3DDDB52455C61391A1',
            $modulus
        );
        $this->assertInternalType('string', $modulus->getValue());
        $this->assertSame(null, $modulus->getLang());
        $this->assertSame('xsd:hexBinary', $modulus->getDatatype());
    }
}
