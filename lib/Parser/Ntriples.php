<?php
namespace EasyRdf\Parser;

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009-2013 Nicholas J Humfrey.  All rights reserved.
 * Copyright (c) 2020 Austrian Centre for Digital Humanities.
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

use EasyRdf\Graph;
use EasyRdf\Parser;

/**
 * A pure-php class to parse N-Triples with no dependancies.
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009-2013 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */
class Ntriples extends Parser
{
    /**
     * Decodes an encoded N-Triples string. Any \-escape sequences are substituted
     * with their decoded value.
     *
     * @param  string $str An encoded N-Triples string.
     *
     * @return string The unencoded string.
     **/
    protected function unescapeString($str)
    {
        if (strpos($str, '\\') === false) {
            return $str;
        }

        // https://www.w3.org/TR/n-triples/#n-triples-grammar
        $mappings = array(
            '\\b' => chr(8),
            '\\t' => "\t",
            '\\n' => "\n",
            '\\f' => chr(12),
            '\\r' => "\r",
            '\\"' => '"',
            "\\'" => "'",
            '\\\\' => "\\"
        );
        $str = str_replace(array_keys($mappings), array_values($mappings), $str);

        if (stripos($str, '\\u') === false) {
            return $str;
        }

        while (
            preg_match('/\\\\U([0-9A-F]{8})/', $str, $matches) ||
            preg_match('/\\\\u([0-9A-F]{4})/', $str, $matches)
        ) {
            //TODO - lines 87-105 can be replaced with mb_chr() in PHP >=7.2
            $no = hexdec($matches[1]);
            if ($no < 128) {                // 0x80
                $char = chr($no);
            } elseif ($no < 2048) {         // 0x800
                $char = chr(($no >> 6) + 192) .
                    chr(($no & 63) + 128);
            } elseif ($no < 65536) {        // 0x10000
                $char = chr(($no >> 12) + 224) .
                    chr((($no >> 6) & 63) + 128) .
                    chr(($no & 63) + 128);
            } elseif ($no < 2097152) {      // 0x200000
                $char = chr(($no >> 18) + 240) .
                    chr((($no >> 12) & 63) + 128) .
                    chr((($no >> 6) & 63) + 128) .
                    chr(($no & 63) + 128);
            } else {
                # FIXME: throw an exception instead?
                $char = '';
            }
            $str = str_replace($matches[0], $char, $str);
        }
        return $str;
    }

    /**
     * @ignore
     */
    protected function parseNtriplesSubject($sub, $lineNum)
    {
        if (preg_match('/<([^<>]+)>/', $sub, $matches)) {
            return $this->unescapeString($matches[1]);
        } elseif (preg_match('/_:([A-Za-z0-9]*)/', $sub, $matches)) {
            if (empty($matches[1])) {
                return $this->graph->newBNodeId();
            } else {
                $nodeid = $this->unescapeString($matches[1]);
                return $this->remapBnode($nodeid);
            }
        } else {
            throw new Exception(
                "Failed to parse subject: $sub",
                $lineNum
            );
        }
    }

    /**
     * Parses the RDF triple's object.
     *
     * Extracted to a separate method only for better code readability of the
     * parse() method.
     *
     * For performance reasons (avoiding unnecessary memory copying) takes the
     * $matches parameter by reference and assumes its values follow the regex
     * used in the parse() method (which ends up with a pretty ugly API - see
     * the $matches parameter description).
     *
     * @param array $matches an array providing the RDF triple object value
     *   in the fourth element, datatype in sixth element and lang tag in
     *   seventh element
     * @param type $lineNum
     * @return array
     * @throws Exception
     * @ignore
     */
    private function parseNtriplesObject(&$matches, $lineNum)
    {
        if (!is_array($matches) || !isset($matches[3])) {
            throw new Exception('Invalid $matches parameter provided');
        }
        switch (substr($matches[3], 0, 1)) {
            case '"':
                $ret = array(
                    'type' => 'literal',
                    'value' => $this->unescapeString(substr($matches[3], 1, -1))
                );
                if (count($matches) === 7) {
                    $ret['lang'] = substr($matches[6], 1);
                } elseif (count($matches) === 6) {
                    $ret['datatype'] = $this->unescapeString(substr($matches[5], 3, -1));
                }
                return $ret;
            case '_':
                if (strlen($matches[3]) === 2) {
                    // empty bnode id is not in line with https://www.w3.org/TR/n-triples/#n-triples-grammar
                    // but examples exist in tests so let's leave it
                    $bnode = $this->graph->newBNodeId();
                } else {
                    $bnode = $this->remapBnode(substr($matches[3], 2));
                }
                return array(
                    'type' => 'bnode',
                    'value' => $bnode
                );
            case '<':
                return array(
                    'type' => 'uri',
                    'value' => $this->unescapeString(substr($matches[3], 1, -1))
                );
            default:
                throw new Exception("Failed to parse triple's object value: " . $matches[3], $lineNum);
        }
    }

    /**
     * Parse an N-Triples document into an EasyRdf\Graph
     *
     * @param Graph  $graph   the graph to load the data into
     * @param string $data    the RDF document data
     * @param string $format  the format of the input data
     * @param string $baseUri the base URI of the data being parsed
     *
     * @throws Exception
     * @throws \EasyRdf\Exception
     * @return integer             The number of triples added to the graph
     */
    public function parse($graph, $data, $format, $baseUri)
    {
        parent::checkParseParams($graph, $data, $format, $baseUri);

        if ($format != 'ntriples') {
            throw new \EasyRdf\Exception(
                "EasyRdf\\Parser\\Ntriples does not support: $format"
            );
        }

        $lines = preg_split('/\x0D?\x0A/', strval($data));
        foreach ($lines as $index => $line) {
            $lineNum = $index + 1;
            if (preg_match('/^\s*#/', $line)) {
                # Comment
                continue;
            } elseif (preg_match('/^\s*(.+?)\s+<([^<>]+?)>\s+(<[^>]+>|_:[^\s]*|"(\\\\"|[^"])*")(\\^\\^<[^>]+>)?(@[-a-zA-Z0-9]+)?\s*\./', $line, $matches)) {
                $this->addTriple(
                    $this->parseNtriplesSubject($matches[1], $lineNum),
                    $this->unescapeString($matches[2]),
                    $this->parseNtriplesObject($matches, $lineNum)
                );
            } elseif (preg_match('/^\s*$/', $line)) {
                # Blank line
                continue;
            } else {
                throw new Exception(
                    "Failed to parse statement",
                    $lineNum
                );
            }
        }

        return $this->tripleCount;
    }
}
