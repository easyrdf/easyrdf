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
 * Class that that instaniatated for owl:Class resources.
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */
class EasyRdf_Owl_Class extends EasyRdf_Resource
{
    /**
      * Convert the RDF Class into a suitable PHP class name
      *
      * @return string The class name (e.g. Foaf_Person)
      */
    function className()
    {
        return ucfirst(str_replace(':', '_', $this->shorten()));
    }
    
    /**
      * Convert the RDF Class into a suitable PHP filename
      *
      * @return string The file name (e.g. Foaf/Person.php)
      */
    function fileName()
    {
        return str_replace('_', '/', $this->className()) . '.php';
    }
    
    /**
      * Get an array of properties for a class
      *
      * If no properties are found for the class, an empty array is returned.
      *
      * @return array An array of EasyRdf_Property associated with the class
      */
    function classProperties($graph)
    {
        // FIXME: not ideal having to pass graph in here
        $properties = array();
        # FIXME: cache this somehow?
        $owlThing = $graph->get('http://www.w3.org/2002/07/owl#Thing');
        $superClass = $this->get('rdfs_subClassOf');
        if ($superClass == $owlThing) $superClass = '';
        $allProperties = EasyRdf_Owl_Property::findAll($graph);
        foreach ($allProperties as $name => $property) {
            if (($superClass == '' and
                (count($property->all('rdfs:domain')) == 0 or 
                in_array($owlThing, $property->all('rdfs:domain')))) or 
                in_array($this, $property->all('rdfs:domain'))
            ) {
                array_push($properties, $property);
            }
        }
        return $properties;
    }
}

EasyRdf_TypeMapper::set('owl:Class', 'EasyRdf_Owl_Class');
