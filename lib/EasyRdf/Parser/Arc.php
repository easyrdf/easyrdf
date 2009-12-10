<?php

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009 Nicholas J Humfrey.  All rights reserved.
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
 * @copyright  Copyright (c) 2009 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 * @version    $Id$
 */

/**
 * @see EasyRdf_Exception
 */
require_once "EasyRdf/Exception.php";

/**
 * Class to allow parsing of RDF using the ARC2 library.
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */
class EasyRdf_Parser_Arc
{
    private static $_supportedTypes = array(
        'json' => 'JSON',
        'rdfxml' => 'RDFXML',
        'turtle' => 'Turtle',
        'rdfa' => 'SemHTML',
    );

    /**
     * Constructor
     *
     * @return object EasyRdf_Parser_Arc
     */
    public function __construct()
    {
        require_once 'arc/ARC2.php';
    }

    /**
      * Parse an RDF document
      *
      * @param string $uri      the base URI of the data
      * @param string $data     the document data
      * @param string $docType  the format of the input data
      * @return array           the parsed data
      */
    public function parse($uri, $data, $docType)
    {
        if (!is_string($uri) or $uri == null or $uri == '') {
            throw new InvalidArgumentException(
                "\$uri should be a string and cannot be null or empty"
            );
        }

        if (!is_string($data) or $data == null or $data == '') {
            throw new InvalidArgumentException(
                "\$data should be a string and cannot be null or empty"
            );
        }

        if (!is_string($docType) or $docType == null or $docType == '') {
            throw new InvalidArgumentException(
                "\$docType should be a string and cannot be null or empty"
            );
        }

        if (array_key_exists($docType, self::$_supportedTypes)) {
            $className = self::$_supportedTypes[$docType];
        } else {
            throw new EasyRdf_Exception(
                "Parsing documents of type $docType ".
                "is not supported by EasyRdf_Parser_Arc."
            );
        }
        
        $parser = ARC2::getParser($className);
        if ($parser) {
            $parser->parse($uri, $data);
            return $parser->getSimpleIndex(false);
        } else {
            throw new EasyRdf_Exception(
                "ARC2 failed to get a $className parser."
            );
        }
    }
}
