<?php
namespace EasyRdf\Sparql;

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
use EasyRdf\Literal;
use EasyRdf\Resource;

/**
 * Class for returned for SPARQL SELECT and ASK query responses.
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009-2020 Nicholas J Humfrey
 * @license    https://www.opensource.org/licenses/bsd-license.php
 */
class Result extends \ArrayIterator
{
    /** The SPARQL Results type (either 'boolean' or 'bindings') */
    private $type = null;

    /** The value of a boolean result */
    private $boolean = null;

    /** Keep track of the XML parser state */
    private $fields = array();

    /** Keep track of the XML parser state */
    private $parseState = array();


    /** A constant for the SPARQL Query Results XML Format namespace */
    const SPARQL_XML_RESULTS_NS = 'http://www.w3.org/2005/sparql-results#';

    /** Create a new SPARQL Result object
     *
     * You should not normally need to create a SPARQL result
     * object directly - it will be constructed automatically
     * for you by EasyRdf\Sparql\_Client.
     *
     * @param string $data     The SPARQL result body
     * @param string $mimeType The MIME type of the result
     *
     * @throws \EasyRdf\Exception
     */
    public function __construct($data, $mimeType)
    {
        if ($mimeType == 'application/sparql-results+xml') {
            $this->parseXml($data);
        } elseif ($mimeType == 'application/sparql-results+json') {
            $this->parseJson($data);
        } else {
            throw new Exception(
                "Unsupported SPARQL Query Results format: $mimeType"
            );
        }
    }

    /** Get the query result type (boolean/bindings)
     *
     * ASK queries return a result of type 'boolean'.
     * SELECT query return a result of type 'bindings'.
     *
     * @return string The query result type.
     */
    public function getType()
    {
        return $this->type;
    }

    /** Return the boolean value of the query result
     *
     * If the query was of type boolean then this method will
     * return either true or false. If the query was of some other
     * type then this method will return null.
     *
     * @return boolean The result of the query.
     */
    public function getBoolean()
    {
        return $this->boolean;
    }

    /** Return true if the result of the query was true.
     *
     * @return boolean True if the query result was true.
     */
    public function isTrue()
    {
        return $this->boolean == true;
    }

    /** Return false if the result of the query was false.
     *
     * @return boolean True if the query result was false.
     */
    public function isFalse()
    {
        return $this->boolean == false;
    }

    /** Return the number of fields in a query result of type bindings.
     *
     * @return integer The number of fields.
     */
    public function numFields()
    {
        return count($this->fields);
    }

    /** Return the number of rows in a query result of type bindings.
     *
     * @return integer The number of rows.
     */
    public function numRows()
    {
        return count($this);
    }

    /** Get the field names in a query result of type bindings.
     *
     * @return array The names of the fields in the result.
     */
    public function getFields()
    {
        return $this->fields;
    }

    /** Return a human readable view of the query result.
     *
     * This method is intended to be a debugging aid and will
     * return a pretty-print view of the query result.
     *
     * @param  string  $format  Either 'text' or 'html'
     *
     * @throws Exception
     * @return string
     */
    public function dump($format = 'html')
    {
        if ($this->type == 'bindings') {
            $result = '';
            if ($format == 'html') {
                $result .= "<table class='sparql-results' style='border-collapse:collapse'>";
                $result .= "<tr>";
                foreach ($this->fields as $field) {
                    $result .= "<th style='border:solid 1px #000;padding:4px;".
                               "vertical-align:top;background-color:#eee;'>".
                               "?$field</th>";
                }
                $result .= "</tr>";
                foreach ($this as $row) {
                    $result .= "<tr>";
                    foreach ($this->fields as $field) {
                        if (isset($row->$field)) {
                            $result .= "<td style='border:solid 1px #000;padding:4px;".
                                       "vertical-align:top'>".
                                       $row->$field->dumpValue($format)."</td>";
                        } else {
                            $result .= "<td>&nbsp;</td>";
                        }
                    }
                    $result .= "</tr>";
                }
                $result .= "</table>";
            } else {
                // First calculate the width of each comment
                $colWidths = array();
                foreach ($this->fields as $field) {
                    $colWidths[$field] = strlen($field);
                }

                $textData = array();
                foreach ($this as $row) {
                    $textRow = array();
                    foreach ($row as $k => $v) {
                        $textRow[$k] = $v->dumpValue('text');
                        $width = strlen($textRow[$k]);
                        if ($colWidths[$k] < $width) {
                            $colWidths[$k] = $width;
                        }
                    }
                    $textData[] = $textRow;
                }

                // Create a horizontal rule
                $hr = "+";
                foreach ($colWidths as $v) {
                    $hr .= "-".str_repeat('-', $v).'-+';
                }

                // Output the field names
                $result .= "$hr\n|";
                foreach ($this->fields as $field) {
                    $result .= ' '.str_pad("?$field", $colWidths[$field]).' |';
                }

                // Output each of the rows
                $result .= "\n$hr\n";
                foreach ($textData as $textRow) {
                    $result .= '|';
                    foreach ($textRow as $k => $v) {
                        $result .= ' '.str_pad($v, $colWidths[$k]).' |';
                    }
                    $result .= "\n";
                }
                $result .= "$hr\n";
            }
            return $result;
        } elseif ($this->type == 'boolean') {
            $str = ($this->boolean ? 'true' : 'false');
            if ($format == 'html') {
                return "<p>Result: <span style='font-weight:bold'>$str</span></p>";
            } else {
                return "Result: $str";
            }
        } else {
            throw new Exception(
                "Failed to dump SPARQL Query Results format, unknown type: ".$this->type
            );
        }
    }

