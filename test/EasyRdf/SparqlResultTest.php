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

require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'TestHelper.php';


class EasyRdf_SparqlResultTest extends EasyRdf_TestCase
{
    public function testQuerySelectAllXml()
    {
        $result = new EasyRdf_SparqlResult(
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

    public function testQuerySelectAllJson()
    {
        $result = new EasyRdf_SparqlResult(
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

    public function testQueryAskTrueJson()
    {
        $result = new EasyRdf_SparqlResult(
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

    public function testQueryAskFalseJson()
    {
        $result = new EasyRdf_SparqlResult(
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

    public function testQueryAskTrueXml()
    {
        $result = new EasyRdf_SparqlResult(
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

    public function testQueryAskFalseXml()
    {
        $result = new EasyRdf_SparqlResult(
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

}
