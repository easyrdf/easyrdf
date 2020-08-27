<?php
namespace EasyRdf\Examples;

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009-2020 Nicholas J Humfrey.  All rights reserved.
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
 * @copyright  Copyright (c) 2009-2020 Nicholas J Humfrey
 * @license    https://www.opensource.org/licenses/bsd-license.php
 */

require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'TestHelper.php';

class HttpgetTest extends \EasyRdf\TestCase
{
    public function testNoParams()
    {
        $output = executeExample('httpget.php');
        $this->assertContains('<title>Test EasyRdf HTTP Client Get</title>', $output);
        $this->assertContains('<h1>Test EasyRdf HTTP Client Get</h1>', $output);
        $this->assertContains(
            '<input type="text" name="uri" id="uri" value="http://tomheath.com/id/me" size="50" />',
            $output
        );
        $this->assertContains(
            '<option value="application/rdf+xml">application/rdf+xml</option>',
            $output
        );
        $this->assertContains(
            '<option value="text/html">text/html</option>',
            $output
        );
    }

    public function testHtml()
    {
        $output = executeExample(
            'httpget.php',
            array(
                'uri' => 'http://tomheath.com/id/me',
                'accept' => 'text/html'
            )
        );
        $this->assertContains('<title>Test EasyRdf HTTP Client Get</title>', $output);
        $this->assertContains('<h1>Test EasyRdf HTTP Client Get</h1>', $output);
        $this->assertContains('<b>Content-type</b>: text/html', $output);
        $this->assertContains('&lt;h1&gt;Home - Tom Heath&lt;/h1&gt;', $output);
    }

    public function testRdfXml()
    {
        $output = executeExample(
            'httpget.php',
            array(
                'uri' => 'http://tomheath.com/id/me',
                'accept' => 'application/rdf+xml'
            )
        );
        $this->assertContains('<title>Test EasyRdf HTTP Client Get</title>', $output);
        $this->assertContains('<h1>Test EasyRdf HTTP Client Get</h1>', $output);
        $this->assertContains('<b>Content-type</b>: application/rdf+xml', $output);
        $this->assertContains(
            '&lt;foaf:Person rdf:about=&quot;http://tomheath.com/id/me&quot;&gt;',
            $output
        );
    }
}
