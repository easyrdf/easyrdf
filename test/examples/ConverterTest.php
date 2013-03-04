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

class Examples_ConverterTest extends EasyRdf_TestCase
{
    public function testNoParams()
    {
        $output = executeExample('converter.php');
        $this->assertContains('<title>EasyRdf Converter</title>', $output);
        $this->assertContains('<h1>EasyRdf Converter</h1>', $output);
        $this->assertContains('<option value="ntriples">N-Triples</option>', $output);
        $this->assertContains('<option value="turtle">Turtle Terse RDF Triple Language</option>', $output);
        $this->assertContains('<option value="rdfxml">RDF/XML</option>', $output);
    }

    public function testConvertRdfXmlToNtriples()
    {
        $output = executeExample(
            'converter.php',
            array(
                'data' =>
                    '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"'.
                    '         xmlns:dc="http://purl.org/dc/elements/1.1/">'.
                    ' <rdf:Description rdf:about="http://www.w3.org/">'.
                    '  <dc:title>World Wide Web Consortium</dc:title>'.
                    ' </rdf:Description>'.
                    '</rdf:RDF>',
                'uri' => 'http://example.com/',
                'input_format' => 'guess',
                'output_format' => 'ntriples'
            )
        );

        $this->assertContains('<title>EasyRdf Converter</title>', $output);
        $this->assertContains('<h1>EasyRdf Converter</h1>', $output);
        $this->assertContains(
            '&lt;http://www.w3.org/&gt; '.
            '&lt;http://purl.org/dc/elements/1.1/title&gt; '.
            '&quot;World Wide Web Consortium&quot; .',
            $output
        );
    }

    public function testConvertTurtle()
    {
        $output = executeExample(
            'converter.php',
            array(
                'uri' => 'http://www.w3.org/TR/turtle/examples/example1.ttl',
                'input_format' => 'guess',
                'output_format' => 'ntriples'
            )
        );

        $this->assertContains('<title>EasyRdf Converter</title>', $output);
        $this->assertContains('<h1>EasyRdf Converter</h1>', $output);
        $this->assertContains(
            '&lt;http://www.w3.org/TR/rdf-syntax-grammar&gt; '.
            '&lt;http://purl.org/dc/elements/1.1/title&gt; '.
            '&quot;RDF/XML Syntax Specification (Revised)&quot; .',
            $output
        );
        $this->assertContains(
            '&lt;http://www.w3.org/TR/rdf-syntax-grammar&gt; '.
            '&lt;http://example.org/stuff/1.0/editor&gt; _:genid1 .',
            $output
        );
        $this->assertContains(
            '_:genid1 &lt;http://example.org/stuff/1.0/fullname&gt; '.
            '&quot;Dave Beckett&quot; .',
            $output
        );
        $this->assertContains(
            '_:genid1 &lt;http://example.org/stuff/1.0/homePage&gt; '.
            '&lt;http://purl.org/net/dajobe/&gt; .',
            $output
        );
    }

    public function testConvertTurtleRaw()
    {
        $output = executeExample(
            'converter.php',
            array(
                'uri' => 'http://www.w3.org/TR/turtle/examples/example1.ttl',
                'input_format' => 'guess',
                'output_format' => 'ntriples',
                'raw' => 1
            )
        );

        $this->assertSame(
            "<http://www.w3.org/TR/rdf-syntax-grammar> <http://purl.org/dc/elements/1.1/title> ".
            "\"RDF/XML Syntax Specification (Revised)\" .\n".
            "<http://www.w3.org/TR/rdf-syntax-grammar> <http://example.org/stuff/1.0/editor> _:genid1 .\n".
            "_:genid1 <http://example.org/stuff/1.0/fullname> \"Dave Beckett\" .\n".
            "_:genid1 <http://example.org/stuff/1.0/homePage> <http://purl.org/net/dajobe/> .\n",
            $output
        );
    }
}
