<?php


    /*
        NOTE: this example is incomplete
    */


    set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/');
    require_once "EasyRdf/Graph.php";
    if (isset($_GET['uri'])) $uri = $_GET['uri'];
?>
<html>
<head><title>FOAF Maker</title></head>
<body>
<h1>FOAF Maker</h1>
<form method="get">
<input name="uri" type="text" size="48" value="<?= empty($uri) ? 'http://www.example.com/joe#me' : htmlspecialchars($uri) ?>" />
<input type="submit" />
</form>
<?php
    if (isset($uri)) {
        $graph = new EasyRdf_Graph();
        
        # 1st Technique
        $me = $graph->get( $uri, 'foaf_Person' );
        $me->set('foaf_age', '26');
        
        # 2nd Technique
        $graph->add( $me, 'foaf_name', 'Joseph Bloggs' );
        $graph->add( $uri, array(
            'foaf_firstName' => 'Joseph',
            'foaf_lastName' => 'Bloggs',
            'foaf_nick' => 'Joe'
        ));

        # Finally output the graph
        $graph->dump();
    }
  
?>

</body>
</html>
