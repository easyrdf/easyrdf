<?php
    set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/');
    require_once "EasyRdf.php";
    require_once "html_tag_helpers.php";
    
    $queries = array(
      'Select All' => "SELECT * WHERE {\n  ?s ?p ?o\n}\nLIMIT 10",
      'Construct All' => "CONSTRUCT {\n  ?s ?p ?o\n} WHERE {\n  ?s ?p ?o\n}\nLIMIT 10",
    );
?>
<html>
<head>
  <title>SPARQL Query Form</title>
</head>
<body>
<h1>SPARQL Query Form</h1>

<div style="margin: 10px">
  <?= form_tag() ?>
  <?
    print "<pre>\n";
    foreach(EasyRdf_Namespace::namespaces() as $prefix => $uri) {
      print "PREFIX $prefix: &lt;$uri&gt;\n";
    }
    print "</pre>\n";
  ?>
  <?= text_area_tag('query', $queries['Select All']) ?><br />
  <?= reset_tag() ?>
  <?= submit_tag() ?>
  <?= form_end_tag() ?>
</div>

<?php
  if (isset($_REQUEST['query'])) {
      $client = new EasyRdf_SparqlClient("http://localhost:8080/sparql");
      $results = $client->query($_REQUEST['query']);
      print $results->dump();
  }
?>

</body>
</html>
