<?php

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009 Nicholas J Humfrey.  All rights reserved.
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
 * @copyright  Copyright (c) 2009 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 * @version    $Id$
 */

require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'TestHelper.php';

class EasyRdf_ResourceTest extends EasyRdf_TestCase
{
    protected $_resource = null;

    /**
     * Set up the test suite before each test
     */
    public function setUp()
    {
        $this->_resource = new EasyRdf_Resource('http://www.example.com/#me');
        $this->_resource->set('rdf:type', 'foaf:Person');
        $this->_resource->add('test:prop', 'Test A');
        $this->_resource->add('test:prop', new EasyRdf_Literal('Test B', 'en'));
    }

    public function testConstructNull()
    {
        $this->setExpectedException('InvalidArgumentException');
        $res = new EasyRdf_Resource(null);
    }

    public function testConstructEmpty()
    {
        $this->setExpectedException('InvalidArgumentException');
        $res = new EasyRdf_Resource('');
    }

    public function testConstructNonString()
    {
        $this->setExpectedException('InvalidArgumentException');
        $res = new EasyRdf_Resource(array());
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
        $this->assertStringEquals(
            'Test A',
            $this->_resource->get('test:prop')
        );
    }

    public function testGetWithLanguage()
    {
        $this->assertStringEquals(
            'Test B',
            $this->_resource->get('test:prop', 'en')
        );
    }

    public function testGetNonExistantProperty()
    {
        $this->assertNull($this->_resource->get('foo:bar'));
    }

