<?php

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
        $this->assertEquals('48656C6C6F', $literal->getValue());
        $this->assertEquals(null, $literal->getLang());
        $this->assertEquals('xsd:hexBinary', $literal->getDatatype());
        $this->assertEquals('Hello', $literal->toBinary());
    }

    public function testConstructLowercase()
    {
        $literal = new EasyRdf_Literal_HexBinary('48656c6C6f');
        $this->assertEquals('48656C6C6F', $literal->getValue());
        $this->assertStringEquals('48656C6C6F', $literal);
        $this->assertEquals('Hello', $literal->toBinary());
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
        $this->assertEquals('xsd:hexBinary', $literal->getDatatype());
        $this->assertStringEquals(
            '3C3F786D6C2076657273696F6E3D22312E302220656E636F64696E673D225554462D38223F3E',
            $literal
        );
    }

    public function testToArray()
    {
        $literal = new EasyRdf_Literal_HexBinary('48656C6C6F');
        $this->assertEquals(
            array(
                'type' => 'literal',
                'value' => '48656C6C6F',
                'datatype' => 'http://www.w3.org/2001/XMLSchema#hexBinary'
            ),
            $literal->toArray()
        );
    }

    public function testDumpValue()
    {
        $literal = new EasyRdf_Literal_HexBinary('48656C6C6F');
        $this->assertEquals(
            '"48656C6C6F"^^xsd:hexBinary',
            $literal->dumpValue(false)
        );
    }

    public function testParseWebId()
    {
        $graph = new EasyRdf_Graph();
        $graph->parseFile( fixturePath('webid.ttl'), 'turtle' );
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
        $this->assertEquals(null, $modulus->getLang());
        $this->assertEquals('xsd:hexBinary', $modulus->getDatatype());
    }

}
