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

class EasyRdf_ParsedUriTest extends EasyRdf_TestCase
{
    public function setup()
    {
        $this->baseUri = new EasyRdf_ParsedUri(
            'http://a/b/c/d;p?q'
        );
    }

    public function testConstruct()
    {
        $uri = new EasyRdf_ParsedUri(
            array(
                'scheme' => 'http',
                'authority' => 'example.com',
                'path' => '/foo/bar',
                'query' => 'k=v',
                'fragment' => 'frag'
            )
        );
        $this->assertStringEquals('http://example.com/foo/bar?k=v#frag', $uri);
    }

    public function testParseHttp()
    {
        $uri = new EasyRdf_ParsedUri('http://www.ietf.org/rfc/rfc2396.txt');
        $this->assertSame('http', $uri->getScheme());
        $this->assertSame('www.ietf.org', $uri->getAuthority());
        $this->assertSame('/rfc/rfc2396.txt', $uri->getPath());
        $this->assertSame(null, $uri->getQuery());
        $this->assertSame(null, $uri->getFragment());
        $this->assertStringEquals('http://www.ietf.org/rfc/rfc2396.txt', $uri);
        $this->assertTrue($uri->isAbsolute());
    }

    public function testParseFile()
    {
        $uri = new EasyRdf_ParsedUri('file:///etc/hosts');
        $this->assertSame('file', $uri->getScheme());
        $this->assertSame('', $uri->getAuthority());
        $this->assertSame('/etc/hosts', $uri->getPath());
        $this->assertSame(null, $uri->getQuery());
        $this->assertSame(null, $uri->getFragment());
        $this->assertStringEquals('file:///etc/hosts', $uri);
        $this->assertTrue($uri->isAbsolute());
    }

    public function testParseFtp()
    {
        $uri = new EasyRdf_ParsedUri('ftp://ftp.is.co.za/rfc/rfc1808.txt');
        $this->assertSame('ftp', $uri->getScheme());
        $this->assertSame('ftp.is.co.za', $uri->getAuthority());
        $this->assertSame('/rfc/rfc1808.txt', $uri->getPath());
        $this->assertSame(null, $uri->getQuery());
        $this->assertSame(null, $uri->getFragment());
        $this->assertStringEquals('ftp://ftp.is.co.za/rfc/rfc1808.txt', $uri);
        $this->assertTrue($uri->isAbsolute());
    }

    public function testParseLdap()
    {
        $uri = new EasyRdf_ParsedUri('ldap://[2001:db8::7]/c=GB?objectClass?one');
        $this->assertSame('ldap', $uri->getScheme());
        $this->assertSame('[2001:db8::7]', $uri->getAuthority());
        $this->assertSame('/c=GB', $uri->getPath());
        $this->assertSame('objectClass?one', $uri->getQuery());
        $this->assertSame(null, $uri->getFragment());
        $this->assertStringEquals('ldap://[2001:db8::7]/c=GB?objectClass?one', $uri);
        $this->assertTrue($uri->isAbsolute());
    }

    public function testParseMailto()
    {
        $uri = new EasyRdf_ParsedUri('mailto:John.Doe@example.com');
        $this->assertSame('mailto', $uri->getScheme());
        $this->assertSame(null, $uri->getAuthority());
        $this->assertSame('John.Doe@example.com', $uri->getPath());
        $this->assertSame(null, $uri->getQuery());
        $this->assertSame(null, $uri->getFragment());
        $this->assertStringEquals('mailto:John.Doe@example.com', $uri);
        $this->assertTrue($uri->isAbsolute());
    }

    public function testParseNews()
    {
        $uri = new EasyRdf_ParsedUri('news:comp.infosystems.www.servers.unix');
        $this->assertSame('news', $uri->getScheme());
        $this->assertSame(null, $uri->getAuthority());
        $this->assertSame('comp.infosystems.www.servers.unix', $uri->getPath());
        $this->assertSame(null, $uri->getQuery());
        $this->assertSame(null, $uri->getFragment());
        $this->assertStringEquals('news:comp.infosystems.www.servers.unix', $uri);
        $this->assertTrue($uri->isAbsolute());
    }

