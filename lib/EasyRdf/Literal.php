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
 * Class that represents an RDF Literal
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009-2012 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */
class EasyRdf_Literal
{
    /** @ignore a mapping from datatype uri to class name */
    private static $_datatypeMap = array();

    /** @ignore A mapping from class name to datatype URI */
    private static $_classMap = array();

    /** @ignore The string value for this literal */
    protected $_value = NULL;

    /** @ignore The language of the literal (e.g. 'en') */
    protected $_lang = NULL;

    /** @ignore The datatype URI of the literal */
    protected $_datatype = NULL;


    /** Create a new literal object
     *
     * PHP values of type bool, int or float, will automatically be converted
     * to the corresponding datatype and PHP sub-class.
     *
     * If a registered datatype is given, then the registered subclass of EasyRdf_Literal
     * will instantiated.
     *
     * Note that literals are not required to have a language or datatype.
     * Literals cannot have both a language and a datatype.
     *
     * @param  mixed  $value     The value of the literal or an associative array
     * @param  string $lang      The natural language of the literal or NULL (e.g. 'en')
     * @param  string $datatype  The datatype of the literal or NULL (e.g. 'xsd:integer')
     * @return object EasyRdf_Literal (or subclass of EasyRdf_Literal)
     */
    public static function create($value, $lang=NULL, $datatype=NULL)
    {
        if (EasyRdf_Utils::isAssociativeArray($value)) {
            if (isset($value['xml:lang'])) {
               $lang = $value['xml:lang'];
            } elseif (isset($value['lang'])) {
               $lang = $value['lang'];
            }
            if (isset($value['datatype'])) {
               $datatype = $value['datatype'];
            }
            $value = isset($value['value']) ? $value['value'] : NULL;
        }

        if (empty($datatype)) {
            if (empty($lang)) {
                // Automatic datatype selection
                $datatype = self::getDatatypeForValue($value);
            }
        } elseif (is_object($datatype)) {
            $datatype = strval($datatype);
        } else {
            // Expand shortened URIs (qnames)
            $datatype = EasyRdf_Namespace::expand($datatype);
        }

        // Work out what class to use for this datatype
        if (isset(self::$_datatypeMap[$datatype])) {
            $class = self::$_datatypeMap[$datatype];
        } else {
            $class = 'EasyRdf_Literal';
        }
        return new $class($value, $lang, $datatype);
    }

    /** Register an RDF datatype with a PHP class name
     *
     * When parsing registered class will be used whenever the datatype
     * is seen.
     *
     * When serialising a registered class, the mapping will be used to
     * set the datatype in the RDF.
     *
     * Example:
     * EasyRdf_Literal::registerDatatype('xsd:dateTime', 'My_DateTime_Class');
     *
     * @param  string  $datatype   The RDF datatype (e.g. xsd:dateTime)
     * @param  string  $class      The PHP class name (e.g. My_DateTime_Class)
     */
    public static function setDatatypeMapping($datatype, $class)
    {
        if (!is_string($datatype) or $datatype == NULL or $datatype == '') {
            throw new InvalidArgumentException(
                "\$datatype should be a string and cannot be NULL or empty"
            );
        }

        if (!is_string($class) or $class == NULL or $class == '') {
            throw new InvalidArgumentException(
                "\$class should be a string and cannot be NULL or empty"
            );
        }

        $datatype = EasyRdf_Namespace::expand($datatype);
        self::$_datatypeMap[$datatype] = $class;
        self::$_classMap[$class] = $datatype;
    }

    /** Remove the mapping between an RDF datatype and a PHP class name
     *
     * @param  string  $datatype   The RDF datatype (e.g. xsd:dateTime)
     */
    public static function deleteDatatypeMapping($datatype)
    {
        if (!is_string($datatype) or $datatype == NULL or $datatype == '') {
            throw new InvalidArgumentException(
                "\$datatype should be a string and cannot be NULL or empty"
            );
        }

        $datatype = EasyRdf_Namespace::expand($datatype);
        if (isset(self::$_datatypeMap[$datatype])) {
            $class = self::$_datatypeMap[$datatype];
            unset(self::$_datatypeMap[$datatype]);
            unset(self::$_classMap[$class]);
        }
    }

