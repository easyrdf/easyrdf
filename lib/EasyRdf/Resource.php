<?php


class EasyRdf_Resource
{
    protected $uri;
    protected $properties;
    
    # This shouldn't be called directly
    public function __construct($uri, $properties='')
    {
        $this->uri = $uri;
        $this->properties = array();
    }
    
    # TODO: Load data for a resource by de-referencing its URI
    #public function load()
    #{
    #    if (!$this->loaded) {
    #    }
    #}

    public function set($property, $object)
    {
        if (isset($this->$property)) {
            $objects = $this->$property;
        } else {
            $objects = array();
        }
        # Add to array of objects, if it isn't already there
        if (!in_array($object, $objects)) {
            array_push($objects, $object);
        }
        $this->$property = $objects;
    }
    
    public function first($property)
    {
        $objects = $this->$property;
        return $objects[0];
    }

    public function __set($key, $value)
    {
        $this->properties[$key] = $value;
    }
    
    public function __get($key)
    {
        // FIXME: how to return single item?
        return $this->properties[$key];
    }
    
    public function __isset($key)
    {
        return array_key_exists($key, $this->properties);
    }
    
    public function __unset($key)
    {
        unset($this->properties[$key]);
    }
    
    public function getUri() {
        return $this->uri;
    }
    
    # Return the resource type as a single word (rather than a URI)
    public function type()
    {
        return $this->first('rdf_type');
    }
    
    public function dump($html=true, $depth=0)
    {
        # FIXME: finish implementing this
        # FIXME: implement reflection for class properties
        echo "<pre>";
        echo "<b>".$this->uri()."</b>\n";
        foreach ($this->properties as $property => $objects) {
          echo "  $property => \n";
          foreach ($objects as $object) {
            echo "    $object\n";
          }
        }
        echo "</pre>";
    }
    
    public function __toString()
    {
        return $this->uri;
    }
}
