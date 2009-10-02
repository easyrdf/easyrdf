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

/**
 * @see EasyRdf_Resource
 */
require_once "EasyRdf/Resource.php";

/**
 * @see EasyRdf_TypeMapper
 */
require_once "EasyRdf/TypeMapper.php";


/**
 * Class that that instaniatated for owl:Property resources.
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */
class EasyRdf_Owl_Property extends EasyRdf_Resource
{

    /**
      * Return an associative array of all the properties in a graph
      *
      * If no properties are found in the graph, an empty array is returned.
      *
      * @param object EasyRdf_Graph $graph The Graph to inspect
      * @return array An array of properties keyed by shortened URI
      */
    public static function findAll($graph)
    {
        $propertyTypes = array(
            'rdf:Property',
            'owl:Property',
            'owl:ObjectProperty',
            'owl:DatatypeProperty'
        );
        $properties = array();
        foreach ($propertyTypes as $propertyType) {
            foreach ($graph->allOfType($propertyType) as $property) {
                $key = $property->shorten();
                if ($key) {
                    $properties[$key] = $property;
                }
            }
        }
        return $properties;
    }
    
    /**
      * Get the cardinality of a property.
      *
      * @return string '1' if the property takes a single value, otherwise 'N'.
      */
    public function cardinality()
    {
        $types = $this->types();
        # Apart from owl_FunctionalProperty, these rules really correct,
        # but they provide a good set of defaults
        if (in_array('owl:FunctionalProperty', $types) or
            in_array('owl:DatatypeProperty', $types) or 
            in_array('owl:InverseFunctionalProperty', $types)) {
            return '1';
        } else {
            return 'N';
        }
    }

}


## FIXME: Don't Repeat Yourself
EasyRdf_TypeMapper::set('rdf:Property', 'EasyRdf_Owl_Property');
EasyRdf_TypeMapper::set('owl:Property', 'EasyRdf_Owl_Property');
EasyRdf_TypeMapper::set('owl:ObjectProperty', 'EasyRdf_Owl_Property');
EasyRdf_TypeMapper::set('owl:DatatypeProperty', 'EasyRdf_Owl_Property');
