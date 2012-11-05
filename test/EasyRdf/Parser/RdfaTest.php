<?php

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2012 Nicholas J Humfrey.  All rights reserved.
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
 * @copyright  Copyright (c) 2012 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 * @version    $Id$
 */

require_once dirname(dirname(dirname(__FILE__))).
             DIRECTORY_SEPARATOR.'TestHelper.php';

require_once 'EasyRdf/Parser/Rdfa.php';
require_once 'EasyRdf/Serialiser/NtriplesArray.php';

class EasyRdf_Parser_RdfaTest extends EasyRdf_TestCase
{
    protected $_parser = null;
    protected $_graph = null;
    protected $_data = null;

    public function setUp()
    {
        $this->_rdfaParser = new EasyRdf_Parser_Rdfa();
        $this->_ntriplesParser = new EasyRdf_Parser_Ntriples();
        $this->_baseUri = 'http://rdfa.info/test-suite/test-cases/rdfa1.1/xhtml5/';
    }


    protected function parseRdfa($filename)
    {
        $graph = new EasyRdf_Graph();
        $this->_rdfaParser->parse(
            $graph,
            readFixture($filename),
            'rdfa',
            $this->_baseUri . basename($filename)
        );
        return $graph->serialise('ntriples-array');
    }

    protected function parseNtriples($filename)
    {
        $graph = new EasyRdf_Graph();
        $this->_ntriplesParser->parse(
            $graph,
            readFixture($filename),
            'ntriples',
            $this->_baseUri . basename($filename)
        );
        return $graph->serialise('ntriples-array');
    }

    protected function rdfaTestCase($name, $message)
    {
        $this->assertEquals(
            $this->parseNtriples("rdfa/$name.nt"),
            $this->parseRdfa("rdfa/$name.xhtml"),
            $message
        );
    }

    public function testCase0001()
    {
        $this->rdfaTestCase('0001', 'Predicate establishment with @property');
    }

    public function testCase0006()
    {
        $this->rdfaTestCase('0006', '@rel and @rev');
    }

    public function testCase0007()
    {
        $this->rdfaTestCase('0007', '@rel, @rev, @property, @content');
    }

    public function testCase0008()
    {
        $this->rdfaTestCase('0008', 'empty string @about');
    }

    public function testCase0009()
    {
        $this->rdfaTestCase('0009', '@rev');
    }

    public function testCase0010()
    {
        $this->rdfaTestCase('0010', '@rel, @rev, @href');
    }

    public function testCase0014()
    {
        $this->rdfaTestCase('0014', '@datatype, xsd:integer');
    }

    public function testCase0015()
    {
        $this->rdfaTestCase('0015', 'meta and link');
    }

    public function testCase0017()
    {
        $this->rdfaTestCase('0017', 'Related blanknodes');
    }

    public function testCase0018()
    {
        $this->rdfaTestCase('0018', '@rel for predicate');
    }

    public function testCase0019()
    {
        $this->rdfaTestCase('0019', '@about for subject');
    }

    public function testCase0020()
    {
        $this->rdfaTestCase('0020', 'Inheriting @about for subject');
    }

    public function testCase0021()
    {
        $this->rdfaTestCase('0021', 'Subject inheritance with no @about');
    }

    public function testCase0023()
    {
        $this->rdfaTestCase('0023', '@id does not generate subjects');
    }

    public function testCase0025()
    {
        $this->rdfaTestCase('0025', 'simple chaining test');
    }

    public function testCase0026()
    {
        $this->rdfaTestCase('0026', '@content');
    }

    public function testCase0027()
    {
        $this->rdfaTestCase('0027', '@content, ignore element content');
    }

    public function testCase0029()
    {
        $this->rdfaTestCase('0029', 'markup stripping with @datatype');
    }

    public function testCase0030()
    {
        $this->rdfaTestCase('0030', 'omitted @about');
    }

    public function testCase0031()
    {
        $this->rdfaTestCase('0031', 'simple @resource');
    }

    public function testCase0032()
    {
        $this->rdfaTestCase('0032', '@resource overrides @href');
    }

    public function testCase0033()
    {
        $this->rdfaTestCase('0033', 'simple chaining test with bNode');
    }

    public function testCase0034()
    {
        $this->rdfaTestCase('0034', 'simple img[@src] test');
    }

    public function testCase0035()
    {
        $this->rdfaTestCase('0035', '@src/@href test');
    }

    public function testCase0036()
    {
        $this->rdfaTestCase('0036', '@src/@resource test');
    }

    public function testCase0037()
    {
        $this->rdfaTestCase('0037', '@src/@href/@resource test');
    }

    public function testCase0038()
    {
        $this->rdfaTestCase('0038', '@rev - img[@src] test');
    }

    public function testCase0039()
    {
        $this->rdfaTestCase('0039', '@rev - @src/@href test');
    }

    public function testCase0041()
    {
        $this->rdfaTestCase('0041', '@rev - @src/@href/@resource test');
    }

