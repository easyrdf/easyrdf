<?php
    set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/');
    require_once "EasyRdf.php";
    require_once "html_tag_helpers.php";

    $format_options = array();
    foreach (EasyRdf_Format::getFormats() as $format) {
        if ($format->getSerialiserClass()) {
            $format_options[$format->getLabel()] = $format->getName();
        }
    }
?>
<html>
<head><title>EasyRdf Converter</title></head>
<body>
<h1>EasyRdf Converter</h1>

<div style="margin: 10px">
  <?= form_tag() ?>
  <?= label_tag('uri').text_field_tag('uri', 'http://www.dajobe.org/foaf.rdf', array('size'=>80)) ?><br />
  <?= label_tag('format').select_tag('format', $format_options, 'rdfxml') ?>
  <?= submit_tag() ?>
  <?= form_end_tag() ?>
</div>

<?php
    if (isset($_REQUEST['uri'])) {
        $graph = new EasyRdf_Graph( $_REQUEST['uri'] );
        $data = $graph->serialise($_REQUEST['format']);
        if (!is_scalar($data)) {
            $data = var_export($data, true);
        }
        print "<pre>".htmlspecialchars($data)."</pre>";
    }
?>
</body>
</html>
