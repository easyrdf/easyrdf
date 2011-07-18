<?php

require_once realpath(dirname(__FILE__) . '/../../') . '/TestHelper.php';


class EasyRdf_Literal_DateTimeTest extends EasyRdf_TestCase
{
    public function testConstructFromString()
    {
        $literal = new EasyRdf_Literal_DateTime('Mon 18 Jul 2011 18:45:43 BST');
        $this->assertStringEquals('2011-07-18T18:45:43+0100', $literal);
        $this->assertClass('DateTime', $literal->getValue());
        $this->assertEquals(null, $literal->getLang());
        $this->assertEquals('xsd:dateTime', $literal->getDatatype());
    }

    public function testConstructFromDateTime()
    {
        $dt = new DateTime('Mon 18 Jul 2011 18:45:43 BST');
        $literal = new EasyRdf_Literal_DateTime($dt);
        $this->assertStringEquals('2011-07-18T18:45:43+0100', $literal);
        $this->assertClass('DateTime', $literal->getValue());
        $this->assertEquals($dt, $literal->getValue());
        $this->assertEquals(null, $literal->getLang());
        $this->assertEquals('xsd:dateTime', $literal->getDatatype());
    }



    public function setUp()
    {
        $this->_dt = new EasyRdf_Literal_DateTime('2010-09-08T07:06:05Z');
    }

    public function testFormat()
    {
        $this->assertEquals(
            'Wed, 08 Sep 10 07:06:05 +0000',
            $this->_dt->format(DateTime::RFC822)
        );
    }

    public function testYear()
    {
        $this->assertEquals(2010, $this->_dt->year());
    }

    public function testMonth()
    {
        $this->assertEquals(9, $this->_dt->month());
    }

    public function testDay()
    {
        $this->assertEquals(8, $this->_dt->day());
    }

    public function testHour()
    {
        $this->assertEquals(7, $this->_dt->hour());
    }

    public function testMin()
    {
        $this->assertEquals(6, $this->_dt->min());
    }

    public function testSec()
    {
        $this->assertEquals(5, $this->_dt->sec());
    }

}
