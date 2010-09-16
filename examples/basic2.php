<?php
    set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/');
    require_once "EasyRdf.php";

    ## FIXME: one day EasyRdf will parse RDF/XML natively
    require_once "EasyRdf/Parser/Rapper.php";
?>
<html>
<head>
  <title>Basic FOAF example</title>
</head>
<body>

<?php
  $graph = new EasyRdf_Graph("http://127.0.0.1/~njh/foaf.rdf");
  $homepage = $graph->get('http://www.aelius.com/njh/'); 
  $me = $homepage->get('-foaf:homepage');
?>

<p>
  My name is: <?= $me->get('foaf:name') ?>
</p>

</body>
</html>
