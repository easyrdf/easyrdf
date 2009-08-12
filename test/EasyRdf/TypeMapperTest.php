<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'TestHelper.php';
require_once 'EasyRdf/Resource.php';
require_once 'EasyRdf/TypeMapper.php';

class MyType extends EasyRdf_Resource
{
}

class EasyRdf_TypeMapperTest extends PHPUnit_Framework_TestCase
{
    public function testAddType()
    {
        # FIXME: why doesn't this work?
        #EasyRdf_TypeMapper::add('mytype', MyType);
        #$this->assertEquals(MyType, EasyRdf_TypeMapper::get('mytype'));
    }

    public function testGetNull()
    {
        $this->assertEquals(null, EasyRdf_TypeMapper::get(null));
    }
}
