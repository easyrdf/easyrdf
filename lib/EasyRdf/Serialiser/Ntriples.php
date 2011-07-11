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
 * Class to serialise an EasyRdf_Graph to N-Triples
 * with no external dependancies.
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009-2010 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */
class EasyRdf_Serialiser_Ntriples extends EasyRdf_Serialiser
{
    private $_escChars = array();   // Character encoding cache

    /**
     * @ignore
     */
    protected function ntriplesResource($res)
    {
        $id = $this->escape($res->getURI());
        if ($res->isBNode()) {
            return $id;
        } else {
            return "<$id>";
        }
    }

    /**
     * @ignore
     */
    protected function ntriplesObject($obj)
    {
        if ($obj instanceof EasyRdf_Resource) {
            return $this->ntriplesResource($obj);
        } else if ($obj instanceof EasyRdf_Literal) {
            $value = $this->escape($obj->getValue());
            if ($obj->getLang()) {
                $lang = $this->escape($obj->getLang());
                return '"' . $value . '"' . '@' . $lang;
            } else if ($obj->getDatatypeUri()) {
                $datatype = $this->escape($obj->getDatatypeUri());
                return '"' . $value . '"' . "^^<$datatype>";
            } else {
                return '"' . $value . '"';
            }
        } else {
            throw new EasyRdf_Exception(
                "Unable to serialise object to ntriples: ".gettype($obj)
            );
        }
    }

    /**
     * @ignore
     */
    protected function escape($str)
    {
        if (strpos(utf8_decode(str_replace('?', '', $str)), '?') === false) {
            $str = utf8_decode($str);
        }

        $result = '';
        $strLen = strlen($str);
        for ($i = 0; $i < $strLen; $i++) {
            $c = $str[$i];
            if (!isset($this->_escChars[$c])) {
                $this->_escChars[$c] = $this->escapedChar($c, $this->unicodeCharNo($c));
            }
            $result .= $this->_escChars[$c];
        }
        return $result;
    }

    /**
     * @ignore
     */
    protected function unicodeCharNo($c)
    {
        $cUtf = utf8_encode($c);
        $bl = strlen($cUtf); /* binary length */
        $r = 0;
        switch ($bl) {
            case 1: /* 0####### (0-127) */
                $r = ord($cUtf);
                break;
            case 2: /* 110##### 10###### = 192+x 128+x */
                $r = ((ord($cUtf[0]) - 192) * 64) +
                     (ord($cUtf[1]) - 128);
                break;
            case 3: /* 1110#### 10###### 10###### = 224+x 128+x 128+x */
                $r = ((ord($cUtf[0]) - 224) * 4096) +
                     ((ord($cUtf[1]) - 128) * 64) +
                     (ord($cUtf[2]) - 128);
                break;
            case 4: /* 1111#### 10###### 10###### 10###### = 240+x 128+x 128+x 128+x */
                $r = ((ord($cUtf[0]) - 240) * 262144) +
                     ((ord($cUtf[1]) - 128) * 4096) +
                     ((ord($cUtf[2]) - 128) * 64) +
                     (ord($cUtf[3]) - 128);
                break;
        }
        return $r;
    }

    /**
     * @ignore
     */
    protected function escapedChar($c, $no)
    {
        /* see http://www.w3.org/TR/rdf-testcases/#ntrip_strings */
        if ($no < 9)        return "\\u" . sprintf('%04X', $no);  /* #x0-#x8 (0-8) */
        if ($no == 9)       return '\t';                          /* #x9 (9) */
        if ($no == 10)      return '\n';                          /* #xA (10) */
        if ($no < 13)       return "\\u" . sprintf('%04X', $no);  /* #xB-#xC (11-12) */
        if ($no == 13)      return '\r';                          /* #xD (13) */
        if ($no < 32)       return "\\u" . sprintf('%04X', $no);  /* #xE-#x1F (14-31) */
        if ($no < 34)       return $c;                            /* #x20-#x21 (32-33) */
        if ($no == 34)      return '\"';                          /* #x22 (34) */
        if ($no < 92)       return $c;                            /* #x23-#x5B (35-91) */
        if ($no == 92)      return '\\';                          /* #x5C (92) */
        if ($no < 127)      return $c;                            /* #x5D-#x7E (93-126) */
        if ($no < 65536)    return "\\u" . sprintf('%04X', $no);  /* #x7F-#xFFFF (128-65535) */
        if ($no < 1114112)  return "\\U" . sprintf('%08X', $no);  /* #x10000-#x10FFFF (65536-1114111) */
        return '';                                                /* not defined => ignore */
    }

    /**
     * Serialise an EasyRdf_Graph into N-Triples
     *
     * @param object EasyRdf_Graph $graph   An EasyRdf_Graph object.
     * @param string  $format               The name of the format to convert to.
     * @return string                       The RDF in the new desired format.
     */
    public function serialise($graph, $format='ntriples')
    {
        parent::checkSerialiseParams($graph, $format);

        if ($format != 'ntriples') {
            throw new EasyRdf_Exception(
                "EasyRdf_Serialiser_Ntriples does not support: $format"
            );
        }

        $nt = '';
        foreach ($graph->resources() as $resource) {
            foreach ($resource->propertyUris() as $property) {
                $objects = $resource->all($property);
                foreach ($objects as $object) {
                    $nt .= $this->ntriplesResource($resource)." ";
                    $nt .= "<" . $this->escape($property) . "> ";
                    $nt .= $this->ntriplesObject($object)." .\n";
                }
            }
        }
        return $nt;
    }
}

EasyRdf_Format::registerSerialiser('ntriples', 'EasyRdf_Serialiser_Ntriples');
