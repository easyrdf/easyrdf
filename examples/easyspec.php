<?php
    set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/');
    require_once "EasyRdf/Graph.php";
    require_once "EasyRdf/Owl/Class.php";
    require_once "EasyRdf/Owl/Property.php";
    
    if (isset($_GET['uri'])) $uri = $_GET['uri'];
    if (isset($_GET['short'])) $short = $_GET['short'];
    
    # TODO LIST:
    # - display rdfs:range
    # - make use of rdfs:isDefinedBy?
    # - do clever things witgh rdfs:subPropertyOf?
?>
<html>
<head><title>EasyRdf Spec Maker</title></head>
<body>
<h1>EasyRdf Spec Maker</h1>
<form method="get">
<input name="short" type="text" size="8" value="<?= empty($short) ? 'foaf' : $short ?>" />
<input name="uri" type="text" size="48" value="<?= empty($uri) ? 'http://xmlns.com/foaf/0.1/' : htmlspecialchars($uri) ?>" />
<input type="submit" />
</form>


<?php
    if (isset($uri)) {
        EasyRdf_Namespace::set( $short, $uri );
    
        $graph = new EasyRdf_Graph( $uri );
        $ontology = $graph->get( $uri );
        
    } else {
        
        echo "<h2>Some examples:</h2>\n";
        echo "<ul>\n";
        echo "<li><a href='easyspec.php?short=foaf&uri=http%3A%2F%2Fxmlns.com%2Ffoaf%2F0.1%2F'>Friend of a Friend</a></li>\n";
        echo "<li><a href='easyspec.php?short=mo&uri=http%3A%2F%2Fpurl.org%2Fontology%2Fmo%2F'>Music Ontology</a></li>\n";
        echo "<li><a href='easyspec.php?short=po&uri=http%3A%2F%2Fpurl.org%2Fontology%2Fpo%2F'>Programmes Ontology</a></li>\n";
        echo "<li><a href='easyspec.php?short=rev&uri=http%3A%2F%2Fpurl.org%2Fstuff%2Frev%23'>Review Vocabulary</a></li>\n";
        echo "</ul>\n";
    }
    
    function link_to($text,$uri=null) {
        if ($uri==null) $uri = $text;
        return "<a href='$uri'>$text</a>";
    }
?>

<? 
    if (isset($ontology)) {
        echo "<h2>".$ontology->label()."</h2>\n";
        
        echo "<dl>\n";
        echo "<dt>Namespace:</dt><dd>".link_to($ontology->getUri())."</dd>\n";
        if ($ontology->get('dc:date')) echo "<dt>Date:</dt><dd>".$ontology->get('dc:date')."</dd>\n";
        #if ($ontology->dc:creator)  # FIXME: implement this
        #if ($ontology->dc:contributor)  # FIXME: implement this
        echo "</dl>\n";
        foreach ($ontology->all('rdfs:comment') as $comment) { echo "<p>$comment</p>\n"; }
        foreach ($ontology->all('dc:description') as $description) { echo "<p>$description</p>\n"; }
        
        echo "<h2>Classes</h2>\n";
        foreach ($graph->allOfType('owl:Class') as $class) {
            if ($class->ns() != $short) continue;
            echo "<div class='class' id='".$class->shorten()."'>";
            echo "<h3>".$class->shorten()."</h3>\n";
            foreach ($class->all('rdfs:comment') as $comment) { echo "<p>$comment</p>\n"; }
            echo "<dl>\n";

            if ($class->get('rdfs:subClassOf')) {
                echo "<dt>SubClass of:</dt>\n";
                foreach ($class->all('rdfs:subClassOf') as $subClass) {
                    if ($subClass->ns() == $short) {
                        echo "<dd>".link_to($subClass->shorten(),'#'.$subClass->shorten())."</dd>\n";
                    } else {
                        echo "<dd>".link_to($subClass)."</dd>\n";
                    }
                }
            }
            
            if ($class->get('owl:disjointWith')) {
                echo "<dt>Disjoint with:</dt>\n";
                foreach ($class->all('owl:disjointWith') as $disjointWith) {
                    if ($disjointWith->ns() == $short) {
                        echo "<dd>".link_to($disjointWith->shorten(),'#'.$disjointWith->shorten())."</dd>\n";
                    } else {
                        echo "<dd>".link_to($disjointWith)."</dd>\n";
                    }
                }
            }

            $properties = $class->classProperties( $graph );
            if ($properties) {
                echo "<dt>Properties:</dt>\n";
                foreach ($properties as $property) {
                    echo "<dd>\n";
                    echo $property->shorten()." - <i>".$property->join('rdfs:comment')."</i>";
                    echo " [".$property->cardinality()."]\n";
                    echo "</dd>\n";
                }
            }
            echo "</dl>\n";
            echo "</div>";
        }
    }
?>

</body>
</html>
