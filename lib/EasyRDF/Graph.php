<?php

require_once("arc/ARC2.php");

class EasyRDF_Graph
{
    protected $_uri;
    protected $_resources;
    
    
    public function get_resource($uri)
    {
        # Create resource object if it doesn't already exist
        if (!array_key_exists($uri, $this->_resources)) {
            $this->_resources[$uri] = new EasyRDF_Resource($uri);
        }
        return $this->_resources[$uri];
    }
    
    # Return all known resources
    public function resources()
    {
        return array_values($this->_resources);
    }

    
    # TODO: Return all resources of a specific type
    #public static function all_by_type($type)
    #{
    #}

    public function __construct($uri, $data='')
    {
        $this->_uri = $uri;
        $this->_resources = array();
        
        if ($data) {
            $this->parse($data);
        }
    }
    

    public function parse($data)
    {
        $parser = ARC2::getRDFXMLParser();
        $parser->parse($this->_uri, $data);
        
        $index = $parser->getSimpleIndex(false);
        foreach ($index as $subj => $touple) {
          $res = $this->get_resource($subj);
          foreach ($touple as $pred => $objs) {
            foreach ($objs as $obj) {
              if ($obj['type'] == 'literal') {
                $res->set($pred, $obj['value']);
              } else if ($obj['type'] == 'uri' or $obj['type'] == 'bnode') {
                $objres = $this->get_resource($obj['value']);
                $res->set($pred, $objres);
              } else {
                print "Unknown object type: ";
                var_dump($obj);
              }
            }
          }
        
        }
    }
    
    
    public function add_triples($resource, $dict)
    {

    }
	
	
}
