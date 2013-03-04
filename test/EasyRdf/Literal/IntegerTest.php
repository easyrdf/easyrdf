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


class EasyRdf_Literal_IntegerTest extends EasyRdf_TestCase
{
    public function testConstruct0()
    {
        $literal = new EasyRdf_Literal_Integer(0);
        $this->assertClass('EasyRdf_Literal_Integer', $literal);
        $this->assertStringEquals('0', $literal);
        $this->assertInternalType('int', $literal->getValue());
        $this->assertSame(0, $literal->getValue());
        $this->assertSame(null, $literal->getLang());
        $this->assertSame('xsd:integer', $literal->getDatatype());
    }

    public function testConstruct1()
    {
        $literal = new EasyRdf_Literal_Integer(1);
        $this->assertClass('EasyRdf_Literal_Integer', $literal);
        $this->assertStringEquals('1', $literal);
        $this->assertInternalType('int', $literal->getValue());
        $this->assertSame(1, $literal->getValue());
        $this->assertSame(null, $literal->getLang());
        $this->assertSame('xsd:integer', $literal->getDatatype());
    }

    public function testConstructString100()
    {
        $literal = new EasyRdf_Literal_Integer('100');
        $this->assertClass('EasyRdf_Literal_Integer', $literal);
        $this->assertStringEquals('100', $literal);
        $this->assertInternalType('int', $literal->getValue());
        $this->assertSame(100, $literal->getValue());
        $this->assertSame(null, $literal->getLang());
        $this->assertSame('xsd:integer', $literal->getDatatype());
    }

    public function testConstructString0100()
    {
        $literal = new EasyRdf_Literal_Integer('0100');
        $this->assertClass('EasyRdf_Literal_Integer', $literal);
        $this->assertStringEquals('0100', $literal);
        $this->assertInternalType('int', $literal->getValue());
        $this->assertSame(100, $literal->getValue());
        $this->assertSame(null, $literal->getLang());
        $this->assertSame('xsd:integer', $literal->getDatatype());
    }
}
