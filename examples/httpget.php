<?php
    set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/');
    require_once "EasyRdf/Http/Client.php";
    if (isset($_GET['uri'])) $uri = $_GET['uri'];
    if (isset($_GET['accept'])) $accept = $_GET['accept'];
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
<input name="uri" type="text" size="48" value="<?= empty($uri) ? 'http://tomheath.com/id/me' : htmlspecialchars($uri) ?>" /><br />
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
    if (isset($uri)) {
        $client = new EasyRdf_Http_Client($uri);
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

<?php
    } 
?>

</body>
</html>
