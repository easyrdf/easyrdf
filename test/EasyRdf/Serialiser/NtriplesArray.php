<?php
namespace EasyRdf\Serialiser;

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009-2016 Nicholas J Humfrey.  All rights reserved.
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
 * @copyright  Copyright (c) 2009-2016 Nicholas J Humfrey
 * @license    https://www.opensource.org/licenses/bsd-license.php
 */
use EasyRdf\Exception;
use EasyRdf\Format;
use EasyRdf\Graph;

/**
 * Class to serialise an EasyRdf\Graph to an array of triples.
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009-2016 Nicholas J Humfrey
 * @license    https://www.opensource.org/licenses/bsd-license.php
 */
class NtriplesArray extends Ntriples
{

    /**
     * Sort an array of triples into a consistent order
     *
     * @ignore
     */
    protected function compareTriples($a, $b)
    {
        if ($a['s'] != $b['s']) {
            return strcmp($a['s'], $b['s']);
        } elseif ($a['p'] != $b['p']) {
            return strcmp($a['p'], $b['p']);
        } elseif ($a['o'] != $b['o']) {
            return strcmp($a['o'], $b['o']);
        } else {
            return 0;
        }
    }


    /**
     * Serialise an EasyRdf\Graph into an array of N-Triples objects
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

        $triples = array();
        foreach ($graph->toRdfPhp() as $resource => $properties) {
            foreach ($properties as $property => $values) {
                foreach ($values as $value) {
                    array_push(
                        $triples,
                        array(
                            's' => $this->serialiseResource($resource),
                            'p' => "<" . $this->escapeString($property) . ">",
                            'o' => $this->serialiseValue($value)
                        )
                    );
                }
            }
        }

        // Sort the triples into a consistent order
        usort($triples, array($this, 'compareTriples'));

        return $triples;
    }
}

Format::register('ntriples-array', 'PHP Array of Triples');
Format::registerSerialiser('ntriples-array', 'EasyRdf\Serialiser\NtriplesArray');
