<?php
    /**
     * Display the contents of a graph
     *
     * Data from the chosen URI is loaded into an EasyRdf_Graph object.
     * Then the graph is dumped and printed to the page using the
     * $graph->dump() method.
     *
     * The call to preg_replace() replaces links in the page with
     * links back to this dump script.
     *
     * @package    EasyRdf
     * @copyright  Copyright (c) 2009-2013 Nicholas J Humfrey
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
        $graph = EasyRdf_Graph::newAndLoad($_REQUEST['uri']);
        if ($graph) {
            if (isset($_REQUEST['format']) && $_REQUEST['format'] == 'text') {
                print "<pre>".$graph->dump('text')."</pre>";
            } else {
                $dump = $graph->dump('html');
                print preg_replace_callback("/ href='([^#][^']*)'/", 'makeLinkLocal', $dump);
            }
        } else {
            print "<p>Failed to create graph.</p>";
        }
    }

    # Callback function to re-write links in the dump to point back to this script
    function makeLinkLocal($matches)
    {
        $href = $matches[1];
        return " href='?uri=".urlencode($href)."#$href'";
    }
?>
</body>
</html>
