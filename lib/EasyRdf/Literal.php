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
 * Class that represents an RDF Literal
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009-2010 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */
class EasyRdf_Literal
{
    /** The value for this literal */
    private $_value = null;

    /** The language of the literal */
    private $_lang = null;

    /** The datatype of the literal */
    private $_datatype = null;

    /** Constructor
     *
     */
    public function __construct($value, $lang=null, $datatype=null)
    {
        if (EasyRdf_Utils::is_associative_array($value)) {
            $this->_value = isset($value['value']) ?
                            $value['value'] : null;
            $this->_lang = isset($value['lang']) ?
                           $value['lang'] : null;
            $this->_datatype = isset($value['datatype']) ?
                               $value['datatype'] : null;
        } else {
            $this->_value = $value;
            $this->_lang = $lang ? $lang : null;
            $this->_datatype = $datatype ? $datatype : null;
        }

        // Automatic datatype selection
        if ($this->_datatype == null && $this->_lang == null) {
            if (is_float($this->_value)) {
                $this->_datatype = 'xsd:decimal';
            } else if (is_int($this->_value)) {
                $this->_datatype = 'xsd:integer';
            } else if (is_bool($this->_value)) {
                $this->_datatype = 'xsd:boolean';
            }
        }

        if ($this->_datatype) {
            // Expand shortened URIs (qnames)
            $this->_datatype = EasyRdf_Namespace::expand($this->_datatype);

            // Literals can not have both a language and a datatype
            $this->_lang = null;
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
    
    public function toArray()
    {
        $array = array('type' => 'literal', 'value' => $this->_value);
        
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
     * @param  bool  $html  Set to true to format the dump using HTML
     */
    public function dumpValue($html=true)
    {
        $text = '"'.$this->_value.'"';
        if ($this->_lang) {
            $text .= '@' . $this->_lang;
        }
        if ($this->_datatype) {
            $datatype = EasyRdf_Namespace::shorten($this->_datatype);
            $text .= "^^$datatype";
        }

        if ($html) {
            return "<span style='color:blue'>".
                   htmlentities($text, ENT_COMPAT, "UTF-8").
                   "</span>";
        } else {
            return $text;
        }
    }
}