    public function testParseTel()
    {
        $uri = new EasyRdf_ParsedUri('tel:+1-816-555-1212');
        $this->assertSame('tel', $uri->getScheme());
        $this->assertSame(null, $uri->getAuthority());
        $this->assertSame('+1-816-555-1212', $uri->getPath());
        $this->assertSame(null, $uri->getQuery());
        $this->assertSame(null, $uri->getFragment());
        $this->assertStringEquals('tel:+1-816-555-1212', $uri);
        $this->assertTrue($uri->isAbsolute());
    }

    public function testParseTelnet()
    {
        $uri = new EasyRdf_ParsedUri('telnet://192.0.2.16:80/');
        $this->assertSame('telnet', $uri->getScheme());
        $this->assertSame('192.0.2.16:80', $uri->getAuthority());
        $this->assertSame('/', $uri->getPath());
        $this->assertSame(null, $uri->getQuery());
        $this->assertSame(null, $uri->getFragment());
        $this->assertStringEquals('telnet://192.0.2.16:80/', $uri);
        $this->assertTrue($uri->isAbsolute());
    }

    public function testParseUrn()
    {
        $uri = new EasyRdf_ParsedUri('urn:oasis:names:specification:docbook:dtd:xml:4.1.2');
        $this->assertSame('urn', $uri->getScheme());
        $this->assertSame(null, $uri->getAuthority());
        $this->assertSame('oasis:names:specification:docbook:dtd:xml:4.1.2', $uri->getPath());
        $this->assertSame(null, $uri->getQuery());
        $this->assertSame(null, $uri->getFragment());
        $this->assertStringEquals('urn:oasis:names:specification:docbook:dtd:xml:4.1.2', $uri);
        $this->assertTrue($uri->isAbsolute());
    }

    public function testParseRelative()
    {
        $uri = new EasyRdf_ParsedUri('/foo/bar');
        $this->assertSame(null, $uri->getScheme());
        $this->assertSame(null, $uri->getAuthority());
        $this->assertSame('/foo/bar', $uri->getPath());
        $this->assertSame(null, $uri->getQuery());
        $this->assertSame(null, $uri->getFragment());
        $this->assertStringEquals('/foo/bar', $uri);
        $this->assertTrue($uri->isRelative());
    }

    public function testParseEmptyFragment()
    {
        $uri = new EasyRdf_ParsedUri('http://www.example.com/foo#');
        $this->assertSame('http', $uri->getScheme());
        $this->assertSame('www.example.com', $uri->getAuthority());
        $this->assertSame('/foo', $uri->getPath());
        $this->assertSame(null, $uri->getQuery());
        $this->assertSame('', $uri->getFragment());
        $this->assertStringEquals('http://www.example.com/foo#', $uri);
        $this->assertTrue($uri->isAbsolute());
    }

    public function testParseEmptyQuery()
    {
        $uri = new EasyRdf_ParsedUri('http://www.example.com/foo?');
        $this->assertSame('http', $uri->getScheme());
        $this->assertSame('www.example.com', $uri->getAuthority());
        $this->assertSame('/foo', $uri->getPath());
        $this->assertSame('', $uri->getQuery());
        $this->assertSame(null, $uri->getFragment());
        $this->assertStringEquals('http://www.example.com/foo?', $uri);
        $this->assertTrue($uri->isAbsolute());
    }

    public function testNormaliseDotSegments()
    {
        $uri = new EasyRdf_ParsedUri('http://www.example.com/foo/././bar/.');
        $uri->normalise();
        $this->assertSame('/foo/bar/', $uri->getPath());
    }

    public function testNormaliseInitalDot()
    {
        $uri = new EasyRdf_ParsedUri('./foo/bar');
        $uri->normalise();
        $this->assertSame('foo/bar', $uri->getPath());
    }

