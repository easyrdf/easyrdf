<?php
    set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/');
    require_once "EasyRdf/Graph.php";

    ## Configure the RDF parser to use
    require_once "EasyRdf/ArcParser.php";
    EasyRdf_Graph::setRdfParser( new EasyRdf_ArcParser() );

    ## Add the Google Vocab namespace
    EasyRdf_Namespace::set('gv', 'http://rdf.data-vocabulary.org/#');
    
    if (isset($_GET['uri'])) $uri = $_GET['uri'];
?>
<html>
<head><title>Review Extract</title></head>
<body>
<h1>Review Extract</h1>
<form method="get">
<p>Please enter the URI of a page with a review on it (marked up with Google Review RDFa):</p>
<input name="uri" type="text" size="48" value="<?= empty($uri) ? 'http://www.bbc.co.uk/music/reviews/2n8c.html' : htmlspecialchars($uri) ?>" />
<input type="submit" />
</form>
<?php
    if (isset($uri)) {
        $graph = new EasyRdf_Graph( $uri );
        if ($graph) $review = $graph->firstOfType('gv:Review');
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
