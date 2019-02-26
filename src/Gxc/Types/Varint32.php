<?php
/**
 * Created by PhpStorm.
 * User: kilmas
 * Date: 2019/2/25
 * Time: 16:55
 */

namespace GXChain\GXClient\Gxc\Types;


class Varint32
{
    const MIN_SIGNED_32 = -1 * 2 ** 31;
    const MAX_SIGNED_32 = 2 ** 31 - 1;

    function fromByteBuffer($b)
    {
        return $b->readVarint32();
    }

    function appendByteBuffer($b, $object)
    {
        v::require_range(
            self::MIN_SIGNED_32,
            self::MAX_SIGNED_32,
            $object,
            "uint32 {$object}"
        );
        $b->writeVarint32($object);
        return;
    }

    function fromObject($object)
    {
        v::require_range(
            self::MIN_SIGNED_32,
            self::MAX_SIGNED_32,
            $object,
            "uint32 {$object}"
        );
        return $object;
    }

    function toObject($object, $debug = [])
    {
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
}