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
class EasyRdf_Serialiser_Ntriples extends EasyRdf_Serialiser
{

    /**
     * Protected method to serialise a subject node into an N-Triples partial
     */
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

    /**
     * Protected method to serialise an object node into an N-Triples partial
     */
    protected function ntriplesObject($obj)
    {
        if (is_object($obj) and $obj instanceof EasyRdf_Resource) {
            return $this->ntriplesResource($obj);
        } else if (is_object($obj) and $obj instanceof EasyRdf_Literal) {
            // FIXME: peform encoding of Unicode characters as described here:
            // http://www.w3.org/TR/rdf-testcases/#ntrip_strings
            $value = $obj->getValue();
            $value = str_replace('\\', '\\\\', $value);
            $value = str_replace('"', '\\"', $value);
            $value = str_replace('\n', '\\n', $value);
            $value = str_replace('\r', '\\r', $value);
            $value = str_replace('\t', '\\t', $value);

            if ($obj->getLang()) {
                return '"' . $value . '"' . '@' . $obj->getLang();
            } else if ($obj->getDatatype()) {
                $datatype = EasyRdf_Namespace::expand($obj->getDatatype());
                return '"' . $value . '"' . "^^<$datatype>";
            } else {
                return '"' . $value . '"';
            }
        } else {
            throw new EasyRdf_Exception(
                "Unable to serialise object to ntriples: $obj"
            );
        }
    }

    /**
     * Serialise an EasyRdf_Graph into N-Triples
     *
     * @param string $graph An EasyRdf_Graph object.
     * @param string $format The name of the format to convert to (ntriples).
     * @return string The N-Triples formatted RDF.
     */
    public function serialise($graph, $format)
    {
        parent::checkSerialiseParams($graph, $format);

        if ($format != 'ntriples') {
            throw new EasyRdf_Exception(
                "EasyRdf_Serialiser_Ntriples does not support: $format"
            );
        }

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
}

EasyRdf_Format::registerSerialiser('ntriples', 'EasyRdf_Serialiser_Ntriples');
