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
        $this->resource->set( 'test_prop', 'Test A' );
        $this->resource->set( 'test_prop', 'Test B' );
    }

    public function testGetUri()
    {
        $this->assertEquals('http://www.example.com/#me', $this->resource->getUri());
    }

    public function testMagicGet()
    {
        $this->resource->foobar = 'teststr';
        $this->assertEquals('teststr', $this->resource->foobar);
    }

    public function testMagicIsset()
    {
        $this->resource->foobar = 'teststr';
        $this->assertEquals(true, isset($this->resource->foobar));
        $this->assertEquals(false, isset($this->resource->ratrat));
    }

    public function testMagicUnset()
    {
        $this->resource->foobar = 'teststr';
        $this->assertEquals(true, isset($this->resource->foobar));
        unset($this->resource->foobar);
        $this->assertEquals(false, isset($this->resource->foobar));
    }

    public function testFirst()
    {
        $this->assertEquals('Test A', $this->resource->first('test_prop'));
    }

    public function testFirstNonExistantProperty()
    {
        $this->assertEquals(null, $this->resource->first('foo_bar'));
    }

    public function testAll()
    {
        $this->assertEquals(array('Test A','Test B'), $this->resource->all('test_prop'));
    }

    public function testAllNonExistantProperty()
    {
        $this->assertEquals(array(), $this->resource->all('foo_bar'));
    }

    public function testJoinDefaultGlue()
    {
        $this->assertEquals('Test A Test B', $this->resource->join('test_prop'));
    }

    public function testJoinNonExistantProperty()
    {
        $this->assertEquals('', $this->resource->join('foo_bar'));
    }

    public function testJoinCustonGlue()
    {
        $this->assertEquals('Test A:Test B', $this->resource->join('test_prop', ':'));
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
        $this->markTestIncomplete();
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

    public function testToString()
    {
        $this->assertEquals('http://www.example.com/#me', sprintf("%s",$this->resource));
    }
}
