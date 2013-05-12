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
 * Class to serialise an EasyRdf_Graph to Turtle
 * with no external dependancies.
 *
 * http://www.dajobe.org/2004/01/turtle
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009-2013 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */
class EasyRdf_Serialiser_Turtle extends EasyRdf_Serialiser
{
    private $outputtedBnodes = array();

    /**
     * @param  string $resource_iri
     * @return string
     */
    public static function encodeResourceIRI($resource_iri)
    {
        $escaped_iri = str_replace('>', '\\>', $resource_iri);
        return "<{$escaped_iri}>";
    }

    /**
     * @param  EasyRdf_Resource $resource
     * @return string
     */
    public function serialiseResource($resource)
    {
        if ($resource->isBNode()) {
            return $resource->getUri();
        } else {
            $short = $resource->shorten();
            if ($short) {
                $this->addPrefix($short);
                return $short;
            } else {
                return self::encodeResourceIRI($resource->getUri());
            }
        }
    }

    /**
     * @param  EasyRdf_Literal $literal
     * @return string
     */
    public function serialiseLiteral($literal)
    {
        $value = strval($literal);
        $quoted = self::quotedString($value);

        if ($datatype = $literal->getDatatypeUri()) {
            $short = EasyRdf_Namespace::shorten($datatype, true);
            if ($short) {
                $this->addPrefix($short);
                if ($short == 'xsd:integer') {
                    return sprintf('%d', $value);
                } elseif ($short == 'xsd:decimal') {
                    return sprintf('%g', $value);
                } elseif ($short == 'xsd:double') {
                    return sprintf('%e', $value);
                } elseif ($short == 'xsd:boolean') {
                    return sprintf('%s', $value ? 'true' : 'false');
                } else {
                    return sprintf('%s^^%s', $quoted, $short);
                }
            } else {
                $encoded_type_iri = self::encodeResourceIRI($datatype);
                return sprintf('%s^^%s', $quoted, $encoded_type_iri);
            }
        } elseif ($lang = $literal->getLang()) {
            return $quoted . '@' . $lang;
        } else {
            return $quoted;
        }
    }

    /**
     * @ignore
     */
    protected function serialiseCollection($node, $indent)
    {
        $turtle = '(';
        $count = 0;
        while ($node) {
            if ($id = $node->getBNodeId()) {
                $this->outputtedBnodes[$id] = true;
            }

            $value = $node->get('rdf:first');
            $node = $node->get('rdf:rest');
            if ($node and $node->hasProperty('rdf:first')) {
                $count++;
            }

            if ($value !== null) {
                $serialised = $this->serialiseObject($value);
                if ($count) {
                    $turtle .= "\n$indent  $serialised";
                } else {
                    $turtle .= " ".$serialised;
                }
            }
        }
        if ($count) {
            $turtle .= "\n$indent)";
        } else {
            $turtle .= " )";
        }
        return $turtle;
    }

    /**
     * @param  string $value
     * @return string
     */
    public static function quotedString($value)
    {
        if (preg_match("/[\t\n\r]/", $value)) {
            $escaped = str_replace(array('\\', '"""'), array('\\\\', '\\"""'), $value);
            return '"""'.$escaped.'"""';
        } else {
            $escaped = str_replace(array('\\', '"'), array('\\\\', '\\"'), $value);
            return '"'.$escaped.'"';
        }
    }

    /**
     * @param  EasyRdf_Resource|EasyRdf_Literal $object
     * @return string
     */
    public function serialiseObject($object)
    {
        if ($object instanceof EasyRdf_Resource) {
            return $this->serialiseResource($object);
        } elseif ($object instanceof EasyRdf_Literal) {
            return $this->serialiseLiteral($object);
        }
    }

