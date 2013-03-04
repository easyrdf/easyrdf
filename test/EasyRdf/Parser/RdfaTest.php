<?php

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2012-2013 Nicholas J Humfrey.  All rights reserved.
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
 * @copyright  Copyright (c) 2012-2013 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */

require_once dirname(dirname(dirname(__FILE__))).
             DIRECTORY_SEPARATOR.'TestHelper.php';

require_once 'EasyRdf/Parser/Rdfa.php';
require_once 'EasyRdf/Serialiser/NtriplesArray.php';

class EasyRdf_Parser_RdfaTest extends EasyRdf_TestCase
{
    protected $parser = null;
    protected $graph = null;
    protected $data = null;

    public function setUp()
    {
        $this->rdfaParser = new EasyRdf_Parser_Rdfa();
        $this->ntriplesParser = new EasyRdf_Parser_Ntriples();
        $this->baseUri = 'http://rdfa.info/test-suite/test-cases/rdfa1.1/xhtml5/';
    }


    protected function parseRdfa($filename)
    {
        $graph = new EasyRdf_Graph();
        $this->rdfaParser->parse(
            $graph,
            readFixture($filename),
            'rdfa',
            $this->baseUri . basename($filename)
        );
        return $graph->serialise('ntriples-array');
    }

    protected function parseNtriples($filename)
    {
        $graph = new EasyRdf_Graph();
        $this->ntriplesParser->parse(
            $graph,
            readFixture($filename),
            'ntriples',
            $this->baseUri . basename($filename)
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
        $this->rdfaTestCase('0075', 'Reserved word \'license\' in @rel with no explicit @about');
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

    public function testCase0084()
    {
        $this->rdfaTestCase('0084', 'multiple ways of handling incomplete triples, this time with both @rel and @rev');
    }

    public function testCase0085()
    {
        $this->rdfaTestCase('0085', '@resource and @href in completing incomplete triples');
    }

    public function testCase0087()
    {
        $this->rdfaTestCase('0087', 'All reserved XHTML @rel values (with :xxx)');
    }

    public function testCase0088()
    {
        $this->markTestSkipped("FIXME: Graph comparison isn't working");
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

    public function testCase0106()
    {
        $this->rdfaTestCase('0106', 'chaining with empty value in inner @rel');
    }

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
        $this->rdfaTestCase('0112', 'plain literal with datatype=""');
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
        $this->rdfaTestCase('0118', 'empty string "" is not equivalent to NULL - @about');
    }

    public function testCase0119()
    {
        $this->rdfaTestCase('0119', '"[prefix:]" CURIE format is valid');
    }

    public function testCase0120()
    {
        $this->rdfaTestCase('0120', '"[:]" CURIE format is valid');
    }

    public function testCase0121()
    {
        $this->rdfaTestCase('0121', '"[]" is a valid safe CURIE');
    }

    public function testCase0122()
    {
        $this->rdfaTestCase('0122', 'resource="[]" does not set the object');
    }

    public function testCase0126()
    {
        $this->rdfaTestCase('0126', 'Multiple @typeof values');
    }

    public function testCase0131()
    {
        $this->rdfaTestCase('0131', 'Whitespace alternatives in attributes');
    }

    public function testCase0134()
    {
        $this->rdfaTestCase('0134', 'Uppercase reserved words');
    }

    public function testCase0140()
    {
        $this->rdfaTestCase('0140', 'Blank nodes identifiers are not allowed as predicates');
    }

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

    public function testCase0183()
    {
        $this->rdfaTestCase('0183', 'Test @xmlns redefines @prefix');
    }

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

    public function testCase0190()
    {
        $this->rdfaTestCase('0190', 'Test term case insensitivity');
    }

    public function testCase0196()
    {
        $this->rdfaTestCase('0196', 'Test process explicit XMLLiteral');
    }

    public function testCase0197()
    {
        $this->rdfaTestCase('0197', 'Test TERMorCURIEorAbsURI requires an absolute URI');
    }

    public function testCase0198()
    {
        $this->rdfaTestCase('0198', 'datatype XMLLiteral with other embedded RDFa');
    }

    public function testCase0206()
    {
        $this->rdfaTestCase('0206', 'Usage of Initial Context');
    }

    public function testCase0207()
    {
        $this->rdfaTestCase('0207', 'Vevent using @typeof');
    }

    public function testCase0213()
    {
        $this->rdfaTestCase('0213', 'Datatype generation for a literal with XML content, version 1.1');
    }

    public function testCase0214()
    {
        $this->rdfaTestCase('0214', 'Root element has implicit @about=""');
    }

    public function testCase0216()
    {
        $this->rdfaTestCase('0216', 'Proper character encoding detection in spite of large headers');
    }

    public function testCase0217()
    {
        $this->rdfaTestCase('0217', '@vocab causes rdfa:usesVocabulary triple to be added');
    }

    public function testCase0218()
    {
        $this->rdfaTestCase('0218', '@inlist to create empty list');
    }

    public function testCase0219()
    {
        $this->rdfaTestCase('0219', '@inlist with literal');
    }

    public function testCase0220()
    {
        $this->rdfaTestCase('0220', '@inlist with IRI');
    }

    public function testCase0221()
    {
        $this->rdfaTestCase('0221', '@inlist with hetrogenious membership');
    }

    public function testCase0222()
    {
        $this->markTestSkipped("FIXME: Graph comparison isn't working");
        $this->rdfaTestCase('0222', '@inlist with multi-level elements');
    }

    public function testCase0223()
    {
        $this->rdfaTestCase('0223', '@inlist with non-list property');
    }

    public function testCase0224()
    {
        $this->markTestSkipped("FIXME: need to implement @inlist");
        $this->rdfaTestCase('0224', '@inlist hanging @rel');
    }

    public function testCase0225()
    {
        $this->rdfaTestCase('0225', '@inlist on different elements with same subject');
    }

    public function testCase0226()
    {
        $this->markTestSkipped("FIXME: Graph comparison isn't working");
        $this->rdfaTestCase('0226', 'confusion between multiple implicit collections (resource)');
    }

    public function testCase0227()
    {
        $this->markTestSkipped("FIXME: Graph comparison isn't working");
        $this->rdfaTestCase('0227', 'confusion between multiple implicit collections (about)');
    }

    public function testCase0228()
    {
        $this->rdfaTestCase('0228', '1.1 alternate for test 0040: @rev - @src/@resource test');
    }

    public function testCase0229()
    {
        $this->rdfaTestCase('0229', 'img[@src] test with omitted @about');
    }

    public function testCase0230()
    {
        $this->rdfaTestCase('0230', '@src does not set a new subject (@rel/@href)');
    }

    public function testCase0231()
    {
        $this->rdfaTestCase('0231', 'Set image license information');
    }

    public function testCase0232()
    {
        $this->rdfaTestCase(
            '0232',
            '@typeof with @rel present, no @href, @resource, or @about (1.1 behavior of 0046);'
        );
    }

    public function testCase0233()
    {
        $this->rdfaTestCase('0233', '@typeof with @rel and @resource present, no @about (1.1 behavior of 0047)');
    }

    public function testCase0234()
    {
        $this->rdfaTestCase('0234', 'All defined HTML link relation values');
    }

    public function testCase0246()
    {
        $this->markTestSkipped("FIXME: Graph comparison isn't working");
        $this->rdfaTestCase('0246', 'hanging @rel creates multiple triples, @typeof permutation; RDFa 1.1 version');
    }

    public function testCase0247()
    {
        $this->markTestSkipped("FIXME: Graph comparison isn't working");
        $this->rdfaTestCase('0247', 'Multiple incomplete triples, RDFa 1.1version');
    }

    public function testCase0248()
    {
        $this->rdfaTestCase('0248', 'multiple ways of handling incomplete triples (with @rev); RDFa 1.1 version');
    }

    public function testCase0249()
    {
        $this->rdfaTestCase(
            '0249',
            'multiple ways of handling incomplete triples (with @rel and @rev); RDFa 1.1 version'
        );
    }

    public function testCase0250()
    {
        $this->rdfaTestCase('0250', 'Checking the right behaviour of @typeof with @about, in presence of @property');
    }

    public function testCase0251()
    {
        $this->rdfaTestCase('0251', 'lang');
    }

    public function testCase0252()
    {
        $this->rdfaTestCase('0252', 'lang inheritance');
    }

    public function testCase0253()
    {
        $this->rdfaTestCase('0253', 'plain literal with datatype="" and lang preservation');
    }

    public function testCase0254()
    {
        $this->rdfaTestCase('0254', '@datatype="" generates plain literal in presence of child nodes');
    }

    public function testCase0255()
    {
        $this->rdfaTestCase('0255', 'lang="" clears language setting');
    }

    public function testCase0256()
    {
        $this->rdfaTestCase('0256', 'lang and xml:lang on the same element');
    }

    public function testCase0257()
    {
        $this->rdfaTestCase(
            '0257',
            'element with @property and no child nodes generates  empty plain literal (HTML5 version of 0113)'
        );
    }

    public function testCase0258()
    {
        $this->rdfaTestCase('0258', 'The underscore character is not allowed as a prefix or in xmlns');
    }

    public function testCase0259()
    {
        $this->rdfaTestCase('0259', 'XML+RDFa Initial Context');
    }

    public function testCase0261()
    {
        $this->rdfaTestCase('0261', 'White space preservation in XMLLiteral');
    }

    public function testCase0262()
    {
        $this->rdfaTestCase(
            '0262',
            'Predicate establishment with @property, with white spaces before and after the attribute value'
        );
    }

    public function testCase0263()
    {
        $this->rdfaTestCase('0263', '@property appearing on the html element yields the base as the subject');
    }

    public function testCase0264()
    {
        $this->rdfaTestCase(
            '0264',
            '@property appearing on the head element gets the subject from <html>, ie, parent'
        );
    }

    public function testCase0265()
    {
        $this->rdfaTestCase(
            '0265',
            '@property appearing on the head element gets the subject from <html>, ie, parent'
        );
    }

    public function testCase0266()
    {
        $this->rdfaTestCase('0266', '@property without @content or @datatype, typed object set by @href and @typeof');
    }

    public function testCase0267()
    {
        $this->rdfaTestCase(
            '0267',
            '@property without @content or @datatype, typed object set by @resource and @typeof'
        );
    }

    public function testCase0268()
    {
        $this->rdfaTestCase('0268', '@property without @content or @datatype, typed object set by @src and @typeof');
    }

    public function testCase0269()
    {
        $this->rdfaTestCase(
            '0269',
            '@property appearing on the html element yields the base as the subject, also with @content'
        );
    }

    public function testCase0271()
    {
        $this->rdfaTestCase('0271', 'Use of @property in HEAD with explicit parent subject via @about');
    }

    public function testCase0272()
    {
        $this->rdfaTestCase('0272', 'time element with @datetime an xsd:date');
    }

    public function testCase0273()
    {
        $this->rdfaTestCase('0273', 'time element with @datetime an xsd:time');
    }

    public function testCase0274()
    {
        $this->rdfaTestCase('0274', 'time element with @datetime an xsd:dateTime');
    }

    public function testCase0275()
    {
        $this->rdfaTestCase('0275', 'time element with value an xsd:date');
    }

    public function testCase0276()
    {
        $this->rdfaTestCase('0276', 'time element with value an xsd:time');
    }

    public function testCase0277()
    {
        $this->rdfaTestCase('0277', 'time element with value an xsd:dateTime');
    }

    public function testCase0278()
    {
        $this->rdfaTestCase('0278', '@datetime overrides @content');
    }

    public function testCase0279()
    {
        $this->rdfaTestCase('0279', '@datatype used with @datetime overrides default datatype');
    }

    public function testCase0280()
    {
        $this->rdfaTestCase('0280', 'time element with @datetime an xsd:duration');
    }

    public function testCase0281()
    {
        $this->rdfaTestCase('0281', 'time element with @datetime an xsd:gYear');
    }

    public function testCase0282()
    {
        $this->rdfaTestCase('0282', 'time element with @datetime an xsd:gYearMonth');
    }

    public function testCase0283()
    {
        $this->rdfaTestCase('0283', 'time element with @datetime an invalid datatype generates plain literal');
    }

    public function testCase0284()
    {
        $this->rdfaTestCase('0284', 'time element not matching datatype but with explicit @datatype');
    }

    public function testCase0285()
    {
        $this->rdfaTestCase(
            '0285',
            'time element with @datetime an invalid datatype and in scope @lang generates plain literal '.
            'with language'
        );
    }

    public function testCase0286()
    {
        $this->rdfaTestCase('0286', '@value attribute overrides @content');
    }

    public function testCase0287()
    {
        $this->rdfaTestCase('0287', 'time element with @datetime an xsd:dateTime with TZ offset');
    }

    public function testCase0289()
    {
        $this->rdfaTestCase('0289', '@href becomes subject when @property and @content are present');
    }

    public function testCase0290()
    {
        $this->rdfaTestCase('0290', '@href becomes subject when @property and @datatype are present');
    }

    public function testCase0291()
    {
        $this->rdfaTestCase('0291', '@href as subject overridden by @about');
    }

    public function testCase0292()
    {
        $this->rdfaTestCase('0292', '@about overriding @href as subject is used as parent resource');
    }

    public function testCase0293()
    {
        $this->rdfaTestCase('0293', 'Testing the \':\' character usage in a CURIE');
    }

    public function testCase0295()
    {
        $this->markTestSkipped("FIXME: Graph comparison isn't working");
        $this->rdfaTestCase('0295', 'Benchmark test');
    }

    public function testCase0296()
    {
        $this->rdfaTestCase('0296', '@property does set parent object without @typeof');
    }

    public function testCase0297()
    {
        $this->rdfaTestCase('0297', '@about=[] with @typeof does not create a new subject');
    }

    public function testCase0298()
    {
        $this->rdfaTestCase('0298', '@about=[] with @typeof does not create a new object');
    }

    public function testCase0299()
    {
        $this->rdfaTestCase('0299', '@resource=[] with @href or @src uses @href or @src (@rel)');
    }

    public function testCase0300()
    {
        $this->rdfaTestCase('0300', '@resource=[] with @href or @src uses @href or @src (@property)');
    }

    public function testCase0301()
    {
        $this->rdfaTestCase('0301', '@property with @typeof creates a typed_resource for chaining');
    }

    public function testCase0302()
    {
        $this->rdfaTestCase('0302', '@typeof with different content types');
    }

    public function testCase0303()
    {
        $this->markTestSkipped("FIXME");
        $this->rdfaTestCase(
            '0303',
            'For HTML+RDFa 1.1, remove term elements of @rel/@rev when on same element as @property'
        );
    }

    public function testCase0311()
    {
        $this->rdfaTestCase('0311', 'Ensure no triples are generated when @property is empty');
    }

    public function testCase0312()
    {
        $this->markTestSkipped("FIXME");
        $this->rdfaTestCase('0312', 'Mute plain @rel if @property is present');
    }

    public function testCase0315()
    {
        $this->rdfaTestCase('0315', '@property and @typeof with incomplete triples');
    }

    public function testCase0316()
    {
        $this->rdfaTestCase('0316', '@property and @typeof with incomplete triples (@href variant)');
    }

    public function testCase0317()
    {
        $this->rdfaTestCase('0317', '@datatype inhibits new @property behavior');
    }

    public function testParseUnsupportedFormat()
    {
        $this->setExpectedException(
            'EasyRdf_Exception',
            'EasyRdf_Parser_Rdfa does not support: unsupportedformat'
        );
        $graph = new EasyRdf_Graph();
        $this->rdfaParser->parse(
            $graph,
            'data',
            'unsupportedformat',
            null
        );
    }
}
