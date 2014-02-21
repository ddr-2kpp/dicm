<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ddr;

/**
 * Description of limits
 *
 * @author ddr
 */

abstract class base_constants {
    // caching the constants, 'cuz reflection is expensive
    private static $cache = null;

    // the method 2 get the cache
    private static function constants($force = false) {
        if ((self::$cache === null) || ($force)) {
            $ref = new \ReflectionClass(get_called_class());
            self::$cache = $ref->getStaticProperties();
        }
        return self::$cache;
    }


    
    public static function is_valid_name($section, $const) {
        $consts = self::constants();

        return array_key_exists($const[$section], $consts);
    }

    
    public static function valid_value($section, $value) {
        $consts = self::constants();
        
        return in_array($value, $consts[$section]);
    }

    
    public static function get_name($section, $value) {
        $consts = self::constants();
        if (!array_key_exists($section, $consts)) {
            throw new \LogicException("constants section '$section' not found");
        }
        foreach ($consts[$section] as $name => $val) {
            if ($value == $val) {
                return $name;
            }
        }
        throw new \LogicException("constant value '$value' not found in section '$section'");
    }
}
