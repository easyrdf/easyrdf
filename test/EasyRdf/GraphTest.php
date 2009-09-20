<?php

require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'TestHelper.php';
require_once 'EasyRdf/Graph.php';

class Mock_Http_Client
{
}

class Mock_Rdf_Parser
{
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

    public function testSimplifyMimeTypeYaml()
    {
        $this->assertEquals(
            'yaml',
            EasyRdf_Graph::simplifyMimeType('text/yaml')
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
            'EasyRdf_RapperParser',
            get_class(EasyRdf_Graph::getRdfParser())
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
    
    public function testSetRdfParser()
    {
        EasyRdf_Graph::setRdfParser(new Mock_Rdf_Parser());
        $this->assertEquals(
            'Mock_Rdf_Parser',
            get_class(EasyRdf_Graph::getRdfParser())
        );
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

    public function testLoadData()
    {
        $graph = new EasyRdf_Graph();
        $graph->load(
            null, array(
                'http://example.com/joe' => array(
                    'http://xmlns.com/foaf/0.1/name' => array(
                        array(
                            'value' => 'Joseph Bloggs',
                            'type' => 'literal'
                        )
                    )
                )
            )
        );
        
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
            $graph->get('http://example.com/joe')->get('foaf_name')
        );
        $this->assertNull(
            $graph->get('http://example.com/joe')->type()
        );
    }

    public function testGet()
    {
        $data = readFixture('foaf.json');
        $graph = new EasyRdf_Graph('http://example.com/joe/foaf.rdf', $data);
        $this->assertEquals(
            'Joe Bloggs',
            $graph->get('http://www.example.com/joe#me')->get('foaf_name')
        );
    }

    public function testGetInvalid()
    {
        $graph = new EasyRdf_Graph();
        $this->assertNull($graph->get(null));
    }

    public function testGetUnknown()
    {
        $graph = new EasyRdf_Graph();
        $this->assertEquals(
            'http://www.foo.com/bar',
            $graph->get('http://www.foo.com/bar')->getUri()
        );
    }

    public function testSetType()
    {
        $graph = new EasyRdf_Graph();
        $resource = $graph->get('http://www.foo.com/bar', 'foo_Bar');
        $this->assertEquals('foo_Bar', $resource->type());
    }

    public function testSetMultipleTypes()
    {
        $graph = new EasyRdf_Graph();
        $resource = $graph->get(
            'http://www.foo.com/bar',
            array('foo_Bar', 'bar_Foo')
        );
        $this->assertEquals(array('foo_Bar', 'bar_Foo'), $resource->types());
    }

    public function testResources()
    {
        $data = readFixture('foaf.json');
        $graph = new EasyRdf_Graph('http://example.com/joe/foaf.rdf', $data);
        $resources = $graph->resources();
        $this->assertEquals(2, count($resources));
        $this->assertEquals(
            'http://www.example.com/joe#me', 
            $resources[0]->getUri()
        );
        $this->assertEquals(
            'http://www.example.com/joe/foaf.rdf', 
            $resources[1]->getUri()
        );
    }
    
    public function testAllOfType()
    {
        $data = readFixture('foaf.json');
        $graph = new EasyRdf_Graph('http://example.com/joe/foaf.rdf', $data);
        $resources = $graph->allOfType('foaf_Person');
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
        $resources = $graph->allOfType('unknown_type');
        $this->assertTrue(is_array($resources));
        $this->assertEquals(0, count($resources));
    }
    
    public function testFirstOfType()
    {
        $data = readFixture('foaf.json');
        $graph = new EasyRdf_Graph('http://example.com/joe/foaf.rdf', $data);
        $resource = $graph->firstOfType('foaf_Person');
        $this->assertEquals(
            'http://www.example.com/joe#me', 
            $resource->getUri()
        );
    }
    
    public function testFirstOfTypeUnknown()
    {
        $graph = new EasyRdf_Graph();
        $resource = $graph->firstOfType('unknown_type');
        $this->assertNull($resource);
    }
    
    public function testAllTypes()
    {
        $data = readFixture('foaf.json');
        $graph = new EasyRdf_Graph('http://example.com/joe/foaf.rdf', $data);
        $types = $graph->allTypes();
        $this->assertTrue(is_array($types));
        $this->assertEquals(2, count($types));
        $this->assertEquals('foaf_Person', $types[0]);
        $this->assertEquals('foaf_PersonalProfileDocument', $types[1]);
    }
    
    public function testAddSingleValueToString()
    {
        $graph = new EasyRdf_Graph();
        $graph->add('http://www.example.com/joe#me', 'foaf_name', 'Joe');
        $resource = $graph->get('http://www.example.com/joe#me');
        $this->assertEquals('Joe', $resource->get('foaf_name'));
    }
    
    public function testAddSingleValueToResource()
    {
        $graph = new EasyRdf_Graph();
        $resource = $graph->get('http://www.example.com/joe#me');
        $graph->add($resource, 'foaf_name', 'Joe');
        $this->assertEquals('Joe', $resource->get('foaf_name'));
    }
    
    public function testAddMultipleValuesToString()
    {
        $graph = new EasyRdf_Graph();
        $resource = $graph->get('http://www.example.com/joe#me');
        $graph->add(
            'http://www.example.com/joe#me',
            'foaf_name',
            array('Joe','Joseph')
        );
        $this->assertEquals(
            array('Joe', 'Joseph'),
            $resource->all('foaf_name')
        );
    }
    
    public function testAddMultipleValuesToResource()
    {
        $graph = new EasyRdf_Graph();
        $resource = $graph->get('http://www.example.com/joe#me');
        $graph->add($resource, 'foaf_name', array('Joe','Joseph'));
        $this->assertEquals(
            array('Joe', 'Joseph'),
            $resource->all('foaf_name')
        );
    }
    
    public function testAddMultiplePropertiesToResource()
    {
        $graph = new EasyRdf_Graph();
        $resource = $graph->get('http://www.example.com/joe#me');
        $graph->add(
            $resource,
            array(
                'foaf_givenname' => 'Joe',
                'foaf_surname' => 'Bloggs'
            )
        );
        $this->assertEquals('Joe', $resource->get('foaf_givenname'));
        $this->assertEquals('Bloggs', $resource->get('foaf_surname'));
    }

    public function testType()
    {
        $data = readFixture('foaf.json');
        $graph = new EasyRdf_Graph(
            'http://www.example.com/joe/foaf.rdf', $data
        );
        $this->assertEquals('foaf_PersonalProfileDocument', $graph->type());
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
