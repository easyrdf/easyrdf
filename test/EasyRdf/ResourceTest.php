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

class EasyRdf_ResourceTest extends EasyRdf_TestCase
{
    /**
     * Set up the test suite before each test
     */
    public function setUp()
    {
        // Reset default namespace
        EasyRdf_Namespace::setDefault(null);
    }

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
        $this->assertSame(
            'http://example.com/testGetUri',
            $res->getUri()
        );
    }

    public function testIsBNode()
    {
        $bnode = new EasyRdf_Resource('_:foobar');
        $this->assertSame(true, $bnode->isBNode());
    }

    public function testIsNotBnode()
    {
        $nonbnode = new EasyRdf_Resource('http://www.exaple.com/');
        $this->assertSame(false, $nonbnode->isBNode());
    }

    public function testGetBNodeId()
    {
        $bnode = new EasyRdf_Resource('_:foobar');
        $this->assertSame('foobar', $bnode->getBNodeId());
    }

    public function testGetBNodeIdForUri()
    {
        $nonbnode = new EasyRdf_Resource('http://www.exaple.com/');
        $this->assertSame(null, $nonbnode->getBNodeId());
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
        $this->assertSame('foaf:name', $foafName->shorten());
    }

    public function testShortenUnknown()
    {
        $unknown = new EasyRdf_Resource('http://example.com/foo');
        $this->assertSame(null, $unknown->shorten());
    }

    public function testLocalnameWithSlash()
    {
        $res = new EasyRdf_Resource('http://xmlns.com/foaf/0.1/name');
        $this->assertSame('name', $res->localName());
    }

    public function testLocalnameWithHash()
    {
        $res = new EasyRdf_Resource('http://purl.org/vocab/aiiso/schema#College');
        $this->assertSame('College', $res->localName());
    }

    public function testLocalnameWithUrn()
    {
        $res = new EasyRdf_Resource('urn:isbn:978-0300067156');
        $this->assertSame('978-0300067156', $res->localName());
    }

    public function testLocalnameWithNoPath()
    {
        $res = new EasyRdf_Resource('http://example.com/');
        $this->assertSame(null, $res->localName());
    }

    public function testParseUri()
    {
        $res = new EasyRdf_Resource('http://example.com/foo/bar');
        $uri = $res->parseUri();
        $this->assertClass('EasyRdf_ParsedUri', $uri);
        $this->assertSame('/foo/bar', $uri->getPath());
    }

    public function testHtmlLinkNoText()
    {
        $res = new EasyRdf_Resource('http://example.com/');
        $this->assertSame('<a href="http://example.com/">http://example.com/</a>', $res->htmlLink());
    }

    public function testHtmlLinkWithText()
    {
        $res = new EasyRdf_Resource('http://example.com/');
        $this->assertSame(
            '<a href="http://example.com/">Click Here</a>',
            $res->htmlLink('Click Here')
        );
    }

    public function testHtmlLinkWithOptions()
    {
        $res = new EasyRdf_Resource('http://example.com/');
        $this->assertSame(
            '<a href="http://example.com/" style="font-weight: bold">Click Here</a>',
            $res->htmlLink('Click Here', array('style' => 'font-weight: bold'))
        );
    }

    public function testHtmlLinkWithEscaping()
    {
        $res = new EasyRdf_Resource('http://example.com/');
        $this->assertSame(
            '<a href="http://example.com/">=&gt; Click Here &lt;=</a>',
            $res->htmlLink('=> Click Here <=')
        );
    }

    public function testHtmlLinkInjectJavascript()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$options should use valid attribute names as keys'
        );

        $res = new EasyRdf_Resource('http://example.com/');
        $html = $res->htmlLink(null, array('onclick=alert(1) a' => 'b"'));
    }

    public function testToRdfPhpForUri()
    {
        $uri = new EasyRdf_Resource('http://www.example.com/');
        $this->assertSame(
            array('type' => 'uri', 'value' => 'http://www.example.com/'),
            $uri->toRdfPhp()
        );
    }

    public function testToRdfPhpForBnode()
    {
        $bnode = new EasyRdf_Resource('_:foobar');
        $this->assertSame(
            array('type' => 'bnode', 'value' => '_:foobar'),
            $bnode->toRdfPhp()
        );
    }

    public function testDumpValue()
    {
        $res = new EasyRdf_Resource('http://www.example.com/');
        $this->assertSame(
            "http://www.example.com/",
            $res->dumpValue('text')
        );
        $this->assertSame(
            "<a href='http://www.example.com/' ".
            "style='text-decoration:none;color:blue'>".
            "http://www.example.com/</a>",
            $res->dumpValue('html')
        );
    }

    public function testDumpValueWithColor()
    {
        $res = new EasyRdf_Resource('http://www.example.com/');
        $this->assertSame(
            "<a href='http://www.example.com/' ".
            "style='text-decoration:none;color:red'>".
            "http://www.example.com/</a>",
            $res->dumpValue('html', 'red')
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

    protected function setupTestGraph()
    {
        $this->graph = new EasyRdf_Graph();
        $this->type = $this->graph->resource('foaf:Person');
        $this->resource = $this->graph->resource('http://example.com/#me');
        $this->graph->set($this->resource, 'rdf:type', $this->type);
        $this->graph->add($this->resource, 'rdf:test', 'Test A');
        $this->graph->add($this->resource, 'rdf:test', new EasyRdf_Literal('Test B', 'en'));
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
        $this->assertSame(
            'http://example.com/testGetUriWithGraph',
            $res->getUri()
        );
    }

    public function testGetGraph()
    {
        $this->setupTestGraph();
        $this->assertSame(
            $this->graph,
            $this->resource->getGraph()
        );
    }

    public function testGet()
    {
        $this->setupTestGraph();
        $this->assertStringEquals(
            'Test A',
            $this->resource->get('rdf:test')
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
        $this->setupTestGraph();
        $this->assertStringEquals(
            'http://xmlns.com/foaf/0.1/Person',
            $this->resource->get('rdf:type')
        );
    }

    public function testGetWithUri()
    {
        $this->setupTestGraph();
        $this->assertStringEquals(
            'Test A',
            $this->resource->get(
                '<http://www.w3.org/1999/02/22-rdf-syntax-ns#test>'
            )
        );
    }

    public function testGetWithPropertyResource()
    {
        $this->setupTestGraph();
        $test = new EasyRdf_Resource(
            'http://www.w3.org/1999/02/22-rdf-syntax-ns#test',
            $this->graph
        );
        $this->assertStringEquals(
            'Test A',
            $this->resource->get($test)
        );
    }

    public function testGetWithLanguage()
    {
        $this->setupTestGraph();
        $this->assertStringEquals(
            'Test B',
            $this->resource->get('rdf:test', 'literal', 'en')
        );
    }

    public function testGetInverse()
    {
        $this->setupTestGraph();
        $homepage = new EasyRdf_Resource('http://example.com/', $this->graph);
        $this->resource->add('foaf:homepage', $homepage);
        $this->assertSame($this->resource, $homepage->get('^foaf:homepage'));
    }

    public function testGetMultipleProperties()
    {
        $this->setupTestGraph();
        $this->assertStringEquals(
            'Test A',
            $this->resource->get('rdf:test|rdf:foobar')
        );
    }

    public function testGetMultipleProperties2()
    {
        $this->setupTestGraph();
        $this->assertStringEquals(
            'Test A',
            $this->resource->get('rdf:foobar|rdf:test')
        );
    }

    public function testGetNonExistantProperty()
    {
        $this->setupTestGraph();
        $this->assertNull($this->resource->get('foo:bar'));
    }

    public function testGetNullKey()
    {
        $this->setupTestGraph();
        $this->setExpectedException(
            'InvalidArgumentException',
            '$propertyPath should be a string or EasyRdf_Resource and cannot be null'
        );
        $this->resource->get(null);
    }

    public function testGetEmptyKey()
    {
        $this->setupTestGraph();
        $this->setExpectedException(
            'InvalidArgumentException',
            '$propertyPath cannot be an empty string'
        );
        $this->resource->get('');
    }

    public function testGetNonStringKey()
    {
        $this->setupTestGraph();
        $this->setExpectedException(
            'InvalidArgumentException',
            '$propertyPath should be a string or EasyRdf_Resource and cannot be null'
        );
        $this->resource->get($this);
    }

    public function testGetLiteral()
    {
        $this->setupTestGraph();
        $this->assertStringEquals(
            'Test A',
            $this->resource->getLiteral('rdf:test')
        );
    }

    public function testGetLiteralForResource()
    {
        $this->setupTestGraph();
        $this->assertNull(
            $this->resource->getLiteral('rdf:type')
        );
    }

    public function testGetResource()
    {
        $this->setupTestGraph();
        $this->resource->addLiteral('foaf:homepage', 'Joe');
        $this->resource->addResource('foaf:homepage', 'http://example.com/');
        $this->assertStringEquals(
            'http://example.com/',
            $this->resource->getResource('foaf:homepage')
        );
    }

    public function testAll()
    {
        $this->setupTestGraph();
        $all = $this->resource->all('rdf:test');
        $this->assertCount(2, $all);
        $this->assertStringEquals('Test A', $all[0]);
        $this->assertStringEquals('Test B', $all[1]);
    }

    public function testAllWithUri()
    {
        $this->setupTestGraph();
        $all = $this->resource->all(
            '<http://www.w3.org/1999/02/22-rdf-syntax-ns#test>'
        );
        $this->assertCount(2, $all);
        $this->assertStringEquals('Test A', $all[0]);
        $this->assertStringEquals('Test B', $all[1]);
    }

    public function testAllWithResource()
    {
        $this->setupTestGraph();
        $prop = $this->graph->resource('http://www.w3.org/1999/02/22-rdf-syntax-ns#test');
        $all = $this->resource->all($prop);
        $this->assertCount(2, $all);
        $this->assertStringEquals('Test A', $all[0]);
        $this->assertStringEquals('Test B', $all[1]);
    }

    public function testAllWithLang()
    {
        $this->setupTestGraph();
        $all = $this->resource->all('rdf:test', 'literal', 'en');
        $this->assertCount(1, $all);
        $this->assertStringEquals('Test B', $all[0]);
    }

    public function testAllInverse()
    {
        $this->setupTestGraph();
        $all = $this->type->all('^rdf:type');
        $this->assertCount(1, $all);
        $this->assertSame($this->resource, $all[0]);
    }

    public function testAllNonExistantProperty()
    {
        $this->setupTestGraph();
        $this->assertSame(
            array(),
            $this->resource->all('foo:bar')
        );
    }

    public function testAllNullKey()
    {
        $this->setupTestGraph();
        $this->setExpectedException(
            'InvalidArgumentException',
            '$propertyPath should be a string or EasyRdf_Resource and cannot be null'
        );
        $this->resource->all(null);
    }

    public function testAllEmptyKey()
    {
        $this->setupTestGraph();
        $this->setExpectedException(
            'InvalidArgumentException',
            '$propertyPath cannot be an empty string'
        );
        $this->resource->all('');
    }

    public function testAllNonStringKey()
    {
        $this->setupTestGraph();
        $this->setExpectedException(
            'InvalidArgumentException',
            '$propertyPath should be a string or EasyRdf_Resource and cannot be null'
        );
        $this->resource->all(array());
    }

    public function testAllLiterals()
    {
        $this->setupTestGraph();
        $all = $this->resource->allLiterals('rdf:test');
        $this->assertCount(2, $all);
        $this->assertStringEquals('Test A', $all[0]);
        $this->assertStringEquals('Test B', $all[1]);
    }

    public function testAllLiteralsForResource()
    {
        $this->setupTestGraph();
        $all = $this->resource->allLiterals('rdf:type');
        $this->assertTrue(is_array($all));
        $this->assertCount(0, $all);
    }

    public function testAllResources()
    {
        $this->setupTestGraph();
        $this->resource->addResource('rdf:test', 'http://example.com/thing');
        $this->resource->addResource('rdf:test', '_:bnode1');
        $all = $this->resource->allResources('rdf:test');
        $this->assertCount(2, $all);
        $this->assertStringEquals('http://example.com/thing', $all[0]);
        $this->assertFalse($all[0]->isBNode());
        $this->assertStringEquals('_:bnode1', $all[1]);
        $this->assertTrue($all[1]->isBNode());
    }

    public function testAdd()
    {
        $this->setupTestGraph();
        $count = $this->resource->add('rdf:test', 'Test C');
        $this->assertSame(1, $count);
        $all = $this->resource->all('rdf:test');
        $this->assertCount(3, $all);
        $this->assertStringEquals('Test A', $all[0]);
        $this->assertStringEquals('Test B', $all[1]);
        $this->assertStringEquals('Test C', $all[2]);
    }

    public function testAddWithUri()
    {
        $this->setupTestGraph();
        $count = $this->resource->add(
            'http://www.w3.org/1999/02/22-rdf-syntax-ns#test',
            'Test C'
        );
        $this->assertSame(1, $count);
        $all = $this->resource->all('rdf:test');
        $this->assertCount(3, $all);
        $this->assertStringEquals('Test A', $all[0]);
        $this->assertStringEquals('Test B', $all[1]);
        $this->assertStringEquals('Test C', $all[2]);
    }

    public function testAddLiteral()
    {
        $this->setupTestGraph();
        $count = $this->resource->addLiteral('rdf:foobar', 'testAddLiteral');
        $this->assertSame(1, $count);
        $this->assertClass('EasyRdf_Literal', $this->resource->get('rdf:foobar'));
        $this->assertStringEquals('testAddLiteral', $this->resource->get('rdf:foobar'));
    }

    public function testAddLiteralWithLanguage()
    {
        $this->setupTestGraph();
        $count = $this->resource->addLiteral('dc:title', 'English Title', 'en');
        $this->assertSame(1, $count);
        $title = $this->resource->get('dc:title');
        $this->assertSame('English Title', $title->getValue());
        $this->assertSame('en', $title->getLang());
        $this->assertSame(null, $title->getDataType());
    }

    public function testAddResource()
    {
        $this->setupTestGraph();
        $count = $this->resource->addResource('rdf:foobar', 'http://testAddResource/');
        $this->assertSame(1, $count);
        $this->assertClass('EasyRdf_Resource', $this->resource->get('rdf:foobar'));
        $this->assertStringEquals('http://testAddResource/', $this->resource->get('rdf:foobar'));
    }

    public function testAddMultipleLiterals()
    {
        $this->setupTestGraph();
        $count = $this->resource->addLiteral('rdf:test', array('Test C', 'Test D'));
        $this->assertSame(2, $count);
        $all = $this->resource->all('rdf:test');
        $this->assertCount(4, $all);
        $this->assertStringEquals('Test A', $all[0]);
        $this->assertStringEquals('Test B', $all[1]);
        $this->assertStringEquals('Test C', $all[2]);
        $this->assertStringEquals('Test D', $all[3]);
    }

    public function testAddLiteralMultipleTimes()
    {
        $this->setupTestGraph();
        $count = $this->resource->add('rdf:test2', 'foobar');
        $this->assertSame(1, $count);
        $count = $this->resource->add('rdf:test2', 'foobar');
        $this->assertSame(0, $count);
        $all = $this->resource->all('rdf:test2');
        $this->assertCount(1, $all);
        $this->assertStringEquals('foobar', $all[0]);
    }

    public function testAddLiteralDifferentLanguages()
    {
        $this->setupTestGraph();
        $count = $this->resource->set('rdf:test', new EasyRdf_Literal('foobar', 'en'));
        $this->assertSame(1, $count);
        $count = $this->resource->add('rdf:test', new EasyRdf_Literal('foobar', 'fr'));
        $this->assertSame(1, $count);
        $all = $this->resource->all('rdf:test');
        $this->assertCount(2, $all);
        $this->assertStringEquals('foobar', $all[0]);
        $this->assertStringEquals('foobar', $all[1]);
    }

    public function testAddNull()
    {
        $this->setupTestGraph();
        $count = $this->resource->add('rdf:test', null);
        $this->assertSame(0, $count);
        $all = $this->resource->all('rdf:test');
        $this->assertCount(2, $all);
        $this->assertStringEquals('Test A', $all[0]);
        $this->assertStringEquals('Test B', $all[1]);
    }

    public function testAddNullKey()
    {
        $this->setupTestGraph();
        $this->setExpectedException(
            'InvalidArgumentException',
            '$property should be a string or EasyRdf_Resource and cannot be null'
        );
        $this->resource->add(null, 'Test C');
    }

    public function testAddEmptyKey()
    {
        $this->setupTestGraph();
        $this->setExpectedException(
            'InvalidArgumentException',
            '$property cannot be an empty string'
        );
        $this->resource->add('', 'Test C');
    }

    public function testAddNonStringKey()
    {
        $this->setupTestGraph();
        $this->setExpectedException(
            'InvalidArgumentException',
            '$property should be a string or EasyRdf_Resource and cannot be null'
        );
        $this->resource->add(array(), 'Test C');
    }

    public function testAddInvalidObject()
    {
        $this->setupTestGraph();
        $this->setExpectedException(
            'PHPUnit_Framework_Error',
            'Object of class EasyRdf_ResourceTest could not be converted to string'
        );
        $this->resource->add('rdf:foo', $this);
    }

    public function testDelete()
    {
        $this->setupTestGraph();
        $this->assertStringEquals('Test A', $this->resource->get('rdf:test'));
        $this->resource->delete('rdf:test');
        $this->assertSame(array(), $this->resource->all('rdf:test'));
    }

    public function testDeleteWithUri()
    {
        $this->setupTestGraph();
        $this->assertStringEquals('Test A', $this->resource->get('rdf:test'));
        $this->resource->delete(
            'http://www.w3.org/1999/02/22-rdf-syntax-ns#test'
        );
        $this->assertSame(array(), $this->resource->all('rdf:test'));
    }

    public function testDeleteNullKey()
    {
        $this->setupTestGraph();
        $this->setExpectedException(
            'InvalidArgumentException',
            '$property should be a string or EasyRdf_Resource and cannot be null'
        );
        $this->resource->delete(null);
    }

    public function testDeleteEmptyKey()
    {
        $this->setupTestGraph();
        $this->setExpectedException(
            'InvalidArgumentException',
            '$property cannot be an empty string'
        );
        $this->resource->delete('');
    }

    public function testDeleteNonStringKey()
    {
        $this->setupTestGraph();
        $this->setExpectedException(
            'InvalidArgumentException',
            '$property should be a string or EasyRdf_Resource and cannot be null'
        );
        $this->resource->delete(array());
    }

    public function testDeleteValue()
    {
        $this->setupTestGraph();
        $testa = $this->resource->get('rdf:test');
        $this->resource->delete('rdf:test', $testa);
        $all = $this->resource->all('rdf:test');
        $this->assertCount(1, $all);
    }

    public function testSet()
    {
        $this->setupTestGraph();
        $count = $this->resource->set('rdf:test', 'Test C');
        $this->assertSame(1, $count);
        $all = $this->resource->all('rdf:test');
        $this->assertCount(1, $all);
        $this->assertStringEquals('Test C', $all[0]);
    }

    public function testSetWithUri()
    {
        $this->setupTestGraph();
        $count = $this->resource->set(
            'http://www.w3.org/1999/02/22-rdf-syntax-ns#test',
            'Test C'
        );
        $this->assertSame(1, $count);
        $all = $this->resource->all('rdf:test');
        $this->assertCount(1, $all);
        $this->assertStringEquals('Test C', $all[0]);
    }

    public function testSetInverse()
    {
        $this->setupTestGraph();
        $homepage1 = new EasyRdf_Resource('http://example.com/1', $this->graph);
        $homepage2 = new EasyRdf_Resource('http://example.com/2', $this->graph);
        $count = $this->resource->set('foaf:homepage', $homepage1);
        $this->assertSame(1, $count);
        $this->assertSame(
            $this->resource,
            $homepage1->get('^foaf:homepage')
        );
        $this->assertSame(
            null,
            $homepage2->get('^foaf:homepage')
        );

        $count = $this->resource->set('foaf:homepage', $homepage2);
        $this->assertSame(1, $count);
        $this->assertSame(
            null,
            $homepage1->get('^foaf:homepage')
        );
        $this->assertSame(
            $this->resource,
            $homepage2->get('^foaf:homepage')
        );
    }

    public function testSetNullKey()
    {
        $this->setupTestGraph();
        $this->setExpectedException(
            'InvalidArgumentException',
            '$property should be a string or EasyRdf_Resource and cannot be null'
        );
        $this->resource->set(null, 'Test C');
    }

    public function testSetEmptyKey()
    {
        $this->setupTestGraph();
        $this->setExpectedException(
            'InvalidArgumentException',
            '$property cannot be an empty string'
        );
        $this->resource->set('', 'Test C');
    }

    public function testSetNonStringKey()
    {
        $this->setupTestGraph();
        $this->setExpectedException(
            'InvalidArgumentException',
            '$property should be a string or EasyRdf_Resource and cannot be null'
        );
        $this->resource->set(array(), 'Test C');
    }

    public function testSetNull()
    {
        $this->setupTestGraph();
        $count = $this->resource->set('rdf:test', null);
        $this->assertSame(0, $count);
        $this->assertSame(
            array(),
            $this->resource->all('rdf:test')
        );
    }

    public function testCountValues()
    {
        $this->setupTestGraph();
        $this->assertSame(2, $this->resource->countValues('rdf:test'));
    }

    public function countValuesNonExistantProperty()
    {
        $this->setupTestGraph();
        $this->assertSame(0, $this->resource->countValues('foo:bar'));
    }

    public function testJoinDefaultGlue()
    {
        $this->setupTestGraph();
        $this->assertSame(
            'Test A Test B',
            $this->resource->join('rdf:test')
        );
    }

    public function testJoinWithUri()
    {
        $this->setupTestGraph();
        $this->assertSame(
            'Test A Test B',
            $this->resource->join(
                '<http://www.w3.org/1999/02/22-rdf-syntax-ns#test>'
            )
        );
    }

    public function testJoinWithResource()
    {
        $this->setupTestGraph();
        $prop = $this->graph->resource('http://www.w3.org/1999/02/22-rdf-syntax-ns#test');
        $this->assertSame(
            'Test A Test B',
            $this->resource->join($prop)
        );
    }

    public function testJoinWithLang()
    {
        $this->setupTestGraph();
        $this->assertSame(
            'Test B',
            $this->resource->join('rdf:test', ' ', 'en')
        );
    }

    public function testJoinNonExistantProperty()
    {
        $this->setupTestGraph();
        $this->assertSame('', $this->resource->join('foo:bar'));
    }

    public function testJoinCustomGlue()
    {
        $this->setupTestGraph();
        $this->assertSame(
            'Test A:Test B',
            $this->resource->join('rdf:test', ':')
        );
    }

    public function testJoinNullKey()
    {
        $this->setupTestGraph();
        $this->setExpectedException(
            'InvalidArgumentException',
            '$propertyPath should be a string or EasyRdf_Resource and cannot be null'
        );
        $this->resource->join(null, 'Test C');
    }

    public function testJoinEmptyKey()
    {
        $this->setupTestGraph();
        $this->setExpectedException(
            'InvalidArgumentException',
            '$propertyPath cannot be an empty string'
        );
        $this->resource->join('', 'Test C');
    }

    public function testJoinNonStringKey()
    {
        $this->setupTestGraph();
        $this->setExpectedException(
            'InvalidArgumentException',
            '$propertyPath should be a string or EasyRdf_Resource and cannot be null'
        );
        $this->resource->join(array(), 'Test C');
    }

    public function testProperties()
    {
        $this->setupTestGraph();
        $this->assertSame(
            array('rdf:type', 'rdf:test'),
            $this->resource->properties()
        );
    }

    public function testPropertyUris()
    {
        $this->setupTestGraph();
        $this->assertSame(
            array(
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type',
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#test'
            ),
            $this->resource->propertyUris()
        );
    }

    public function testReversePropertyUris()
    {
        $this->setupTestGraph();
        $this->assertSame(
            array(
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type'
            ),
            $this->type->reversePropertyUris()
        );
    }

    public function testHasProperty()
    {
        $this->setupTestGraph();
        $this->assertTrue(
            $this->resource->hasProperty('rdf:type')
        );
    }

    public function testHasPropertyWithValue()
    {
        $this->setupTestGraph();
        $this->assertTrue(
            $this->resource->hasProperty('rdf:type', $this->type)
        );
    }

    public function testDoesntHaveProperty()
    {
        $this->setupTestGraph();
        $this->assertFalse(
            $this->resource->hasProperty('rdf:doesntexist')
        );
    }

    public function testTypes()
    {
        $this->setupTestGraph();
        $types = $this->resource->types();
        $this->assertCount(1, $types);
        $this->assertStringEquals('foaf:Person', $types[0]);
    }

    public function testType()
    {
        $this->setupTestGraph();
        $this->assertStringEquals('foaf:Person', $this->resource->type());
    }

    public function testTypeAsResource()
    {
        $this->setupTestGraph();
        $this->assertSame(
            $this->type,
            $this->resource->typeAsResource()
        );
    }

    public function testIsA()
    {
        $this->setupTestGraph();
        $this->assertTrue($this->resource->isA('foaf:Person'));
    }

    public function testIsAFullUri()
    {
        $this->setupTestGraph();
        $this->assertTrue(
            $this->resource->isA('http://xmlns.com/foaf/0.1/Person')
        );
    }

    public function testIsntA()
    {
        $this->setupTestGraph();
        $this->assertFalse($this->resource->isA('foaf:Rat'));
    }

    public function testAddType()
    {
        $this->setupTestGraph();
        $count = $this->resource->addType('rdf:newType');
        $this->assertSame(1, $count);
        $this->assertTrue(
            $this->resource->isA('rdf:newType')
        );
    }

    public function testSetType()
    {
        $this->setupTestGraph();
        $this->assertTrue(
            $this->resource->isA('foaf:Person')
        );
        $count = $this->resource->setType('foaf:Rat');
        $this->assertSame(1, $count);
        $this->assertTrue(
            $this->resource->isA('foaf:Rat')
        );
        $this->assertFalse(
            $this->resource->isA('foaf:Person')
        );
    }

    public function testPrimaryTopic()
    {
        $this->setupTestGraph();
        $doc = $this->graph->resource('http://example.com/foaf.rdf');
        $person = $this->graph->resource('http://example.com/foaf.rdf#me');
        $doc->add('foaf:primaryTopic', $person);
        $this->assertSame(
            'http://example.com/foaf.rdf#me',
            $doc->primaryTopic()->getUri()
        );
    }

    public function testIsPrimaryTopicOf()
    {
        $this->setupTestGraph();
        $doc = $this->graph->resource('http://example.com/foaf.rdf');
        $person = $this->graph->resource('http://example.com/foaf.rdf#me');
        $person->add('foaf:isPrimaryTopicOf', $doc);
        $this->assertSame(
            'http://example.com/foaf.rdf#me',
            $doc->primaryTopic()->getUri()
        );
    }

    public function testLabelWithRdfsLabel()
    {
        $this->setupTestGraph();
        $this->resource->set('rdfs:label', 'Label Text');
        $this->resource->set('foaf:name', 'Foaf Name');
        $this->resource->set('dc:title', 'Dc Title');
        $this->assertStringEquals('Label Text', $this->resource->label());
    }

    public function testLabelWithFoafName()
    {
        $this->setupTestGraph();
        $this->resource->set('foaf:name', 'Foaf Name');
        $this->resource->set('dc:title', 'Dc Title');
        $this->assertStringEquals('Foaf Name', $this->resource->label());
    }

    public function testLabelWithDc11Title()
    {
        $this->setupTestGraph();
        $this->resource->set('dc11:title', 'Dc11 Title');
        $this->assertStringEquals('Dc11 Title', $this->resource->label());
    }

    public function testLabelNoRdfsLabel()
    {
        $this->setupTestGraph();
        $this->assertNull($this->resource->label());
    }

    public function testLabelWithLang()
    {
        $this->setupTestGraph();
        $this->resource->set('rdfs:label', 'Label Text');
        $this->resource->set(
            'dc:title',
            new EasyRdf_Literal('Dc Title', 'en')
        );
        $this->assertStringEquals('Dc Title', $this->resource->label('en'));
    }

    public function testDump()
    {
        $this->setupTestGraph();
        $text = $this->resource->dump('text');
        $this->assertContains(
            "http://example.com/#me (EasyRdf_Resource)",
            $text
        );
        $this->assertContains(
            '-> rdf:type -> foaf:Person',
            $text
        );
        $this->assertContains(
            '-> rdf:test -> "Test A", "Test B"@en',
            $text
        );

        $html = $this->resource->dump();
        $this->assertContains("<div id='http://example.com/#me'", $html);
        $this->assertContains(
            "<a href='http://example.com/#me' ".
            "style='text-decoration:none;color:blue'>".
            "http://example.com/#me</a>",
            $html
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
            "<span style='color:black'>&quot;Test A&quot;</span>",
            $html
        );
        $this->assertContains(
            "<span style='color:black'>&quot;Test B&quot;@en</span>",
            $html
        );
    }

    public function testMagicGet()
    {
        $this->setupTestGraph();
        EasyRdf_Namespace::setDefault('rdf');
        $this->assertStringEquals(
            'Test A',
            $this->resource->test
        );
    }

    public function testMagicGetNonExistent()
    {
        $this->setupTestGraph();
        EasyRdf_Namespace::setDefault('rdf');
        $this->assertStringEquals(
            null,
            $this->resource->foobar
        );
    }

    public function testMagicSet()
    {
        $this->setupTestGraph();
        EasyRdf_Namespace::setDefault('rdf');
        $this->resource->test = 'testMagicSet';
        $this->assertStringEquals(
            'testMagicSet',
            $this->resource->get('rdf:test')
        );
    }

    public function testMagicIsSet()
    {
        $this->setupTestGraph();
        EasyRdf_Namespace::setDefault('rdf');
        $this->assertFalse(isset($this->resource->testMagicIsSet));
        $this->resource->add('rdf:testMagicIsSet', 'testMagicIsSet');
        $this->assertTrue(isset($this->resource->testMagicIsSet));
    }

    public function testMagicUnset()
    {
        $this->setupTestGraph();
        EasyRdf_Namespace::setDefault('rdf');
        $this->resource->add('rdf:testMagicUnset', 'testMagicUnset');
        unset($this->resource->testMagicUnset);
        $this->assertStringEquals(
            null,
            $this->resource->get('rdf:testMagicUnset')
        );
    }
}
