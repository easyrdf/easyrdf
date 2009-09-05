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
  $me = new EasyRdf_Graph("http://www.aelius.com/njh#me");
?>

<p>
  My name is: <?= $me->join('foaf_name') ?>
</p>

</body>
</html>
