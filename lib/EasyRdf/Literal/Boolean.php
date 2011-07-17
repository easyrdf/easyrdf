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
 * Class that represents an RDF Literal of datatype xsd:boolean
 *
 * @package    EasyRdf
 * @link       http://www.w3.org/TR/xmlschema-2/#boolean
 * @copyright  Copyright (c) 2009-2011 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */
class EasyRdf_Literal_Boolean extends EasyRdf_Literal
{
    /** Constructor for creating a new boolean literal
     *
     * Non-boolean values will be cast to boolean.
     *
     * @param  mixed  $value     The value of the literal or an associative array
     * @param  string $lang      Should be null (literals with a datatype can't have a language)
     * @param  string $datatype  Optional datatype (default 'xsd:boolean')
     * @return object EasyRdf_Literal_Boolean
     */
    public function __construct($value, $lang=null, $datatype=null)
    {
        parent::__construct((bool)$value, null, $datatype);
    }

    /** Return true if the value of the literal is true
     *
     * @return bool
     */
    public function isTrue()
    {
        return $this->_value == true;
    }

    /** Return true if the value of the literal is false
     *
     * @return bool
     */
    public function isFalse()
    {
        return $this->_value == false;
    }

    /** Magic method to return the value of a boolean when casted to string
     *
     * @return string The value of the boolean literal ('true' or 'false')
     */
    public function __toString()
    {
        return $this->_value ? 'true' : 'false';
    }
}

EasyRdf_Literal::setDatatypeMapping('xsd:boolean', 'EasyRdf_Literal_Boolean');
