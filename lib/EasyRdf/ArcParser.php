<?php

require_once 'arc/ARC2.php';

class EasyRdf_ArcParser
{

    public function parse($uri, $data, $doc_type='guess')
    {
        switch($doc_type) {
            case 'application/json':
                $parser = ARC2::getJSONParser();
            break;

            case 'application/rdf+xml':
               $parser = ARC2::getRDFXMLParser();
            break;
            
            case 'text/turtle':
            case 'text/n3':
                $parser = ARC2::getTurtleParser();
            break;
        
            default:
                echo "<pre>EasyRdf_ArcParser: unsupported type $doc_type\n</pre>";
                # FIXME: throw exception?
            break;
        }
        
        if ($parser) {
            $parser->parse($uri, $data);
            return $parser->getSimpleIndex(false);
        } else {
            return null;
        }
    }
}
