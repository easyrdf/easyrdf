<?php

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
