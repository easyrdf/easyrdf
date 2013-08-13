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

class EasyRdf_NamespaceTest extends EasyRdf_TestCase
{
    public function setUp()
    {
        EasyRdf_Namespace::setDefault(null);
        $this->graph = new EasyRdf_Graph();
        $this->resource = $this->graph->resource('http://xmlns.com/foaf/0.1/name');
    }

    public function tearDown()
    {
        EasyRdf_Namespace::delete('po');
        EasyRdf_Namespace::reset();
    }

    public function testNamespaces()
    {
        $ns = EasyRdf_Namespace::namespaces();
        $this->assertSame('http://purl.org/dc/terms/', $ns['dc']);
        $this->assertSame('http://xmlns.com/foaf/0.1/', $ns['foaf']);
    }

    public function testGetDcNamespace()
    {
        $this->assertSame(
            'http://purl.org/dc/terms/',
            EasyRdf_Namespace::get('dc')
        );
    }

    public function testGetFoafNamespace()
    {
        $this->assertSame(
            'http://xmlns.com/foaf/0.1/',
            EasyRdf_Namespace::get('foaf')
        );
    }

    public function testGetRdfNamespace()
    {
        $this->assertSame(
            'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
            EasyRdf_Namespace::get('rdf')
        );
    }

    public function testGetRdfsNamespace()
    {
        $this->assertSame(
            'http://www.w3.org/2000/01/rdf-schema#',
            EasyRdf_Namespace::get('rdfs')
        );
    }

    public function testGetXsdNamespace()
    {
        $this->assertSame(
            'http://www.w3.org/2001/XMLSchema#',
            EasyRdf_Namespace::get('xsd')
        );
    }

    public function testGetUpperCaseFoafNamespace()
    {
        $this->assertSame(
            'http://xmlns.com/foaf/0.1/',
            EasyRdf_Namespace::get('FOAF')
        );
    }

