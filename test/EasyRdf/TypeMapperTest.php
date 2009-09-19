<?php

require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'TestHelper.php';
require_once 'EasyRdf/Resource.php';
require_once 'EasyRdf/TypeMapper.php';

class MyType extends EasyRdf_Resource
{
}

class EasyRdf_TypeMapperTest extends PHPUnit_Framework_TestCase
{
    /**
     * Set up the test suite before each test
     */
    public function setUp()
    {
        EasyRdf_TypeMapper::add('mytype', 'MyType_Class');
    }

    public function testGet()
    {
        $this->assertEquals('MyType_Class', EasyRdf_TypeMapper::get('mytype'));
    }

    public function testGetNull()
    {
        $this->assertEquals(null, EasyRdf_TypeMapper::get(null));
    }

    public function testGetEmpty()
    {
        $this->assertEquals(null, EasyRdf_TypeMapper::get(''));
    }

    public function testGetUnknown()
    {
        $this->assertEquals(null, EasyRdf_TypeMapper::get('unknown_type'));
    }
}
