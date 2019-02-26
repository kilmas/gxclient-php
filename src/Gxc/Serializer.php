<?php
/**
 * Created by PhpStorm.
 * User: kilmas
 * Date: 2019/1/28
 * Time: 23:17
 */


namespace GXChain\GXClient\Gxc;

class Serializer
{

    public $operation_name;
    public $types;
    public $printDebug;
    public $keys;

    public function __construct($operation_name, $types)
    {
        $this->operation_name = $operation_name;
        $this->types = $types;
        if ($this->types)
            $this->keys = array_keys($this->types);

        $this->printDebug = true;
    }

    function fromByteBuffer($b)
    {
        $field = '';
        $object = [];
        try {
            $iterable = $this->keys;
            for ($i = 0; $i < count($iterable); $i++) {
                $field = $iterable[$i];
                $type = $this->types[$field];
                try {
                    $object[$field] = $type->fromByteBuffer($b);
                } catch (\Exception $e) {
                    throwException($e->getMessage());
                }
            }

        } catch (\Exception $error) {
            throwException($this->operation_name . '.' . $field . $error->getMessage());
        }

        return $object;
    }

    function appendByteBuffer($b, $object)
    {
        $field = null;
        try {
            $iterable = $this->keys;
            for ($i = 0, $field = null; $i < count($iterable); $i++) {
                $field = $iterable[$i];
                $type = $this->types[$field];
                if (is_object($object)) {
                    $_field = $object->$field;
                } else {
                    $_field = $object[$field];
                }
                $type->appendByteBuffer($b, $_field);
            }

        } catch (\Exception $error) {
            throwException($this->operation_name . '.' . $field . $error->getMessage());
        }
        return;
    }

    function fromObject($serialized_object)
    {
        $result = [];
        $field = null;
        try {
            $iterable = $this->keys;
            for ($i = 0, $field = null; $i < count($iterable); $i++) {
                $field = $iterable[$i];
                $type = $this->types[$field];
                $value = isset($serialized_object[$field]) ? $serialized_object[$field] : null;
                $object = $type->fromObject($value);

                $result[$field] = $object;
            }

        } catch (\Exception $error) {
            throwException($this->operation_name . '.' . $field . $error->getMessage());
        }

        return $result;
    }

    /**
     * @param $serialized_object {boolean} [debug.use_default = false] - more template friendly
     * @param $debug {boolean} [debug.annotate = false] - add user-friendly information
     * @return mixed
     */
    function toObject($serialized_object = [], $debug = ['use_default' => false, 'annotate' => false])
    {
        $result = [];
        $field = null;
        try {
            if (!$this->types)
                return $result;

            $iterable = $this->keys;
            for ($i = 0; $i < count($iterable); $i++) {
                $field = $iterable[$i];
                $type = $this->types[$field];
                if ($serialized_object == null) {
                    $_field = null;
                } elseif (is_object($serialized_object)) {
                    $_field = $serialized_object->$field;
                } else {
                    $_field = $serialized_object[$field];
                }
                $object = $type->toObject($_field, $debug);
                $result[$field] = $object;
            }
        } catch (\Exception $error) {
            throwException($this->operation_name . '.' . $field . $error->getMessage());
        }

        return $result;
    }

    /** Sort by the first element in a operation */
    function compare($a, $b)
    {
        $first_key = $this->keys[0];
        $first_type = $this->types[$first_key];

        $valA = $a[$first_key];
        $valB = $b[$first_key];

        if (is_object($first_type) && method_exists($first_type, 'compare'))
            return $first_type->compare($valA, $valB);

        if (is_numeric($valA) && is_numeric($valB))
            return $valA - $valB;

        $strA = strval($valA);
        $strB = strval($valB);
        return $strA > $strB ? 1 : ($strA < $strB ? -1 : 0);
    }

    // <helper_functions>

    function fromHex($hex)
    {
        $b = ByteBuffer::fromHex($hex, ByteBuffer ::LITTLE_ENDIAN);
        return $this->fromByteBuffer($b);
    }

    function fromBuffer($buffer)
    {
        $b = ByteBuffer::fromBinary($buffer::toString("binary"), ByteBuffer::LITTLE_ENDIAN);
        return $this->fromByteBuffer($b);
    }

    function toHex($object)
    {
        // return this.toBuffer(object).toString("hex")
        $b = new ByteBuffer(ByteBuffer::DEFAULT_CAPACITY, ByteBuffer::LITTLE_ENDIAN);
        $this->appendByteBuffer($b, $object);
        return $b->hex;
    }

    function toByteBuffer($object)
    {
        $b = new ByteBuffer(ByteBuffer::DEFAULT_CAPACITY, ByteBuffer::LITTLE_ENDIAN);
        $this->appendByteBuffer($b, $object);
        return $b->pack;
    }

    function toBuffer($object)
    {
        $b = new ByteBuffer(ByteBuffer::DEFAULT_CAPACITY, ByteBuffer::LITTLE_ENDIAN);
        $this->appendByteBuffer($b, $object);
        return $b->pack;
    }

}