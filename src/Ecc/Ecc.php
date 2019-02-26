<?php
/**
 * Created by PhpStorm.
 * User: kilmas
 * Date: 2019/1/26
 * Time: 13:45
 */
namespace GXChain\GXClient\Ecc;
use Elliptic\EC;

class Ecc
{
    /**
     * Wif private key To private hex
     */
    public static function wifPrivateToPrivateHex(string $privateKey)
    {
        return substr(Utils::checkDecode($privateKey), 2);
    }
    /**
     * Private hex To  wif private key
     * @return mixed
     * @throws
     */
    public static function privateHexToWifPrivate(string $privateHex)
    {
        return Utils::checkEncode(hex2bin('80' . $privateHex));
    }
    /**
     * privateKey to Public
     * @param $privateKey string
     * @param $prefix
     * @return mixed
     * @throws
     */
    public static function privateToPublic(string $privateKey, string $prefix = 'EOS')
    {
        // wif private
        $privateHex = self::wifPrivateToPrivateHex($privateKey);
        $ec = new EC('secp256k1');
        $key = $ec->keyFromPrivate($privateHex);
        return $prefix . Utils::checkEncode(hex2bin($key->getPublic(true, 'hex')), null);
    }
    /**
     * 随机生成私钥
     * Random private key
     */
    public static function randomKey($wif = true)
    {
        $ec = new EC('secp256k1');
        $kp = $ec->genKeyPair();
        if ($wif) {
            return self::privateHexToWifPrivate($kp->getPrivate('hex'));
        }
        return $kp->getPrivate('hex');
    }

    /**
     * 根据种子生产私钥
     * @param $seed
     * @param $wif
     * @return boolean
     */
    public static function seedPrivate(string $seed, $wif = true)
    {
        $secret = hash('sha256', hash('sha512', $seed . ' 0', true));
        if ($wif) {
            return self::privateHexToWifPrivate($secret);
        }
        return $secret;
    }
    
    /**
     * 随机生成brainKey
     */
    public static function suggestBrainKey()
    {   
        $str = file_get_contents(dirname(__FILE__) . '/dictionary.txt');
        $strEncoding = mb_convert_encoding($str, 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');

        $randomBytes = random_bytes(32);
        $randomBuffer = array();
        for($i = 0; $i < strlen($randomBytes); $i++){
            $randomBuffer[] = ord($randomBytes[$i]);
        }

        $wordCount = 16;
        $dictionaryLines = explode(",", $strEncoding);

        if (!(count($dictionaryLines) === 49744)) {
            die("expecting 49744 but got " . count($dictionaryLines) . " dictionary words");
        }

        $brainKey = array();
        $end = $wordCount * 2;
        
        for ($i = 0; $i < $end; $i += 2) {
            // randomBuffer has 256 bits / 16 bits per word == 16 words
            $num = ($randomBuffer[$i] << 8) + $randomBuffer[$i + 1];
            
             // convert into a number between 0 and 1 (inclusive)
             $rndMultiplier = $num / pow(2, 16);
             $wordIndex = round(count($dictionaryLines) * $rndMultiplier);
            
             array_push($brainKey, $dictionaryLines[$wordIndex]);
        }

        $brainKeyStr = '';
        for ($i = 0; $i < count($brainKey); $i++) {
            $brainKeyStr .= $brainKey[$i] . ' ';
        }
        return trim($brainKeyStr);
    }

    /**
     * 是否是合法公钥
     * @param $public
     * @param $prefix
     * @return boolean
     */
    public static function isValidPublic(string $public, string $prefix = 'EOS')
    {
        if (strtoupper(substr($public, 0, 3)) == strtoupper($prefix)) {
            try {
                Utils::checkDecode(substr($public, 3), null);
                return true;
            } catch (\Exception $e) {
                return false;
            }
        }
        return false;
    }
    /**
     * 是否是合法wif私钥
     * @return boolean
     */
    public static function isValidPrivate(string $privateKey)
    {
        try {
            self::wifPrivateToPrivateHex($privateKey);
            return true;
        } catch(\Exception $e) {
            return false;
        }
    }
    /**
     * 签名
     * @param $data string
     * @param $privateKey wifi私钥
     * @return mixed
     * @throws
     */
    public static function sign(string $data, string $privateKey)
    {
        $dataSha256 = hash('sha256', hex2bin($data));
        return self::signHash($dataSha256, $privateKey);
    }
    /**
     * 对hash进行签名
     * @param $dataSha256 sha256
     * @param $privateKey wifi私钥
     * @return mixed
     * @throws
     */
    public static function signHash(string $dataSha256, string $privateKey)
    {
        $privHex = self::wifPrivateToPrivateHex($privateKey);
        $ecdsa = new Signature();
        $nonce = 0;
        while (true) {
            // Sign message (can be hex sequence or array)
            $signature = $ecdsa->sign($dataSha256, $privHex, $nonce);
            // der
            $der = $signature->toDER('hex');
            // Switch der
            $lenR = hexdec(substr($der, 6, 2));
            $lenS = hexdec(substr($der, (5 + $lenR) * 2, 2));
            // Need 32
            if ($lenR == 32 && $lenS == 32) {
                $r = $signature->r->toString('hex');
                $s = $signature->s->toString('hex');
                $i = dechex($signature->recoveryParam + 4 + 27);
                break;
            }
            $nonce++;
            if ($nonce % 10 == 0) {
                throw new \Exception('签名失败', 1);
            }
        }
        return 'SIG_K1_' . Utils::checkEncode(hex2bin($i . $r . $s), 'K1');
    }
    /**
     * Verify signed data.
     */
    public static function verify()
    {
        // TODO::
    }
    /**
     * Recover the public key used to create the signature.
     */
    public static function recover()
    {
        // TODO::
    }
    /**
     * Recover hash
     */
    public static function recoverHash()
    {
        // TODO::
    }
    /**
     * Recover hash
     */
    public static function sha256(string $data, $encoding = 'hex')
    {
        // TODO::
        // You can to use hash('sha256') of php;
    }
}