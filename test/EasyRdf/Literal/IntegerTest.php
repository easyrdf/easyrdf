<?php

require_once realpath(dirname(__FILE__) . '/../../') . '/TestHelper.php';


class EasyRdf_Literal_IntegerTest extends EasyRdf_TestCase
{
    public function testConstruct0()
    {
        $literal = new EasyRdf_Literal_Integer(0);
        $this->assertClass('EasyRdf_Literal_Integer', $literal);
        $this->assertStringEquals('0', $literal);
        $this->assertInternalType('int', $literal->getValue());
        $this->assertSame(0, $literal->getValue());
        $this->assertSame(NULL, $literal->getLang());
        $this->assertSame('xsd:integer', $literal->getDatatype());
    }

    public function testConstruct1()
    {
        $literal = new EasyRdf_Literal_Integer(1);
        $this->assertClass('EasyRdf_Literal_Integer', $literal);
        $this->assertStringEquals('1', $literal);
        $this->assertInternalType('int', $literal->getValue());
        $this->assertSame(1, $literal->getValue());
        $this->assertSame(NULL, $literal->getLang());
        $this->assertSame('xsd:integer', $literal->getDatatype());
    }

    public function testConstructString100()
    {
        $literal = new EasyRdf_Literal_Integer('100');
        $this->assertClass('EasyRdf_Literal_Integer', $literal);
        $this->assertStringEquals('100', $literal);
        $this->assertInternalType('int', $literal->getValue());
        $this->assertSame(100, $literal->getValue());
        $this->assertSame(NULL, $literal->getLang());
        $this->assertSame('xsd:integer', $literal->getDatatype());
    }

    public function testConstructString0100()
    {
        $literal = new EasyRdf_Literal_Integer('0100');
        $this->assertClass('EasyRdf_Literal_Integer', $literal);
        $this->assertStringEquals('0100', $literal);
        $this->assertInternalType('int', $literal->getValue());
        $this->assertSame(100, $literal->getValue());
        $this->assertSame(NULL, $literal->getLang());
        $this->assertSame('xsd:integer', $literal->getDatatype());
    }
}
