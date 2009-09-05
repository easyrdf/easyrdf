<?php
    set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/');
    require_once "EasyRdf/Http/Client.php";
    $url = $_GET['url'];
    $accept = $_GET['accept'];
    $accept_options = array(
      'text/html',
      'application/rdf+xml',
      'application/xhtml+xml',
      'application/json',
      'text/turtle',
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

<form method="get">
<input name="url" type="text" size="48" value="<?= empty($url) ? 'http://tomheath.com/id/me' : htmlspecialchars($url) ?>" /><br />
Accept header:
<select name="accept">
<?
    foreach ($accept_options as $accept_option) {
        if ($accept_option == $accept) {
            echo "<option selected='selected'>";
        } else {
            echo "<option>";
        }
        echo "$accept_option</option>\n";
    }
?>
</select>
<input type="submit" />
</form>

<?php
    if ($url) {
        $client = new EasyRdf_Http_Client($url);
        $client->setHeaders('Accept',$accept);
        $response = $client->request();

?>

    <p class="status">
    <b>Status</b>: <?= $response->getStatus() ?><br />
    <b>Message</b>: <?= $response->getMessage() ?><br />
    <b>Version</b>: HTTP/<?= $response->getVersion() ?><br />
    </p>
    
    <p class="headers">
    <?
        foreach ($response->getHeaders() as $name => $value) {
            echo "<b>$name</b>: $value<br />\n";
        }
    ?>
    </p>
    
    <p class="body">
      <?= nl2br(htmlentities($response->getBody())) ?>
    </p>

<? } ?>

</body>
</html>
