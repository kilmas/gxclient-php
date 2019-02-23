<?php
/**
 * Created by PhpStorm.
 * User: kilmas
 * Date: 2019/2/2
 * Time: 20:19
 */

namespace Kilmas\GxcRpc\Gxc\Chain;

use Kilmas\GxcRpc\Gxc\SerializerValidation as v;

class ObjectId
{
    const DB_MAX_INSTANCE_ID = 2 ** 48 - 1;

    public $space;
    public $type;
    public $instance;

    public function __construct($space, $type, $instance)
    {
        $this->space = $space;
        $this->type = $type;
        $this->instance = $instance;
        $instance_string = $this->instance->toString();
        $ObjectId = `{$this->space}.{$this->type}.{$instance_string}`;
        if (!v::is_digits($instance_string)) {
            throwException(`Invalid object id ${ObjectId}`);
        }
    }

    static function fromString($value)
    {
        if (
            $value['space'] !== null &&
            $value['type'] !== null &&
            $value['instance'] !== null
        ) {
            return $value;
        }

        $params = v::require_match(
            "/^([0-9]+)\.([0-9]+)\.([0-9]+)$/",
            v::required($value, "ObjectId"),
            "ObjectId"
        );
        return new ObjectId(
            intval($params[1]),
            intval($params[2]),
            intval($params[3])
        );
    }

    static function fromLong($long)
    {
        $space = $long . shiftRight(56) . toInt();
        $type = $long . shiftRight(48) . toInt() & 0x00ff;
        $instance = $long->and(self::DB_MAX_INSTANCE_ID);
        return new ObjectId($space, $type, $instance);
    }

    static function fromByteBuffer($b)
    {
        return self::fromLong($b . readUint64());
    }

    function toLong()
    {
//        return Long.fromNumber($this->space).shiftLeft(56).or(
//    Long.fromNumber(this.type).shiftLeft(48).or(this.instance)
//        );
    }

    function appendByteBuffer($b)
    {
        return $b->writeUint64($this->toLong());
    }

    function toString()
    {
        return `{$this->space}.{$this->type}.{$this->instance()->toString()}`;
    }
}
