<?php

require_once "EasyRdf/Namespace.php";

class EasyRdf_Resource
{
    /** The URI for this resource */
    protected $uri = null;
    
    /** The type(s) of this resource */
    protected $rdf_type = array();
    
    /** Associative array of properties - uses by magic methods */
    protected $properties = array();
    
    
    public static function disableMagic()
    {
    
    }
    
    public static function enableMagic()
    {
    
    }
    
    # This shouldn't be called directly
    public function __construct($uri)
    {
        $this->uri = $uri;
    }

    public function set($property, $object)
    {
        if ($property == null or $object == null) {
            return null;
        } else if (isset($this->$property)) {
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
    
    public function first($property)
    {
        if (isset($this->$property)) {
            if (is_array($this->$property)) {
                $objects = $this->$property;
                return $objects[0];
            } else {
                return $this->$property;
            }
        } else {
            return null;
        }
    }
    
    public function all($property)
    {
        if (isset($this->$property)) {
            if (is_array($this->$property)) {
                return $this->$property;
            } else {
                return array($this->$property);
            }
        } else {
            return array();
        }
    }
    
    public function join($property, $glue=' ')
    {
        return join( $glue, $this->all($property) );
    }
    
    public function getUri() {
        return $this->uri;
    }
    
    # Return an array of this resource's types
    public function types()
    {
        return $this->all('rdf_type');
    }
    
    # Return the resource type as a single word (rather than a URI)
    public function type()
    {
        return $this->first('rdf_type');
    }
    
    # Return the namepace that this resource is part of
    public function ns()
    {
        return EasyRdf_Namespace::namespaceOfUri($this->uri);
    }
    
    public function shorten()
    {
        return EasyRdf_Namespace::shorten($this->uri);
    }
    
    public function label()
    {
        if (isset($this->rdfs_label)) {
            return $this->first('rdfs_label');
        } else if (isset($this->foaf_name)) {
            return $this->first('foaf_name');
        } else if (isset($this->dc_title)) {
            return $this->first('dc_title');
        } else {
            return EasyRdf_Namespace::shorten($this->uri); 
        }
    }
    
    public function dump($html=true, $depth=0)
    {
        # FIXME: finish implementing this
        # FIXME: implement reflection for class properties
        echo '<pre>';
        echo '<b>'.$this->getUri()."</b>\n";
        echo 'Class: '.get_class($this)."\n";
        echo 'Types: '.implode(', ',$this->types())."\n";
        echo "Properties:</i>\n";
        foreach ($this->properties as $property => $objects)
        {
            echo "  $property => \n";
            foreach ($objects as $object)
            {
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

