<?php
namespace EasyRdf;

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009-2015 Nicholas J Humfrey.  All rights reserved.
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
 * @copyright  Copyright (c) 2009-2015 Nicholas J Humfrey
 * @license    https://www.opensource.org/licenses/bsd-license.php
 */

/**
 * Class to map between RDF Types and PHP Classes
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009-2015 Nicholas J Humfrey
 * @license    https://www.opensource.org/licenses/bsd-license.php
 */
class TypeMapper
{
    /** The type map registry */
    private static $map = array();

    /** Default resource class */
    private static $defaultResourceClass = 'EasyRdf\Resource';

    /** Get the registered class for an RDF type
     *
     * If a type is not registered, then this method will return null.
     *
     * @param  string  $type   The RDF type (e.g. foaf:Person)
     *
     * @throws \InvalidArgumentException
     * @return string          The class name (e.g. Model_Foaf_Name)
     */
    public static function get($type)
    {
        if (!is_string($type) or $type == null or $type == '') {
            throw new \InvalidArgumentException(
                "\$type should be a string and cannot be null or empty"
            );
        }

        $type = RdfNamespace::expand($type);
        if (array_key_exists($type, self::$map)) {
            return self::$map[$type];
        } else {
            return null;
        }
    }

    /** Register an RDF type with a PHP Class name
     *
     * @param  string  $type   The RDF type (e.g. foaf:Person)
     * @param  string  $class  The PHP class name (e.g. Model_Foaf_Name)
     *
     * @throws \InvalidArgumentException
     * @return string          The PHP class name
     */
    public static function set($type, $class)
    {
        if (!is_string($type) or $type == null or $type == '') {
            throw new \InvalidArgumentException(
                "\$type should be a string and cannot be null or empty"
            );
        }

        if (!is_string($class) or $class == null or $class == '') {
            throw new \InvalidArgumentException(
                "\$class should be a string and cannot be null or empty"
            );
        }

        $type = RdfNamespace::expand($type);
        return self::$map[$type] = $class;
    }

    /**
      * Delete an existing RDF type mapping.
      *
      * @param  string  $type   The RDF type (e.g. foaf:Person)
      *
      * @throws \InvalidArgumentException
      */
    public static function delete($type)
    {
        if (!is_string($type) or $type == null or $type == '') {
            throw new \InvalidArgumentException(
                "\$type should be a string and cannot be null or empty"
            );
        }

        $type = RdfNamespace::expand($type);
        if (isset(self::$map[$type])) {
            unset(self::$map[$type]);
        }
    }

    /**
     * @return string           The default Resource class
     */
    public static function getDefaultResourceClass()
    {
        return self::$defaultResourceClass;
    }

    /**
     * Sets the default resource class
     *
     * @param  string $class The resource full class name (e.g. \MyCompany\Resource)
     *
     * @throws \InvalidArgumentException
     * @return string           The default Resource class
     */
    public static function setDefaultResourceClass($class)
    {
        if (!is_string($class) or $class == null or $class == '') {
            throw new \InvalidArgumentException(
                "\$class should be a string and cannot be null or empty"
            );
        }

        if (!class_exists($class)) {
            throw new \InvalidArgumentException(
                "Given class should be an existing class"
            );
        }

        $ancestors = class_parents($class);
        if (($class != 'EasyRdf\Resource') && (empty($ancestors) || !in_array('EasyRdf\Resource', $ancestors))) {
            throw new \InvalidArgumentException(
                "Given class should have EasyRdf\\Resource as an ancestor"
            );
        }

        return self::$defaultResourceClass = $class;
    }
}


/*
   Register default set of mapped types
*/

TypeMapper::set('rdf:Alt', 'EasyRdf\Container');
TypeMapper::set('rdf:Bag', 'EasyRdf\Container');
TypeMapper::set('rdf:List', 'EasyRdf\Collection');
TypeMapper::set('rdf:Seq', 'EasyRdf\Container');
