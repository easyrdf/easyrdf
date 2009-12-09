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
 * @see EasyRdf_Graph
 */
require_once "EasyRdf/Graph.php";

/**
 * @see EasyRdf_Namespace
 */
require_once "EasyRdf/Namespace.php";

/**
 * Class to serialise an EasyRdf_Graph into N-Triples
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */
class EasyRdf_Serialiser_Ntriples
{
    protected static function serialiseResource($res)
    {
        if (is_object($res)) {
            if ($res->isBNode()) {
                return $res->getURI();
            } else {
                return "<".$res->getURI().">";
            }
        } else {
            $uri = EasyRdf_Namespace::expand($res);
            if ($uri) {
                return "<$uri>";
            } else {
                return "<$res>";
            }
        }
    }

    protected static function serialiseObject($obj)
    {
        if (is_object($obj) and $obj instanceof EasyRdf_Resource) {
            return self::serialiseResource($obj);
        } else if (is_scalar($obj)) {
            return "\"$obj\"";
        } else {
            echo "Unknown!";
        }
    }

    public static function serialise($graph)
    {
        $nt = '';
        foreach ($graph->resources() as $resource) {
            foreach ($resource->properties() as $property) {
                $objects = $resource->all($property);
                foreach ($objects as $object) {
                    $nt .= self::serialiseResource($resource)." ";
                    $nt .= self::serialiseResource($property)." ";
                    $nt .= self::serialiseObject($object)." .\n";
                }
            }
        }
        return $nt;
    }
}

