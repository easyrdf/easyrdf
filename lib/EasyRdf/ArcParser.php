<?php

require_once 'arc/ARC2.php';

class EasyRdf_ArcParser
{
    private static $_supportedTypes = array(
        'json' => 'JSON',
        'rdfxml' => 'RDFXML',
        'turtle' => 'Turtle',
        'rdfa' => 'SemHTML',
    );

    public function parse($uri, $data, $docType)
    {
        if (array_key_exists($docType, self::$_supportedTypes)) {
            $className = self::$_supportedTypes[$docType];
        } else {
            # FIXME: throw exception?
            return null;
        }
        
        $parser = ARC2::getParser($className);
        if ($parser) {
            $parser->parse($uri, $data);
            return $parser->getSimpleIndex(false);
        } else {
            # FIXME: throw exception?
            return null;
        }
    }
}
