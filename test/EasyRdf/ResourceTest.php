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
        $this->resource = new EasyRdf_Resource('http://www.example.com/');
    }

    public function testGetUri()
    {
        $this->assertEquals('http://www.example.com/', $this->resource->getUri());
    }

    public function testToString()
    {
        $this->assertEquals('http://www.example.com/', strval($res));
    }

    public function testSetProperty()
    {
        $this->resource->set('foaf_name', 'avalue');
        $this->assertEquals('avalue', $this->resource->foaf_name);
    }

}
