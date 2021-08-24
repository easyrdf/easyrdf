<?php
namespace EasyRdf;

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) Nicholas J Humfrey.  All rights reserved.
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
 * @copyright  Copyright (c) Nicholas J Humfrey
 * @license    https://www.opensource.org/licenses/bsd-license.php
 */

require_once dirname(__DIR__).DIRECTORY_SEPARATOR.'TestHelper.php';

class MyTypeClass extends Resource
{
    public function myMethod()
    {
        return true;
    }
}


class TypeMapperTest extends TestCase
{
    public function setUp(): void
    {
        TypeMapper::set('rdf:mytype', 'EasyRdf\MyTypeClass');
    }

    public function tearDown(): void
    {
        TypeMapper::delete('rdf:mytype');
        TypeMapper::delete('foaf:Person');
    }


    public function testGet()
    {
        $this->assertSame(
            'EasyRdf\MyTypeClass',
            TypeMapper::get('rdf:mytype')
        );
    }

    public function testGetUri()
    {
        $this->assertSame(
            'EasyRdf\MyTypeClass',
            TypeMapper::get(
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#mytype'
            )
        );
    }

    public function testGetNull()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$type should be a string and cannot be null or empty'
        );
        TypeMapper::get(null);
    }

    public function testGetEmpty()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$type should be a string and cannot be null or empty'
        );
        TypeMapper::get('');
    }

    public function testGetNonString()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$type should be a string and cannot be null or empty'
        );
        TypeMapper::get(array());
    }

    public function testGetUnknown()
    {
        $this->assertSame(null, TypeMapper::get('unknown:type'));
    }

    public function testSetUri()
    {
        TypeMapper::set(
            'http://xmlns.com/foaf/0.1/Person',
            'EasyRdf\MyTypeClass'
        );

        $this->assertSame(
            'EasyRdf\MyTypeClass',
            TypeMapper::get('foaf:Person')
        );
    }

    public function testSetTypeNull()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$type should be a string and cannot be null or empty'
        );
        TypeMapper::set(null, 'EasyRdf\MyTypeClass');
    }

    public function testSetTypeEmpty()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$type should be a string and cannot be null or empty'
        );
        TypeMapper::set('', 'EasyRdf\MyTypeClass');
    }

    public function testSetTypeNonString()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$type should be a string and cannot be null or empty'
        );
        TypeMapper::set(array(), 'EasyRdf\MyTypeClass');
    }

    public function testSetClassNull()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$class should be a string and cannot be null or empty'
        );
        TypeMapper::set('rdf:mytype', null);
    }

    public function testSetClassEmpty()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$class should be a string and cannot be null or empty'
        );
        TypeMapper::set('rdf:mytype', '');
    }

    public function testSetClassNonString()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$class should be a string and cannot be null or empty'
        );
        TypeMapper::set('rdf:mytype', array());
    }

    public function testDelete()
    {
        $this->assertSame('EasyRdf\MyTypeClass', TypeMapper::get('rdf:mytype'));
        TypeMapper::delete('rdf:mytype');
        $this->assertSame(null, TypeMapper::get('rdf:mytype'));
    }

    public function testDeleteTypeNull()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$type should be a string and cannot be null or empty'
        );
        TypeMapper::delete(null);
    }

    public function testDeleteTypeEmpty()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$type should be a string and cannot be null or empty'
        );
        TypeMapper::delete('');
    }

    public function testDeleteTypeNonString()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$type should be a string and cannot be null or empty'
        );
        TypeMapper::delete(array());
    }

    public function testSetNonExtendingDefaultResourceClass()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Given class should have EasyRdf\Resource as an ancestor'
        );
        TypeMapper::setDefaultResourceClass('EasyRdf\Graph');
    }

    public function testSetBaseDefaultResourceClass()
    {
        TypeMapper::setDefaultResourceClass('EasyRdf\Resource');
        $this->assertEquals('EasyRdf\Resource', TypeMapper::getDefaultResourceClass());
    }

    public function testSetDefaultResourceClass()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Given class should be an existing class'
        );
        TypeMapper::setDefaultResourceClass('FooBar\Resource');
    }

    public function testSetDefaultResourceClassEmptyString()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$class should be a string and cannot be null or empty'
        );
        TypeMapper::setDefaultResourceClass('');
    }

    public function testSetDefaultResourceClassNull()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$class should be a string and cannot be null or empty'
        );
        TypeMapper::setDefaultResourceClass(null);
    }

    public function testSetDefaultResourceClassNonString()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$class should be a string and cannot be null or empty'
        );
        TypeMapper::setDefaultResourceClass(array());
    }

    public function testInstantiate()
    {
        TypeMapper::set('foaf:Person', 'EasyRdf\MyTypeClass');
        $data = readFixture('foaf.json');
        $graph = new Graph(
            'http://www.example.com/joe/foaf.rdf',
            $data,
            'json'
        );
        $joe = $graph->resource('http://www.example.com/joe#me');
        $this->assertClass('EasyRdf\MyTypeClass', $joe);
        $this->assertTrue($joe->myMethod());

        $joeFoaf = $graph->resource('http://www.example.com/joe/foaf.rdf');

        $this->assertClass('EasyRdf\Resource', $joeFoaf);

        TypeMapper::setDefaultResourceClass('EasyRdf\MyTypeClass');
        $graph = new Graph(
            'http://www.example.com/joe/foaf.rdf',
            $data,
            'json'
        );
        $joesFoaf = $graph->resource('http://www.example.com/joe/foaf.rdf');
        $this->assertClass('EasyRdf\MyTypeClass', $joesFoaf);
        $this->assertTrue($joesFoaf->myMethod());
    }
}
