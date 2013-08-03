<?php
    /**
     * Store and retrieve data from a SPARQL 1.1 Graph Store
     *
     * This example adds a triple containing the current time into
     * a local graph store. It then fetches the whole graph out
     * and displays the contents.
     *
     * Note that you will need a graph store, for example RedStore,
     * running on your local machine in order to test this example.
     *
     * @package    EasyRdf
     * @copyright  Copyright (c) 2009-2013 Nicholas J Humfrey
     * @license    http://unlicense.org/
     */

    set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/');
    require_once "EasyRdf.php";
?>
<html>
<head>
  <title>GraphStore example</title>
</head>
<body>

<?php
  // Use a local SPARQL 1.1 Graph Store (eg RedStore)
  $gs = new EasyRdf_GraphStore('http://localhost:8080/data/');

  // Add the current time in a graph
  $graph1 = new EasyRdf_Graph();
  $graph1->add('http://example.com/test', 'rdfs:label', 'Test');
  $graph1->add('http://example.com/test', 'dc:date', time());
  $gs->insert($graph1, 'time.rdf');

  // Get the graph back out of the graph store and display it
  $graph2 = $gs->get('time.rdf');
  print $graph2->dump();
?>

</body>
</html>
