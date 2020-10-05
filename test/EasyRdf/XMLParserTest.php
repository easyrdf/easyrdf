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

require_once dirname(__DIR__).DIRECTORY_SEPARATOR.'TestHelper.php';


class XMLParserTest extends TestCase
{
    public $result = array();

    public function testParseNoop()
    {
        $parser = new XMLParser();
        $parser->parse('<html><body>Body Text</body></html>');
        $this->assertTrue(true, "Sucessfully parsed XML");
    }

    public function testElementDepth()
    {
        $parser = new XMLParser();
        $this->result = array();
        $parser->startElementCallback = function ($parser) {
            $this->result[$parser->path()] = $parser->depth();
        };
        $parser->parse('<html><head /><body>Body <b>Text</b></body><tail /></html>');
        $this->assertSame(
            array(
                'html' => 1,
                'html/head' => 2,
                'html/body' => 2,
                'html/body/b' => 3,
                'html/tail' => 2
            ),
            $this->result
        );
    }

    public function testTextArray()
    {
        $parser = new XMLParser();
        $this->result = array();
        $parser->textCallback = function ($parser) {
            if ($parser->depth() == 2) {
                $name = end($parser->path);
                $this->result[$name] = $parser->value;
            }
        };
        $parser->parse('<root><a>Hello</a><b>World</b></root>');
        $this->assertSame(
            array(
                'a' => 'Hello',
                'b' => 'World'
            ),
            $this->result
        );
    }
}
