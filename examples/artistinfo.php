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

    }

    ## Add namespaces
    EasyRdf_Namespace::add('mo', 'http://purl.org/ontology/mo/');
    EasyRdf_Namespace::add('bio', 'http://purl.org/vocab/bio/0.1/');
    EasyRdf_TypeMapper::add('mo_MusicArtist', Model_MusicArtist);
    
    $url = $_GET['url'];
?>
<html>
<head><title>Artist Info</title></head>
<body>
<h1>Artist Info</h1>
<form method="get">
<input name="url" type="text" size="48" value="<?= empty($url) ? 'http://www.bbc.co.uk/music/artists/beff21d3-88c7-4ee0-8b7a-40b6db22c6d7.rdf' : $url ?>" />
<input type="submit" />
</form>
<?php
    if ($url) {
        $graph = new EasyRdf_Graph( $url );
        if ($graph) $artist = $graph->primaryTopic();
    }
  
    if ($artist) {
?>

<dl>
  <dt>Artist Name:</dt><dd><?= $artist->get('foaf_name') ?></dd>
  <dt>Type:</dt><dd><?= $artist->type() ?></dd>
  <dt>Homepage:</dt><dd><?= $artist->get('foaf_homepage') ?></dd>
  <dt>Wikipedia page:</dt><dd><?= $artist->get('mo_wikipedia') ?></dd>
</dl>

<?php
      if ($artist->type() == 'mo_MusicGroup') {
      
      
      }

    }
    
    if ($graph) {
        echo "<hr />";
        echo $graph->dump();
    }
?>
</body>
</html>
