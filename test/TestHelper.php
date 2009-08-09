<?php

/*
 * Include PHPUnit dependencies
 */
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Framework/IncompleteTestError.php';
require_once 'PHPUnit/Framework/TestCase.php';
require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/Runner/Version.php';
require_once 'PHPUnit/TextUI/TestRunner.php';
require_once 'PHPUnit/Util/Filter.php';

/*
 * Set error reporting to the level to be stricter.
 */
error_reporting( E_ALL | E_STRICT );

/*
 * Determine the root, lib, and test directories
 */
$easyrdfRoot      = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..');
$easyrdfLibDir    = $easyrdfRoot . DIRECTORY_SEPARATOR . 'lib';
$easyrdfTestDir   = $easyrdfRoot . DIRECTORY_SEPARATOR . 'test';

/*
 * Prepend the lib and test directories to the  include_path. 
 */
$path = array(
    $easyrdfLibDir,
    $easyrdfTestDir,
    get_include_path()
    );
set_include_path(implode(PATH_SEPARATOR, $path));

/*
 * Unset global variables that are no longer needed.
 */
unset($easyrdfRoot, $easyrdfLibDir, $easyrdfTestDir, $path);


/**
 * Helper function: read fixture data from file
 *
 * @param string $name fixture file name
 * @return string Fixture data
 */
function readFixture($name)
{
    return file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . $name);
}
