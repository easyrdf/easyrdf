<?php

require_once realpath(dirname(__FILE__) . '/../../') . '/TestHelper.php';


class EasyRdf_Literal_XMLTest extends EasyRdf_TestCase
{
    public function testConstruct()
    {
        $literal = new EasyRdf_Literal_XML('<tag>Hello World</tag>');
        $this->assertClass('EasyRdf_Literal_XML', $literal);
        $this->assertStringEquals('<tag>Hello World</tag>', $literal);
        $this->assertInternalType('string', $literal->getValue());
        $this->assertSame('<tag>Hello World</tag>', $literal->getValue());
        $this->assertSame(null, $literal->getLang());
        $this->assertSame('rdf:XMLLiteral', $literal->getDatatype());
    }

    public function testDomParse()
    {
        $literal = new EasyRdf_Literal_XML('<tag>Hello World</tag>');
        $dom = $literal->domParse();
        $this->assertClass('DOMDocument', $dom);
        $this->assertSame(
            "<?xml version=\"1.0\"?>\n<tag>Hello World</tag>\n",
            $dom->saveXML()
        );
    }
}
