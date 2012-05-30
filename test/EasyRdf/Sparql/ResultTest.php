<?php

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009-2011 Nicholas J Humfrey.  All rights reserved.
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
 * @copyright  Copyright (c) 2009-2011 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 * @version    $Id$
 */

require_once realpath(dirname(__FILE__) . '/../../') . '/TestHelper.php';


class EasyRdf_Sparql_ResultTest extends EasyRdf_TestCase
{
    public function testSelectAllXml()
    {
        $result = new EasyRdf_Sparql_Result(
            readFixture('sparql_select_all.xml'),
            'application/sparql-results+xml'
        );

        $this->assertEquals(3, $result->numFields());
        $this->assertEquals(array('s','p','o'), $result->getFields());

        $this->assertEquals(14, count($result));
        $this->assertEquals(14, $result->numRows());
        $this->assertEquals(
            new EasyRdf_Resource('_:genid1'), $result[0]->s
        );
        $this->assertEquals(
            new EasyRdf_Resource('http://xmlns.com/foaf/0.1/name'), $result[0]->p
        );
        $this->assertEquals(
            new EasyRdf_Literal("Joe's Current Project"), $result[0]->o
        );
    }

    public function testSelectAllJson()
    {
        $result = new EasyRdf_Sparql_Result(
            readFixture('sparql_select_all.json'),
            'application/sparql-results+json'
        );

        $this->assertEquals(3, $result->numFields());
        $this->assertEquals(array('s','p','o'), $result->getFields());

        $this->assertEquals(14, count($result));
        $this->assertEquals(14, $result->numRows());
        $this->assertEquals(
            new EasyRdf_Resource('_:genid1'), $result[0]->s
        );
        $this->assertEquals(
            new EasyRdf_Resource('http://xmlns.com/foaf/0.1/name'), $result[0]->p
        );
        $this->assertEquals(
            new EasyRdf_Literal("Joe's Current Project"), $result[0]->o
        );
    }

    public function testSelectAllJsonWithCharset()
    {
        $result = new EasyRdf_Sparql_Result(
            readFixture('sparql_select_all.json'),
            'application/sparql-results+json; charset=utf-8'
        );

        $this->assertEquals(3, $result->numFields());
        $this->assertEquals(array('s','p','o'), $result->getFields());
        $this->assertEquals(
            new EasyRdf_Literal("Joe's Current Project"), $result[0]->o
        );
    }

    public function testSelectEmptyXml()
    {
        $result = new EasyRdf_Sparql_Result(
            readFixture('sparql_select_empty.xml'),
            'application/sparql-results+xml'
        );

        $this->assertEquals(3, $result->numFields());
        $this->assertEquals(array('s','p','o'), $result->getFields());
        $this->assertEquals(0, count($result));
    }

    public function testSelectEmptyJson()
    {
        $result = new EasyRdf_Sparql_Result(
            readFixture('sparql_select_empty.json'),
            'application/sparql-results+json'
        );

        $this->assertEquals(3, $result->numFields());
        $this->assertEquals(array('s','p','o'), $result->getFields());
        $this->assertEquals(0, count($result));
    }

    public function testSelectTypedLiteralJson()
    {
        $result = new EasyRdf_Sparql_Result(
            readFixture('sparql_typed_literal.json'),
            'application/sparql-results+json'
        );

        $first = $result[0];
        $this->assertStringEquals('http://www.bbc.co.uk/programmes/b0074dlv#programme', $first->episode);
        $this->assertStringEquals(1, $first->pos);
        $this->assertStringEquals('Rose', $first->label);
    }

    public function testSelectLangLiteralJson()
    {
        $result = new EasyRdf_Sparql_Result(
            readFixture('sparql_select_lang.json'),
            'application/sparql-results+json'
        );

        # 1st: Example using "lang": "en"
        $first = $result[0];
        $this->assertStringEquals('http://www.bbc.co.uk/programmes/b0074dlv#programme', $first->episode);
        $this->assertEquals(1, $first->pos->getValue());
        $this->assertEquals('Rose', $first->label->getValue());
        $this->assertEquals('en', $first->label->getLang());

        # 2nd: Example using "xml:lang": "en"
        $second = $result[1];
        $this->assertStringEquals('http://www.bbc.co.uk/programmes/b0074dmp#programme', $second->episode);
        $this->assertEquals(2, $second->pos->getValue());
        $this->assertEquals('The End of the World', $second->label->getValue());
        $this->assertEquals('en', $second->label->getLang());

        # 3rd: no lang
        $second = $result[2];
        $this->assertStringEquals('http://www.bbc.co.uk/programmes/b0074dng#programme', $second->episode);
        $this->assertEquals(3, $second->pos->getValue());
        $this->assertEquals('The Unquiet Dead', $second->label->getValue());
        $this->assertEquals(null, $second->label->getLang());
    }

    public function testAskTrueJson()
    {
        $result = new EasyRdf_Sparql_Result(
            readFixture('sparql_ask_true.json'),
            'application/sparql-results+json'
        );

        $this->assertEquals('boolean', $result->getType());
        $this->assertFalse($result->isFalse());
        $this->assertTrue($result->isTrue());
        $this->assertEquals(true, $result->getBoolean());
        $this->assertStringEquals('true', $result);

        $this->assertEquals(0, $result->numFields());
        $this->assertEquals(0, $result->numRows());
    }

