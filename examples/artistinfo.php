<?php
    set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/');
    require_once "EasyRdf/Graph.php";
    require_once "EasyRdf/Namespace.php";
    require_once "EasyRdf/TypeMapper.php";

    ## Configure the RDF parser to use
    require_once "EasyRdf/ArcParser.php";
    EasyRdf_Graph::setRdfParser( new EasyRdf_ArcParser() );
    
    # Configure the HTTP client to use
    require_once "EasyRdf/Http/Client.php";
    EasyRdf_Graph::setHttpClient( new EasyRdf_Http_Client() );
    
  
    class Model_MusicArtist extends EasyRdf_Resource
    {
        function birthEvent()
        {
            foreach($this->all('bio:event') as $event) {
                if (in_array('bio:Birth', $event->types())) {
                    return $event;
                }
            }
            return null;
        }
        
        function age()
        {
            $birth = $this->birthEvent();
            if ($birth) {
                $year = substr($birth->get('bio:date'), 0, 4);
                if ($year) {
                    return date('Y') - $year;
                }
            }
            return 'unknown';
        }
    }

    function link_to($text,$uri=null) {
        if ($uri==null) $uri = $text;
        return "<a href='$uri'>$text</a>";
    }

    ## Add namespaces
    EasyRdf_Namespace::set('mo', 'http://purl.org/ontology/mo/');
    EasyRdf_Namespace::set('bio', 'http://purl.org/vocab/bio/0.1/');
    EasyRdf_TypeMapper::set('mo:MusicArtist', 'Model_MusicArtist');
    
    if (isset($_GET['uri'])) $uri = $_GET['uri'];
?>
<html>
<head><title>Artist Info</title></head>
<body>
<h1>Artist Info</h1>
<form method="get">
<input name="uri" type="text" size="48" value="<?= empty($uri) ? 'http://www.bbc.co.uk/music/artists/70248960-cb53-4ea4-943a-edb18f7d336f.rdf' : htmlspecialchars($uri) ?>" />
<input type="submit" />
</form>
<?php
    if (isset($uri)) {
        $graph = new EasyRdf_Graph( $uri );
        if ($graph) $artist = $graph->primaryTopic();
    }
  
    if (isset($artist)) {
?>

<dl>
    <dt>Artist Name:</dt><dd><?= $artist->get('foaf:name') ?></dd>
    <dt>Type:</dt><dd><?= $artist->join('rdf:type',', ') ?></dd>
    <dt>Homepage:</dt><dd><?= link_to($artist->get('foaf:homepage')) ?></dd>
    <dt>Wikipedia page:</dt><dd><?= link_to($artist->get('mo:wikipedia')) ?></dd>
    <?php
        if (in_array('mo:SoloMusicArtist', $artist->types())) {
            echo "  <dt>Age:</dt>";
            echo "  <dd>".$artist->age()."</dd>\n";
        }
    ?>
</dl>
<?php
    }

    if (isset($graph)) {
        echo "<hr />";
        echo $graph->dump();
    }
?>
</body>
</html>
