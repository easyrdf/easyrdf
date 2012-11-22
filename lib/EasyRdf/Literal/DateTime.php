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
 * Class that represents an RDF Literal of datatype xsd:dateTime
 *
 * @package    EasyRdf
 * @link       http://www.w3.org/TR/xmlschema-2/#date
 * @copyright  Copyright (c) 2009-2012 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */
class EasyRdf_Literal_DateTime extends EasyRdf_Literal_Date
{
    const REGEXP = '/^-?\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(Z|[\-\+]\d{2}:\d{2})?$/';

    /** Constructor for creating a new date and time literal
     *
     * If the value is a DateTime object, then it will be converted to the xsd:dateTime format.
     * If the value is a string that does not look like an xsd:dateTime, then it will be
     * parsed using DateTime and converted to the xsd:dateTime format.
     *
     * @see DateTime
     *
     * @param  mixed  $value     The value of the literal
     * @param  string $lang      Should be null (literals with a datatype can't have a language)
     * @param  string $datatype  Optional datatype (default 'xsd:dateTime')
     * @return object EasyRdf_Literal_DateTime
     */
    public function __construct($value, $lang=null, $datatype=null)
    {
        // If string doesn't match XSD pattern, convert it to a DateTime object
        if (is_string($value) and !preg_match(self::REGEXP, $value)) {
            $value = new DateTime($value);
        }

        // Convert DateTime objects into string
        if ($value instanceof DateTime) {
            $iso = $value->format(DateTime::ISO8601);
            $value = preg_replace('/[\+\-]00(\:?)00$/', 'Z', $iso);
        }

        EasyRdf_Literal::__construct($value, null, $datatype);
    }

    /** 24-hour format of the hour as an integer
     *
     * @return integer
     */
    public function hour()
    {
        return (int)$this->format('H');
    }

    /** The minutes pasts the hour as an integer
     *
     * @return integer
     */
    public function min()
    {
        return (int)$this->format('i');
    }

    /** The seconds pasts the minute as an integer
     *
     * @return integer
     */
    public function sec()
    {
        return (int)$this->format('s');
    }
}

EasyRdf_Literal::setDatatypeMapping('xsd:dateTime', 'EasyRdf_Literal_DateTime');
