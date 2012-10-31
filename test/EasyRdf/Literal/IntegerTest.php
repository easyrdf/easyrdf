<?php

require_once realpath(dirname(__FILE__) . '/../../') . '/TestHelper.php';


class EasyRdf_Literal_IntegerTest extends EasyRdf_TestCase
{
    public function testConstruct()
    {
        $literal = new EasyRdf_Literal_Integer(1);
        $this->assertClass('EasyRdf_Literal_Integer', $literal);
        $this->assertStringEquals('1', $literal);
        $this->assertInternalType('int', $literal->getValue());
        $this->assertSame(1, $literal->getValue());
        $this->assertSame(NULL, $literal->getLang());
        $this->assertSame('xsd:integer', $literal->getDatatype());
    }

    public function testConstructCast()
    {
        $literal = new EasyRdf_Literal_Integer('100');
        $this->assertClass('EasyRdf_Literal_Integer', $literal);
        $this->assertStringEquals('100', $literal);
        $this->assertInternalType('int', $literal->getValue());
        $this->assertSame(100, $literal->getValue());
        $this->assertSame(NULL, $literal->getLang());
        $this->assertSame('xsd:integer', $literal->getDatatype());
    }
}
