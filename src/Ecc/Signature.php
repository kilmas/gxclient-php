<?php
/**
 * Created by PhpStorm.
 * User: kilmas
 * Date: 2019/1/26
 * Time: 13:52
 */

namespace GXChain\GXClient\Ecc;

use Elliptic\EC\Signature as ECSignature;
use Elliptic\HmacDRBG;
use BN\BN;
use Elliptic\EC;

class Signature
{
    public $ec;

    public function __construct()
    {
        $this->ec = new EC('secp256k1');
    }

    public function sign($msg, $key, $options)
    {
        $data = null;
        $noncefn = null;
        if (isset($options)) {

            if (isset($options['data'])) {
                $data = $options['data'];
            }

            if (isset($options['noncefn'])) {
                $noncefn = $options['noncefn'];
            }
        }

        $message = $msg;
        $privateKey = $key;
        if (is_callable($noncefn)) {
            $getNonce = $noncefn;
            $noncefn = function ($counter) use ($getNonce, $message, $privateKey, $data) {
                $nonce = $getNonce($message, $privateKey, null, $data, $counter);
                if (!is_string($nonce) || strlen($nonce) !== 64) throwException("messages.ECDSA_SIGN_FAIL");
                return new BN($nonce, 16);
            };
        }
        $enc = ['canonical' => true, 'k' => $noncefn, 'pers' => $data];
        if (is_array($enc)) {
            $options = $enc;
            $enc = null;
        }
        if (empty($options))
            $options = [];

        $key = $this->ec->keyFromPrivate($key, $enc);
        $msg = $this->_truncateToN(new BN($msg, 16));

        // Zero-extend key to provide enough entropy
        $bytes = $this->ec->n->byteLength();
        $bkey = $key->getPrivate()->toArray('be', $bytes);

        // Zero-extend nonce to have the same byte size as N
        $nonce = $msg->toArray('be', $bytes);

        // Instantiate Hmac_DRBG
        $drbg = new HmacDRBG([
            'hash' => $this->ec->hash,
            'entropy' => $bkey,
            'nonce' => $nonce,
            'pers' => $options['pers'],
            'persEnc' => isset($options['persEnc']) ? $options['persEnc'] : 'utf8'
        ]);

        // Number of bytes to generate
        $ns1 = $this->ec->n->sub(new BN(1));

        for ($iter = 0; true; $iter++) {
            $k = isset($options['k']) ?
                $options['k']($iter) :
                new BN($drbg->generate($this->ec->n->byteLength()));
            $k = $this->_truncateToN($k, true);
            if ($k->cmpn(1) <= 0 || $k->cmp($ns1) >= 0)
                continue;

            $kp = $this->ec->g->mul($k);
            if ($kp->isInfinity())
                continue;

            $kpX = $kp->getX();
            $r = $kpX->umod($this->ec->n);
            if ($r->cmpn(0) === 0)
                continue;

            $s = $k->invm($this->ec->n)->mul($r->mul($key->getPrivate())->iadd($msg));
            $s = $s->umod($this->ec->n);
            if ($s->cmpn(0) === 0)
                continue;

            $recoveryParam = ($kp->getY()->isOdd() ? 1 : 0) |
                ($kpX->cmp($r) !== 0 ? 2 : 0);

            // Use complement of `s`, if it is > `n / 2`
            if ($options['canonical'] && $s->cmp($this->ec->nh) > 0) {
                $s = $this->ec->n->sub($s);
                $recoveryParam ^= 1;
            }

            return new ECSignature(array(
                "r" => $r,
                "s" => $s,
                "recoveryParam" => $recoveryParam
            ));
        }
    }

    private function _truncateToN($msg, $truncOnly = false)
    {
        $delta = intval(($msg->byteLength() * 8) - $this->ec->n->bitLength());
        if ($delta > 0) {
            $msg = $msg->ushrn($delta);
        }
        if ($truncOnly || $msg->cmp($this->ec->n) < 0)
            return $msg;
        return $msg->sub($this->ec->n);
    }


    public static function signBuffer($buff, $private_key, $public_key)
    {
        $dataSha256 = hash('sha256', hex2bin($buff));
        $privHex = $private_key;
        $ecdsa = new Signature();
        $nonce = 0;
        while (true) {
            // Sign message (can be hex sequence or array)
            $signature = $ecdsa->sign($dataSha256, $privHex, [
                'noncefn' => function () use ($dataSha256, $nonce) {
                    // $ds = new BN($dataSha256, 16);
                    // return hash('sha256', Buffer::utf8Slice($ds->toArray(), 0, $ds->byteLength()) . $nonce);
                    return hash('sha256', $dataSha256 . $nonce);
                }
            ]);
            $nonce++;
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
            if ($nonce % 10 == 0) {
                throw new \Exception('签名失败', 1);
            }
        }
        return $i . $r . $s;
    }
}