<?php

    /**
     * Script to update test cases from rdfa.info
     *
     * @package    EasyRdf
     * @copyright  Copyright (c) 2012 Nicholas J Humfrey
     * @license    http://www.opensource.org/licenses/bsd-license.php
     */

    set_include_path(get_include_path() . PATH_SEPARATOR . './lib/');
    require_once "EasyRdf.php";

    $TEST_SUITE_ROOT = '../rdfa-website/';

    EasyRdf_Namespace::set('test', 'http://www.w3.org/2006/03/test-description#');
    EasyRdf_Namespace::set('rdfatest', 'http://rdfa.info/vocabs/rdfa-test#');

    $manifest = new EasyRdf_Graph('http://rdfa.info/test-suite/manifest');
    $manifest->parseFile($TEST_SUITE_ROOT . 'manifest.ttl');

    foreach ($manifest->allOfType('test:TestCase') as $test) {
        if (!in_array('rdfa1.1', $test->all('rdfatest:rdfaVersion')))
            continue;
        if (!in_array('xhtml5', $test->all('rdfatest:hostLanguage')))
            continue;
        if ($test->get('test:classification')->shorten() != 'test:required')
            continue;

        $id = $test->localName();
        $title = $test->get('dc:title');
        print "    public function testCase$id()\n";
        print "    {\n";
        print "        \$this->rdfaTestCase('$id', '$title');\n";
        print "    }\n\n";
    }
