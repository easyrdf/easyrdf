<?php
    /**
     * Using EasyRdf_Graph directly without EasyRdf_Resource
     *
     * Triple data is inserted and retrieved directly from a graph object,
     * where it is stored internally as an associative array.
     *
     * @package    EasyRdf
     * @copyright  Copyright (c) 2009-2013 Nicholas J Humfrey
     * @license    http://unlicense.org/
     */

    set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/');
    require_once "EasyRdf.php";
?>
<html>
<head>
  <title>Example of using EasyRdf_Graph directly</title>
</head>
<body>

<?php
  $graph = new EasyRdf_Graph();
  $graph->addResource("http://example.com/joe", "rdf:type", "foaf:Person");
  $graph->addLiteral("http://example.com/joe", "foaf:name", "Joe Bloggs");
  $graph->addLiteral("http://example.com/joe", "foaf:name", "Joseph Bloggs");
  $graph->add("http://example.com/joe", "rdfs:label", "Joe");

  $graph->setType("http://njh.me/", "foaf:Person");
  $graph->add("http://njh.me/", "rdfs:label", "Nick");
  $graph->addLiteral("http://njh.me/", "foaf:name", "Nicholas Humfrey");
  $graph->addResource("http://njh.me/", "foaf:homepage", "http://www.aelius.com/njh/");
?>

<p>
  <b>Name:</b> <?= $graph->get("http://example.com/joe", "foaf:name") ?> <br />
  <b>Names:</b> <?= $graph->join("http://example.com/joe", "foaf:name") ?> <br />

  <b>Label:</b> <?= $graph->label("http://njh.me/") ?> <br />
  <b>Properties:</b> <?= join(', ', $graph->properties("http://example.com/joe")) ?> <br />
  <b>PropertyUris:</b> <?= join(', ', $graph->propertyUris("http://example.com/joe")) ?> <br />
  <b>People:</b> <?= join(', ', $graph->allOfType('foaf:Person')) ?> <br />
  <b>Unknown:</b> <?= $graph->get("http://example.com/joe", "unknown:property") ?> <br />
</p>

<?= $graph->dump() ?>

</body>
</html>
