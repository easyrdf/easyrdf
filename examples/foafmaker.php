<?php
    set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/');
    require_once "EasyRdf/Graph.php";
    $uri = $_GET['uri'];
?>
<html>
<head><title>FOAF Maker</title></head>
<body>
<h1>FOAF Maker</h1>
<form method="get">
<input name="uri" type="text" size="48" value="<?= empty($uri) ? 'http://www.aelius.com/njh#me' : $uri ?>" />
<input type="submit" />
</form>
<?php
    if ($uri) {
        $graph = new EasyRdf_Graph();
        
        # 1st Technique
        $me = $graph->get( $uri, 'foaf_Person' );
        $me->set('foaf_age', '28');
        
        # 2nd Technique
        $graph->add( $me, 'foaf_name', 'Nicholas J Humfrey' );
        $graph->add( $uri, array(
            'foaf_firstName' => 'Nicholas',
            'foaf_lastName' => 'Humfrey',
            'foaf_nick' => 'Nick'
        ));

        # Finally output the graph
        $graph->dump();
    }
  
?>

</body>
</html>
