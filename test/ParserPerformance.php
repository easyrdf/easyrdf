<?php

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

require_once realpath(__DIR__.'/..')."/vendor/autoload.php";

$parsers = array(
    'Arc',
    'Json',
    'Ntriples',
    'RdfXml',
    'Rapper',
    'Turtle',
);

$documents = array(
    'foaf.rdf' => 'rdfxml',
    'foaf.ttl' => 'turtle',
    'foaf.nt' => 'ntriples',
    'foaf.json' => 'json',
    'dundee.rdf' => 'rdfxml',
    'dundee.ttl' => 'turtle',
    'dundee.nt' => 'ntriples',
    'dundee.json' => 'json',
    'london.rdf' => 'rdfxml',
    'london.ttl' => 'turtle',
    'london.nt' => 'ntriples',
    'london.json' => 'json',
);

foreach ($documents as $filename => $type) {
    print "Input file: $filename\n";
    $filepath = dirname(__FILE__) . "/performance/$filename";
    if (!file_exists($filepath)) {
        print "Error: File does not exist.\n";
        continue;
    }

    $url = "http://www.example.com/$filename";
    $data = file_get_contents($filepath);
    print "File size: ".strlen($data)." bytes\n";

    foreach ($parsers as $parser_name) {
        $class = "EasyRdf\\Parser\\{$parser_name}";
        print "  Parsing using: {$class}\n";

        try {
            $parser = new $class();
            $graph = new \EasyRdf\Graph();

            $start = microtime(true);
            $parser->parse($graph, $data, $type, $url);
            $duration = microtime(true) - $start;
            print "  Parse time: $duration seconds\n";
            print "  Triple count: ".$graph->countTriples()."\n";
        } catch (Exception $e) {
            print 'Parsing failed: '.$e->getMessage()."\n";
        }
        print "\n";

        unset($graph);
    }
}
