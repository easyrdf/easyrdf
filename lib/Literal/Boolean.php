<?php
namespace EasyRdf\Literal;

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009-2014 Nicholas J Humfrey.  All rights reserved.
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
 * @copyright  Copyright (c) 2009-2014 Nicholas J Humfrey
 * @license    https://www.opensource.org/licenses/bsd-license.php
 */
use EasyRdf\Literal;

/**
 * Class that represents an RDF Literal of datatype xsd:boolean
 *
 * @package    EasyRdf
 * @link       http://www.w3.org/TR/xmlschema-2/#boolean
 * @copyright  Copyright (c) 2009-2014 Nicholas J Humfrey
 * @license    https://www.opensource.org/licenses/bsd-license.php
 */
class Boolean extends Literal
{
    /** Constructor for creating a new boolean literal
     *
     * If the value is not a string, then it will be converted to 'true' or 'false'.
     *
     * @param  mixed  $value    The value of the literal
     * @param  string $lang     Should be null (literals with a datatype can't have a language)
     * @param  string $datatype Optional datatype (default 'xsd:boolean')
     */
    public function __construct($value, $lang = null, $datatype = null)
    {
        if (!is_string($value)) {
            $value = $value ? 'true' : 'false';
        }
        parent::__construct($value, null, $datatype);
    }

    /** Return the value of the literal cast to a PHP bool
     *
     * If the value is 'true' or '1' return true, otherwise returns false.
     *
     * @return bool
     */
    public function getValue()
    {
        return strtolower($this->value) === 'true' or $this->value === '1';
    }

    /** Return true if the value of the literal is 'true' or '1'
     *
     * @return bool
     */
    public function isTrue()
    {
        return strtolower($this->value) === 'true' or $this->value === '1';
    }

    /** Return true if the value of the literal is 'false' or '0'
     *
     * @return bool
     */
    public function isFalse()
    {
        return strtolower($this->value) === 'false' or $this->value === '0';
    }
}
