<?php

set_include_path(
    get_include_path() . PATH_SEPARATOR . 
    dirname(__FILE__) . '/../lib/'
);
require_once "EasyRdf/Graph.php";

$parsers = array(
    'ArcParser',
    'RapperParser',
    'RedlandParser',
);

$documents = array(
    'http://www.example.com/joe/foaf.rdf' => 'foaf.rdf',
    'http://dbpedia.org/data/Dundee.rdf' => 'dundee.rdf',
    'http://dbpedia.org/data/London.rdf' => 'london.rdf',
);

foreach($documents as $url => $filename) {

    $filepath = dirname(__FILE__) . "/fixtures/$filename";
    $rdf = file_get_contents($filepath);
    print "Input file: $filename\n";
    print "File size: ".filesize($filepath)." bytes\n";
    
    foreach($parsers as $parser) {
        require_once "EasyRdf/$parser.php";
        $class = "EasyRdf_$parser";
        EasyRdf_Graph::setRdfParser(new $class());
    
        print "  Parsing using: $class\n";
        $start = microtime(true);
        $graph = new EasyRdf_Graph($url, $rdf, 'rdfxml');
        $duration = microtime(true) - $start;
        print "  Parse time: $duration seconds\n";
        print "  Resource count: ".count($graph->resources())."\n";
        print "\n";
        
        unset($graph);
    }

}
