<?php
    set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/');
    require_once "EasyRdf/Graph.php";
    $url = $_GET['url'];
    
    # TODO LIST:
    # - display rdfs_range
    # - make use of rdfs_isDefinedBy?
    # - do clever things witgh rdfs_subPropertyOf?
?>
<html>
<head><title>EasyRdf Spec Maker</title></head>
<body>
<h1>EasyRdf Spec Maker</h1>
<form method="get">
<input name="url" type="text" size="48" value="<?= empty($url) ? 'http://xmlns.com/foaf/0.1/' : $url ?>" />
<input type="submit" />
</form>
<?php
    if ($url) {
        $graph = new EasyRdf_Graph( $url );
        $owl_thing = $graph->getResource('http://www.w3.org/2002/07/owl#Thing', 'owl_Class');
        $ontology = $graph->firstOfType('owl_Ontology');
    }
    
    function link_to($text,$url=null) {
        if ($url==null) $url = $text;
        return "<a href='$url'>$text</a>";
    }

    function shorten($ns,$uri) {
        if (is_object($ns)) $ns = $ns->getUri();
        if (is_object($uri)) $uri = $uri->getUri();
        if ($uri == 'http://www.w3.org/2002/07/owl#Thing') return 'Owl_Thing';
        if (strpos($uri, $ns) === 0) {
            return substr($uri, strlen($ns));
        } else {
            return null;
        }
    }

    function getAllProperties($graph, $ontology)
    {
        $property_types = array('rdf_Property','owl_Property','owl_ObjectProperty','owl_DatatypeProperty');
        $properties = array();
        foreach ($property_types as $property_type) {
            foreach ($graph->allOfType($property_type) as $property) {
                $name = shorten($ontology, $property);
                $properties[$name] = $property;
            }
        }
        return $properties;
    }
    
    function getClassProperties($class, $all_properties) {
        $properties = array();
        foreach ($all_properties as $name => $property) {
            if (in_array($class, $property->all('rdfs_domain')))
            {
                array_push($properties, "$name - <i>".$property->join('rdfs_comment')."</i>");
            }
        }
        return $properties;
    }
?>

<? 
    if ($ontology) {
        echo "<h2>".$ontology->label()."</h2>\n";
        
        echo "<dl>\n";
        echo "<dt>Namespace:</dt><dd>".link_to($ontology->getUri())."</dd>\n";
        if ($ontology->dc_date) echo "<dt>Date:</dt><dd>".$ontology->first('dc_date')."</dd>\n";
        #if ($ontology->dc_creator)  # FIXME: implement this
        #if ($ontology->dc_contributor)  # FIXME: implement this
        echo "</dl>\n";
        foreach ($ontology->all('rdfs_comment') as $comment) { echo "<p>$comment</p>\n"; }
        foreach ($ontology->all('dc_description') as $description) { echo "<p>$description</p>\n"; }
        
        echo "<h2>Classes</h2>\n";
        $all_properties = getAllProperties($graph, $ontology);
        foreach ($graph->allOfType('owl_Class') as $class) {
            $class_name = shorten($ontology,$class);
            if ($class_name == null) continue;
            echo "<div class='class' id='$class_name'>";
            echo "<h3>$class_name</h3>\n";
            foreach ($class->all('rdfs_comment') as $comment) { echo "<p>$comment</p>\n"; }
            echo "<dl>\n";
            
            if ($class != $owl_thing) {
                # Make class a subclass of owl_Thing if it isn't a subclass of anything else
                if (count($class->all('rdfs_subClassOf')) == 0) {
                    $class->set('rdfs_subClassOf', $owl_thing);
                }
                echo "<dt>SubClass of:</dt>\n";
                foreach ($class->all('rdfs_subClassOf') as $subClass) {
                    $short = shorten($ontology,$subClass);
                    if ($short) {
                        echo "<dd>".link_to($short,"#$short")."</dd>\n";
                    } else {
                        echo "<dd>".link_to($subClass)."</dd>\n";
                    }
                }
            }
            if ($class->owl_disjointWith) {
                echo "<dt>Disjoint with:</dt>\n";
                foreach ($class->all('owl_disjointWith') as $disjointWith) {
                    $short = shorten($ontology,$disjointWith);
                    if ($short) {
                        echo "<dd>".link_to($short,"#$short")."</dd>\n";
                    } else {
                        echo "<dd>".link_to($disjointWith)."</dd>\n";
                    }
                }
            }
            $properties = getClassProperties( $class, $all_properties );
            if ($properties) {
                echo "<dt>Properties:</dt>\n";
                foreach ($properties as $property) { echo "<dd>$property</d>\n"; }
            }
            echo "</dl>\n";
            echo "</div>";
        }
    }
?>

</body>
</html>
