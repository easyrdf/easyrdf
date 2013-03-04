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

class Examples_FoafinfoTest extends EasyRdf_TestCase
{
    public function testNoParams()
    {
        $output = executeExample('foafinfo.php');
        $this->assertContains('<title>EasyRdf FOAF Info Example</title>', $output);
        $this->assertContains('<h1>EasyRdf FOAF Info Example</h1>', $output);
        $this->assertContains(
            '<input type="text" name="uri" id="uri" value="http://njh.me/foaf.rdf" size="50" />',
            $output
        );
    }

    public function testNjh()
    {
        $output = executeExample(
            'foafinfo.php',
            array('uri' => 'http://njh.me/foaf.rdf')
        );

        $this->assertContains('<title>EasyRdf FOAF Info Example</title>', $output);
        $this->assertContains('<h1>EasyRdf FOAF Info Example</h1>', $output);
        $this->assertContains("<dt>Name:</dt><dd>Nicholas J Humfrey</dd>", $output);
        $this->assertContains(
            "<dt>Homepage:</dt><dd><a href=\"http://www.aelius.com/njh/\">http://www.aelius.com/njh/</a></dd>",
            $output
        );

        $this->assertContains("<h2>Known Persons</h2>", $output);
        $this->assertContains(">Patrick Sinclair</a></li>", $output);
        $this->assertContains(">Yves Raimond</a></li>", $output);

        $this->assertContains("<h2>Interests</h2>", $output);
        $this->assertContains(">RDF</a></li>", $output);
    }
}
