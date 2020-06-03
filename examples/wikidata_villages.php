<?php
    /**
     * Consuming Linked Data from Wikidata.
     *
     * This example demonstrates fetching information about villages in Fife
     * from Wikidata. The list of villages is fetched by running a SPARQL query. 
     *
     * If you click on an village, then it fetched by getting the Turtle formatted
     * RDF from Wikidata for that village. It then parses the result and 
     * displays a page about that village with a title, synopsis and Open Street Map.
     *
     * @package    EasyRdf
     * @copyright  Copyright (c) 2009-2020 Nicholas J Humfrey
     * @license    http://unlicense.org/
     */

    require_once realpath(__DIR__.'/..')."/vendor/autoload.php";
    require_once __DIR__."/html_tag_helpers.php";

    // Setup some additional prefixes for Wikidata
    \EasyRdf\RdfNamespace::set('wd', 'http://www.wikidata.org/entity/');
    \EasyRdf\RdfNamespace::set('wds', 'http://www.wikidata.org/entity/statement/');
    \EasyRdf\RdfNamespace::set('wdt', 'http://www.wikidata.org/prop/direct/');
    \EasyRdf\RdfNamespace::set('p', 'http://www.wikidata.org/prop/');
    \EasyRdf\RdfNamespace::set('wikibase', 'http://wikiba.se/ontology#');

    // SPARQL Query to get a list of villages in Fife
    define(SPARQL_QUERY, '
      SELECT ?item ?itemLabel
      WHERE {
        ?item wdt:P31 wd:Q532 .       # Instance of Village
        ?item wdt:P131 wd:Q201149 .   # Located in Fife
        SERVICE wikibase:label { bd:serviceParam wikibase:language "[AUTO_LANGUAGE],en". }
      }
      ORDER BY ?itemLabel
    ');
    define(SPARQL_ENDPOINT, 'https://query.wikidata.org/sparql');
    
    define(WIKIDATA_IMAGE, 'wdt:P18');
    define(WIKIDATA_POINT, 'wdt:P625');    
?>
<html>
<head><title>EasyRdf Village Info Example</title></head>
<body>
<h1>EasyRdf Village Info Example</h1>

<?php
    if (isset($_REQUEST['id'])) {
        $id = $_REQUEST['id'];
        $doc = "https://www.wikidata.org/wiki/Special:EntityData/$id.ttl";
        $graph = \EasyRdf\Graph::newAndLoad($doc, 'turtle');

        $village = $graph->resource("wd:$id");

        if ($village->get(WIKIDATA_IMAGE)) {
            print image_tag(
                $village->get(WIKIDATA_IMAGE),
                array('style'=>'max-width:400px;max-height:250px;margin:10px;float:right')
            );
        }
        print content_tag('h2',$village->label('en'));
        print content_tag('p', $village->get('schema:description', null, 'en'));

        if (preg_match("/Point\((\S+) (\S+)\)/", $village->get(WIKIDATA_POINT), $matches)) {
            $long = $matches[1];
            $lat = $matches[2];
            print "<iframe width='420' height='350' frameborder='0' scrolling='no' marginheight='0' marginwidth='0' src='http://www.openlinkmap.org/small.php?lat=$lat&lon=$long&zoom=14' style='border: 1px solid black'></iframe>";
        }
        
        print content_tag('h3', "Pages about " . $village->label('en'));
        print "<ul>\n";
        foreach ($graph->all($village, "^schema:about") as $doc) {
            print '<li>'.link_to($doc)."</li>\n";
        }
        print "</ul>\n";

        echo "<br /><br />";
        echo $village->dump();
    } else {
        print "<p>List of villages in Fife.</p>";
        $sparql = new \EasyRdf\Sparql\Client(SPARQL_ENDPOINT);
        $results = $sparql->query(SPARQL_QUERY);

        print "<ul>\n";
        foreach ($results as $row) {
          if (preg_match("|/(Q\d+)|", $row->item, $matches)) {
            print '<li>'.link_to_self($row->itemLabel, "id=".$matches[1])."</li>\n";
          }
        }
        print "</ul>\n";
    }
?>
</body>
</html>
