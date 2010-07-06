<?php

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009-2010 Nicholas J Humfrey.  All rights reserved.
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
 * @copyright  Copyright (c) 2009-2010 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 * @version    $Id$
 */

require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'TestHelper.php';

class EasyRdf_LiteralTest extends EasyRdf_TestCase
{
    public function testConstruct()
    {
        $literal = new EasyRdf_Literal('Rat');
        $this->assertEquals('Rat', $literal->getValue());
        $this->assertEquals(null, $literal->getLang());
        $this->assertEquals(null, $literal->getDatatype());
    }

    public function testConstructWithLanguage()
    {
        $literal = new EasyRdf_Literal('Rat', 'en');
        $this->assertEquals('Rat', $literal->getValue());
        $this->assertEquals('en', $literal->getLang());
        $this->assertEquals(null, $literal->getDatatype());
    }

    public function testConstructWithDatatype()
    {
        $literal = new EasyRdf_Literal(1, null, 'http://foo.com/');
        $this->assertEquals(1, $literal->getValue());
        $this->assertEquals(null, $literal->getLang());
        $this->assertEquals('http://foo.com/', $literal->getDatatype());
    }

    public function testConstructWithAssociativeArray()
    {
        $literal = new EasyRdf_Literal(array('value' => 'Rat'));
        $this->assertEquals('Rat', $literal->getValue());
        $this->assertEquals(null, $literal->getLang());
        $this->assertEquals(null, $literal->getDatatype());
    }

    public function testConstructWithAssociativeArrayWithLang()
    {
        $literal = new EasyRdf_Literal(array(
            'value' => 'Rat',
            'lang' => 'en'
        ));
        $this->assertEquals('Rat', $literal->getValue());
        $this->assertEquals(null, $literal->getDatatype());
        $this->assertEquals('en', $literal->getLang());
    }

    public function testConstructWithAssociativeArrayWithDatatype()
    {
        $literal = new EasyRdf_Literal(array(
            'value' => 'Rat',
            'datatype' => 'http://example.com/'
        ));
        $this->assertEquals('Rat', $literal->getValue());
        $this->assertEquals('http://example.com/', $literal->getDatatype());
        $this->assertEquals(null, $literal->getLang());
    }

    public function testToString()
    {
        $literal = new EasyRdf_Literal('Rat');
        $this->assertStringEquals('Rat', $literal);
    }
}
