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

require_once realpath(__DIR__.'/../..').'/vendor/autoload.php';

# Load the manifest
\EasyRdf\RdfNamespace::set('test', 'http://www.w3.org/2000/10/rdf-tests/rdfcore/testSchema#');
$manifest = parseTestdata("http://www.w3.org/2000/10/rdf-tests/rdfcore/Manifest.rdf");

$passCount = 0;
$failCount = 0;

foreach ($manifest->allOfType('test:PositiveParserTest') as $test) {
    echo "\n\n";

    echo "Input: ".$test->get('test:inputDocument')."\n";
    if (!file_exists(testdataFilepath($test->get('test:inputDocument')))) {
        echo "File does not exist.\n";
        continue;
    }

    echo "Output: ".$test->get('test:outputDocument')."\n";
    if (!file_exists(testdataFilepath($test->get('test:outputDocument')))) {
        echo "File does not exist.\n";
        continue;
    }

    echo "Status: ".$test->get('test:status')."\n";
    if ($test->get('test:status') != 'APPROVED') {
        continue;
    }

    $graph = parseTestdata($test->get('test:inputDocument'));
    $out_path = testdataFilepath($test->get('test:outputDocument'));

    $easyrdf_out_path = $out_path . ".easyrdf";
    file_put_contents($easyrdf_out_path, $graph->serialise('ntriples'));

    system("rdfdiff -f ntriples -t ntriples $out_path $easyrdf_out_path", $result);
    if ($result == 0) {
        echo "OK!\n";
        $passCount++;
    } else {
        echo "Failed!\n";
        $failCount++;
    }
}

echo "Tests that pass: $passCount\n";
echo "Tests that fail: $failCount\n";


function testdataFilepath($uri)
{
    return str_replace(
        "http://www.w3.org/2000/10/rdf-tests/rdfcore/",
        dirname(__FILE__) . '/rdfxml/',
        $uri
    );
}

function parseTestdata($uri)
{
    $filepath = testdataFilepath($uri);
    $data = file_get_contents($filepath);
    return new \EasyRdf\Graph("$uri", $data, 'rdfxml');
}
