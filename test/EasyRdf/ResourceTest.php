<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'TestHelper.php';
require_once 'EasyRdf/Resource.php';

class EasyRdf_ResourceTest extends PHPUnit_Framework_TestCase
{
    protected $resource = null;
    
    /**
     * Set up the test suite before each test
     */
    public function setUp()
    {
        $this->resource = new EasyRdf_Resource('http://www.example.com/#me');
        $this->resource->set( 'rdf_type', 'foaf_Person' );
        $this->resource->add( 'test_prop', 'Test A' );
        $this->resource->add( 'test_prop', 'Test B' );
    }

    public function testGetUri()
    {
        $this->assertEquals('http://www.example.com/#me', $this->resource->getUri());
    }

    public function testGet()
    {
        $this->assertEquals('Test A', $this->resource->get('test_prop'));
    }

    public function testGetNonExistantProperty()
    {
        $this->assertEquals(null, $this->resource->get('foo_bar'));
    }

    public function testAll()
    {
        $this->assertEquals(array('Test A','Test B'), $this->resource->all('test_prop'));
    }

    public function testAllNonExistantProperty()
    {
        $this->assertEquals(array(), $this->resource->all('foo_bar'));
    }

    public function testSet()
    {
        $this->resource->set('test_prop', 'Test C');
        $this->assertEquals(array('Test C'), $this->resource->all('test_prop'));
    }
    
    public function testSetNull()
    {
        $this->resource->set('test_prop', null);
        $this->assertEquals(array(), $this->resource->all('test_prop'));
    }

    public function testAdd()
    {
        $this->resource->add('test_prop', 'Test C');
        $this->assertEquals(array('Test A', 'Test B', 'Test C'), $this->resource->all('test_prop'));
    }
    
    public function testAddNull()
    {
        $this->resource->add('test_prop', null);
        $this->assertEquals(array('Test A', 'Test B'), $this->resource->all('test_prop'));
    }

    public function testJoinDefaultGlue()
    {
        $this->assertEquals('Test A Test B', $this->resource->join('test_prop'));
    }

    public function testJoinNonExistantProperty()
    {
        $this->assertEquals('', $this->resource->join('foo_bar'));
    }

    public function testJoinCustomGlue()
    {
        $this->assertEquals('Test A:Test B', $this->resource->join('test_prop', ':'));
    }

    public function testIsBnode()
    {
        $bnode = new EasyRdf_Resource('_:foobar');
        $this->assertEquals(true, $bnode->isBnode());
    }

    public function testIsNotBnode()
    {
        $this->assertEquals(false, $this->resource->isBnode());
    }

    public function testProperties()
    {
        $this->assertEquals(array('rdf_type', 'test_prop'), $this->resource->properties());
    }

    public function testTypes()
    {
        $this->assertEquals(array('foaf_Person'), $this->resource->types());
    }

    public function testType()
    {
        $this->assertEquals('foaf_Person', $this->resource->type());
    }

    public function testNs()
    {
        $foaf_name = new EasyRdf_Resource('http://xmlns.com/foaf/0.1/name');
        $this->assertEquals('foaf', $foaf_name->ns());
    }

    public function testShorten()
    {
        $foaf_name = new EasyRdf_Resource('http://xmlns.com/foaf/0.1/name');
        $this->assertEquals('foaf_name', $foaf_name->shorten());
    }

    public function testLabelNoRdfsLabel()
    {
        $this->assertEquals(null, $this->resource->label());
    }

    public function testLabelWithRdfsLabel()
    {
        $this->resource->set( 'rdfs_label', 'Label Text' );
        $this->resource->set( 'foaf_name', 'Foaf Name' );
        $this->resource->set( 'dc_title', 'Dc Title' );
        $this->assertEquals('Label Text', $this->resource->label());
    }

    public function testLabelWithFoafName()
    {
        $this->resource->set( 'foaf_name', 'Foaf Name' );
        $this->resource->set( 'dc_title', 'Dc Title' );
        $this->assertEquals('Foaf Name', $this->resource->label());
    }

    public function testLabelWithDcTitle()
    {
        $this->resource->set( 'dc_title', 'Dc Title' );
        $this->assertEquals('Dc Title', $this->resource->label());
    }

    public function testDump()
    {
        $this->markTestIncomplete();
    }

    public function testMagicGet()
    {
        $this->assertEquals('Test A', $this->resource->getTest_prop());
    }

    public function testMagicGetNonExistantProperty()
    {
        $this->assertEquals('', $this->resource->getFoo_bar());
    }

    public function testMagicAll()
    {
        $this->assertEquals(array('Test A','Test B'), $this->resource->allTest_prop());
    }

    public function testMagicAllNonExistantProperty()
    {
        $this->assertEquals(array(), $this->resource->allFoo_bar());
    }

    public function testToString()
    {
        $this->assertEquals('http://www.example.com/#me', $this->resource->__toString());
    }
}
