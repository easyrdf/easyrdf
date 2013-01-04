<?php

require_once realpath(dirname(__FILE__) . '/../../') . '/TestHelper.php';


class EasyRdf_Literal_DecimalTest extends EasyRdf_TestCase
{
    public function testConstruct15()
    {
        $literal = new EasyRdf_Literal_Decimal(1.5);
        $this->assertClass('EasyRdf_Literal_Decimal', $literal);
        $this->assertStringEquals('1.5', $literal);
        $this->assertInternalType('float', $literal->getValue());
        $this->assertSame(1.5, $literal->getValue());
        $this->assertSame(null, $literal->getLang());
        $this->assertSame('xsd:decimal', $literal->getDatatype());
    }

    public function testConstructString100()
    {
        $literal = new EasyRdf_Literal_Decimal('100.00');
        $this->assertClass('EasyRdf_Literal_Decimal', $literal);
        $this->assertStringEquals('100.00', $literal);
        $this->assertInternalType('float', $literal->getValue());
        $this->assertSame(100.0, $literal->getValue());
        $this->assertSame(null, $literal->getLang());
        $this->assertSame('xsd:decimal', $literal->getDatatype());
    }
}
