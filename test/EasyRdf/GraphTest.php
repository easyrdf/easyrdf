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
 * @copyright  Copyright (c) 2009-2010 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 * @version    $Id$
 */

require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'TestHelper.php';

class Mock_Http_Response
{
    public function getBody()
    {
        return readFixture('foaf.json');
    }

    public function getHeader($header)
    {
        return 'application/json';
    }

    public function isSuccessful()
    {
        return true;
    }
}

class Mock_Http_Client
{
    public function setUri($uri)
    {
    }

    public function setHeaders($headers)
    {
    }

    public function request()
    {
        return new Mock_Http_Response();
    }
}

class Mock_RdfParser
{
    public function parse($graph, $data, $format, $baseUri)
    {
        $graph->add(
            'http://www.example.com/joe#me',
            'foaf:name',
            'Joseph Bloggs'
        );
        return true;
    }
}

class Mock_RdfSerialiser
{
    public function serialise($graph, $format=null)
    {
        return "<rdf></rdf>";
    }
}

class EasyRdf_GraphTest extends EasyRdf_TestCase
{
    /**
     * Set up the test suite before each test
     */
    public function setUp()
    {
        $this->_graph = new EasyRdf_Graph();
        $this->_uri = 'http://example.com/#me';
        $this->_graph->setType($this->_uri, 'foaf:Person');
        $this->_graph->add($this->_uri, 'rdf:test', 'Test A');
        $this->_graph->add($this->_uri, 'rdf:test', new EasyRdf_Literal('Test B', 'en'));
    }

    public function testGetDefaultHttpClient()
    {
        $this->assertEquals(
            'EasyRdf_Http_Client',
            get_class(EasyRdf_Graph::getHttpClient())
        );
    }

    public function testSetHttpClient()
    {
        EasyRdf_Graph::setHttpClient(new Mock_Http_Client());
        $this->assertEquals(
            'Mock_Http_Client',
            get_class(EasyRdf_Graph::getHttpClient())
        );
    }

