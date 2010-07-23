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
 * Class to parse RDF with no external dependancies.
 *
 * http://n2.talis.com/wiki/RDF_PHP_Specification
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009-2010 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */
class EasyRdf_Parser_RdfPhp extends EasyRdf_Parser
{
    private $_bnodeMap = array();

    /** A constant for the RDF Type property URI */
    const RDF_TYPE_URI = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type';

    /**
     * Constructor
     *
     * @return object EasyRdf_Parser_RdfPhp
     */
    public function __construct()
    {
        $this->_bnodeMap = array();
    }

    /**
     * Get the type of a resource from some RDF/PHP
     * @ignore
     */
    protected function getResourceType($data, $uri)
    {
        if (array_key_exists($uri, $data)) {
            $subj = $data[$uri];
            if (array_key_exists(self::RDF_TYPE_URI, $subj)) {
                $types = array();
                foreach ($subj[self::RDF_TYPE_URI] as $type) {
                    if ($type['type'] == 'uri') {
                        $type = EasyRdf_Namespace::shorten($type['value']);
                        if ($type) {
                            array_push($types, $type);
                        }
                    }
                }
                if (count($types) > 0) {
                    return $types;
                }
            }
        }
        return null;
    }
    
    /**
     * @ignore
     */
    protected function addProperty($graph, $data, $res, $property, $objects)
    {
        $property = EasyRdf_Namespace::shorten($property);
        if (!isset($property))
            return;
        
        foreach ($objects as $object) {
            if ($property == 'rdf:type') {
                # Type has already been set
            } else if ($object['type'] == 'literal') {
                $res->add($property, new EasyRdf_Literal($object));
            } else if ($object['type'] == 'uri') {
                $type = $this->getResourceType(
                    $data, $object['value']
                );
                $objres = $graph->resource($object['value'], $type);
                $res->add($property, $objres);
            } else if ($object['type'] == 'bnode') {
                $objuri = $object['value'];
                if (!isset($this->_bnodeMap[$objuri])) {
                    $type = $this->getResourceType(
                        $data, $object['value']
                    );
                    $this->_bnodeMap[$objuri] = $graph->newBNode($type);
                }
                $res->add($property, $this->_bnodeMap[$objuri]);
            } else {
                throw new EasyRdf_Exception(
                    "Document contains unsupported type: " . $object['type']
                );
            }
        }
    }

    /**
      * Parse RDF/PHP into an EasyRdf_Graph
      *
      * @param string $graph    the graph to load the data into
      * @param string $data     the RDF document data
      * @param string $format   the format of the input data
      * @param string $baseUri  the base URI of the data being parsed
      * @return boolean         true if parsing was successful
      */
    public function parse($graph, $data, $format, $baseUri)
    {
        parent::checkParseParams($graph, $data, $format, $baseUri);

        if ($format != 'php') {
            throw new EasyRdf_Exception(
                "EasyRdf_Parser_RdfPhp does not support: $format"
            );
        }

        # Convert into an object graph
        foreach ($data as $subject => $touple) {
            $type = $this->getResourceType($data, $subject);

            # Is this a bnode?
            if (substr($subject, 0, 2) == '_:') {
                if (!isset($this->_bnodeMap[$subject])) {
                    $this->_bnodeMap[$subject] = $graph->newBNode($type);
                }
                $res = $this->_bnodeMap[$subject];
            } else {
                $res = $graph->resource($subject, $type);
            }

            foreach ($touple as $property => $objects) {
                $this->addProperty($graph, $data, $res, $property, $objects);
            }
        }

        return true;
    }
}

EasyRdf_Format::registerParser('php', 'EasyRdf_Parser_RdfPhp');
