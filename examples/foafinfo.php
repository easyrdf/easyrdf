<?php
    set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/');
    require_once "EasyRdf/Graph.php";
    $url = $_GET['url'];

    function link_to_self($text, $url)
    {
        $url = preg_replace("|#(.+)$|", '', $url);
        return link_to($text, $_SERVER['PHP_SELF'] . '?url=' . urlencode($url));
    }
    
    function link_to($text,$url=null) {
        if ($url==null) $url = $text;
        return "<a href='$url'>$text</a>";
    }
?>
<html>
<head><title>FOAF Info</title></head>
<body>
<h1>FOAF Info</h1>
<form method="get">
<input name="url" type="text" size="48" value="<?= empty($url) ? 'http://www.aelius.com/njh/foaf.rdf' : $url ?>" />
<input type="submit" />
</form>
<?php
    if ($url) {
        $graph = new EasyRdf_Graph( $url );
        if ($graph) {
            if ($graph->type() == 'foaf_PersonalProfileDocument') {
                $person = $graph->primaryTopic();
            } else if ($graph->type() == 'foaf_Person') {
                $person = $graph->getResource( $graph->getUri() );
            }
        }
    }
  
    if ($person) {
?>

<dl>
  <dt>Name:</dt><dd><?= $person->first('foaf_name') ?></dd>
  <dt>Homepage:</dt><dd><?= link_to( $person->first('foaf_homepage') ) ?></dd>
  <dt>Description:</dt><dd><?= $person->first('dc_description') ?></dd>
</dl>

<?php
        echo "<h2>Known Persons</h2>\n";
        echo "<ul>\n";
        foreach ($person->foaf_knows as $friend) {
          if ($friend->foaf_name) {
              $friend_name = $friend->first('foaf_name');
          } else if ($friend->rdfs_label) {
              $friend_name = $friend->first('rdfs_label');
          }
          if ($friend_name) {
              echo "<li>".link_to_self( $friend_name, $friend )."</li>";
          }
        }
        echo "</ul>\n";
    }
    
    if ($graph) {
        echo "<hr />";
        echo $graph->dump();
    }
?>
</body>
</html>
