<?php

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009-2010 Nicholas J Humfrey.  All rights reserved.
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

/**
 * Class to serialise an EasyRdf_Graph into RDF
 * with no external dependancies.
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009-2010 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */
class EasyRdf_Serialiser_RdfPhp extends EasyRdf_Serialiser
{
    /**
     * Serialise an EasyRdf_Graph into RDF format of choice.
     *
     * @param string $graph An EasyRdf_Graph object.
     * @param string $format The name of the format to convert to.
     * @return string The RDF in the new desired format.
     */

    /**
     * Method to serialise an EasyRdf_Graph into RDF/PHP
     *
     * http://n2.talis.com/wiki/RDF_PHP_Specification
     */
    public function serialise($graph, $format)
    {
        parent::checkSerialiseParams($graph, $format);

        if ($format != 'php') {
            throw new EasyRdf_Exception(
                "EasyRdf_Serialiser_RdfPhp does not support: $format"
            );
        }

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
                        } else if (is_object($obj) and
                           ($obj instanceof EasyRdf_Literal)) {
                            $object = array('type' => 'literal',
                                            'value' => $obj->getValue());
                            if ($obj->getLang())
                                $object['lang'] = $obj->getLang();
                            if ($obj->getDatatype())
                                $object['datatype'] = $obj->getDatatype();
                        } else {
                            throw new EasyRdf_Exception(
                                "Unsupported to serialise: $obj"
                            );
                        }

                        array_push($rdfphp[$subj][$prop], $object);
                    }
                }
            }
        }
        return $rdfphp;
    }
}

EasyRdf_Format::registerSerialiser('php', 'EasyRdf_Serialiser_RdfPhp');
