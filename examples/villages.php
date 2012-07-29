<?php
    /**
     * Fetch and information about villages in Fife from dbpedialite.org
     *
     * @package    EasyRdf
     * @copyright  Copyright (c) 2009-2012 Nicholas J Humfrey
     * @license    http://unlicense.org/
     */

    set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/');
    require_once "EasyRdf.php";
    require_once "html_tag_helpers.php";
    
    $CATEGORY_ID = 4309010;
?>
<html>
<head><title>EasyRdf Village Info Example</title></head>
<body>
<h1>EasyRdf Village Info Example</h1>

<?php
    if (isset($_REQUEST['id'])) {
        $graph = new EasyRdf_Graph("http://dbpedialite.org/things/".$_REQUEST['id']);
        $graph->load();

        $village = $graph->primaryTopic();
        print content_tag('h2',$village->label());

        if ($village->get('foaf:depiction')) {
            print image_tag($village->get('foaf:depiction'),
              array('style'=>'max-width:400px;max-height:250px;'));
        }

        print content_tag('p',$village->get('rdfs:comment'));

        if ($village->get('geo:long')) {
            $ll = $village->get('geo:lat').','.$village->get('geo:long');
            print "<iframe width='425' height='350' frameborder='0' scrolling='no' marginheight='0' marginwidth='0' src='http://maps.google.com/maps?f=q&amp;ll=$ll&amp;output=embed'></iframe>";
        }

        echo "<br /><br />";
        echo $graph->dump();
    } else {
        $graph = new EasyRdf_Graph("http://dbpedialite.org/categories/".$CATEGORY_ID);
        $graph->load();
        $category = $graph->primaryTopic();

        print "<ul>\n";
        foreach ($category->all('^rdf:type') as $resource) {
            if (preg_match("|http://dbpedialite.org/things/(\d+)#id|", $resource, $matches)) {
                print '<li>'.link_to_self($resource->label(), "id=".$matches[1])."</li>\n";
            }
        }
        print "</ul>\n";
    }
?>
</body>
</html>
