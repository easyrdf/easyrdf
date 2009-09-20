<?php

require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'TestHelper.php';
require_once 'EasyRdf/ArcParser.php';

class EasyRdf_ArcParserTest extends PHPUnit_Framework_TestCase
{
    // FIXME: skip tests if ARC isn't available
    
    function testDummy() {
        $this->markTestIncomplete();
    }
}
