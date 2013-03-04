<?php

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009-2013 Nicholas J Humfrey.  All rights reserved.
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
 * @copyright  Copyright (c) 2009-2013 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */

require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'TestHelper.php';

class Examples_FoafmakerTest extends EasyRdf_TestCase
{
    public function testNoParams()
    {
        $output = executeExample('foafmaker.php');
        $this->assertContains('<title>EasyRdf FOAF Maker Example</title>', $output);
        $this->assertContains('<h1>EasyRdf FOAF Maker Example</h1>', $output);
    }

    public function testJoeBloggs()
    {
        $output = executeExample(
            'foafmaker.php',
            array(
                'uri' => 'http://www.example.com/joe#me',
                'title' => 'Mr',
                'given_name' => 'Joe',
                'family_name' => 'Bloggs',
                'email' => 'joe@example.com',
                'nickname' => 'Joe',
                'homepage' => 'http://www.example.com/joe/',
                'person_1' => 'http://www.example.com/fred#me',
                'person_2' => 'http://www.example.com/alice#me',
                'person_3' => '',
                'person_4' => '',
                'format' => 'turtle'
            )
        );

        $this->assertContains('<title>EasyRdf FOAF Maker Example</title>', $output);
        $this->assertContains('<h1>EasyRdf FOAF Maker Example</h1>', $output);
        $this->assertContains(
            "@prefix foaf: &lt;http://xmlns.com/foaf/0.1/&gt; .\n\n".
            "&lt;http://www.example.com/joe#me&gt;\n".
            "  a foaf:Person ;\n".
            "  foaf:name &quot;Mr Joe Bloggs&quot; ;\n".
            "  foaf:mbox &lt;mailto:joe@example.com&gt; ;\n".
            "  foaf:homepage &lt;http://www.example.com/joe/&gt; ;\n".
            "  foaf:title &quot;Mr&quot; ;\n".
            "  foaf:givenname &quot;Joe&quot; ;\n".
            "  foaf:family_name &quot;Bloggs&quot; ;\n".
            "  foaf:nick &quot;Joe&quot; ;\n".
            "  foaf:knows &lt;http://www.example.com/fred#me&gt;,".
            " &lt;http://www.example.com/alice#me&gt; .\n",
            $output
        );
    }
}
