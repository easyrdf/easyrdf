<!DOCTYPE html>
<html>
<head>
  <title>EasyRdf Examples</title>
</head>
<body>
<h1>EasyRdf Examples</h1>
<?php
  $dh = opendir(__DIR__);
  if (!$dh) {
      die("Failed to open directory: " . __DIR__);
  }

  $exampleList = array();
  while (($filename = readdir($dh)) !== false) {
      if (substr($filename, 0, 1) == '.' or
          $filename == 'index.php' or
          $filename == 'html_tag_helpers.php') {
          continue;
      }

      $exampleList[] = $filename;
  }
  closedir($dh);

  sort($exampleList);

  echo "<ul>";
  foreach($exampleList as $example) {
      echo "<li><a href='./$example'>$example</a></li>\n";
  }
  echo "</ul>";
?>
</body>
</html>
