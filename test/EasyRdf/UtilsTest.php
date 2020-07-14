<?php
namespace EasyRdf;

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009-2020 Nicholas J Humfrey.  All rights reserved.
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
 * @copyright  Copyright (c) 2009-2020 Nicholas J Humfrey
 * @license    https://www.opensource.org/licenses/bsd-license.php
 */

require_once dirname(__DIR__).DIRECTORY_SEPARATOR.'TestHelper.php';

class UtilsTest extends TestCase
{
    public function testCameliseSimple()
    {
        $this->assertSame(
            'Hello',
            Utils::camelise('hEllO')
        );
    }

    public function testCameliseUnderscore()
    {
        $this->assertSame(
            'HelloWorld',
            Utils::camelise('hello_world')
        );
    }

    public function testCameliseDHyphen()
    {
        $this->assertSame(
            'HelloWorld',
            Utils::camelise('hello-world')
        );
    }

    public function testCameliseDoubleHyphen()
    {
        $this->assertSame(
            'HelloWorld',
            Utils::camelise('hello--world')
        );
    }

    public function testCameliseSpace()
    {
        $this->assertSame(
            'HelloWorld',
            Utils::camelise('hello  world')
        );
    }

    public function testCameliseFilePath()
    {
        $this->assertSame(
            'IAmEvilPhp',
            Utils::camelise('../../I/am/Evil.php')
        );
    }

    public function testCameliseEmpty()
    {
        $this->assertSame(
            '',
            Utils::camelise('')
        );
    }

    public function testIsAssoc()
    {
        $arr = array('foo' => 'bar');
        $this->assertTrue(Utils::isAssociativeArray($arr));
    }

    public function testIsAssocNonArray()
    {
         $this->assertFalse(Utils::isAssociativeArray('foo'));
    }

    public function testIsAssocArray()
    {
        $arr = array('foo', 'bar');
        $this->assertFalse(Utils::isAssociativeArray($arr));
    }

    public function testIsAssocIntAppend()
    {
        $arr = array('foo' => 'bar');
        array_push($arr, 'rat');
        $this->assertTrue(Utils::isAssociativeArray($arr));
    }

    public function testIsAssocIntPreppend()
    {
        $arr = array('foo' => 'bar');
        array_unshift($arr, 'rat');
        $this->assertFalse(Utils::isAssociativeArray($arr));
    }

    public function testRemoveFragment()
    {
        $this->assertSame(
            'http://example.com/',
            Utils::removeFragmentFromUri('http://example.com/#foo')
        );
    }

    public function testRemoveFragmentNoFragment()
    {
        $this->assertSame(
            'http://example.com/',
            Utils::removeFragmentFromUri('http://example.com/')
        );
    }

    public function testRemoveFragmentExtraHash()
    {
        $this->assertSame(
            'http://example.com/',
            Utils::removeFragmentFromUri('http://example.com/#foo#bar')
        );
    }

    public function testDumpResourceValue()
    {
        $res = new Resource('http://www.example.com/');
        $this->assertSame(
            "http://www.example.com/",
            Utils::dumpResourceValue($res, 'text')
        );
        $this->assertSame(
            "<a href='http://www.example.com/' ".
            "style='text-decoration:none;color:blue'>".
            "http://www.example.com/</a>",
            Utils::dumpResourceValue($res, 'html')
        );
    }

    public function testDumpResourceValueFromArray()
    {
        $res = array('type' => 'uri', 'value' => 'http://www.example.com/');
        $this->assertSame(
            "http://www.example.com/",
            Utils::dumpResourceValue($res, 'text')
        );
        $this->assertSame(
            "<a href='http://www.example.com/' ".
            "style='text-decoration:none;color:blue'>".
            "http://www.example.com/</a>",
            Utils::dumpResourceValue($res, 'html')
        );
    }

    public function testDumpResourceValueWithQuotes()
    {
        $this->assertSame(
            "<a href='a&#039; onclick=&#039;alert(1)' ".
            "style='text-decoration:none;color:blue'>a&#039; onclick=&#039;alert(1)</a>",
            Utils::dumpResourceValue("a' onclick='alert(1)")
        );
    }

