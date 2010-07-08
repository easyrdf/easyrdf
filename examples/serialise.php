<?php
    set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/');
    require_once "EasyRdf.php";
  
    $graph = new EasyRdf_Graph();
    $me = $graph->resource('http://example.com/#joe');
    $me->set('foaf:name', 'Joeseph Bloggs');
    $me->set('foaf:title', 'mr');
    $me->set('foaf:nick', 'Joe');    
?>
<html>
<head><title>Serialiser</title></head>
<body>
<h1>Serialisation example</h1>

<pre>
<?php
    $data = $graph->serialise('ntriples');
    if (!is_scalar($data)) {
        $data = var_export($data, true);
    }
    print htmlspecialchars($data);
?>
</pre>

</body>
</html>
