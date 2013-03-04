<?php
    /**
     * Form to submit and display SPARQL queries
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
     * @copyright  Copyright (c) 2009-2013 Nicholas J Humfrey
     * @license    http://unlicense.org/
     */

    set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/');
    require_once "EasyRdf.php";
    require_once "html_tag_helpers.php";

    // Stupid PHP :(
    if (get_magic_quotes_gpc() and isset($_REQUEST['query'])) {
        $_REQUEST['query'] = stripslashes($_REQUEST['query']);
    }
?>
<html>
<head>
  <title>EasyRdf SPARQL Query Form</title>
  <style type="text/css">
    .error {
      width: 35em;
      border: 2px red solid;
      padding: 1em;
      margin: 0.5em;
      background-color: #E6E6E6;
    }
  </style>
</head>
<body>
<h1>EasyRdf SPARQL Query Form</h1>

<div style="margin: 0.5em">
  <?php
    print form_tag();
    print label_tag('endpoint');
    print text_field_tag('endpoint', "http://dbpedia.org/sparql", array('size'=>80)).'<br />';
    print "<code>";
    foreach(EasyRdf_Namespace::namespaces() as $prefix => $uri) {
        print "PREFIX $prefix: &lt;".htmlspecialchars($uri)."&gt;<br />\n";
    }
    print "</code>";
    print text_area_tag('query', "SELECT * WHERE {\n  ?s ?p ?o\n}\nLIMIT 10", array('rows' => 10, 'cols' => 80)).'<br />';
    print check_box_tag('text') . label_tag('text', 'Plain text results').'<br />';
    print reset_tag() . submit_tag();
    print form_end_tag();
  ?>
</div>

<?php
  if (isset($_REQUEST['endpoint']) and isset($_REQUEST['query'])) {
      $sparql = new EasyRdf_Sparql_Client($_REQUEST['endpoint']);
      try {
          $results = $sparql->query($_REQUEST['query']);
          if (isset($_REQUEST['text'])) {
              print "<pre>".htmlspecialchars($results->dump('text'))."</pre>";
          } else {
              print $results->dump('html');
          }
      } catch (Exception $e) {
          print "<div class='error'>".$e->getMessage()."</div>\n";
      }
  }
?>

</body>
</html>
