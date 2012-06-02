<?php

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009-2012 Nicholas J Humfrey.  All rights reserved.
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
 * @copyright  Copyright (c) 2009-2012 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 * @version    $Id$
 */

require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'TestHelper.php';

class EasyRdf_UtilsTest extends EasyRdf_TestCase
{
    public function setup()
    {
      $this->baseUri = "http://example.org/bpath/cpath/d;p?querystr#frag";
    }

    public function testCameliseSimple()
    {
        $this->assertEquals(
            'Hello',
            EasyRdf_Utils::camelise('hEllO')
        );
    }

    public function testCameliseUnderscore()
    {
        $this->assertEquals(
            'HelloWorld',
            EasyRdf_Utils::camelise('hello_world')
        );
    }

    public function testCameliseDHyphen()
    {
        $this->assertEquals(
            'HelloWorld',
            EasyRdf_Utils::camelise('hello-world')
        );
    }

    public function testCameliseDoubleHyphen()
    {
        $this->assertEquals(
            'HelloWorld',
            EasyRdf_Utils::camelise('hello--world')
        );
    }

    public function testCameliseSpace()
    {
        $this->assertEquals(
            'HelloWorld',
            EasyRdf_Utils::camelise('hello  world')
        );
    }

    public function testCameliseFilePath()
    {
        $this->assertEquals(
            'IAmEvilPhp',
            EasyRdf_Utils::camelise('../../I/am/Evil.php')
        );
    }

