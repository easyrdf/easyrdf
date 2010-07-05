<?php
    set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/');
    require_once "EasyRdf.php";
    require_once "html_tag_helpers.php";

    ## Configure the RDF parser to use
    require_once "EasyRdf/Parser/Rapper.php";
    EasyRdf_Graph::setRdfParser( new EasyRdf_Parser_Rapper('/usr/local/bin/rapper') );
?>
<html>
<head><title>EasyRdf Graph Dumper</title></head>
<body>
<h1>EasyRdf Graph Dumper</h1>
<?= form_tag() ?>
<?= text_field_tag('uri', 'http://www.aelius.com/njh/foaf.rdf', array('size'=>50)) ?>
<?= submit_tag() ?>
<?= form_end_tag() ?>

<?php
    if (isset($_REQUEST['uri'])) {
        $graph = new EasyRdf_Graph( $_REQUEST['uri'] );
        if ($graph) {
            $graph->dump();
        } else {
            echo "<p>Failed to create graph.</p>";
        }
    }
?>
</body>
</html>
