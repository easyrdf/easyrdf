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
}

EasyRdf_TypeMapper::add('owl_Class', EasyRdf_Owl_Class);
