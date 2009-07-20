<?php


class EasyRDF_Resource
{
    protected $_uri;
    protected $_data;
    
    # Return the primary topic of a document
    public static function get_primary_topic($uri)
    {
        $doc = EasyRDF_Resource::get($uri);
        return $doc->foaf_primaryTopic;
    }
    
    
    # This shouldn't be called directly
    public function __construct($uri, $data='')
    {
        $this->_uri = $uri;
        $this->_data = array();
    }
    
    # TODO: Load data for a resource by de-referencing its URI
    #public function load()
    #{
    #    if (!$this->_loaded) {
    #    }
    #}

    public function get($predicate)
    {
        return $this->_data[$predicate];
    }

    public function set($predicate, $object)
    {
        # Add to array of objects, if it isn't already there
        if (!array_key_exists($predicate, $this->_data)) {
            $this->_data[$predicate] = array();
        }
        if (!in_array($object, $this->_data[$predicate])) {
            array_push($this->_data[$predicate], $object);
        }
    }

    # eg. $artist->foaf_name = "Foo Fighters"
    #     $artist->_foaf_name = "Foo Bar"
    public function __set($predicate, $object)
    {
        # TODO: Implement this
    }
    
    # Example: $artist->foaf_name = "Foo Fighters"
    #          $artist->_foaf_name = array('Foo Fighters', 'foo foo')
    #
    public function __get($predicate)
    {
        if (!preg_match('/^(_?)(\w+)_(.+)$/', $predicate, $matches)) {
            throw new Exception('Invalid predicate.'); 
        }

        $key = EasyRDF_Namespace::get($matches[2]) . $matches[3];
        if (array_key_exists($key, $this->_data)) {
            if ($matches[1] == '_') {
                return $this->_data[$key];
            } else {
                return $this->_data[$key][0];
            }
        } else {
            return NULL;
        }
    }
    
    public function uri() {
        return $this->_uri;
    }
    
    # Return the resource type as a single word (rather than a URI)
    public function type()
    {
        return $this->rdfs_type;
    }

    public function add_triples($dict)
    {
  
    }
    
    public function __toString()
    {
        return $this->_uri;
    }
}
