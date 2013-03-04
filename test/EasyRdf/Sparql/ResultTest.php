<?php

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009-2013 Nicholas J Humfrey.  All rights reserved.
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


class EasyRdf_Sparql_ResultTest extends EasyRdf_TestCase
{
    public function testSelectAllXml()
    {
        $result = new EasyRdf_Sparql_Result(
            readFixture('sparql_select_all.xml'),
            'application/sparql-results+xml'
        );

        $this->assertSame(3, $result->numFields());
        $this->assertSame(array('s','p','o'), $result->getFields());

        $this->assertCount(14, $result);
        $this->assertSame(14, $result->numRows());
        $this->assertSame(14, count($result));
        $this->assertEquals(
            new EasyRdf_Resource('_:genid1'),
            $result[0]->s
        );
        $this->assertEquals(
            new EasyRdf_Resource('http://xmlns.com/foaf/0.1/name'),
            $result[0]->p
        );
        $this->assertEquals(
            new EasyRdf_Literal("Joe's Current Project"),
            $result[0]->o
        );
    }

    public function testSelectAllXmlWithWhitespace()
    {
        $result = new EasyRdf_Sparql_Result(
            readFixture('sparql_select_all_ws.xml'),
            'application/sparql-results+xml'
        );

        $this->assertSame(3, $result->numFields());
        $this->assertSame(array('s','p','o'), $result->getFields());

        $this->assertCount(14, $result);
        $this->assertSame(14, $result->numRows());
        $this->assertSame(14, count($result));
        $this->assertEquals(
            new EasyRdf_Resource('_:genid1'),
            $result[0]->s
        );
        $this->assertEquals(
            new EasyRdf_Resource('http://xmlns.com/foaf/0.1/name'),
            $result[0]->p
        );
        $this->assertEquals(
            new EasyRdf_Literal("Joe's Current Project"),
            $result[0]->o
        );
    }

    public function testSelectAllJson()
    {
        $result = new EasyRdf_Sparql_Result(
            readFixture('sparql_select_all.json'),
            'application/sparql-results+json'
        );

        $this->assertSame(3, $result->numFields());
        $this->assertSame(array('s','p','o'), $result->getFields());

        $this->assertCount(14, $result);
        $this->assertSame(14, $result->numRows());
        $this->assertSame(14, count($result));
        $this->assertEquals(
            new EasyRdf_Resource('_:genid1'),
            $result[0]->s
        );
        $this->assertEquals(
            new EasyRdf_Resource('http://xmlns.com/foaf/0.1/name'),
            $result[0]->p
        );
        $this->assertEquals(
            new EasyRdf_Literal("Joe's Current Project"),
            $result[0]->o
        );
    }

    public function testSelectEmptyXml()
    {
        $result = new EasyRdf_Sparql_Result(
            readFixture('sparql_select_empty.xml'),
            'application/sparql-results+xml'
        );

        $this->assertSame(3, $result->numFields());
        $this->assertSame(array('s','p','o'), $result->getFields());
        $this->assertCount(0, $result);
    }

    public function testSelectEmptyJson()
    {
        $result = new EasyRdf_Sparql_Result(
            readFixture('sparql_select_empty.json'),
            'application/sparql-results+json'
        );

        $this->assertSame(3, $result->numFields());
        $this->assertSame(array('s','p','o'), $result->getFields());
        $this->assertCount(0, $result);
    }

    public function testSelectLangLiteralXml()
    {
        $result = new EasyRdf_Sparql_Result(
            readFixture('sparql_select_lang.xml'),
            'application/sparql-results+xml'
        );

        # 1st: Example using xml:lang="en"
        $first = $result[0];
        $this->assertSame('London', $first->label->getValue());
        $this->assertSame('en', $first->label->getLang());
        $this->assertSame(null, $first->label->getDatatype());

        # 2nd: Example using xml:lang="es"
        $second = $result[1];
        $this->assertSame('Londres', $second->label->getValue());
        $this->assertSame('es', $second->label->getLang());
        $this->assertSame(null, $second->label->getDatatype());

        # 3rd: no lang
        $third = $result[2];
        $this->assertSame('London', $third->label->getValue());
        $this->assertSame(null, $third->label->getLang());
        $this->assertSame(null, $third->label->getDatatype());
    }

