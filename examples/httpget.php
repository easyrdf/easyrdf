<?php
    set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/');
    require_once "EasyRdf/Http/Client.php";
    $url = $_GET['url'];
?>
<html>
<head><title>Test EasyRdf_HTTP_Client Get</title></head>
<body>
<h1>Test EasyRdf_HTTP_Client Get</h1>

<form method="get">
<input name="url" type="text" size="48" value="<?= empty($url) ? 'http://tomheath.com/id/me' : $url ?>" />
<input type="submit" />
</form>

<?php
    if ($url) {
        $client = new EasyRdf_Http_Client($url);
        $response = $client->request();

?>

<pre>
<b>Status</b>: <?= $response->getStatus() ?> 
<b>Message</b>: <?= $response->getMessage() ?> 
<b>Version</b>: HTTP/<?= $response->getVersion() ?> 
</pre>

<pre>
<?
    foreach ($response->getHeaders() as $name => $value)
    {
        echo "<b>$name</b>: $value\n";
    }
?>
</pre>


<? } ?>

</body>
</html>
