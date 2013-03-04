<?php
    /**
     * Using EasyRdf with the Zend Framework
     *
     * This example demonstrates using Zend_Http_Client and
     * Zend_Loader_Autoloader with EasyRdf.
     *
     * It creates a simple graph in memory, saves it to a local graphstore
     * and then fetches the data back using a SPARQL SELECT query.
     * Zend's curl HTTP client adaptor is used to perform the HTTP requests.
     *
     * @package    EasyRdf
     * @copyright  Copyright (c) 2009-2013 Nicholas J Humfrey
     * @license    http://unlicense.org/
     */

    set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/');

    // require the Zend Autoloader
    require_once 'Zend/Loader/Autoloader.php';
    $autoloader = Zend_Loader_Autoloader::getInstance();
    $autoloader->setFallbackAutoloader(true);

    // use the CURL based HTTP client adaptor
    $client = new Zend_Http_Client(
        null,
        array(
            'adapter' => 'Zend_Http_Client_Adapter_Curl',
            'keepalive' => true,
            'useragent' => "EasyRdf/zendtest"
        )
    );
    EasyRdf_Http::setDefaultHttpClient($client);
?>

<html>
<head>
  <title>Zend Framework Example</title>
</head>
<body>
<h1>Zend Framework Example</h1>

<?php
    # Load some sample data into a graph
    $graph = new EasyRdf_Graph('http://example.com/joe');
    $joe = $graph->resource('http://example.com/joe#me', 'foaf:Person');
    $joe->add('foaf:name', 'Joe Bloggs');
    $joe->addResource('foaf:homepage', 'http://example.com/joe/');

    # Store it in a local graphstore
    $store = new EasyRdf_GraphStore('http://localhost:8080/data/');
    $store->replace($graph);

    # Now make a query to the graphstore
    $sparql = new EasyRdf_Sparql_Client('http://localhost:8080/sparql/');
    $result = $sparql->query('SELECT * WHERE {<http://example.com/joe#me> ?p ?o}');
    echo $result->dump();
?>

</body>
</html>
