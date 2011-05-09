<?php
    set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/');
    require_once "EasyRdf.php";
    require_once "html_tag_helpers.php";


    class Model_MusicArtist extends EasyRdf_Resource
    {
        function birthEvent()
        {
            foreach ($this->all('bio:event') as $event) {
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

    ## Add namespaces
    EasyRdf_Namespace::set('mo', 'http://purl.org/ontology/mo/');
    EasyRdf_Namespace::set('bio', 'http://purl.org/vocab/bio/0.1/');
    EasyRdf_TypeMapper::set('mo:MusicArtist', 'Model_MusicArtist');
?>
<html>
<head><title>Artist Info</title></head>
<body>
<h1>Artist Info</h1>

<?= form_tag() ?>
<?= text_field_tag('uri', 'http://www.bbc.co.uk/music/artists/70248960-cb53-4ea4-943a-edb18f7d336f.rdf', array('size'=>50)) ?>
<?= submit_tag() ?>
<?= form_end_tag() ?>

<?php
    if (isset($_REQUEST['uri'])) {
        $graph = new EasyRdf_Graph( $_REQUEST['uri'] );
        if ($graph) $artist = $graph->primaryTopic();
    }

    if (isset($artist)) {
?>

<dl>
    <dt>Artist Name:</dt><dd><?= $artist->get('foaf:name') ?></dd>
    <dt>Type:</dt><dd><?= join(', ', $artist->types()) ?></dd>
    <dt>Homepage:</dt><dd><?= link_to($artist->get('foaf:homepage')) ?></dd>
    <dt>Wikipedia page:</dt><dd><?= link_to($artist->get('mo:wikipedia')) ?></dd>
    <?php
        if ($artist->is_a('mo:SoloMusicArtist')) {
            echo "  <dt>Age:</dt>";
            echo "  <dd>".$artist->age()."</dd>\n";
        }
    ?>
</dl>
<?php
    }

    if (isset($graph)) {
        echo $graph->dump();
    }
?>
</body>
</html>
