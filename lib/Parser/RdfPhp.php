<?php
namespace EasyRdf\Parser;

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
use EasyRdf\Graph;
use EasyRdf\Parser;

/**
 * Class to parse RDF with no external dependencies.
 *
 * https://www.easyrdf.org/docs/rdf-formats-php
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009-2020 Nicholas J Humfrey
 * @license    https://www.opensource.org/licenses/bsd-license.php
 */
class RdfPhp extends Parser
{
    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Parse RDF/PHP into an EasyRdf\Graph
     *
     * @param Graph  $graph   the graph to load the data into
     * @param array[] $data   the RDF document data
     * @param string $format  the format of the input data
     * @param string $baseUri the base URI of the data being parsed
     *
     * @throws \EasyRdf\Exception
     * @return integer        The number of triples added to the graph
     */
    public function parse($graph, $data, $format, $baseUri)
    {
        $this->checkParseParams($graph, $data, $format, $baseUri);

        if ($format != 'php') {
            throw new \EasyRdf\Exception(
                "EasyRdf\\Parser\\RdfPhp does not support: $format"
            );
        }

        if (!is_array($data)) {
            throw new Exception('expected array, got '.gettype($data));
        }

        foreach ($data as $orig_subject => $properties) {
            if (is_int($orig_subject)) {
                throw new Exception('expected array indexed by IRIs, got list');
            }

            if (substr($orig_subject, 0, 2) === '_:') {
                $subject = $this->remapBnode($orig_subject);
            } elseif (preg_match('/^\w+$/', $orig_subject)) {
                # Cope with invalid RDF/JSON serialisations that
                # put the node name in, without the _: prefix
                # (such as net.fortytwo.sesametools.rdfjson)
                $subject = $this->remapBnode($orig_subject);
            } else {
                $subject = $orig_subject;
            }

            if (!is_array($properties)) {
                throw new Exception("expected array as value of '{$orig_subject}' key, got ".gettype($properties));
            }

            foreach ($properties as $property => $objects) {
                if (is_int($property)) {
                    throw new Exception("expected 'array indexed by IRIs' as value of '{$orig_subject}' key, got list");
                }

                if (!is_array($objects)) {
                    throw new Exception(
                        "expected list of objects as value of '{$orig_subject}' -> '{$property}' node, got ".
                        gettype($objects)
                    );
                }

                foreach ($objects as $i => $object) {
                    if (!is_array($object) or !isset($object['type']) or !isset($object['value'])) {
                        throw new Exception(
                            "expected array with 'type' and 'value' keys as value of ".
                            "'{$orig_subject}' -> '{$property}' -> '{$i}' node"
                        );
                    }

                    if ($object['type'] === 'bnode') {
                        $object['value'] = $this->remapBnode($object['value']);
                    }
                    $this->addTriple($subject, $property, $object);
                }
            }
        }

        return $this->tripleCount;
    }
}
