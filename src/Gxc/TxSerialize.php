<?php

namespace GXChain\GXClient\Gxc;

use GXChain\GXClient\Gxc\Operations as ops;
use GXChain\GXClient\Gxc\Types as types;

class TxSerialize
{

    private static function isArrayType($type)
    {
        return strpos($type, "[]") !== false;
    }

    static function serializeCallData($action, $params, $abi)
    {
        $abi = json_decode(json_encode($abi), true);
        $structs = $abi['structs'];
        $struct = [];
        foreach ($structs as $value) {
            if ($value['name'] === $action) {
                $struct = $value;
                break;
            }
        }
        $b = new ByteBuffer(ByteBuffer::DEFAULT_CAPACITY, ByteBuffer::LITTLE_ENDIAN);
        foreach ($struct['fields'] as $f) {
            $_value = $params[$f['name']];
            $isArrayFlag = false;
            if (self::isArrayType($f['type'])) {
                $isArrayFlag = true;
                $f['type'] = explode("[", $f['type'])[0];
            }
            $type = null;
            if (preg_match("/([A-Za-z_]+)(\d+)/", $f['type'], $match)) {
                $fun = $match[1];
            } else {
                $fun = $f['type'];
            }

            if (!method_exists(types::class, $fun)) {
                $t = null;
                foreach ($abi['types'] as $tmp) {
                    if ($tmp['new_type_name'] === $f['type']) {
                        $t = $tmp;
                        break;
                    }
                }
                $t = null;
                foreach ($abi['types'] as $value) {
                    if ($value['new_type_name'] === $value['type']) {
                        $t = $value;
                        break;
                    }
                }
                if ($t) {
                    $fun = $t['type'];
                }
                if (!method_exists(types::class, $fun)) {
                    $type = ops::serializer($fun);
                }
            } else {
                if (isset($match[2])) {
                    $type = types::$fun($match[2]);
                } else {
                    $type = types::$fun();
                }
            }

            if ($type) {
                if ($isArrayFlag) {
                    $type = types::set($type);
                }
                $type->appendByteBuffer($b, $type->fromObject($_value));
            }
        }
        return $b->hex;
    }

    static function serializeTransaction($transaction)
    {
        $ops = ops::serializer('transaction');
        return $ops->toBuffer($ops->fromObject($transaction));
    }
}