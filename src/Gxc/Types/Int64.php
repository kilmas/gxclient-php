<?php
/**
 * Created by PhpStorm.
 * User: kilmas
 * Date: 2019/2/25
 * Time: 11:01
 */

namespace GXChain\GXClient\Gxc\Types;

class Int64
{
    protected $int;

    public function __construct($int = 64)
    {
        $this->int = $int;
    }


    function fromByteBuffer($b)
    {
        return ('readInt' . $this->int)($b);
    }

    function appendByteBuffer($b, $object)
    {
        v::require_range(0, 2 ** $this->int - 1, $object, "int$this->int {$object}");
        $writeint = 'writeInt' . $this->int;
        $b->$writeint($object);
    }

    function fromObject($object)
    {
        v::require_range(0, 2 ** $this->int - 1, $object, "int$this->int {$object}");
        return $object;
    }

    function toObject($object, $debug = [])
    {
        if (!empty($debug['use_default']) && $object === null) {
            return 0;
        }
        v::require_range(0, 2 ** $this->int - 1, $object, "int$this->int {$object}");
        $object = intval($object);
        return "$object";
    }
}