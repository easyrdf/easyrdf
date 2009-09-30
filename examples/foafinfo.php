<?php
    set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/');
    require_once "EasyRdf/Graph.php";
    if (isset($_GET['uri'])) $uri = $_GET['uri'];

    function link_to_self($text, $uri)
    {
        $uri = preg_replace("|#(.+)$|", '', $uri);
        return link_to($text, $_SERVER['PHP_SELF'] . '?uri=' . urlencode($uri));
    }
    
    function link_to($text,$uri=null) {
        if ($uri==null) $uri = $text;
        return "<a href='$uri'>$text</a>";
    }
?>
<html>
<head><title>FOAF Info</title></head>
<body>
<h1>FOAF Info</h1>
<form method="get">
<input name="uri" type="text" size="48" value="<?= empty($uri) ? 'http://www.aelius.com/njh/foaf.rdf' : htmlspecialchars($uri) ?>" />
<input type="submit" />
</form>
<?php
    if (isset($uri)) {
        $graph = new EasyRdf_Graph( $uri );
        if ($graph) {
            if ($graph->type() == 'foaf:PersonalProfileDocument') {
                $person = $graph->primaryTopic();
            } else if ($graph->type() == 'foaf:Person') {
                $person = $graph->get( $graph->getUri() );
            }
        }
    }
  
    if (isset($person)) {
?>

<dl>
  <dt>Name:</dt><dd><?= $person->get('foaf:name') ?></dd>
  <dt>Homepage:</dt><dd><?= link_to( $person->get('foaf:homepage') ) ?></dd>
  <dt>Description:</dt><dd><?= $person->get('dc:description') ?></dd>
</dl>

<?php
        echo "<h2>Known Persons</h2>\n";
        echo "<ul>\n";
        foreach ($person->all('foaf:knows') as $friend) {
            if ($friend->label()) {
                $label = $friend->label();
            } else {
                $label = $friend->getUri();
            }

            if ($friend->isBnode()) {
                echo "<li>$label</li>";
            } else {
                echo "<li>".link_to_self( $label, $friend )."</li>";
            }
        }
        echo "</ul>\n";
    }
    
    if (isset($graph)) {
        echo "<hr />";
        echo $graph->dump();
    }
?>
</body>
</html>
