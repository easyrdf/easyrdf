<?php
  set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/');
  require_once "EasyRDF/Graph.php";
  $url = $_GET['url'];
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
        $data = file_get_contents( $url );
        $graph = new EasyRdf_Graph( $url, $data );
        if ($graph) $person = $graph->primaryTopic();
    }
  
    if ($person) {
?>

<dl>
  <dt>Name:</dt><dd><?= $person->first('foaf_name') ?></dd>
  <dt>Homepage:</dt><dd><?= $person->first('foaf_homepage') ?></dd>
  <dt>Description:</dt><dd><?= $person->first('dc_description') ?></dd>
</dl>

<?php
        echo "<h2>Known Persons</h2>\n";
        echo "<ul>\n";
        foreach ($person->foaf_knows as $friend) {
          echo "<li>";
          if ($friend->foaf_name) {
              echo $friend->first('foaf_name');
          } else if ($friend->rdfs_label) {
              echo $friend->first('rdfs_label');
          }
          echo "</li>\n";
        }
        echo "</ul>\n";
    }
?>
</body>
</html>
