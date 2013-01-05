<?php

use Sami\Sami;
use Symfony\Component\Finder\Finder;

$root = realpath(__DIR__ . "/..");
$iterator = Finder::create()
    ->files()
    ->name('*.php')
    ->exclude('arc')
    ->in($root.'/lib')
;

return new Sami($iterator, array(
    'title'               => 'EasyRdf API Documentation',
    'theme'               => 'enhanced',
    'build_dir'           => "$root/docs/api",
    'cache_dir'           => "$root/samicache",
    'include_parent_data' => true,
    'simulate_namespaces' => true,
    'default_opened_level' => 1,
));