    public function testDumpResourceValueWithIllegalColor()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$color must be a legal color code or name'
        );
        Utils::dumpResourceValue(
            'http://example.com/',
            'html',
            "blue'><script>alert(1);</script><!--"
        );
    }

    public function testDumpLiteralValue()
    {
        $literal = new Literal("hello & world");
        $this->assertSame(
            '"hello & world"',
            Utils::dumpLiteralValue($literal, 'text')
        );
        $this->assertSame(
            "<span style='color:black'>&quot;hello &amp; world&quot;</span>",
            Utils::dumpLiteralValue($literal, 'html')
        );
    }

    public function testDumpLiteralValueFromArray()
    {
        $literal = array('type' => 'literal', 'value' => 'Hot Sauce');
        $this->assertSame(
            '"Hot Sauce"',
            Utils::dumpLiteralValue($literal, 'text')
        );
        $this->assertSame(
            "<span style='color:black'>&quot;Hot Sauce&quot;</span>",
            Utils::dumpLiteralValue($literal, 'html')
        );
    }

    public function testDumpLiteralValueFromString()
    {
        $literal = 'a string';
        $this->assertSame(
            '"a string"',
            Utils::dumpLiteralValue($literal, 'text')
        );
        $this->assertSame(
            "<span style='color:black'>&quot;a string&quot;</span>",
            Utils::dumpLiteralValue($literal, 'html')
        );
    }

    public function testDumpLiteralValueWithLanguage()
    {
        $literal = array('type' => 'literal', 'value' => 'Nick', 'lang' => 'en');
        $this->assertSame(
            '"Nick"@en',
            Utils::dumpLiteralValue($literal, 'text')
        );
        $this->assertSame(
            "<span style='color:black'>&quot;Nick&quot;@en</span>",
            Utils::dumpLiteralValue($literal, 'html')
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
            Utils::dumpLiteralValue($literal, 'text')
        );
        $this->assertSame(
            "<span style='color:black'>&quot;1&quot;^^xsd:integer</span>",
            Utils::dumpLiteralValue($literal, 'html')
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
            Utils::dumpLiteralValue($literal, 'text')
        );
        $this->assertSame(
            "<span style='color:black'>&quot;1&quot;^^".
            "&lt;http://example.com/datatypes/int&gt;</span>",
            Utils::dumpLiteralValue($literal, 'html')
        );
    }

    public function testDumpLiteralValueWithIllegalColor()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$color must be a legal color code or name'
        );
        Utils::dumpLiteralValue(
            'literal',
            'html',
            "blue'><script>alert(1);</script><!--"
        );
    }

    public function testParseMimeTypeBasic()
    {
        list($type) = Utils::parseMimeType('text/plain');
        $this->assertSame('text/plain', $type);
    }

    public function testParseMimeTypeMixedCase()
    {
        list($type) = Utils::parseMimeType('TEXT/Plain');
        $this->assertSame('text/plain', $type);
    }

    public function testParseMimeTypeBasicWithWhitespace()
    {
        list($type) = Utils::parseMimeType(' text/plain  ');
        $this->assertSame('text/plain', $type);
    }

    public function testParseMimeTypeBasicWithCharset()
    {
        list($type, $params) = Utils::parseMimeType('text/plain;charset=utf8');
        $this->assertSame('text/plain', $type);
        $this->assertSame('utf8', $params['charset']);
    }

    public function testParseMimeTypeBasicWithMixedcaseCharset()
    {
        list($type, $params) = Utils::parseMimeType('text/plain;charset=UTF8');
        $this->assertSame('text/plain', $type);
        $this->assertSame('utf8', $params['charset']);
    }

    public function testParseMimeTypeBasicWithCharsetAndWhitespace()
    {
        list($type, $params) = Utils::parseMimeType(' text/plain ; charset = utf8 ');
        $this->assertSame('text/plain', $type);
        $this->assertSame('utf8', $params['charset']);
    }

    public function testExecCommandPipeTrue()
    {
        $output = Utils::execCommandPipe('true');
        $this->assertSame('', $output);
    }

    public function testExecCommandPipeLs()
    {
        $output = Utils::execCommandPipe('ls', array('/bin/'));
        $this->assertContains('cat', explode("\n", $output));
    }

    public function testExecCommandPipeLsWithDir()
    {
        $output = Utils::execCommandPipe('ls', null, null, '/bin');
        $this->assertContains('rm', explode("\n", $output));
    }

    public function testExecCommandPipeEcho()
    {
        $output = Utils::execCommandPipe('echo', 'Test Message');
        $this->assertSame("Test Message\n", $output);
    }

    public function testExecCommandPipeCat()
    {
        $output = Utils::execCommandPipe('cat', null, 'Test Message 2');
        $this->assertSame("Test Message 2", $output);
    }

    public function testExecCommandPipeFalse()
    {
        $this->setExpectedException(
            'EasyRdf\Exception',
            'Error while executing command false'
        );
        Utils::execCommandPipe('false');
    }

    public function testExecCommandPipeNotFound()
    {
        $this->setExpectedException(
            'EasyRdf\Exception',
            'Error while executing command no_such_command'
        );
        Utils::execCommandPipe('no_such_command');
    }
}
