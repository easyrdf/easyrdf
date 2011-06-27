<?php
    /**
     * Store and retrieve data from a SPARQL Graph Store
     *
     * @package    EasyRdf
     * @copyright  Copyright (c) 2009-2011 Nicholas J Humfrey
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
  $gs = new EasyRdf_GraphStore('http://localhost:8080/data/');

  $graph = new EasyRdf_Graph();
  #$graph = $gs->get('foobar.rdf');
  #$gs->delete('foobar.rdf');

  
  $graph->add('http://time.com/current', 'dc:date', time());

  echo $graph->dump();

  $gs->insert($graph, 'time.rdf');
  
  
//   $gs->insert($graph);
//   $gs->insert($graph, 'new.rdf');
  
  
?>

</body>
</html>
