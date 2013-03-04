<?php

/**
 * Script to update test cases from rdfa.info
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2012-2013 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */

set_include_path(get_include_path() . PATH_SEPARATOR . './lib/');
require_once "EasyRdf.php";

$RDFA_VERSION = 'rdfa1.1';
$HOST_LANGUAGE = 'xhtml5';
$REFERENCE_DISTILLER = 'http://rdf.greggkellogg.net/distiller?raw=true&fmt=ntriples&in_fmt=rdfa&uri=';
$FIXTURE_DIR = dirname(__FILE__);

EasyRdf_Namespace::set('test', 'http://www.w3.org/2006/03/test-description#');
EasyRdf_Namespace::set('rdfatest', 'http://rdfa.info/vocabs/rdfa-test#');

$client = new EasyRdf_Http_Client();

$manifest = EasyRdf_Graph::newAndLoad('http://rdfa.info/test-suite/manifest.ttl');
foreach ($manifest->allOfType('test:TestCase') as $test) {
    if (!in_array($RDFA_VERSION, $test->all('rdfatest:rdfaVersion'))) {
        continue;
    }
    if (!in_array($HOST_LANGUAGE, $test->all('rdfatest:hostLanguage'))) {
        continue;
    }
    if ($test->get('test:classification')->shorten() != 'test:required') {
        continue;
    }

    $id = $test->localName();
    $title = $test->get('dc:title');
    $escapedTitle = addcslashes($title, '\'');

    # Download the test input
    $inputUri = "http://rdfa.info/test-suite/test-cases/$RDFA_VERSION/$HOST_LANGUAGE/$id.xhtml";
    $client->setUri("$inputUri");
    $response = $client->request();
    file_put_contents("$FIXTURE_DIR/$id.xhtml", $response->getBody());

    # Download the expected output
    $client->setUri($REFERENCE_DISTILLER . urlencode($inputUri));
    $response = $client->request();
    file_put_contents("$FIXTURE_DIR/$id.nt", $response->getBody());

    # Output code for PHPUnit
    print "    public function testCase$id()\n";
    print "    {\n";
    if (strlen($title) < 80) {
        print "        \$this->rdfaTestCase('$id', '$escapedTitle');\n";
    } else {
        print "        \$this->rdfaTestCase(\n";
        print "            '$id', '$escapedTitle'\n";
        print "        );\n";
    }
    print "    }\n\n";
}
