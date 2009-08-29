<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'TestHelper.php';
require_once 'EasyRdf/Namespace.php';

class EasyRdf_NamespaceTest extends PHPUnit_Framework_TestCase
{
    public function testGetDcNamespace()
    {
        $this->assertEquals('http://purl.org/dc/elements/1.1/', EasyRdf_Namespace::get('dc'));
    }

    public function testGetFoafNamespace()
    {
        $this->assertEquals('http://xmlns.com/foaf/0.1/', EasyRdf_Namespace::get('foaf'));
    }

    public function testGetRdfsNamespace()
    {
        $this->assertEquals('http://www.w3.org/2000/01/rdf-schema#', EasyRdf_Namespace::get('rdfs'));
    }

    public function testGetXsdNamespace()
    {
        $this->assertEquals('http://www.w3.org/2001/XMLSchema#', EasyRdf_Namespace::get('xsd'));
    }

    public function testAddNamespace()
    {
        EasyRdf_Namespace::add('po', 'http://purl.org/ontology/po/');
        $this->assertEquals('http://purl.org/ontology/po/', EasyRdf_Namespace::get('po'));
    }

    public function testShortenFoafName()
    {
        $this->assertEquals('foaf_name', EasyRdf_Namespace::shorten('http://xmlns.com/foaf/0.1/name'));
    }

    public function testShortenUnknownUrl()
    {
        $this->assertEquals(null, EasyRdf_Namespace::shorten('http://www.aelius.com/njh/'));
    }

    public function testNamespaceOfUriFoafName()
    {
        $this->assertEquals('foaf', EasyRdf_Namespace::namespaceOfUri('http://xmlns.com/foaf/0.1/name'));
    }

    public function testExpandFoafName()
    {
        $this->assertEquals('http://xmlns.com/foaf/0.1/name', EasyRdf_Namespace::expand('foaf_name'));
    }

    public function testExpandMissingUnderscore()
    {
        $this->assertEquals(null, EasyRdf_Namespace::expand('unknown'));
    }

    public function testExpandUnknown()
    {
        $this->assertEquals(null, EasyRdf_Namespace::expand('unknown_unknown'));
    }
}
