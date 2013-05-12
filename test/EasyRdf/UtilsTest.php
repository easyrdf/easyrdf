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

require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'TestHelper.php';

class EasyRdf_UtilsTest extends EasyRdf_TestCase
{
    public function testCameliseSimple()
    {
        $this->assertSame(
            'Hello',
            EasyRdf_Utils::camelise('hEllO')
        );
    }

    public function testCameliseUnderscore()
    {
        $this->assertSame(
            'HelloWorld',
            EasyRdf_Utils::camelise('hello_world')
        );
    }

    public function testCameliseDHyphen()
    {
        $this->assertSame(
            'HelloWorld',
            EasyRdf_Utils::camelise('hello-world')
        );
    }

    public function testCameliseDoubleHyphen()
    {
        $this->assertSame(
            'HelloWorld',
            EasyRdf_Utils::camelise('hello--world')
        );
    }

    public function testCameliseSpace()
    {
        $this->assertSame(
            'HelloWorld',
            EasyRdf_Utils::camelise('hello  world')
        );
    }

    public function testCameliseFilePath()
    {
        $this->assertSame(
            'IAmEvilPhp',
            EasyRdf_Utils::camelise('../../I/am/Evil.php')
        );
    }

    public function testCameliseEmpty()
    {
        $this->assertSame(
            '',
            EasyRdf_Utils::camelise('')
        );
    }

    public function testIsAssoc()
    {
        $arr = array('foo' => 'bar');
        $this->assertTrue(EasyRdf_Utils::isAssociativeArray($arr));

    }

    public function testIsAssocNonArray()
    {
         $this->assertFalse(EasyRdf_Utils::isAssociativeArray('foo'));
    }

    public function testIsAssocArray()
    {
        $arr = array('foo', 'bar');
        $this->assertFalse(EasyRdf_Utils::isAssociativeArray($arr));
    }

    public function testIsAssocIntAppend()
    {
        $arr = array('foo' => 'bar');
        array_push($arr, 'rat');
        $this->assertTrue(EasyRdf_Utils::isAssociativeArray($arr));
    }

    public function testIsAssocIntPreppend()
    {
        $arr = array('foo' => 'bar');
        array_unshift($arr, 'rat');
        $this->assertFalse(EasyRdf_Utils::isAssociativeArray($arr));
    }

    public function testRemoveFragment()
    {
        $this->assertSame(
            'http://example.com/',
            EasyRdf_Utils::removeFragmentFromUri('http://example.com/#foo')
        );
    }

    public function testRemoveFragmentNoFragment()
    {
        $this->assertSame(
            'http://example.com/',
            EasyRdf_Utils::removeFragmentFromUri('http://example.com/')
        );
    }

    public function testRemoveFragmentExtraHash()
    {
        $this->assertSame(
            'http://example.com/',
            EasyRdf_Utils::removeFragmentFromUri('http://example.com/#foo#bar')
        );
    }

    public function testDumpResourceValue()
    {
        $res = new EasyRdf_Resource('http://www.example.com/');
        $this->assertSame(
            "http://www.example.com/",
            EasyRdf_Utils::dumpResourceValue($res, 'text')
        );
        $this->assertSame(
            "<a href='http://www.example.com/' ".
            "style='text-decoration:none;color:blue'>".
            "http://www.example.com/</a>",
            EasyRdf_Utils::dumpResourceValue($res, 'html')
        );
    }

    public function testDumpResourceValueFromArray()
    {
        $res = array('type' => 'uri', 'value' => 'http://www.example.com/');
        $this->assertSame(
            "http://www.example.com/",
            EasyRdf_Utils::dumpResourceValue($res, 'text')
        );
        $this->assertSame(
            "<a href='http://www.example.com/' ".
            "style='text-decoration:none;color:blue'>".
            "http://www.example.com/</a>",
            EasyRdf_Utils::dumpResourceValue($res, 'html')
        );
    }

    public function testDumpResourceValueWithQuotes()
    {
        $this->assertSame(
            "<a href='a&#039; onclick=&#039;alert(1)' ".
            "style='text-decoration:none;color:blue'>a&#039; onclick=&#039;alert(1)</a>",
            EasyRdf_Utils::dumpResourceValue("a' onclick='alert(1)")
        );
    }

