<?php

require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'TestHelper.php';
require_once 'EasyRdf/Graph.php';

class EasyRdf_GraphTest extends PHPUnit_Framework_TestCase
{
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
    
    public function testGuessTypeRdfa()
    {
        $data = readFixture('foaf.html');
        $this->assertEquals('rdfa', EasyRdf_Graph::guessDocType($data));
    }
    
    public function testGuessTypeUnknown()
    {
        $this->assertEquals(
            '',
            EasyRdf_Graph::guessDocType('blah blah blah')
        );
    }
}