    public function testSetHttpClientNull()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Graph::setHttpClient(null);
    }

    public function testSetHttpClientString()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Graph::setHttpClient('foobar');
    }

    public function testGetUri()
    {
        $data = readFixture('foaf.json');
        $graph = new EasyRdf_Graph('http://example.com/joe/foaf.rdf', $data);
        $this->assertEquals(
            'http://example.com/joe/foaf.rdf',
            $graph->getUri()
        );
    }

    public function testNewBNode()
    {
        $graph = new EasyRdf_Graph();

        $bnodeOne = $graph->newBNode();
        $this->assertEquals(
            '_:eid1',
            $bnodeOne->getUri()
        );

        $bnodeTwo = $graph->newBNode();
        $this->assertEquals(
            '_:eid2',
            $bnodeTwo->getUri()
        );
    }

    public function testLoadData()
    {
        $graph = new EasyRdf_Graph();
        $data = readFixture('foaf.json');
        $graph->load('http://www.example.com/foaf.json', $data, 'json');

        $joe = $graph->resource('http://www.example.com/joe#me');
        $this->assertNotNull($joe);
        $this->assertEquals('EasyRdf_Resource', get_class($joe));
        $this->assertEquals('http://www.example.com/joe#me', $joe->getUri());

        $name = $joe->get('foaf:name');
        $this->assertNotNull($name);
        $this->assertEquals('EasyRdf_Literal', get_class($name));
        $this->assertEquals('Joe Bloggs', $name->getValue());
        $this->assertEquals('en', $name->getLang());
        $this->assertEquals(null, $name->getDatatype());
    }

    public function testLoadNullUri()
    {
        $this->setExpectedException('InvalidArgumentException');
        $graph = new EasyRdf_Graph();
        $graph->load(null);
    }

    public function testLoadEmptyUri()
    {
        $this->setExpectedException('InvalidArgumentException');
        $graph = new EasyRdf_Graph();
        $graph->load('');
    }

    public function testLoadNonStringUri()
    {
        $this->setExpectedException('InvalidArgumentException');
        $graph = new EasyRdf_Graph();
        $graph->load(array());
    }

    public function testLoadUnknownFormat()
    {
        $this->setExpectedException('EasyRdf_Exception');
        $graph = new EasyRdf_Graph();
        $graph->load('http://www.example.com/foaf.unknown', 'data');
    }

    public function testLoadMockParser()
    {
        EasyRdf_Format::registerParser('mock', 'Mock_RdfParser');

        $graph = new EasyRdf_Graph();
        $graph->load('http://www.example.com/foaf.mock', 'data', 'mock');
        $this->assertStringEquals(
            'Joseph Bloggs',
            $graph->get('http://www.example.com/joe#me', 'foaf:name')
        );
    }

    public function testLoadMockHttpClient()
    {
        EasyRdf_Graph::setHttpClient(new Mock_Http_Client());
        $graph = new EasyRdf_Graph('http://www.example.com/');
        $this->assertStringEquals(
            'Joe Bloggs',
            $graph->get('http://www.example.com/joe#me', 'foaf:name')
        );
    }

    public function testGetResourceSameGraph()
    {
        $graph = new EasyRdf_Graph();
        $resource1 = $graph->resource('http://example.com/');
        $this->assertType('EasyRdf_Resource', $resource1);
        $this->assertStringEquals('http://example.com/', $resource1->getUri());
        $resource2 = $graph->resource('http://example.com/');
        $this->assertTrue($resource1 === $resource2);
    }

    public function testGetResourceDifferentGraph()
    {
        $graph1 = new EasyRdf_Graph();
        $resource1 = $graph1->resource('http://example.com/');
        $graph2 = new EasyRdf_Graph();
        $resource2 = $graph2->resource('http://example.com/');
        $this->assertFalse($resource1 === $resource2);
    }

    public function testGetShortenedResource()
    {
        $graph = new EasyRdf_Graph();
        $person = $graph->resource('foaf:Person');
        $this->assertStringEquals(
            'http://xmlns.com/foaf/0.1/Person',
            $person->getUri()
        );
    }

    public function testGetResourceUnknown()
    {
        $graph = new EasyRdf_Graph();
        $this->assertEquals(
            'http://www.foo.com/bar',
            $graph->resource('http://www.foo.com/bar')->getUri()
        );
    }

    public function testGetResourceNullUri()
    {
        $this->setExpectedException('InvalidArgumentException');
        $graph = new EasyRdf_Graph();
        $graph->resource(null);
    }

    public function testGetResourceEmptyUri()
    {
        $this->setExpectedException('InvalidArgumentException');
        $graph = new EasyRdf_Graph();
        $graph->resource('');
    }

    public function testGetResourceNonStringUri()
    {
        $this->setExpectedException('InvalidArgumentException');
        $graph = new EasyRdf_Graph();
        $graph->resource(array());
    }

    public function testResolveResource()
    {
        $resource = $this->_graph->resolveResource(
            'http://www.example.com/foo', '/bar'
        );
        $this->assertType('EasyRdf_Resource', $resource);
        $this->assertStringEquals('http://www.example.com/bar', $resource);
    }

    public function testResourceWithType()
    {
        $graph = new EasyRdf_Graph();
        $resource = $graph->resource(
            'http://www.foo.com/bar',
            'foaf:Person'
        );
        $type = $graph->type('http://www.foo.com/bar');
        $this->assertStringEquals('foaf:Person', $type);
    }

    public function testResourceWithTypeUri()
    {
        $graph = new EasyRdf_Graph();
        $resource = $graph->resource(
            'http://www.foo.com/bar',
            'http://xmlns.com/foaf/0.1/Person'
        );
        $type = $graph->type('http://www.foo.com/bar');
        $this->assertStringEquals('foaf:Person', $type);
    }

    public function testResourceWithMultipleTypes()
    {
        $graph = new EasyRdf_Graph();
        $resource = $graph->resource(
            'http://www.foo.com/bar',
            array('rdf:Type1', 'rdf:Type2')
        );

        $types = $resource->types();
        $this->assertEquals(2, count($types));
        $this->assertStringEquals('rdf:Type1', $types[0]);
        $this->assertStringEquals('rdf:Type2', $types[1]);
    }

    public function testResources()
    {
        $data = readFixture('foaf.json');
        $graph = new EasyRdf_Graph('http://example.com/joe/foaf.rdf', $data);
        $resources = $graph->resources();
        $this->assertTrue(is_array($resources));
        $this->assertType('EasyRdf_Resource', $resources['_:eid1']);

        $urls = array_keys($resources);
        sort($urls);

        $this->assertEquals(
            array(
                '_:eid1',
                'http://www.example.com/joe#me',
                'http://www.example.com/joe/',
                'http://www.example.com/joe/foaf.rdf',
                'http://www.example.com/project',
                'http://xmlns.com/foaf/0.1/Person',
                'http://xmlns.com/foaf/0.1/PersonalProfileDocument',
                'http://xmlns.com/foaf/0.1/Project'
            ),
            $urls
        );
    }

    public function testResourcesMatching()
    {
        $data = readFixture('foaf.json');
        $graph = new EasyRdf_Graph('http://example.com/joe/foaf.rdf', $data);
        $matched = $graph->resourcesMatching('foaf:name', 'Joe Bloggs');
        $this->assertEquals(1, count($matched));
        $this->assertEquals(
            'http://www.example.com/joe#me',
            $matched[0]->getUri()
        );
    }

    public function testResourcesMatchingObject()
    {
        $matched = $this->_graph->resourcesMatching(
            'rdf:test',
            new EasyRdf_Literal('Test B', 'en')
        );
        $this->assertEquals(1, count($matched));
        $this->assertStringEquals(
            'http://example.com/#me',
            $matched[0]
        );
    }

    public function testGet()
    {
        $this->assertStringEquals(
            'Test A',
            $this->_graph->get($this->_uri, 'rdf:test')
        );
    }

    public function testGetWithUri()
    {
        $this->assertStringEquals(
            'Test A',
            $this->_graph->get(
                $this->_uri,
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#test'
            )
        );
    }

    public function testGetWithLanguage()
    {
        $this->assertStringEquals(
            'Test B',
            $this->_graph->get($this->_uri, 'rdf:test', 'literal', 'en')
        );
    }

    public function testGetInverse()
    {
        $this->_graph->addResource($this->_uri, 'foaf:homepage', 'http://example.com/');
        $this->assertStringEquals(
            $this->_uri,
            $this->_graph->get('http://example.com/', '^foaf:homepage')
        );
    }

    public function testGetArray()
    {
        $this->assertStringEquals(
            'Test A',
            $this->_graph->get($this->_uri, array('rdf:test', 'rdf:foobar'))
        );
    }

    public function testPropertyAsResource()
    {
        $rdfTest = $this->_graph->resource('rdf:test');
        $this->assertStringEquals(
            'Test A', $this->_graph->get($this->_uri, $rdfTest)
        );
    }

    public function testGetLiteral()
    {
        $this->_graph->addResource($this->_uri, 'foaf:name', 'http://example.com/');
        $this->_graph->addLiteral($this->_uri, 'foaf:name', 'Joe');
        $this->assertStringEquals(
            'Joe', $this->_graph->getLiteral($this->_uri, 'foaf:name')
        );
    }

    public function testGetNonExistantLiteral()
    {
        $this->assertNull(
            $this->_graph->getLiteral($this->_uri, 'rdf:type')
        );
    }

    public function testGetArray2()
    {
        $this->assertStringEquals(
            'Test A',
            $this->_graph->get($this->_uri, array('rdf:foobar', 'rdf:test'))
        );
    }

    public function testGetEmptyArray()
    {
        $this->assertEquals(
            null,
            $this->_graph->get($this->_uri, array())
        );
    }

    public function testGetNonExistantResource()
    {
        $this->assertNull(
            $this->_graph->get('foo:bar', 'foo:bar')
        );
    }

    public function testGetNonExistantProperty()
    {
        $this->assertNull($this->_graph->get($this->_uri, 'foo:bar'));
    }

    public function testGetNullResource()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_graph->get(null, 'rdf:test');
    }

    public function testGetEmptyResource()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_graph->get('', 'rdf:test');
    }

    public function testGetNullProperty()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_graph->get($this->_uri, null);
    }

    public function testGetEmptyProperty()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_graph->get($this->_uri, '');
    }

    public function testGetNonStringProperty()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_graph->get($this->_uri, $this);
    }

    public function testAll()
    {
        $all = $this->_graph->all($this->_uri, 'rdf:test');
        $this->assertEquals(2, count($all));
        $this->assertStringEquals('Test A', $all[0]);
        $this->assertStringEquals('Test B', $all[1]);
    }

    public function testAllWithPropertyUri()
    {
        $all = $this->_graph->all(
            $this->_uri,
            'http://www.w3.org/1999/02/22-rdf-syntax-ns#test'
        );
        $this->assertEquals(2, count($all));
        $this->assertStringEquals('Test A', $all[0]);
        $this->assertStringEquals('Test B', $all[1]);
    }

    public function testAllWithLang()
    {
        $all = $this->_graph->all($this->_uri, 'rdf:test', 'literal', 'en');
        $this->assertEquals(1, count($all));
        $this->assertStringEquals('Test B', $all[0]);
    }

    public function testAllInverse()
    {
        $all = $this->_graph->all('foaf:Person', '^rdf:type');
        $this->assertEquals(1, count($all));
        $this->assertStringEquals($this->_uri, $all[0]);
    }

    public function testAllNonExistantResource()
    {
        $this->assertEquals(
            array(),
            $this->_graph->all('foo:bar', 'foo:bar')
        );
    }

    public function testAllNonExistantProperty()
    {
        $this->assertEquals(
            array(),
            $this->_graph->all($this->_uri, 'foo:bar')
        );
    }

    public function testAllNullKey()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_graph->all($this->_uri, null);
    }

    public function testAllEmptyKey()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_graph->all($this->_uri, '');
    }

    public function testAllNonStringKey()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_graph->all($this->_uri, array());
    }

    public function testAllOfType()
    {
        $data = readFixture('foaf.json');
        $graph = new EasyRdf_Graph('http://example.com/joe/foaf.rdf', $data);
        $resources = $graph->allOfType('foaf:Person');
        $this->assertTrue(is_array($resources));
        $this->assertEquals(1, count($resources));
        $this->assertEquals(
            'http://www.example.com/joe#me',
            $resources[0]->getUri()
        );
    }

    public function testAllOfTypeUnknown()
    {
        $graph = new EasyRdf_Graph();
        $resources = $graph->allOfType('unknown:type');
        $this->assertTrue(is_array($resources));
        $this->assertEquals(0, count($resources));
    }

    public function testAllLiterals()
    {
        $all = $this->_graph->allLiterals($this->_uri, 'rdf:test');
        $this->assertEquals(2, count($all));
        $this->assertStringEquals('Test A', $all[0]);
        $this->assertStringEquals('Test B', $all[1]);
    }

    public function testAllLiteralsForResource()
    {
        $all = $this->_graph->allLiterals($this->_uri, 'rdf:type');
        $this->assertTrue(is_array($all));
        $this->assertEquals(0, count($all));
    }

    public function testJoinDefaultGlue()
    {
        $this->assertEquals(
            'Test A Test B',
            $this->_graph->join($this->_uri, 'rdf:test')
        );
    }

    public function testJoinWithUri()
    {
        $this->assertEquals(
            'Test A Test B',
            $this->_graph->join(
                $this->_uri,
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#test'
            )
        );
    }

    public function testJoinWithLang()
    {
        $this->assertEquals(
            'Test B',
            $this->_graph->join($this->_uri, 'rdf:test', ' ', 'en')
        );
    }

    public function testJoinNonExistantProperty()
    {
        $this->assertEquals('', $this->_graph->join($this->_uri, 'foo:bar'));
    }

    public function testJoinCustomGlue()
    {
        $this->assertEquals(
            'Test A:Test B',
            $this->_graph->join($this->_uri, 'rdf:test', ':')
        );
    }

    public function testJoinNullKey()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_graph->join($this->_uri, null, 'Test C');
    }

    public function testJoinEmptyKey()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_graph->join($this->_uri, '', 'Test C');
    }

    public function testJoinNonStringKey()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_graph->join($this->_uri, array(), 'Test C');
    }

    public function testAdd()
    {
        $this->_graph->add($this->_uri, 'rdf:test', 'Test C');
        $all = $this->_graph->all($this->_uri, 'rdf:test');
        $this->assertEquals(3, count($all));
        $this->assertStringEquals('Test A', $all[0]);
        $this->assertStringEquals('Test B', $all[1]);
        $this->assertStringEquals('Test C', $all[2]);
    }

    public function testAddWithUri()
    {
        $this->_graph->add(
            $this->_uri,
            'http://www.w3.org/1999/02/22-rdf-syntax-ns#test',
            'Test C'
        );
        $all = $this->_graph->all($this->_uri, 'rdf:test');
        $this->assertEquals(3, count($all));
        $this->assertStringEquals('Test A', $all[0]);
        $this->assertStringEquals('Test B', $all[1]);
        $this->assertStringEquals('Test C', $all[2]);
    }

    public function testAddMultipleLiterals()
    {
        $this->_graph->addLiteral($this->_uri, 'rdf:test', array('Test C', 'Test D'));
        $all = $this->_graph->all($this->_uri, 'rdf:test');
        $this->assertEquals(4, count($all));
        $this->assertStringEquals('Test A', $all[0]);
        $this->assertStringEquals('Test B', $all[1]);
        $this->assertStringEquals('Test C', $all[2]);
        $this->assertStringEquals('Test D', $all[3]);
    }

    public function testAddLiteralMultipleTimes()
    {
        $this->_graph->add($this->_uri, 'rdf:test2', 'foobar');
        $this->_graph->add($this->_uri, 'rdf:test2', 'foobar');
        $all = $this->_graph->all($this->_uri, 'rdf:test2');
        $this->assertEquals(1, count($all));
        $this->assertStringEquals('foobar', $all[0]);
    }

    public function testAddLiteralDifferentLanguages()
    {
        $this->_graph->set($this->_uri, 'rdf:test', new EasyRdf_Literal('foobar', 'en'));
        $this->_graph->add($this->_uri, 'rdf:test', new EasyRdf_Literal('foobar', 'fr'));
        $all = $this->_graph->all($this->_uri, 'rdf:test');
        $this->assertEquals(2, count($all));
        $this->assertStringEquals('foobar', $all[0]);
        $this->assertStringEquals('foobar', $all[1]);
    }

    public function testAddNull()
    {
        $this->_graph->add($this->_uri, 'rdf:test', null);
        $all = $this->_graph->all($this->_uri, 'rdf:test');
        $this->assertEquals(2, count($all));
        $this->assertStringEquals('Test A', $all[0]);
        $this->assertStringEquals('Test B', $all[1]);
    }

    public function testAddNullKey()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_graph->add($this->_uri, null, 'Test C');
    }

    public function testAddEmptyKey()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_graph->add($this->_uri, '', 'Test C');
    }

    public function testAddNonStringKey()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_graph->add($this->_uri, array(), 'Test C');
    }

    function testAddInvalidObject()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_graph->add($this->_uri, 'rdf:foo', $this);
    }

    public function testAddSingleValueToString()
    {
        $graph = new EasyRdf_Graph();
        $graph->add('http://www.example.com/joe#me', 'foaf:name', 'Joe');
        $this->assertStringEquals('Joe', $graph->get('http://www.example.com/joe#me', 'foaf:name'));
    }

    public function testAddSingleValueToResource()
    {
        $graph = new EasyRdf_Graph();
        $graph->add('http://www.example.com/joe#me', 'foaf:name', 'Joe');
        $this->assertStringEquals('Joe', $graph->get('http://www.example.com/joe#me', 'foaf:name'));
    }

