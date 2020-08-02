<?php
namespace EasyRdf\Examples;

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009-2014 Nicholas J Humfrey.  All rights reserved.
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
 * @copyright  Copyright (c) 2009-2014 Nicholas J Humfrey
 * @license    https://www.opensource.org/licenses/bsd-license.php
 */

require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'TestHelper.php';

class GraphdirectTest extends \EasyRdf\TestCase
{
    public function test()
    {
        $output = executeExample('graph_direct.php');
        $this->assertContains('<title>Example of using EasyRdf\\Graph directly</title>', $output);

        $this->assertContains('<b>Name:</b> Joe Bloggs <br />', $output);
        $this->assertContains('<b>Names:</b> Joe Bloggs Joseph Bloggs <br />', $output);

        $this->assertContains('<b>Label:</b> Nick <br />', $output);
        $this->assertContains(
            '<b>Properties:</b> rdf:type, foaf:name, rdfs:label <br />',
            $output
        );
        $this->assertContains(
            '<b>PropertyUris:</b> http://www.w3.org/1999/02/22-rdf-syntax-ns#type, '.
            'http://xmlns.com/foaf/0.1/name, http://www.w3.org/2000/01/rdf-schema#label <br />',
            $output
        );
        $this->assertContains(
            '<b>People:</b> http://example.com/joe, http://njh.me/ <br />',
            $output
        );
        $this->assertContains('<b>Unknown:</b>  <br />', $output);
    }
}