    public function testNormaliseOneParent()
    {
        $uri = new EasyRdf_ParsedUri('http://www.example.com/foo/bar/../file');
        $uri->normalise();
        $this->assertSame('/foo/file', $uri->getPath());
    }

    public function testNormaliseTwoParents()
    {
        $uri = new EasyRdf_ParsedUri('http://www.example.com/foo/bar/../../file');
        $uri->normalise();
        $this->assertSame('/file', $uri->getPath());
    }

    public function testNormaliseThreeParents()
    {
        $uri = new EasyRdf_ParsedUri('http://www.example.com/foo/bar/../../../file');
        $uri->normalise();
        $this->assertSame('/file', $uri->getPath());
    }

    public function testNormaliseMixed()
    {
        $uri = new EasyRdf_ParsedUri('http://example.com/a/b/../c/./d/.');
        $uri->normalise();
        $this->assertStringEquals('http://example.com/a/c/d/', $uri);
    }

    public function testResolve()
    {
        $base = new EasyRdf_ParsedUri('http://example.com');
        $this->assertStringEquals(
            "http://example.com/filename",
            $base->resolve("filename")
        );
    }

    public function testToString()
    {
        $uri = new EasyRdf_ParsedUri('http://example.com/foo/bar?q#f');
        $this->assertSame(
            "http://example.com/foo/bar?q#f",
            $uri->toString()
        );
    }

    public function testSetScheme()
    {
        $uri = new EasyRdf_ParsedUri('http://www.ietf.org/rfc/rfc2396.txt');
        $uri->setScheme('https');
        $this->assertStringEquals('https://www.ietf.org/rfc/rfc2396.txt', $uri);
    }

    public function testSetAuthority()
    {
        $uri = new EasyRdf_ParsedUri('http://www.ietf.org/rfc/rfc2396.txt');
        $uri->setAuthority('example.com');
        $this->assertStringEquals('http://example.com/rfc/rfc2396.txt', $uri);
    }

    public function testSetPath()
    {
        $uri = new EasyRdf_ParsedUri('http://www.ietf.org/rfc/rfc2396.txt');
        $uri->setPath('/foobar');
        $this->assertStringEquals('http://www.ietf.org/foobar', $uri);
    }

    public function testSetQuery()
    {
        $uri = new EasyRdf_ParsedUri('http://www.ietf.org/rfc/rfc2396.txt');
        $uri->setQuery('foo=bar');
        $this->assertStringEquals('http://www.ietf.org/rfc/rfc2396.txt?foo=bar', $uri);
    }

    public function testSetQueryEmpty()
    {
        $uri = new EasyRdf_ParsedUri('http://www.ietf.org/rfc/rfc2396.txt?foo=bar');
        $uri->setQuery('');
        $this->assertStringEquals('http://www.ietf.org/rfc/rfc2396.txt?', $uri);
    }

    public function testSetQueryNull()
    {
        $uri = new EasyRdf_ParsedUri('http://www.ietf.org/rfc/rfc2396.txt?foo=bar');
        $uri->setQuery(null);
        $this->assertStringEquals('http://www.ietf.org/rfc/rfc2396.txt', $uri);
    }

    public function testSetFragment()
    {
        $uri = new EasyRdf_ParsedUri('http://www.ietf.org/rfc/rfc2396.txt');
        $uri->setFragment('foobar');
        $this->assertStringEquals('http://www.ietf.org/rfc/rfc2396.txt#foobar', $uri);
    }

    public function testSetFragmentEmpty()
    {
        $uri = new EasyRdf_ParsedUri('http://www.ietf.org/rfc/rfc2396.txt#foobar');
        $uri->setFragment('');
        $this->assertStringEquals('http://www.ietf.org/rfc/rfc2396.txt#', $uri);
    }

    public function testSetFragmentNull()
    {
        $uri = new EasyRdf_ParsedUri('http://www.ietf.org/rfc/rfc2396.txt#foobar');
        $uri->setFragment(null);
        $this->assertStringEquals('http://www.ietf.org/rfc/rfc2396.txt', $uri);
    }

    /**
     * Tests from RFC3986 Section 5
     */

