<?php
/**
 * Created by PhpStorm.
 * User: kilmas
 * Date: 2019/2/1
 * Time: 15:03
 */

namespace Kilmas\GxcRpc;

use Kilmas\GxcRpc\Ecc\Signature;

use Kilmas\GxcRpc\Gxc\Operations as ops;
use Kilmas\GxcRpc\Gxc\Types;

class TransactionHelper
{

    private static $unique_nonce_entropy = 0;

    public static function unique_nonce_uint64()
    {

        if (self::$unique_nonce_entropy === null) {
            $entropy = self::$unique_nonce_entropy = rand(0, 255);
        } else {
            $entropy = self::$unique_nonce_entropy = ++self::$unique_nonce_entropy % 256;
        }

        $long = time() * 1000;
        $long = $long . $entropy;
        return $long;
    }

    static function to_json($tr, $broadcast = false)
    {
        $tr_object = ops::serializer('signed_transaction')->toObject($tr);
        if ($broadcast) {
            $net = Apis::instance()->network_api();
            return $net->exec("broadcast_transaction", [$tr_object]);
        } else {
            return $tr_object;
        }
    }

    static function signed_tr_json($tr, $private_keys)
    {
        $tr_buffer = ops::serializer('transaction')->toBuffer($tr);
        $tr = ops::serializer('transaction')->toObject($tr);

        $result = [];
        for ($i = 0; 0 < count($private_keys) ? $i < count($private_keys) : $i > count($private_keys); 0 < count($private_keys) ? $i++ : $i++) {
            $private_key = $private_keys[$i];
            array_push($result, Signature::signBuffer($tr_buffer, $private_key));
        }
        $tr['signatures'] = $result;
        return $tr;
    }

    static function expire_in_min($min)
    {
        return round(now()) + ($min * 60);
    }

    static function seconds_from_now($timeout_sec)
    {
        return round(now()) + $timeout_sec;
    }

    static function template($serializer_operation_type_name, $debug = ['use_default' => true, 'annotate' => true])
    {
        if (!ops::serializer($serializer_operation_type_name)) {
            throwException(`unknown serializer_operation_type {$serializer_operation_type_name}`);
        }
        $so = ops::serializer($serializer_operation_type_name);
        return $so->toObject(null, $debug);
    }

    static function new_operation($serializer_operation_type_name)
    {
        if (!ops::serializer($serializer_operation_type_name)) {
            throwException(`unknown serializer_operation_type {$serializer_operation_type_name}`);
        }
        $so = ops::serializer($serializer_operation_type_name);
        $object = $so->toObject(null, ['use_default' => true, 'annotate' => true]);
        return $so->fromObject($object);
    }

    static function instance($ObjectId)
    {
        return substr($ObjectId, strlen("0.0."));
    }
}

