<?php
    set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/');
    require_once "EasyRdf.php";
    require_once "html_tag_helpers.php";

    $serialiser_options = array(
      'Builtin' => 'Builtin',
      'ARC 2' => 'Arc',
      'Rapper' => 'Rapper',
    );

    $output_options = array(
      'N-Triples' => 'ntriples',
      'RDF/PHP' => 'php',
      'RDF/JSON' => 'json',
      'RDF/XML' => 'rdfxml',
      'poshRDF' => 'poshrdf',
      'Turtle' => 'turtle',
    );
?>
<html>
<head><title>FOAF Maker</title></head>
<body>
<h1>FOAF Maker</h1>

<?= form_tag(null,array('method' => 'POST')) ?>

<h2>Your Identifier</h2>
<?= labeled_text_field_tag('uri', 'http://www.example.com/joe#me', array('size'=>40)) ?><br />

<h2>Your details</h2>
<?= labeled_text_field_tag('title', 'Mr', array('size'=>8)) ?><br />
<?= labeled_text_field_tag('given_name', 'Joseph') ?><br />
<?= labeled_text_field_tag('family_name', 'Bloggs') ?><br />
<?= labeled_text_field_tag('nickname', 'Joe') ?><br />
<?= labeled_text_field_tag('email', 'joe@example.com') ?><br />
<?= labeled_text_field_tag('homepage', 'http://www.example.com/', array('size'=>40)) ?><br />

<h2>People you know</h2>
<?= labeled_text_field_tag('person_1', 'http://www.example.com/dave#me', array('size'=>40)) ?><br />
<?= labeled_text_field_tag('person_2', '', array('size'=>40)) ?><br />
<?= labeled_text_field_tag('person_3', '', array('size'=>40)) ?><br />
<?= labeled_text_field_tag('person_4', '', array('size'=>40)) ?><br />

<h2>Output</h2>
<?= label_tag('serialiser').select_tag('serialiser', $serialiser_options, 'builtin') ?><br />
<?= label_tag('format').select_tag('format', $output_options, 'rdfxml') ?><br />

<?= submit_tag() ?>
<?= form_end_tag() ?>


<?php
    if (isset($_REQUEST['uri'])) {
    
        require_once "EasyRdf/Serialiser/".$_REQUEST['serialiser'].'.php';
        $serialiser = "EasyRdf_Serialiser_".$_REQUEST['serialiser'];
        EasyRdf_Graph::setRdfSerialiser(new $serialiser());
        $graph = new EasyRdf_Graph();
        
        # 1st Technique
        $me = $graph->get($_REQUEST['uri'], 'foaf:Person');
        $me->set('foaf:name', $_REQUEST['title'].' '.$_REQUEST['given_name'].' '.$_REQUEST['family_name']);
        if ($_REQUEST['email']) {
            $email = $graph->get("mailto:".$_REQUEST['email']);
            $me->add('foaf:mbox', $email);
        }
        if ($_REQUEST['homepage']) {
            $homepage = $graph->get($_REQUEST['homepage']);
            $me->add('foaf:homepage', $homepage);
        }
        
        # 2nd Technique
        $graph->add( $_REQUEST['uri'], array(
            'foaf:title' => $_REQUEST['title'],
            'foaf:givenname' => $_REQUEST['given_name'],
            'foaf:family_name' => $_REQUEST['family_name'],
            'foaf:nick' => $_REQUEST['nickname']
        ));
        
        # Add friends
        for($i=1; $i<=4; $i++) {
            if ($_REQUEST["person_$i"]) {
                $person = $graph->get($_REQUEST["person_$i"]);
                $graph->add($me, 'foaf:knows', $person);
            }      
        }

        # Finally output the graph
        $data = $graph->serialise($_REQUEST['format']);
        if (!is_scalar($data)) {
            $data = var_export($data, true);
        }
        print "<pre>".htmlspecialchars($data)."</pre>";
    }
  
?>

</body>
</html>