    public function testGetNullKey()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_resource->get(null);
    }

    public function testGetEmptyKey()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_resource->get('');
    }

    public function testGetNonStringKey()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_resource->get(array());
    }

    public function testAll()
    {
        $all = $this->_resource->all('test:prop');
        $this->assertEquals(2, count($all));
        $this->assertStringEquals('Test A', $all[0]);
        $this->assertStringEquals('Test B', $all[1]);
    }

    public function testAllWithLang()
    {
        $all = $this->_resource->all('test:prop','en');
        $this->assertEquals(1, count($all));
        $this->assertStringEquals('Test B', $all[0]);
    }

    public function testAllNonExistantProperty()
    {
        $this->assertEquals(
            array(),
            $this->_resource->all('foo:bar')
        );
    }

    public function testAllNullKey()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_resource->all(null);
    }

    public function testAllEmptyKey()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_resource->all('');
    }

    public function testAllNonStringKey()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_resource->all(array());
    }

    public function testSet()
    {
        $this->_resource->set('test:prop', 'Test C');
        $all = $this->_resource->all('test:prop');
        $this->assertEquals(1, count($all));
        $this->assertStringEquals('Test C', $all[0]);
    }

    public function testSetNullKey()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_resource->set(null, 'Test C');
    }

    public function testSetEmptyKey()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_resource->set('', 'Test C');
    }

    public function testSetNonStringKey()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_resource->set(array(), 'Test C');
    }

    public function testSetNull()
    {
        $this->_resource->set('test:prop', null);
        $this->assertEquals(
            array(),
            $this->_resource->all('test:prop')
        );
    }

    public function testAdd()
    {
        $this->_resource->add('test:prop', 'Test C');
        $all = $this->_resource->all('test:prop');
        $this->assertEquals(3, count($all));
        $this->assertStringEquals('Test A', $all[0]);
        $this->assertStringEquals('Test B', $all[1]);
        $this->assertStringEquals('Test C', $all[2]);
    }

    public function testAddMultipleValues()
    {
        $this->_resource->add('test:prop', array('Test C', 'Test D'));
        $all = $this->_resource->all('test:prop');
        $this->assertEquals(4, count($all));
        $this->assertStringEquals('Test A', $all[0]);
        $this->assertStringEquals('Test B', $all[1]);
        $this->assertStringEquals('Test C', $all[2]);
        $this->assertStringEquals('Test D', $all[3]);
    }

    public function testAddMultipleProperties()
    {
        $this->_resource->add(array('test:prop1', 'test:prop2'), 'Test');

        $all = $this->_resource->all('test:prop1');
        $this->assertEquals(1, count($all));
        $this->assertStringEquals('Test', $all[0]);

        $all = $this->_resource->all('test:prop2');
        $this->assertEquals(1, count($all));
        $this->assertStringEquals('Test', $all[0]);
    }

    public function testAddAssociateProperties()
    {
        $this->_resource->add(array('test:prop' => 'Test C'));
        $all = $this->_resource->all('test:prop');
        $this->assertEquals(3, count($all));
        $this->assertStringEquals('Test A', $all[0]);
        $this->assertStringEquals('Test B', $all[1]);
        $this->assertStringEquals('Test C', $all[2]);
    }

    public function testAddNull()
    {
        $this->_resource->add('test:prop', null);
        $all = $this->_resource->all('test:prop');
        $this->assertEquals(2, count($all));
        $this->assertStringEquals('Test A', $all[0]);
        $this->assertStringEquals('Test B', $all[1]);
    }

    public function testAddNullKey()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_resource->add(null, 'Test C');
    }

    public function testAddEmptyKey()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_resource->add('', 'Test C');
    }

    public function testAddNonStringKey()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_resource->add(array(), 'Test C');
    }

    public function testDelete()
    {
        $this->assertStringEquals('Test A', $this->_resource->get('test:prop'));
        $this->_resource->delete('test:prop');
        $this->assertEquals(array(), $this->_resource->all('test:prop'));
    }

    public function testDeleteNullKey()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_resource->delete(null);
    }

    public function testDeleteEmptyKey()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_resource->delete('');
    }

    public function testDeleteNonStringKey()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_resource->delete(array());
    }

    public function testJoinDefaultGlue()
    {
        $this->assertEquals(
            'Test A Test B',
            $this->_resource->join('test:prop')
        );
    }

    public function testJoinWithLang()
    {
        $this->assertEquals(
            'Test B',
            $this->_resource->join('test:prop', ' ', 'en')
        );
    }

    public function testJoinNonExistantProperty()
    {
        $this->assertEquals('', $this->_resource->join('foo:bar'));
    }

    public function testJoinCustomGlue()
    {
        $this->assertEquals(
            'Test A:Test B',
            $this->_resource->join('test:prop', ':')
        );
    }

    public function testJoinNullKey()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_resource->join(null, 'Test C');
    }

    public function testJoinEmptyKey()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_resource->join('', 'Test C');
    }

    public function testJoinNonStringKey()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_resource->join(array(), 'Test C');
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
            array('rdf:type', 'test:prop'),
            $this->_resource->properties()
        );
    }

    public function testMatches()
    {
        $this->assertTrue(
            $this->_resource->matches('test:prop', 'Test A')
        );
    }

    public function testNotMatches()
    {
        $this->assertFalse(
            $this->_resource->matches('test:prop', 'Test C')
        );
    }

    public function testTypes()
    {
        $types = $this->_resource->types();
        $this->assertEquals(1, count($types));
        $this->assertStringEquals('foaf:Person', $types[0]);
    }

    public function testType()
    {
        $this->assertStringEquals('foaf:Person', $this->_resource->type());
    }

    public function testPrefix()
    {
        $foafName = new EasyRdf_Resource('http://xmlns.com/foaf/0.1/name');
        $this->assertStringEquals('foaf', $foafName->prefix());
    }

    public function testUnknownPrefix()
    {
        $unknown = new EasyRdf_Resource('http://www.example.com/foo');
        $this->assertNull($unknown->prefix());
    }

    public function testShorten()
    {
        $foafName = new EasyRdf_Resource('http://xmlns.com/foaf/0.1/name');
        $this->assertEquals('foaf:name', $foafName->shorten());
    }
    public function testShortenUnknown()
    {
        $unknown = new EasyRdf_Resource('http://www.example.com/foo');
        $this->assertNull($unknown->shorten());
    }

    public function testLabelNoRdfsLabel()
    {
        $this->assertNull($this->_resource->label());
    }

    public function testLabelWithRdfsLabel()
    {
        $this->_resource->set('rdfs:label', 'Label Text');
        $this->_resource->set('foaf:name', 'Foaf Name');
        $this->_resource->set('dc:title', 'Dc Title');
        $this->assertStringEquals('Label Text', $this->_resource->label());
    }

    public function testLabelWithFoafName()
    {
        $this->_resource->set('foaf:name', 'Foaf Name');
        $this->_resource->set('dc:title', 'Dc Title');
        $this->assertStringEquals('Foaf Name', $this->_resource->label());
    }

    public function testLabelWithDcTitle()
    {
        $this->_resource->set('dc:title', 'Dc Title');
        $this->assertStringEquals('Dc Title', $this->_resource->label());
    }

    public function testLabelWithLang()
    {
        $this->_resource->set('rdfs:label', 'Label Text');
        $this->_resource->set('dc:title', new EasyRdf_Literal('Dc Title', 'en'));
        $this->assertStringEquals('Dc Title', $this->_resource->label('en'));
    }

    public function testDump()
    {
        $this->markTestIncomplete();
    }

    public function testMagicGet()
    {
        $this->assertStringEquals('Test A', $this->_resource->getTest_prop());
    }

    public function testMagicGetNonExistantProperty()
    {
        $this->assertStringEquals('', $this->_resource->getFoo_bar());
    }

    public function testMagicAll()
    {
        $all = $this->_resource->allTest_prop();
        $this->assertEquals(2, count($all));
        $this->assertStringEquals('Test A', $all[0]);
        $this->assertStringEquals('Test B', $all[1]);
    }

    public function testMagicAllNonExistantProperty()
    {
        $this->assertEquals(array(), $this->_resource->allFoo_bar());
    }

    public function testMagicBadMethodCall()
    {
        $this->setExpectedException('BadMethodCallException');
        $this->_resource->fooBar();
    }

    public function testToString()
    {
        $this->assertStringEquals(
            'http://www.example.com/#me',
            $this->_resource
        );
    }
}
