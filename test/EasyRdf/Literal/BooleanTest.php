<?php

require_once realpath(dirname(__FILE__) . '/../../') . '/TestHelper.php';


class EasyRdf_Literal_BooleanTest extends EasyRdf_TestCase
{
    public function testConstructTrue()
    {
        $literal = new EasyRdf_Literal_Boolean(true);
        $this->assertStringEquals('true', $literal);
        $this->assertType('bool', $literal->getValue());
        $this->assertEquals(true, $literal->getValue());
        $this->assertEquals(null, $literal->getLang());
        $this->assertEquals('xsd:boolean', $literal->getDatatype());
        $this->assertEquals(true, $literal->isTrue());
        $this->assertEquals(false, $literal->isFalse());
    }

    public function testConstructFalse()
    {
        $literal = new EasyRdf_Literal_Boolean(false);
        $this->assertStringEquals('false', $literal);
        $this->assertType('bool', $literal->getValue());
        $this->assertEquals(false, $literal->getValue());
        $this->assertEquals(null, $literal->getLang());
        $this->assertEquals('xsd:boolean', $literal->getDatatype());
        $this->assertEquals(false, $literal->isTrue());
        $this->assertEquals(true, $literal->isFalse());
    }

    public function testConstructCast1()
    {
        $literal = new EasyRdf_Literal_Boolean(1);
        $this->assertStringEquals('true', $literal);
        $this->assertType('bool', $literal->getValue());
        $this->assertEquals(true, $literal->getValue());
        $this->assertEquals(null, $literal->getLang());
        $this->assertEquals('xsd:boolean', $literal->getDatatype());
        $this->assertEquals(true, $literal->isTrue());
        $this->assertEquals(false, $literal->isFalse());
    }

    public function testConstructCast0()
    {
        $literal = new EasyRdf_Literal_Boolean(0);
        $this->assertStringEquals('false', $literal);
        $this->assertType('bool', $literal->getValue());
        $this->assertEquals(false, $literal->getValue());
        $this->assertEquals(null, $literal->getLang());
        $this->assertEquals('xsd:boolean', $literal->getDatatype());
        $this->assertEquals(false, $literal->isTrue());
        $this->assertEquals(true, $literal->isFalse());
    }
}
