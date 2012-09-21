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

class EasyRdf_ResourceTest extends EasyRdf_TestCase
{
    public function testConstructNullUri()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$uri should be a string and cannot be null or empty'
        );
        $res = new EasyRdf_Resource(null);
    }

    public function testConstructEmptyUri()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$uri should be a string and cannot be null or empty'
        );
        $res = new EasyRdf_Resource('');
    }

    public function testConstructNonStringUri()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$uri should be a string and cannot be null or empty'
        );
        $res = new EasyRdf_Resource(array());
    }

    public function testConstructBadGraph()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$graph should be an EasyRdf_Graph object'
        );
        $res = new EasyRdf_Resource('http://www.example.com/', $this);
    }

    public function testGetUri()
    {
        $res = new EasyRdf_Resource('http://example.com/testGetUri');
        $this->assertEquals(
            'http://example.com/testGetUri',
            $res->getUri()
        );
    }

    public function testIsBnode()
    {
        $bnode = new EasyRdf_Resource('_:foobar');
        $this->assertEquals(true, $bnode->isBnode());
    }

    public function testIsNotBnode()
    {
        $nonbnode = new EasyRdf_Resource('http://www.exaple.com/');
        $this->assertEquals(false, $nonbnode->isBnode());
    }

    public function testGetNodeId()
    {
        $bnode = new EasyRdf_Resource('_:foobar');
        $this->assertEquals('foobar', $bnode->getNodeId());
    }

    public function testGetNodeIdForUri()
    {
        $nonbnode = new EasyRdf_Resource('http://www.exaple.com/');
        $this->assertEquals(null, $nonbnode->getNodeId());
    }

    public function testPrefix()
    {
        $foafName = new EasyRdf_Resource('http://xmlns.com/foaf/0.1/name');
        $this->assertStringEquals('foaf', $foafName->prefix());
    }

    public function testUnknownPrefix()
    {
        $unknown = new EasyRdf_Resource('http://example.com/foo');
        $this->assertNull($unknown->prefix());
    }

    public function testShorten()
    {
        $foafName = new EasyRdf_Resource('http://xmlns.com/foaf/0.1/name');
        $this->assertEquals('foaf:name', $foafName->shorten());
    }

    public function testShortenUnknown()
    {
        $unknown = new EasyRdf_Resource('http://example.com/foo');
        $this->assertEquals(null, $unknown->shorten());
    }

    public function testLocalnameWithSlash()
    {
        $res = new EasyRdf_Resource('http://xmlns.com/foaf/0.1/name');
        $this->assertEquals('name', $res->localName());
    }

    public function testLocalnameWithHash()
    {
        $res = new EasyRdf_Resource('http://purl.org/vocab/aiiso/schema#College');
        $this->assertEquals('College', $res->localName());
    }

    public function testLocalnameWithUrn()
    {
        $res = new EasyRdf_Resource('urn:isbn:978-0300067156');
        $this->assertEquals('978-0300067156', $res->localName());
    }

    public function testLocalnameWithNoPath()
    {
        $res = new EasyRdf_Resource('http://example.com/');
        $this->assertEquals(NULL, $res->localName());
    }

    public function testParseUri()
    {
        $res = new EasyRdf_Resource('http://example.com/foo/bar');
        $uri = $res->parseUri();
        $this->assertInstanceOf('EasyRdf_ParsedUri', $uri);
        $this->assertEquals('/foo/bar', $uri->getPath());
    }

    public function testHtmlLinkNoText()
    {
        $res = new EasyRdf_Resource('http://example.com/');
        $this->assertEquals('<a href="http://example.com/">http://example.com/</a>', $res->htmlLink());
    }

    public function testHtmlLinkWithText()
    {
        $res = new EasyRdf_Resource('http://example.com/');
        $this->assertEquals(
            '<a href="http://example.com/">Click Here</a>',
            $res->htmlLink('Click Here')
        );
    }

    public function testHtmlLinkWithOptions()
    {
        $res = new EasyRdf_Resource('http://example.com/');
        $this->assertEquals(
            '<a href="http://example.com/" style="font-weight: bold">Click Here</a>',
            $res->htmlLink('Click Here', array('style' => 'font-weight: bold'))
        );
    }

    public function testHtmlLinkWithEscaping()
    {
        $res = new EasyRdf_Resource('http://example.com/');
        $this->assertEquals(
            '<a href="http://example.com/">=&gt; Click Here &lt;=</a>',
            $res->htmlLink('=> Click Here <=')
        );
    }

    public function testToArrayForUri()
    {
        $uri = new EasyRdf_Resource('http://www.example.com/');
        $this->assertEquals(
            array('type' => 'uri', 'value' => 'http://www.example.com/'),
            $uri->toArray()
        );
    }

    public function testToArrayForBnode()
    {
        $bnode = new EasyRdf_Resource('_:foobar');
        $this->assertEquals(
            array('type' => 'bnode', 'value' => '_:foobar'),
            $bnode->toArray()
        );
    }

    public function testDumpValue()
    {
        $res = new EasyRdf_Resource('http://www.example.com/');
        $this->assertEquals(
            "http://www.example.com/",
            $res->dumpValue(false)
        );
        $this->assertEquals(
            "<a href='http://www.example.com/' ".
            "style='text-decoration:none;color:blue'>".
            "http://www.example.com/</a>",
            $res->dumpValue(true)
        );
    }

    public function testDumpValueWithColor()
    {
        $res = new EasyRdf_Resource('http://www.example.com/');
        $this->assertEquals(
            "<a href='http://www.example.com/' ".
            "style='text-decoration:none;color:red'>".
            "http://www.example.com/</a>",
            $res->dumpValue(true, 'red')
        );
    }

    public function testToString()
    {
        $res = new EasyRdf_Resource('http://example.com/testToString');
        $this->assertStringEquals('http://example.com/testToString', $res);
    }




    /*
     *
     * The following tests require a graph of test data
     *
     */

    protected function _setupTestGraph()
    {
        $this->_graph = new EasyRdf_Graph();
        $this->_type = $this->_graph->resource('foaf:Person');
        $this->_resource = $this->_graph->resource('http://example.com/#me');
        $this->_graph->set($this->_resource, 'rdf:type', $this->_type);
        $this->_graph->add($this->_resource, 'rdf:test', 'Test A');
        $this->_graph->add($this->_resource, 'rdf:test', new EasyRdf_Literal('Test B', 'en'));
    }

    public function testLoad()
    {
        EasyRdf_Http::setDefaultHttpClient(
            $client = new EasyRdf_Http_MockClient()
        );
        $client->addMock('GET', 'http://example.com/foaf.json', readFixture('foaf.json'));
        $graph = new EasyRdf_Graph('http://example.com/');
        $resource = $graph->resource('http://example.com/foaf.json');
        $resource->load();
        $this->assertStringEquals(
            'Joe Bloggs',
            $graph->get('http://www.example.com/joe#me', 'foaf:name')
        );
    }

    public function testGetUriWithGraph()
    {
        $graph = new EasyRdf_Graph();
        $res = new EasyRdf_Resource('http://example.com/testGetUriWithGraph', $graph);
        $this->assertEquals(
            'http://example.com/testGetUriWithGraph',
            $res->getUri()
        );
    }

    public function testGet()
    {
        $this->_setupTestGraph();
        $this->assertStringEquals(
            'Test A',
            $this->_resource->get('rdf:test')
        );
    }

    public function testGetWithoutGraph()
    {
        $this->setExpectedException(
            'EasyRdf_Exception',
            'EasyRdf_Resource is not part of a graph.'
        );
        $res = new EasyRdf_Resource('http://example.com/');
        $res->get('rdf:test');
    }

    public function testGetAResource()
    {
        $this->_setupTestGraph();
        $this->assertStringEquals(
            'http://xmlns.com/foaf/0.1/Person',
            $this->_resource->get('rdf:type')
        );
    }

    public function testGetWithUri()
    {
        $this->_setupTestGraph();
        $this->assertStringEquals(
            'Test A',
            $this->_resource->get(
                '<http://www.w3.org/1999/02/22-rdf-syntax-ns#test>'
            )
        );
    }

    public function testGetWithPropertyResource()
    {
        $this->_setupTestGraph();
        $test = new EasyRdf_Resource(
            'http://www.w3.org/1999/02/22-rdf-syntax-ns#test',
            $this->_graph
        );
        $this->assertStringEquals(
            'Test A',
            $this->_resource->get($test)
        );
    }

    public function testGetWithLanguage()
    {
        $this->_setupTestGraph();
        $this->assertStringEquals(
            'Test B',
            $this->_resource->get('rdf:test', 'literal', 'en')
        );
    }

    public function testGetInverse()
    {
        $this->_setupTestGraph();
        $homepage = new EasyRdf_Resource('http://example.com/', $this->_graph);
        $this->_resource->add('foaf:homepage', $homepage);
        $this->assertEquals($this->_resource, $homepage->get('^foaf:homepage'));
    }

    public function testGetMultipleProperties()
    {
        $this->_setupTestGraph();
        $this->assertStringEquals(
            'Test A',
            $this->_resource->get('rdf:test|rdf:foobar')
        );
    }

    public function testGetMultipleProperties2()
    {
        $this->_setupTestGraph();
        $this->assertStringEquals(
            'Test A',
            $this->_resource->get('rdf:foobar|rdf:test')
        );
    }

    public function testGetNonExistantProperty()
    {
        $this->_setupTestGraph();
        $this->assertNull($this->_resource->get('foo:bar'));
    }

    public function testGetNullKey()
    {
        $this->_setupTestGraph();
        $this->setExpectedException(
            'InvalidArgumentException',
            '$propertyPath should be a string or EasyRdf_Resource and cannot be null'
        );
        $this->_resource->get(null);
    }

    public function testGetEmptyKey()
    {
        $this->_setupTestGraph();
        $this->setExpectedException(
            'InvalidArgumentException',
            '$propertyPath cannot be an empty string'
        );
        $this->_resource->get('');
    }

    public function testGetNonStringKey()
    {
        $this->_setupTestGraph();
        $this->setExpectedException(
            'InvalidArgumentException',
            '$propertyPath should be a string or EasyRdf_Resource and cannot be null'
        );
        $this->_resource->get($this);
    }

    public function testGetLiteral()
    {
        $this->_setupTestGraph();
        $this->assertStringEquals(
            'Test A',
            $this->_resource->getLiteral('rdf:test')
        );
    }

    public function testGetLiteralForResource()
    {
        $this->_setupTestGraph();
        $this->assertNull(
            $this->_resource->getLiteral('rdf:type')
        );
    }

    public function testGetResource()
    {
        $this->_setupTestGraph();
        $this->_resource->addLiteral('foaf:homepage', 'Joe');
        $this->_resource->addResource('foaf:homepage', 'http://example.com/');
        $this->assertStringEquals(
            'http://example.com/', $this->_resource->getResource('foaf:homepage')
        );
    }

    public function testAll()
    {
        $this->_setupTestGraph();
        $all = $this->_resource->all('rdf:test');
        $this->assertEquals(2, count($all));
        $this->assertStringEquals('Test A', $all[0]);
        $this->assertStringEquals('Test B', $all[1]);
    }

    public function testAllWithUri()
    {
        $this->_setupTestGraph();
        $all = $this->_resource->all(
            '<http://www.w3.org/1999/02/22-rdf-syntax-ns#test>'
        );
        $this->assertEquals(2, count($all));
        $this->assertStringEquals('Test A', $all[0]);
        $this->assertStringEquals('Test B', $all[1]);
    }

    public function testAllWithResource()
    {
        $this->_setupTestGraph();
        $prop = $this->_graph->resource('http://www.w3.org/1999/02/22-rdf-syntax-ns#test');
        $all = $this->_resource->all($prop);
        $this->assertEquals(2, count($all));
        $this->assertStringEquals('Test A', $all[0]);
        $this->assertStringEquals('Test B', $all[1]);
    }

    public function testAllWithLang()
    {
        $this->_setupTestGraph();
        $all = $this->_resource->all('rdf:test', 'literal', 'en');
        $this->assertEquals(1, count($all));
        $this->assertStringEquals('Test B', $all[0]);
    }

    public function testAllInverse()
    {
        $this->_setupTestGraph();
        $all = $this->_type->all('^rdf:type');
        $this->assertEquals(1, count($all));
        $this->assertEquals($this->_resource, $all[0]);
    }

    public function testAllNonExistantProperty()
    {
        $this->_setupTestGraph();
        $this->assertEquals(
            array(),
            $this->_resource->all('foo:bar')
        );
    }

    public function testAllNullKey()
    {
        $this->_setupTestGraph();
        $this->setExpectedException(
            'InvalidArgumentException',
            '$propertyPath should be a string or EasyRdf_Resource and cannot be null'
        );
        $this->_resource->all(null);
    }

    public function testAllEmptyKey()
    {
        $this->_setupTestGraph();
        $this->setExpectedException(
            'InvalidArgumentException',
            '$propertyPath cannot be an empty string'
        );
        $this->_resource->all('');
    }

    public function testAllNonStringKey()
    {
        $this->_setupTestGraph();
        $this->setExpectedException(
            'InvalidArgumentException',
            '$propertyPath should be a string or EasyRdf_Resource and cannot be null'
        );
        $this->_resource->all(array());
    }

    public function testAllLiterals()
    {
        $this->_setupTestGraph();
        $all = $this->_resource->allLiterals('rdf:test');
        $this->assertEquals(2, count($all));
        $this->assertStringEquals('Test A', $all[0]);
        $this->assertStringEquals('Test B', $all[1]);
    }

    public function testAllLiteralsForResource()
    {
        $this->_setupTestGraph();
        $all = $this->_resource->allLiterals('rdf:type');
        $this->assertTrue(is_array($all));
        $this->assertEquals(0, count($all));
    }

    public function testAllResources()
    {
        $this->_setupTestGraph();
        $this->_resource->addResource('rdf:test', 'http://example.com/thing');
        $this->_resource->addResource('rdf:test', '_:bnode1');
        $all = $this->_resource->allResources('rdf:test');
        $this->assertEquals(2, count($all));
        $this->assertStringEquals('http://example.com/thing', $all[0]);
        $this->assertFalse($all[0]->isBnode());
        $this->assertStringEquals('_:bnode1', $all[1]);
        $this->assertTrue($all[1]->isBnode());
    }

    public function testAdd()
    {
        $this->_setupTestGraph();
        $count = $this->_resource->add('rdf:test', 'Test C');
        $this->assertEquals(1, $count);
        $all = $this->_resource->all('rdf:test');
        $this->assertEquals(3, count($all));
        $this->assertStringEquals('Test A', $all[0]);
        $this->assertStringEquals('Test B', $all[1]);
        $this->assertStringEquals('Test C', $all[2]);
    }

    public function testAddWithUri()
    {
        $this->_setupTestGraph();
        $count = $this->_resource->add(
            'http://www.w3.org/1999/02/22-rdf-syntax-ns#test',
            'Test C'
        );
        $this->assertEquals(1, $count);
        $all = $this->_resource->all('rdf:test');
        $this->assertEquals(3, count($all));
        $this->assertStringEquals('Test A', $all[0]);
        $this->assertStringEquals('Test B', $all[1]);
        $this->assertStringEquals('Test C', $all[2]);
    }

    public function testAddLiteral()
    {
        $this->_setupTestGraph();
        $count = $this->_resource->addLiteral('rdf:foobar', 'testAddLiteral');
        $this->assertEquals(1, $count);
        $this->assertInstanceOf('EasyRdf_Literal', $this->_resource->get('rdf:foobar'));
        $this->assertStringEquals('testAddLiteral', $this->_resource->get('rdf:foobar'));
    }

    public function testAddLiteralWithLanguage()
    {
        $this->_setupTestGraph();
        $count = $this->_resource->addLiteral('dc:title', 'English Title', 'en');
        $this->assertEquals(1, $count);
        $title = $this->_resource->get('dc:title');
        $this->assertEquals('English Title', $title->getValue());
        $this->assertEquals('en', $title->getLang());
        $this->assertEquals(null, $title->getDataType());
    }

    public function testAddResource()
    {
        $this->_setupTestGraph();
        $count = $this->_resource->addResource('rdf:foobar', 'http://testAddResource/');
        $this->assertEquals(1, $count);
        $this->assertInstanceOf('EasyRdf_Resource', $this->_resource->get('rdf:foobar'));
        $this->assertStringEquals('http://testAddResource/', $this->_resource->get('rdf:foobar'));
    }

    public function testAddMultipleLiterals()
    {
        $this->_setupTestGraph();
        $count = $this->_resource->addLiteral('rdf:test', array('Test C', 'Test D'));
        $this->assertEquals(2, $count);
        $all = $this->_resource->all('rdf:test');
        $this->assertEquals(4, count($all));
        $this->assertStringEquals('Test A', $all[0]);
        $this->assertStringEquals('Test B', $all[1]);
        $this->assertStringEquals('Test C', $all[2]);
        $this->assertStringEquals('Test D', $all[3]);
    }

    public function testAddLiteralMultipleTimes()
    {
        $this->_setupTestGraph();
        $count = $this->_resource->add('rdf:test2', 'foobar');
        $this->assertEquals(1, $count);
        $count = $this->_resource->add('rdf:test2', 'foobar');
        $this->assertEquals(0, $count);
        $all = $this->_resource->all('rdf:test2');
        $this->assertEquals(1, count($all));
        $this->assertStringEquals('foobar', $all[0]);
    }

    public function testAddLiteralDifferentLanguages()
    {
        $this->_setupTestGraph();
        $count = $this->_resource->set('rdf:test', new EasyRdf_Literal('foobar', 'en'));
        $this->assertEquals(1, $count);
        $count = $this->_resource->add('rdf:test', new EasyRdf_Literal('foobar', 'fr'));
        $this->assertEquals(1, $count);
        $all = $this->_resource->all('rdf:test');
        $this->assertEquals(2, count($all));
        $this->assertStringEquals('foobar', $all[0]);
        $this->assertStringEquals('foobar', $all[1]);
    }

    public function testAddNull()
    {
        $this->_setupTestGraph();
        $count = $this->_resource->add('rdf:test', null);
        $this->assertEquals(0, $count);
        $all = $this->_resource->all('rdf:test');
        $this->assertEquals(2, count($all));
        $this->assertStringEquals('Test A', $all[0]);
        $this->assertStringEquals('Test B', $all[1]);
    }

    public function testAddNullKey()
    {
        $this->_setupTestGraph();
        $this->setExpectedException(
            'InvalidArgumentException',
            '$property should be a string or EasyRdf_Resource and cannot be null'
        );
        $this->_resource->add(null, 'Test C');
    }

    public function testAddEmptyKey()
    {
        $this->_setupTestGraph();
        $this->setExpectedException(
            'InvalidArgumentException',
            '$property cannot be an empty string'
        );
        $this->_resource->add('', 'Test C');
    }

    public function testAddNonStringKey()
    {
        $this->_setupTestGraph();
        $this->setExpectedException(
            'InvalidArgumentException',
            '$property should be a string or EasyRdf_Resource and cannot be null'
        );
        $this->_resource->add(array(), 'Test C');
    }

    function testAddInvalidObject()
    {
        $this->_setupTestGraph();
        $this->setExpectedException(
            'InvalidArgumentException',
            '$value should respond to the method toArray()'
        );
        $this->_resource->add('rdf:foo', $this);
    }

    public function testDelete()
    {
        $this->_setupTestGraph();
        $this->assertStringEquals('Test A', $this->_resource->get('rdf:test'));
        $this->_resource->delete('rdf:test');
        $this->assertEquals(array(), $this->_resource->all('rdf:test'));
    }

    public function testDeleteWithUri()
    {
        $this->_setupTestGraph();
        $this->assertStringEquals('Test A', $this->_resource->get('rdf:test'));
        $this->_resource->delete(
            'http://www.w3.org/1999/02/22-rdf-syntax-ns#test'
        );
        $this->assertEquals(array(), $this->_resource->all('rdf:test'));
    }

    public function testDeleteNullKey()
    {
        $this->_setupTestGraph();
        $this->setExpectedException(
            'InvalidArgumentException',
            '$property should be a string or EasyRdf_Resource and cannot be null'
        );
        $this->_resource->delete(null);
    }

    public function testDeleteEmptyKey()
    {
        $this->_setupTestGraph();
        $this->setExpectedException(
            'InvalidArgumentException',
            '$property cannot be an empty string'
        );
        $this->_resource->delete('');
    }

    public function testDeleteNonStringKey()
    {
        $this->_setupTestGraph();
        $this->setExpectedException(
            'InvalidArgumentException',
            '$property should be a string or EasyRdf_Resource and cannot be null'
        );
        $this->_resource->delete(array());
    }

    public function testDeleteValue()
    {
        $this->_setupTestGraph();
        $testa = $this->_resource->get('rdf:test');
        $this->_resource->delete('rdf:test', $testa);
        $all = $this->_resource->all('rdf:test');
        $this->assertEquals(1, count($all));
    }

    public function testSet()
    {
        $this->_setupTestGraph();
        $count = $this->_resource->set('rdf:test', 'Test C');
        $this->assertEquals(1, $count);
        $all = $this->_resource->all('rdf:test');
        $this->assertEquals(1, count($all));
        $this->assertStringEquals('Test C', $all[0]);
    }

    public function testSetWithUri()
    {
        $this->_setupTestGraph();
        $count = $this->_resource->set(
            'http://www.w3.org/1999/02/22-rdf-syntax-ns#test',
            'Test C'
        );
        $this->assertEquals(1, $count);
        $all = $this->_resource->all('rdf:test');
        $this->assertEquals(1, count($all));
        $this->assertStringEquals('Test C', $all[0]);
    }

    public function testSetInverse()
    {
        $this->_setupTestGraph();
        $homepage1 = new EasyRdf_Resource('http://example.com/1', $this->_graph);
        $homepage2 = new EasyRdf_Resource('http://example.com/2', $this->_graph);
        $count = $this->_resource->set('foaf:homepage', $homepage1);
        $this->assertEquals(1, $count);
        $this->assertEquals(
            $this->_resource,
            $homepage1->get('^foaf:homepage')
        );
        $this->assertEquals(
            null,
            $homepage2->get('^foaf:homepage')
        );

        $count = $this->_resource->set('foaf:homepage', $homepage2);
        $this->assertEquals(1, $count);
        $this->assertEquals(
            null,
            $homepage1->get('^foaf:homepage')
        );
        $this->assertEquals(
            $this->_resource,
            $homepage2->get('^foaf:homepage')
        );
    }

    public function testSetNullKey()
    {
        $this->_setupTestGraph();
        $this->setExpectedException(
            'InvalidArgumentException',
            '$property should be a string or EasyRdf_Resource and cannot be null'
        );
        $this->_resource->set(null, 'Test C');
    }

    public function testSetEmptyKey()
    {
        $this->_setupTestGraph();
        $this->setExpectedException(
            'InvalidArgumentException',
            '$property cannot be an empty string'
        );
        $this->_resource->set('', 'Test C');
    }

    public function testSetNonStringKey()
    {
        $this->_setupTestGraph();
        $this->setExpectedException(
            'InvalidArgumentException',
            '$property should be a string or EasyRdf_Resource and cannot be null'
        );
        $this->_resource->set(array(), 'Test C');
    }

    public function testSetNull()
    {
        $this->_setupTestGraph();
        $count = $this->_resource->set('rdf:test', null);
        $this->assertEquals(0, $count);
        $this->assertEquals(
            array(),
            $this->_resource->all('rdf:test')
        );
    }

    public function testCount()
    {
        $this->_setupTestGraph();
        $this->assertEquals(2, $this->_resource->count('rdf:test'));
    }

    public function testCountNonExistantProperty()
    {
        $this->_setupTestGraph();
        $this->assertEquals(0, $this->_resource->count('foo:bar'));
    }

    public function testJoinDefaultGlue()
    {
        $this->_setupTestGraph();
        $this->assertEquals(
            'Test A Test B',
            $this->_resource->join('rdf:test')
        );
    }

    public function testJoinWithUri()
    {
        $this->_setupTestGraph();
        $this->assertEquals(
            'Test A Test B',
            $this->_resource->join(
                '<http://www.w3.org/1999/02/22-rdf-syntax-ns#test>'
            )
        );
    }

    public function testJoinWithResource()
    {
        $this->_setupTestGraph();
        $prop = $this->_graph->resource('http://www.w3.org/1999/02/22-rdf-syntax-ns#test');
        $this->assertEquals(
            'Test A Test B',
            $this->_resource->join($prop)
        );
    }

    public function testJoinWithLang()
    {
        $this->_setupTestGraph();
        $this->assertEquals(
            'Test B',
            $this->_resource->join('rdf:test', ' ', 'en')
        );
    }

    public function testJoinNonExistantProperty()
    {
        $this->_setupTestGraph();
        $this->assertEquals('', $this->_resource->join('foo:bar'));
    }

    public function testJoinCustomGlue()
    {
        $this->_setupTestGraph();
        $this->assertEquals(
            'Test A:Test B',
            $this->_resource->join('rdf:test', ':')
        );
    }

    public function testJoinNullKey()
    {
        $this->_setupTestGraph();
        $this->setExpectedException(
            'InvalidArgumentException',
            '$propertyPath should be a string or EasyRdf_Resource and cannot be null'
        );
        $this->_resource->join(null, 'Test C');
    }

    public function testJoinEmptyKey()
    {
        $this->_setupTestGraph();
        $this->setExpectedException(
            'InvalidArgumentException',
            '$propertyPath cannot be an empty string'
        );
        $this->_resource->join('', 'Test C');
    }

    public function testJoinNonStringKey()
    {
        $this->_setupTestGraph();
        $this->setExpectedException(
            'InvalidArgumentException',
            '$propertyPath should be a string or EasyRdf_Resource and cannot be null'
        );
        $this->_resource->join(array(), 'Test C');
    }

    public function testProperties()
    {
        $this->_setupTestGraph();
        $this->assertEquals(
            array('rdf:type', 'rdf:test'),
            $this->_resource->properties()
        );
    }

    public function testPropertyUris()
    {
        $this->_setupTestGraph();
        $this->assertEquals(
            array(
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type',
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#test'
            ),
            $this->_resource->propertyUris()
        );
    }

    public function testReversePropertyUris()
    {
        $this->_setupTestGraph();
        $this->assertEquals(
            array(
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type'
            ),
            $this->_type->reversePropertyUris()
        );
    }

    public function testHasProperty()
    {
        $this->_setupTestGraph();
        $this->assertTrue(
            $this->_resource->hasProperty('rdf:type')
        );
    }

    public function testDoesntHaveProperty()
    {
        $this->_setupTestGraph();
        $this->assertFalse(
            $this->_resource->hasProperty('rdf:doesntexist')
        );
    }

    public function testTypes()
    {
        $this->_setupTestGraph();
        $types = $this->_resource->types();
        $this->assertEquals(1, count($types));
        $this->assertStringEquals('foaf:Person', $types[0]);
    }

    public function testType()
    {
        $this->_setupTestGraph();
        $this->assertStringEquals('foaf:Person', $this->_resource->type());
    }

    public function testTypeAsResource()
    {
        $this->_setupTestGraph();
        $this->assertEquals(
            $this->_type,
            $this->_resource->typeAsResource()
        );
    }

    public function testIsA()
    {
        $this->_setupTestGraph();
        $this->assertTrue($this->_resource->is_a('foaf:Person'));
    }

    public function testIsAFullUri()
    {
        $this->_setupTestGraph();
        $this->assertTrue(
            $this->_resource->is_a('http://xmlns.com/foaf/0.1/Person')
        );
    }

    public function testIsntA()
    {
        $this->_setupTestGraph();
        $this->assertFalse($this->_resource->is_a('foaf:Rat'));
    }

    public function testAddType()
    {
        $this->_setupTestGraph();
        $count = $this->_resource->addType('rdf:newType');
        $this->assertEquals(1, $count);
        $this->assertTrue(
            $this->_resource->is_a('rdf:newType')
        );
    }

    public function testSetType()
    {
        $this->_setupTestGraph();
        $this->assertTrue(
            $this->_resource->is_a('foaf:Person')
        );
        $count = $this->_resource->setType('foaf:Rat');
        $this->assertEquals(1, $count);
        $this->assertTrue(
            $this->_resource->is_a('foaf:Rat')
        );
        $this->assertFalse(
            $this->_resource->is_a('foaf:Person')
        );
    }

    public function testPrimaryTopic()
    {
        $this->_setupTestGraph();
        $doc = $this->_graph->resource('http://example.com/foaf.rdf');
        $person = $this->_graph->resource('http://example.com/foaf.rdf#me');
        $doc->add('foaf:primaryTopic', $person);
        $this->assertEquals(
            'http://example.com/foaf.rdf#me',
            $doc->primaryTopic()->getUri()
        );
    }

    public function testIsPrimaryTopicOf()
    {
        $this->_setupTestGraph();
        $doc = $this->_graph->resource('http://example.com/foaf.rdf');
        $person = $this->_graph->resource('http://example.com/foaf.rdf#me');
        $person->add('foaf:isPrimaryTopicOf', $doc);
        $this->assertEquals(
            'http://example.com/foaf.rdf#me',
            $doc->primaryTopic()->getUri()
        );
    }

    public function testLabelWithRdfsLabel()
    {
        $this->_setupTestGraph();
        $this->_resource->set('rdfs:label', 'Label Text');
        $this->_resource->set('foaf:name', 'Foaf Name');
        $this->_resource->set('dc:title', 'Dc Title');
        $this->assertStringEquals('Label Text', $this->_resource->label());
    }

    public function testLabelWithFoafName()
    {
        $this->_setupTestGraph();
        $this->_resource->set('foaf:name', 'Foaf Name');
        $this->_resource->set('dc:title', 'Dc Title');
        $this->assertStringEquals('Foaf Name', $this->_resource->label());
    }

    public function testLabelWithDc11Title()
    {
        $this->_setupTestGraph();
        $this->_resource->set('dc11:title', 'Dc11 Title');
        $this->assertStringEquals('Dc11 Title', $this->_resource->label());
    }

    public function testLabelNoRdfsLabel()
    {
        $this->_setupTestGraph();
        $this->assertNull($this->_resource->label());
    }

    public function testLabelWithLang()
    {
        $this->_setupTestGraph();
        $this->_resource->set('rdfs:label', 'Label Text');
        $this->_resource->set(
            'dc:title',
            new EasyRdf_Literal('Dc Title', 'en')
        );
        $this->assertStringEquals('Dc Title', $this->_resource->label('en'));
    }

    public function testDump()
    {
        $this->_setupTestGraph();
        $text = $this->_resource->dump(false);
        $this->assertContains(
            "http://example.com/#me (EasyRdf_Resource)", $text
        );
        $this->assertContains(
            '-> rdf:type -> foaf:Person', $text
        );
        $this->assertContains(
            '-> rdf:test -> "Test A", "Test B"@en', $text
        );

        $html = $this->_resource->dump(true);
        $this->assertContains("<div id='http://example.com/#me'", $html);
        $this->assertContains(
            "<a href='http://example.com/#me' ".
            "style='text-decoration:none;color:blue'>".
            "http://example.com/#me</a>", $html
        );
        $this->assertContains(
            "<span style='text-decoration:none;color:green'>rdf:type</span>",
            $html
        );
        $this->assertContains(
            "<a href='http://xmlns.com/foaf/0.1/Person' ".
            "style='text-decoration:none;color:blue'>foaf:Person</a>",
            $html
        );
        $this->assertContains(
            "<span style='text-decoration:none;color:green'>rdf:test</span>",
            $html
        );
        $this->assertContains(
            "<span style='color:black'>&quot;Test A&quot;</span>", $html
        );
        $this->assertContains(
            "<span style='color:black'>&quot;Test B&quot;@en</span>", $html
        );
    }
}
