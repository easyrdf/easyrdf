<?php
    /**
     * Parse an RSS 1.0 feed and display titles
     *
     * The example demonstrates fetching an RSS 1.0 feed from the
     * web and then parsing as RDF/XML. The channel is found by getting
     * the first object of type rss:channel (a file should only contain
     * a single RSS channel).
     *
     * In RSS 1.0, the list of items in the feed are listed by relating
     * the rss:channel to the rss:items using an rdf:Seq. In EasyRdf
     * this maps into an EasyRdf_Container object, which can be
     * iterated over using a foreach() loop.
     *
     * Note that this example only works with RSS 1.0 and no
     * other version (0.90, 1.1 and 2.0) as they are not RDF.
     *
     * @package    EasyRdf
     * @copyright  Copyright (c) 2013 Nicholas J Humfrey
     * @license    http://unlicense.org/
     */

    set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/');
    require_once "EasyRdf.php";
    require_once "html_tag_helpers.php";
?>
<html>
<head>
  <title>EasyRdf RSS 1.0 Parsing example</title>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
</head>
<body>
<h1>EasyRdf RSS 1.0 Parsing example</h1>

<?= form_tag() ?>
<?= text_field_tag('uri', 'http://planetrdf.com/index.rdf', array('size'=>50)) ?>
<?= submit_tag() ?>
<?= form_end_tag() ?>

<?php
    if (isset($_REQUEST['uri'])) {
        $graph = EasyRdf_Graph::newAndLoad($_REQUEST['uri'], 'rdfxml');
        $channel = $graph->get('rss:channel', '^rdf:type');

        print "<p>Channel: ".link_to($channel->label(), $channel->get('rss:link'))."</p>\n";
        print "<p>Description: ".$channel->get('rss:description')."</p>\n";

        print "<ol>\n";
        foreach($channel->get('rss:items') as $item) {
            print "<li>".link_to($item->get('rss:title'), $item)."</li>\n";
        }
        print "</ol>\n";
    }
?>
</body>
</html>