# FIXME: should this be allowed?
//     public function testAddMultipleValuesToString()
//     {
//         $graph = new EasyRdf_Graph();
//         $graph->add(
//             'http://www.example.com/joe#me',
//             'foaf:name',
//             array('Joe','Joseph')
//         );
//
//         $all = $graph->all('http://www.example.com/joe#me', 'foaf:name');
//         $this->assertStringEquals('Joe', $all[0]);
//         $this->assertStringEquals('Joseph', $all[1]);
//     }
//
//     public function testAddMultipleValuesToResource()
//     {
//         $graph = new EasyRdf_Graph();
//         $graph->add('http://www.example.com/joe#me', 'foaf:name', array('Joe','Joseph'));
//
//         $all = $resource->all('http://www.example.com/joe#me', 'foaf:name');
//         $this->assertStringEquals('Joe', $all[0]);
//         $this->assertStringEquals('Joseph', $all[1]);
//     }

# FIXME: move to ResourceTest.php?
//     public function testAddMultiplePropertiesToResource()
//     {
//         $graph = new EasyRdf_Graph();
//         $resource = $graph->resource('http://www.example.com/joe#me');
//         $graph->add(
//             $resource,
//             array(
//                 'foaf:givenname' => 'Joe',
//                 'foaf:surname' => 'Bloggs'
//             )
//         );
//         $this->assertStringEquals('Joe', $resource->get('foaf:givenname'));
//         $this->assertStringEquals('Bloggs', $resource->get('foaf:surname'));
//     }

