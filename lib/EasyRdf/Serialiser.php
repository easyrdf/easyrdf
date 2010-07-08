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
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */
class EasyRdf_Serialiser
{
    private static $_serialisersByName = array();

    /** Get the registered serialiser by name
     *
     * If a name is not registered, then this method will return null.
     *
     * @param  string  $name   The serialisation name (e.g. ntriples)
     * @return string          The class name (e.g. EasyRdf_Serialiser_Ntriples)
     */
    public static function getByName($name)
    {
        if (!is_string($name) or $name == null or $name == '') {
            throw new InvalidArgumentException(
                "\$name should be a string and cannot be null or empty"
            );
        } else if (array_key_exists($name, self::$_serialisersByName)) {
            return self::$_serialisersByName[$name];
        } else {
            return null;
        }
    }
    
    /** Register a serialiser
     *
     * @param  string  $class  The PHP class name (e.g. EasyRdf_Serialiser_Json)
     * @param  string  $name   The name of the serialiation (e.g. ntriples)
     * @return string          The PHP class name
     */
    public static function register($class, $name)
    {
        // FIXME: store the mime types and suffixes too
        
        if (!is_string($class) or $class == null or $class == '') {
            throw new InvalidArgumentException(
                "\$class should be a string and cannot be null or empty"
            );
        }

        if (!is_string($name) or $name == null or $name == '') {
            throw new InvalidArgumentException(
                "\$name should be a string and cannot be null or empty"
            );
        }

        self::$_serialisersByName[$name] = $class;
    }
    
    /** Get a list of serialisation format names
     *
     * @return array          An array of serialisation formats
     */
    public static function getNames()
    {
        return array_keys(self::$_serialisersByName);
    }
}
