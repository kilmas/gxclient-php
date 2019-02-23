<?php
/**
 * Created by PhpStorm.
 * User: kilmas
 * Date: 2019/1/30
 * Time: 23:45
 */


/**
 * Most validations are skipped and the$value returned unchanged when an empty string, null, or undefined is encountered (except "required").
 * Validations support a string format for dealing with large numbers.
 */

namespace Kilmas\GxcRpc\Gxc;

use Kilmas\GxcRpc\Gxc\Chain\ChainTypes;

class SerializerValidation
{
    const MAX_SAFE_INT = 9007199254740991;
    const MIN_SAFE_INT = -9007199254740991;

    static function is_empty($value)
    {
        return $value === null;
    }

    static function required($value, $field_name = "")
    {
        if (self::is_empty($value)) {
            throwException("value required {$field_name} {$value}");
        }
        return $value;
    }

    static function require_long($value, $field_name = "")
    {
        if (!is_long($value)) {
            throwException("Long$value required {$field_name} {$value}");
        }
        return $value;
    }

    static function string($value)
    {
        if (self::is_empty($value)) {
            return $value;
        }
        if (!is_string($value)) {
            throwException("string required: {$value}");
        }
        return $value;
    }

    static function number($value)
    {
        if (self::is_empty($value)) {
            return $value;
        }
        if (!is_numeric($value)) {
            throwException("number required: {$value}");
        }
        return $value;
    }

    static function whole_number($value, $field_name = "")
    {
        if (self::is_empty($value)) {
            return $value;
        }
        if (preg_match("/\./", $value)) {
            throwException("whole number required {$field_name} {$value}");
        }
        return $value;
    }

    static function unsigned($value, $field_name = "")
    {
        if (self::is_empty($value)) {
            return $value;
        }
        if (preg_match("/-/", $value)) {
            throwException("unsigned required {$field_name} {$value}");
        }
        return $value;
    }

    static function is_digits($value)
    {
        if (is_numeric($value)) {
            return true;
        }
        return preg_match("/^[0-9]+$/", $value);
    }

    static function to_number($value, $field_name = "")
    {
        if (self::is_empty($value)) {
            return $value;
        }
        self::no_overflow53($value, $field_name);
        if (is_numeric($value)) {
            return $value;
        } else {
            return intval($value);
        }
    }

    static function to_long($value, $field_name = "", $unsigned = false)
    {
        if (self::is_empty($value)) {
            return $value;
        }
        if (is_long($value)) {
            return $value;
        }

        self::no_overflow64($value, $field_name, $unsigned);
        if (is_numeric($value)) {
            $value = "" . $value;
        }
        return $value;
    }

    static function to_string($value, $field_name = "")
    {
        if (self::is_empty($value)) {
            return $value;
        }
        if (is_string($value)) {
            return $value;
        }
        if (is_numeric($value)) {
            self::no_overflow53($value, $field_name);
            return "" . $value;
        }
        if (is_long($value)) {
            return "$value";
        }
    }

    static function require_test($regex, $value, $field_name = "")
    {
        if (self::is_empty($value)) {
            return $value;
        }
        if (!preg_match($regex, $value)) {
            throwException("unmatched ${regex} {$field_name} {$value}");
        }
        return $value;
    }

    static function require_match($regex, $value, $field_name = "")
    {
        if (self::is_empty($value)) {
            return $value;
        }
        preg_match_all($regex, $value, $match);
        if ($match === null) {
            throwException("unmatched {$regex} {$field_name} {$value}");
        }
        return $match;
    }

    static function require_object_id($value, $field_name)
    {
        return require_match(
            "/^([0-9]+)\.([0-9]+)\.([0-9]+)$/",
            $value,
            $field_name
        );
    }

    // Does not support over 53 bits
    static function require_range($min, $max, $value, $field_name = "")
    {
        if (self::is_empty($value)) {
            return $value;
        }
        $number = self::to_number($value);
        if ($number < $min || $number > $max) {
            throwException("out of range {$value} {$field_name} {$value}");
        }
        return $value;
    }

