<?php
    /**
     * Basic "Hello World" type example
     *
     * A new EasyRdf\Graph object is created and then the contents
     * of my FOAF profile is loaded from the web. An EasyRdf\Resource for
     * the primary topic of the document (me, Nicholas Humfrey) is returned
     * and then used to display my name.
     *
     * @package    EasyRdf
     * @copyright  Copyright (c) 2009-2013 Nicholas J Humfrey
     * @license    http://unlicense.org/
     */

    require_once realpath(__DIR__.'/..')."/vendor/autoload.php";
?>
<html>
<head>
  <title>Basic FOAF example</title>
</head>
<body>

<?php
  $foaf = \EasyRdf\Graph::newAndLoad('http://njh.me/foaf.rdf');
  $me = $foaf->primaryTopic();
?>

<p>
  My name is: <?= $me->get('foaf:name') ?>
</p>

</body>
</html>
