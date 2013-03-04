<?php
    /**
     * Basic serialisation example
     *
     * This example create a simple FOAF graph in memory and then
     * serialises it to the page in the format of choice.
     *
     * @package    EasyRdf
     * @copyright  Copyright (c) 2009-2013 Nicholas J Humfrey
     * @license    http://unlicense.org/
     */

    set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/');
    require_once "EasyRdf.php";

    $graph = new EasyRdf_Graph();
    $me = $graph->resource('http://www.example.com/joe#me', 'foaf:Person');
    $me->set('foaf:name', 'Joseph Bloggs');
    $me->set('foaf:title', 'Mr');
    $me->set('foaf:nick', 'Joe');
    $me->add('foaf:homepage', $graph->resource('http://example.com/joe/'));

    // I made these up; they are not officially part of FOAF
    $me->set('foaf:dateOfBirth', new EasyRdf_Literal_Date('1980-09-08'));
    $me->set('foaf:height', 1.82);

    $project = $graph->newBnode('foaf:Project');
    $project->set('foaf:name', "Joe's current project");
    $me->set('foaf:currentProject', $project);

    if (isset($_REQUEST['format'])) {
        $format = preg_replace("/[^\w\-]+/", '', strtolower($_REQUEST['format']));
    } else {
        $format = 'ntriples';
    }
?>
<html>
<head><title>EasyRdf Serialiser Example</title></head>
<body>
<h1>EasyRdf Serialiser Example</h1>

<ul>
<?php
    foreach (EasyRdf_Format::getFormats() as $f) {
        if ($f->getSerialiserClass()) {
            if ($f->getName() == $format) {
                print "<li><b>".$f->getLabel()."</b></li>\n";
            } else {
                print "<li><a href='?format=$f'>";
                print $f->getLabel()."</a></li>\n";
            }
        }
    }
?>
</ul>

<pre style="margin: 0.5em; padding:0.5em; background-color:#eee; border:dashed 1px grey;">
<?php
    $data = $graph->serialise($format);
    if (!is_scalar($data)) {
        $data = var_export($data, true);
    }
    print htmlspecialchars($data);
?>
</pre>

</body>
</html>
