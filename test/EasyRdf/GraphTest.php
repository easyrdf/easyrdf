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
    protected $_graph = null;

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
            $graph->resource('http://www.example.com/joe#me')->get('foaf:name')
        );
    }

    public function testLoadMockHttpClient()
    {
        EasyRdf_Graph::setHttpClient(new Mock_Http_Client());
        $graph = new EasyRdf_Graph('http://www.example.com/');
        $this->assertStringEquals(
            'Joe Bloggs',
            $graph->resource('http://www.example.com/joe#me')->get('foaf:name')
        );
    }

    public function testLoadDuplicateBNodes()
    {
        $foafName = 'http://xmlns.com/foaf/0.1/name';
        $bnodeA = array( '_:genid1' => array(
            $foafName => array(array( 'type' => 'literal', 'value' => 'A' ))
        ));
        $bnodeB = array( '_:genid1' => array(
            $foafName => array(array( 'type' => 'literal', 'value' => 'B' ))
        ));

        $graph = new EasyRdf_Graph();
        $graph->load('file://bnodeA', $bnodeA);
        $graph->load('file://bnodeB', $bnodeB);
        $this->assertStringEquals(
            'A',
            $graph->resource('_:eid1')->get('foaf:name')
        );
        $this->assertStringEquals(
            'B',
            $graph->resource('_:eid2')->get('foaf:name')
        );
    }

    public function testGetResource()
    {
        $data = readFixture('foaf.json');
        $graph = new EasyRdf_Graph('http://example.com/joe/foaf.rdf', $data);
        $this->assertStringEquals(
            'Joe Bloggs',
            $graph->resource('http://www.example.com/joe#me')->get('foaf:name')
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

    public function testSetType()
    {
        $graph = new EasyRdf_Graph();
        $resource = $graph->resource('http://www.foo.com/bar', 'foo:Bar');
        $this->assertStringEquals('foo:Bar', $resource->type());
    }

    public function testSetMultipleTypes()
    {
        $graph = new EasyRdf_Graph();
        $resource = $graph->resource(
            'http://www.foo.com/bar',
            array('foo:Bar', 'bar:Foo')
        );

        $types = $resource->types();
        $this->assertEquals(2, count($types));
        $this->assertStringEquals('foo:Bar', $types[0]);
        $this->assertStringEquals('bar:Foo', $types[1]);
    }

    public function testResources()
    {
        $data = readFixture('foaf.json');
        $graph = new EasyRdf_Graph('http://example.com/joe/foaf.rdf', $data);
        $resources = array_values($graph->resources());
        $this->assertEquals(5, count($resources));
        $this->assertEquals(
            'http://www.example.com/joe#me',
            $resources[0]->getUri()
        );
        $this->assertEquals(
            '_:eid1',
            $resources[1]->getUri()
        );
        $this->assertEquals(
            'http://www.example.com/joe/',
            $resources[2]->getUri()
        );
        $this->assertEquals(
            'http://www.example.com/joe/foaf.rdf',
            $resources[3]->getUri()
        );
        $this->assertEquals(
            'http://www.example.com/project',
            $resources[4]->getUri()
        );

        $keys = array_keys($graph->resources());
        $this->assertEquals('http://www.example.com/joe#me', $keys[0]);
        $this->assertEquals('_:eid1', $keys[1]);
        $this->assertEquals('http://www.example.com/joe/', $keys[2]);
        $this->assertEquals('http://www.example.com/joe/foaf.rdf', $keys[3]);
        $this->assertEquals('http://www.example.com/project', $keys[4]);
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

    public function testAllTypes()
    {
        $data = readFixture('foaf.json');
        $graph = new EasyRdf_Graph('http://example.com/joe/foaf.rdf', $data);
        $types = $graph->allTypes();
        $this->assertTrue(is_array($types));
        $this->assertEquals(3, count($types));
        $this->assertEquals('foaf:Person', $types[0]);
        $this->assertEquals('foaf:Project', $types[1]);
        $this->assertEquals('foaf:PersonalProfileDocument', $types[2]);
    }

    public function testAddSingleValueToString()
    {
        $graph = new EasyRdf_Graph();
        $graph->add('http://www.example.com/joe#me', 'foaf:name', 'Joe');
        $resource = $graph->resource('http://www.example.com/joe#me');
        $this->assertStringEquals('Joe', $resource->get('foaf:name'));
    }

    public function testAddSingleValueToResource()
    {
        $graph = new EasyRdf_Graph();
        $resource = $graph->resource('http://www.example.com/joe#me');
        $graph->add($resource, 'foaf:name', 'Joe');
        $this->assertStringEquals('Joe', $resource->get('foaf:name'));
    }

    public function testAddMultipleValuesToString()
    {
        $graph = new EasyRdf_Graph();
        $resource = $graph->resource('http://www.example.com/joe#me');
        $graph->add(
            'http://www.example.com/joe#me',
            'foaf:name',
            array('Joe','Joseph')
        );

        $all = $resource->all('foaf:name');
        $this->assertStringEquals('Joe', $all[0]);
        $this->assertStringEquals('Joseph', $all[1]);
    }

    public function testAddMultipleValuesToResource()
    {
        $graph = new EasyRdf_Graph();
        $resource = $graph->resource('http://www.example.com/joe#me');
        $graph->add($resource, 'foaf:name', array('Joe','Joseph'));

        $all = $resource->all('foaf:name');
        $this->assertStringEquals('Joe', $all[0]);
        $this->assertStringEquals('Joseph', $all[1]);
    }

    public function testAddMultiplePropertiesToResource()
    {
        $graph = new EasyRdf_Graph();
        $resource = $graph->resource('http://www.example.com/joe#me');
        $graph->add(
            $resource,
            array(
                'foaf:givenname' => 'Joe',
                'foaf:surname' => 'Bloggs'
            )
        );
        $this->assertStringEquals('Joe', $resource->get('foaf:givenname'));
        $this->assertStringEquals('Bloggs', $resource->get('foaf:surname'));
    }

    public function testAddAnonymousBNodeToResource()
    {
        $graph = new EasyRdf_Graph();
        $resource = $graph->resource('http://www.example.com/joe#me');
        $graph->add(
            $resource, 'foaf:knows', array('foaf:name' => 'Yves')
        );
        $yves = $resource->get('foaf:knows');
        $this->assertTrue($yves->isBNode());
        $this->assertStringEquals('Yves', $yves->get('foaf:name'));
    }

    public function testAddTypedBNodeToResource()
    {
        $graph = new EasyRdf_Graph();
        $resource = $graph->resource('http://www.example.com/joe#me');
        $graph->add(
            $resource, 'foaf:knows', array('rdf:type' => 'foaf:Person')
        );
        $person = $resource->get('foaf:knows');
        $this->assertTrue($person->isBNode());
        $this->assertStringEquals('foaf:Person', $person->type());
    }

    public function testAddBNodeViaPropertiesToResource()
    {
        $graph = new EasyRdf_Graph();
        $resource = $graph->resource('http://www.example.com/joe#me');
        $graph->add(
            $resource, array('foaf:knows' => array('foaf:name' => 'Yves'))
        );
        $yves = $resource->get('foaf:knows');
        $this->assertTrue($yves->isBNode());
        $this->assertStringEquals('Yves', $yves->get('foaf:name'));
    }

    public function testAddPropertriesInvalidResourceClass()
    {
        $this->setExpectedException('InvalidArgumentException');
        $graph = new EasyRdf_Graph();
        $invalidResource = new EasyRdf_Utils();
        $graph->add($invalidResource, 'foo:bar', 'value');
    }

    public function testType()
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

    public function testDump()
    {
        $graph = new EasyRdf_Graph();
        $graph->add('http://example.com/joe#me', 'foaf:name', 'Joe');

        $text = $graph->dump(false);
        $this->assertContains('http://example.com/joe#me', $text);
        $this->assertContains('-> foaf:name -> "Joe"', $text);

        $html = $graph->dump(true);
        $this->assertContains('http://example.com/joe#me', $html);
        $this->assertContains('>foaf:name</span>', $html);
        $this->assertContains('>&quot;Joe&quot;</span>', $html);
    }

    public function testMagicGet()
    {
        $data = readFixture('foaf.json');
        $graph = new EasyRdf_Graph(
            'http://www.example.com/joe/foaf.rdf', $data
        );
        $this->assertStringEquals(
            "Joe Bloggs' FOAF File",
            $graph->label()
        );
        $this->assertStringEquals(
            "Joe Bloggs' FOAF File",
            $graph->getRdfs_label()
        );
    }

    public function testMagicGetUnknown()
    {
        $graph = new EasyRdf_Graph();
        $this->assertNull($graph->getRdfs_label());
    }

    public function testToString()
    {
        $data = readFixture('foaf.json');
        $graph = new EasyRdf_Graph('http://example.com/joe/foaf.rdf', $data);
        $this->assertStringEquals('http://example.com/joe/foaf.rdf', $graph);
    }
}