# FIXME: probably will have to scrap this
//     public function testAddAnonymousBNodeToResource()
//     {
//         $graph = new EasyRdf_Graph();
//         $resource = $graph->resource('http://www.example.com/joe#me');
//         $graph->add(
//             $resource, 'foaf:knows', array('foaf:name' => 'Yves')
//         );
//         $yves = $resource->get('foaf:knows');
//         $this->assertTrue($yves->isBNode());
//         $this->assertStringEquals('Yves', $yves->get('foaf:name'));
//     }
//
//     public function testAddTypedBNodeToResource()
//     {
//         $graph = new EasyRdf_Graph();
//         $resource = $graph->resource('http://www.example.com/joe#me');
//         $graph->add(
//             $resource, 'foaf:knows', array('rdf:type' => 'foaf:Person')
//         );
//         $person = $resource->get('foaf:knows');
//         $this->assertTrue($person->isBNode());
//         $this->assertStringEquals('foaf:Person', $person->type());
//     }
//
//     public function testAddBNodeViaPropertiesToResource()
//     {
//         $graph = new EasyRdf_Graph();
//         $resource = $graph->resource('http://www.example.com/joe#me');
//         $graph->add(
//             $resource, array('foaf:knows' => array('foaf:name' => 'Yves'))
//         );
//         $yves = $resource->get('foaf:knows');
//         $this->assertTrue($yves->isBNode());
//         $this->assertStringEquals('Yves', $yves->get('foaf:name'));
//     }

    public function testAddPropertriesInvalidResourceClass()
    {
        $this->setExpectedException('InvalidArgumentException');
        $graph = new EasyRdf_Graph();
        $invalidResource = new EasyRdf_Utils();
        $graph->add($invalidResource, 'foaf:name', 'value');
    }

    public function testAddResource()
    {
        $this->_graph->addResource($this->_uri, 'foaf:homepage', 'http://www.example.com/');
        $res = $this->_graph->get($this->_uri, 'foaf:homepage');
        $this->assertType('EasyRdf_Resource', $res);
        $this->assertStringEquals('http://www.example.com/', $res);
    }

    public function testAddBnodeResource()
    {
        $this->_graph->addResource($this->_uri, 'foaf:interest', '_:abc');
        $res = $this->_graph->get($this->_uri, 'foaf:interest');
        $this->assertType('EasyRdf_Resource', $res);
        $this->assertTrue($res->isBnode());
        $this->assertStringEquals('_:abc', $res);
    }

    public function testAddDulicateTriple()
    {
        $homepage = $this->_graph->resource('http://example.com/');
        $this->_graph->add($this->_uri, 'foaf:homepage', $homepage);
        $this->_graph->addResource($this->_uri, 'foaf:homepage', $homepage);
        $this->_graph->addResource($this->_uri, 'foaf:homepage', $homepage);
        $all = $this->_graph->all($this->_uri, 'foaf:homepage');
        $this->assertEquals(1, count($all));
        $this->assertStringEquals($homepage, $all[0]);

        # Check inverse too
        $all = $this->_graph->all($homepage, '^foaf:homepage');
        $this->assertEquals(1, count($all));
        $this->assertStringEquals('http://example.com/#me', $all[0]);
    }

    public function testDelete()
    {
        $this->assertStringEquals('Test A', $this->_graph->get($this->_uri, 'rdf:test'));
        $this->_graph->delete($this->_uri, 'rdf:test');
        $this->assertEquals(array(), $this->_graph->all($this->_uri, 'rdf:test'));
    }

    public function testDeleteWithUri()
    {
        $this->assertStringEquals('Test A', $this->_graph->get($this->_uri, 'rdf:test'));
        $this->_graph->delete(
            $this->_uri,
            'http://www.w3.org/1999/02/22-rdf-syntax-ns#test'
        );
        $this->assertEquals(array(), $this->_graph->all($this->_uri, 'rdf:test'));
    }

    public function testDeleteNullKey()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_graph->delete($this->_uri, null);
    }

    public function testDeleteEmptyKey()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_graph->delete($this->_uri, '');
    }

    public function testDeleteNonStringKey()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_graph->delete($this->_uri, array());
    }

    public function testDeleteValue()
    {
        $testa = $this->_graph->get($this->_uri, 'rdf:test');
        $this->_graph->delete($this->_uri, 'rdf:test', $testa);
        $all = $this->_graph->all($this->_uri, 'rdf:test');
        $this->assertEquals(1, count($all));
    }

    public function testDeleteResource()
    {
        $this->_graph->addResource($this->_uri, 'foaf:homepage', 'http://example.com/');
        $this->_graph->addResource($this->_uri, 'foaf:homepage', 'http://example.com/');
        $this->assertTrue($this->_graph->hasProperty($this->_uri, 'foaf:homepage'));
        $this->assertTrue($this->_graph->hasProperty('http://example.com/', '^foaf:homepage'));
        $this->_graph->delete($this->_uri, 'foaf:homepage');
        $this->assertFalse($this->_graph->hasProperty($this->_uri, 'foaf:homepage'));
        $this->assertFalse($this->_graph->hasProperty('http://example.com/', '^foaf:homepage'));
    }

    public function testGetType()
    {
        $data = readFixture('foaf.json');
        $graph = new EasyRdf_Graph(
            'http://www.example.com/joe/foaf.rdf', $data
        );
        $this->assertStringEquals(
            'foaf:PersonalProfileDocument',
            $graph->type()
        );
    }

    public function testTypeUnknown()
    {
        $graph = new EasyRdf_Graph();
        $this->assertNull($graph->type());
    }

    public function testPrimaryTopic()
    {
        $data = readFixture('foaf.json');
        $graph = new EasyRdf_Graph(
            'http://www.example.com/joe/foaf.rdf', $data
        );
        $this->assertEquals(
            'http://www.example.com/joe#me',
            $graph->primaryTopic()->getUri()
        );
    }

    public function testPrimaryTopicUnknown()
    {
        $graph = new EasyRdf_Graph();
        $this->assertNull($graph->primaryTopic());
    }

    public function testSerialise()
    {
        EasyRdf_Format::registerSerialiser('mock', 'Mock_RdfSerialiser');
        $graph = new EasyRdf_Graph();
        $this->assertEquals("<rdf></rdf>", $graph->serialise('mock'));
    }

    public function testSerialiseByMime()
    {
        EasyRdf_Format::registerSerialiser('mock', 'Mock_RdfSerialiser');
        EasyRdf_Format::register('mock', 'Mock', null, 'mock/mime');
        $graph = new EasyRdf_Graph();
        $this->assertEquals(
            "<rdf></rdf>",
            $graph->serialise('mock/mime')
        );
    }

    public function testIsEmpty()
    {
        $graph = new EasyRdf_Graph();
        $this->assertTrue($graph->isEmpty());
    }

    public function testIsNotEmpty()
    {
        $graph = new EasyRdf_Graph();
        $graph->add('http://example.com/', 'rdfs:label', 'Example');
        $this->assertFalse($graph->isEmpty());
    }

    public function testProperties()
    {
        $this->assertEquals(
            array('rdf:type', 'rdf:test'),
            $this->_graph->properties($this->_uri)
        );
    }

    public function testPropertiesForNonExistantResource()
    {
        $this->assertEquals(
            array(),
            $this->_graph->properties('http://doesnotexist.com/')
        );
    }

    public function testPropertyUris()
    {
        $this->assertEquals(
            array(
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type',
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#test'
            ),
            $this->_graph->propertyUris($this->_uri)
        );
    }

    public function testPropertyUrisForNonExistantResource()
    {
        $this->assertEquals(
            array(),
            $this->_graph->propertyUris('http://doesnotexist.com/')
        );
    }

    public function testHasProperty()
    {
        $this->assertTrue(
            $this->_graph->hasProperty($this->_uri, 'rdf:type')
        );
    }

    public function testHasInverseProperty()
    {
        $this->assertTrue(
            $this->_graph->hasProperty('foaf:Person', '^rdf:type')
        );
    }

    public function testDoesntHaveProperty()
    {
        $this->assertFalse(
            $this->_graph->hasProperty($this->_uri, 'rdf:doesntexist')
        );
    }

    public function testDoesntHaveInverseProperty()
    {
        $this->assertFalse(
            $this->_graph->hasProperty($this->_uri, '^rdf:doesntexist')
        );
    }

    public function testDumpLiteral()
    {
        $graph = new EasyRdf_Graph();
        $graph->add('http://example.com/joe#me', 'foaf:name', 'Joe');
        $graph->add('http://example.com/joe#me', 'foaf:age', new EasyRdf_Literal(52));
        $deutschland = new EasyRdf_Literal('Deutschland', 'de');
        $graph->add('http://example.com/joe#me', 'foaf:birthPlace', $deutschland);

        $text = $graph->dump(false);
        $this->assertContains('http://example.com/joe#me', $text);
        $this->assertContains('-> foaf:name -> "Joe"', $text);
        $this->assertContains('-> foaf:age -> "52"^^xsd:integer', $text);
        $this->assertContains('-> foaf:birthPlace -> "Deutschland"@de', $text);

        $html = $graph->dump(true);
        $this->assertContains('http://example.com/joe#me', $html);
        $this->assertContains('>foaf:name</span>', $html);
        $this->assertContains('>&quot;Joe&quot;</span>', $html);
        $this->assertContains('>foaf:age</span>', $html);
        $this->assertContains('>&quot;52&quot;^^xsd:integer</span>', $html);
        $this->assertContains('>foaf:birthPlace</span>', $html);
        $this->assertContains('>&quot;Deutschland&quot;@de</span>', $html);
    }

    public function testDumpResource()
    {
        $graph = new EasyRdf_Graph();
        $graph->addResource('http://example.com/joe#me', 'rdf:type', 'foaf:Person');
        $graph->addResource('http://example.com/joe#me', 'foaf:homepage', 'http://example.com/');
        $graph->add('http://example.com/joe#me', 'foaf:knows', $graph->newBnode());

        $text = $graph->dumpResource('http://example.com/joe#me', false);
        $this->assertContains('http://example.com/joe#me', $text);
        $this->assertContains('-> rdf:type -> foaf:Person', $text);
        $this->assertContains('-> foaf:homepage -> http://example.com/', $text);
        $this->assertContains('-> foaf:knows -> _:eid1', $text);

        $html = $graph->dumpResource('http://example.com/joe#me', true);
        $this->assertContains('http://example.com/joe#me', $html);
        $this->assertContains('>rdf:type</span>', $html);
        $this->assertContains('>foaf:Person</a>', $html);
        $this->assertContains('>foaf:homepage</span>', $html);
        $this->assertContains('>http://example.com/</a>', $html);
        $this->assertContains('>foaf:knows</span>', $html);
        $this->assertContains('>_:eid1</a>', $html);
    }

    public function testDumpResourceWithNoProperties()
    {
        $graph = new EasyRdf_Graph();
        $this->assertEquals('', $graph->dumpResource('http://example.com/empty', false));
        $this->assertEquals('', $graph->dumpResource('http://example.com/empty', true));
    }

    public function testTypes()
    {
        $types = $this->_graph->types($this->_uri);
        $this->assertEquals(1, count($types));
        $this->assertStringEquals('foaf:Person', $types[0]);
    }

    public function testType()
    {
        $this->assertStringEquals('foaf:Person', $this->_graph->type($this->_uri));
    }

    public function testTypeForResourceWithNoType()
    {
        $resource = $this->_graph->resource('http://example.com/notype');
        $this->assertNull($resource->type());
    }

    public function testTypeForUnamedGraph()
    {
        $graph = new EasyRdf_Graph();
        $this->assertNull($graph->type());
    }

    public function testTypeAsResource()
    {
        $type = $this->_graph->typeAsResource($this->_uri);
        $this->assertType('EasyRdf_Resource', $type);
        $this->assertStringEquals('http://xmlns.com/foaf/0.1/Person', $type);
    }

    public function testTypeAsResourceForUnamedGraph()
    {
        $graph = new EasyRdf_Graph();
        $this->assertNull($graph->typeAsResource());
    }

    public function testIsA()
    {
        $this->assertTrue($this->_graph->is_a($this->_uri, 'foaf:Person'));
    }

    public function testIsAFullUri()
    {
        $this->assertTrue(
            $this->_graph->is_a($this->_uri, 'http://xmlns.com/foaf/0.1/Person')
        );
    }

    public function testIsntA()
    {
        $this->assertFalse($this->_graph->is_a($this->_uri, 'foaf:Rat'));
    }

    public function testAddType()
    {
        $this->_graph->addType($this->_uri, 'rdf:newType');
        $this->assertTrue(
            $this->_graph->is_a($this->_uri, 'rdf:newType')
        );
    }

    public function testSetType()
    {
        $this->_graph->setType($this->_uri, 'foaf:Rat');
        $this->assertTrue(
            $this->_graph->is_a($this->_uri, 'foaf:Rat')
        );
        $this->assertFalse(
            $this->_graph->is_a($this->_uri, 'http://xmlns.com/foaf/0.1/Person')
        );
    }

    public function testLabelForUnnamedGraph()
    {
        $graph = new EasyRdf_Graph();
        $this->assertNull($graph->label());
    }

    public function testLabelWithSkosPrefLabel()
    {
        $this->_graph->addLiteral($this->_uri, 'skos:prefLabel', 'Preferred Label');
        $this->_graph->addLiteral($this->_uri, 'rdfs:label', 'Label Text');
        $this->_graph->addLiteral($this->_uri, 'foaf:name', 'Foaf Name');
        $this->_graph->addLiteral($this->_uri, 'dc:title', 'Dc Title');
        $this->assertStringEquals('Preferred Label', $this->_graph->label($this->_uri));
    }

    public function testLabelWithRdfsLabel()
    {
        $this->_graph->addLiteral($this->_uri, 'rdfs:label', 'Label Text');
        $this->_graph->addLiteral($this->_uri, 'foaf:name', 'Foaf Name');
        $this->_graph->addLiteral($this->_uri, 'dc:title', 'Dc Title');
        $this->assertStringEquals('Label Text', $this->_graph->label($this->_uri));
    }

    public function testLabelWithFoafName()
    {
        $this->_graph->addLiteral($this->_uri, 'foaf:name', 'Foaf Name');
        $this->_graph->addLiteral($this->_uri, 'dc:title', 'Dc Title');
        $this->assertStringEquals('Foaf Name', $this->_graph->label($this->_uri));
    }

    public function testLabelWithDc11Title()
    {
        $this->_graph->addLiteral($this->_uri, 'dc11:title', 'Dc11 Title');
        $this->assertStringEquals('Dc11 Title', $this->_graph->label($this->_uri));
    }

    public function testLabelNoRdfsLabel()
    {
        $this->assertNull($this->_graph->label($this->_uri));
    }


    public function testToString()
    {
        $data = readFixture('foaf.json');
        $graph = new EasyRdf_Graph('http://example.com/joe/foaf.rdf', $data);
        $this->assertStringEquals('http://example.com/joe/foaf.rdf', $graph);
    }
}
