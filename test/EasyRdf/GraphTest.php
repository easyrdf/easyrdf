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

$validRdf = array(
                'http://example.com/joe' => array(
                    'http://xmlns.com/foaf/0.1/name' => array(
                        array(
                            'value' => 'Joseph Bloggs',
                            'type' => 'literal',
                            'lang' => 'en'
                        )
                    )
                )
            );

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
    public function parse($uri, $data, $format)
    {
        global $validRdf;
        if ($uri == 'valid:rdf' and $data == 'valid:rdf') {
            return $validRdf;
        } else {
            return null;
        }
    }    
}

class Mock_RdfSerialiser
{
    public function serialise($graph, $format)
    {
        if ($format == 'rdfxml') {
            return "<rdf></rdf>";
        } else {
            return null;
        }
    }    
}

class EasyRdf_GraphTest extends PHPUnit_Framework_TestCase
{
    protected $_graph = null;

    public function testSimplifyMimeTypeJson()
    {
        $this->assertEquals(
            'json',
            EasyRdf_Graph::simplifyMimeType('application/json')
        );
        $this->assertEquals(
            'json',
            EasyRdf_Graph::simplifyMimeType('text/json')
        );
    }

    public function testSimplifyMimeTypeRdfXml()
    {
        $this->assertEquals(
            'rdfxml',
            EasyRdf_Graph::simplifyMimeType('application/rdf+xml')
        );
    }

    public function testSimplifyMimeTypeTurtle()
    {
        $this->assertEquals(
            'turtle',
            EasyRdf_Graph::simplifyMimeType('text/turtle')
        );
    }

    public function testSimplifyMimeTypeNTriples()
    {
        $this->assertEquals(
            'ntriples',
            EasyRdf_Graph::simplifyMimeType('application/n-triples')
        );
    }

    public function testSimplifyMimeTypeHtml()
    {
        $this->assertEquals(
            'rdfa',
            EasyRdf_Graph::simplifyMimeType('text/html')
        );
    }

    public function testSimplifyMimeTypeXHtml()
    {
        $this->assertEquals(
            'rdfa',
            EasyRdf_Graph::simplifyMimeType('application/xhtml+xml')
        );
    }

    public function testSimplifyMimeTypeUnknown()
    {
        $this->assertNull(
            EasyRdf_Graph::simplifyMimeType('foo/bar')
        );
    }
    
    public function testGuessTypePhp()
    {
        $data = array('http://www.example.com' => array());
        $this->assertEquals('php', EasyRdf_Graph::guessDocType($data));
    }
    
    public function testGuessTypeRdfXml()
    {
        $data = readFixture('foaf.rdf');
        $this->assertEquals('rdfxml', EasyRdf_Graph::guessDocType($data));
    }
    
    public function testGuessTypeJson()
    {
        $data = readFixture('foaf.json');
        $this->assertEquals('json', EasyRdf_Graph::guessDocType($data));
    }
    
    public function testGuessTypeTurtle()
    {
        $data = readFixture('foaf.ttl');
        $this->assertEquals('turtle', EasyRdf_Graph::guessDocType($data));
    }
    
    public function testGuessTypeNtriples()
    {
        $data = readFixture('foaf.nt');
        $this->assertEquals('ntriples', EasyRdf_Graph::guessDocType($data));
    }
    
    public function testGuessTypeRdfa()
    {
        $data = readFixture('foaf.html');
        $this->assertEquals('rdfa', EasyRdf_Graph::guessDocType($data));
    }
    
    public function testGuessTypeUnknown()
    {
        $this->assertNull(
            EasyRdf_Graph::guessDocType('blah blah blah')
        );
    }
    
    public function testGetDefaultHttpClient()
    {
        $this->assertEquals(
            'EasyRdf_Http_Client',
            get_class(EasyRdf_Graph::getHttpClient())
        );
    }
    
    public function testGetDefaultRdfParser()
    {
        $this->assertEquals(
            'EasyRdf_Parser_Builtin',
            get_class(EasyRdf_Graph::getRdfParser())
        );
    }
    
    public function testGetDefaultRdfSerialiser()
    {
        $this->assertEquals(
            'EasyRdf_Serialiser_Builtin',
            get_class(EasyRdf_Graph::getRdfSerialiser())
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
    
    public function testSetRdfParser()
    {
        EasyRdf_Graph::setRdfParser(new Mock_RdfParser());
        $this->assertEquals(
            'Mock_RdfParser',
            get_class(EasyRdf_Graph::getRdfParser())
        );
    }

    public function testSetRdfParserNull()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Graph::setRdfParser(null);
    }
    
    public function testSetRdfParserString()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Graph::setRdfParser('foobar');
    }
    
