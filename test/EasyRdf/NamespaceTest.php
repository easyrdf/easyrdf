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

class NamespaceTest extends TestCase
{
    /** @var Graph */
    private $graph;
    /** @var Resource */
    private $resource;

    public function setUp()
    {
        RdfNamespace::setDefault(null);
        $this->graph = new Graph();
        $this->resource = $this->graph->resource('http://xmlns.com/foaf/0.1/name');
    }

    public function tearDown()
    {
        RdfNamespace::delete('po');
        RdfNamespace::reset();
    }

    public function testNamespaces()
    {
        $ns = RdfNamespace::namespaces();
        $this->assertSame('http://purl.org/dc/terms/', $ns['dc']);
        $this->assertSame('http://xmlns.com/foaf/0.1/', $ns['foaf']);
    }

    public function testGetDcNamespace()
    {
        $this->assertSame(
            'http://purl.org/dc/terms/',
            RdfNamespace::get('dc')
        );
    }

    public function testGetFoafNamespace()
    {
        $this->assertSame(
            'http://xmlns.com/foaf/0.1/',
            RdfNamespace::get('foaf')
        );
    }

    public function testGetRdfNamespace()
    {
        $this->assertSame(
            'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
            RdfNamespace::get('rdf')
        );
    }

    public function testGetRdfsNamespace()
    {
        $this->assertSame(
            'http://www.w3.org/2000/01/rdf-schema#',
            RdfNamespace::get('rdfs')
        );
    }

    public function testGetXsdNamespace()
    {
        $this->assertSame(
            'http://www.w3.org/2001/XMLSchema#',
            RdfNamespace::get('xsd')
        );
    }

    public function testGetUpperCaseFoafNamespace()
    {
        $this->assertSame(
            'http://xmlns.com/foaf/0.1/',
            RdfNamespace::get('FOAF')
        );
    }

