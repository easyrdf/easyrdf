<?php
    /**
     * Display a list of examples and extract the documentation
     * block from the top of each one.
     *
     * @package    EasyRdf
     * @copyright  Copyright (c) 2009-2011 Nicholas J Humfrey
     * @license    http://unlicense.org/
     */

    $dir = dirname(__FILE__);
    $dh = opendir($dir);
    if (!$dh) {
      die("Failed to open directory: $dir\n");
    }

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>EasyRdf - Examples</title>
</head>
<body>

<h1>EasyRdf Examples</h1>

<?php

    $examples = array();
    while (($filename = readdir($dh)) !== false) {
        if ($filename == '.' || $filename == '..' || $filename == 'index.php') {
            continue;
        }

        print "<h2><a href='$filename'>$filename</a></h2>\n";
        $lines = file(
            $dir . DIRECTORY_SEPARATOR . $filename,
            FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
        );

        $startDoc = false;
        $tags = array();
        $text = array();
        $para = '';
        foreach ($lines as $line) {
            if (preg_match("/^\s*\/\*\*/", $line, $m)) {
              $startDoc = true;
              $tags = array();
            } else if ($startDoc && preg_match("/^\s+\*\//", $line, $m)) {
              $text[] = $para;
              break;
            } else if ($startDoc && preg_match("/^\s+\*\s+@(\w+)\s+(.*)/", $line, $m)) {
              $tags[$m[1]] = $m[2];
            } else if ($startDoc && preg_match("/^\s+\*\s*$/", $line, $m)) {
              $text[] = $para;
              $para = '';
           } else if ($startDoc && preg_match("/^\s+\*\s*(.*)/", $line, $m)) {
              if ($para) $para .= ' ';
              $para .= $m[1];
            }
        }

        foreach($text as $paragraph) {
            print "<p>$paragraph</p>\n";
        }

        $examples[$filename] = $text[0];
    }
    closedir($dh);

    // Create some Markdown that can be pasted into README.md
    print "<!--\n";
    foreach($examples as $filename => $desc) {
        print "* [$filename](https://github.com/njh/easyrdf/blob/master/examples/$filename#slider) - $desc\n";
    }
    print "-->\n";

?>

</body>
</html>
