<?php

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
