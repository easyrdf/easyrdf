<?php

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009-2011 Nicholas J Humfrey.  All rights reserved.
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
 * @copyright  Copyright (c) 2009-2010 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 * @version    $Id$
 */

require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'TestHelper.php';

class MockParserClass
{
}

class MockSerialiserClass
{
}

class EasyRdf_FormatTest extends EasyRdf_TestCase
{
    /**
     * Set up the test suite before each test
     */
    public function setUp()
    {
        $this->_format = EasyRdf_Format::register(
            'my',
            'My Format',
            'http://example.com/myformat',
            array('my/mime' => 1.0, 'my/x-mime' => 0.9)
        );
    }

    public function tearDown()
    {
        EasyRdf_Format::unregister('my');
    }

    public function testRegisterNameNull()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Format::register(null);
    }

    public function testRegisterNameEmpty()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Format::register('');
    }

    public function testRegisterNameNonString()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Format::register(array());
    }

    public function testGetFormats()
    {
        $formats = EasyRdf_Format::getFormats();
        $this->assertType('array', $formats);
        $this->assertGreaterThan(0, count($formats));
        foreach ($formats as $format) {
            $this->assertEquals('EasyRdf_Format', get_class($format));
        }
    }

    public function testGetHttpAcceptHeader()
    {
        $accept = EasyRdf_Format::getHttpAcceptHeader();
        $this->assertContains('application/json', $accept);
        $this->assertContains('application/rdf+xml;q=0.8', $accept);
    }

    public function testGetHttpAcceptHeaderWithExtra()
    {
        $accept = EasyRdf_Format::getHttpAcceptHeader(array('extra/header' => 0.5));
        $this->assertContains('application/json', $accept);
        $this->assertContains('extra/header;q=0.5', $accept);
    }

    public function testFormatExistsTrue()
    {
        assert(EasyRdf_Format::formatExists('my'));
    }

    public function testFormatExistsFalse()
    {
        assert(!EasyRdf_Format::formatExists('testFormatExistsFalse'));
    }

    public function testUnRegister()
    {
        EasyRdf_Format::unregister('my');
        assert(!EasyRdf_Format::formatExists('my'));
    }

    public function testGetFormatByName()
    {
        $format = EasyRdf_Format::getFormat('my');
        $this->assertNotNull($format);
        $this->assertEquals('EasyRdf_Format', get_class($format));
        $this->assertEquals('my', $format->getName());
        $this->assertEquals('My Format', $format->getLabel());
        $this->assertEquals('http://example.com/myformat', $format->getUri());
    }

    public function testGetFormatByUri()
    {
        $format = EasyRdf_Format::getFormat('http://example.com/myformat');
        $this->assertNotNull($format);
        $this->assertEquals('EasyRdf_Format', get_class($format));
        $this->assertEquals('my', $format->getName());
        $this->assertEquals('My Format', $format->getLabel());
        $this->assertEquals('http://example.com/myformat', $format->getUri());
    }

    public function testGetFormatByMime()
    {
        $format = EasyRdf_Format::getFormat('my/mime');
        $this->assertNotNull($format);
        $this->assertEquals('EasyRdf_Format', get_class($format));
        $this->assertEquals('my', $format->getName());
        $this->assertEquals('My Format', $format->getLabel());
        $this->assertEquals('http://example.com/myformat', $format->getUri());
    }

    public function testGetFormatByMime2()
    {
        $format = EasyRdf_Format::getFormat('my/x-mime');
        $this->assertNotNull($format);
        $this->assertEquals('EasyRdf_Format', get_class($format));
        $this->assertEquals('my', $format->getName());
        $this->assertEquals('My Format', $format->getLabel());
        $this->assertEquals('http://example.com/myformat', $format->getUri());
    }

    public function testGetFormatNull()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Format::getFormat(null);
    }

    public function testGetFormatEmpty()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Format::getFormat('');
    }

    public function testGetFormatNonString()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Format::getFormat(array());
    }

    public function testGetFormatUnknown()
    {
        $this->setExpectedException('EasyRdf_Exception');
        $this->assertEquals(null, EasyRdf_Format::getFormat('unknown'));
    }

    public function testGetNames()
    {
        $names = EasyRdf_Format::getNames();
        $this->assertTrue(is_array($names));
        $this->assertTrue(in_array('ntriples', $names));
    }

    public function testGetName()
    {
        $this->assertEquals('my', $this->_format->getName());
    }

    public function testGetLabel()
    {
        $this->assertEquals('My Format', $this->_format->getLabel());
    }

    public function testSetLabel()
    {
        $this->_format->setLabel('testSetLabel');
        $this->assertEquals('testSetLabel', $this->_format->getLabel());
    }

    public function testSetLabelNull()
    {
        $this->_format->setLabel(null);
        $this->assertEquals(null, $this->_format->getLabel());
    }

    public function testSetLabelEmpty()
    {
        $this->_format->setLabel('');
        $this->assertEquals(null, $this->_format->getLabel());
    }

    public function testSetLabelNonString()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_format->setLabel($this);
    }

    public function testSetUri()
    {
        $this->_format->setUri('testSetUri');
        $this->assertEquals('testSetUri', $this->_format->getUri());
    }

    public function testSetUriNull()
    {
        $this->_format->setUri(null);
        $this->assertEquals(null, $this->_format->getUri());
    }

    public function testSetUriEmpty()
    {
        $this->_format->setUri('');
        $this->assertEquals(null, $this->_format->getUri());
    }

    public function testSetUriNonString()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_format->setUri($this);
    }

    public function testGetUri()
    {
        $this->assertEquals(
            'http://example.com/myformat',
            $this->_format->getUri()
        );
    }

    public function testGetDefaultMimeType()
    {
        $this->assertEquals(
            'my/mime',
            $this->_format->getDefaultMimeType()
        );
    }

    public function testGetMimeTypes()
    {
        $this->assertEquals(
            array('my/mime' => 1.0, 'my/x-mime' => 0.9),
            $this->_format->getMimeTypes()
        );
    }

    public function testSetMimeType()
    {
        $this->_format->setMimeTypes('testSetMimeType');
        $this->assertEquals(
            array('testSetMimeType'),
            $this->_format->getMimeTypes()
        );
    }

    public function testSetMimeTypes()
    {
        $this->_format->setMimeTypes(
            array('testSetMimeTypes1', 'testSetMimeTypes2')
        );
        $this->assertEquals(
            array('testSetMimeTypes1', 'testSetMimeTypes2'),
            $this->_format->getMimeTypes()
        );
    }

    public function testSetMimeTypeNull()
    {
        $this->_format->setMimeTypes(null);
        $this->assertEquals(array(), $this->_format->getMimeTypes());
    }

    public function testToString()
    {
        $this->assertStringEquals('my', $this->_format);
    }

    public function testSetParserClass()
    {
        $this->_format->setParserClass('MockParserClass');
        $this->assertEquals(
            'MockParserClass',
            $this->_format->getParserClass()
        );
    }

    public function testSetParserClassNull()
    {
        $this->_format->setParserClass(null);
        $this->assertEquals(null, $this->_format->getParserClass());
    }

    public function testSetParserClassEmpty()
    {
        $this->_format->setParserClass('');
        $this->assertEquals(null, $this->_format->getParserClass());
    }

    public function testSetParserClassNonString()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_format->setParserClass($this);
    }

    public function testRegisterParser()
    {
        EasyRdf_Format::registerParser('my', 'MockParserClass');
        $this->assertEquals(
            'MockParserClass',
            $this->_format->getParserClass()
        );
    }

    public function testRegisterParserForUnknownFormat()
    {
        EasyRdf_Format::registerParser('testRegisterParser', 'MockParserClass');
        $format = EasyRdf_Format::getFormat('testRegisterParser');
        $this->assertNotNull($format);
        $this->assertEquals('MockParserClass', $format->getParserClass());
    }

    public function testNewParser()
    {
        $this->_format->setParserClass('MockParserClass');
        $parser = $this->_format->newParser();
        $this->assertType('object', $parser);
        $this->assertEquals('MockParserClass', get_class($parser));
    }

    public function testNewParserNull()
    {
        $this->setExpectedException('EasyRdf_Exception');
        $this->_format->setParserClass(null);
        $parser = $this->_format->newParser();
    }

    public function testSetSerialiserClass()
    {
        $this->_format->setSerialiserClass('MockSerialiserClass');
        $this->assertEquals(
            'MockSerialiserClass',
            $this->_format->getSerialiserClass()
        );
    }

    public function testSetSerialiserClassNull()
    {
        $this->_format->setSerialiserClass(null);
        $this->assertEquals(null, $this->_format->getSerialiserClass());
    }

    public function testSetSerialiserClassEmpty()
    {
        $this->_format->setSerialiserClass('');
         $this->assertEquals(null, $this->_format->getSerialiserClass());
    }

    public function testSetSerialiserClassNonString()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_format->setSerialiserClass($this);
    }

    public function testNewSerialiser()
    {
        $this->_format->setSerialiserClass('MockSerialiserClass');
        $serialiser = $this->_format->newSerialiser();
        $this->assertType('object', $serialiser);
        $this->assertEquals('MockSerialiserClass', get_class($serialiser));
    }

    public function testNewSerialiserNull()
    {
        $this->setExpectedException('EasyRdf_Exception');
        $this->_format->setSerialiserClass(null);
        $serialiser = $this->_format->newSerialiser();
    }

    public function testRegisterSerialiser()
    {
        EasyRdf_Format::registerSerialiser('my', 'MockSerialiserClass');
        $this->assertEquals(
            'MockSerialiserClass',
            $this->_format->getSerialiserClass()
        );
    }

    public function testRegisterSerialiserForUnknownFormat()
    {
        EasyRdf_Format::registerSerialiser(
            'testRegisterSerialiser', 'MockSerialiserClass'
        );
        $format = EasyRdf_Format::getFormat('testRegisterSerialiser');
        $this->assertNotNull($format);
        $this->assertEquals(
            'MockSerialiserClass',
            $format->getSerialiserClass()
        );
    }

    public function testGuessFormatPhp()
    {
        $data = array('http://www.example.com' => array());
        $this->assertStringEquals('php', EasyRdf_Format::guessFormat($data));
    }

    public function testGuessFormatRdfXml()
    {
        $data = readFixture('foaf.rdf');
        $this->assertStringEquals('rdfxml', EasyRdf_Format::guessFormat($data));
    }

    public function testGuessFormatJson()
    {
        $data = readFixture('foaf.json');
        $this->assertStringEquals('json', EasyRdf_Format::guessFormat($data));
    }

    public function testGuessFormatTurtle()
    {
        $data = readFixture('foaf.ttl');
        $this->assertStringEquals('turtle', EasyRdf_Format::guessFormat($data));
    }

    public function testGuessFormatNtriples()
    {
        $data = readFixture('foaf.nt');
        $this->assertStringEquals('ntriples', EasyRdf_Format::guessFormat($data));
    }

    public function testGuessFormatRdfa()
    {
        $data = readFixture('foaf.html');
        $this->assertStringEquals('rdfa', EasyRdf_Format::guessFormat($data));
    }

    public function testGuessFormatXml()
    {
        $format = EasyRdf_Format::guessFormat(
            '<?xml version="1.0" encoding="UTF-8"?><!-- long comment '
        );
        $this->assertStringEquals('rdfxml', $format);
    }

    public function testGuessFormatUnknown()
    {
        $this->assertNull(
            EasyRdf_Format::guessFormat('blah blah blah')
        );
    }
}
