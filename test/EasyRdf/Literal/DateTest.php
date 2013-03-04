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


class EasyRdf_Literal_DateTest extends EasyRdf_TestCase
{
    public function testConstructFromString()
    {
        $literal = new EasyRdf_Literal_Date('2011-08-05Z');
        $this->assertStringEquals('2011-08-05Z', $literal);
        $this->assertClass('DateTime', $literal->getValue());
        $this->assertSame(null, $literal->getLang());
        $this->assertSame('xsd:date', $literal->getDatatype());
    }

    public function testConstructFromDateTime()
    {
        $dt = new DateTime('2011-07-18');
        $literal = new EasyRdf_Literal_Date($dt);
        $this->assertStringEquals('2011-07-18', $literal);
        $this->assertClass('DateTime', $literal->getValue());
        $this->assertEquals($dt, $literal->getValue());
        $this->assertSame(null, $literal->getLang());
        $this->assertSame('xsd:date', $literal->getDatatype());
    }

    public function testConstructNoValue()
    {
        // Would be very unlucky if this ran at midnight and failed
        // (but it is possible)
        $today = new DateTime('today');
        $literal = new EasyRdf_Literal_Date();
        $this->assertEquals($today, $literal->getValue());
        $this->assertRegExp('|^\d{4}-\d{2}-\d{2}$|', strval($literal));
    }

    public function testParse()
    {
        $literal = EasyRdf_Literal_Date::parse('5th August 2011');
        $this->assertStringEquals('2011-08-05', $literal);
        $this->assertClass('DateTime', $literal->getValue());
        $this->assertSame(null, $literal->getLang());
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
