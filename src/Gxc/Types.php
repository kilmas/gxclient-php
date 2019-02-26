<?php
/**
 * Created by PhpStorm.
 * User: kilmas
 * Date: 2019/1/28
 * Time: 23:57
 */

namespace GXChain\GXClient\Gxc;

use GXChain\GXClient\Gxc\SerializerValidation as v;
use GXChain\GXClient\Gxc\Chain\ChainTypes;
use GXChain\GXClient\Gxc\Chain\ObjectId;
use GXChain\GXClient\Ecc\Utils;

class Types
{

    const MIN_SIGNED_32 = -1 * 2 ** 31;
    const MAX_SIGNED_32 = 2 ** 31 - 1;

    public static function a2o($arr)
    {
        $obj = new stdObject();
        foreach ($arr as $key => $val) {
            $obj->$key = $val;
        }
        return $obj;
    }

    public static function uint($int)
    {
        $object = [
            'fromByteBuffer' => function ($b) use ($int) {
                $readUint = 'writeUint' . $int;
                return $b->$readUint();
            },
            'appendByteBuffer' => function ($b, $object) use ($int) {
                v::require_range(0, 2 ** $int - 1, $object, "uint$int {$object}");
                $writeUint = 'writeUint' . $int;
                $b->$writeUint($object);
            },
            'fromObject' => function ($object) use ($int) {
                v::require_range(0, 2 ** $int - 1, $object, "uint$int {$object}");
                return $object;
            },
            'toObject' => function ($object, $debug = []) use ($int) {
                if (!empty($debug['use_default']) && $object === null) {
                    return 0;
                }
                v::require_range(0, 2 ** $int - 1, $object, "uint$int {$object}");
                $object = intval($object);
                return $int == 64 ? "$object" : $object;
            }
        ];
        return self::a2o($object);
    }

    public static function varint32()
    {
        $object = [
            'fromByteBuffer' => function ($b) {
                return $b->readVarint32();
            },
            'appendByteBuffer' => function ($b, $object) {
                v::require_range(
                    self::MIN_SIGNED_32,
                    self::MAX_SIGNED_32,
                    $object,
                    "uint32 {$object}"
                );
                $b->writeVarint32($object);
                return;
            },
            'fromObject' => function ($object) {
                v::require_range(
                    self::MIN_SIGNED_32,
                    self::MAX_SIGNED_32,
                    $object,
                    "uint32 {$object}"
                );
                return $object;
            },
            'toObject' => function ($object, $debug = []) {
                if (!empty($debug['use_default']) && $object == null) {
                    return 0;
                }
                v::require_range(
                    self::MIN_SIGNED_32,
                    self::MAX_SIGNED_32,
                    $object,
                    "uint32 {$object}"
                );
                return intval($object);
            }
        ];
        return self::a2o($object);
    }

    public static function int($int = 64)
    {
        $object = [
            'fromByteBuffer' => function ($b) use ($int) {
                return ('readInt' . $int)($b);
            },
            'appendByteBuffer' => function ($b, $object) use ($int) {
                v::require_range(0, 2 ** $int - 1, $object, "int$int {$object}");
                $writeint = 'writeInt' . $int;
                $b->$writeint($object);
            },
            'fromObject' => function ($object) use ($int) {
                v::require_range(0, 2 ** $int - 1, $object, "int$int {$object}");
                return $object;
            },
            'toObject' => function ($object, $debug = []) use ($int) {
                if (!empty($debug['use_default']) && $object === null) {
                    return 0;
                }
                v::require_range(0, 2 ** $int - 1, $object, "int$int {$object}");
                $object = intval($object);
                return "$object";
            }
        ];
        return self::a2o($object);
    }

    static function string()
    {
        $object = [
            'fromByteBuffer' => function ($b) {
                return $b;
            },
            'appendByteBuffer' => function ($b, $object) {
                v::required($object);
                $len = strlen($object);
                $b->writeVarint32($len);
                $b->append($object);
                return;
            },
            'fromObject' => function ($object) {
                v::required($object);
                return $object;
            },
            'toObject' => function ($object, $debug = []) {
                if (!empty($debug['use_default']) && $object === null) {
                    return "";
                }
                return $object;
            }
        ];
        return self::a2o($object);
    }