    /** Get datatype URI for a PHP value.
     *
     * This static function is intended for internal use.
     * Given a PHP value, it will return an XSD datatype
     * URI for that value, for example:
     * http://www.w3.org/2001/XMLSchema#integer
     *
     * @return string  A URI for the datatype of $value.
     */
    public static function getDatatypeForValue($value)
    {
        if (is_float($value)) {
            return 'http://www.w3.org/2001/XMLSchema#decimal';
        } elseif (is_int($value)) {
            return 'http://www.w3.org/2001/XMLSchema#integer';
        } elseif (is_bool($value)) {
            return 'http://www.w3.org/2001/XMLSchema#boolean';
        } elseif (is_object($value) and $value instanceof DateTime) {
            return 'http://www.w3.org/2001/XMLSchema#dateTime';
        } else {
            return NULL;
        }
    }



    /** Constructor for creating a new literal
     *
     * @param  string $value     The value of the literal
     * @param  string $lang      The natural language of the literal or NULL (e.g. 'en')
     * @param  string $datatype  The datatype of the literal or NULL (e.g. 'xsd:string')
     * @return object EasyRdf_Literal
     */
    public function __construct($value, $lang=NULL, $datatype=NULL)
    {
        $this->_value = $value;
        $this->_lang = $lang ? $lang : NULL;
        $this->_datatype = $datatype ? $datatype : NULL;

        if ($this->_datatype) {
            if (is_object($this->_datatype)) {
                // Convert objects to strings
                $this->_datatype = strval($this->_datatype);
            } else {
                // Expand shortened URIs (CURIEs)
                $this->_datatype = EasyRdf_Namespace::expand($this->_datatype);
            }

            // Literals can not have both a language and a datatype
            $this->_lang = NULL;
        } else {
            // Set the datatype based on the subclass
            $class = get_class($this);
            if (isset(self::$_classMap[$class])) {
                $this->_datatype = self::$_classMap[$class];
                $this->_lang = NULL;
            }
        }

        // Cast value to string
        settype($this->_value, 'string');
    }

    /** Returns the value of the literal.
     *
     * @return string  Value of this literal.
     */
    public function getValue()
    {
        return $this->_value;
    }

    /** Returns the full datatype URI of the literal.
     *
     * @return string  Datatype URI of this literal.
     */
    public function getDatatypeUri()
    {
        return $this->_datatype;
    }

    /** Returns the shortened datatype URI of the literal.
     *
     * @return string  Datatype of this literal (e.g. xsd:integer).
     */
    public function getDatatype()
    {
        if ($this->_datatype) {
            return EasyRdf_Namespace::shorten($this->_datatype);
        } else {
            return NULL;
        }
    }

    /** Returns the language of the literal.
     *
     * @return string  Language of this literal.
     */
    public function getLang()
    {
        return $this->_lang;
    }

    /** Returns the properties of the literal as an associative array
     *
     * For example:
     * array('type' => 'literal', 'value' => 'string value')
     *
     * @return array  The properties of the literal
     */
    public function toArray()
    {
        $array = array(
            'type' => 'literal',
            'value' => $this->_value
        );

        if ($this->_datatype)
            $array['datatype'] = $this->_datatype;

        if ($this->_lang)
            $array['lang'] = $this->_lang;

        return $array;
    }

    /** Magic method to return the value of a literal as a string
     *
     * @return string The value of the literal
     */
    public function __toString()
    {
        return isset($this->_value) ? $this->_value : '';
    }

    /** Return pretty-print view of the literal
     *
     * @param  bool   $html  Set to true to format the dump using HTML
     * @param  string $color The colour of the text
     * @return string
     */
    public function dumpValue($html=true, $color='black')
    {
        return EasyRdf_Utils::dumpLiteralValue($this, $html, $color);
    }
}