    public function testGetNullNamespace()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$prefix should be a string and cannot be null or empty'
        );
        RdfNamespace::get(null);
    }

    public function testGetNonStringNamespace()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$prefix should be a string and cannot be null or empty'
        );
        RdfNamespace::get(array());
    }

    public function testGetNonAlphanumeric()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$prefix should only contain alpha-numeric characters'
        );
        RdfNamespace::get('/K.O/');
    }

    public function testAddNamespace()
    {
        RdfNamespace::set('po', 'http://purl.org/ontology/po/');
        $this->assertSame(
            'http://purl.org/ontology/po/',
            RdfNamespace::get('po')
        );
    }

    public function testAddUppercaseNamespace()
    {
        RdfNamespace::set('PO', 'http://purl.org/ontology/po/');
        $this->assertSame(
            'http://purl.org/ontology/po/',
            RdfNamespace::get('po')
        );
    }

    public function testAddNamespaceShortNull()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$prefix should be a string and cannot be null or empty'
        );
        RdfNamespace::set(null, 'http://purl.org/ontology/ko/');
    }

    public function testAddNamespaceShortEmpty()
    {
        try {
            RdfNamespace::set('', 'http://purl.org/ontology/ko/');
            $this->assertTrue(true);  // this is here to avoid marking test as incomplete
        } catch (\LogicException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testAddNamespaceShortNonString()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$prefix should be a string and cannot be null or empty'
        );
        RdfNamespace::set(array(), 'http://purl.org/ontology/ko/');
    }

    public function testAddNamespaceShortInvalid()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'should match RDFXML-QName specification'
        );
        RdfNamespace::set('/K.O/', 'http://purl.org/ontology/ko/');
    }

    public function testAddNamespaceLongNull()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$long should be a string and cannot be null or empty'
        );
        RdfNamespace::set('ko', null);
    }

    public function testAddNamespaceLongEmpty()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$long should be a string and cannot be null or empty'
        );
        RdfNamespace::set('ko', '');
    }

    public function testAddNamespaceLongNonString()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$long should be a string and cannot be null or empty'
        );
        RdfNamespace::set('ko', array());
    }

    public function testDeleteNamespace()
    {
        RdfNamespace::set('po', 'http://purl.org/ontology/po/');
        $this->assertNotNull(RdfNamespace::get('po'));
        RdfNamespace::delete('po');
        $this->assertNull(RdfNamespace::get('po'));
    }

    public function testDeleteEmptyNamespace()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$prefix should be a string and cannot be null or empty'
        );
        RdfNamespace::delete('');
    }

    public function testDeleteNullNamespace()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$prefix should be a string and cannot be null or empty'
        );
        RdfNamespace::delete(null);
    }

    public function testDeleteNonStringNamespace()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$prefix should be a string and cannot be null or empty'
        );
        RdfNamespace::delete($this);
    }

    public function testSetDefaultUri()
    {
        RdfNamespace::setDefault('http://ogp.me/ns#');
        $this->assertSame(
            'http://ogp.me/ns#',
            RdfNamespace::getDefault()
        );
    }

    public function testSetDefaultPrefix()
    {
        RdfNamespace::setDefault('foaf');
        $this->assertSame(
            'http://xmlns.com/foaf/0.1/',
            RdfNamespace::getDefault()
        );
    }

    public function testSetDefaultEmpty()
    {
        RdfNamespace::setDefault('http://ogp.me/ns#');
        RdfNamespace::setDefault('');
        $this->assertSame(null, RdfNamespace::getDefault());
    }

    public function testSetDefaultUnknown()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Unable to set default namespace to unknown prefix: foobar'
        );
        RdfNamespace::setDefault('foobar');
    }

    public function testSplitUriFoafName()
    {
        $this->assertSame(
            array('foaf', 'name'),
            RdfNamespace::splitUri('http://xmlns.com/foaf/0.1/name')
        );
    }

    public function testSplitUriResource()
    {
        $this->assertSame(
            array('foaf','name'),
            RdfNamespace::splitUri($this->resource)
        );
    }

    public function testSlitUriUnknown()
    {
        $this->assertSame(
            null,
            RdfNamespace::splitUri('http://example.com/ns/foo/bar')
        );
    }

    public function testSplitUriAndCreateOneUnknown()
    {
        $this->assertSame(
            array('ns0', 'bar'),
            RdfNamespace::splitUri('http://example.com/ns/foo/bar', true)
        );
    }

    public function testSplitUriAndCreateTwice()
    {
        $this->assertSame(
            array('ns0', 'bar'),
            RdfNamespace::splitUri('http://example.com/ns/foo/bar', true)
        );
        $this->assertSame(
            array('ns0', 'bar'),
            RdfNamespace::splitUri('http://example.com/ns/foo/bar', true)
        );
    }

    public function testSplitUriAndCreateTwoUnknown()
    {
        $this->assertSame(
            array('ns0', 'bar'),
            RdfNamespace::splitUri('http://example1.org/ns/foo/bar', true)
        );
        $this->assertSame(
            array('ns1', 'bar'),
            RdfNamespace::splitUri('http://example2.org/ns/foo/bar', true)
        );
    }

    public function testSplitUriUnsplitable()
    {
        $this->assertSame(
            null,
            RdfNamespace::splitUri('http://example.com/foo/', true)
        );
    }

    public function testSplitUriNull()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$uri cannot be null or empty'
        );
        RdfNamespace::splitUri(null);
    }

    public function testSplitUriEmpty()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$uri cannot be null or empty'
        );
        RdfNamespace::splitUri('');
    }

    public function testSplitUriNonString()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$uri should be a string or EasyRdf\Resource'
        );
        RdfNamespace::splitUri($this);
    }


    public function testShortenFoafName()
    {
        $this->assertSame(
            'foaf:name',
            RdfNamespace::shorten('http://xmlns.com/foaf/0.1/name')
        );
    }

    public function testShortenResource()
    {
        $this->assertSame('foaf:name', RdfNamespace::shorten($this->resource));
    }

    public function testShortenMostSpecific()
    {
        RdfNamespace::set('animals', 'http://example.com/ns/animals/');
        RdfNamespace::set('reptils', 'http://example.com/ns/animals/reptils/');
        RdfNamespace::set('snakes', 'http://example.com/ns/animals/reptils/snakes/');

        $this->assertSame(
            'snakes:milksnake',
            RdfNamespace::shorten('http://example.com/ns/animals/reptils/snakes/milksnake')
        );
    }

    public function testShortenMostSpecific2()
    {
        RdfNamespace::set('snakes', 'http://example.com/ns/animals/reptils/snakes/');
        RdfNamespace::set('reptils', 'http://example.com/ns/animals/reptils/');
        RdfNamespace::set('cat', 'http://example.com/ns/animals/cat/');
        RdfNamespace::set('animals', 'http://example.com/ns/animals/');

        $this->assertSame(
            'snakes:milksnake',
            RdfNamespace::shorten('http://example.com/ns/animals/reptils/snakes/milksnake')
        );
    }

    public function testShortenUnknown()
    {
        $this->assertSame(
            null,
            RdfNamespace::shorten('http://example.com/ns/foo/bar')
        );
    }

    public function testShortenAndCreateOneUnknown()
    {
        $this->assertSame(
            'ns0:bar',
            RdfNamespace::shorten('http://example.com/ns/foo/bar', true)
        );
    }

    public function testShortenAndCreateTwoUnknown()
    {
        $this->assertSame(
            'ns0:bar',
            RdfNamespace::shorten('http://example.com/ns/foo/bar', true)
        );
        $this->assertSame(
            'ns1:bar',
            RdfNamespace::shorten('http://example.org/ns/foo/bar', true)
        );
    }

    public function testShortenUnshortenable()
    {
        $this->assertSame(
            null,
            RdfNamespace::shorten('http://example.com/foo/', true)
        );
    }

    public function testShortenNull()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$uri cannot be null or empty'
        );
        RdfNamespace::shorten(null);
    }

    public function testShortenEmpty()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$uri cannot be null or empty'
        );
        RdfNamespace::shorten('');
    }

    public function testShortenNonString()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$uri should be a string or EasyRdf\Resource'
        );
        RdfNamespace::shorten($this);
    }

    public function testPrefixOfUriFoafName()
    {
        $this->assertSame(
            'foaf',
            RdfNamespace::prefixOfUri('http://xmlns.com/foaf/0.1/name')
        );
    }

    public function testrefixOfUriForResource()
    {
        $this->assertSame(
            'foaf',
            RdfNamespace::prefixOfUri($this->resource)
        );
    }

    public function testPrefixOfUnknownUrl()
    {
        $this->assertSame(
            null,
            RdfNamespace::prefixOfUri('http://www.aelius.com/njh/')
        );
    }

    public function testGetEmptyNamespace()
    {
        RdfNamespace::set('', 'http://xmlns.com/foaf/0.1/name');

        $url = RdfNamespace::get('');

        $this->assertSame(
            'http://xmlns.com/foaf/0.1/name',
            $url
        );
    }

    public function testPrefixOfUriNull()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$uri cannot be null or empty'
        );
        RdfNamespace::prefixOfUri(null);
    }

    public function testPrefixOfUriEmpty()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$uri cannot be null or empty'
        );
        RdfNamespace::prefixOfUri('');
    }

    public function testPrefixOfUriNonString()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$uri should be a string or EasyRdf\Resource'
        );
        RdfNamespace::prefixOfUri(array());
    }

    public function testExpandFoafName()
    {
        $this->assertSame(
            'http://xmlns.com/foaf/0.1/name',
            RdfNamespace::expand('foaf:name')
        );
    }

    public function testExpandZero()
    {
        $this->assertSame(
            '0',
            RdfNamespace::expand('0')
        );
    }

    public function testExpandA()
    {
        $this->assertSame(
            'http://www.w3.org/1999/02/22-rdf-syntax-ns#type',
            RdfNamespace::expand('a')
        );
    }

    public function testExpandFoafDoapProgrammingLanguage()
    {
        $this->assertSame(
            'http://usefulinc.com/ns/doap#programming-language',
            RdfNamespace::expand('doap:programming-language')
        );
    }

    public function testExpandWithDefaultUri()
    {
        RdfNamespace::setDefault('http://ogp.me/ns#');
        $this->assertSame(
            'http://ogp.me/ns#title',
            RdfNamespace::expand('title')
        );
    }

    public function testExpandWithDefaultPrefix()
    {
        RdfNamespace::setDefault('foaf');
        $this->assertSame(
            'http://xmlns.com/foaf/0.1/name',
            RdfNamespace::expand('name')
        );
    }

    public function testExpandZeroWithDefaultPrefix()
    {
        RdfNamespace::setDefault('foaf');
        $this->assertSame(
            'http://xmlns.com/foaf/0.1/0',
            RdfNamespace::expand('0')
        );
    }

    public function testExpandWithoutDefault()
    {
        RdfNamespace::setDefault(null);
        $this->assertSame(
            'unknown',
            RdfNamespace::expand('unknown')
        );
    }

    public function testExpandExpanded()
    {
        $this->assertSame(
            'http://www.aelius.com/njh/',
            RdfNamespace::expand('http://www.aelius.com/njh/')
        );
    }

    public function testExpandURN()
    {
        $this->assertSame(
            'urn:isbn:0451450523',
            RdfNamespace::expand('urn:isbn:0451450523')
        );
    }

    public function testExpandNull()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$shortUri should be a string and cannot be null or empty'
        );
        RdfNamespace::expand(null);
    }

    public function testExpandEmpty()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$shortUri should be a string and cannot be null or empty'
        );
        RdfNamespace::expand('');
    }

    public function testExpandNonString()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$shortUri should be a string and cannot be null or empty'
        );
        RdfNamespace::expand($this);
    }

    /**
     * @see https://github.com/easyrdf/easyrdf/issues/185
     */
    public function testIssue185DashInPrefix()
    {
        RdfNamespace::set('foo-bar', 'http://example.org/dash#');
        $this->assertSame('foo-bar:baz', RdfNamespace::shorten('http://example.org/dash#baz'));
    }

    /**
     * Namespace which is too short shouldn't apply
     */
    public function testShortNamespace()
    {
        RdfNamespace::set('ex', 'http://example.org/');

        $this->assertSame('ex:foo', RdfNamespace::shorten('http://example.org/foo'));
        $this->assertNull(RdfNamespace::shorten('http://example.org/bar/baz'));
    }
}
