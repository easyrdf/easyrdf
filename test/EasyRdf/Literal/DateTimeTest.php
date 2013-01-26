<?php

require_once realpath(dirname(__FILE__) . '/../../') . '/TestHelper.php';


class EasyRdf_Literal_DateTimeTest extends EasyRdf_TestCase
{

    public function testConstruct()
    {
        $literal = new EasyRdf_Literal_DateTime('2011-07-18T18:45:43Z');
        $this->assertStringEquals('2011-07-18T18:45:43Z', $literal);
        $this->assertClass('DateTime', $literal->getValue());
        $this->assertSame(null, $literal->getLang());
        $this->assertSame('xsd:dateTime', $literal->getDatatype());
    }

    public function testConstructNoValue()
    {
        $now = new DateTime('now');
        $literal = new EasyRdf_Literal_DateTime();
        $diff = $now->diff($literal->getValue());
        $one = new DateInterval('PT1S');
        $this->assertTrue($diff <= $one);
    }

    public function testConstructFromDateTimeBST()
    {
        $dt = new DateTime('2010-09-08T07:06:05+0100');
        $literal = new EasyRdf_Literal_DateTime($dt);
        $this->assertStringEquals('2010-09-08T07:06:05+01:00', $literal);
        $this->assertClass('DateTime', $literal->getValue());
        $this->assertEquals($dt, $literal->getValue());
        $this->assertSame(null, $literal->getLang());
        $this->assertSame('xsd:dateTime', $literal->getDatatype());
    }

    public function testConstructFromDateTimeUTC()
    {
        $dt = new DateTime('2010-09-08T07:06:05Z');
        $literal = new EasyRdf_Literal_DateTime($dt);
        $this->assertStringEquals('2010-09-08T07:06:05Z', $literal);
        $this->assertClass('DateTime', $literal->getValue());
        $this->assertEquals($dt, $literal->getValue());
        $this->assertSame(null, $literal->getLang());
        $this->assertSame('xsd:dateTime', $literal->getDatatype());
    }

    public function testParseUTC()
    {
        $literal = EasyRdf_Literal_DateTime::parse('Mon 18 Jul 2011 18:45:43 UTC');
        $this->assertStringEquals('2011-07-18T18:45:43Z', $literal);
        $this->assertClass('DateTime', $literal->getValue());
        $this->assertSame(null, $literal->getLang());
        $this->assertSame('xsd:dateTime', $literal->getDatatype());
    }

    public function testParseBST()
    {
        $literal = EasyRdf_Literal_DateTime::parse('Mon 18 Jul 2011 18:45:43 BST');
        $this->assertStringEquals('2011-07-18T18:45:43+01:00', $literal);
        $this->assertClass('DateTime', $literal->getValue());
        $this->assertSame(null, $literal->getLang());
        $this->assertSame('xsd:dateTime', $literal->getDatatype());
    }



    public function setUp()
    {
        $this->dt = new EasyRdf_Literal_DateTime('2010-09-08T07:06:05Z');
    }

    public function testFormat()
    {
        $this->assertSame(
            'Wed, 08 Sep 10 07:06:05 +0000',
            $this->dt->format(DateTime::RFC822)
        );
    }

    public function testYear()
    {
        $this->assertSame(2010, $this->dt->year());
    }

    public function testMonth()
    {
        $this->assertSame(9, $this->dt->month());
    }

    public function testDay()
    {
        $this->assertSame(8, $this->dt->day());
    }

    public function testHour()
    {
        $this->assertSame(7, $this->dt->hour());
    }

    public function testMin()
    {
        $this->assertSame(6, $this->dt->min());
    }

    public function testSec()
    {
        $this->assertSame(5, $this->dt->sec());
    }

    public function testToString()
    {
        $this->assertStringEquals(
            '2010-09-08T07:06:05Z',
            $this->dt
        );
    }
}