    public function testSetRdfSerialiser()
    {
        EasyRdf_Graph::setRdfSerialiser(new Mock_RdfSerialiser());
        $this->assertEquals(
            'Mock_RdfSerialiser',
            get_class(EasyRdf_Graph::getRdfSerialiser())
        );
    }

    public function testSetRdfSerialiserNull()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Graph::setRdfSerialiser(null);
    }
    
    public function testSetRdfSerialiserString()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Graph::setRdfSerialiser('foobar');
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
        global $validRdf;
        $graph = new EasyRdf_Graph();
        $graph->load('http://www.example.com/foaf.php', $validRdf);
        
        $this->assertEquals(
            'EasyRdf_Resource',
            get_class($graph->get('http://example.com/joe'))
        );
        $this->assertEquals(
            'http://example.com/joe',
            $graph->get('http://example.com/joe')->getUri()
        );
        $this->assertEquals(
            'Joseph Bloggs',
            $graph->get('http://example.com/joe')->get('foaf:name')
        );
        $this->assertNull(
            $graph->get('http://example.com/joe')->type()
        );
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
    
    public function testLoadMockParser()
    {
        EasyRdf_Graph::setRdfParser(new Mock_RdfParser());
        $graph = new EasyRdf_Graph();
        # Use magic URI to trigger Mock parser to return valid RDF
        $graph->load('valid:rdf', 'valid:rdf');
        $this->assertEquals(
            'Joseph Bloggs',
            $graph->get('http://example.com/joe')->get('foaf:name')
        );
    }
    
    public function testLoadMockParserInvalid()
    {
        $this->setExpectedException('EasyRdf_Exception');
        EasyRdf_Graph::setRdfParser(new Mock_RdfParser());
        $graph = new EasyRdf_Graph();
        $graph->load('invalid:rdf', 'invalid:rdf');
    }
    
    public function testLoadMockHttpClient()
    {
        EasyRdf_Graph::setHttpClient(new Mock_Http_Client());
        $graph = new EasyRdf_Graph('http://www.example.com/');
        $this->assertEquals(
            'Joe Bloggs',
            $graph->get('http://www.example.com/joe#me')->get('foaf:name')
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
        $this->assertEquals('A', $graph->get('_:eid1')->get('foaf:name'));
        $this->assertEquals('B', $graph->get('_:eid2')->get('foaf:name'));
    }

    public function testGet()
    {
        $data = readFixture('foaf.json');
        $graph = new EasyRdf_Graph('http://example.com/joe/foaf.rdf', $data);
        $this->assertEquals(
            'Joe Bloggs',
            $graph->get('http://www.example.com/joe#me')->get('foaf:name')
        );
    }

    public function testGetUnknown()
    {
        $graph = new EasyRdf_Graph();
        $this->assertEquals(
            'http://www.foo.com/bar',
            $graph->get('http://www.foo.com/bar')->getUri()
        );
    }

    public function testGetNullUri()
    {
        $this->setExpectedException('InvalidArgumentException');
        $graph = new EasyRdf_Graph();
        $graph->get(null);
    }
    
    public function testGetEmptyUri()
    {
        $this->setExpectedException('InvalidArgumentException');
        $graph = new EasyRdf_Graph();
        $graph->get('');
    }
    
    public function testGetNonStringUri()
    {
        $this->setExpectedException('InvalidArgumentException');
        $graph = new EasyRdf_Graph();
        $graph->get(array());
    }

    public function testGetDefaultLangFilter()
    {
        $this->assertEquals(null, EasyRdf_Graph::getLangFilter());
    }

    public function testSetLangFilterEnglish()
    {
        global $validRdf;
        EasyRdf_Graph::setLangFilter('en');
        $graph = new EasyRdf_Graph();
        $graph->load('http://www.example.com/foaf.php', $validRdf);
        $joe = $graph->get('http://example.com/joe');
        $this->assertEquals('Joseph Bloggs', $joe->get('foaf:name'));
    }

    public function testSetLangFilterFrench()
    {
        global $validRdf;
        EasyRdf_Graph::setLangFilter('fr');
        $graph = new EasyRdf_Graph();
        $graph->load('http://www.example.com/foaf.php', $validRdf);
        $joe = $graph->get('http://example.com/joe');
        $this->assertEquals(null, $joe->get('foaf:name'));
    }

    public function testSetLangFilterNonString()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Graph::setLangFilter(10);
    }

    public function testSetType()
    {
        $graph = new EasyRdf_Graph();
        $resource = $graph->get('http://www.foo.com/bar', 'foo:Bar');
        $this->assertEquals('foo:Bar', $resource->type());
    }

    public function testSetMultipleTypes()
    {
        $graph = new EasyRdf_Graph();
        $resource = $graph->get(
            'http://www.foo.com/bar',
            array('foo:Bar', 'bar:Foo')
        );
        $this->assertEquals(array('foo:Bar', 'bar:Foo'), $resource->types());
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
        $resource = $graph->get('http://www.example.com/joe#me');
        $this->assertEquals('Joe', $resource->get('foaf:name'));
    }
    
    public function testAddSingleValueToResource()
    {
        $graph = new EasyRdf_Graph();
        $resource = $graph->get('http://www.example.com/joe#me');
        $graph->add($resource, 'foaf:name', 'Joe');
        $this->assertEquals('Joe', $resource->get('foaf:name'));
    }
    
    public function testAddMultipleValuesToString()
    {
        $graph = new EasyRdf_Graph();
        $resource = $graph->get('http://www.example.com/joe#me');
        $graph->add(
            'http://www.example.com/joe#me',
            'foaf:name',
            array('Joe','Joseph')
        );
        $this->assertEquals(
            array('Joe', 'Joseph'),
            $resource->all('foaf:name')
        );
    }
    
    public function testAddMultipleValuesToResource()
    {
        $graph = new EasyRdf_Graph();
        $resource = $graph->get('http://www.example.com/joe#me');
        $graph->add($resource, 'foaf:name', array('Joe','Joseph'));
        $this->assertEquals(
            array('Joe', 'Joseph'),
            $resource->all('foaf:name')
        );
    }
    
    public function testAddMultiplePropertiesToResource()
    {
        $graph = new EasyRdf_Graph();
        $resource = $graph->get('http://www.example.com/joe#me');
        $graph->add(
            $resource,
            array(
                'foaf:givenname' => 'Joe',
                'foaf:surname' => 'Bloggs'
            )
        );
        $this->assertEquals('Joe', $resource->get('foaf:givenname'));
        $this->assertEquals('Bloggs', $resource->get('foaf:surname'));
    }
    
    public function testAddAnonymousBNodeToResource()
    {
        $graph = new EasyRdf_Graph();
        $resource = $graph->get('http://www.example.com/joe#me');
        $graph->add(
            $resource, 'foaf:knows', array('foaf:name' => 'Yves')
        );
        $yves = $resource->get('foaf:knows');
        $this->assertTrue($yves->isBNode());
        $this->assertEquals('Yves', $yves->get('foaf:name'));
    }
    
    public function testAddTypedBNodeToResource()
    {
        $graph = new EasyRdf_Graph();
        $resource = $graph->get('http://www.example.com/joe#me');
        $graph->add(
            $resource, 'foaf:knows', array('rdf:type' => 'foaf:Person')
        );
        $person = $resource->get('foaf:knows');
        $this->assertTrue($person->isBNode());
        $this->assertEquals('foaf:Person', $person->type());
    }
    
    public function testAddBNodeViaPropertiesToResource()
    {
        $graph = new EasyRdf_Graph();
        $resource = $graph->get('http://www.example.com/joe#me');
        $graph->add(
            $resource, array('foaf:knows' => array('foaf:name' => 'Yves'))
        );
        $yves = $resource->get('foaf:knows');
        $this->assertTrue($yves->isBNode());
        $this->assertEquals('Yves', $yves->get('foaf:name'));
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
        $this->assertEquals('foaf:PersonalProfileDocument', $graph->type());
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
        EasyRdf_Graph::setRdfSerialiser(new Mock_RdfSerialiser());
        $graph = new EasyRdf_Graph();
        $this->assertEquals("<rdf></rdf>", $graph->serialise('rdfxml'));
    }

    public function testSerialiseByMime()
    {
        EasyRdf_Graph::setRdfSerialiser(new Mock_RdfSerialiser());
        $graph = new EasyRdf_Graph();
        $this->assertEquals("<rdf></rdf>", $graph->serialise('application/rdf+xml'));
    }

    public function testDump()
    {
        $this->markTestIncomplete();
    }

    public function testMagicGet()
    {
        $data = readFixture('foaf.json');
        $graph = new EasyRdf_Graph(
            'http://www.example.com/joe/foaf.rdf', $data
        );
        $this->assertEquals(
            "Joe Bloggs' FOAF File",
            $graph->label()
        );
        $this->assertEquals(
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
        $this->assertEquals(
            'http://example.com/joe/foaf.rdf',
            $graph->__toString()
        );
    }
}
