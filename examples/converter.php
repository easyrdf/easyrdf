<?php
    /**
     * Convert RDF from one format to another
     *
     * The source RDF data can either be fetched from the web
     * or typed into the Input box.
     *
     * The first thing that this script does is make a list the names of the
     * supported input and output formats. These options are then
     * displayed on the HTML form.
     *
     * The input data is loaded or parsed into an EasyRdf_Graph.
     * That graph is than outputted again in the desired output format.
     *
     * @package    EasyRdf
     * @copyright  Copyright (c) 2009-2011 Nicholas J Humfrey
     * @license    http://unlicense.org/
     */

    set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/');
    require_once "EasyRdf.php";
    require_once "html_tag_helpers.php";

    $input_format_options = array('Guess' => 'guess');
    $output_format_options = array();
    foreach (EasyRdf_Format::getFormats() as $format) {
        if ($format->getSerialiserClass()) {
            $output_format_options[$format->getLabel()] = $format->getName();
        }
        if ($format->getParserClass()) {
            $input_format_options[$format->getLabel()] = $format->getName();
        }
    }

    // Stupid PHP :(
    if (get_magic_quotes_gpc() and isset($_REQUEST['data'])) {
        $_REQUEST['data'] = stripslashes($_REQUEST['data']);
    }
?>
<html>
<head><title>EasyRdf Converter</title></head>
<body>
<h1>EasyRdf Converter</h1>

<div style="margin: 10px">
  <?= form_tag() ?>
  <?= label_tag('data', 'Input Data: ').'<br />'.text_area_tag('data', '', array('cols'=>80, 'rows'=>10)) ?><br />
  <?= label_tag('uri', 'or Uri: ').text_field_tag('uri', 'http://www.dajobe.org/foaf.rdf', array('size'=>80)) ?><br />
  <?= label_tag('input_format', 'Input Format: ').select_tag('input_format', $input_format_options, 'guess') ?><br />
  <?= label_tag('output_format', 'Output Format: ').select_tag('output_format', $output_format_options, 'turtle') ?><br />
  <?= reset_tag() ?> <?= submit_tag() ?>
  <?= form_end_tag() ?>
</div>

<?php
    if (isset($_REQUEST['uri']) or isset($_REQUEST['data'])) {
        $graph = new EasyRdf_Graph($_REQUEST['uri']);
        if (empty($_REQUEST['data'])) {
            $graph->load($_REQUEST['uri'], NULL, $_REQUEST['input_format']);
        } else {
            $graph->parse($_REQUEST['data'], $_REQUEST['input_format'], $_REQUEST['uri']);
        }

        $output = $graph->serialise($_REQUEST['output_format']);
        if (!is_scalar($output)) {
            $output = var_export($output, true);
        }
        print "<pre>".htmlspecialchars($output)."</pre>";
    }
?>
</body>
</html>
