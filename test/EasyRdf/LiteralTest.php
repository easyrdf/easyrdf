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
        $literal = new EasyRdf_Literal(1, null, 'xsd:integer');
        $this->assertEquals(1, $literal->getValue());
        $this->assertEquals(null, $literal->getLang());
        $this->assertEquals('xsd:integer', $literal->getDatatype());
    }

    public function testConstructWithLanguageAndDatatype()
    {
        $literal = new EasyRdf_Literal('Rat', 'en', 'http://www.w3.org/2001/XMLSchema#string');
        $this->assertEquals('Rat', $literal->getValue());
        $this->assertEquals(null, $literal->getLang());
        $this->assertEquals('xsd:string', $literal->getDatatype());
    }

    public function testConstructIntegerLiteralWithLanguage()
    {
        $literal = new EasyRdf_Literal(10, 'en');
        $this->assertEquals(10, $literal->getValue());
        $this->assertEquals('en', $literal->getLang());
        $this->assertEquals(null, $literal->getDatatype());
    }

    public function testConstructWithUriDatatype()
    {
        $literal = new EasyRdf_Literal(
            1, null, 'http://www.w3.org/2001/XMLSchema#integer'
        );
        $this->assertEquals(1, $literal->getValue());
        $this->assertEquals(null, $literal->getLang());
        $this->assertEquals('xsd:integer', $literal->getDatatype());
    }

    public function testConstructWithUnshortenableUriDatatype()
    {
        $literal = new EasyRdf_Literal(
            1, null, 'http://example.com/integer'
        );
        $this->assertEquals('1', $literal->getValue());
        $this->assertEquals(null, $literal->getLang());
        $this->assertEquals(null, $literal->getDatatype());
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
            'datatype' => 'xsd:integer'
        ));
        $this->assertEquals('Rat', $literal->getValue());
        $this->assertEquals('xsd:integer', $literal->getDatatype());
        $this->assertEquals(null, $literal->getLang());
    }

    public function testConstructWithInteger()
    {
        $literal = new EasyRdf_Literal(10);
        $this->assertEquals(10, $literal->getValue());
        $this->assertEquals('xsd:integer', $literal->getDatatype());
        $this->assertEquals(null, $literal->getLang());
    }

    public function testConstructWithFloat()
    {
        $literal = new EasyRdf_Literal(1.5);
        $this->assertEquals(1.5, $literal->getValue());
        $this->assertEquals('xsd:decimal', $literal->getDatatype());
        $this->assertEquals(null, $literal->getLang());
    }

    public function testConstructWithBooleanTrue()
    {
        $literal = new EasyRdf_Literal(true);
        $this->assertEquals(true, $literal->getValue());
        $this->assertEquals('xsd:boolean', $literal->getDatatype());
        $this->assertEquals(null, $literal->getLang());
    }

    public function testConstructWithBooleanFalse()
    {
        $literal = new EasyRdf_Literal(false);
        $this->assertEquals(false, $literal->getValue());
        $this->assertEquals('xsd:boolean', $literal->getDatatype());
        $this->assertEquals(null, $literal->getLang());
    }

    public function testGetDatatypeUri()
    {
        $literal = new EasyRdf_Literal(1);
        $this->assertEquals(
            'http://www.w3.org/2001/XMLSchema#integer',
            $literal->getDatatypeUri()
        );
    }

    public function testToString()
    {
        $literal = new EasyRdf_Literal('Rat');
        $this->assertEquals('Rat', strval($literal));
    }

    public function testToRdfPhp()
    {
        $literal = new EasyRdf_Literal('Rat');
        $this->assertEquals(
            array(
               'type' => 'literal',
               'value' => 'Rat'
            ), $literal->toRdfPhp()
        );
    }

    public function testToRdfPhpWithLang()
    {
        $literal = new EasyRdf_Literal('Chat', 'fr');
        $this->assertEquals(
            array(
               'type' => 'literal',
               'value' => 'Chat',
               'lang' => 'fr'
            ), $literal->toRdfPhp()
        );
    }

    public function testToRdfPhpWithDatatype()
    {
        $literal = new EasyRdf_Literal(44);
        $this->assertEquals(
            array(
               'type' => 'literal',
               'value' => '44',
               'datatype' => 'http://www.w3.org/2001/XMLSchema#integer'
            ), $literal->toRdfPhp()
        );
    }

    public function testFoatToString()
    {
        $literal = new EasyRdf_Literal(0.5);
        $this->assertEquals('0.5', strval($literal));
    }

    public function testDumpValue()
    {
        $literal = new EasyRdf_Literal("hello & world");
        $this->assertEquals(
            '"hello & world"',
            $literal->dumpValue(false)
        );
        $this->assertEquals(
            "<span style='color:blue'>&quot;hello &amp; world&quot;</span>",
            $literal->dumpValue(true)
        );
    }

    public function testDumpValueWithLanguage()
    {
        $literal = new EasyRdf_Literal("Nick", 'en');
        $this->assertEquals(
            '"Nick"@en',
            $literal->dumpValue(false)
        );
        $this->assertEquals(
            "<span style='color:blue'>&quot;Nick&quot;@en</span>",
            $literal->dumpValue(true)
        );
    }

    public function testDumpValueWithDatatype()
    {
        $literal = new EasyRdf_Literal(1, null, 'xsd:integer');
        $this->assertEquals(
            '"1"^^xsd:integer',
            $literal->dumpValue(false)
        );
        $this->assertEquals(
            "<span style='color:blue'>&quot;1&quot;^^xsd:integer</span>",
            $literal->dumpValue(true)
        );
    }
}