    /**
     * Protected method to serialise the properties of a resource
     * @ignore
     */
    protected function serialiseProperties($res, $depth = 1)
    {
        $properties = $res->propertyUris();
        $indent = str_repeat(' ', ($depth*2)-1);

        $turtle = '';
        if (count($properties) > 1) {
            $turtle .= "\n$indent";
        }

        $pCount = 0;
        foreach ($properties as $property) {
            $short = EasyRdf_Namespace::shorten($property, true);
            if ($short) {
                if ($short == 'rdf:type') {
                    $pStr = 'a';
                } else {
                    $this->addPrefix($short);
                    $pStr = $short;
                }
            } else {
                $pStr = self::encodeResourceIRI($property);
            }

            if ($pCount) {
                $turtle .= " ;\n$indent";
            }

            $turtle .= ' ' . $pStr;

            $oCount = 0;
            foreach ($res->all("<$property>") as $object) {
                if ($oCount) {
                    $turtle .= ',';
                }

                if ($object instanceof EasyRdf_Collection) {
                    $turtle .= ' ' . $this->serialiseCollection($object, $indent);
                } elseif ($object instanceof EasyRdf_Resource and $object->isBNode()) {
                    $id = $object->getBNodeId();
                    $rpcount = $this->reversePropertyCount($object);
                    if ($rpcount <= 1 and !isset($this->outputtedBnodes[$id])) {
                        // Nested unlabelled Blank Node
                        $this->outputtedBnodes[$id] = true;
                        $turtle .= ' [';
                        $turtle .= $this->serialiseProperties($object, $depth+1);
                        $turtle .= ' ]';
                    } else {
                        // Multiple properties pointing to this blank node
                        $turtle .= ' ' . $this->serialiseObject($object);
                    }
                } else {
                    $turtle .= ' ' . $this->serialiseObject($object);
                }
                $oCount++;
            }
            $pCount++;
        }

        if ($depth == 1) {
            $turtle .= " .";
            if ($pCount > 1) {
                $turtle .= "\n";
            }
        } elseif ($pCount > 1) {
            $turtle .= "\n" . str_repeat(' ', (($depth-1)*2)-1);
        }

        return $turtle;
    }

    /**
     * @ignore
     */
    protected function serialisePrefixes()
    {
        $turtle = '';
        foreach ($this->prefixes as $prefix => $count) {
            $url = EasyRdf_Namespace::get($prefix);
            $turtle .= "@prefix $prefix: <$url> .\n";
        }
        return $turtle;
    }

    /**
     * @ignore
     */
    protected function serialiseSubjects($graph, $filterType)
    {
        $turtle = '';
        foreach ($graph->resources() as $resource) {
            /** @var $resource EasyRdf_Resource */
            // If the resource has no properties - don't serialise it
            $properties = $resource->propertyUris();
            if (count($properties) == 0) {
                continue;
            }

            // Is this node of the right type?
            $thisType = $resource->isBNode() ? 'bnode' : 'uri';
            if ($thisType != $filterType) {
                continue;
            }

            if ($thisType == 'bnode') {
                $id = $resource->getBNodeId();
                if (isset($this->outputtedBnodes[$id])) {
                    // Already been serialised
                    continue;
                } else {
                    $this->outputtedBnodes[$id] = true;
                    $rpcount = $this->reversePropertyCount($resource);
                    if ($rpcount == 0) {
                        $turtle .= '[]';
                    } else {
                        $turtle .= $this->serialiseResource($resource);
                    }
                }
            } else {
                $turtle .= $this->serialiseResource($resource);
            }

            $turtle .= $this->serialiseProperties($resource);
            $turtle .= "\n";
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

        $this->prefixes = array();
        $this->outputtedBnodes = array();

        $turtle = '';
        $turtle .= $this->serialiseSubjects($graph, 'uri');
        $turtle .= $this->serialiseSubjects($graph, 'bnode');

        if (count($this->prefixes)) {
            return $this->serialisePrefixes() . "\n" . $turtle;
        } else {
            return $turtle;
        }
    }
}
