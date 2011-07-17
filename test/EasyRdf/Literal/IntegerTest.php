<?php

require_once realpath(dirname(__FILE__) . '/../../') . '/TestHelper.php';


class EasyRdf_Literal_IntegerTest extends EasyRdf_TestCase
{
    public function testConstruct()
    {
        $literal = new EasyRdf_Literal_Integer(1);
        $this->assertType('EasyRdf_Literal_Integer', $literal);
        $this->assertStringEquals('1', $literal);
        $this->assertType('int', $literal->getValue());
        $this->assertEquals(1, $literal->getValue());
        $this->assertEquals(null, $literal->getLang());
        $this->assertEquals('xsd:integer', $literal->getDatatype());
    }

    public function testConstructCast()
    {
        $literal = new EasyRdf_Literal_Integer('100');
        $this->assertType('EasyRdf_Literal_Integer', $literal);
        $this->assertStringEquals('100', $literal);
        $this->assertType('int', $literal->getValue());
        $this->assertEquals(100, $literal->getValue());
        $this->assertEquals(null, $literal->getLang());
        $this->assertEquals('xsd:integer', $literal->getDatatype());
    }
}
