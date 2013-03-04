<?php
    /**
     * Construct a FOAF document with a choice of serialisations
     *
     * This example is similar in concept to Leigh Dodds' FOAF-a-Matic.
     * The fields in the HTML form are inserted into an empty
     * EasyRdf_Graph and then serialised to the chosen format.
     *
     * @package    EasyRdf
     * @copyright  Copyright (c) 2009-2013 Nicholas J Humfrey
     * @license    http://unlicense.org/
     */

    set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/');
    require_once "EasyRdf.php";
    require_once "html_tag_helpers.php";

    if (isset($_REQUEST['enable_arc']) && $_REQUEST['enable_arc']) {
        require_once "EasyRdf/Serialiser/Arc.php";
        EasyRdf_Format::registerSerialiser('ntriples', 'EasyRdf_Serialiser_Arc');
        EasyRdf_Format::registerSerialiser('posh', 'EasyRdf_Serialiser_Arc');
        EasyRdf_Format::registerSerialiser('rdfxml', 'EasyRdf_Serialiser_Arc');
        EasyRdf_Format::registerSerialiser('turtle', 'EasyRdf_Serialiser_Arc');
    }

    if (isset($_REQUEST['enable_rapper']) && $_REQUEST['enable_rapper']) {
        require_once "EasyRdf/Serialiser/Rapper.php";
        EasyRdf_Format::registerSerialiser('dot', 'EasyRdf_Serialiser_Rapper');
        EasyRdf_Format::registerSerialiser('rdfxml', 'EasyRdf_Serialiser_Rapper');
        EasyRdf_Format::registerSerialiser('turtle', 'EasyRdf_Serialiser_Rapper');
    }

    $format_options = array();
    foreach (EasyRdf_Format::getFormats() as $format) {
        if ($format->getSerialiserClass()) {
            $format_options[$format->getLabel()] = $format->getName();
        }
    }
?>
<html>
<head><title>EasyRdf FOAF Maker Example</title></head>
<body>
<h1>EasyRdf FOAF Maker Example</h1>

<?= form_tag(null, array('method' => 'POST')) ?>

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
Enable Arc 2? <?= check_box_tag('enable_arc') ?><br />
Enable Rapper? <?= check_box_tag('enable_rapper') ?><br />
<?= label_tag('format').select_tag('format', $format_options, 'rdfxml') ?><br />

<?= submit_tag() ?>
<?= form_end_tag() ?>


<?php
    if (isset($_REQUEST['uri'])) {

        $graph = new EasyRdf_Graph();

        # 1st Technique
        $me = $graph->resource($_REQUEST['uri'], 'foaf:Person');
        $me->set('foaf:name', $_REQUEST['title'].' '.$_REQUEST['given_name'].' '.$_REQUEST['family_name']);
        if ($_REQUEST['email']) {
            $email = $graph->resource("mailto:".$_REQUEST['email']);
            $me->add('foaf:mbox', $email);
        }
        if ($_REQUEST['homepage']) {
            $homepage = $graph->resource($_REQUEST['homepage']);
            $me->add('foaf:homepage', $homepage);
        }

        # 2nd Technique
        $graph->addLiteral($_REQUEST['uri'], 'foaf:title', $_REQUEST['title']);
        $graph->addLiteral($_REQUEST['uri'], 'foaf:givenname', $_REQUEST['given_name']);
        $graph->addLiteral($_REQUEST['uri'], 'foaf:family_name', $_REQUEST['family_name']);
        $graph->addLiteral($_REQUEST['uri'], 'foaf:nick', $_REQUEST['nickname']);

        # Add friends
        for($i=1; $i<=4; $i++) {
            if ($_REQUEST["person_$i"]) {
                $person = $graph->resource($_REQUEST["person_$i"]);
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
