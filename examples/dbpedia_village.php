<?php
    set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/');
    require_once "EasyRdf/Graph.php";
    require_once "EasyRdf/Namespace.php";
    require_once "EasyRdf/TypeMapper.php";
    require_once "html_tag_helpers.php";
    
    EasyRdf_Graph::setLangFilter('en');
    EasyRdf_Namespace::set('dbpprop', "http://dbpedia.org/property/");
    EasyRdf_Namespace::set('georss', "http://www.georss.org/georss/");
?>
<html>
<head><title>Village Info</title></head>
<body>
<h1>Village Info</h1>

<?php
    if (isset($_REQUEST['term'])) {
        $uri = "http://dbpedia.org/resource/".$_REQUEST['term'];
        $graph = new EasyRdf_Graph( $uri );
        $village = $graph->get($uri);
        
        print content_tag('h2',$village->label());
        
        if ($village->get('foaf:depiction')) {
            print image_tag($village->get('foaf:depiction'),
              array('style'=>'max-width:400px;max-height:250px;'));
        }
        
        print content_tag('p',$village->get('rdfs:comment'));
        
        if ($village->get('dbpprop:longitude')) {
            $ll = $village->get('dbpprop:latitude').','.$village->get('dbpprop:longitude');
            print "<iframe width='425' height='350' frameborder='0' scrolling='no' marginheight='0' marginwidth='0' src='http://maps.google.com/maps?f=q&amp;sll=$ll&amp;output=embed'></iframe>";
        }

        echo "<br /><hr />";
        echo $graph->dump();
    } else {
        $uri = "http://dbpedia.org/resource/Category:Villages_in_Fife";
        $graph = new EasyRdf_Graph( $uri );
        $category = $graph->get($uri);
        foreach ($graph->resourcesMatching('skos:subject',$category) as $resource) {
            $term = str_replace('http://dbpedia.org/resource/','',$resource);
            $label = urldecode(str_replace('_',' ',$term));
            print '<li>'.link_to_self($label, 'term='.$term)."</li>\n";
        }
    }
?>
</body>
</html>