    static function bytes($size = null)
    {
        $object = [
            'fromByteBuffer' => function ($b) use ($size) {
                if ($size === null) {
                    $len = $b->readVarint32();
                    $b_copy = $b->copy($b->offset, $b->offset + $len) . $b->skip($len);
                    return $b_copy;
                    // return new Buffer($b_copy->toBinary(), "binary");
                } else {
                    $b_copy = $b->copy($b->offset, $b->offset + $size) . $b->skip($size);
                    return $b_copy;
                    // return new Buffer($b_copy->toBinary(), "binary");
                }
            },
            'appendByteBuffer' => function ($b, $object) use ($size) {
                v::required($object);
                if ($size === null) {
                    $b->writeVarint32(strlen($object) / 2);
                }
                if (preg_match("/^[a-fA-F0-9]+$/", $object))
                    $object = hex2bin($object);
                $b->append($object);
                return;
            },
            'fromObject' => function ($object) {
                v::required($object);
                if (preg_match("/^[a-fA-F0-9]+$/", $object))
                    return $object;
                return $object;
            },
            'toObject' => function ($object, $debug = []) use ($size) {
                if (!empty($debug['use_default']) && $object) {
                    return Array($size, "00");
                }
                v::required($object);
                return $object;
            }
        ];
        return self::a2o($object);
    }

//
    static function bool()
    {
        $object = [
            'fromByteBuffer' => function ($b) {
                return readUint8($b) === 1;
            },
            'appendByteBuffer' => function ($b, $object) {
                // supports boolean or integer
                $b->writeUint8(json_decode($object) ? 1 : 0);
                return;
            },
            'fromObject' => function ($object) {
                return json_decode($object) ? true : false;
            },
            'toObject' => function ($object, $debug = []) {
                if (!empty($debug['use_default']) && $object === null) {
                    return false;
                }
                return json_decode($object) ? true : false;
            }];
        return self::a2o($object);
    }

    static function void()
    {
        $object = [
            'fromByteBuffer' => function ($b) {
                throwException("(void) null type");
            },
            'appendByteBuffer' => function ($b, $object) {
                throwException("(void) null type");
            },
            'fromObject' => function ($object) {
                throwException("(void) null type");
            },
            'toObject' => function ($object, $debug = []) {
                if (!empty($debug['use_default']) && $object === null) {
                    return null;
                }
                throwException("(void) null type");
            }];
        return self::a2o($object);
    }

    static function array($st_operation)
    {
        $object = [
            'fromByteBuffer' => function ($b) use ($st_operation) {
                $size = $b->readVarint32();
                $result = [];
                for ($i = 0; 0 < $size ? $i < $size : $i > $size; 0 < $size ? $i++ : $i++) {
                    array_push($result, $st_operation->fromByteBuffer($b));
                }
                return self::sortOperation($result, $st_operation);
            },
            'appendByteBuffer' => function ($b, $object) use ($st_operation) {
                v::required($object);
                $object = self::sortOperation($object, $st_operation);
                $b->writeVarint32(count($object));
                for ($i = 0; $i < count($object); $i++) {
                    $o = $object[$i];
                    $st_operation->appendByteBuffer($b, $o);
                }
            },
            'fromObject' => function ($object) use ($st_operation) {
                v::required($object);
                $object = self::sortOperation($object, $st_operation);
                $result = [];
                for ($i = 0, $o = null; $i < count($object); $i++) {
                    $o = $object[$i];
                    array_push($result, ($st_operation:: fromObject($o)));
                }
                return $result;
            },
            'toObject' => function ($object, $debug = []) use ($st_operation) {
                if (!empty($debug['use_default']) && $object === null) {
                    $object = [$st_operation->toObject($object, $debug)];
                }
                v::required($object);
                $object = self::sortOperation($object, $st_operation);

                $result = [];
                for ($i = 0, $o = null; $i < count($object); $i++) {
                    $o = $object[$i];
                    array_push($result, $st_operation->toObject($o, $debug));
                }
                return $result;
            }
        ];
        return self::a2o($object);
    }

