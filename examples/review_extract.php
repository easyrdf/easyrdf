<?php
    set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/');
    require_once "EasyRdf/Graph.php";
    require_once "html_tag_helpers.php";

    ## Configure the RDF parser to use
    require_once "EasyRdf/Parser/Arc.php";
    EasyRdf_Graph::setRdfParser( new EasyRdf_Parser_Arc() );

    ## Add the Google Vocab namespace
    EasyRdf_Namespace::set('gv', 'http://rdf.data-vocabulary.org/#');
?>
<html>
<head><title>Review Extract</title></head>
<body>
<h1>Review Extract</h1>
<?= form_tag() ?>
<p>Please enter the URI of a page with a review on it (marked up with Google Review RDFa):</p>
<?= text_field_tag('uri', 'http://www.bbc.co.uk/music/reviews/2n8c.html', array('size'=>50)) ?><br />
<?= submit_tag() ?>
<?= form_end_tag() ?>

<?php
    if (isset($_REQUEST['uri'])) {
        $graph = new EasyRdf_Graph( $_REQUEST['uri'] );
        if ($graph) {
            $reviews = $graph->allOfType('gv:Review');
            $review = $reviews[0];
        }
    }
      
    if (isset($review)) {
        echo "<dl>\n";
        # FIXME: support gv:itemreviewed->gv:name ??
        if ($review->get('gv:itemreviewed')) echo "<dt>Item Reviewed:</dt><dd>".$review->get('gv:itemreviewed')."</dd>\n";
        if ($review->get('gv:rating')) echo "<dt>Rating:</dt><dd>".$review->get('gv:rating')."</dd>\n";
        # FIXME: support gv:reviewer->gv:name ??
        if ($review->get('gv:reviewer')) echo "<dt>Reviewer:</dt><dd>".$review->get('gv:reviewer')."</dd>\n";
        if ($review->get('gv:dtreviewed')) echo "<dt>Date Reviewed:</dt><dd>".$review->get('gv:dtreviewed')."</dd>\n";
        if ($review->get('gv:summary')) echo "<dt>Review Summary:</dt><dd>".$review->get('gv:summary')."</dd>\n";
        echo "</dl>\n";

        if ($review->get('gv:description'))
          echo "<div>".$review->get('gv:description')."</div>\n";
    }
?>
</body>
</html>