    public function testAskFalseJson()
    {
        $result = new EasyRdf_Sparql_Result(
            readFixture('sparql_ask_false.json'),
            'application/sparql-results+json'
        );

        $this->assertEquals('boolean', $result->getType());
        $this->assertTrue($result->isFalse());
        $this->assertFalse($result->isTrue());
        $this->assertEquals(false, $result->getBoolean());
        $this->assertStringEquals('false', $result);

        $this->assertEquals(0, $result->numFields());
        $this->assertEquals(0, $result->numRows());
    }

    public function testAskTrueXml()
    {
        $result = new EasyRdf_Sparql_Result(
            readFixture('sparql_ask_true.xml'),
            'application/sparql-results+xml'
        );
        $this->assertEquals('boolean', $result->getType());
        $this->assertFalse($result->isFalse());
        $this->assertTrue($result->isTrue());
        $this->assertEquals(true, $result->getBoolean());
        $this->assertStringEquals('true', $result);

        $this->assertEquals(0, $result->numFields());
        $this->assertEquals(0, $result->numRows());
    }

    public function testAskFalseXml()
    {
        $result = new EasyRdf_Sparql_Result(
            readFixture('sparql_ask_false.xml'),
            'application/sparql-results+xml'
        );
        $this->assertEquals('boolean', $result->getType());
        $this->assertTrue($result->isFalse());
        $this->assertFalse($result->isTrue());
        $this->assertEquals(false, $result->getBoolean());
        $this->assertStringEquals('false', $result);

        $this->assertEquals(0, $result->numFields());
        $this->assertEquals(0, $result->numRows());
    }

    public function testInvalidXml()
    {
        $this->setExpectedException(
            'EasyRdf_Exception',
            'Failed to parse SPARQL XML Query Results format'
        );
        $result = new EasyRdf_Sparql_Result(
            readFixture('sparql_invalid.xml'),
            'application/sparql-results+xml'
        );
    }

    public function testInvalidJson()
    {
        $this->setExpectedException(
            'EasyRdf_Exception',
            'Failed to parse SPARQL JSON Query Results format'
        );
        $result = new EasyRdf_Sparql_Result(
            readFixture('sparql_invalid.json'),
            'application/sparql-results+json'
        );
    }

    public function testInvalidJsonTerm()
    {
        $this->setExpectedException(
            'EasyRdf_Exception',
            'Failed to parse SPARQL Query Results format, unknown term type: newtype'
        );
        $result = new EasyRdf_Sparql_Result(
            readFixture('sparql_invalid_term.json'),
            'application/sparql-results+json'
        );
    }

    public function testDumpSelectAllHtml()
    {
        $result = new EasyRdf_Sparql_Result(
            readFixture('sparql_select_all.xml'),
            'application/sparql-results+xml'
        );

        $html = $result->dump(true);
        $this->assertContains("<table class='sparql-results'", $html);
        $this->assertContains(">?s</th>", $html);
        $this->assertContains(">?p</th>", $html);
        $this->assertContains(">?o</th></tr>", $html);

        $this->assertContains(">http://www.example.com/joe#me</a></td>", $html);
        $this->assertContains(">foaf:name</a></td>", $html);
        $this->assertContains(">&quot;Joe Bloggs&quot;</span></td>", $html);
        $this->assertContains("</table>", $html);
    }

    public function testDumpSelectAllText()
    {
        $result = new EasyRdf_Sparql_Result(
            readFixture('sparql_select_all.xml'),
            'application/sparql-results+xml'
        );

        $text = $result->dump(false);
        $this->assertContains('+-------------------------------------+', $text);
        $this->assertContains('| ?s                                  |', $text);
        $this->assertContains('| http://www.example.com/joe#me       |', $text);
        $this->assertContains('+---------------------+', $text);
        $this->assertContains('| ?p                  |', $text);
        $this->assertContains('| foaf:name           |', $text);
        $this->assertContains('+--------------------------------+', $text);
        $this->assertContains('| ?o                             |', $text);
        $this->assertContains('| "Joe Bloggs"                   |', $text);
    }

    public function testDumpAskFalseHtml()
    {
        $result = new EasyRdf_Sparql_Result(
            readFixture('sparql_ask_false.xml'),
            'application/sparql-results+xml'
        );

        $html = $result->dump(true);
        $this->assertContains(">false</span>", $html);
    }

    public function testDumpAskFalseText()
    {
        $result = new EasyRdf_Sparql_Result(
            readFixture('sparql_ask_false.xml'),
            'application/sparql-results+xml'
        );

        $text = $result->dump(false);
        $this->assertEquals("Result: false", $text);
    }

    public function testInvalidMimeType()
    {
        $this->setExpectedException(
            'EasyRdf_Exception',
            'Invalid MIME type: foobar'
        );
        $result = new EasyRdf_Sparql_Result('foobar', 'foobar');
    }

    public function testUnsupportedFormat()
    {
        $this->setExpectedException(
            'EasyRdf_Exception',
            'Unsupported SPARQL Query Results format: foo/bar'
        );
        $result = new EasyRdf_Sparql_Result('foobar', 'foo/bar');
    }

}