    static function time_point_sec()
    {
        $fromObject = function ($object) {
            v::required($object);

            if (is_numeric($object))
                return $object;
            if (!is_string($object))
                throwException("Unknown date type: " + $object);

            if (greg_match($object, "/T[0-2][0-9]:[0-5][0-9]:[0-5][0-9]$/"))
                $object = $object + "Z";

            return floor(strtotime($object));
        };
        $object = [
            'fromByteBuffer' => function ($b) {
                return $b->readUint32();
            },
            'appendByteBuffer' => function ($b, $object) use ($fromObject) {
                if (!is_numeric($object))
                    $object = $fromObject($object);
                $b->writeUint32($object);
                return;
            },
            'fromObject' => $fromObject,
            'toObject' => function ($object, $debug = []) {
                if (!empty($debug['use_default']) && empty($object))
                    return date('Y-m-d\TH:i:s', time());
                v::required($object);
                $int = intval($object);
                v::require_range(0, 0xFFFFFFFF, $int, "uint32 {$object}");
                return date('Y-m-d\TH:i:s', $int);
            }
        ];
        return self::a2o($object);
    }

    static function set($st_operation)
    {
        $validate = function ($array) use ($st_operation) {
            $dup_map = [];
            for ($i = 0, $o = null; $i < count($array); $i++) {
                $o = $array[$i];
                if (is_string($o) || is_numeric($o)) {
                    if (isset($dup_map[$o])) {
                        throwException("duplicate (set)");
                    }
                    $dup_map[$o] = true;
                }
            }
            return self::sortOperation($array, $st_operation);
        };
        $object = [
            'validate' => $validate,
            'fromByteBuffer' => function ($b) use ($st_operation, $validate) {
                $size = $b->readVarint32();
                $result = [];
                for ($i = 0; 0 < $size ? $i < $size : $i > $size; 0 < $size ? $i++ : $i++) {
                    array_push($result, $st_operation->fromByteBuffer($b));
                    return $validate($result);
                }
            },
            'appendByteBuffer' => function ($b, $object) use ($st_operation, $validate) {
                if (!$object) {
                    $object = [];
                }
                $b->writeVarint32(count($object));
                $iterable = $validate($object);

                for ($i = 0, $o = null; $i < count($iterable); $i++) {
                    $o = $iterable[$i];
                    $st_operation->appendByteBuffer($b, $o);

                }
                return;
            },
            'fromObject' => function ($object) use ($st_operation, $validate) {
                if (!$object) {
                    $object = [];
                }
                $result = [];

                for ($i = 0, $o = null; $i < count($object); $i++) {
                    $o = $object[$i];
                    array_push($result, $st_operation->fromObject($o));
                }
                return $validate($result);
            },
            'toObject' => function ($object, $debug = []) use ($st_operation, $validate) {
                if (!empty($debug['use_default']) && $object === null) {
                    $object = [$st_operation->toObject($object, $debug)];
                }
                if (!$object) {
                    $object = [];
                }
                $result = [];
                for ($i = 0, $o = null; $i < count($object); $i++) {
                    $o = $object[$i];
                    array_push($result, $st_operation->toObject($o, $debug));
                }
                return $validate($result);
            }
        ];
        return self::a2o($object);
    }

    //// global_parameters_update_operation current_fees
    static function fixed_array($count, $st_operation)
    {
        $object = [
            'fromByteBuffer' => function ($b) use ($count, $st_operation) {
                $results = [];
                for ($i = $j = 0, $ref = $count; $j < $ref; $i = $j += 1) {
                    array_push($results, $st_operation->fromByteBuffer($b));
                }
                return self::sortOperation($results, $st_operation);
            },
            'appendByteBuffer' => function ($b, $object) use ($count, $st_operation) {
                if ($count !== 0) {
                    v::required($object);
                    $object = self::sortOperation($object, $st_operation);
                }
                for ($i = $j = 0, $ref = $count; $j < $ref; $i = $j += 1) {
                    $st_operation->appendByteBuffer($b, $object[$i]);
                }
            },
            'fromObject' => function ($object) use ($count, $st_operation) {
                if ($count !== 0) {
                    v::required($object);
                }
                $results = [];
                for ($i = $j = 0, $ref = $count; $j < $ref; $i = $j += 1) {
                    array_push($results, $st_operation->fromObject($object[$i]));
                }
                return $results;
            },
            'toObject' => function ($object, $debug) use ($count, $st_operation) {
                if ($debug == null) {
                    $debug = [];
                }
                if (!empty($debug['use_default']) && $object === null) {
                    $results = [];
                    // void 0
                    for ($i = $j = 0, $ref = $count; $j < $ref; $i = $j += 1) {
                        array_push($results, $st_operation->toObject(null, $debug));
                    }
                    return $results;
                }
                if ($count !== 0) {
                    v::required($object);
                }
                $results1 = [];
                for ($i = $k = 0, $ref1 = $count; $k < $ref1; $i = $k += 1) {
                    array_push($results1, $st_operation->toObject($object[$i], $debug));
                }
                return $results1;
            }
        ];
        return self::a2o($object);
    }