    public function testCase0048()
    {
        $this->rdfaTestCase('0048', '@typeof with @about and @rel present, no @resource');
    }

    public function testCase0049()
    {
        $this->rdfaTestCase('0049', '@typeof with @about, no @rel or @resource');
    }

    public function testCase0050()
    {
        $this->rdfaTestCase('0050', '@typeof without anything else');
    }

    public function testCase0051()
    {
        $this->rdfaTestCase('0051', '@typeof with a single @property');
    }

    public function testCase0052()
    {
        $this->rdfaTestCase('0052', '@typeof with @resource and nothing else');
    }

    public function testCase0053()
    {
        $this->rdfaTestCase('0053', '@typeof with @resource and nothing else, with a subelement');
    }

    public function testCase0054()
    {
        $this->rdfaTestCase('0054', 'multiple @property');
    }

    public function testCase0055()
    {
        $this->rdfaTestCase('0055', 'multiple @rel');
    }

    public function testCase0056()
    {
        $this->rdfaTestCase('0056', '@typeof applies to @about on same element with hanging rel');
    }

    public function testCase0057()
    {
        $this->rdfaTestCase('0057', 'hanging @rel creates multiple triples');
    }

    public function testCase0059()
    {
        $this->rdfaTestCase('0059', 'multiple hanging @rels with multiple children');
    }

    public function testCase0060()
    {
        $this->rdfaTestCase('0060', 'UTF-8 conformance');
    }

    public function testCase0063()
    {
        $this->rdfaTestCase('0063', '@rel in head using reserved XHTML value and empty-prefix CURIE syntax');
    }

    public function testCase0064()
    {
        $this->rdfaTestCase('0064', '@about with safe CURIE');
    }

    public function testCase0065()
    {
        $this->rdfaTestCase('0065', '@rel with safe CURIE');
    }

    public function testCase0066()
    {
        $this->rdfaTestCase('0066', '@about with @typeof in the head');
    }

    public function testCase0067()
    {
        $this->rdfaTestCase('0067', '@property in the head');
    }

    public function testCase0068()
    {
        $this->rdfaTestCase('0068', 'Relative URI in @about');
    }

    public function testCase0069()
    {
        $this->rdfaTestCase('0069', 'Relative URI in @href');
    }

    public function testCase0070()
    {
        $this->rdfaTestCase('0070', 'Relative URI in @resource');
    }

    public function testCase0071()
    {
        $this->rdfaTestCase('0071', 'No explicit @about');
    }

    public function testCase0072()
    {
        $this->rdfaTestCase('0072', 'Relative URI in @about (with XHTML base in head)');
    }

    public function testCase0073()
    {
        $this->rdfaTestCase('0073', 'Relative URI in @resource (with XHTML base in head)');
    }

    public function testCase0074()
    {
        $this->rdfaTestCase('0074', 'Relative URI in @href (with XHTML base in head)');
    }

    public function testCase0075()
    {
        $this->rdfaTestCase('0075', 'Reserved word \'license\' in @rel with no explizit @about');
    }

    public function testCase0079()
    {
        $this->rdfaTestCase('0079', '@resource and @href in completing incomplete triples');
    }

    public function testCase0080()
    {
        $this->rdfaTestCase('0080', '@about overrides @resource in incomplete triples');
    }

    public function testCase0083()
    {
        $this->rdfaTestCase('0083', 'multiple ways of handling incomplete triples (merged)');
    }

// FIXME
//     public function testCase0084()
//     {
//         $this->markTestSkipped("The bnode code needs fixing");
//         $this->rdfaTestCase('0084', 'multiple ways of handling incomplete triples, this time with both @rel and @rev');
//     }

    public function testCase0085()
    {
        $this->rdfaTestCase('0085', '@resource and @href in completing incomplete triples');
    }

    public function testCase0087()
    {
        $this->rdfaTestCase('0087', 'All reserved XHTML @rel values (with :xxx)');
    }

    # FIXME: RDFa parser is working but graph comparison isn't
    public function testCase0088()
    {
        $this->markTestSkipped("Graph comparison isn't working");
        $this->rdfaTestCase('0088', 'Interpretation of the CURIE "_:"');
    }

    public function testCase0089()
    {
        $this->rdfaTestCase('0089', '@src sets a new subject (@typeof)');
    }

    public function testCase0091()
    {
        $this->rdfaTestCase('0091', 'Non-reserved, un-prefixed CURIE in @property');
    }

    public function testCase0093()
    {
        $this->rdfaTestCase('0093', 'Tests XMLLiteral content with explicit @datatype (user-data-typed literal)');
    }

    public function testCase0099()
    {
        $this->rdfaTestCase('0099', 'Preservation of white space in literals');
    }

    public function testCase0104()
    {
        $this->rdfaTestCase('0104', 'rdf:value');
    }

// FIXME:
//     public function testCase0106()
//     {
//         $this->rdfaTestCase('0106', 'chaining with empty value in inner @rel');
//     }

