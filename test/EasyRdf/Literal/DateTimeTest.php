<?php

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2011-2013 Nicholas J Humfrey.  All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 * 3. The name of the author 'Nicholas J Humfrey" may be used to endorse or
 *    promote products derived from this software without specific prior
 *    written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009-2013 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */

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
        $now = strtotime('now');
        $literal = new EasyRdf_Literal_DateTime();
        $check = strtotime(strval($literal));
        $this->assertLessThan(2, $check-$now);
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
