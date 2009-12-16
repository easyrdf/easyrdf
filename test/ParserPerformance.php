<?php

set_include_path(
    get_include_path() . PATH_SEPARATOR . 
    dirname(__FILE__) . '/../lib/'
);
require_once "EasyRdf/Graph.php";

$parsers = array(
    'Arc',
    'Builtin',
    'Rapper',
    'Redland',
);

$documents = array(
    'foaf.rdf' => 'rdfxml',
    'foaf.json' => 'json',
    'foaf.nt' => 'ntriples',
    'dundee.rdf' => 'rdfxml',
    'dundee.json' => 'json',
    'dundee.nt' => 'ntriples',
    'london.rdf' => 'rdfxml',
    'london.json' => 'json',
    'london.nt' => 'ntriples',
);

foreach($documents as $filename => $type) {

    $filepath = dirname(__FILE__) . "/fixtures/$filename";
    $url = "http://www.example.com/$filename";
    $rdf = file_get_contents($filepath);
    print "Input file: $filename\n";
    print "File size: ".filesize($filepath)." bytes\n";
    
    foreach($parsers as $parser) {
        $class = "EasyRdf_Parser_$parser";
        print "  Parsing using: $class\n";
    
        try {
            require_once "EasyRdf/Parser/$parser.php";
            EasyRdf_Graph::setRdfParser(new $class());
    
            $start = microtime(true);
            $graph = new EasyRdf_Graph($url, $rdf, $type);
            $duration = microtime(true) - $start;
            print "  Parse time: $duration seconds\n";
            print "  Resource count: ".count($graph->resources())."\n";
        } catch (Exception $e) {
            print 'Parsing failed: '.$e->getMessage()."\n";
        }
        print "\n";
        
        unset($graph);
    }

}
