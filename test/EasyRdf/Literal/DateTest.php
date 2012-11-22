<?php

require_once realpath(dirname(__FILE__) . '/../../') . '/TestHelper.php';


class EasyRdf_Literal_DateTest extends EasyRdf_TestCase
{
    public function testConstructFromString()
    {
        $literal = new EasyRdf_Literal_Date('2011-08-05Z');
        $this->assertStringEquals('2011-08-05Z', $literal);
        $this->assertClass('DateTime', $literal->getValue());
        $this->assertSame(NULL, $literal->getLang());
        $this->assertSame('xsd:date', $literal->getDatatype());
    }

    public function testConstructFromNonXSDString()
    {
        $literal = new EasyRdf_Literal_Date('5th August 2011');
        $this->assertStringEquals('2011-08-05', $literal);
        $this->assertClass('DateTime', $literal->getValue());
        $this->assertSame(NULL, $literal->getLang());
        $this->assertSame('xsd:date', $literal->getDatatype());
    }

    public function testConstructFromDateTime()
    {
        $dt = new DateTime('2011-07-18');
        $literal = new EasyRdf_Literal_Date($dt);
        $this->assertStringEquals('2011-07-18', $literal);
        $this->assertClass('DateTime', $literal->getValue());
        $this->assertEquals($dt, $literal->getValue());
        $this->assertSame(NULL, $literal->getLang());
        $this->assertSame('xsd:date', $literal->getDatatype());
    }

    public function testFormat()
    {
        $literal = new EasyRdf_Literal_Date('2011-08-05');
        $this->assertSame('05 Aug 11', $literal->format('d M y'));
    }

    public function testYear()
    {
        $literal = new EasyRdf_Literal_Date('2011-08-05');
        $this->assertSame(2011, $literal->year());
    }

    public function testMonth()
    {
        $literal = new EasyRdf_Literal_Date('2011-08-05');
        $this->assertSame(8, $literal->month());
    }

    public function testDate()
    {
        $literal = new EasyRdf_Literal_Date('2011-08-05');
        $this->assertSame(5, $literal->day());
    }

}
