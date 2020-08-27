<?php
namespace EasyRdf\Serialiser;

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009-2020 Nicholas J Humfrey.  All rights reserved.
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
 * @copyright  Copyright (c) 2009-2020 Nicholas J Humfrey
 * @license    https://www.opensource.org/licenses/bsd-license.php
 */
use EasyRdf\Collection;
use EasyRdf\Exception;
use EasyRdf\Graph;
use EasyRdf\Literal;
use EasyRdf\RdfNamespace;
use EasyRdf\Resource;
use EasyRdf\Serialiser;

/**
 * Class to serialise an EasyRdf\Graph to Turtle
 * with no external dependencies.
 *
 * http://www.w3.org/TR/turtle/
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009-2020 Nicholas J Humfrey
 * @license    https://www.opensource.org/licenses/bsd-license.php
 */
class Turtle extends Serialiser
{
    private $outputtedBnodes = array();

    /**
     * Given a IRI string, escape and enclose in angle brackets.
     *
     * @param  string $resourceIri
     *
     * @return string
     */
    public static function escapeIri($resourceIri)
    {
        $escapedIri = str_replace('>', '\\>', $resourceIri);
        return "<$escapedIri>";
    }

    /**
     * Given a string, enclose in quotes and escape any quotes in the string.
     * Strings containing tabs, linefeeds or carriage returns will be
     * enclosed in three double quotes (""").
     *
     * @param  string $value
     *
     * @return string
     */
    public static function quotedString($value)
    {
        if (preg_match('/[\t\n\r]/', $value)) {
            $escaped = str_replace(array('\\', '"""'), array('\\\\', '\\"""'), $value);

            // Check if the last character is a trailing double quote, if so, escape it.
            $pos = strrpos($escaped, '"');

            if ($pos !== false && $pos + 1 == strlen($escaped)) {
                $escaped = substr($escaped, 0, -1);

                $escaped .= '\"';
            }

            return '"""'.$escaped.'"""';
        } else {
            $escaped = str_replace(array('\\', '"'), array('\\\\', '\\"'), $value);
            return '"'.$escaped.'"';
        }
    }

    /**
     * Given a an EasyRdf\Resource or URI, convert it into a string, suitable to
     * be written to a Turtle document. URIs will be shortened into CURIES
     * where possible.
     *
     * @param  Resource|string $resource The resource to convert to a Turtle string
     * @param  boolean $createNamespace  If true, a new namespace may be created
     *
     * @return string
     */
    public function serialiseResource($resource, $createNamespace = false)
    {
        if (is_object($resource)) {
            if ($resource->isBNode()) {
                return $resource->getUri();
            }

            $resource = $resource->getUri();
        }

        $short = RdfNamespace::shorten($resource, $createNamespace);

        if ($short) {
            $this->addPrefix($short);
            return $short;
        }

        return self::escapeIri($resource);
    }

    /**
     * Given an EasyRdf\Literal object, convert it into a string, suitable to
     * be written to a Turtle document. Supports multiline literals and literals with
     * datatypes or languages.
     *
     * @param  Literal $literal
     *
     * @return string
     */
    public function serialiseLiteral($literal)
    {
        $value = strval($literal);
        $quoted = self::quotedString($value);

        if ($datatype = $literal->getDatatypeUri()) {
            if ($datatype == 'http://www.w3.org/2001/XMLSchema#integer') {
                return sprintf('%d', $value);
            } elseif ($datatype == 'http://www.w3.org/2001/XMLSchema#decimal') {
                return sprintf('%s', $value);
            } elseif ($datatype == 'http://www.w3.org/2001/XMLSchema#double') {
                return sprintf('%e', $value);
            } elseif ($datatype == 'http://www.w3.org/2001/XMLSchema#boolean') {
                return sprintf('%s', $value);
            } else {
                $escaped = $this->serialiseResource($datatype, true);
                return sprintf('%s^^%s', $quoted, $escaped);
            }
        } elseif ($lang = $literal->getLang()) {
            return $quoted . '@' . $lang;
        } else {
            return $quoted;
        }
    }

    /**
     * Convert an EasyRdf object into a string suitable to
     * be written to a Turtle document.
     *
     * @param  Resource|Literal $object
     *
     * @throws \InvalidArgumentException
     * @return string
     */
    public function serialiseObject($object)
    {
        if ($object instanceof Resource) {
            return $this->serialiseResource($object);
        } elseif ($object instanceof Literal) {
            return $this->serialiseLiteral($object);
        } else {
            throw new \InvalidArgumentException(
                "serialiseObject() requires \$object to be ".
                'of type EasyRdf\Resource or EasyRdf\Literal'
            );
        }
    }


    /**
     * Protected method to serialise a RDF collection
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
            if ($property === 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type') {
                $pStr = 'a';
            } else {
                $pStr = $this->serialiseResource($property, true);
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

                if ($object instanceof Collection) {
                    $turtle .= ' ' . $this->serialiseCollection($object, $indent);
                } elseif ($object instanceof Resource and $object->isBNode()) {
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
            $url = RdfNamespace::get($prefix);
            $turtle .= "@prefix $prefix: <$url> .\n";
        }
        return $turtle;
    }

    /**
     * @ignore
     */
    protected function serialiseSubjects(Graph $graph, $filterType)
    {
        $turtle = '';
        foreach ($graph->resources() as $resource) {
            /** @var $resource Resource */
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
                }

                $this->outputtedBnodes[$id] = true;
                $rpcount = $this->reversePropertyCount($resource);

                if ($rpcount == 0) {
                    $turtle .= '[]';
                } else {
                    $turtle .= $this->serialiseResource($resource);
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
     * Serialise an EasyRdf\Graph to Turtle.
     *
     * @param Graph  $graph  An EasyRdf\Graph object.
     * @param string $format The name of the format to convert to.
     * @param array  $options
     *
     * @return string The RDF in the new desired format.
     * @throws Exception
     */
    public function serialise(Graph $graph, $format, array $options = array())
    {
        parent::checkSerialiseParams($format);

        if ($format != 'turtle' and $format != 'n3') {
            throw new Exception(
                "EasyRdf\\Serialiser\\Turtle does not support: {$format}"
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
