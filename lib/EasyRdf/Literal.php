<?php

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009-2011 Nicholas J Humfrey.  All rights reserved.
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
 * @copyright  Copyright (c) 2009-2011 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 * @version    $Id$
 */

/**
 * Class that represents an RDF Literal
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009-2011 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */
class EasyRdf_Literal
{
    private static $_datatypeMap = array();
    private static $_classMap = array();

    /** The value for this literal */
    protected $_value = null;

    /** The language of the literal */
    protected $_lang = null;

    /** The datatype of the literal */
    protected $_datatype = null;


    public static function create($value, $lang=null, $datatype=null)
    {
        if (EasyRdf_Utils::is_associative_array($value)) {
            $lang = isset($value['lang']) ? $value['lang'] : null;
            $datatype = isset($value['datatype']) ? $value['datatype'] : null;
            $value = isset($value['value']) ? $value['value'] : null;
        }

        if ($datatype == null) {
            if ($lang == null) {
                // Automatic datatype selection
                $datatype = self::getDatatypeForValue($value);
            }
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
        if (!is_string($datatype) or $datatype == null or $datatype == '') {
            throw new InvalidArgumentException(
                "\$datatype should be a string and cannot be null or empty"
            );
        }

        if (!is_string($class) or $class == null or $class == '') {
            throw new InvalidArgumentException(
                "\$class should be a string and cannot be null or empty"
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
        if (!is_string($datatype) or $datatype == null or $datatype == '') {
            throw new InvalidArgumentException(
                "\$datatype should be a string and cannot be null or empty"
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
        } else if (is_int($value)) {
            return 'http://www.w3.org/2001/XMLSchema#integer';
        } else if (is_bool($value)) {
            return 'http://www.w3.org/2001/XMLSchema#boolean';
        } else {
            return null;
        }
    }



    /** Constructor
     *
     */
    public function __construct($value, $lang=null, $datatype=null)
    {
        $this->_value = $value;
        $this->_lang = $lang ? $lang : null;
        $this->_datatype = $datatype ? $datatype : null;

        if ($this->_datatype) {
            // Expand shortened URIs (qnames)
            $this->_datatype = EasyRdf_Namespace::expand($this->_datatype);

            // Literals can not have both a language and a datatype
            $this->_lang = null;
        } else {
            // Set the datatype based on the subclass
            $class = get_class($this);
            if (isset(self::$_classMap[$class])) {
                $this->_datatype = self::$_classMap[$class];
                $this->_lang = null;
            }
        }

        // Cast to string if it is a string
        if ($this->_lang or !$this->_datatype or $this->_datatype == 'http://www.w3.org/2001/XMLSchema#string') {
            settype($this->_value, 'string');
        }
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
            return null;
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
            'value' => strval($this->_value)
        );

        if ($this->_datatype)
            $array['datatype'] = $this->_datatype;

        if ($this->_lang)
            $array['lang'] = $this->_lang;

        return $array;
    }

    /** Magic method to return the value of a literal when casted to string
     *
     * @return string The value of the literal
     */
    public function __toString()
    {
        return isset($this->_value) ? strval($this->_value) : '';
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
