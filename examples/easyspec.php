<?php
    set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/');
    require_once "EasyRdf/Graph.php";
    $url = $_GET['url'];
?>
<html>
<head><title>EasyRdf Spec Maker</title></head>
<body>
<h1>EasyRdf Spec Maker</h1>
<form method="get">
<input name="url" type="text" size="48" value="<?= empty($url) ? 'http://www.bbc.co.uk/ontologies/programmes/2009-04-17.n3' : $url ?>" />
<input type="submit" />
</form>
<?php
    if ($url) {
        $graph = new EasyRdf_Graph( $url );
    }
?>

<? 
    if ($graph) {
        #$graph->dump();
        echo "<p>Classes: ";
        foreach ($graph->allByType('owl_Class') as $class) {
            echo $class->first('rdfs_label');
            echo " | ";
        }
        echo "</p>";
        echo "<p>Properties: ";
        foreach ($graph->allByType('owl_ObjectProperty') as $class) {
            echo $class->first('rdfs_label');
            echo " | ";
        }
        echo "</p>";
    }        
?>

</body>
</html>
