<?php


class EasyRdf_Resource
{
    protected $_uri;
    protected $_data;
    
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

    public function set($predicate, $object)
    {
        if (isset($this->$predicate)) {
            $objects = $this->$predicate;
        } else {
            $objects = array();
        }
        # Add to array of objects, if it isn't already there
        if (!in_array($object, $objects)) {
            array_push($objects, $object);
        }
        $this->$predicate = $objects;
    }
    
    public function first($predicate)
    {
        $objects = $this->$predicate;
        return $objects[0];
    }

    public function __set($key, $value)
    {
        $this->_data[$key] = $value;
    }
    
    public function __get($key)
    {
        // FIXME: how to return single item?
        return $this->_data[$key];
    }
    
    public function __isset($key)
    {
        return array_key_exists($key, $this->_data);
    }
    
    public function __unset($key)
    {
        unset($this->_data[$key]);
    }
    
    public function uri() {
        return $this->_uri;
    }
    
    # Return the resource type as a single word (rather than a URI)
    public function type()
    {
        return $this->first('rdfs_type');
    }
    
    public function __toString()
    {
        return $this->_uri;
    }
}
