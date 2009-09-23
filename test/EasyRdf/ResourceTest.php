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

    public function testConstructNull()
    {
        $this->setExpectedException('EasyRdf_Exception');
        $res = new EasyRdf_Resource(null);
    }

    public function testConstructEmpty()
    {
        $this->setExpectedException('EasyRdf_Exception');
        $res = new EasyRdf_Resource('');
    }

    public function testConstructNonString()
    {
        $this->setExpectedException('EasyRdf_Exception');
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

    public function testGetNullKey()
    {
        $this->setExpectedException('EasyRdf_Exception');
        $this->_resource->get(null);
    }
    
    public function testGetEmptyKey()
    {
        $this->setExpectedException('EasyRdf_Exception');
        $this->_resource->get('');
    }
    
    public function testGetNonStringKey()
    {
        $this->setExpectedException('EasyRdf_Exception');
        $this->_resource->get(array());
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

    public function testAllNullKey()
    {
        $this->setExpectedException('EasyRdf_Exception');
        $this->_resource->all(null);
    }
    
    public function testAllEmptyKey()
    {
        $this->setExpectedException('EasyRdf_Exception');
        $this->_resource->all('');
    }
    
    public function testAllNonStringKey()
    {
        $this->setExpectedException('EasyRdf_Exception');
        $this->_resource->all(array());
    }

    public function testSet()
    {
        $this->_resource->set('test_prop', 'Test C');
        $this->assertEquals(
            array('Test C'),
            $this->_resource->all('test_prop')
        );
    }

    public function testSetNullKey()
    {
        $this->setExpectedException('EasyRdf_Exception');
        $this->_resource->set(null, 'Test C');
    }
    
    public function testSetEmptyKey()
    {
        $this->setExpectedException('EasyRdf_Exception');
        $this->_resource->set('', 'Test C');
    }
    
    public function testSetNonStringKey()
    {
        $this->setExpectedException('EasyRdf_Exception');
        $this->_resource->set(array(), 'Test C');
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

    public function testAddMultiple()
    {
        $this->_resource->add('test_prop', array('Test C', 'Test D'));
        $this->assertEquals(
            array('Test A', 'Test B', 'Test C', 'Test D'),
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

    public function testAddNullKey()
    {
        $this->setExpectedException('EasyRdf_Exception');
        $this->_resource->add(null, 'Test C');
    }
    
    public function testAddEmptyKey()
    {
        $this->setExpectedException('EasyRdf_Exception');
        $this->_resource->add('', 'Test C');
    }
    
    public function testAddNonStringKey()
    {
        $this->setExpectedException('EasyRdf_Exception');
        $this->_resource->add(array(), 'Test C');
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

    public function testJoinNullKey()
    {
        $this->setExpectedException('EasyRdf_Exception');
        $this->_resource->join(null, 'Test C');
    }
    
    public function testJoinEmptyKey()
    {
        $this->setExpectedException('EasyRdf_Exception');
        $this->_resource->join('', 'Test C');
    }
    
    public function testJoinNonStringKey()
    {
        $this->setExpectedException('EasyRdf_Exception');
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

    public function testMagicInvalidCall()
    {
        $this->setExpectedException('EasyRdf_Exception');
        $this->_resource->fooBar();
    }

    public function testToString()
    {
        $this->assertEquals(
            'http://www.example.com/#me',
            $this->_resource->__toString()
        );
    }
}
