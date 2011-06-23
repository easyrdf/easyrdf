<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
        "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>EasyRdf</title>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta name="author" content="Nicholas J Humfrey" />
	<link rel="stylesheet" href="/njh/pagestyle.css" type="text/css" />
</head>


<body>

<?php
  include_once "markdown.php";
  $readme = file_get_contents('README.md');

  $downloads = "<ul>\n";
  $lines = preg_split("/[\n\r]+/",file_get_contents("http://github.com/njh/easyrdf/downloads"));
  foreach ($lines as $line) {
      if (preg_match('[<a href="(/downloads/njh/easyrdf/.+)">(.+)</a>]', $line, $matches)) {
          $downloads .= "<li><a href='http://github.com$matches[1]'>$matches[2]</a></li>\n";
      }
  }
  $downloads .= "</ul>\n";
  
  $readme = str_replace('The latest version of EasyRdf can be downloaded from GitHub.', $downloads, $readme);

  $html = Markdown($readme);
  $html = str_replace('<em>', '_', $html);
  $html = str_replace('</em>', '_', $html);
  print $html;
?>

<hr />

<address>
	<a href="http://validator.w3.org/check?uri=referer">
	<img class="float-right" src="/njh/img/valid-xhtml11.png" alt="Valid XHTML 1.1!" height="31" width="88" />
	</a>
	Nicholas J Humfrey &lt;<a href="mailto:njh@aelius.com">njh@aelius.com</a>&gt;
</address>

</body>
</html>