    // * Supports instance numbers (11) or object types (1.2.11).  Object type
    // Validation is enforced when an object type is used. */
    static function id_type($reserved_spaces, $object_type)
    {
        v::required($reserved_spaces, "reserved_spaces");
        v::required($object_type, "object_type");
        $object = [
            'fromByteBuffer' => function ($b) {
                return $b->readVarint32();
            },
            'appendByteBuffer' => function ($b, $object) use ($reserved_spaces, $object_type) {
                v::required($object);
                if (isset($object['resolve'])) {
                    $object = $object['resolve'];
                }
                // convert 1.2.n into just n
                if (preg_match("/^[0-9]+\.[0-9]+\.[0-9]+$/", $object)) {
                    $object = v::get_instance($reserved_spaces, $object_type, $object);
                }
                $b->writeVarint32(v::to_number($object));
                return;
            },
            'fromObject' => function ($object) use ($reserved_spaces, $object_type) {
                v::required($object);
                if (isset($object['resolve'])) {
                    $object = $object['resolve'];
                }
                if (v::is_digits($object)) {
                    return v::to_number($object);
                }
                return v::get_instance($reserved_spaces, $object_type, $object);
            },
            'toObject' => function ($object, $debug = []) use ($reserved_spaces, $object_type) {
                $object_type_id = ChainTypes::$object_type[$object_type];
                if (!empty($debug['use_default']) && $object === null) {
                    return "{$reserved_spaces}.{$object_type_id}.0";
                }
                v::required($object);
                if (isset($object['resolve'])) {
                    $object = $object['resolve'];
                }
                if (preg_match("/^[0-9]+\.[0-9]+\.[0-9]+$/", $object)) {
                    $object = v::get_instance($reserved_spaces, $object_type, $object);
                }

                return "{$reserved_spaces}.{$object_type_id}." . $object;
            }
        ];
        return self::a2o($object);
    }

    static function protocol_id_type($name)
    {
        v::required($name, "name");
        return self::id_type(ChainTypes::$reserved_spaces['protocol_ids'], $name);
    }

    static function object_id_type()
    {
        $object = [
            'fromByteBuffer' => function ($b) {
                return ObjectId::fromByteBuffer($b);
            },
            'appendByteBuffer' => function ($b, $object) {
                v::required($object);
                if ($object['resolve'] !== null) {
                    $object = $object['resolve'];
                }
                $object = ObjectId::fromString($object);
                $object->appendByteBuffer($b);
                return;
            },
            'fromObject' => function ($object) {
                v::required($object);
                if ($object['resolve'] !== null) {
                    $object = $object['resolve'];
                }
                return ObjectId::fromString($object);
            },
            'toObject' => function ($object, $debug = []) {
                if (!empty($debug['use_default']) && $object === null) {
                    return "0.0.0";
                }
                v::required($object);
                if (isset($object['resolve'])) {
                    $object = $object['resolve'];
                }
                $object = ObjectId::fromString($object);
                return $object->toString();
            }];
        return self::a2o($object);
    }

    static function vote_id()
    {
        $TYPE = 0x000000FF;
        $ID = 0xFFFFFF00;
        $fromObject = function ($object) {
            v::required($object, "(type vote_id)");
            if (is_object($object)) {
                v::required($object['type'], "type");
                v::required($object['id'], "id");
                return $object;
            }
            v::require_test("/^[0-9]+:[0-9]+$/", $object, "vote_id format {$object}");
            list($type, $id) = explode(":", $object);
            v::require_range(0, 0xff, $type, "vote type {$object}");
            v::require_range(0, 0xffffff, $id, "vote id {$object}");
            return ['type' => $type, 'id' => $id];
        };
        $object = [
            'fromByteBuffer' => function ($b) use ($TYPE, $ID) {
                $value = $b->readUint32();
                return $object = [
                    'type' => $value & $TYPE,
                    'id' => $value & $ID
                ];
            },
            'appendByteBuffer' => function ($b, $object) use ($fromObject) {
                v::required($object);
                if ($object === "string")
                    $object = $fromObject($object);

                $value = $object['id'] << 8 | $object['type'];
                $b->writeUint32($value);
                return;
            },
            'fromObject' => $fromObject,
            'toObject' => function ($object, $debug = []) use ($fromObject) {
                if (!empty($debug['use_default']) && $object === null) {
                    return "0:0";
                }
                v::required($object);
                if (is_string($object))
                    $object = $fromObject($object);

                return $object['type'] . ":" . $object['id'];
            },
            'compare' => function ($a, $b) use ($fromObject) {
                //typeof a !== "object"
                if (!is_array($a)) $a = $fromObject($a);
                if (!is_array($b)) $b = $fromObject($b);
                return intval($a['id']) - intval($b['id']);
            }
        ];
        return self::a2o($object);
    }