    static function require_object_type($reserved_spaces = 1, $type, $value, $field_name = "")
    {
        if (self::is_empty($value)) {
            return $value;
        }
        $object_type = ChainTypes::$object_type[$type];
        if (!$object_type) {
            throwException("Unknown object$type ${type} {$field_name} {$value}");
        }
        if (!preg_match("/{$reserved_spaces}\.{$object_type}\.[0-9]+$/", $value)) {
            throwException("Expecting ${type} in format " . "{$reserved_spaces}.{$object_type}.[0-9]+ " . "instead of {$value} {$field_name} {$value}");
        }
        return $value;
    }

    static function get_instance($reserve_spaces, $type, $value, $field_name = null)
    {
        if (self::is_empty($value)) {
            return $value;
        }
        self::require_object_type($reserve_spaces, $type, $value, $field_name);
        return self::to_number(explode(".", $value)[2]);
    }

    static function require_relative_type($type, $value, $field_name)
    {
        self::require_object_type(0, $type, $value, $field_name);
        return $value;
    }

    static function get_relative_instance($type, $value, $field_name)
    {
        if (self::is_empty($value)) {
            return $value;
        }
        self::require_object_type(0, $type, $value, $field_name);
        return self::to_number(explode(".", $value)[2]);
    }

    static function require_protocol_type($type, $value, $field_name)
    {
        self::require_object_type(1, $type, $value, $field_name);
        return $value;
    }

    static function get_protocol_instance($type, $value, $field_name)
    {
        if (self::is_empty($value)) {
            return $value;
        }
        self::require_object_type(1, $type, $value, $field_name);
        return self::to_number(explode(".", $value)[2]);
    }

    static function get_protocol_type($value, $field_name)
    {
        if (self::is_empty($value)) {
            return $value;
        }
        self::require_object_id($value, $field_name);
        $values = explode(".", $value);
        return self::to_number($values[1]);
    }

    static function get_protocol_type_name($value, $field_name)
    {
        if (self::is_empty($value)) {
            return $value;
        }
        $type_id = self::get_protocol_type($value, $field_name);
        return array_keys(ChainTypes::$object_type)[$type_id];
    }

    static function require_implementation_type($type, $value, $field_name)
    {
        self::require_object_type(2, $type, $value, $field_name);
        return $value;
    }

    static function get_implementation_instanc($type, $value, $field_name)
    {
        if (self::is_empty($value)) {
            return $value;
        }
        self::require_object_type(2, $type, $value, $field_name);
        return self::to_number(explode(".", $value)[2]);
    }

    // signed / unsigned decimal
    static function no_overflow53($value, $field_name = "")
    {
        if (is_numeric($value)) {
            if ($value > self::MAX_SAFE_INT || $value < self::MIN_SAFE_INT) {
                throwException("overflow {$field_name} {$value}");
            }
            return;
        }
        if (is_string($value)) {
            $int = intval($value);
            if ($int > self::MAX_SAFE_INT || $int < self::MIN_SAFE_INT) {
                throwException("overflow {$field_name} {$value}");
            }
            return;
        }
        if (is_long($value)) {
            self::no_overflow53(intval($value), $field_name);
            return;
        }
        throwException("unsupported type {$field_name}: ({$value}) ");
    }

    // signed / unsigned whole numbers only
    static function no_overflow64($value, $field_name = "")
    {
        // https://github.com/dcodeIO/Long.js/issues/20
        if (is_long($value)) {
            return;
        }

        // BigInteger#isBigInteger https://github.com/cryptocoinjs/bigi/issues/20
        if (isset($value['t']) && isset($value['s'])) {
            self::no_overflow64(strval($value), $field_name);
            return;
        }

        if (is_string($value)) {
            // remove leading zeros, will cause a false positive
            $value = str_replace("/^0+/", "", $value);
            // remove trailing zeros
            while (preg_match("/0$/", $value)) {
                $value = substr($value, 0, count($value) - 1);
            }
            if (preg_match("/\.$/", $value)) {
                // remove trailing dot
                $value = substr($value, 0, count($value) - 1);
            }
            if ($value === "") {
                $value = "0";
            }
            $long_string = strval($value);
            if ($long_string !== trim($value)) {
                throwException("overflow {$field_name} {$value}");
            }
            return;
        }
        if (is_numeric($value)) {
            if ($value > self::MAX_SAFE_INT || $value < self::MIN_SAFE_INT) {
                throwException("overflow {$field_name} {$value}");
            }
            return;
        }

        throwException("unsupported type {$field_name}: ({$value}) {$value}");
    }
}
