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

require_once dirname(dirname(dirname(__FILE__))).
    DIRECTORY_SEPARATOR.'TestHelper.php';

class JsonLdTest extends EasyRdf_TestCase
{
    /** @var EasyRdf_Serialiser_JsonLd */
    protected $serialiser = null;

    /** @var EasyRdf_Graph */
    protected $graph = null;

    public function setUp()
    {
        if (PHP_MAJOR_VERSION < 5 or (PHP_MAJOR_VERSION >= 5 and PHP_MINOR_VERSION < 3)) {
            $this->markTestSkipped("JSON-LD support requires PHP 5.3+");
        }

        if (!class_exists('\ML\JsonLD\JsonLD')) {
            $this->markTestSkipped('"ml/json-ld" dependency is not installed');
        }

        $this->graph = new EasyRdf_Graph('http://example.com/');
        $this->serialiser = new EasyRdf_Serialiser_JsonLd();


        $joe = $this->graph->resource('http://www.example.com/joe#me', 'foaf:Person');
        $joe->set('foaf:name', new EasyRdf_Literal('Joe Bloggs', 'en'));
        $joe->set('foaf:age', 59);

        $project = $this->graph->newBNode();
        $project->add('foaf:name', 'Project Name');

        $joe->add('foaf:project', $project);
    }

    public function testSerialiseJsonLd()
    {
        $serialised = $this->serialiser->serialise($this->graph, 'jsonld');

        $doc = \ML\JsonLD\JsonLD::getDocument($serialised);
        $graph = $doc->getGraph();
        $node = $graph->getNode('http://www.example.com/joe#me');

        $this->assertEquals(
            'http://xmlns.com/foaf/0.1/Person',
            $node->getProperty('http://www.w3.org/1999/02/22-rdf-syntax-ns#type')->getId()
        );
        $this->assertEquals(
            59,
            $node->getProperty('http://xmlns.com/foaf/0.1/age')
        );
        $this->assertEquals(
            'Joe Bloggs',
            $node->getProperty('http://xmlns.com/foaf/0.1/name')->getValue()
        );
        $this->assertEquals(
            'Project Name',
            $node->getProperty('http://xmlns.com/foaf/0.1/project')->getProperty('http://xmlns.com/foaf/0.1/name')
        );
    }

    public function testSerialiseJsonLdOptions()
    {
        // Expanded form
        $string = $this->serialiser->serialise($this->graph, 'jsonld');
        $decoded = json_decode($string, true);

        $this->assertArrayNotHasKey('@graph', $decoded);
        $this->assertInternalType('array', $decoded[1]["http://xmlns.com/foaf/0.1/age"][0]);
        $this->assertSame(59, $decoded[1]["http://xmlns.com/foaf/0.1/age"][0]['@value']);
        $this->assertArrayNotHasKey('@type', $decoded[1]["http://xmlns.com/foaf/0.1/age"][0]);


        // Expanded form + explicit types
        $string = $this->serialiser->serialise($this->graph, 'jsonld', array('expand_native_types' => true));
        $decoded = json_decode($string, true);

        $this->assertArrayNotHasKey('@graph', $decoded);
        $this->assertInternalType('array', $decoded[1]["http://xmlns.com/foaf/0.1/age"][0]);
        $this->assertSame('59', $decoded[1]["http://xmlns.com/foaf/0.1/age"][0]['@value']);
        $this->assertSame('http://www.w3.org/2001/XMLSchema#integer', $decoded[1]["http://xmlns.com/foaf/0.1/age"][0]['@type']);


        // Compact form
        $string = $this->serialiser->serialise($this->graph, 'jsonld', array('compact' => true));
        $decoded = json_decode($string, true);

        $this->assertArrayHasKey('@graph', $decoded);
        $this->assertSame(59, $decoded['@graph'][1]["http://xmlns.com/foaf/0.1/age"]);


        // Compact form + explicit types
        $string = $this->serialiser->serialise($this->graph, 'jsonld', array('compact' => true, 'expand_native_types' => true));
        $decoded = json_decode($string, true);

        $this->assertArrayHasKey('@graph', $decoded);
        $this->assertSame('59', $decoded['@graph'][1]["http://xmlns.com/foaf/0.1/age"]['@value']);
        $this->assertSame('http://www.w3.org/2001/XMLSchema#integer', $decoded['@graph'][1]["http://xmlns.com/foaf/0.1/age"]['@type']);


        // Compact form + explicit types + context
        $ctx = new stdClass();
        $ctx->foaf = 'http://xmlns.com/foaf/0.1/';
        $ctx->xmls = 'http://www.w3.org/2001/XMLSchema#';

        $string = $this->serialiser->serialise($this->graph, 'jsonld', array('compact' => true, 'expand_native_types' => true, 'context' => $ctx));
        $decoded = json_decode($string, true);

        $this->assertArrayHasKey('@graph', $decoded);
        $this->assertSame('59', $decoded['@graph'][1]["foaf:age"]['@value']);
        $this->assertSame('xmls:integer', $decoded['@graph'][1]["foaf:age"]['@type']);
    }
}
