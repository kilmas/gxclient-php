<?php
/**
 * Created by PhpStorm.
 * User: kilmas
 * Date: 2019/2/25
 * Time: 11:01
 */

namespace Kilmas\GxcRpc\Gxc\Types;

class Uint
{
    protected $int;

    public function __construct($int = 8)
    {
        $this->int = $int;
    }


    function fromByteBuffer($b)
    {
        $readUint = 'writeUint' . $this->int;
        return $b->$readUint();
    }

    function appendByteBuffer($b, $object)
    {
        v::require_range(0, 2 ** $this->int - 1, $object, "uint$this->int {$object}");
        $writeUint = 'writeUint' . $this->int;
        $b->$writeUint($object);
    }

    function fromObject($object)
    {
        v::require_range(0, 2 ** $this->int - 1, $object, "uint{$this->int} {$object}");
        return $object;
    }

    function toObject($object, $debug = [])
    {
        if (!empty($debug['use_default']) && $object === null) {
            return 0;
        }
        v::require_range(0, 2 ** $this->int - 1, $object, "uint$this->int {$object}");
        $object = intval($object);
        return $this->int == 64 ? "$object" : $object;
    }
}