    static function optional($st_operation)
    {
        v::required($st_operation, "st_operation");
        $object = [
            'fromByteBuffer' => function ($b) use ($st_operation) {
                if (!($b->readUint8() === 1)) {
                    return null;
                }
                $st_operation->fromByteBuffer($b);
            },
            'appendByteBuffer' => function ($b, $object) use ($st_operation) {

                if (!empty($object)) {
                    $b->writeUint8(1);
                    $st_operation->appendByteBuffer($b, $object);
                } else {
                    $b->writeUint8(0);
                }
                return;
            },
            'fromObject' => function ($object) use ($st_operation) {
                if ($object === null) {
                    return null;
                }
                return $st_operation->fromObject($object);
            },
            'toObject' => function ($object, $debug = []) use ($st_operation) {
                if (empty($debug['use_default']) && $object === null) {
                    $result_object = null;
                } else {
                    $result_object = $st_operation->toObject($object, $debug);
                }

                if (!empty($debug['annotate'])) {
                    if (is_array($result_object)) {
                        $result_object['__optional'] = "parent is optional";
                    } else {
                        $result_object = ['__optional' => $result_object];
                    }
                }
                return $result_object;
            }
        ];
        return self::a2o($object);
    }

    public static function static_variant($_st_operations = [])
    {
        $object = [
            'nosort' => true,
            'st_operations' => $_st_operations,
            'fromByteBuffer' => function ($b) use ($_st_operations) {
                $type_id = $b->readVarint32();
                $st_operation = Operations::serializer($_st_operations[$type_id]);
                v::required($st_operation, "operation {$type_id}");
                return [
                    $type_id,
                    $st_operation->fromByteBuffer($b)
                ];
            },
            'appendByteBuffer' => function ($b, $object) use ($_st_operations) {
                v::required($object);
                $type_id = $object[0];
                $st_operation = Operations::serializer($_st_operations[$type_id]);
                v::required($st_operation, "operation {$type_id}");
                $b->writeVarint32($type_id);
                $st_operation->appendByteBuffer($b, $object[1]);
                return;
            },
            'fromObject' => function ($object) use ($_st_operations) {
                v::required($object);
                $type_id = $object[0];
                $st_operation = Operations::serializer($_st_operations[$type_id]);
                v::required($st_operation, "operation {$type_id}");
                return [
                    $type_id,
                    $st_operation->fromObject($object[1])
                ];
            },
            'toObject' => function ($object, $debug = []) use ($_st_operations) {
                if (!empty($debug['use_default']) && $object === null) {
                    return [0, $_st_operations[0]->toObject(null, $debug)];
                }
                v::required($object);
                $type_id = $object[0];
                $st_operation = Operations::serializer($_st_operations[$type_id]);
                v::required($st_operation, "operation {$type_id}");
                return [
                    $type_id,
                    $st_operation->toObject($object[1], $debug)
                ];
            }
        ];
        return self::a2o($object);
    }

