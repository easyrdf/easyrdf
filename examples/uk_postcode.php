<?php
    /**
     * Resolving UK postcodes using uk-postcodes.com
     *
     * Another basic example that demonstrates registering namespaces,
     * loading RDF data from the web and then directly displaying
     * literals from the graph on the page.
     *
     * @package    EasyRdf
     * @copyright  Copyright (c) 2009-2013 Nicholas J Humfrey
     * @license    http://unlicense.org/
     */

    set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/');
    require_once "EasyRdf.php";
    require_once "html_tag_helpers.php";

    EasyRdf_Namespace::set('postcode', 'http://data.ordnancesurvey.co.uk/ontology/postcode/');
    EasyRdf_Namespace::set('sr', 'http://data.ordnancesurvey.co.uk/ontology/spatialrelations/');
    EasyRdf_Namespace::set('eg', 'http://statistics.data.gov.uk/def/electoral-geography/');
    EasyRdf_Namespace::set('ag', 'http://statistics.data.gov.uk/def/administrative-geography/');
    EasyRdf_Namespace::set('osag', 'http://data.ordnancesurvey.co.uk/ontology/admingeo/');
?>
<html>
<head>
  <title>EasyRdf UK Postcode Resolver</title>
  <style type="text/css" media="all">
    #map
    {
        border: 1px gray solid;
        float: right;
        margin: 0 0 20px 20px;
    }
    th { text-align: right }
    td { padding: 5px; }
  </style>
</head>
<body>
<h1>EasyRdf UK Postcode Resolver</h1>

<?= form_tag() ?>
  <?= text_field_tag('postcode', 'W1A 1AA', array('size'=>10)) ?>
  <?= submit_tag() ?>
<?= form_end_tag() ?>

<?php
    if (isset($_REQUEST['postcode'])) {
        $postcode = str_replace(' ', '', strtoupper($_REQUEST['postcode']));
        $docuri = "http://www.uk-postcodes.com/postcode/$postcode.rdf";
        $graph = EasyRdf_Graph::newAndLoad($docuri, 'rdfxml');


        // Get the first resource of type PostcodeUnit
        $res = $graph->get('postcode:PostcodeUnit', '^rdf:type');
        if ($res) {
            $ll = $res->get('geo:lat').','.$res->get('geo:long');
            print "<iframe id='map' width='500' height='250' frameborder='0' scrolling='no' src='http://maps.google.com/maps?f=q&amp;ll=$ll&amp;output=embed'></iframe>";
            print "<table id='facts'>\n";
            print "<tr><th>Easting:</th><td>" . $res->get('sr:easting') . "</td></tr>\n";
            print "<tr><th>Northing:</th><td>" . $res->get('sr:northing') . "</td></tr>\n";
            print "<tr><th>Longitude:</th><td>" . $res->get('geo:long') . "</td></tr>\n";
            print "<tr><th>Latitude:</th><td>" . $res->get('geo:lat') . "</td></tr>\n";
            print "<tr><th>Local Authority:</th><td>" . $res->get('ag:localAuthority')->label() . "</td></tr>\n";
            print "<tr><th>Electoral Ward:</th><td>" . $res->get('eg:ward')->label() . "</td></tr>\n";
            print "</table>\n";

            print "<div style='clear: both'></div>\n";
        }

        print $graph->dump();
    }
?>
</body>
</html>
