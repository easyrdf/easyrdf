#!/usr/bin/env php
<?php

//
// This wrapper prepares an environment, similar to running
// the example on a real web server.
//

$EXAMPLES_DIR = realpath(dirname(__FILE__) . '/../examples');
if (count($argv) <= 1) {
    print "Error: Missing name of the example to run.\n";
    exit(-1);
} else {
    $THIS_SCRIPT = array_shift($argv);
    $EXAMPLE_FILE = array_shift($argv);
}

// Catch more errors
error_reporting(E_ALL | E_STRICT);

// Set time zone to UTC for running tests
date_default_timezone_set('UTC');

// Change to the examples directory
chdir($EXAMPLES_DIR);

// Check that the example exists
if (!file_exists($EXAMPLE_FILE)) {
    print "Error: example does not exist: $EXAMPLE_FILE\n";
    exit(-1);
}

// Setup the $_GET variable based on command-line arguments
parse_str(
    implode('&', $argv),
    $_GET
);

// Copy the GET parameters into the REQUEST variable
$_REQUEST = $_GET;

// Run the example
require $EXAMPLE_FILE;