    static function map($key_st_operation, $value_st_operation)
    {
        $validate = function ($array) use ($key_st_operation, $value_st_operation) {
            if (!is_array($array)) {
                throwException("expecting array");
            }
            $dup_map = [];
            for ($i = 0; $i < count($array); $i++) {
                $o = $array[$i];
                if (!(count($o) === 2)) {
                    throwException("expecting two elements");
                }
                $ref = $o[0];
                if (in_array($ref, ["number", "string"])) {
                    if ($dup_map[$o[0]] !== null) {
                        throwException("duplicate (map)");
                    }
                    $dup_map[$o[0]] = true;
                }
            }
            return self::sortOperation($array, $key_st_operation);
        };
        $object = [
            'validate' => $validate,

            'fromByteBuffer' => function ($b) use ($key_st_operation, $value_st_operation, $validate) {
                $result = [];
                $end = readVarint32($b);
                for ($i = 0; 0 < $end ? $i < $end : $i > $end; 0 < $end ? $i++ : $i++) {
                    array_push($result, [
                        $key_st_operation->fromByteBuffer($b),
                        $value_st_operation->fromByteBuffer($b)
                    ]);
                }
                return $validate($result);
            },

            'appendByteBuffer' => function ($b, $object) use ($key_st_operation, $value_st_operation, $validate) {
                $validate($object);
                $b->writeVarint32(count($object));
                for ($i = 0; $i < count($object); $i++) {
                    $o = $object[$i];
                    $key_st_operation->appendByteBuffer($b, $o[0]);
                    $value_st_operation->appendByteBuffer($b, $o[1]);
                }
                return;
            },
            'fromObject' => function ($object) use ($key_st_operation, $value_st_operation, $validate) {
                v::required($object);
                $result = [];
                for ($i = 0; $i < count($object); $i++) {
                    $o = $object[$i];
                    array_push($result, [
                        $key_st_operation->fromObject($o[0]),
                        $value_st_operation->fromObject($o[1])
                    ]);
                }
                return $validate($result);
            },
            'toObject' => function ($object, $debug = []) use ($key_st_operation, $value_st_operation, $validate) {
                if (!empty($debug['use_default']) && $object === null) {
                    return [
                        [
                            $key_st_operation->toObject(null, $debug),
                            $value_st_operation->toObject(null, $debug)
                        ]
                    ];
                }
                v::required($object);
                $object = $validate($object);
                $result = [];
                for ($i = 0; $i < count($object); $i++) {
                    $o = $object[$i];
                    array_push($result, [
                        $key_st_operation->toObject($o[0], $debug),
                        $value_st_operation->toObject($o[1], $debug)
                    ]);
                }
                return $result;
            }
        ];
        return self::a2o($object);
    }

    static function public_key()
    {
        $toPublic = function ($object) {
            if (isset($object['resolve'])) {
                $object = $object['resolve'];
            }
            return $object == null ? $object :
                isset($object['Q']) ? $object : PublicKey::fromStringOrThrow($object);
        };
        $object = [
            'toPublic' => $toPublic,
            'fromByteBuffer' => function ($b) {
                return fp::public_key($b);
            },
            'appendByteBuffer' => function ($b, $object) use ($toPublic) {
                v::required($object);
                $b->append(hex2bin(Utils::checkDecode(substr($object, 3), null)));
                // fp::public_key($b, $toPublic($object));
                return;
            },
            'fromObject' => function ($object) use ($toPublic) {
                v::required($object);
                if (isset($object['Q'])) {
                    return $object;
                }
                return $object;
                // return $toPublic($object);
            },
            'toObject' => function ($object, $debug = []) {
                v::required($object);
                return $object;
                // return $object . toString();
            },
            'compare' => function ($a, $b) {
                return strCmp($a, $b);
                // return strCmp($a . toAddressString(), $b . toAddressString());
            }
        ];
        return self::a2o($object);
    }

    static function address()
    {
        $_to_address = function ($object) {
            v::required($object);
            if ($object['addy']) {
                return $object;
            }
            return Address::fromString($object);
        };
        $object = [
            '_to_address' => $_to_address,
            'romByteBuffer' => function ($b) {
                return new Address(fp:: ripemd160($b));
            },
            'appendByteBuffer' => function ($b, $object) use ($_to_address) {
                fp::ripemd160($b, $_to_address($object) . toBuffer());
                return;
            },
            'fromObject' => function ($object) use ($_to_address) {
                return $_to_address($object);
            },
            'toObject' => function ($object, $debug = []) use ($_to_address) {
                return $_to_address($object);
            },
            'compare' => function ($a, $b) {
                return strCmp($a, $b);
            }];
        return self::a2o($object);
    }

//
    static function name_type()
    {
        $object = [
            'fromByteBuffer' => function ($b) {
                return name_to_string($b->readUint64());
            },
            'appendByteBuffer' => function ($b, $object) {
                $obj = self::string_to_name($object);
                $b->writeUint64(floatval(v::unsigned($obj)));
                return;
            },
            'fromObject' => function ($object) {
                v::required($object);
                return $object;
                // return new Buffer($object);
            },
            'toObject' => function ($object, $debug = []) {
                if (!empty($debug['use_default']) && $object === null) {
                    return "";
                }
                return $object;
            }];
        return self::a2o($object);
    }