    /** Create a new EasyRdf\Resource or EasyRdf\Literal depending
     *  on the type of data passed in.
     *
     * @ignore
     */
    protected function newTerm($data)
    {
        switch ($data['type']) {
            case 'bnode':
                return new Resource('_:'.$data['value']);
            case 'uri':
                return new Resource($data['value']);
            case 'literal':
            case 'typed-literal':
                return Literal::create($data);
            default:
                throw new Exception(
                    "Failed to parse SPARQL Query Results format, unknown term type: ".
                    $data['type']
                );
        }
    }

    /** XML Result Parser: this function is called when an XML element starts
     *
     * @ignore
     */
    public function startElementHandler($parser)
    {
        if ($parser->depth() == 1) {
            if ($parser->name != 'sparql') {
                throw new Exception(
                    "Root node in XML Query Results format is not <sparql>"
                );
            } elseif ($parser->namespaceURI != self::SPARQL_XML_RESULTS_NS) {
                throw new Exception(
                    "Root node namespace is not ".self::SPARQL_XML_RESULTS_NS
                );
            }
        } else {
            switch ($parser->path()) {
                case 'sparql/boolean':
                    $this->type = 'boolean';
                    break;
                case 'sparql/head/variable':
                    $this->fields[] = $parser->getAttribute('name');
                    break;
                case 'sparql/results':
                    $this->type = 'bindings';
                    break;
                case 'sparql/results/result':
                    $this->parseState['result'] = new \stdClass();
                    break;
                case 'sparql/results/result/binding':
                    $this->parseState['key'] = $parser->getAttribute('name');
                    $this->parseState['bindingType'] = null;
                    $this->parseState['text'] = null;
                    $this->parseState['lang'] = null;
                    $this->parseState['datatype'] = null;
                    break;
                case 'sparql/results/result/binding/bnode':
                    $this->parseState['bindingType'] = 'bnode';
                    break;
                case 'sparql/results/result/binding/literal':
                    $this->parseState['bindingType'] = 'literal';
                    $this->parseState['lang'] = $parser->getAttribute('xml:lang');
                    $this->parseState['datatype'] = $parser->getAttribute('datatype');
                    break;
                case 'sparql/results/result/binding/uri':
                    $this->parseState['bindingType'] = 'uri';
                    break;
            }
        }
    }

    /** XML Result Parser: this function is called when text is encountered
     *
     * @ignore
     */
    public function textHandler($parser)
    {
        $this->parseState['text'] = $parser->value;
    }

    /** XML Result Parser: this function is called when an XML element ends
     *
     * @ignore
     */
    public function endElementHandler($parser)
    {
        switch ($parser->path()) {
            case 'sparql/boolean':
                $this->boolean = ($this->parseState['text'] == 'true') ? true : false;
                break;
            case 'sparql/results/result/binding':
                $key = $this->parseState['key'];
                $this->parseState['result']->$key = $this->newTerm([
                    'type' => $this->parseState['bindingType'],
                    'value' => $this->parseState['text'],
                    'lang' => $this->parseState['lang'],
                    'datatype' => $this->parseState['datatype']
                ]);
                break;
            case 'sparql/results/result':
                $this[] = $this->parseState['result'];
                break;
        }
    }

    /** Parse a SPARQL result in the XML format into the object.
     *
     * @ignore
     */
    protected function parseXml($data)
    {
        $this->parseState = array();
        $this->type = null;
        $parser = new \EasyRdf\XMLParser();
        $parser->startElementCallback = [$this, 'startElementHandler'];
        $parser->textCallback = [$this, 'textHandler'];
        $parser->endElementCallback = [$this, 'endElementHandler'];
        $parser->parse($data);

        if (!$this->type) {
            throw new Exception(
                "Failed to parse SPARQL XML Query Results format: unknown type"
            );
        }
    }

    /** Parse a SPARQL result in the JSON format into the object.
     *
     * @ignore
     */
    protected function parseJson($data)
    {
        // Decode JSON to an array
        $data = json_decode($data, true);

        if (isset($data['boolean'])) {
            $this->type = 'boolean';
            $this->boolean = $data['boolean'];
        } elseif (isset($data['results'])) {
            $this->type = 'bindings';
            if (isset($data['head']['vars'])) {
                $this->fields = $data['head']['vars'];
            }

            foreach ($data['results']['bindings'] as $row) {
                $t = new \stdClass();
                foreach ($row as $key => $value) {
                    $t->$key = $this->newTerm($value);
                }
                $this[] = $t;
            }
        } else {
            throw new Exception(
                "Failed to parse SPARQL JSON Query Results format"
            );
        }
    }

    /** Magic method to return value of the result to string
     *
     * If this is a boolean result then it will return 'true' or 'false'.
     * If it is a bindings type, then it will dump as a text based table.
     *
     * @return string A string representation of the result.
     */
    public function __toString()
    {
        if ($this->type == 'boolean') {
            return $this->boolean ? 'true' : 'false';
        } else {
            return $this->dump('text');
        }
    }
}
