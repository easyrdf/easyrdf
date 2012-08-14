<?php

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009-2012 Nicholas J Humfrey.  All rights reserved.
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
 * @copyright  Copyright (c) 2009-2012 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 * @version    $Id$
 */

/**
 * Class to serialise an EasyRdf_Graph to Turtle
 * with no external dependancies.
 *
 * http://www.dajobe.org/2004/01/turtle
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009-2012 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */
class EasyRdf_Serialiser_Turtle extends EasyRdf_Serialiser_Ntriples
{
    private $_prefixes = array();

    /**
     * @ignore
     */
    protected function addPrefix($qname)
    {
        list ($prefix) = explode(':', $qname);
        $this->_prefixes[$prefix] = true;
    }

    /**
     * @ignore
     */
    protected function serialiseResource($resource)
    {
        if (substr($resource, 0, 2) == '_:') {
            return $resource;
        } else {
            $short = EasyRdf_Namespace::shorten($resource);
            if ($short) {
                $this->addPrefix($short);
                return $short;
            } else {
                $uri = str_replace('>', '\\>', $resource);
                return "<$resource>";
            }
        }
    }

    /**
     * @ignore
     */
    protected function serialiseObject($object)
    {
        if ($object['type'] == 'uri' or $object['type'] == 'bnode') {
            return $this->serialiseResource($object['value']);
        } else if ($object['type'] == 'literal') {
            $value = $this->escapeString($object['value']);

            if (isset($object['datatype'])) {
                $short = EasyRdf_Namespace::shorten($object['datatype'], true);
                if ($short) {
                    $this->addPrefix($short);
                    if ($short == 'xsd:integer') {
                        return sprintf('%d^^%s', $value, $short);
                    } else if ($short == 'xsd:decimal') {
                        return sprintf('%g^^%s', $value, $short);
                    } else if ($short == 'xsd:double') {
                        return sprintf('%e^^%s', $value, $short);
                    } else if ($short == 'xsd:boolean') {
                        return sprintf(
                            '%s^^%s',
                            $value ? 'true' : 'false',
                            $short
                        );
                    } else {
                        return sprintf('"%s"^^%s', $value, $short);
                    }
                } else {
                    $datatypeUri = str_replace('>', '\\>', $object['datatype']);
                    return sprintf('"%s"^^<%s>', $value, $datatypeUri);
                }
            } else if (isset($object['lang'])) {
                return '"' . $value . '"@' . $object['lang'];
            } else {
                return '"' . $value . '"';
            }
        }
    }

    /**
     * @ignore
     */
    protected function serialisePrefixes()
    {
        $turtle = '';
        foreach ($this->_prefixes as $prefix => $count) {
            $url = EasyRdf_Namespace::get($prefix);
            $turtle .= "@prefix $prefix: <$url> .\n";
        }
        return $turtle;
    }

    /**
     * Serialise an EasyRdf_Graph to Turtle.
     *
     * @param object EasyRdf_Graph $graph   An EasyRdf_Graph object.
     * @param string  $format               The name of the format to convert to.
     * @return string                       The RDF in the new desired format.
     */
    public function serialise($graph, $format)
    {
        parent::checkSerialiseParams($graph, $format);

        if ($format != 'turtle' and $format != 'n3') {
            throw new EasyRdf_Exception(
                "EasyRdf_Serialiser_Turtle does not support: $format"
            );
        }

        $this->_prefixes = array('rdf' => true);

        $turtle = '';
        foreach ($graph->toArray() as $subject => $properties) {
            if (count($properties) == 0)
                continue;

            $turtle .= $this->serialiseResource($subject);

            if (count($properties) > 1) {
                $turtle .= "\n   ";
            }

            $pCount = 0;
            foreach ($properties as $property => $objects) {
                $short = EasyRdf_Namespace::shorten($property, true);
                if ($short) {
                    $this->addPrefix($short);
                    $pStr = ($short == 'rdf:type' ? 'a' : $short);
                } else {
                    $pStr = '<'.str_replace('>', '\\>', $property).'>';
                }

                if ($pCount) {
                    $turtle .= " ;\n   ";
                }

                $turtle .= " " . $pStr;

                $oCount = 0;
                foreach ($objects as $object) {
                    if ($oCount)
                        $turtle .= ",";
                    $turtle .= " " . $this->serialiseObject($object);
                    $oCount++;
                }
                $pCount++;
            }

            $turtle .= " .\n\n";
        }

        return $this->serialisePrefixes() . "\n" . $turtle;
    }
}

EasyRdf_Format::registerSerialiser('n3', 'EasyRdf_Serialiser_Turtle');
EasyRdf_Format::registerSerialiser('turtle', 'EasyRdf_Serialiser_Turtle');