    static function checksum($int)
    {
        return self::bytes($int / 8);
    }
//
//Types.checksum160 = Types.bytes(20);
//Types.checksum256 = Types.bytes(32);
//Types.checksum512 = Types.bytes(64);
//
//Types.block_id_type = Types.bytes(20);
    static function block_id_type()
    {
        return self::bytes(20);
    }


    static private function char_to_symbol($c)
    {
        $result = 0;
        if ($c >= ord("a") && $c <= ord("z"))
            $result = ($c - ord("a")) + 6;
        if ($c >= ord("1") && $c <= ord("5"))
            $result = ($c - ord("1")) + 1;
        return $result;
    }

    public static function string_to_name($str)
    {
        $name = v::to_long(0);
        $i = 0;
        $len = strlen($str);
        for (; $i < $len && $i < 12; ++$i) {
            // NOTE: char_to_symbol() returns char type, and without this explicit
            // expansion to uint64 type, the compilation fails at the point of usage
            // of string_to_name(), where the usage requires constant (compile time) expression.
//            $tmp = new \BN\BN(v::to_long(self::char_to_symbol(ord($str[$i]))), '10');
//            $symbol = $tmp->_and(0x1f) ;//.and(0x1f);
//            $name = $name | ($symbol * 2 ** (64 - 5 * ($i + 1)));
            $symbol = v::to_long(self::char_to_symbol(ord($str[$i]))) & 0x1f;//.and(0x1f);
            $name = (new \BN\BN($name))->_or(new \BN\BN(number_format($symbol * 2 ** (64 - 5 * ($i + 1)), 0, '', '')))->toString();
        }

        // The for-loop encoded up to 60 high bits into uint64 'name' variable,
        // if (strlen(str) > 12) then encode str[12] into the low (remaining)
        // 4 bits of 'name'
        if ($i == 12 && $len > 12) {
            $char = $str[12];
            $symbol = v::to_long(self::char_to_symbol(ord($char)));
            $name = $name | ($symbol & 0x0F);// .or($symbol.and(0x0F));
            $name = (new \BN\BN($name))->_or(new \BN\BN($symbol & 0x0F))->toString();
        }
        return $name;
    }

//
    public static function name_to_string($name)
    {
        $charmap = ".12345abcdefghijklmnopqrstuvwxyz";
        $str = [];
        $tmp = $name;
        for ($i = 0; $i <= 12; ++$i) {
            $index = $tmp; //.and($i == 0 ? 0x0f : 0x1f).toInt();
            $c = $charmap[$index];
            array_push($str, $c);
            $tmp = $tmp . shiftRightUnsigned($i == 0 ? 4 : 5);
        }
        // return $str.reverse().join("").replace(/\.+$/g, "");
        return $str = str_replace("/\.+$/g", "", implode("", array_reverse($str)));
    }

    /**
     * convert 1.2.* to object_id_type, which will be used in smart contract
     * @param account_id_str
     * @return string
     */
    public static function _object_id_type($account_id_str)
    {
        $account_id = [];
        foreach (explode(".", $account_id_str) as $key => $v) {
            $account_id[$key] = v::to_long($v);
        }
        return $account_id[0] . shiftLeft(56);
    }

    public static function strCmp($a, $b)
    {
        return $a > $b ? 1 : $a < $b ? -1 : 0;
    }

    public static function firstEl($el)
    {
        return is_array($el) ? $el[0] : $el;
    }

    public static function sortOperation($array, $st_operation)
    {
        $st_operation = Operations::serializer($st_operation);
        isset($st_operation->nosort) ?
            $array :
            (method_exists($st_operation, 'compare') ? usort($array, function ($a, $b) use ($st_operation) {
                return $st_operation->compare(firstEl($a), firstEl($b));
            }) :
                usort($array, function ($a, $b) {
                    $a = firstEl($a);
                    $b = firstEl($b);
                    return (is_numeric($a) && is_numeric($b)) ? $a - $b :
                        // A binary string compare does not work. Performanance is very good so HEX is used..  localeCompare is another option.
                        self::strCmp($a, $b);

                }));
        return $array;
    }

}

function firstEl($el)
{
    return (is_array($el) && isset($el[0])) ? $el[0] : $el;
}