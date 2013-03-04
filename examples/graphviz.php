<?php
    /**
     * GraphViz rendering example
     *
     * This example demonstrates converting an EasyRdf_Graph into the
     * GraphViz graph file language. Using the 'Use Labels' option, you
     * can have resource URIs replaced with text based labels and using
     * 'Only Labelled' option, only the resources and properties with
     * a label will be displayed.
     *
     * Rending a graph to an image will only work if you have the
     * GraphViz 'dot' command installed.
     *
     * @package    EasyRdf
     * @copyright  Copyright (c) 2012-2013 Nicholas J Humfrey
     * @license    http://unlicense.org/
     */

    set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/');
    require_once "EasyRdf.php";
    require_once "html_tag_helpers.php";

    $formats = array(
      'PNG' => 'png',
      'GIF' => 'gif',
      'SVG' => 'svg'
    );

    $format = EasyRdf_Format::getFormat(
        isset($_REQUEST['format']) ? $_REQUEST['format'] : 'png'
    );

    // Construct a graph of three people
    $graph = new EasyRdf_Graph();
    $graph->set('foaf:knows', 'rdfs:label', 'knows');
    $bob = $graph->resource('http://www.example.com/bob', 'foaf:Person');
    $alice = $graph->resource('http://www.example.com/alice', 'foaf:Person');
    $carol = $graph->resource('http://www.example.com/carol', 'foaf:Person');
    $bob->set('foaf:name', 'Bob');
    $alice->set('foaf:name', 'Alice');
    $carol->set('foaf:name', 'Carol');
    $bob->add('foaf:knows', $alice);
    $bob->add('foaf:knows', $carol);
    $alice->add('foaf:knows', $bob);
    $alice->add('foaf:knows', $carol);

    // Create a GraphViz serialiser
    $gv = new EasyRdf_Serialiser_GraphViz();
    $gv->setUseLabels(isset($_REQUEST['ul']));
    $gv->setOnlyLabelled(isset($_REQUEST['ol']));

    // If this is a request for the image, just render it and exit
    if (isset($_REQUEST['image'])) {
        header("Content-Type: ".$format->getDefaultMimeType());
        echo $gv->renderImage($graph, $format);
        exit;
    }
?>
<html>
<head><title>EasyRdf GraphViz Example</title></head>
<body>
<h1>EasyRdf GraphViz Example</h1>

<form action='' method='get'>
<?php
    echo label_tag('format').' '.select_tag('format', $formats).tag('br');
    echo label_tag('ul', 'Use labels:').' '.check_box_tag('ul').tag('br');
    echo label_tag('ol', 'Only labelled:').' '.check_box_tag('ol').tag('br');
    echo submit_tag();
?>
</form>

<div>
    <img src='?image&<?=$_SERVER["QUERY_STRING"]?>' />
</div>

<pre style="margin: 0.5em; padding:0.5em; background-color:#eee; border:dashed 1px grey;">
<?php
    print htmlspecialchars(
        $gv->serialise($graph, 'dot')
    );
?>
</pre>

</body>
</html>
