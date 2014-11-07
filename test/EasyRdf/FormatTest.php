<?php
namespace EasyRdf;

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009-2013 Nicholas J Humfrey.  All rights reserved.
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
 * @copyright  Copyright (c) 2009-2013 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */

require_once dirname(__DIR__).DIRECTORY_SEPARATOR.'TestHelper.php';

class MockParserClass
{
}

class MockSerialiserClass
{
}

class FormatTest extends TestCase
{
    /** @var Format */
    private $format;

    /**
     * Set up the test suite before each test
     */
    public function setUp()
    {
        $this->format = Format::register(
            'my',
            'My Format',
            'http://example.com/myformat',
            array('my/mime' => 1.0, 'my/x-mime' => 0.9),
            array('mext')
        );
    }

    public function tearDown()
    {
        Format::unregister('my');
    }

    public function testRegisterNameNull()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$name should be a string and cannot be null or empty'
        );
        Format::register(null);
    }

    public function testRegisterNameEmpty()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$name should be a string and cannot be null or empty'
        );
        Format::register('');
    }

    public function testRegisterNameNonString()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$name should be a string and cannot be null or empty'
        );
        Format::register(array());
    }

    public function testGetFormats()
    {
        $formats = Format::getFormats();
        $this->assertInternalType('array', $formats);
        $this->assertGreaterThan(0, count($formats));
        foreach ($formats as $format) {
            $this->assertClass('EasyRdf\Format', $format);
        }
    }

    public function testGetHttpAcceptHeader()
    {
        $accept = Format::getHttpAcceptHeader();
        $this->assertContains('application/json', $accept);
        $this->assertContains('application/rdf+xml;q=0.8', $accept);
    }

    public function testGetHttpAcceptHeaderWithExtra()
    {
        $accept = Format::getHttpAcceptHeader(array('extra/header' => 0.5));
        $this->assertContains('application/json', $accept);
        $this->assertContains('extra/header;q=0.5', $accept);
    }

    public function testGetHttpAcceptHeaderLocale()
    {
        $current_locale = setlocale(LC_NUMERIC, 0);
        setlocale(LC_NUMERIC, 'fi_FI.UTF-8');

        $accept = Format::getHttpAcceptHeader(array('extra/header' => 0.5));
        $this->assertContains('extra/header;q=0.5', $accept);

        setlocale(LC_NUMERIC, $current_locale);
    }

    public function testFormatExistsTrue()
    {
        $this->assertTrue(Format::formatExists('my'));
    }

    public function testFormatExistsFalse()
    {
        $this->assertFalse(Format::formatExists('testFormatExistsFalse'));
    }

    public function testUnRegister()
    {
        Format::unregister('my');
        $this->assertFalse(Format::formatExists('my'));
    }

    public function testGetFormatByName()
    {
        $format = Format::getFormat('my');
        $this->assertNotNull($format);
        $this->assertClass('EasyRdf\Format', $format);
        $this->assertSame('my', $format->getName());
        $this->assertSame('My Format', $format->getLabel());
        $this->assertSame('http://example.com/myformat', $format->getUri());
    }

    public function testGetFormatByUri()
    {
        $format = Format::getFormat('http://example.com/myformat');
        $this->assertNotNull($format);
        $this->assertClass('EasyRdf\Format', $format);
        $this->assertSame('my', $format->getName());
        $this->assertSame('My Format', $format->getLabel());
        $this->assertSame('http://example.com/myformat', $format->getUri());
    }

    public function testGetFormatByMime()
    {
        $format = Format::getFormat('my/mime');
        $this->assertNotNull($format);
        $this->assertClass('EasyRdf\Format', $format);
        $this->assertSame('my', $format->getName());
        $this->assertSame('My Format', $format->getLabel());
        $this->assertSame('http://example.com/myformat', $format->getUri());
    }

    public function testGetFormatByMime2()
    {
        $format = Format::getFormat('my/x-mime');
        $this->assertNotNull($format);
        $this->assertClass('EasyRdf\Format', $format);
        $this->assertSame('my', $format->getName());
        $this->assertSame('My Format', $format->getLabel());
        $this->assertSame('http://example.com/myformat', $format->getUri());
    }

    public function testGetFormatByExtension()
    {
        $format = Format::getFormat('mext');
        $this->assertNotNull($format);
        $this->assertClass('EasyRdf\Format', $format);
        $this->assertSame('my', $format->getName());
        $this->assertSame('My Format', $format->getLabel());
        $this->assertSame('http://example.com/myformat', $format->getUri());
    }

    public function testGetFormatNull()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$query should be a string and cannot be null or empty'
        );
        Format::getFormat(null);
    }

    public function testGetFormatEmpty()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$query should be a string and cannot be null or empty'
        );
        Format::getFormat('');
    }

    public function testGetFormatNonString()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$query should be a string and cannot be null or empty'
        );
        Format::getFormat(array());
    }

    public function testGetFormatUnknown()
    {
        $this->setExpectedException(
            'EasyRdf\Exception',
            'Format is not recognised: unknown'
        );
        $this->assertSame(null, Format::getFormat('unknown'));
    }

    public function testGetNames()
    {
        $names = Format::getNames();
        $this->assertTrue(is_array($names));
        $this->assertTrue(in_array('ntriples', $names));
    }

    public function testGetName()
    {
        $this->assertSame('my', $this->format->getName());
    }

    public function testGetLabel()
    {
        $this->assertSame('My Format', $this->format->getLabel());
    }

    public function testSetLabel()
    {
        $this->format->setLabel('testSetLabel');
        $this->assertSame('testSetLabel', $this->format->getLabel());
    }

    public function testSetLabelNull()
    {
        $this->format->setLabel(null);
        $this->assertSame(null, $this->format->getLabel());
    }

    public function testSetLabelEmpty()
    {
        $this->format->setLabel('');
        $this->assertSame(null, $this->format->getLabel());
    }

    public function testSetLabelNonString()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$label should be a string'
        );
        $this->format->setLabel($this);
    }

    public function testSetUri()
    {
        $this->format->setUri('testSetUri');
        $this->assertSame('testSetUri', $this->format->getUri());
    }

    public function testSetUriNull()
    {
        $this->format->setUri(null);
        $this->assertSame(null, $this->format->getUri());
    }

    public function testSetUriEmpty()
    {
        $this->format->setUri('');
        $this->assertSame(null, $this->format->getUri());
    }

    public function testSetUriNonString()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$uri should be a string'
        );
        $this->format->setUri($this);
    }

    public function testGetUri()
    {
        $this->assertSame(
            'http://example.com/myformat',
            $this->format->getUri()
        );
    }

    public function testGetDefaultMimeType()
    {
        $this->assertSame(
            'my/mime',
            $this->format->getDefaultMimeType()
        );
    }

    public function testGetDefaultMimeTypeNoDefault()
    {
        $format2 = Format::register('my2', 'Other Format');
        $this->assertNull(
            $format2->getDefaultMimeType()
        );
    }

    public function testGetMimeTypes()
    {
        $this->assertSame(
            array('my/mime' => 1.0, 'my/x-mime' => 0.9),
            $this->format->getMimeTypes()
        );
    }

    public function testSetMimeType()
    {
        $this->format->setMimeTypes('testSetMimeType');
        $this->assertSame(
            array('testSetMimeType'),
            $this->format->getMimeTypes()
        );
    }

    public function testSetMimeTypes()
    {
        $this->format->setMimeTypes(
            array('testSetMimeTypes1', 'testSetMimeTypes2')
        );
        $this->assertSame(
            array('testSetMimeTypes1', 'testSetMimeTypes2'),
            $this->format->getMimeTypes()
        );
    }

    public function testSetMimeTypeNull()
    {
        $this->format->setMimeTypes(null);
        $this->assertSame(array(), $this->format->getMimeTypes());
    }

    public function testGetDefaultExtension()
    {
        $this->assertSame(
            'mext',
            $this->format->getDefaultExtension()
        );
    }

    public function testGetExtensionNoDefault()
    {
        $format2 = Format::register('my2', 'Other Format');
        $this->assertNull(
            $format2->getDefaultExtension()
        );
    }

    public function testGetExtensions()
    {
        $this->assertSame(
            array('mext'),
            $this->format->getExtensions()
        );
    }

    public function testSetExtension()
    {
        $this->format->setExtensions('testSetExtension');
        $this->assertSame(
            array('testSetExtension'),
            $this->format->getExtensions()
        );
    }

    public function testSetExtensions()
    {
        $this->format->setExtensions(
            array('ext1', 'ext2')
        );
        $this->assertSame(
            array('ext1', 'ext2'),
            $this->format->getExtensions()
        );
    }

    public function testSetExtensionsNull()
    {
        $this->format->setExtensions(null);
        $this->assertSame(array(), $this->format->getExtensions());
    }

    public function testToString()
    {
        $this->assertStringEquals('my', $this->format);
    }

    public function testSetParserClass()
    {
        $this->format->setParserClass('EasyRdf\MockParserClass');
        $this->assertSame(
            'EasyRdf\MockParserClass',
            $this->format->getParserClass()
        );
    }

    public function testSetParserClassNull()
    {
        $this->format->setParserClass(null);
        $this->assertSame(null, $this->format->getParserClass());
    }

    public function testSetParserClassEmpty()
    {
        $this->format->setParserClass('');
        $this->assertSame(null, $this->format->getParserClass());
    }

    public function testSetParserClassNonString()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$class should be a string'
        );
        $this->format->setParserClass($this);
    }

    public function testRegisterParser()
    {
        Format::registerParser('my', 'EasyRdf\MockParserClass');
        $this->assertSame(
            'EasyRdf\MockParserClass',
            $this->format->getParserClass()
        );
    }

    public function testRegisterParserForUnknownFormat()
    {
        Format::registerParser('testRegisterParser', 'EasyRdf\MockParserClass');
        $format = Format::getFormat('testRegisterParser');
        $this->assertNotNull($format);
        $this->assertSame('EasyRdf\MockParserClass', $format->getParserClass());
    }

    public function testNewParser()
    {
        $this->format->setParserClass('EasyRdf\MockParserClass');
        $parser = $this->format->newParser();
        $this->assertInternalType('object', $parser);
        $this->assertClass('EasyRdf\MockParserClass', $parser);
    }

    public function testNewParserNull()
    {
        $this->setExpectedException(
            'EasyRdf\Exception',
            'No parser class available for format: my'
        );
        $this->format->setParserClass(null);
        $parser = $this->format->newParser();
    }

    public function testSetSerialiserClass()
    {
        $this->format->setSerialiserClass('EasyRdf\MockSerialiserClass');
        $this->assertSame(
            'EasyRdf\MockSerialiserClass',
            $this->format->getSerialiserClass()
        );
    }

    public function testSetSerialiserClassNull()
    {
        $this->format->setSerialiserClass(null);
        $this->assertSame(null, $this->format->getSerialiserClass());
    }

    public function testSetSerialiserClassEmpty()
    {
        $this->format->setSerialiserClass('');
         $this->assertSame(null, $this->format->getSerialiserClass());
    }

    public function testSetSerialiserClassNonString()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$class should be a string'
        );
        $this->format->setSerialiserClass($this);
    }

    public function testNewSerialiser()
    {
        $this->format->setSerialiserClass('EasyRdf\MockSerialiserClass');
        $serialiser = $this->format->newSerialiser();
        $this->assertInternalType('object', $serialiser);
        $this->assertClass('EasyRdf\MockSerialiserClass', $serialiser);
    }

    public function testNewSerialiserNull()
    {
        $this->setExpectedException(
            'EasyRdf\Exception',
            'No serialiser class available for format: my'
        );
        $this->format->setSerialiserClass(null);
        $serialiser = $this->format->newSerialiser();
    }

    public function testRegisterSerialiser()
    {
        Format::registerSerialiser('my', 'EasyRdf\MockSerialiserClass');
        $this->assertSame(
            'EasyRdf\MockSerialiserClass',
            $this->format->getSerialiserClass()
        );
    }

    public function testRegisterSerialiserForUnknownFormat()
    {
        Format::registerSerialiser(
            'testRegisterSerialiser',
            'EasyRdf\MockSerialiserClass'
        );
        $format = Format::getFormat('testRegisterSerialiser');
        $this->assertNotNull($format);
        $this->assertSame(
            'EasyRdf\MockSerialiserClass',
            $format->getSerialiserClass()
        );
    }

    public function testGuessFormatPhp()
    {
        $data = array('http://www.example.com' => array());
        $this->assertStringEquals('php', Format::guessFormat($data));
    }

    public function testGuessFormatRdfXml()
    {
        $data = readFixture('foaf.rdf');
        $this->assertStringEquals('rdfxml', Format::guessFormat($data));
    }

    public function testGuessFormatJson()
    {
        $data = readFixture('foaf.json');
        $this->assertStringEquals('json', Format::guessFormat($data));
    }

    public function testGuessFormatTurtle()
    {
        $data = readFixture('foaf.ttl');
        $this->assertStringEquals('turtle', Format::guessFormat($data));
    }

    public function testGuessFormatTurtleWithComments()
    {
        $data = readFixture('webid.ttl');
        $this->assertStringEquals('turtle', Format::guessFormat($data));
    }

    public function testGuessFormatNtriples()
    {
        $data = readFixture('foaf.nt');
        $this->assertStringEquals('ntriples', Format::guessFormat($data));
    }

    public function testGuessFormatNtriplesWithComments()
    {
        $format = Format::guessFormat(
            "# This is a comment before the first triple\n".
            " <http://example.com> <http://example.com> <http://example.com> .\n"
        );
        $this->assertStringEquals('ntriples', $format);
    }

    public function testGuessFormatSparqlXml()
    {
        $data = readFixture('sparql_select_all.xml');
        $this->assertStringEquals('sparql-xml', Format::guessFormat($data));
    }

    public function testGuessFormatRdfa()
    {
        $data = readFixture('foaf.html');
        $this->assertStringEquals('rdfa', Format::guessFormat($data));
    }

    public function testGuessFormatHtml()
    {
        # We don't support any other microformats embedded in HTML
        $format = Format::guessFormat(
            '<html><head><title>Hello</title></head><body><h1>Hello World</h1></body></html>'
        );
        $this->assertStringEquals('rdfa', $format);
    }

    public function testGuessFormatHtml5()
    {
        $data = readFixture('html5.html');
        $this->assertStringEquals('rdfa', Format::guessFormat($data));
    }

    public function testGuessFormatXml()
    {
        # We support several different XML formats, don't know which one this is...
        $format = Format::guessFormat(
            '<?xml version="1.0" encoding="UTF-8"?>'
        );
        $this->assertSame(null, $format);
    }

    public function testGuessFormatByFilenameTtl()
    {
        $format = Format::guessFormat(
            '# This is a comment',
            'http://example.com/filename.ttl'
        );
        $this->assertStringEquals('turtle', $format);
    }

    public function testGuessFormatByFilenameRdf()
    {
        $format = Format::guessFormat(
            '                    <!-- lots of whitespace ',
            'file://../data/foaf.rdf'
        );
        $this->assertStringEquals('rdfxml', $format);
    }

    public function testGuessFormatByFilenameUnknown()
    {
        $format = Format::guessFormat(
            '<http://example.com> <http://example.com> <http://example.com> .',
            'http://example.com/foaf.foobar'
        );
        $this->assertStringEquals('ntriples', $format);
    }

    public function testGuessFormatUnknown()
    {
        $this->assertNull(
            Format::guessFormat('blah blah blah')
        );
    }
}