    public function testGetNullNamespace()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$prefix should be a string and cannot be null or empty'
        );
        EasyRdf_Namespace::get(null);
    }

    public function testGetEmptyNamespace()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$prefix should be a string and cannot be null or empty'
        );
        EasyRdf_Namespace::get('');
    }

    public function testGetNonStringNamespace()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$prefix should be a string and cannot be null or empty'
        );
        EasyRdf_Namespace::get(array());
    }

    public function testGetNonAlphanumeric()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$prefix should only contain alpha-numeric characters'
        );
        EasyRdf_Namespace::get('/K.O/');
    }

    public function testAddNamespace()
    {
        EasyRdf_Namespace::set('po', 'http://purl.org/ontology/po/');
        $this->assertSame(
            'http://purl.org/ontology/po/',
            EasyRdf_Namespace::get('po')
        );
    }

    public function testAddUppercaseNamespace()
    {
        EasyRdf_Namespace::set('PO', 'http://purl.org/ontology/po/');
        $this->assertSame(
            'http://purl.org/ontology/po/',
            EasyRdf_Namespace::get('po')
        );
    }

    public function testAddNamespaceShortNull()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$prefix should be a string and cannot be null or empty'
        );
        EasyRdf_Namespace::set(null, 'http://purl.org/ontology/ko/');
    }

    public function testAddNamespaceShortEmpty()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$prefix should be a string and cannot be null or empty'
        );
        EasyRdf_Namespace::set('', 'http://purl.org/ontology/ko/');
    }

    public function testAddNamespaceShortNonString()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$prefix should be a string and cannot be null or empty'
        );
        EasyRdf_Namespace::set(array(), 'http://purl.org/ontology/ko/');
    }

    public function testAddNamespaceShortNonAlphanumeric()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$prefix should only contain alpha-numeric characters'
        );
        EasyRdf_Namespace::set('/K.O/', 'http://purl.org/ontology/ko/');
    }

    public function testAddNamespaceLongNull()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$long should be a string and cannot be null or empty'
        );
        EasyRdf_Namespace::set('ko', null);
    }

    public function testAddNamespaceLongEmpty()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$long should be a string and cannot be null or empty'
        );
        EasyRdf_Namespace::set('ko', '');
    }

    public function testAddNamespaceLongNonString()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$long should be a string and cannot be null or empty'
        );
        EasyRdf_Namespace::set('ko', array());
    }

    public function testDeleteNamespace()
    {
        EasyRdf_Namespace::set('po', 'http://purl.org/ontology/po/');
        $this->assertNotNull(EasyRdf_Namespace::get('po'));
        EasyRdf_Namespace::delete('po');
        $this->assertNull(EasyRdf_Namespace::get('po'));
    }

    public function testDeleteEmptyNamespace()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$prefix should be a string and cannot be null or empty'
        );
        EasyRdf_Namespace::delete('');
    }

    public function testDeleteNullNamespace()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$prefix should be a string and cannot be null or empty'
        );
        EasyRdf_Namespace::delete(null);
    }

    public function testDeleteNonStringNamespace()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$prefix should be a string and cannot be null or empty'
        );
        EasyRdf_Namespace::delete($this);
    }

    public function testSetDefaultUri()
    {
        EasyRdf_Namespace::setDefault('http://ogp.me/ns#');
        $this->assertSame(
            'http://ogp.me/ns#',
            EasyRdf_Namespace::getDefault()
        );
    }

    public function testSetDefaultPrefix()
    {
        EasyRdf_Namespace::setDefault('foaf');
        $this->assertSame(
            'http://xmlns.com/foaf/0.1/',
            EasyRdf_Namespace::getDefault()
        );
    }

    public function testSetDefaultEmpty()
    {
        EasyRdf_Namespace::setDefault('http://ogp.me/ns#');
        EasyRdf_Namespace::setDefault('');
        $this->assertSame(null, EasyRdf_Namespace::getDefault());
    }

    public function testSetDefaultUnknown()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Unable to set default namespace to unknown prefix: foobar'
        );
        EasyRdf_Namespace::setDefault('foobar');
    }

    public function testSplitUriFoafName()
    {
        $this->assertSame(
            array('foaf', 'name'),
            EasyRdf_Namespace::splitUri('http://xmlns.com/foaf/0.1/name')
        );
    }

    public function testSplitUriResource()
    {
        $this->assertSame(
            array('foaf','name'),
            EasyRdf_Namespace::splitUri($this->resource)
        );
    }

    public function testSlitUriUnknown()
    {
        $this->assertSame(
            null,
            EasyRdf_Namespace::splitUri('http://example.com/ns/foo/bar')
        );
    }

    public function testSplitUriAndCreateOneUnknown()
    {
        $this->assertSame(
            array('ns0', 'bar'),
            EasyRdf_Namespace::splitUri('http://example.com/ns/foo/bar', true)
        );
    }

    public function testSplitUriAndCreateTwice()
    {
        $this->assertSame(
            array('ns0', 'bar'),
            EasyRdf_Namespace::splitUri('http://example.com/ns/foo/bar', true)
        );
        $this->assertSame(
            array('ns0', 'bar'),
            EasyRdf_Namespace::splitUri('http://example.com/ns/foo/bar', true)
        );
    }

    public function testSplitUriAndCreateTwoUnknown()
    {
        $this->assertSame(
            array('ns0', 'bar'),
            EasyRdf_Namespace::splitUri('http://example1.org/ns/foo/bar', true)
        );
        $this->assertSame(
            array('ns1', 'bar'),
            EasyRdf_Namespace::splitUri('http://example2.org/ns/foo/bar', true)
        );
    }

    public function testSplitUriUnsplitable()
    {
        $this->assertSame(
            null,
            EasyRdf_Namespace::splitUri('http://example.com/foo/', true)
        );
    }

    public function testSplitUriNull()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$uri cannot be null or empty'
        );
        EasyRdf_Namespace::splitUri(null);
    }

    public function testSplitUriEmpty()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$uri cannot be null or empty'
        );
        EasyRdf_Namespace::splitUri('');
    }

    public function testSplitUriNonString()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$uri should be a string or EasyRdf_Resource'
        );
        EasyRdf_Namespace::splitUri($this);
    }


    public function testShortenFoafName()
    {
        $this->assertSame(
            'foaf:name',
            EasyRdf_Namespace::shorten('http://xmlns.com/foaf/0.1/name')
        );
    }

    public function testShortenResource()
    {
        $this->assertSame('foaf:name', EasyRdf_Namespace::shorten($this->resource));
    }

    public function testShortenUnknown()
    {
        $this->assertSame(
            null,
            EasyRdf_Namespace::shorten('http://example.com/ns/foo/bar')
        );
    }

    public function testShortenAndCreateOneUnknown()
    {
        $this->assertSame(
            'ns0:bar',
            EasyRdf_Namespace::shorten('http://example.com/ns/foo/bar', true)
        );
    }

    public function testShortenAndCreateTwoUnknown()
    {
        $this->assertSame(
            'ns0:bar',
            EasyRdf_Namespace::shorten('http://example.com/ns/foo/bar', true)
        );
        $this->assertSame(
            'ns1:bar',
            EasyRdf_Namespace::shorten('http://example.org/ns/foo/bar', true)
        );
    }

    public function testShortenUnshortenable()
    {
        $this->assertSame(
            null,
            EasyRdf_Namespace::shorten('http://example.com/foo/', true)
        );
    }

    public function testShortenNull()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$uri cannot be null or empty'
        );
        EasyRdf_Namespace::shorten(null);
    }

    public function testShortenEmpty()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$uri cannot be null or empty'
        );
        EasyRdf_Namespace::shorten('');
    }

    public function testShortenNonString()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$uri should be a string or EasyRdf_Resource'
        );
        EasyRdf_Namespace::shorten($this);
    }

    public function testPrefixOfUriFoafName()
    {
        $this->assertSame(
            'foaf',
            EasyRdf_Namespace::prefixOfUri('http://xmlns.com/foaf/0.1/name')
        );
    }

    public function testrefixOfUriForResource()
    {
        $this->assertSame(
            'foaf',
            EasyRdf_Namespace::prefixOfUri($this->resource)
        );
    }

    public function testPrefixOfUnknownUrl()
    {
        $this->assertSame(
            null,
            EasyRdf_Namespace::prefixOfUri('http://www.aelius.com/njh/')
        );
    }

    public function testPrefixOfUriNull()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$uri cannot be null or empty'
        );
        EasyRdf_Namespace::prefixOfUri(null);
    }

    public function testPrefixOfUriEmpty()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$uri cannot be null or empty'
        );
        EasyRdf_Namespace::prefixOfUri('');
    }

    public function testPrefixOfUriNonString()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$uri should be a string or EasyRdf_Resource'
        );
        EasyRdf_Namespace::prefixOfUri(array());
    }

    public function testExpandFoafName()
    {
        $this->assertSame(
            'http://xmlns.com/foaf/0.1/name',
            EasyRdf_Namespace::expand('foaf:name')
        );
    }

    public function testExpandZero()
    {
        $this->assertSame(
            '0',
            EasyRdf_Namespace::expand('0')
        );
    }

    public function testExpandA()
    {
        $this->assertSame(
            'http://www.w3.org/1999/02/22-rdf-syntax-ns#type',
            EasyRdf_Namespace::expand('a')
        );
    }

    public function testExpandFoafDoapProgrammingLanguage()
    {
        $this->assertSame(
            'http://usefulinc.com/ns/doap#programming-language',
            EasyRdf_Namespace::expand('doap:programming-language')
        );
    }

    public function testExpandWithDefaultUri()
    {
        EasyRdf_Namespace::setDefault('http://ogp.me/ns#');
        $this->assertSame(
            'http://ogp.me/ns#title',
            EasyRdf_Namespace::expand('title')
        );
    }

    public function testExpandWithDefaultPrefix()
    {
        EasyRdf_Namespace::setDefault('foaf');
        $this->assertSame(
            'http://xmlns.com/foaf/0.1/name',
            EasyRdf_Namespace::expand('name')
        );
    }

    public function testExpandZeroWithDefaultPrefix()
    {
        EasyRdf_Namespace::setDefault('foaf');
        $this->assertSame(
            'http://xmlns.com/foaf/0.1/0',
            EasyRdf_Namespace::expand('0')
        );
    }

    public function testExpandWithoutDefault()
    {
        EasyRdf_Namespace::setDefault(null);
        $this->assertSame(
            'unknown',
            EasyRdf_Namespace::expand('unknown')
        );
    }

    public function testExpandExpanded()
    {
        $this->assertSame(
            'http://www.aelius.com/njh/',
            EasyRdf_Namespace::expand('http://www.aelius.com/njh/')
        );
    }

    public function testExpandURN()
    {
        $this->assertSame(
            'urn:isbn:0451450523',
            EasyRdf_Namespace::expand('urn:isbn:0451450523')
        );
    }

    public function testExpandNull()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$shortUri should be a string and cannot be null or empty'
        );
        EasyRdf_Namespace::expand(null);
    }

    public function testExpandEmpty()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$shortUri should be a string and cannot be null or empty'
        );
        EasyRdf_Namespace::expand('');
    }

    public function testExpandNonString()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$shortUri should be a string and cannot be null or empty'
        );
        EasyRdf_Namespace::expand($this);
    }
}