    /*  RFC3986 5.4.1 Normal Examples */
    public function testResolveReferenceUriNormal1()
    {
        $this->assertStringEquals(
            "g:h",
            $this->baseUri->resolve("g:h")
        );
    }

    public function testResolveReferenceUriNormal2()
    {
        $this->assertStringEquals(
            "http://a/b/c/g",
            $this->baseUri->resolve("g")
        );
    }

    public function testResolveReferenceUriNormal3()
    {
        $this->assertStringEquals(
            "http://a/b/c/g",
            $this->baseUri->resolve("./g")
        );
    }

    public function testResolveReferenceUriNormal4()
    {
        $this->assertStringEquals(
            "http://a/b/c/g/",
            $this->baseUri->resolve("g/")
        );
    }

    public function testResolveReferenceUriNormal5()
    {
        $this->assertStringEquals(
            "http://a/g",
            $this->baseUri->resolve("/g")
        );
    }

    public function testResolveReferenceUriNormal6()
    {
        $this->assertStringEquals(
            "http://g",
            $this->baseUri->resolve("//g")
        );
    }

    public function testResolveReferenceUriNormal7()
    {
        $this->assertStringEquals(
            "http://a/b/c/d;p?y",
            $this->baseUri->resolve("?y")
        );
    }

    public function testResolveReferenceUriNormal8()
    {
        $this->assertStringEquals(
            "http://a/b/c/g?y",
            $this->baseUri->resolve("g?y")
        );
    }

    public function testResolveReferenceUriNormal9()
    {
        $this->assertStringEquals(
            "http://a/b/c/d;p?q#s",
            $this->baseUri->resolve("#s")
        );
    }

    public function testResolveReferenceUriNormal10()
    {
        $this->assertStringEquals(
            "http://a/b/c/g#s",
            $this->baseUri->resolve("g#s")
        );
    }

    public function testResolveReferenceUriNormal11()
    {
        $this->assertStringEquals(
            "http://a/b/c/g?y#s",
            $this->baseUri->resolve("g?y#s")
        );
    }

    public function testResolveReferenceUriNormal12()
    {
        $this->assertStringEquals(
            "http://a/b/c/g;x",
            $this->baseUri->resolve("g;x")
        );
    }

    public function testResolveReferenceUriNormal13()
    {
        $this->assertStringEquals(
            "http://a/b/c/g;x",
            $this->baseUri->resolve("g;x")
        );
    }

    public function testResolveReferenceUriNormal14()
    {
        $this->assertStringEquals(
            "http://a/b/c/g;x?y#s",
            $this->baseUri->resolve("g;x?y#s")
        );
    }

    public function testResolveReferenceUriNormal15()
    {
        $this->assertStringEquals(
            "http://a/b/c/d;p?q",
            $this->baseUri->resolve("")
        );
    }

    public function testResolveReferenceUriNormal16()
    {
        $this->assertStringEquals(
            "http://a/b/c/",
            $this->baseUri->resolve(".")
        );
    }

    public function testResolveReferenceUriNormal17()
    {
        $this->assertStringEquals(
            "http://a/b/c/",
            $this->baseUri->resolve("./")
        );
    }

    public function testResolveReferenceUriNormal18()
    {
        $this->assertStringEquals(
            "http://a/b/",
            $this->baseUri->resolve("..")
        );
    }

    public function testResolveReferenceUriNormal19()
    {
        $this->assertStringEquals(
            "http://a/b/",
            $this->baseUri->resolve("../")
        );
    }

    public function testResolveReferenceUriNormal20()
    {
        $this->assertStringEquals(
            "http://a/b/g",
            $this->baseUri->resolve("../g")
        );
    }

    public function testResolveReferenceUriNormal21()
    {
        $this->assertStringEquals(
            "http://a/",
            $this->baseUri->resolve("../..")
        );
    }

    public function testResolveReferenceUriNormal22()
    {
        $this->assertStringEquals(
            "http://a/",
            $this->baseUri->resolve("../../")
        );
    }

