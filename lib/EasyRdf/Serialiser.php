<?php

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

/**
 * Parent class for the EasyRdf serialiser
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009-2013 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */
class EasyRdf_Serialiser
{
    protected $prefixes = array();

    public function __construct()
    {
    }

    /**
     * Keep track of the prefixes used while serialising
     * @ignore
     */
    protected function addPrefix($qname)
    {
        list ($prefix) = explode(':', $qname);
        $this->prefixes[$prefix] = true;
    }

    /**
     * Check and cleanup parameters passed to serialise() method
     * @ignore
     */
    protected function checkSerialiseParams(&$graph, &$format)
    {
        if (is_null($graph) or !is_object($graph) or !($graph instanceof EasyRdf_Graph)) {
            throw new InvalidArgumentException(
                "\$graph should be an EasyRdf_Graph object and cannot be null"
            );
        }

        if (is_null($format) or $format == '') {
            throw new InvalidArgumentException(
                "\$format cannot be null or empty"
            );
        } elseif (is_object($format) and ($format instanceof EasyRdf_Format)) {
            $format = $format->getName();
        } elseif (!is_string($format)) {
            throw new InvalidArgumentException(
                "\$format should be a string or an EasyRdf_Format object"
            );
        }
    }

    /**
     * Protected method to get the number of reverse properties for a resource
     * If a resource only has a single property, the number of values for that
     * property is returned instead.
     * @ignore
     */
    protected function reversePropertyCount($resource)
    {
        $properties = $resource->reversePropertyUris();
        $count = count($properties);
        if ($count == 1) {
            $property = $properties[0];
            return $resource->countValues("^<$property>");
        } else {
            return $count;
        }
    }

    /**
     * Sub-classes must follow this protocol
     * @ignore
     */
    public function serialise($graph, $format)
    {
        throw new EasyRdf_Exception(
            "This method should be overridden by sub-classes."
        );
    }
}
