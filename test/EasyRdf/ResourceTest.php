<?php

require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'TestHelper.php';
require_once 'EasyRdf/Resource.php';

class EasyRdf_ResourceTest extends PHPUnit_Framework_TestCase
{
    protected $_resource = null;
    
    /**
     * Set up the test suite before each test
     */
    public function setUp()
    {
        $this->_resource = new EasyRdf_Resource('http://www.example.com/#me');
        $this->_resource->set('rdf_type', 'foaf_Person');
        $this->_resource->add('test_prop', 'Test A');
        $this->_resource->add('test_prop', 'Test B');
    }

    public function testGetUri()
    {
        $this->assertEquals(
            'http://www.example.com/#me',
            $this->_resource->getUri()
        );
    }

    public function testGet()
    {
        $this->assertEquals(
            'Test A',
            $this->_resource->get('test_prop')
        );
    }

    public function testGetNonExistantProperty()
    {
        $this->assertEquals(
            null,
            $this->_resource->get('foo_bar')
        );
    }

    public function testAll()
    {
        $this->assertEquals(
            array('Test A','Test B'),
            $this->_resource->all('test_prop')
        );
    }

    public function testAllNonExistantProperty()
    {
        $this->assertEquals(
            array(),
            $this->_resource->all('foo_bar')
        );
    }

    public function testSet()
    {
        $this->_resource->set('test_prop', 'Test C');
        $this->assertEquals(
            array('Test C'),
            $this->_resource->all('test_prop')
        );
    }
    
    public function testSetNull()
    {
        $this->_resource->set('test_prop', null);
        $this->assertEquals(
            array(),
            $this->_resource->all('test_prop')
        );
    }

    public function testAdd()
    {
        $this->_resource->add('test_prop', 'Test C');
        $this->assertEquals(
            array('Test A', 'Test B', 'Test C'),
            $this->_resource->all('test_prop')
        );
    }
    
    public function testAddNull()
    {
        $this->_resource->add('test_prop', null);
        $this->assertEquals(
            array('Test A', 'Test B'),
            $this->_resource->all('test_prop')
        );
    }

    public function testJoinDefaultGlue()
    {
        $this->assertEquals(
            'Test A Test B',
            $this->_resource->join('test_prop')
        );
    }

    public function testJoinNonExistantProperty()
    {
        $this->assertEquals('', $this->_resource->join('foo_bar'));
    }

    public function testJoinCustomGlue()
    {
        $this->assertEquals(
            'Test A:Test B',
            $this->_resource->join('test_prop', ':')
        );
    }

    public function testIsBnode()
    {
        $bnode = new EasyRdf_Resource('_:foobar');
        $this->assertEquals(true, $bnode->isBnode());
    }

    public function testIsNotBnode()
    {
        $this->assertEquals(false, $this->_resource->isBnode());
    }

    public function testProperties()
    {
        $this->assertEquals(
            array('rdf_type', 'test_prop'),
            $this->_resource->properties()
        );
    }

    public function testTypes()
    {
        $this->assertEquals(
            array('foaf_Person'),
            $this->_resource->types()
        );
    }

    public function testType()
    {
        $this->assertEquals('foaf_Person', $this->_resource->type());
    }

    public function testNs()
    {
        $foafName = new EasyRdf_Resource('http://xmlns.com/foaf/0.1/name');
        $this->assertEquals('foaf', $foafName->ns());
    }

    public function testShorten()
    {
        $foafName = new EasyRdf_Resource('http://xmlns.com/foaf/0.1/name');
        $this->assertEquals('foaf_name', $foafName->shorten());
    }

    public function testLabelNoRdfsLabel()
    {
        $this->assertEquals(null, $this->_resource->label());
    }

    public function testLabelWithRdfsLabel()
    {
        $this->_resource->set('rdfs_label', 'Label Text');
        $this->_resource->set('foaf_name', 'Foaf Name');
        $this->_resource->set('dc_title', 'Dc Title');
        $this->assertEquals('Label Text', $this->_resource->label());
    }

    public function testLabelWithFoafName()
    {
        $this->_resource->set('foaf_name', 'Foaf Name');
        $this->_resource->set('dc_title', 'Dc Title');
        $this->assertEquals('Foaf Name', $this->_resource->label());
    }

    public function testLabelWithDcTitle()
    {
        $this->_resource->set('dc_title', 'Dc Title');
        $this->assertEquals('Dc Title', $this->_resource->label());
    }

    public function testDump()
    {
        $this->markTestIncomplete();
    }

    public function testMagicGet()
    {
        $this->assertEquals('Test A', $this->_resource->getTest_prop());
    }

    public function testMagicGetNonExistantProperty()
    {
        $this->assertEquals('', $this->_resource->getFoo_bar());
    }

    public function testMagicAll()
    {
        $this->assertEquals(
            array('Test A','Test B'),
            $this->_resource->allTest_prop()
        );
    }

    public function testMagicAllNonExistantProperty()
    {
        $this->assertEquals(array(), $this->_resource->allFoo_bar());
    }

    public function testToString()
    {
        $this->assertEquals(
            'http://www.example.com/#me',
            $this->_resource->__toString()
        );
    }
}
