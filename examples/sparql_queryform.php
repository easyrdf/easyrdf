<?php
    set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/');
    require_once "EasyRdf.php";
    require_once "html_tag_helpers.php";
?>
<html>
<head>
  <title>SPARQL Query Form</title>
</head>
<body>
<h1>SPARQL Query Form</h1>

<div style="margin: 10px">
  <?php
    print form_tag();
    print label_tag('endpoint');
    print text_field_tag('endpoint', "http://localhost:8080/sparql", array('size'=>70)).'<br />';
    print "<pre>";
    foreach(EasyRdf_Namespace::namespaces() as $prefix => $uri) {
        print "PREFIX $prefix: &lt;".htmlspecialchars($uri)."&gt;\n";
    }
    print "</pre>";
    print text_area_tag('query', "SELECT * WHERE {\n  ?s ?p ?o\n}\nLIMIT 10").'<br />';
    print check_box_tag('text') . label_tag('text', 'Plain text results').'<br />';
    print reset_tag() . submit_tag();
    print form_end_tag();
  ?>
</div>

<?php
  if (isset($_REQUEST['endpoint']) and isset($_REQUEST['query'])) {
      $client = new EasyRdf_SparqlClient($_REQUEST['endpoint']);
      $results = $client->query($_REQUEST['query']);
      if (isset($_REQUEST['text'])) {
          print "<pre>".htmlspecialchars($results->dump(false))."</pre>";
      } else {
          print $results->dump(true);
      }
  }
?>

</body>
</html>
