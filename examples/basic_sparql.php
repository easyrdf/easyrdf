<?php
    /**
     * Making a SPARQL SELECT query
     *
     * This example creates a new SPARQL client, pointing at the
     * dbpedia.org endpoint. It then makes a SELECT query that
     * returns all of the countries in DBpedia along with an
     * english label.
     *
     * Note how the namespace prefix declarations are automatically
     * added to the query.
     *
     * @package    EasyRdf
     * @copyright  Copyright (c) 2009-2013 Nicholas J Humfrey
     * @license    http://unlicense.org/
     */

    require_once realpath(__DIR__.'/..')."/vendor/autoload.php";
    require_once __DIR__."/html_tag_helpers.php";

    // Setup some additional prefixes for DBpedia
    \EasyRdf\RdfNamespace::set('category', 'http://dbpedia.org/resource/Category:');
    \EasyRdf\RdfNamespace::set('dbpedia', 'http://dbpedia.org/resource/');
    \EasyRdf\RdfNamespace::set('dbo', 'http://dbpedia.org/ontology/');
    \EasyRdf\RdfNamespace::set('dbp', 'http://dbpedia.org/property/');

    $sparql = new \EasyRdf\Sparql\Client('http://dbpedia.org/sparql');
?>
<html>
<head>
  <title>EasyRdf Basic Sparql Example</title>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
</head>
<body>
<h1>EasyRdf Basic Sparql Example</h1>

<h2>List of countries</h2>
<ul>
<?php
    $result = $sparql->query(
        'SELECT * WHERE {'.
        '  ?country rdf:type dbo:Country .'.
        '  ?country rdfs:label ?label .'.
        '  ?country dc:subject category:Member_states_of_the_United_Nations .'.
        '  FILTER ( lang(?label) = "en" )'.
        '} ORDER BY ?label'
    );
    foreach ($result as $row) {
        echo "<li>".link_to($row->label, $row->country)."</li>\n";
    }
?>
</ul>
<p>Total number of countries: <?= $result->numRows() ?></p>

</body>
</html>
