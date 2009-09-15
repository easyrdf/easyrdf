<?php
    set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/');
    require_once "EasyRdf/Graph.php";
?>
<html>
<head>
  <title>Basic FOAF example</title>
</head>
<body>

<?php
  $foaf = new EasyRdf_Graph("http://www.aelius.com/njh/foaf.rdf");
  $me = $foaf->primaryTopic();
?>

<p>
  My name is: <?= $me->getFoaf_name() ?>
</p>

</body>
</html>
