<?php
    /**
     * Example of making a SPARQL SELECT query
     *
     * The example creates a new SPARQL client, pointing at the
     * Talis hosted BBC Backstage store. It then makes a SELECT
     * query that returns all of the episodes, along with their
     * episode number and title.
     *
     * Note how the PO prefix declaration is automatically added to the query.
     *
     * @package    EasyRdf
     * @copyright  Copyright (c) 2009-2011 Nicholas J Humfrey
     * @license    http://unlicense.org/
     */

    set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/');
    require_once "EasyRdf.php";
    require_once "html_tag_helpers.php";

    EasyRdf_Namespace::set('po', 'http://purl.org/ontology/po/');

    $sparql = new EasyRdf_Sparql_Client(
      'http://api.talis.com/stores/bbc-backstage/services/sparql'
    );
?>
<html>
<head><title>Basic Sparql</title></head>
<body>
<h1>Basic Sparql</h1>

<h2>Doctor Who - Series 1</h2>
<ul>
<?
    $series1 = 'http://www.bbc.co.uk/programmes/b007vvcq#programme';
    $result = $sparql->query(
      "SELECT * WHERE {".
      "  <$series1> po:episode ?episode .".
      "  ?episode po:position ?pos .".
      "  ?episode rdfs:label ?title .".
      "} ORDER BY ?pos"
    );
    foreach ($result as $row) {
        echo "<li>$row->pos. ".link_to($row->title, $row->episode)."</li>\n";
    }
?>
</ul>

</body>
</html>
