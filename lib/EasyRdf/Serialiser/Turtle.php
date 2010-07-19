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
class EasyRdf_Serialiser_Turtle extends EasyRdf_Serialiser
{
    protected $_prefixes = array();

    protected function addPrefix($qname)
    {
        list ($prefix) = explode(':', $qname);
        $this->_prefixes[$prefix] = true;
    }

    protected function serialiseResource($resource)
    {
        $uri = $resource->getUri();
        if ($resource->isBNode()) {
            return $uri;
        } else {
            $short = EasyRdf_Namespace::shorten($uri);
            if ($short) {
                return $short;
            } else {
                $uri = str_replace('>', '\\>', $uri);
                return "<$uri>";
            }
        }
    }

    protected function serialiseObject($object)
    {
        if (get_class($object) == 'EasyRdf_Resource') {
            return $this->serialiseResource($object);
        } else if (get_class($object) == 'EasyRdf_Literal') {
            $value = $object->getValue();
            $value = str_replace('\\', '\\\\', $value);
            $value = str_replace('\n', '\\n', $value);
            $value = str_replace('\r', '\\r', $value);
            $value = str_replace('\t', '\\t', $value);
            $value = str_replace('"', '\\"', $value);

            if ($object->getLang()) {
                return '"' . $value . '"' . '@' . $object->getLang();
            } else if ($object->getDatatype()) {
                $datatype = $object->getDatatype();
                if ($datatype == 'xsd:integer') {
                    return sprintf('%d^^%s', $value, $datatype);
                } else if ($datatype == 'xsd:decimal') {
                    return sprintf('%g^^%s', $value, $datatype);
                } else if ($datatype == 'xsd:double') {
                    return sprintf('%e^^%s', $value, $datatype);
                } else if ($datatype == 'xsd:boolean') {
                    return sprintf(
                        '%s^^%s',
                        $value ? 'true' : 'false',
                        $datatype
                    );
                } else {
                    return sprintf('"%s"^^%s', $value, $datatype);
                }
            } else {
                return '"' . $value . '"';
            }
        } else {
            throw new EasyRdf_Exception(
                "Unable to serialise object to turtle: $object"
            );
        }
    }

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
     * @param string $graph An EasyRdf_Graph object.
     * @return string The RDF in Turtle format.
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
        foreach ($graph->resources() as $subject) {
            $properties = $subject->properties();
            if (count($properties) == 0)
                continue;

            $turtle .= $this->serialiseResource($subject);

            if (count($properties) > 1) {
                $turtle .= "\n   ";
            }

            $pCount = 0;
            foreach ($properties as $property) {
                $this->addPrefix($property);
                $pStr = $property == 'rdf:type' ? 'a' : $property;

                if ($pCount) {
                    $turtle .= " ;\n   ";
                }

                $turtle .= " " . $pStr;
                $objects = $subject->all($property);

                $oCount = 0;
                foreach ($objects as $object) {
                    # FIXME: remove this when types are stored as Resources
                    if ($pStr == 'a') {
                        $uri = EasyRdf_Namespace::expand($object);
                        $object = new EasyRdf_Resource($uri);
                    }
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
