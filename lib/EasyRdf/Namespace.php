<?php

/**
  * A namespace registry and manipulation class.
  */
class EasyRdf_Namespace
{
    private static $_namespaces = array(
      'dc' => 'http://purl.org/dc/elements/1.1/',
      'foaf' => 'http://xmlns.com/foaf/0.1/',
      'owl' => 'http://www.w3.org/2002/07/owl#',
      'rdf' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
      'rdfs' => 'http://www.w3.org/2000/01/rdf-schema#',
      'xhtml' => 'http://www.w3.org/1999/xhtml/vocab#',
      'xsd' => 'http://www.w3.org/2001/XMLSchema#'
    );

    /**
      * Return a namespace given its prefix.
      *
      * @param string $short The namespace prefix (eg 'foaf')
      * @return string The namespace URI (eg 'http://xmlns.com/foaf/0.1/')
      */
    public static function get($short)
    {
        $short = strtolower($short);
        if (array_key_exists($short, self::$_namespaces)) {
            return self::$_namespaces[$short];
        } else {
            return null;
        }
    }

    /**
      * Register a new namespace.
      *
      * @param string $short The namespace prefix (eg 'foaf')
      * @param string $long The namespace URI (eg 'http://xmlns.com/foaf/0.1/')
      */
    public static function add($short, $long)
    {
        $short = strtolower($short);
        self::$_namespaces[$short] = $long;
    }

    /**
      * Return the short namespace that a URI belongs to.
      *
      * @param string $uri A full URI (eg 'http://xmlns.com/foaf/0.1/name')
      * @return string The short namespace that it is a part of(eg 'foaf')
      */
    public static function namespaceOfUri($uri)
    {
        foreach (self::$_namespaces as $short => $long) {
            if (strpos($uri, $long) === 0) {
                return $short;
            }
        }
        return null;
    }

    /**
      * Shorten a URI by substituting in the namespace prefix.
      *
      * @param string $uri The full URI (eg 'http://xmlns.com/foaf/0.1/name')
      * @return string The shortened URI (eg 'foaf_name')
      */
    public static function shorten($uri)
    {
        foreach (self::$_namespaces as $short => $long) {
            if (strpos($uri, $long) === 0) {
                return $short . '_' . substr($uri, strlen($long));
            }
        }
        return null;
    }

    /**
      * Expand a shortened URI back into a full URI.
      *
      * @param string $shortUri The short URI (eg 'foaf_name')
      * @return string The full URI (eg 'http://xmlns.com/foaf/0.1/name')
      */
    public static function expand($shortUri)
    {
        if (preg_match("/^(\w+?)_(.+)$/", $shortUri, $matches)) {
            $long = self::get($matches[1]);
            if ($long) {
                return $long . $matches[2];
            } else {
                return null;
            }
        } else {
            return null;
        }
    }
}
