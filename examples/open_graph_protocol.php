<?php
    /**
     * Example of reading Open Graph Protocol properties
     *
     * Fetches and parses an HTML, reading the Open Graph Protocol data from the page.
     * Open Graph Protocol uses a subset of RDFa.
     *
     * @package    EasyRdf
     * @copyright  Copyright (c) Nicholas J Humfrey
     * @license    http://unlicense.org/
     */

    require_once realpath(__DIR__.'/..')."/vendor/autoload.php";
    require_once __DIR__."/html_tag_helpers.php";

    \EasyRdf\RdfNamespace::setDefault('og');
?>
<html>
<head>
  <title>Open Graph Protocol example</title>
  <style type="text/css">
    body { font-family: sans-serif; }
    dt { font-weight: bold; }
    .image { float: right; margin: 10px; max-width: 240px}
  </style>
</head>
<body>
<?php
  $doc = \EasyRdf\Graph::newAndLoad('https://www.imdb.com/title/tt0240772/');
  if ($doc->image) {
    echo content_tag('img', null, array('src'=>$doc->image, 'class'=>'image'));
  }
?>

<h1>Open Graph Protocol example</h1>
<dl>
  <dt>Page:</dt> <dd><?= link_to($doc->url) ?></dd>
  <dt>Site Name:</dt> <dd><?= $doc->site_name ?></dd>
  <dt>Type:</dt> <dd><code><?= $doc->type ?></code></dd>
  <dt>Title:</dt> <dd><?= $doc->title ?></dd>
  <dt>Description:</dt> <dd><?= $doc->description ?></dd>
</dl>

</body>
</html>
