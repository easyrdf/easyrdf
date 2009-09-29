<?php
    set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/');
    require_once "EasyRdf/Graph.php";
    $url = $_GET['url'];
?>
<html>
<head><title>EasyRdf Graph Dumper</title></head>
<body>
<h1>EasyRdf Graph Dumper</h1>
<form method="get">
<input name="uri" type="text" size="48" value="<?= empty($uri) ? 'http://www.aelius.com/njh/foaf.rdf' : htmlspecialchars($uri) ?>" />
<input type="submit" />
</form>
<?php
    if ($uri) {
        $graph = new EasyRdf_Graph( $uri );
        if ($graph) {
            $graph->dump();
        } else {
            echo "<p>Failed to create graph.</p>";
        }
    }
?>
</body>
</html>
