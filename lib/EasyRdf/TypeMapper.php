<?php

class EasyRdf_TypeMapper
{
    private static $map = array();

    public static function get($type)
    {
        if (array_key_exists( $type, self::$map )) {
            return self::$map[$type];
        } else {
            return null;
        }
    }

    public static function add($type, $class)
    {
        self::$map[$type] = $class;
    }

}