    public function testCase0107()
    {
        $this->rdfaTestCase('0107', 'no garbage collecting bnodes');
    }

    public function testCase0110()
    {
        $this->rdfaTestCase('0110', 'bNode generated even though no nested @about exists');
    }

    public function testCase0111()
    {
        $this->rdfaTestCase('0111', 'two bNodes generated after three levels of nesting');
    }

    public function testCase0112()
    {
        $this->rdfaTestCase('0112', 'plain literal with datatype=\"\"');
    }

    public function testCase0114()
    {
        $this->rdfaTestCase('0114', 'Relative URI dot-segment removal');
    }

    public function testCase0115()
    {
        $this->rdfaTestCase('0115', 'XML Entities must be supported by RDFa parser');
    }

    public function testCase0117()
    {
        $this->rdfaTestCase('0117', 'Fragment identifiers stripped from BASE');
    }

    public function testCase0118()
    {
        $this->rdfaTestCase('0118', 'empty string \"\" is not equivalent to NULL - @about');
    }

// FIXME:
//     public function testCase0119()
//     {
//         $this->rdfaTestCase('0119', '\"[prefix:]\" CURIE format is valid');
//     }

// FIXME:
//     public function testCase0120()
//     {
//         $this->rdfaTestCase('0120', '\"[:]\" CURIE format is valid');
//     }

// FIXME:
//     public function testCase0121()
//     {
//         $this->rdfaTestCase('0121', '\"[]\" is a valid safe CURIE');
//     }

// FIXME:
//     public function testCase0122()
//     {
//         $this->rdfaTestCase('0122', 'resource=\"[]\" does not set the object');
//     }

// FIXME:
//     public function testCase0126()
//     {
//         $this->rdfaTestCase('0126', 'Multiple @typeof values');
//     }

    public function testCase0131()
    {
        $this->rdfaTestCase('0131', 'Whitespace alternatives in attributes');
    }

// FIXME:
//     public function testCase0134()
//     {
//         $this->rdfaTestCase('0134', 'Uppercase reserved words');
//     }

// FIXME:
//     public function testCase0140()
//     {
//         $this->rdfaTestCase('0140', 'Blank nodes identifiers are not allowed as predicates');
//     }

    public function testCase0147()
    {
        $this->rdfaTestCase('0147', 'xmlns prefix \'xmlzzz\' (reserved)');
    }

    public function testCase0174()
    {
        $this->rdfaTestCase('0174', 'Support single character prefix in CURIEs');
    }

    public function testCase0175()
    {
        $this->rdfaTestCase('0175', 'IRI for @property is allowed');
    }

    public function testCase0176()
    {
        $this->rdfaTestCase('0176', 'IRI for @rel and @rev is allowed');
    }

    public function testCase0177()
    {
        $this->rdfaTestCase('0177', 'Test @prefix');
    }

    public function testCase0178()
    {
        $this->rdfaTestCase('0178', 'Test @prefix with multiple mappings');
    }

    public function testCase0179()
    {
        $this->rdfaTestCase('0179', 'Test @prefix vs @xmlns priority');
    }

    public function testCase0180()
    {
        $this->rdfaTestCase('0180', 'Test @prefix with empty mapping');
    }

    public function testCase0181()
    {
        $this->rdfaTestCase('0181', 'Test default XHTML vocabulary');
    }

    public function testCase0182()
    {
        $this->rdfaTestCase('0182', 'Test prefix locality');
    }

// FIXME: not sure how to fix this test yet
//     public function testCase0183()
//     {
//         $this->rdfaTestCase('0183', 'Test @xmlns redefines @prefix');
//     }

    public function testCase0186()
    {
        $this->rdfaTestCase('0186', '@vocab after subject declaration');
    }

    public function testCase0187()
    {
        $this->rdfaTestCase('0187', '@vocab redefinition');
    }

    public function testCase0188()
    {
        $this->rdfaTestCase('0188', '@vocab only affects predicates');
    }

    public function testCase0189()
    {
        $this->rdfaTestCase('0189', '@vocab overrides default term');
    }

// FIXME:
//     public function testCase0190()
//     {
//         $this->rdfaTestCase('0190', 'Test term case insensitivity');
//     }

    public function testCase0196()
    {
        $this->rdfaTestCase('0196', 'Test process explicit XMLLiteral');
    }

// FIXME:
//     public function testCase0197()
//     {
//         $this->rdfaTestCase('0197', 'Test TERMorCURIEorAbsURI requires an absolute URI');
//     }

// FIXME:
//     public function testCase0198()
//     {
//         $this->rdfaTestCase('0198', 'datatype XMLLiteral with other embedded RDFa');
//     }

    public function testParseUnsupportedFormat()
    {
        $this->setExpectedException(
            'EasyRdf_Exception',
            'EasyRdf_Parser_Rdfa does not support: unsupportedformat'
        );
        $graph = new EasyRdf_Graph();
        $this->_rdfaParser->parse(
            $graph, 'data', 'unsupportedformat', null
        );
    }
}