    public function testResolveReferenceUriNormal23()
    {
        $this->assertStringEquals(
            "http://a/g",
            $this->baseUri->resolve("../../g")
        );
    }


    /* RFC3986 5.4.2 Abnormal Examples */
    public function testResolveReferenceUriAbnormal1()
    {
        $this->assertStringEquals(
            "http://a/g",
            $this->baseUri->resolve("../../../g")
        );
    }

    public function testResolveReferenceUriAbnormal2()
    {
        $this->assertStringEquals(
            "http://a/g",
            $this->baseUri->resolve("../../../../g")
        );
    }

    public function testResolveReferenceUriAbnormal3()
    {
        $this->assertStringEquals(
            "http://a/g",
            $this->baseUri->resolve("/./g")
        );
    }

    public function testResolveReferenceUriAbnormal4()
    {
        $this->assertStringEquals(
            "http://a/g",
            $this->baseUri->resolve("/../g")
        );
    }

    public function testResolveReferenceUriAbnormal5()
    {
        $this->assertStringEquals(
            "http://a/b/c/g.",
            $this->baseUri->resolve("g.")
        );
    }

    public function testResolveReferenceUriAbnormal6()
    {
        $this->assertStringEquals(
            "http://a/b/c/.g",
            $this->baseUri->resolve(".g")
        );
    }

    public function testResolveReferenceUriAbnormal7()
    {
        $this->assertStringEquals(
            "http://a/b/c/g..",
            $this->baseUri->resolve("g..")
        );
    }

    public function testResolveReferenceUriAbnormal8()
    {
        $this->assertStringEquals(
            "http://a/b/c/..g",
            $this->baseUri->resolve("..g")
        );
    }

    public function testResolveReferenceUriAbnormal9()
    {
        $this->assertStringEquals(
            "http://a/b/g",
            $this->baseUri->resolve("./../g")
        );
    }

    public function testResolveReferenceUriAbnormal10()
    {
        $this->assertStringEquals(
            "http://a/b/c/g/",
            $this->baseUri->resolve("./g/.")
        );
    }

    public function testResolveReferenceUriAbnormal11()
    {
        $this->assertStringEquals(
            "http://a/b/c/g/h",
            $this->baseUri->resolve("g/./h")
        );
    }

    public function testResolveReferenceUriAbnormal12()
    {
        $this->assertStringEquals(
            "http://a/b/c/h",
            $this->baseUri->resolve("g/../h")
        );
    }

    public function testResolveReferenceUriAbnormal13()
    {
        $this->assertStringEquals(
            "http://a/b/c/g;x=1/y",
            $this->baseUri->resolve("g;x=1/./y")
        );
    }

    public function testResolveReferenceUriAbnormal14()
    {
        $this->assertStringEquals(
            "http://a/b/c/y",
            $this->baseUri->resolve("g;x=1/../y")
        );
    }

    public function testResolveReferenceUriAbnormal15()
    {
        $this->assertStringEquals(
            "http://a/b/c/y",
            $this->baseUri->resolve("g;x=1/../y")
        );
    }

    public function testResolveReferenceUriAbnormal16()
    {
        $this->assertStringEquals(
            "http://a/b/c/g?y/./x",
            $this->baseUri->resolve("g?y/./x")
        );
    }

    public function testResolveReferenceUriAbnormal17()
    {
        $this->assertStringEquals(
            "http://a/b/c/g?y/../x",
            $this->baseUri->resolve("g?y/../x")
        );
    }

    public function testResolveReferenceUriAbnormal18()
    {
        $this->assertStringEquals(
            "http://a/b/c/g#s/./x",
            $this->baseUri->resolve("g#s/./x")
        );
    }

    public function testResolveReferenceUriAbnormal19()
    {
        $this->assertStringEquals(
            "http://a/b/c/g#s/../x",
            $this->baseUri->resolve("g#s/../x")
        );
    }

    public function testResolveReferenceUriAbnormal20()
    {
        $this->assertStringEquals(
            "http:g",
            $this->baseUri->resolve("http:g")
        );
    }
}