    public function testCameliseEmpty()
    {
        $this->assertEquals(
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


    /* Tests from RFC2396 Appendix C
     * and RFC3986 Section 5
     *
     * Modifications:
     *  - add 'path' when items are path components to make easier to read
     *  - use example.org instead of 'a' for the authority
     *  - results are against the baseUri above
     */

    /* RFC2396 Appendix C.1 / RFC3986 5.4.1 Normal Examples */
    public function testResolveReferenceUriNormal0()
    {

        $this->assertEquals(
            "http://example.org/bpath/cpath/d;p?querystr",
            EasyRdf_Utils::resolveUriReference($this->baseUri, "")
        );
    }

    public function testResolveReferenceUriNormal1()
    {
        $this->assertEquals(
            "g:h",
            EasyRdf_Utils::resolveUriReference($this->baseUri, "g:h")
        );
    }

    public function testResolveReferenceUriNormal2()
    {
        $this->assertEquals(
            "http://example.org/bpath/cpath/gpath",
            EasyRdf_Utils::resolveUriReference($this->baseUri, "gpath")
        );
    }

    public function testResolveReferenceUriNormal3()
    {
        $this->assertEquals(
            "http://example.org/bpath/cpath/gpath",
            EasyRdf_Utils::resolveUriReference($this->baseUri, "./gpath")
        );
    }

    public function testResolveReferenceUriNormal4()
    {
        $this->assertEquals(
            "http://example.org/bpath/cpath/gpath/",
            EasyRdf_Utils::resolveUriReference($this->baseUri, "gpath/")
        );
    }

    public function testResolveReferenceUriNormal5()
    {
        $this->assertEquals(
            "http://example.org/gpath",
            EasyRdf_Utils::resolveUriReference($this->baseUri, "/gpath")
        );
    }

    public function testResolveReferenceUriNormal6()
    {
        $this->assertEquals(
            "http://gpath",
            EasyRdf_Utils::resolveUriReference($this->baseUri, "//gpath")
        );
    }

    public function testResolveReferenceUriNormal7()
    {
        $this->markTestSkipped('FIXME');
        $this->assertEquals(
            "http://example.org/bpath/cpath/?y",
            EasyRdf_Utils::resolveUriReference($this->baseUri, "?y")
        );
    }

    public function testResolveReferenceUriNormal8()
    {
        $this->assertEquals(
            "http://example.org/bpath/cpath/gpath?y",
            EasyRdf_Utils::resolveUriReference($this->baseUri, "gpath?y")
        );
    }

    public function testResolveReferenceUriNormal9()
    {
        $this->assertEquals(
            "http://example.org/bpath/cpath/d;p?querystr#s",
            EasyRdf_Utils::resolveUriReference($this->baseUri, "#s")
        );
    }

    public function testResolveReferenceUriNormal10()
    {
        $this->assertEquals(
            "http://example.org/bpath/cpath/gpath#s",
            EasyRdf_Utils::resolveUriReference($this->baseUri, "gpath#s")
        );
    }

    public function testResolveReferenceUriNormal11()
    {
        $this->assertEquals(
            "http://example.org/bpath/cpath/gpath?y#s",
            EasyRdf_Utils::resolveUriReference($this->baseUri, "gpath?y#s")
        );
    }

    public function testResolveReferenceUriNormal12()
    {
        $this->assertEquals(
            "http://example.org/bpath/cpath/;x",
            EasyRdf_Utils::resolveUriReference($this->baseUri, ";x")
        );
    }

    public function testResolveReferenceUriNormal13()
    {
        $this->assertEquals(
            "http://example.org/bpath/cpath/gpath;x",
            EasyRdf_Utils::resolveUriReference($this->baseUri, "gpath;x")
        );
    }

    public function testResolveReferenceUriNormal14()
    {
        $this->assertEquals(
            "http://example.org/bpath/cpath/gpath;x?y#s",
            EasyRdf_Utils::resolveUriReference($this->baseUri, "gpath;x?y#s")
        );
    }

    public function testResolveReferenceUriNormal15()
    {
        $this->markTestSkipped('FIXME');
        $this->assertEquals(
            "http://example.org/bpath/cpath/",
            EasyRdf_Utils::resolveUriReference($this->baseUri, ".")
        );
    }

    public function testResolveReferenceUriNormal16()
    {
        $this->markTestSkipped('FIXME');
        $this->assertEquals(
            "http://example.org/bpath/cpath/",
            EasyRdf_Utils::resolveUriReference($this->baseUri, "./")
        );
    }

    public function testResolveReferenceUriNormal17()
    {
        $this->markTestSkipped('FIXME');
        $this->assertEquals(
            "http://example.org/bpath/",
            EasyRdf_Utils::resolveUriReference($this->baseUri, "..")
        );
    }

    public function testResolveReferenceUriNormal18()
    {
        $this->assertEquals(
            "http://example.org/bpath/",
            EasyRdf_Utils::resolveUriReference($this->baseUri, "../")
        );
    }

    public function testResolveReferenceUriNormal19()
    {
        $this->assertEquals(
            "http://example.org/bpath/gpath",
            EasyRdf_Utils::resolveUriReference($this->baseUri, "../gpath")
        );
    }

    public function testResolveReferenceUriNormal20()
    {
        $this->markTestSkipped('FIXME');
        $this->assertEquals(
            "http://example.org/",
            EasyRdf_Utils::resolveUriReference($this->baseUri, "../..")
        );
    }

    public function testResolveReferenceUriNormal21()
    {
        $this->assertEquals(
            "http://example.org/",
            EasyRdf_Utils::resolveUriReference($this->baseUri, "../../")
        );
    }

    public function testResolveReferenceUriNormal22()
    {
        $this->assertEquals(
            "http://example.org/gpath",
            EasyRdf_Utils::resolveUriReference($this->baseUri, "../../gpath")
        );
    }

    public function testResolveReferenceUriNormal23()
    {
        $this->assertEquals(
            "http:gauthority",
            EasyRdf_Utils::resolveUriReference($this->baseUri, "http:gauthority")
        );
    }


    /* RFC2396 Appendix C.2 / RFC3986 5.4.2 Abnormal Examples */
    public function testResolveReferenceUriAbnormal1()
    {
        $this->assertEquals(
            EasyRdf_Utils::resolveUriReference($this->baseUri, "../../../gpath"),
            "http://example.org/gpath"
        );
    }

    public function testResolveReferenceUriAbnormal2()
    {
        $this->assertEquals(
            EasyRdf_Utils::resolveUriReference($this->baseUri, "../../../../gpath"),
            "http://example.org/gpath"
        );
    }

    public function testResolveReferenceUriAbnormal3()
    {
        $this->markTestSkipped('FIXME');
        $this->assertEquals(
            EasyRdf_Utils::resolveUriReference($this->baseUri, "/./gpath"),
            "http://example.org/gpath"
        );
    }

    public function testResolveReferenceUriAbnormal4()
    {
        $this->markTestSkipped('FIXME');
        $this->assertEquals(
            EasyRdf_Utils::resolveUriReference($this->baseUri, "/../gpath"),
            "http://example.org/gpath"
        );
    }

    public function testResolveReferenceUriAbnormal5()
    {
        $this->assertEquals(
            EasyRdf_Utils::resolveUriReference($this->baseUri, "gpath."),
            "http://example.org/bpath/cpath/gpath."
        );
    }

    public function testResolveReferenceUriAbnormal6()
    {
        $this->assertEquals(
            EasyRdf_Utils::resolveUriReference($this->baseUri, ".gpath"),
            "http://example.org/bpath/cpath/.gpath"
        );
    }

    public function testResolveReferenceUriAbnormal7()
    {
        $this->assertEquals(
            EasyRdf_Utils::resolveUriReference($this->baseUri, "gpath.."),
            "http://example.org/bpath/cpath/gpath.."
        );
    }

    public function testResolveReferenceUriAbnormal8()
    {
        $this->assertEquals(
            EasyRdf_Utils::resolveUriReference($this->baseUri, "..gpath"),
            "http://example.org/bpath/cpath/..gpath"
        );
    }

    public function testResolveReferenceUriAbnormal9()
    {
        $this->assertEquals(
            EasyRdf_Utils::resolveUriReference($this->baseUri, "./../gpath"),
            "http://example.org/bpath/gpath"
        );
    }

    public function testResolveReferenceUriAbnormal10()
    {
        $this->markTestSkipped('FIXME');
        $this->assertEquals(
            EasyRdf_Utils::resolveUriReference($this->baseUri, "./gpath/."),
            "http://example.org/bpath/cpath/gpath/"
        );
    }

    public function testResolveReferenceUriAbnormal11()
    {
        $this->markTestSkipped('FIXME');
        $this->assertEquals(
            EasyRdf_Utils::resolveUriReference($this->baseUri, "gpath/./hpath"),
            "http://example.org/bpath/cpath/gpath/hpath"
        );
    }

    public function testResolveReferenceUriAbnormal12()
    {
        $this->markTestSkipped('FIXME');
        $this->assertEquals(
            EasyRdf_Utils::resolveUriReference($this->baseUri, "gpath/../hpath"),
            "http://example.org/bpath/cpath/hpath"
        );
    }

    public function testResolveReferenceUriAbnormal13()
    {
        $this->markTestSkipped('FIXME');
        $this->assertEquals(
            EasyRdf_Utils::resolveUriReference($this->baseUri, "gpath;x = 1/./y"),
            "http://example.org/bpath/cpath/gpath;x = 1/y"
        );
    }

    public function testResolveReferenceUriAbnormal14()
    {
        $this->markTestSkipped('FIXME');
        $this->assertEquals(
            EasyRdf_Utils::resolveUriReference($this->baseUri, "gpath;x = 1/../y"),
            "http://example.org/bpath/cpath/y"
        );
    }

    public function testResolveReferenceUriAbnormal15()
    {
        $this->assertEquals(
            EasyRdf_Utils::resolveUriReference($this->baseUri, "gpath?y/./x"),
            "http://example.org/bpath/cpath/gpath?y/./x"
        );
    }

    public function testResolveReferenceUriAbnormal16()
    {
        $this->assertEquals(
            EasyRdf_Utils::resolveUriReference($this->baseUri, "gpath?y/../x"),
            "http://example.org/bpath/cpath/gpath?y/../x"
        );
    }

    public function testResolveReferenceUriAbnormal17()
    {
        $this->assertEquals(
            EasyRdf_Utils::resolveUriReference($this->baseUri, "gpath#s/./x"),
            "http://example.org/bpath/cpath/gpath#s/./x"
        );
    }

    public function testResolveReferenceUriAbnormal18()
    {
        $this->assertEquals(
            EasyRdf_Utils::resolveUriReference($this->baseUri, "gpath#s/../x"),
            "http://example.org/bpath/cpath/gpath#s/../x"
        );
    }

    public function testResolveReferenceUriExtra1()
    {
        $this->assertEquals(
            EasyRdf_Utils::resolveUriReference(
                "http://example.org/dir/file",
                "../../../absfile"
            ),
            "http://example.org/absfile"
        );
    }

    public function testResolveReferenceUriExtra2()
    {
        $this->assertEquals(
            EasyRdf_Utils::resolveUriReference(
                "http://example.org/dir/file",
                "http://another.example.org/dir2/file2"
            ),
            "http://another.example.org/dir2/file2"
        );
    }

    public function testResolveReferenceUriExtra3()
    {
        $this->markTestSkipped('FIXME');
        $this->assertEquals(
            EasyRdf_Utils::resolveUriReference(
                "foo:",
                "not_scheme:blah"
            ),
            "foo:not_scheme:blah"
        );
    }

    public function testResolveReferenceUriExtra4()
    {
        $this->markTestSkipped('FIXME');
        $this->assertEquals(
            EasyRdf_Utils::resolveUriReference(
                "foo:1234",
                "9999"
            ),
            "foo:9999"
        );
    }

    public function testDumpResourceValue()
    {
        $res = new EasyRdf_Resource('http://www.example.com/');
        $this->assertEquals(
            "http://www.example.com/",
            EasyRdf_Utils::dumpResourceValue($res, false)
        );
        $this->assertEquals(
            "<a href='http://www.example.com/' ".
            "style='text-decoration:none;color:blue'>".
            "http://www.example.com/</a>",
            EasyRdf_Utils::dumpResourceValue($res, true)
        );
    }

    public function testDumpResourceValueFromArray()
    {
        $res = array('type' => 'uri', 'value' => 'http://www.example.com/');
        $this->assertEquals(
            "http://www.example.com/",
            EasyRdf_Utils::dumpResourceValue($res, false)
        );
        $this->assertEquals(
            "<a href='http://www.example.com/' ".
            "style='text-decoration:none;color:blue'>".
            "http://www.example.com/</a>",
            EasyRdf_Utils::dumpResourceValue($res, true)
        );
    }

    public function testDumpLiteralValue()
    {
        $literal = new EasyRdf_Literal("hello & world");
        $this->assertEquals(
            '"hello & world"',
            EasyRdf_Utils::dumpLiteralValue($literal, false)
        );
        $this->assertEquals(
            "<span style='color:black'>&quot;hello &amp; world&quot;</span>",
            EasyRdf_Utils::dumpLiteralValue($literal, true)
        );
    }

    public function testDumpLiteralValueFromArray()
    {
        $literal = array('type' => 'literal', 'value' => 'Hot Sauce');
        $this->assertEquals(
            '"Hot Sauce"',
            EasyRdf_Utils::dumpLiteralValue($literal, false)
        );
        $this->assertEquals(
            "<span style='color:black'>&quot;Hot Sauce&quot;</span>",
            EasyRdf_Utils::dumpLiteralValue($literal, true)
        );
    }

    public function testDumpLiteralValueFromString()
    {
        $literal = 'a string';
        $this->assertEquals(
            '"a string"',
            EasyRdf_Utils::dumpLiteralValue($literal, false)
        );
        $this->assertEquals(
            "<span style='color:black'>&quot;a string&quot;</span>",
            EasyRdf_Utils::dumpLiteralValue($literal, true)
        );
    }

    public function testDumpLiteralValueWithLanguage()
    {
        $literal = array('type' => 'literal', 'value' => 'Nick', 'lang' => 'en');
        $this->assertEquals(
            '"Nick"@en',
            EasyRdf_Utils::dumpLiteralValue($literal, false)
        );
        $this->assertEquals(
            "<span style='color:black'>&quot;Nick&quot;@en</span>",
            EasyRdf_Utils::dumpLiteralValue($literal, true)
        );
    }

    public function testDumpLiteralValueWithDatatype()
    {
        $literal = array(
            'type' => 'literal',
            'value' => '1',
            'datatype' => 'http://www.w3.org/2001/XMLSchema#integer'
        );
        $this->assertEquals(
            '"1"^^xsd:integer',
            EasyRdf_Utils::dumpLiteralValue($literal, false)
        );
        $this->assertEquals(
            "<span style='color:black'>&quot;1&quot;^^xsd:integer</span>",
            EasyRdf_Utils::dumpLiteralValue($literal, true)
        );
    }

    public function testParseMimeTypeBasic()
    {
        list($type) = EasyRdf_Utils::parseMimeType('text/plain');
        $this->assertEquals('text/plain', $type);
    }

    public function testParseMimeTypeMixedCase()
    {
        list($type) = EasyRdf_Utils::parseMimeType('TEXT/Plain');
        $this->assertEquals('text/plain', $type);
    }

    public function testParseMimeTypeBasicWithWhitespace()
    {
        list($type) = EasyRdf_Utils::parseMimeType(' text/plain  ');
        $this->assertEquals('text/plain', $type);
    }

    public function testParseMimeTypeBasicWithCharset()
    {
        list($type, $params) = EasyRdf_Utils::parseMimeType('text/plain;charset=utf8');
        $this->assertEquals('text/plain', $type);
        $this->assertEquals('utf8', $params['charset']);
    }

    public function testParseMimeTypeBasicWithMixedcaseCharset()
    {
        list($type, $params) = EasyRdf_Utils::parseMimeType('text/plain;charset=UTF8');
        $this->assertEquals('text/plain', $type);
        $this->assertEquals('utf8', $params['charset']);
    }

    public function testParseMimeTypeBasicWithCharsetAndWhitespace()
    {
        list($type, $params) = EasyRdf_Utils::parseMimeType(' text/plain ; charset = utf8 ');
        $this->assertEquals('text/plain', $type);
        $this->assertEquals('utf8', $params['charset']);
    }
}
