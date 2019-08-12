<?php
/**
 * Created by PhpStorm.
 * User: kilmas
 * Date: 2019/2/13
 * Time: 10:18
 */

namespace GXChain\GXClient\Gxc;

class ByteBuffer
{

    public $pack = '';
    public $hex = '';


    const DEFAULT_CAPACITY = 1;
    const LITTLE_ENDIAN = 1;

    public $offset = 0;

    public function __construct($a, $b)
    {

    }


    public function readUint8($offset = null)
    {
        return unpack('C', $this->pack, $offset)[1];
    }

    public function readUint16($offset)
    {
        return unpack('v', $this->pack, $offset)[1];
    }

    public function readUint32($offset)
    {
        return unpack('V', $this->pack, $offset)[1];
    }

    public function writeUint8($value)
    {
        $this->offset += 1;
        $this->hex .= bin2hex(pack('C', $value));
        $this->pack .= pack('C', $value);
    }

    public function writeUint16($value)
    {
        $this->offset += 2;
        $this->hex .= bin2hex(pack('v', $value));
        $this->pack .= pack('v', $value);
    }

    public function writeUint32($value)
    {
        $this->offset += 4;
        $this->hex .= bin2hex(pack('V', $value));
        $this->pack .= pack('V', $value);
    }

    public function writeUint64($value)
    {   
        $this->offset += 8;
        if (is_string($value)) {
            // change uint64 string to bytes
            $a = $value;
            $r = "";
            $hex = "";
            while($a) {
                $r = sprintf('%02x%s', bcmod($a, 256), $r);
                $a = bcdiv($a, 256);
            }
            // change to little endian byte order
            for ($i=strlen($r)-1;$i > 0;$i = $i - 2) {
                $hex .= $r[$i-1].$r[$i];
            }
            $this->hex .= $hex;
            $this->pack .= hex2bin($hex);
        } else {
            $this->hex .= bin2hex(pack('P', $value));
            $this->pack .= pack('P', $value);
        }
    }

    public function writeInt64($value)
    {
        $this->offset += 8;
        $this->hex .= bin2hex(pack('q', $value));
        $this->pack .= pack('q', $value);
    }

    public function readVarint32($length, $offset)
    {
//        unpack('v', $this->pack, $offset)[1];
//        $this->offset += 8;
//        return unpack('V', $this->pack, $offset)[1];
    }

    public static function f_uint8($i)
    {
        return bin2hex(pack("C", $i));
    }

    public static function _uint8($i)
    {
        return pack("C", $i);
    }

    public function writeVarint32($i)
    {
        $t = '';
        $tmp = '';
        while (true) {
            $this->offset++;
            if ($i >> 7) {
                $t .= self::f_uint8(0x80 | ($i & 0x7f));
                $tmp .= self::_uint8(0x80 | ($i & 0x7f));
                $i = $i >> 7;
            } else {
                $t .= self::f_uint8($i);
                $tmp .= self::_uint8($i);
                break;
            }
        }
        $this->hex .= $t;
        $this->pack .= $tmp;
    }

    public function toBinary()
    {
        return $this->pack;
    }

    public function append($bin)
    {
        $this->offset += strlen($bin);
        $this->hex .= bin2hex($bin);
        $this->pack .= $bin;
    }
}