<?php

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009-2012 Nicholas J Humfrey.
 * Copyright (c) 1997-2006 Aduna (http://www.aduna-software.com/)
 * All rights reserved.
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
 *             Copyright (c) 1997-2006 Aduna (http://www.aduna-software.com/)
 * @license    http://www.opensource.org/licenses/bsd-license.php
 * @version    $Id$
 */

/**
 * Class to parse Turtle with no external dependancies.
 *
 * http://www.w3.org/TR/turtle/
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009-2012 Nicholas J Humfrey
 *             Copyright (c) 1997-2006 Aduna (http://www.aduna-software.com/)
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */
class EasyRdf_Parser_Turtle extends EasyRdf_Parser
{
    /**
     * Constructor
     *
     * @return object EasyRdf_Parser_Turtle
     */
    public function __construct()
    {
    }

    /**
      * Parse Turtle into an EasyRdf_Graph
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

        if ($format != 'turtle') {
            throw new EasyRdf_Exception(
                "EasyRdf_Parser_Turtle does not support: $format"
            );
        }

        $this->_graph = $graph;
        $this->_data = $data;
        $this->_baseUri = $baseUri;
        $this->_len = strlen($data);
        $this->_pos = 0;

        $this->_namespaces = array();
        $this->_subject = NULL;
        $this->_predicate = NULL;
        $this->_object = NULL;

        $this->resetBnodeMap();

        $c = $this->skipWSC();
        while ($c != -1) {
          $this->parseStatement();

          $c = $this->skipWSC();
        }

        // Success
        return true;
    }


    protected function parseStatement()
    {
        $c = $this->peek();
        if ($c == '@') {
            $this->parseDirective();
            $this->skipWSC();
            $this->verifyCharacter($this->read(), ".");
        } else {
            $this->parseTriples();
            $this->skipWSC();
            $this->verifyCharacter($this->read(), ".");
        }
    }

    protected function parseDirective()
    {
        // Verify that the first characters form the string "prefix"
        $this->verifyCharacter($this->read(), "@");

        $directive = '';

        $c = $this->read();
        while ($c != -1 && !self::isWhitespace($c)) {
            $directive .= $c;
            $c = $this->read();
        }

        if ($directive == "prefix") {
          $this->parsePrefixID();
        }
        else if ($directive == "base") {
            $this->parseBase();
        }
        else if (strlen($directive) == 0) {
          $this->reportFatalError("Directive name is missing, expected @prefix or @base");
        }
        else {
          $this->reportFatalError("Unknown directive \"@" . $directive . "\"");
        }
    }

    protected function parsePrefixID()
    {
        $this->skipWSC();

        // Read prefix ID (e.g. "rdf:" or ":")
        $prefixID = '';

        while (true) {
            $c = $this->read();
            if ($c == ':') {
                $this->unread($c);
                break;
            } else if (self::isWhitespace($c)) {
                break;
            } else if ($c == -1) {
                $this->throwEOFException();
            }

            $prefixID .= $c;
        }

        $this->skipWSC();
        $this->verifyCharacter($this->read(), ":");
        $this->skipWSC();

        // Read the namespace URI
        $namespace = $this->parseURI();

        // Store local namespace mapping
        $this->_namespaces[$prefixID] = $namespace['value'];
    }

    protected function parseBase()
    {
        $this->skipWSC();

        $baseUri = $this->parseURI();
        $this->_baseUri = $baseUri['value'];
    }

    protected function parseTriples()
    {
        $this->parseSubject();
        $this->skipWSC();
        $this->parsePredicateObjectList();

        $this->_subject = NULL;
        $this->_predicate = NULL;
        $this->_object = NULL;
    }

    protected function parsePredicateObjectList()
    {
        $this->_predicate = $this->parsePredicate();

        $this->skipWSC();
        $this->parseObjectList();

        while ($this->skipWSC() == ';') {
            $this->read();

            $c = $this->skipWSC();

            if ($c == '.' || // end of triple
                $c == ']') // end of predicateObjectList inside blank node
            {
                break;
            }

            $this->_predicate = $this->parsePredicate();

            $this->skipWSC();

            $this->parseObjectList();
        }
    }

    protected function parseObjectList()
    {
        $this->parseObject();

        while ($this->skipWSC() == ',') {
            $this->read();
            $this->skipWSC();
            $this->parseObject();
        }
    }

    protected function parseSubject()
    {
        $c = $this->peek();
        if ($c == '(') {
            $this->_subject = $this->parseCollection();
        } else if ($c == '[') {
            $this->_subject = $this->parseImplicitBlank();
        } else {
            $value = $this->parseValue();

            if ($value['type'] == 'uri' or $value['type'] == 'bnode') {
                $this->_subject = $value;
            } else {
                $this->reportFatalError("Illegal subject type: ".$value['type']);
            }
        }
    }

    protected function parsePredicate()
    {
        // Check if the short-cut 'a' is used
        $c1 = $this->read();

        if ($c1 == 'a') {
            $c2 = $this->read();

            if (self::isWhitespace($c2)) {
                // Short-cut is used, return the rdf:type URI
                return array(
                    'type' => 'uri',
                    'value' => EasyRdf_Namespace::get('rdf') . 'type'
                );
            }

            // Short-cut is not used, unread all characters
            $this->unread($c2);
        }
        $this->unread($c1);

        // Predicate is a normal resource
        $predicate = $this->parseValue();
        if ($predicate['type'] == 'uri') {
            return $predicate;
        } else {
            $this->reportFatalError("Illegal predicate value: " . $predicate);
            return NULL;
        }
    }

    protected function parseObject()
    {
        $c = $this->peek();

        if ($c == '(') {
            $this->_object = $this->parseCollection();
        } else if ($c == '[') {
            $this->_object = $this->parseImplicitBlank();
        } else {
            $this->_object = $this->parseValue();
        }

        $this->_graph->add(
            $this->_subject['value'],
            $this->_predicate['value'],
            $this->_object
        );
    }

    /**
    * Parses a collection, e.g. <tt>( item1 item2 item3 )</tt>.
    */
    protected function parseCollection()
    {
        $this->verifyCharacter($this->read(), "(");

        $c = $this->skipWSC();
        if ($c == ')') {
            // Empty list
            $this->read();
            return array(
                'type' => 'uri',
                'value' => EasyRdf_Namespace::get('rdf') . 'nil'
            );
        } else {
            $listRoot = array(
                'type' => 'bnode',
                'value' => $this->_graph->newBNodeId()
            );

            // Remember current subject and predicate
            $oldSubject = $this->_subject;
            $oldPredicate = $this->_predicate;

            // generated bNode becomes subject, predicate becomes rdf:first
            $this->_subject = $listRoot;
            $this->_predicate = array(
                'type' => 'uri',
                'value' => EasyRdf_Namespace::get('rdf') . 'first'
            );

            $this->parseObject();
            $bNode = $listRoot;

            while ($this->skipWSC() != ')') {
                // Create another list node and link it to the previous
                $newNode = array(
                    'type' => 'bnode',
                    'value' => $this->_graph->newBNodeId()
                );

                $this->_graph->add(
                    $bNode['value'],
                    EasyRdf_Namespace::get('rdf') . 'rest',
                    $newNode
                );

                // New node becomes the current
                $this->_subject = $bNode = $newNode;

                $this->parseObject();
            }

            // Skip ')'
            $this->read();

            // Close the list
            $this->_graph->add(
                $bNode['value'],
                EasyRdf_Namespace::get('rdf') . 'rest',
                array(
                    'type' => 'uri',
                    'value' => EasyRdf_Namespace::get('rdf') . 'nil'
                )
            );

            // Restore previous subject and predicate
            $this->_subject = $oldSubject;
            $this->_predicate = $oldPredicate;

            return $listRoot;
        }
    }

    /**
     * Parses an implicit blank node. This method parses the token <tt>[]</tt>
     * and predicateObjectLists that are surrounded by square brackets.
     */
    protected function parseImplicitBlank()
    {
        $this->verifyCharacter($this->read(), "[");

        $bnode = array(
            'type' => 'bnode',
            'value' => $this->_graph->newBNodeId()
        );

        $c = $this->read();
        if ($c != ']') {
            $this->unread($c);

            // Remember current subject and predicate
            $oldSubject = $this->_subject;
            $oldPredicate = $this->_predicate;

            // generated bNode becomes subject
            $this->_subject = $bnode;

            // Enter recursion with nested predicate-object list
            $this->skipWSC();

            $this->parsePredicateObjectList();

            $this->skipWSC();

            // Read closing bracket
            $this->verifyCharacter($this->read(), "]");

            // Restore previous subject and predicate
            $this->_subject = $oldSubject;
            $this->_predicate = $oldPredicate;
        }

        return $bnode;
    }

    /**
    * Parses an RDF value. This method parses uriref, qname, node ID, quoted
    * literal, integer, double and boolean.
    */
    protected function parseValue()
    {
        $c = $this->peek();

        if ($c == '<') {
            // uriref, e.g. <foo://bar>
            return $this->parseURI();
        } else if ($c == ':' || self::isPrefixStartChar($c)) {
            // qname or boolean
            return $this->parseQNameOrBoolean();
        } else if ($c == '_') {
            // node ID, e.g. _:n1
            return $this->parseNodeID();
        } else if ($c == '"') {
            // quoted literal, e.g. "foo" or """foo"""
            return $this->parseQuotedLiteral();
        } else if (ctype_digit($c) || $c == '.' || $c == '+' || $c == '-') {
            // integer or double, e.g. 123 or 1.2e3
            return $this->parseNumber();
        } else if ($c == -1) {
            $this->throwEOFException();
            return null;
        } else {
            $this->reportFatalError("Expected an RDF value here, found '$c'");
            return null;
        }
    }

    /**
     * Parses a quoted string, optionally followed by a language tag or datatype.
     */
    protected function parseQuotedLiteral()
    {
        $label = $this->parseQuotedString();

        // Check for presence of a language tag or datatype
        $c = $this->peek();

        if ($c == '@') {
            $this->read();

            // Read language
            $lang = '';
            $c = $this->read();
            if ($c == -1) {
                $this->throwEOFException();
            }
            if (!self::isLanguageStartChar($c)) {
                $this->reportError("Expected a letter, found '$c'");
            }

            $lang .= $c;

            $c = $this->read();
            while (self::isLanguageChar($c)) {
                $lang .= $c;
                $c = $this->read();
            }

            $this->unread($c);

            return array(
                'type' => 'literal',
                'value' => $label,
                'lang' => $lang
            );
        } else if ($c == '^') {
            $this->read();

            // next character should be another '^'
            $this->verifyCharacter($this->read(), "^");

            // Read datatype
            $datatype = $this->parseValue();
            if ($datatype['type'] == 'uri') {
                return array(
                    'type' => 'literal',
                    'value' => $label,
                    'datatype' => $datatype['value']
                );
            } else {
                $this->reportFatalError("Illegal datatype value: $datatype");
                return NULL;
            }
        } else {
            return array(
                'type' => 'literal',
                'value' => $label
            );
        }
    }

    /**
    * Parses a quoted string, which is either a "normal string" or a """long
    * string""".
    */
    protected function parseQuotedString()
    {
        $result = NULL;

        // First character should be '"'
        $this->verifyCharacter($this->read(), "\"");

        // Check for long-string, which starts and ends with three double quotes
        $c2 = $this->read();
        $c3 = $this->read();

        if ($c2 == '"' && $c3 == '"') {
            // Long string
            $result = $this->parseLongString();
        } else {
            // Normal string
            $this->unread($c3);
            $this->unread($c2);

            $result = $this->parseString();
        }

        // FIXME: Unescape any escape sequences
        //     try {
        //       result = TurtleUtil.decodeString(result);
        //     }
        //     catch (IllegalArgumentException e) {
        //       reportError(e.getMessage());
        //     }

        return $result;
    }

    /**
    * Parses a "normal string". This method assumes that the first double quote
    * has already been parsed.
    */
    protected function parseString()
    {
        $str = '';

        while (true) {
            $c = $this->read();

            if ($c == '"') {
                break;
            } else if ($c == -1) {
                $this->throwEOFException();
            }

            $str .= $c;

            if ($c == '\\') {
                // This escapes the next character, which might be a '"'
                $c = $this->read();
                if ($c == -1) {
                    $this->throwEOFException();
                }
                $str .= $c;
            }
        }

        return $str;
    }

    /**
     * Parses a """long string""". This method assumes that the first three
     * double quotes have already been parsed.
     */
    protected function parseLongString()
    {
        $str = '';
        $doubleQuoteCount = 0;

        while ($doubleQuoteCount < 3) {
            $c = $this->read();

            if ($c == -1) {
                $this->throwEOFException();
            } else if ($c == '"') {
                $doubleQuoteCount++;
            } else {
                $doubleQuoteCount = 0;
            }

            $str .= $c;

            if ($c == '\\') {
                // This escapes the next character, which might be a '"'
                $c = $this->read();
                if ($c == -1) {
                    $this->throwEOFException();
                }
                $str .= $c;
            }
        }

        return substr($str, 0, -3);
    }

    protected function parseNumber()
    {
        $value = '';
        $datatype = EasyRdf_Namespace::get('xsd').'integer';

        $c = $this->read();

        // read optional sign character
        if ($c == '+' || $c == '-') {
            $value .= $c;
            $value .= $c;
            $c = $this->read();
        }

        while (ctype_digit($c)) {
            $value .= $c;
            $c = $this->read();
        }

        if ($c == '.' || $c == 'e' || $c == 'E') {
            // We're parsing a decimal or a double
            $datatype = EasyRdf_Namespace::get('xsd').'decimal';

            // read optional fractional digits
            if ($c == '.') {
                $value .= $c;
                $c = $this->read();
                while (ctype_digit($c)) {
                    $value .= $c;
                    $c = $this->read();
                }

                if (strlen($value) == 1) {
                    // We've only parsed a '.'
                    $this->reportFatalError("Object for statement missing");
                }
            } else {
                if (strlen($value) == 0) {
                    // We've only parsed an 'e' or 'E'
                    $this->reportFatalError("Object for statement missing");
                }
            }

            // read optional exponent
            if ($c == 'e' || $c == 'E') {
                $datatype = EasyRdf_Namespace::get('xsd').'double';
                $value .= $c;

                $c = $this->read();
                if ($c == '+' || $c == '-') {
                    $value .= $c;
                    $c = $this->read();
                }

                if (!ctype_digit($c)) {
                    $this->reportError("Exponent value missing");
                }

                $value .= $c;

                $c = $this->read();
                while (ctype_digit($c)) {
                    $value .= $c;
                    $c = $this->read();
                }
            }
        }

        // Unread last character, it isn't part of the number
        $this->unread($c);

        // Return result as a typed literal
        return array(
            'type' => 'literal',
            'value' => $value,
            'datatype' => $datatype
        );
     }

    protected function parseURI()
    {
        $uri = '';

        // First character should be '<'
        $this->verifyCharacter($this->read(), "<");

        // Read up to the next '>' character
        while (true) {
            $c = $this->read();

            if ($c == '>') {
                break;
            } else if ($c == -1) {
                $this->throwEOFException();
            }

            $uri .= $c;

            if ($c == '\\') {
                // This escapes the next character, which might be a '>'
                $c = $this->read();
                if ($c == -1) {
                    $this->throwEOFException();
                }
                $uri .= $c;
            }
        }

        // FIXME: Unescape any escape sequences
        //try {
        //  $uri = self::decodeString($uri);
        //}
        //catch (IllegalArgumentException e) {
        //  $this->reportError(e.getMessage());
        //}

        return array(
            'type' => 'uri',
            'value' => EasyRdf_Utils::resolveUriReference($this->_baseUri, $uri)
        );
    }

    /**
    * Parses qnames and boolean values, which have equivalent starting
    * characters.
    */
    protected function parseQNameOrBoolean()
    {
        // First character should be a ':' or a letter
        $c = $this->read();
        if ($c == -1) {
            $this->throwEOFException();
        }
        if ($c != ':' && !self::isPrefixStartChar($c)) {
            $this->reportError("Expected a ':' or a letter, found '$c'");
        }

        $namespace = NULL;

        if ($c == ':') {
            // qname using default namespace
            $namespace = $this->_namespaces[""];
            if ($namespace == NULL) {
                $this->reportError("Default namespace used but not defined");
            }
        } else {
            // $c is the first letter of the prefix
            $prefix = $c;

            $c = $this->read();
            while (self::isPrefixChar($c)) {
                $prefix .= $c;
                $c = $this->read();
            }

            if ($c != ':') {
                // prefix may actually be a boolean value
                $value = $prefix;

                if ($value == "true" || $value == "false") {
                    return array(
                        'type' => 'literal',
                        'value' => $value,
                        'datatype' => EasyRdf_Namespace::get('xsd') . 'boolean'
                    );
                }
            }

            $this->verifyCharacter($c, ":");

            if (isset($this->_namespaces[$prefix])) {
                $namespace = $this->_namespaces[$prefix];
            } else {
                $this->reportError("Namespace prefix '$prefix' used but not defined");
            }
        }

        // $c == ':', read optional local name
        $localName = '';
        $c = $this->read();
        if (self::isNameStartChar($c)) {
            $localName .= $c;

            $c = $this->read();
            while (self::isNameChar($c)) {
                $localName .= $c;
                $c = $this->read();
            }
        }

        // Unread last character
        $this->unread($c);

        // Note: namespace has already been resolved
        return array(
            'type' => 'uri',
            'value' => $namespace . $localName
        );
    }

    /**
     * Parses a blank node ID, e.g. <tt>_:node1</tt>.
     */
    protected function parseNodeID()
    {
        // Node ID should start with "_:"
        $this->verifyCharacter($this->read(), "_");
        $this->verifyCharacter($this->read(), ":");

        // Read the node ID
        $c = $this->read();
        if ($c == -1) {
            $this->throwEOFException();
        } else if (!self::isNameStartChar($c)) {
            $this->reportError("Expected a letter, found '$c'");
        }

        // Read all following letter and numbers, they are part of the name
        $name = $c;
        $c = $this->read();
        while (self::isNameChar($c)) {
            $name .= $c;
            $c = $this->read();
        }

        $this->unread($c);

        return array(
            'type' => 'bnode',
            'value' => $this->remapBnode($this->_graph, $name)
        );
    }


    /**
     * Verifies that the supplied character <tt>c</tt> is one of the expected
     * characters specified in <tt>expected</tt>. This method will throw a
     * <tt>ParseException</tt> if this is not the case.
     */
    protected function verifyCharacter($c, $expected)
    {
        if ($c == -1) {
            $this->throwEOFException();
        } else if (strpbrk($c, $expected) === FALSE) {
            $msg = 'Expected ';
            for ($i = 0; $i < strlen($expected); $i++) {
                if ($i > 0) {
                    $msg .= " or ";
                }
                $msg .= '\''.$expected[$i].'\'';
            }
            $msg .= ", found '$c'";

            $this->reportError($msg);
        }
    }

    protected function skipWSC()
    {
        $c = $this->read();
        while (self::isWhitespace($c) || $c == '#') {
            if ($c == '#') {
                $this->skipLine();
            }

            $c = $this->read();
        }

        $this->unread($c);
        return $c;
    }

    /**
     * Consumes characters from reader until the first EOL has been read.
     */
    protected function skipLine()
    {
        $c = $this->read();
        while ($c != -1 && $c != "\r" && $c != "\n") {
            $c = $this->read();
        }

        // c is equal to -1, \r or \n.
        // In case c is equal to \r, we should also read a following \n.
        if ($c == "\r") {
            $c = $this->read();
            if ($c != "\n") {
                $this->unread($c);
            }
        }
        // FIXME: reportLocation();
    }


    protected function read()
    {
        if ($this->_pos < $this->_len) {
            $c = $this->_data[$this->_pos];
            $this->_pos++;
            return $c;
        } else {
            return -1;
        }
    }

    protected function peek()
    {
        if ($this->_pos < $this->_len) {
            return $this->_data[$this->_pos];
        } else {
            return -1;
        }
    }

    protected function unread()
    {
        // FIXME: check for negative position
        $this->_pos--;
    }

    protected function reportError($str)
    {
        // FIXME: throw an exception instead
        error_log("Error! $str\n");
        exit(-1);
    }

    protected function reportFatalError($str)
    {
        // FIXME: throw an exception instead
        error_log("Error! $str\n");
        exit(-1);
    }

    protected function throwEOFException()
    {
        // FIXME: throw an exception instead
        error_log("Unexpected end of file.\n");
        exit(-1);
    }

    public static function isWhitespace($c)
    {
        // Whitespace character are space, tab, newline and carriage return:
        return $c == " " || $c == "\t" || $c == "\r" || $c == "\n";
    }

    public static function isPrefixStartChar($c) {
        $o = ord($c);
        return
            $o >= 0x41   && $o <= 0x5a ||
            $o >= 0x61   && $o <= 0x7a ||
            $o >= 0x00C0 && $o <= 0x00D6 ||
            $o >= 0x00D8 && $o <= 0x00F6 ||
            $o >= 0x00F8 && $o <= 0x02FF ||
            $o >= 0x0370 && $o <= 0x037D ||
            $o >= 0x037F && $o <= 0x1FFF ||
            $o >= 0x200C && $o <= 0x200D ||
            $o >= 0x2070 && $o <= 0x218F ||
            $o >= 0x2C00 && $o <= 0x2FEF ||
            $o >= 0x3001 && $o <= 0xD7FF ||
            $o >= 0xF900 && $o <= 0xFDCF ||
            $o >= 0xFDF0 && $o <= 0xFFFD ||
            $o >= 0x10000 && $o <= 0xEFFFF;
    }

    public static function isNameStartChar($c) {
        return $c == '_' || self::isPrefixStartChar($c);
    }

    public static function isNameChar($c) {
        $o = ord($c);
        return
            self::isNameStartChar($c) ||
            $c == '-' ||
            $o >= 0x30 && $o <= 0x39 ||   # numeric
            $o == 0x00B7 ||
            $o >= 0x0300 && $o <= 0x036F ||
            $o >= 0x203F && $o <= 0x2040;
    }

    public static function isPrefixChar($c) {
        return self::isNameChar($c);
    }

    public static function isLanguageStartChar($c) {
        $o = ord($c);
        return
            $o >= 0x41   && $o <= 0x5a ||
            $o >= 0x61   && $o <= 0x7a;
    }

    public static function isLanguageChar($c) {
        $o = ord($c);
        return
            $o >= 0x41   && $o <= 0x5a ||   # A-Z
            $o >= 0x61   && $o <= 0x7a ||   # a-z
            $o >= 0x30   && $o <= 0x39 ||   # 0-9
            $c == '-';
    }
}

EasyRdf_Format::registerParser('turtle', 'EasyRdf_Parser_Turtle');
