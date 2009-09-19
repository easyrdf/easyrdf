<?php

class EasyRdf_TypeMapper
{
    private static $_map = array();

    public static function get($type)
    {
        if ($type == null or $type == '') {
            return null;
        } else if (array_key_exists($type, self::$_map)) {
            return self::$_map[$type];
        } else {
            return null;
        }
    }

    public static function add($type, $class)
    {
        if ($type == null or $type == '') {
            # FIXME: throw exception
        }

        if ($class == null or $class == '') {
            # FIXME: throw exception
        }
        
        self::$_map[$type] = $class;
    }

}
