<?php
    /**
     * Form to submit SPARQL queries and display the result
     *
     * This example presents a form that you can enter the URI
     * of a a SPARQL endpoint and a SPARQL query into. The
     * results are displayed using a call to dump() on what will be
     * either a EasyRdf_Sparql_Result or EasyRdf_Graph object.
     *
     * A list of registered namespaces is displayed above the query
     * box - any of these namespaces can be used in the query and PREFIX
     * statements will automatically be added to the start of the query
     * string.
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
      $sparql = new EasyRdf_Sparql_Client($_REQUEST['endpoint']);
      $results = $sparql->query($_REQUEST['query']);
      if (isset($_REQUEST['text'])) {
          print "<pre>".htmlspecialchars($results->dump(false))."</pre>";
      } else {
          print $results->dump(true);
      }
  }
?>

</body>
</html>
