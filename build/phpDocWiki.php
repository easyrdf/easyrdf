#!/usr/bin/env php
<?php

/*
   Tool to convert PHPDoc for classes into Wiki text.

   TODO:
    - rewrite as PhpDocumenter/Doxygen output template
    - add parameter/return value documentation
    - add documentation for constants
    - add links to subversion line numbers
*/

$source_dir = dirname(__FILE__). "/../lib/EasyRdf";
$wiki_doc_dir = dirname(__FILE__). "/../../../wiki";
if (!file_exists($source_dir)) {
    throw new Exception("Source directory does not exist: $source_dir");
}
if (!file_exists($wiki_doc_dir)) {
    throw new Exception("Wiki output directory does not exist: $wiki_doc_dir");
}

process_directory($source_dir);


function output_methods($wiki, $methods)
{
    ksort($methods);
    foreach($methods as $name => $method) {
        fwrite($wiki, "== $name() ==\n\n");
        fwrite($wiki, "{{{".$method['signature']."}}}\n\n");
        fwrite($wiki, $method['docs']."\n\n");
    }
}

function output_class($class_name, $class_docs, $class_methods, $member_methods)
{
    global $wiki_doc_dir;
    
    $wiki = fopen("$wiki_doc_dir/$class_name.wiki",'w');
    if ($wiki) {
        fwrite($wiki, "#summary $class_docs\n\n");
        fwrite($wiki, "<wiki:toc max_depth='2' />\n\n");
        if (count($class_methods)>0) {
            fwrite($wiki, "= Class Methods =\n\n");
            output_methods($wiki, $class_methods);
        }
        if (count($member_methods)>0) {
            fwrite($wiki, "= Member Methods =\n\n");
            output_methods($wiki, $member_methods);
        }
    }
    fclose($wiki);
}

function process_file($filepath)
{
    print "Processing: $filepath\n";
    
    $state = 'OUTDOC';
    $class_name = null;
    $class_docs = '';
    $class_methods = array();
    $member_methods = array();

    $lines = preg_split("/[\r\n]+/", file_get_contents($filepath));
    while ($line = array_shift($lines)) {
        if (preg_match("{^\s*/\*\*\s*(.*)\s*$}", $line, $matches)) {
            $docs = $matches[1] ? $matches[1]."\n" : "";
            $state = 'INDOC';
        } else if ($state == 'INDOC' and preg_match("{^\s*\* ([^/].+)$}", $line, $matches)) {
            $line = $matches[1];
            if (preg_match("{^@}", $line)) {
                # FIXME: insert tag handling code here
            } else {
                $docs .= "$line\n";
            }
        } else if (preg_match("{\*/}", $line)) {
            $state = 'OUTDOC';
        } else if (preg_match("{^\s*class (\w+)}", $line, $matches)) {
            $class_name = $matches[1];
            $class_docs = trim($docs);
        } else if (preg_match("{public (static )?function (\w+)(.*)\s*$}", $line, $matches)) {
            $func = array(
                'signature' => $matches[1]."function ".$matches[2].$matches[3],
                'name' => $matches[2],
                'docs' => trim($docs)
            );
            if ($matches[1] == 'static ') {
                $class_methods[$matches[2]] = $func;
            } else {
                $member_methods[$matches[2]] = $func;
            }
        }
    }
    
    if ($class_name) {
        output_class($class_name, $class_docs, $class_methods, $member_methods);
    }
}

function process_directory($dirpath)
{
    $dh = opendir($dirpath);
    while($filename = readdir($dh)) {
        $filepath = realpath("$dirpath/$filename");
        if (is_file($filepath) and preg_match("/\.php$/",$filename)) {
            process_file($filepath);
        } else if (is_dir($filepath) and substr($filename,0,1) != '.') {
            process_directory($filepath);
        }
    }
    closedir($dh);
}

