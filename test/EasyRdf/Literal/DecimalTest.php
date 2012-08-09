<?php

require_once realpath(dirname(__FILE__) . '/../../') . '/TestHelper.php';


class EasyRdf_Literal_DecimalTest extends EasyRdf_TestCase
{
    public function testConstruct()
    {
        $literal = new EasyRdf_Literal_Decimal(1.5);
        $this->assertInstanceOf('EasyRdf_Literal_Decimal', $literal);
        $this->assertStringEquals('1.5', $literal);
        $this->assertInternalType('float', $literal->getValue());
        $this->assertEquals(1.5, $literal->getValue());
        $this->assertEquals(null, $literal->getLang());
        $this->assertEquals('xsd:decimal', $literal->getDatatype());
    }

    public function testConstructCast()
    {
        $literal = new EasyRdf_Literal_Decimal('100.00');
        $this->assertInstanceOf('EasyRdf_Literal_Decimal', $literal);
        $this->assertStringEquals('100.00', $literal);
        $this->assertInternalType('float', $literal->getValue());
        $this->assertEquals(100.0, $literal->getValue());
        $this->assertEquals(null, $literal->getLang());
        $this->assertEquals('xsd:decimal', $literal->getDatatype());
    }
}
