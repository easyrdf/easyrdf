<?php

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009 Nicholas J Humfrey.  All rights reserved.
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
 * @copyright  Copyright (c) 2009 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 * @version    $Id$
 */

require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'TestHelper.php';
require_once 'EasyRdf/Namespace.php';

class EasyRdf_NamespaceTest extends PHPUnit_Framework_TestCase
{
    public function testGetDcNamespace()
    {
        $this->assertEquals(
            'http://purl.org/dc/elements/1.1/',
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

    public function testAddNamespace()
    {
        EasyRdf_Namespace::add('po', 'http://purl.org/ontology/po/');
        $this->assertEquals(
            'http://purl.org/ontology/po/',
            EasyRdf_Namespace::get('po')
        );
    }

    public function testAddUppercaseNamespace()
    {
        EasyRdf_Namespace::add('PO', 'http://purl.org/ontology/po/');
        $this->assertEquals(
            'http://purl.org/ontology/po/',
            EasyRdf_Namespace::get('po')
        );
    }

    public function testAddNamespaceShortNull()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Namespace::add(null, 'http://purl.org/ontology/ko/');
    }

    public function testAddNamespaceShortEmpty()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Namespace::add('', 'http://purl.org/ontology/ko/');
    }

    public function testAddNamespaceShortNonString()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Namespace::add(array(), 'http://purl.org/ontology/ko/');
    }

    public function testAddNamespaceLongNull()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Namespace::add('ko', null);
    }

    public function testAddNamespaceLongEmpty()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Namespace::add('ko', '');
    }

    public function testAddNamespaceLongNonString()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Namespace::add('ko', array());
    }

    public function testShortenFoafName()
    {
        $this->assertEquals(
            'foaf:name',
            EasyRdf_Namespace::shorten('http://xmlns.com/foaf/0.1/name')
        );
    }

    public function testShortenUnknownUrl()
    {
        $this->assertEquals(
            null,
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
        EasyRdf_Namespace::shorten(array());
    }

    public function testNamespaceOfUriFoafName()
    {
        $this->assertEquals(
            'foaf',
            EasyRdf_Namespace::namespaceOfUri('http://xmlns.com/foaf/0.1/name')
        );
    }

    public function testNamespaceOfUnknownUrl()
    {
        $this->assertEquals(
            null,
            EasyRdf_Namespace::namespaceOfUri('http://www.aelius.com/njh/')
        );
    }

    public function testNamespaceOfUriNull()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Namespace::namespaceOfUri(null);
    }

    public function testNamespaceOfUriEmpty()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Namespace::namespaceOfUri('');
    }

    public function testNamespaceOfUriNonString()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Namespace::namespaceOfUri(array());
    }

    public function testExpandFoafName()
    {
        $this->assertEquals(
            'http://xmlns.com/foaf/0.1/name',
            EasyRdf_Namespace::expand('foaf:name')
        );
    }

    public function testExpandMissingUnderscore()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Namespace::expand('unknown');
    }

    public function testExpandUnknown()
    {
        $this->assertEquals(
            null,
            EasyRdf_Namespace::expand('unknown:unknown')
        );
    }
}
