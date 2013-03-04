<?php
    /**
     * No RDF, just test EasyRdf_Http_Client
     *
     * This example does nothing but test EasyRdf's build in HTTP client.
     * It demonstrates setting Accept headers and displays the response
     * headers and body.
     *
     * @package    EasyRdf
     * @copyright  Copyright (c) 2009-2013 Nicholas J Humfrey
     * @license    http://unlicense.org/
     */

    set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/');
    require_once "EasyRdf.php";
    require_once "html_tag_helpers.php";

    $accept_options = array(
      'text/html' => 'text/html',
      'application/rdf+xml' => 'application/rdf+xml',
      'application/xhtml+xml' => 'application/xhtml+xml',
      'application/json' => 'application/json',
      'text/turtle' => 'text/turtle',
    );
?>
<html>
<head>
  <title>Test EasyRdf_HTTP_Client Get</title>
  <style type="text/css">
    .body
    {
      width: 800px;
      font-family: monospace;
      font-size: 0.8em;
    }
  </style>
</head>
<body>
<h1>Test EasyRdf_HTTP_Client Get</h1>
<?= form_tag() ?>
<?= text_field_tag('uri', 'http://tomheath.com/id/me', array('size'=>50)) ?><br />
<?= label_tag('accept', 'Accept Header: ').select_tag('accept',$accept_options) ?>
<?= submit_tag() ?>
<?= form_end_tag() ?>

<?php
    if (isset($_REQUEST['uri'])) {
        $client = new EasyRdf_Http_Client($_REQUEST['uri']);
        $client->setHeaders('Accept',$_REQUEST['accept']);
        $response = $client->request();

?>

    <p class="status">
    <b>Status</b>: <?= $response->getStatus() ?><br />
    <b>Message</b>: <?= $response->getMessage() ?><br />
    <b>Version</b>: HTTP/<?= $response->getVersion() ?><br />
    </p>

    <p class="headers">
    <?php
        foreach ($response->getHeaders() as $name => $value) {
            echo "<b>$name</b>: $value<br />\n";
        }
    ?>
    </p>

    <p class="body">
      <?php
        if (defined('ENT_SUBSTITUTE')) {
            // This is needed for PHP 5.4+
            print nl2br(htmlentities($response->getBody(), ENT_SUBSTITUTE | ENT_QUOTES));
        } else {
            print nl2br(htmlentities($response->getBody()));
        }
      ?>
    </p>

<?php
    }
?>

</body>
</html>
