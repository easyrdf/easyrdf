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
use EasyRdf\Exception;
use EasyRdf\Graph;
use EasyRdf\Serialiser;

/**
 * Class to serialise an EasyRdf\Graph to N-Triples
 * with no external dependencies.
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009-2020 Nicholas J Humfrey
 * @license    https://www.opensource.org/licenses/bsd-license.php
 */
class Ntriples extends Serialiser
{

    /**
     * Characters forbidden in n-triples literals according to
     * https://www.w3.org/TR/n-triples/#grammar-production-IRIREF
     *
     * @var string[]
     */
    private static $iriEscapeMap = array(
        "<" => "\\u003C",
        ">" => "\\u003E",
        '"' => "\\u0022",
        "{" => "\\u007B",
        "}" => "\\u007D",
        "|" => "\\u007C",
        "^" => "\\u005E",
        "`" => "\\u0060",
        "\\" => "\\u005C",
        "\x00" => "\\u0030",
        "\x01" => "\\u0031",
        "\x02" => "\\u0032",
        "\x03" => "\\u0033",
        "\x04" => "\\u0034",
        "\x05" => "\\u0035",
        "\x06" => "\\u0036",
        "\x07" => "\\u0037",
        "\x08" => "\\u0038",
        "\x09" => "\\u0039",
        "\x0A" => "\\u0031",
        "\x0B" => "\\u0031",
        "\x0C" => "\\u0031",
        "\x0D" => "\\u0031",
        "\x0E" => "\\u0031",
        "\x0F" => "\\u0031",
        "\x10" => "\\u0031",
        "\x11" => "\\u0031",
        "\x12" => "\\u0031",
        "\x13" => "\\u0031",
        "\x14" => "\\u0032",
        "\x15" => "\\u0032",
        "\x16" => "\\u0032",
        "\x17" => "\\u0032",
        "\x18" => "\\u0032",
        "\x19" => "\\u0032",
        "\x1A" => "\\u0032",
        "\x1B" => "\\u0032",
        "\x1C" => "\\u0032",
        "\x1D" => "\\u0032",
        "\x1E" => "\\u0033",
        "\x1F" => "\\u0033",
        "\x20" => "\\u0033"
    );

    /**
     * Characters forbidden in n-triples literals according to
     * https://www.w3.org/TR/n-triples/#grammar-production-STRING_LITERAL_QUOTE
     * @var string[]
     */
    private static $literalEscapeMap = array(
        "\n" => '\\n',
        "\r" => '\\r',
        '"' => '\\"',
        '\\' => '\\\\'
    );

    public static function escapeLiteral($str)
    {
        return strtr($str, self::$literalEscapeMap);
    }

    public static function escapeIri($str)
    {
        return strtr($str, self::$iriEscapeMap);
    }

    /**
     * @ignore
     */
    protected function serialiseResource($res)
    {
        $escaped = self::escapeIri($res);
        if (substr($res, 0, 2) == '_:') {
            return $escaped;
        } else {
            return "<$escaped>";
        }
    }

    /**
     * Serialise an RDF value into N-Triples
     *
     * The value can either be an array in RDF/PHP form, or
     * an EasyRdf\Literal or EasyRdf\Resource object.
     *
     * @param array|object  $value   An associative array or an object
     *
     * @throws Exception
     *
     * @return string The RDF value serialised to N-Triples
     */
    public function serialiseValue($value)
    {
        if (is_object($value)) {
            $value = $value->toRdfPhp();
        }

        if ($value['type'] == 'uri' or $value['type'] == 'bnode') {
            return $this->serialiseResource($value['value']);
        } elseif ($value['type'] == 'literal') {
            $escaped = self::escapeLiteral($value['value']);
            if (isset($value['lang'])) {
                $lang = $value['lang'];
                return '"' . $escaped . '"' . '@' . $lang;
            } elseif (isset($value['datatype'])) {
                $datatype = self::escapeIri($value['datatype']);
                return '"' . $escaped . '"' . "^^<$datatype>";
            } else {
                return '"' . $escaped . '"';
            }
        } else {
            throw new Exception(
                "Unable to serialise object of type '" . $value['type'] . "' to ntriples: "
            );
        }
    }

    /**
     * Serialise an EasyRdf\Graph into N-Triples
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

        if ($format == 'ntriples') {
            $nt = '';
            foreach ($graph->toRdfPhp() as $resource => $properties) {
                foreach ($properties as $property => $values) {
                    foreach ($values as $value) {
                        $nt .= $this->serialiseResource($resource) . " ";
                        $nt .= "<" . self::escapeIri($property) . "> ";
                        $nt .= $this->serialiseValue($value) . " .\n";
                    }
                }
            }
            return $nt;
        } else {
            throw new Exception(
                __CLASS__ . " does not support: $format"
            );
        }
    }
}