    public function testSelectLangLiteralJson()
    {
        $result = new EasyRdf_Sparql_Result(
            readFixture('sparql_select_lang.json'),
            'application/sparql-results+json'
        );

        # 1st: Example using xml:lang="en"
        $first = $result[0];
        $this->assertSame('London', $first->label->getValue());
        $this->assertSame('en', $first->label->getLang());
        $this->assertSame(null, $first->label->getDatatype());

        # 2nd: Example using lang="es"
        $second = $result[1];
        $this->assertSame('Londres', $second->label->getValue());
        $this->assertSame('es', $second->label->getLang());
        $this->assertSame(null, $second->label->getDatatype());

        # 3rd: no lang
        $third = $result[2];
        $this->assertSame('London', $third->label->getValue());
        $this->assertSame(null, $third->label->getLang());
        $this->assertSame(null, $third->label->getDatatype());
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

    public function testSelectTypedLiteralXml()
    {
        $result = new EasyRdf_Sparql_Result(
            readFixture('sparql_typed_literal.xml'),
            'application/sparql-results+xml'
        );

        $first = $result[0];
        $this->assertStringEquals('http://www.bbc.co.uk/programmes/b0074dlv#programme', $first->episode);
        $this->assertStringEquals(1, $first->pos);
        $this->assertStringEquals('Rose', $first->label);
    }

    public function testAskTrueJson()
    {
        $result = new EasyRdf_Sparql_Result(
            readFixture('sparql_ask_true.json'),
            'application/sparql-results+json'
        );

        $this->assertSame('boolean', $result->getType());
        $this->assertFalse($result->isFalse());
        $this->assertTrue($result->isTrue());
        $this->assertSame(true, $result->getBoolean());
        $this->assertStringEquals('true', $result);

        $this->assertSame(0, $result->numFields());
        $this->assertSame(0, $result->numRows());
        $this->assertSame(0, count($result));
    }

    public function testAskFalseJson()
    {
        $result = new EasyRdf_Sparql_Result(
            readFixture('sparql_ask_false.json'),
            'application/sparql-results+json'
        );

        $this->assertSame('boolean', $result->getType());
        $this->assertTrue($result->isFalse());
        $this->assertFalse($result->isTrue());
        $this->assertSame(false, $result->getBoolean());
        $this->assertStringEquals('false', $result);

        $this->assertSame(0, $result->numFields());
        $this->assertSame(0, $result->numRows());
        $this->assertSame(0, count($result));
    }

    public function testAskTrueXml()
    {
        $result = new EasyRdf_Sparql_Result(
            readFixture('sparql_ask_true.xml'),
            'application/sparql-results+xml'
        );
        $this->assertSame('boolean', $result->getType());
        $this->assertFalse($result->isFalse());
        $this->assertTrue($result->isTrue());
        $this->assertSame(true, $result->getBoolean());
        $this->assertStringEquals('true', $result);

        $this->assertSame(0, $result->numFields());
        $this->assertSame(0, $result->numRows());
        $this->assertSame(0, count($result));
    }

    public function testAskFalseXml()
    {
        $result = new EasyRdf_Sparql_Result(
            readFixture('sparql_ask_false.xml'),
            'application/sparql-results+xml'
        );
        $this->assertSame('boolean', $result->getType());
        $this->assertTrue($result->isFalse());
        $this->assertFalse($result->isTrue());
        $this->assertSame(false, $result->getBoolean());
        $this->assertStringEquals('false', $result);

        $this->assertSame(0, $result->numFields());
        $this->assertSame(0, $result->numRows());
        $this->assertSame(0, count($result));
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

    public function testNotSparqlXml()
    {
        $this->setExpectedException(
            'EasyRdf_Exception',
            'Incorrect root node in SPARQL XML Query Results format'
        );
        $result = new EasyRdf_Sparql_Result(
            readFixture('not_sparql_result.xml'),
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

        $html = $result->dump('html');
        $this->assertContains("<table class='sparql-results'", $html);
        $this->assertContains(">?s</th>", $html);
        $this->assertContains(">?p</th>", $html);
        $this->assertContains(">?o</th></tr>", $html);

        $this->assertContains(">http://www.example.com/joe#me</a></td>", $html);
        $this->assertContains(">foaf:name</a></td>", $html);
        $this->assertContains(">&quot;Joe Bloggs&quot;@en</span></td>", $html);
        $this->assertContains("</table>", $html);
    }

    public function testDumpSelectAllText()
    {
        $result = new EasyRdf_Sparql_Result(
            readFixture('sparql_select_all.xml'),
            'application/sparql-results+xml'
        );

        $text = $result->dump('text');
        $this->assertContains('+-------------------------------------+', $text);
        $this->assertContains('| ?s                                  |', $text);
        $this->assertContains('| http://www.example.com/joe#me       |', $text);
        $this->assertContains('+---------------------+', $text);
        $this->assertContains('| ?p                  |', $text);
        $this->assertContains('| foaf:name           |', $text);
        $this->assertContains('+--------------------------------+', $text);
        $this->assertContains('| ?o                             |', $text);
        $this->assertContains('| "Joe Bloggs"@en                |', $text);
    }

    public function testDumpSelectUnbound()
    {
        $result = new EasyRdf_Sparql_Result(
            readFixture('sparql_select_unbound.xml'),
            'application/sparql-results+xml'
        );

        $html = $result->dump('html');
        $this->assertContains(">?person</th>", $html);
        $this->assertContains(">?name</th>", $html);
        $this->assertContains(">?foo</th>", $html);

        $this->assertContains('>http://dbpedia.org/resource/Tim_Berners-Lee</a>', $html);
        $this->assertContains('>&quot;Tim Berners-Lee&quot;@en</span>', $html);
        $this->assertContains('<td>&nbsp;</td>', $html);
    }

    public function testDumpAskFalseHtml()
    {
        $result = new EasyRdf_Sparql_Result(
            readFixture('sparql_ask_false.xml'),
            'application/sparql-results+xml'
        );

        $html = $result->dump('html');
        $this->assertContains(">false</span>", $html);
    }

    public function testDumpAskFalseText()
    {
        $result = new EasyRdf_Sparql_Result(
            readFixture('sparql_ask_false.xml'),
            'application/sparql-results+xml'
        );

        $text = $result->dump('text');
        $this->assertSame("Result: false", $text);
    }

    public function testDumpUnknownType()
    {
        $result = new EasyRdf_Sparql_Result(
            readFixture('sparql_ask_false.xml'),
            'application/sparql-results+xml'
        );

        $reflector = new ReflectionProperty('EasyRdf_Sparql_Result', 'type');
        if (!method_exists($reflector, 'setAccessible')) {
            $this->markTestSkipped(
                'ReflectionProperty::setAccessible() is not available.'
            );
        } else {
            $reflector->setAccessible(true);
        }
        $reflector->setValue($result, 'foobar');

        $this->setExpectedException(
            'EasyRdf_Exception',
            'Failed to dump SPARQL Query Results format, unknown type: foobar'
        );
        $str = $result->dump();
    }

    public function testToStringBooleanTrue()
    {
        $result = new EasyRdf_Sparql_Result(
            readFixture('sparql_ask_true.xml'),
            'application/sparql-results+xml'
        );

        $this->assertSame("true", strval($result));
    }

    public function testToStringBooleanFalse()
    {
        $result = new EasyRdf_Sparql_Result(
            readFixture('sparql_ask_false.xml'),
            'application/sparql-results+xml'
        );

        $this->assertSame("false", strval($result));
    }

    public function testToStringSelectAll()
    {
        $result = new EasyRdf_Sparql_Result(
            readFixture('sparql_select_all.xml'),
            'application/sparql-results+xml'
        );

        $string = strval($result);
        $this->assertContains('+-------------------------------------+', $string);
        $this->assertContains('| http://www.example.com/joe#me       |', $string);
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
