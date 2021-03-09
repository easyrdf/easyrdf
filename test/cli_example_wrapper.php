#!/usr/bin/env php
<?php

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) Nicholas J Humfrey.  All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 * 3. The name of the author 'Nicholas J Humfrey" may be used to endorse or
 *    promote products derived from this software without specific prior
 *    written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) Nicholas J Humfrey
 * @license    https://www.opensource.org/licenses/bsd-license.php
 */

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
