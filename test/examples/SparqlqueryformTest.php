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

class Examples_SparqlqueryformTest extends EasyRdf_TestCase
{
    public function testNoParams()
    {
        $output = executeExample('sparql_queryform.php');
        $this->assertContains('<title>EasyRdf SPARQL Query Form</title>', $output);
        $this->assertContains('<h1>EasyRdf SPARQL Query Form</h1>', $output);
        $this->assertContains('PREFIX foaf: &lt;http://xmlns.com/foaf/0.1/&gt;', $output);
    }

    public function testDbpediaCountries()
    {
        $output = executeExample(
            'sparql_queryform.php',
            array(
                'endpoint' => 'http://dbpedia.org/sparql',
                'query' =>
                    'PREFIX dbo: <http://dbpedia.org/ontology/> '.
                    'SELECT * WHERE {'.
                    '  ?country rdf:type dbo:Country . '.
                    '  ?country rdfs:label ?label .'.
                    '  ?country dc:subject category:Member_states_of_the_United_Nations .'.
                    '  FILTER ( lang(?label) = "en" ) '.
                    '} ORDER BY ?label LIMIT 100'
            )
        );
        $this->assertContains('>http://dbpedia.org/resource/China</a>', $output);
        $this->assertContains('>&quot;China&quot;@en</span>', $output);
    }

    public function testDbpediaCountriesText()
    {
        $output = executeExample(
            'sparql_queryform.php',
            array(
                'endpoint' => 'http://dbpedia.org/sparql',
                'query' =>
                    'PREFIX dbo: <http://dbpedia.org/ontology/> '.
                    'SELECT * WHERE {'.
                    '  ?country rdf:type dbo:Country . '.
                    '  ?country rdfs:label ?label .'.
                    '  ?country dc:subject category:Member_states_of_the_United_Nations .'.
                    '  FILTER ( lang(?label) = "en" ) '.
                    '} ORDER BY ?label LIMIT 100',
                'text' => 1
            )
        );

        $this->assertContains('| http://dbpedia.org/resource/China', $output);
        $this->assertContains('| &quot;China&quot;@en', $output);
    }
}
