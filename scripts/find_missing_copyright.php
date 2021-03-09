<?php

$ROOT = realpath(__DIR__ . '/..');

function process_file($path) {
    $year = date('Y', filemtime($path));
    $contents = file_get_contents($path);

    $copy_statements = 0;
    foreach (preg_split("/[\r\n]/", $contents) as $line) {
        if (preg_match("/Copyright \(c\) Nicholas J Humfrey/", $line, $m)) {
            $copy_statements++;
        }
    }

    if ($copy_statements == 0) {
        print "Warning: $path does not contain any copyright statements\n";
    }
}


function process_directory($path) {
    $dir = opendir($path);

    while ($file = readdir($dir)) {
        if (substr($file, 0, 1) == '.') {
            continue;
        }

        $filepath = $path . '/' . $file;
        if (is_dir($filepath)) {
            process_directory($filepath);
        } elseif (is_file($filepath)) {
            if (substr($file, -4) == '.php') {
                process_file($filepath);
            }
        } else {
            print "Unknown type: $filepath\n";
        }
    }

    closedir($dir);
}

process_directory($ROOT . '/examples');
process_directory($ROOT . '/lib');
process_directory($ROOT . '/test');
