#!/usr/bin/env php
<?php

/* Tool to convert PhpDocumentor HTML output into wiki text.
   This script is wrong but it was quick to write and works.

   TODO:
    - rewrite as PhpDocumenter template
    - add parameter/return value documentation
    - add documentation for constants
    - add links to subversion line numbers
*/

$html_doc_dir = dirname(__FILE__). "/../docs/EasyRdf";
$wiki_doc_dir = dirname(__FILE__). "/../../../wiki";
if (!file_exists($html_doc_dir)) {
    throw new Exception("HTML Documentation directory does not exist: $html_doc_dir");
}
if (!file_exists($wiki_doc_dir)) {
    throw new Exception("Wiki output directory does not exist: $wiki_doc_dir");
}

process_classes($html_doc_dir);



function process_method($html) {
    $output = '';

    if (preg_match("/<h3>(static |)method (.+) <span/", $html, $matches)) {
        $output .= "== ".$matches[2]."() ==\n\n";
        print " - ".$matches[2]."()\n";
    }

    if (preg_match("/<code>\s*(.+)\s*<\/code>/s", $html, $matches)) {
        $code = str_replace('( ', '(', preg_replace("/([\r\n]+)/", ' ', $matches[1]));
        $output .= "{{{".strip_tags($code)."}}}\n\n";
    }
    
    $lines  = preg_split("/[\r\n]+/", $html);
    foreach ($lines as $line) {
        if (preg_match("/^\s+\w+/", $line)) {
            $line = strip_tags(preg_replace("/<br \/>|<\/p>/", "\n", $line));
            $output .= trim($line)."\n\n";
        }
    
    }
    
    return $output;
}

function process_class($html_filepath, $wiki_filepath) {
    print "Processing: $html_filepath\n";
    print "Output: $wiki_filepath\n";
    
    $wiki = fopen($wiki_filepath, 'w');

    $sections  = preg_split("/<hr \/>/", file_get_contents($html_filepath));
    foreach ($sections as $section) {
        if (preg_match("/<div class=\"function\">/", $section)) {
            fwrite($wiki, process_method($section));
        } else if (preg_match("/<div class=\"description\">(.+?)<\/div>/", $section, $matches)) {
            fwrite($wiki, "#summary ".$matches[1]."\n\n");
            fwrite($wiki, "<wiki:toc max_depth='2' />\n\n");
            fwrite($wiki, "= Methods =\n\n");
         }
    }
    
    print "\n";
}

function process_classes($dirpath) {
    global $wiki_doc_dir;
    
    $dh = opendir($dirpath);
    while($filename = readdir($dh)) {
        $html_filepath = "$dirpath/$filename";
        if (is_file($html_filepath) and preg_match("/^(EasyRdf_\w+)/",$filename,$matches)) {
            $wiki_filepath = "$wiki_doc_dir/".$matches[1].".wiki";
            process_class($html_filepath, $wiki_filepath);
        }
    }
    closedir($dh);
}

