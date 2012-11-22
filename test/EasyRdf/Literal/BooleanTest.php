<?php

require_once realpath(dirname(__FILE__) . '/../../') . '/TestHelper.php';


class EasyRdf_Literal_BooleanTest extends EasyRdf_TestCase
{
    public function testConstructStringTrue()
    {
        $literal = new EasyRdf_Literal_Boolean('true');
        $this->assertStringEquals('true', $literal);
        $this->assertInternalType('bool', $literal->getValue());
        $this->assertSame(true, $literal->getValue());
        $this->assertSame(NULL, $literal->getLang());
        $this->assertSame('xsd:boolean', $literal->getDatatype());
    }

    public function testConstructStringFalse()
    {
        $literal = new EasyRdf_Literal_Boolean('false');
        $this->assertStringEquals('false', $literal);
        $this->assertInternalType('bool', $literal->getValue());
        $this->assertSame(false, $literal->getValue());
        $this->assertSame(NULL, $literal->getLang());
        $this->assertSame('xsd:boolean', $literal->getDatatype());
    }

    public function testConstructString1()
    {
        $literal = new EasyRdf_Literal_Boolean('1');
        $this->assertStringEquals('1', $literal);
        $this->assertInternalType('bool', $literal->getValue());
        $this->assertSame(true, $literal->getValue());
        $this->assertSame(NULL, $literal->getLang());
        $this->assertSame('xsd:boolean', $literal->getDatatype());
    }

    public function testConstructString0()
    {
        $literal = new EasyRdf_Literal_Boolean('0');
        $this->assertStringEquals('0', $literal);
        $this->assertInternalType('bool', $literal->getValue());
        $this->assertSame(false, $literal->getValue());
        $this->assertSame(NULL, $literal->getLang());
        $this->assertSame('xsd:boolean', $literal->getDatatype());
    }

    public function testConstructTrue()
    {
        $literal = new EasyRdf_Literal_Boolean(true);
        $this->assertStringEquals('true', $literal);
        $this->assertInternalType('bool', $literal->getValue());
        $this->assertSame(true, $literal->getValue());
        $this->assertSame(NULL, $literal->getLang());
        $this->assertSame('xsd:boolean', $literal->getDatatype());
    }

    public function testConstructFalse()
    {
        $literal = new EasyRdf_Literal_Boolean(false);
        $this->assertStringEquals('false', $literal);
        $this->assertInternalType('bool', $literal->getValue());
        $this->assertSame(false, $literal->getValue());
        $this->assertSame(NULL, $literal->getLang());
        $this->assertSame('xsd:boolean', $literal->getDatatype());
    }

    public function testConstruct1()
    {
        $literal = new EasyRdf_Literal_Boolean(1);
        $this->assertStringEquals('true', $literal);
        $this->assertInternalType('bool', $literal->getValue());
        $this->assertSame(true, $literal->getValue());
        $this->assertSame(NULL, $literal->getLang());
        $this->assertSame('xsd:boolean', $literal->getDatatype());
    }

    public function testConstruct0()
    {
        $literal = new EasyRdf_Literal_Boolean(0);
        $this->assertStringEquals('false', $literal);
        $this->assertInternalType('bool', $literal->getValue());
        $this->assertSame(false, $literal->getValue());
        $this->assertSame(NULL, $literal->getLang());
        $this->assertSame('xsd:boolean', $literal->getDatatype());
    }

    public function testIsTrue()
    {
        $true = new EasyRdf_Literal_Boolean(true);
        $this->assertSame(true, $true->isTrue());

        $false = new EasyRdf_Literal_Boolean(false);
        $this->assertSame(false, $false->isTrue());
    }

    public function testIsFalse()
    {
        $false = new EasyRdf_Literal_Boolean(false);
        $this->assertSame(true, $false->isFalse());

        $true = new EasyRdf_Literal_Boolean(true);
        $this->assertSame(false, $true->isFalse());
    }
}
