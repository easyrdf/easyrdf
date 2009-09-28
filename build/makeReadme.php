<?php

## FIXME: write Text_Wiki_GoogleCode package

require_once 'Text/Wiki.php';

$files = array(
    'README' => 'http://easyrdf.googlecode.com/svn/wiki/ReadMe.wiki',
    'LICENSE' => 'http://easyrdf.googlecode.com/svn/wiki/License.wiki',
    'ROADMAP' => 'http://easyrdf.googlecode.com/svn/wiki/Roadmap.wiki'
);

$wiki = new Text_Wiki();
foreach ($files as $name => $url) {
    $markup = file_get_contents($url);
    $text = $wiki->transform($markup, 'Plain');
    $filepath = dirname(__FILE__)."../$name";
    file_put_contents( $name, $text );
}
