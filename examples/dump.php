<?php
    /**
     * Display the contents of a graph
     *
     * @package    EasyRdf
     * @copyright  Copyright (c) 2009-2011 Nicholas J Humfrey
     * @license    http://unlicense.org/
     */

    set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/');
    require_once "EasyRdf.php";
    require_once "html_tag_helpers.php";

?>
<html>
<head><title>EasyRdf Graph Dumper</title></head>
<body>
<h1>EasyRdf Graph Dumper</h1>

<div style="margin: 10px">
  <?= form_tag() ?>
  URI: <?= text_field_tag('uri', 'http://mmt.me.uk/foaf.rdf', array('size'=>80)) ?><br />
  Format: <?= label_tag('format_html', 'HTML').' '.radio_button_tag('format', 'html', true) ?>
          <?= label_tag('format_text', 'Text').' '.radio_button_tag('format', 'text') ?><br />

  <?= submit_tag() ?>
  <?= form_end_tag() ?>
</div>

<?php
    if (isset($_REQUEST['uri'])) {
        $graph = new EasyRdf_Graph( $_REQUEST['uri'] );
        $graph->load();
        if ($graph) {
            if (isset($_REQUEST['format']) && $_REQUEST['format'] == 'text') {
                print "<pre>".$graph->dump(false)."</pre>";
            } else {
                $dump = $graph->dump(true);
                print preg_replace("/ href='([^#][^']*)'/e",'" href=\'?uri=".urlencode("$1")."#$1\'"', $dump);
            }
        } else {
            print "<p>Failed to create graph.</p>";
        }
    }
?>
</body>
</html>
