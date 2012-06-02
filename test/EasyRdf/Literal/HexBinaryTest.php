<?php

require_once realpath(dirname(__FILE__) . '/../../') . '/TestHelper.php';


class EasyRdf_Literal_HexBinaryTest extends EasyRdf_TestCase
{
    public function testConstructHello()
    {
        $literal = new EasyRdf_Literal_HexBinary('Hello');
        $this->assertStringEquals('48656C6C6F', $literal);
        $this->assertType('string', $literal->getValue());
        $this->assertEquals('Hello', $literal->getValue());
        $this->assertEquals(null, $literal->getLang());
        $this->assertEquals('xsd:hexBinary', $literal->getDatatype());
    }

    public function testConstructXml()
    {
        $literal = new EasyRdf_Literal_HexBinary(
            '<?xml version="1.0" encoding="UTF-8"?>'
        );
        $this->assertStringEquals(
            '3C3F786D6C2076657273696F6E3D22312E302220656E636F64696E673D225554462D38223F3E',
            $literal
        );
    }

    public function testFromHex()
    {
        $literal = EasyRdf_Literal_HexBinary::fromHex('48656C6C6F');
        $this->assertStringEquals('48656C6C6F', $literal);
        $this->assertType('string', $literal->getValue());
        $this->assertEquals('Hello', $literal->getValue());
        $this->assertEquals(null, $literal->getLang());
        $this->assertEquals('xsd:hexBinary', $literal->getDatatype());
    }

    public function testToArray()
    {
        $literal = new EasyRdf_Literal_HexBinary('Hello');
        $this->assertEquals(
            array(
                'type' => 'literal',
                'value' => '48656C6C6F',
                'datatype' => 'http://www.w3.org/2001/XMLSchema#hexBinary'
            ),
            $literal->toArray()
        );
    }

    public function testDumpValueWithDatatype()
    {
        $literal = new EasyRdf_Literal_HexBinary('Hello');
        $this->assertEquals(
            '"48656C6C6F"^^xsd:hexBinary',
            $literal->dumpValue(false)
        );
    }

}