    public function testDumpResourceValueWithIllegalColor()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$color must be a legal color code or name'
        );
        EasyRdf_Utils::dumpResourceValue(
            'http://example.com/',
            'html',
            "blue'><script>alert(1);</script><!--"
        );
    }

    public function testDumpLiteralValue()
    {
        $literal = new EasyRdf_Literal("hello & world");
        $this->assertSame(
            '"hello & world"',
            EasyRdf_Utils::dumpLiteralValue($literal, 'text')
        );
        $this->assertSame(
            "<span style='color:black'>&quot;hello &amp; world&quot;</span>",
            EasyRdf_Utils::dumpLiteralValue($literal, 'html')
        );
    }

    public function testDumpLiteralValueFromArray()
    {
        $literal = array('type' => 'literal', 'value' => 'Hot Sauce');
        $this->assertSame(
            '"Hot Sauce"',
            EasyRdf_Utils::dumpLiteralValue($literal, 'text')
        );
        $this->assertSame(
            "<span style='color:black'>&quot;Hot Sauce&quot;</span>",
            EasyRdf_Utils::dumpLiteralValue($literal, 'html')
        );
    }

    public function testDumpLiteralValueFromString()
    {
        $literal = 'a string';
        $this->assertSame(
            '"a string"',
            EasyRdf_Utils::dumpLiteralValue($literal, 'text')
        );
        $this->assertSame(
            "<span style='color:black'>&quot;a string&quot;</span>",
            EasyRdf_Utils::dumpLiteralValue($literal, 'html')
        );
    }

    public function testDumpLiteralValueWithLanguage()
    {
        $literal = array('type' => 'literal', 'value' => 'Nick', 'lang' => 'en');
        $this->assertSame(
            '"Nick"@en',
            EasyRdf_Utils::dumpLiteralValue($literal, 'text')
        );
        $this->assertSame(
            "<span style='color:black'>&quot;Nick&quot;@en</span>",
            EasyRdf_Utils::dumpLiteralValue($literal, 'html')
        );
    }

    public function testDumpLiteralValueWithDatatype()
    {
        $literal = array(
            'type' => 'literal',
            'value' => '1',
            'datatype' => 'http://www.w3.org/2001/XMLSchema#integer'
        );
        $this->assertSame(
            '"1"^^xsd:integer',
            EasyRdf_Utils::dumpLiteralValue($literal, 'text')
        );
        $this->assertSame(
            "<span style='color:black'>&quot;1&quot;^^xsd:integer</span>",
            EasyRdf_Utils::dumpLiteralValue($literal, 'html')
        );
    }

    public function testDumpLiteralValueWithUriDatatype()
    {
        $literal = array(
            'type' => 'literal',
            'value' => '1',
            'datatype' => 'http://example.com/datatypes/int'
        );
        $this->assertSame(
            '"1"^^<http://example.com/datatypes/int>',
            EasyRdf_Utils::dumpLiteralValue($literal, 'text')
        );
        $this->assertSame(
            "<span style='color:black'>&quot;1&quot;^^".
            "&lt;http://example.com/datatypes/int&gt;</span>",
            EasyRdf_Utils::dumpLiteralValue($literal, 'html')
        );
    }

    public function testDumpLiteralValueWithIllegalColor()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$color must be a legal color code or name'
        );
        EasyRdf_Utils::dumpLiteralValue(
            'literal',
            'html',
            "blue'><script>alert(1);</script><!--"
        );
    }

    public function testParseMimeTypeBasic()
    {
        list($type) = EasyRdf_Utils::parseMimeType('text/plain');
        $this->assertSame('text/plain', $type);
    }

    public function testParseMimeTypeMixedCase()
    {
        list($type) = EasyRdf_Utils::parseMimeType('TEXT/Plain');
        $this->assertSame('text/plain', $type);
    }

    public function testParseMimeTypeBasicWithWhitespace()
    {
        list($type) = EasyRdf_Utils::parseMimeType(' text/plain  ');
        $this->assertSame('text/plain', $type);
    }

    public function testParseMimeTypeBasicWithCharset()
    {
        list($type, $params) = EasyRdf_Utils::parseMimeType('text/plain;charset=utf8');
        $this->assertSame('text/plain', $type);
        $this->assertSame('utf8', $params['charset']);
    }

    public function testParseMimeTypeBasicWithMixedcaseCharset()
    {
        list($type, $params) = EasyRdf_Utils::parseMimeType('text/plain;charset=UTF8');
        $this->assertSame('text/plain', $type);
        $this->assertSame('utf8', $params['charset']);
    }

    public function testParseMimeTypeBasicWithCharsetAndWhitespace()
    {
        list($type, $params) = EasyRdf_Utils::parseMimeType(' text/plain ; charset = utf8 ');
        $this->assertSame('text/plain', $type);
        $this->assertSame('utf8', $params['charset']);
    }

    public function testExecCommandPipeTrue()
    {
        $output = EasyRdf_Utils::execCommandPipe('true');
        $this->assertSame('', $output);
    }

    public function testExecCommandPipeLs()
    {
        $output = EasyRdf_Utils::execCommandPipe('ls', array('/bin/'));
        $this->assertContains('cat', explode("\n", $output));
    }

    public function testExecCommandPipeLsWithDir()
    {
        $output = EasyRdf_Utils::execCommandPipe('ls', null, null, '/bin');
        $this->assertContains('rm', explode("\n", $output));
    }

    public function testExecCommandPipeEcho()
    {
        $output = EasyRdf_Utils::execCommandPipe('echo', 'Test Message');
        $this->assertSame("Test Message\n", $output);
    }

    public function testExecCommandPipeCat()
    {
        $output = EasyRdf_Utils::execCommandPipe('cat', null, 'Test Message 2');
        $this->assertSame("Test Message 2", $output);
    }

    public function testExecCommandPipeFalse()
    {
        $this->setExpectedException(
            'EasyRdf_Exception',
            'Error while executing command false'
        );
        $output = EasyRdf_Utils::execCommandPipe('false');
    }

    public function testExecCommandPipeNotFound()
    {
        $this->setExpectedException(
            'EasyRdf_Exception',
            'Error while executing command no_such_command'
        );
        $output = EasyRdf_Utils::execCommandPipe('no_such_command');
    }
}
