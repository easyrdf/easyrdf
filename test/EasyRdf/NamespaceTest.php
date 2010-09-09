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

class EasyRdf_NamespaceTest extends EasyRdf_TestCase
{
    public function tearDown()
    {
        EasyRdf_Namespace::delete('po');
    }

    public function testNamespaces()
    {
        $ns = EasyRdf_Namespace::namespaces();
        $this->assertEquals('http://purl.org/dc/terms/', $ns['dc']);
        $this->assertEquals('http://xmlns.com/foaf/0.1/', $ns['foaf']);
    }

    public function testGetDcNamespace()
    {
        $this->assertEquals(
            'http://purl.org/dc/terms/',
            EasyRdf_Namespace::get('dc')
        );
    }

    public function testGetFoafNamespace()
    {
        $this->assertEquals(
            'http://xmlns.com/foaf/0.1/',
            EasyRdf_Namespace::get('foaf')
        );
    }

    public function testGetRdfNamespace()
    {
        $this->assertEquals(
            'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
            EasyRdf_Namespace::get('rdf')
        );
    }

    public function testGetRdfsNamespace()
    {
        $this->assertEquals(
            'http://www.w3.org/2000/01/rdf-schema#',
            EasyRdf_Namespace::get('rdfs')
        );
    }

    public function testGetXsdNamespace()
    {
        $this->assertEquals(
            'http://www.w3.org/2001/XMLSchema#',
            EasyRdf_Namespace::get('xsd')
        );
    }

    public function testGetUpperCaseFoafNamespace()
    {
        $this->assertEquals(
            'http://xmlns.com/foaf/0.1/',
            EasyRdf_Namespace::get('FOAF')
        );
    }

    public function testGetNullNamespace()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Namespace::get(null);
    }

    public function testGetEmptyNamespace()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Namespace::get('');
    }

    public function testGetNonStringNamespace()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Namespace::get(array());
    }

    public function testGetNonAlphanumeric()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Namespace::get('/K.O/');
    }

    public function testAddNamespace()
    {
        EasyRdf_Namespace::set('po', 'http://purl.org/ontology/po/');
        $this->assertEquals(
            'http://purl.org/ontology/po/',
            EasyRdf_Namespace::get('po')
        );
    }

    public function testAddUppercaseNamespace()
    {
        EasyRdf_Namespace::set('PO', 'http://purl.org/ontology/po/');
        $this->assertEquals(
            'http://purl.org/ontology/po/',
            EasyRdf_Namespace::get('po')
        );
    }

    public function testAddNamespaceShortNull()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Namespace::set(null, 'http://purl.org/ontology/ko/');
    }

    public function testAddNamespaceShortEmpty()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Namespace::set('', 'http://purl.org/ontology/ko/');
    }

    public function testAddNamespaceShortNonString()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Namespace::set(array(), 'http://purl.org/ontology/ko/');
    }

    public function testAddNamespaceShortNonAlphanumeric()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Namespace::set('/K.O/', 'http://purl.org/ontology/ko/');
    }

    public function testAddNamespaceLongNull()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Namespace::set('ko', null);
    }

    public function testAddNamespaceLongEmpty()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Namespace::set('ko', '');
    }

    public function testAddNamespaceLongNonString()
    {
        $this->setExpectedException('InvalidArgumentException');
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
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Namespace::delete('');
    }

    public function testDeleteNullNamespace()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Namespace::delete(null);
    }

    public function testDeleteNonStringNamespace()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Namespace::delete($this);
    }

    public function testShortenFoafName()
    {
        $this->assertEquals(
            'foaf:name',
            EasyRdf_Namespace::shorten('http://xmlns.com/foaf/0.1/name')
        );
    }

    public function testShortenResource()
    {
        $resource = new EasyRdf_Resource('http://xmlns.com/foaf/0.1/name');
        $this->assertEquals('foaf:name', EasyRdf_Namespace::shorten($resource));
    }

    public function testShortenUnknownUrl()
    {
        $this->assertEquals(
            'http://www.aelius.com/njh/',
            EasyRdf_Namespace::shorten('http://www.aelius.com/njh/')
        );
    }

    public function testShortenNull()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Namespace::shorten(null);
    }

    public function testShortenEmpty()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Namespace::shorten('');
    }

    public function testShortenNonString()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Namespace::shorten($this);
    }

    public function testPrefixOfUriFoafName()
    {
        $this->assertEquals(
            'foaf',
            EasyRdf_Namespace::prefixOfUri('http://xmlns.com/foaf/0.1/name')
        );
    }

    public function testPrefixOfUnknownUrl()
    {
        $this->assertEquals(
            null,
            EasyRdf_Namespace::prefixOfUri('http://www.aelius.com/njh/')
        );
    }

    public function testPrefixOfUriNull()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Namespace::prefixOfUri(null);
    }

    public function testPrefixOfUriEmpty()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Namespace::prefixOfUri('');
    }

    public function testPrefixOfUriNonString()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Namespace::prefixOfUri(array());
    }

    public function testExpandFoafName()
    {
        $this->assertEquals(
            'http://xmlns.com/foaf/0.1/name',
            EasyRdf_Namespace::expand('foaf:name')
        );
    }

    public function testExpandMissingColon()
    {
        $this->assertEquals(
            'unknown',
            EasyRdf_Namespace::expand('unknown')
        );
    }

    public function testExpandExpanded()
    {
        $this->assertEquals(
            'http://www.aelius.com/njh/',
            EasyRdf_Namespace::expand('http://www.aelius.com/njh/')
        );
    }

    public function testExpandURN()
    {
        $this->assertEquals(
            'urn:isbn:0451450523',
            EasyRdf_Namespace::expand('urn:isbn:0451450523')
        );
    }

    public function testExpandNull()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Namespace::expand(null);
    }

    public function testExpandEmpty()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Namespace::expand('');
    }

    public function testExpandNonString()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Namespace::expand($this);
    }
}
