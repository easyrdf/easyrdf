<?php
    set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/');
    require_once "EasyRdf.php";
    require_once "html_tag_helpers.php";

    EasyRdf_Namespace::set('po', 'http://purl.org/ontology/po/');

    $client = new EasyRdf_SparqlClient('http://api.talis.com/stores/bbc-backstage/services/sparql');
?>
<html>
<head><title>Basic Sparql</title></head>
<body>
<h1>Basic Sparql</h1>

<h2>Doctor Who - Series 1</h2>
<ul>
<?
    $series1 = 'http://www.bbc.co.uk/programmes/b007vvcq#programme';
    $result = $client->query(
      "SELECT * WHERE {".
      "  <$series1> po:episode ?episode .".
      "  ?episode po:position ?pos .".
      "  ?episode rdfs:label ?label .".
      "} ORDER BY ?pos"
    );
    foreach ($result as $row) {
        echo "<li>$row->pos. ".link_to($row->label, $row->episode)."</li>\n";
    }
?>
</ul>

</body>
</html>
