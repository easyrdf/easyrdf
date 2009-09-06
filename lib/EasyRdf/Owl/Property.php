<?php

require_once "EasyRdf/Namespace.php";
require_once "EasyRdf/Resource.php";
require_once "EasyRdf/TypeMapper.php";

class EasyRdf_Owl_Property extends EasyRdf_Resource
{

    public static function findAll($graph)
    {
        $property_types = array('rdf_Property','owl_Property','owl_ObjectProperty','owl_DatatypeProperty');
        $properties = array();
        foreach ($property_types as $property_type) {
            foreach ($graph->allOfType($property_type) as $property) {
                $key = $property->shorten();
                if ($key) {
                    $properties[$key] = $property;
                }
            }
        }
        return $properties;
    }
    
    public function cardinality()
    {
        $types = $this->types();
        # Apart from owl_FunctionalProperty, these rules really correct,
        # but they provide a good set of defaults
        if (in_array( 'owl_FunctionalProperty', $types ) or
            in_array( 'owl_DatatypeProperty', $types ) or 
            in_array( 'owl_InverseFunctionalProperty', $types)) {
            return '1';
        } else {
            return 'N';
        }
    }

}

EasyRdf_TypeMapper::add('rdf_Property', 'EasyRdf_Owl_Property');
EasyRdf_TypeMapper::add('owl_Property', 'EasyRdf_Owl_Property');
EasyRdf_TypeMapper::add('owl_ObjectProperty', 'EasyRdf_Owl_Property');
EasyRdf_TypeMapper::add('owl_DatatypeProperty', 'EasyRdf_Owl_Property');
