<?php

require_once "EasyRdf/Namespace.php";
require_once "EasyRdf/Resource.php";
require_once "EasyRdf/TypeMapper.php";

class EasyRdf_Owl_Class extends EasyRdf_Resource
{
    function className()
    {
        return ucfirst($this->shorten());
    }
    
    function fileName()
    {
        return str_replace('_','/',$this->className()) . '.php';
    }

    # FIXME: not ideal having to pass graph in here
    function properties($graph)
    {
        $properties = array();
        # FIXME: cache this somehow?
        $owl_thing = $graph->getResource('http://www.w3.org/2002/07/owl#Thing');
        $superClass = $this->get('rdfs_subClassOf');
        if ($superClass == $owl_thing) $superClass = '';
        $all_properties = EasyRdf_Owl_Property::findAll($graph);
        foreach ($all_properties as $name => $property) {
            if (($superClass == '' and
                (count($property->all('rdfs_domain')) == 0 or 
                in_array($owl_thing, $property->all('rdfs_domain')))) or 
                in_array($this, $property->all('rdfs_domain')))
            {
                array_push($properties, $property);
            }
        }
        return $properties;
    }
}

EasyRdf_TypeMapper::add('owl_Class', 'EasyRdf_Owl_Class');
