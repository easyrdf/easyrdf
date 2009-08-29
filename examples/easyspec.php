<?php
    set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/');
    require_once "EasyRdf/Graph.php";
    $url = $_GET['url'];
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
        $ontology = $graph->firstOfType('owl_Ontology');
    }
    
    function link_to($text,$url=null) {
        if ($url==null) $url = $text;
        return "<a href='$url'>$text</a>";
    }

    function shorten($ns,$uri) {
        if (is_object($ns)) $ns = $ns->getUri();
        if (is_object($uri)) $uri = $uri->getUri();
        if (strpos($uri, $ns) === 0) {
            return substr($uri, strlen($ns));
        } else {
            return null;
        }
    }
    
    function getClassProperties($graph, $ontology, $class) {
        $properties = array();
        foreach ($graph->allOfType('owl_ObjectProperty') as $property) {
            if (in_array($class, $property->all('rdfs_domain'))) {
                $short = shorten($ontology, $property);
                if ($short) {
                    array_push($properties, "$short - <i>".$property->join('rdfs_comment')."</i>");
                } else {
                    array_push($properties, link_to($property));
                }
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
        
        echo "<h2>Classes</h2>\n";
        foreach ($graph->allOfType('owl_Class') as $class) {
            $class_name = shorten($ontology,$class);
            if ($class_name == null) continue;
            echo "<div class='class' id='$class_name'>";
            echo "<h3>$class_name</h3>\n";
            foreach ($class->all('rdfs_comment') as $comment) { echo "<p>$comment</p>\n"; }
            echo "<dl>\n";
            if ($class->rdfs_subClassOf) {
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
            $properties = getClassProperties( $graph, $ontology, $class );
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
