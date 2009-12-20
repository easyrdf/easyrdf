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
 * @version    $Id: Rdfphp.php 197 2009-10-18 11:55:04Z njh@aelius.com $
 */

/**
 * @see EasyRdf_Exception
 */
require_once "EasyRdf/Exception.php";

/**
 * @see EasyRdf_Graph
 */
require_once "EasyRdf/Graph.php";

/**
 * @see EasyRdf_Namespace
 */
require_once "EasyRdf/Namespace.php";

/**
 * Class to serialise an EasyRdf_Graph into RDF
 * with no external dependancies.
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */
class EasyRdf_Serialiser_Builtin
{

    protected function ntriplesResource($res)
    {
        if (is_object($res)) {
            if ($res->isBNode()) {
                return $res->getURI();
            } else {
                return "<".$res->getURI().">";
            }
        } else {
            $uri = EasyRdf_Namespace::expand($res);
            if ($uri) {
                return "<$uri>";
            } else {
                return "<$res>";
            }
        }
    }

    protected function ntriplesObject($obj)
    {
        if (is_object($obj) and $obj instanceof EasyRdf_Resource) {
            return $this->ntriplesResource($obj);
        } else if (is_scalar($obj)) {
            // FIXME: peform encoding of Unicode characters as described here:
            // http://www.w3.org/TR/rdf-testcases/#ntrip_strings
            $literal = str_replace('\\', '\\\\', $obj);
            $literal = str_replace('"', '\\"', $literal);
            $literal = str_replace('\n', '\\n', $literal);
            $literal = str_replace('\r', '\\r', $literal);
            $literal = str_replace('\t', '\\t', $literal);
            return "\"$literal\"";
        } else {
            throw new EasyRdf_Exception(
                "Unable to serialise object to ntriples: $obj"
            );
        }
    }

    /**
     * Method to serialise an EasyRdf_Graph into N-Triples
     *
     */
    protected function to_ntriples($graph)
    {
        $nt = '';
        foreach ($graph->resources() as $resource) {
            foreach ($resource->properties() as $property) {
                $objects = $resource->all($property);
                foreach ($objects as $object) {
                    $nt .= $this->ntriplesResource($resource)." ";
                    $nt .= $this->ntriplesResource($property)." ";
                    $nt .= $this->ntriplesObject($object)." .\n";
                }
            }
        }
        return $nt;
    }

    /**
     * Method to serialise an EasyRdf_Graph into RDF/PHP
     *
     * http://n2.talis.com/wiki/RDF_PHP_Specification
     */
    protected function to_rdfphp($graph)
    {
        $rdfphp = array();
        foreach ($graph->resources() as $resource) {
            $properties = $resource->properties();
            if (count($properties) == 0) continue;
            
            $subj = $resource->getUri();
            if (!isset($rdfphp[$subj])) {
                $rdfphp[$subj] = array();
            }
        
            foreach ($properties as $property) {
                $prop = EasyRdf_Namespace::expand($property);
                if ($prop) {
                    if (!isset($rdfphp[$subj][$prop])) {
                        $rdfphp[$subj][$prop] = array();
                    }
                    $objects = $resource->all($property);
                    foreach ($objects as $obj) {
                        if (is_object($obj) and 
                           ($obj instanceof EasyRdf_Resource)) {
                            if ($obj->isBNode()) {
                                $object = array('type' => 'bnode',
                                                'value' => $obj->getUri());
                            } else {
                                $object = array('type' => 'uri',
                                                'value' => $obj->getUri());
                            }
                        } else {
                            $object = array('type' => 'literal',
                                            'value' => $obj);
                        }

                        array_push($rdfphp[$subj][$prop], $object);
                    }
                }
            }
        }
        return $rdfphp;
    }
    
    /**
     * Method to serialise an EasyRdf_Graph into RDF/JSON
     *
     * http://n2.talis.com/wiki/RDF_JSON_Specification
     */
    protected function to_json($graph)
    {
        return json_encode($this->to_rdfphp($graph));
    }
    
    /**
     * Method to serialise an EasyRdf_Graph into format of choice
     */
    public function serialise($graph, $format)
    {
        if ($graph == null or !is_object($graph) or
            get_class($graph) != 'EasyRdf_Graph') {
            throw new InvalidArgumentException(
                "\$graph should be an EasyRdf_Graph object and cannot be null"
            );
        }

        if ($format == null or !is_string($format) or $format == '') {
            throw new InvalidArgumentException(
                "\$format should be a string and cannot be null or empty"
            );
        }
    
        if ($format == 'php') {
            return $this->to_rdfphp($graph);
        } else if ($format == 'json') {
            return $this->to_json($graph);
        } else if ($format == 'ntriples') {
            return $this->to_ntriples($graph);
        } else {
            throw new EasyRdf_Exception(
                "Unsupported serialisation format: $format"
            );
        }
    }
}

