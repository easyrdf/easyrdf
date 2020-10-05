<?php
namespace EasyRdf;

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) Nicholas J Humfrey.  All rights reserved.
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
 * @copyright  Copyright (c) Nicholas J Humfrey
 * @license    https://www.opensource.org/licenses/bsd-license.php
 */

/**
 * Utility class for parsing XML documents
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) Nicholas J Humfrey
 * @license    https://www.opensource.org/licenses/bsd-license.php
 */
class XMLParser extends \XMLReader
{
    /** Array containing list of element names for current path */
    public $path = array();

    /** Callback to call when a new element tag starts */
    public $startElementCallback = null;

    /** Callback to call when a element tag ends */
    public $endElementCallback = null;

    /** Callback to call when text or cdata is encountered */
    public $textCallback = null;

    /** Callback to call when significant whitespace is encountered */
    public $whitespaceCallback = null;


    /** Parse an XML string. Calls the callback methods
     *  when various nodes of an XML document are encountered
     */
    public function parse($xml)
    {
        $this->xml($xml);
        $this->path = array();

        while ($this->read()) {
            switch ($this->nodeType) {
                case \XMLReader::ELEMENT:
                    $this->path[] = $this->name;
                    if ($this->startElementCallback) {
                        call_user_func($this->startElementCallback, $this);
                    }
                    if ($this->isEmptyElement) {
                        array_pop($this->path);
                    }
                    break;

                case \XMLReader::END_ELEMENT:
                    if ($this->endElementCallback) {
                        call_user_func($this->endElementCallback, $this);
                    }
                    array_pop($this->path);
                    break;

                case \XMLReader::TEXT:
                case \XMLReader::CDATA:
                    if ($this->textCallback) {
                        call_user_func($this->textCallback, $this);
                    }
                    break;

                case \XMLReader::SIGNIFICANT_WHITESPACE:
                    if ($this->whitespaceCallback) {
                        call_user_func($this->whitespaceCallback);
                    }
                    break;
            }
        }

        $this->close();
    }

    /** Returns the current path in the XML document as a string with slashes
     */
    public function path()
    {
        return implode('/', $this->path);
    }

    /** Returns the current element depth of the path in the XML document
     */
    public function depth()
    {
        return count($this->path);
    }
}
