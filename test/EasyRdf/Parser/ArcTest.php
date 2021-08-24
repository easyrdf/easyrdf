<?php
namespace EasyRdf\Parser;

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

use EasyRdf\Graph;
use EasyRdf\TestCase;

require_once dirname(dirname(__DIR__)).
             DIRECTORY_SEPARATOR.'TestHelper.php';

class ArcTest extends TestCase
{
    /** @var Arc */
    protected $parser = null;
    /** @var Graph */
    protected $graph = null;
    protected $data = null;

    public function setUp(): void
    {
        if (class_exists('ARC2')) {
            $this->parser = new Arc();
            $this->graph = new Graph();
            $this->data = readFixture('foaf.rdf');
        } else {
            $this->markTestSkipped(
                "ARC2 library is not available."
            );
        }
    }

    public function testParseRdfXml()
    {
        $count = $this->parser->parse(
            $this->graph,
            $this->data,
            'rdfxml',
            'http://www.example.com/joe/foaf.rdf'
        );
        $this->assertSame(14, $count);

        $joe = $this->graph->resource('http://www.example.com/joe#me');
        $this->assertNotNull($joe);
        $this->assertClass('EasyRdf\Resource', $joe);
        $this->assertSame('http://www.example.com/joe#me', $joe->getUri());

        $name = $joe->get('foaf:name');
        $this->assertNotNull($name);
        $this->assertClass('EasyRdf\Literal', $name);
        $this->assertStringEquals('Joe Bloggs', $name);
        $this->assertSame('en', $name->getLang());
        $this->assertSame(null, $name->getDatatype());

        $foaf = $this->graph->resource('http://www.example.com/joe/foaf.rdf');
        $this->assertNotNull($foaf);
        $this->assertStringEquals("Joe Bloggs' FOAF File", $foaf->label());
    }

    public function testParseUnsupportedFormat()
    {
        $this->setExpectedException(
            'EasyRdf\Exception',
            'EasyRdf\Parser\Arc does not support: unsupportedformat'
        );
        $this->parser->parse(
            $this->graph,
            $this->data,
            'unsupportedformat',
            null
        );
    }
}
