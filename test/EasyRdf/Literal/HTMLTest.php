<?php

require_once realpath(dirname(__FILE__) . '/../../') . '/TestHelper.php';


class EasyRdf_Literal_HTMLTest extends EasyRdf_TestCase
{
    public function testConstruct()
    {
        $literal = new EasyRdf_Literal_HTML('<p>Hello World</p>');
        $this->assertClass('EasyRdf_Literal_HTML', $literal);
        $this->assertStringEquals('<p>Hello World</p>', $literal);
        $this->assertInternalType('string', $literal->getValue());
        $this->assertSame('<p>Hello World</p>', $literal->getValue());
        $this->assertSame(null, $literal->getLang());
        $this->assertSame('rdf:HTML', $literal->getDatatype());
    }

    public function testStripTags()
    {
        $literal = new EasyRdf_Literal_HTML('<p>Hello World</p>');
        $this->assertSame('Hello World', $literal->stripTags());
    }

    public function testStripTagsWithAllowable()
    {
        $literal = new EasyRdf_Literal_HTML(
            '<script src="foo"></script><p>Hello World</p><foo>'
        );
        $this->assertSame(
            '<p>Hello World</p>',
            $literal->stripTags('<p>')
        );
    }
}
