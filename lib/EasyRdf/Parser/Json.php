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
 * A pure-php class to parse RDF/JSON with no dependancies.
 *
 * http://n2.talis.com/wiki/RDF_JSON_Specification
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009-2012 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */
class EasyRdf_Parser_Json extends EasyRdf_Parser_RdfPhp
{
    private $_jsonLastErrorExists = false;

    /**
     * Constructor
     *
     * @return object EasyRdf_Parser_Json
     */
    public function __construct()
    {
        $this->_jsonLastErrorExists = function_exists('json_last_error');
    }

    public function _jsonLastErrorString()
    {
        if ($this->_jsonLastErrorExists) {
            switch (json_last_error()) {
                case JSON_ERROR_NONE:
                   return null;
                case JSON_ERROR_DEPTH:
                   return "JSON Parse error: the maximum stack depth has been exceeded";
                case JSON_ERROR_STATE_MISMATCH:
                   return "JSON Parse error: invalid or malformed JSON";
                case JSON_ERROR_CTRL_CHAR:
                   return "JSON Parse error: control character error, possibly incorrectly encoded";
                case JSON_ERROR_SYNTAX:
                   return "JSON Parse syntax error";
                case JSON_ERROR_UTF8:
                   return "JSON Parse error: malformed UTF-8 characters, possibly incorrectly encoded";
                default:
                   return "JSON Parse error: unknown";
            }
        } else {
           return "JSON Parse error";
        }
    }

    /** Parse the triple-centric JSON format, as output by libraptor
     *
     * http://librdf.org/raptor/api/serializer-json.html
     *
     * @ignore
     */
    protected function _parseJsonTriples($graph, $data, $baseUri)
    {
        foreach ($data['triples'] as $triple) {
            if ($triple['subject']['type'] == 'bnode') {
                $subject = $this->remapBnode($graph, $triple['subject']['value']);
            } else {
                $subject = $triple['subject']['value'];
            }

            $predicate = $triple['predicate']['value'];

            if ($triple['object']['type'] == 'bnode') {
                $object = array(
                    'type' => 'bnode',
                    'value' => $this->remapBnode($graph, $triple['object']['value'])
                );
            } else {
                $object = $triple['object'];
            }

            $graph->add($subject, $predicate, $object);
        }

        return true;
    }

    /**
      * Parse RDF/JSON into an EasyRdf_Graph
      *
      * @param object EasyRdf_Graph $graph   the graph to load the data into
      * @param string               $data    the RDF document data
      * @param string               $format  the format of the input data
      * @param string               $baseUri the base URI of the data being parsed
      * @return boolean             true if parsing was successful
      */
    public function parse($graph, $data, $format, $baseUri)
    {
        parent::checkParseParams($graph, $data, $format, $baseUri);

        if ($format != 'json') {
            throw new EasyRdf_Exception(
                "EasyRdf_Parser_Json does not support: $format"
            );
        }

        // Reset the bnode mapping
        $this->resetBnodeMap();

        $decoded = @json_decode(strval($data), true);
        if ($decoded === null) {
            throw new EasyRdf_Exception(
                $this->_jsonLastErrorString()
            );
        }

        if (array_key_exists('triples', $decoded)) {
            return $this->_parseJsonTriples($graph, $decoded, $baseUri);
        } else {
            return parent::parse($graph, $decoded, 'php', $baseUri);
        }
    }
}

EasyRdf_Format::registerParser('json', 'EasyRdf_Parser_Json');
