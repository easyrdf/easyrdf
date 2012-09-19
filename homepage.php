<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
        "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>EasyRdf</title>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta name="author" content="Nicholas J Humfrey" />
	<link rel="meta" type="application/rdf+xml" title="DOAP" href="doap.rdf" />
	<link rel="stylesheet" href="/njh/pagestyle.css" type="text/css" />
</head>


<body>

<?php
  include_once "markdown.php";
  $readme = file_get_contents('README.md');
  $downloads = json_decode(file_get_contents("https://api.github.com/repos/njh/easyrdf/downloads"));

  $ul = "<ul>\n";
  foreach ($downloads as $download) {
      $ul .= "<li><a href='".htmlspecialchars($download->html_url)."'>".htmlspecialchars($download->name)."</a></li>\n";
  }
  $ul .= "</ul>\n";

  $readme = str_replace('The latest version of EasyRdf can be [downloaded from GitHub].', $ul, $readme);

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
