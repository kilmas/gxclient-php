<?php
/**
 * Created by PhpStorm.
 * User: kilmas
 * Date: 2019/2/2
 * Time: 17:21
 */

namespace Kilmas\GxcRpc\Ecc;

use Elliptic\EC;

/** Provides symetric encrypt and decrypt via AES. */
class Aes
{

    private $iv;
    private $key;

    /** @private */
    public function __construct($iv, $key)
    {
        $this->iv = $iv;
        $this->key = $key;
    }

    /** This is an excellent way to ensure that all references to Aes can not operate anymore (example: a wallet becomes locked).  An application should ensure there is only one Aes object instance for a given secret `seed`. */
    function clear()
    {
        return $this->iv = $this->key = null;
    }

    /**
     * @param {string} seed - secret seed may be used to encrypt or decrypt.
     * @return mixed
     */
    static function fromSeed($seed)
    {
        if ($seed === null) {
            throwException("seed is required");
        }
        $_hash = hash('sha512', $seed);
        // _hash = _hash.toString('hex');
        // DEBUG console.log('... fromSeed _hash',_hash)
        return Aes::fromSha512($_hash);
    }

    /**
     * @param  {string} hash - A 128 byte hex string, typically one would call {@link fromSeed} instead.
     * @return mixed
     */
    static function fromSha512($hash)
    {
        $strlen = strlen($hash);
        assert($strlen == 128, `A Sha512 in HEX should be 128 characters long, instead got {$strlen}`);
//        $iv = encHex.parse(hash.substring(64, 96));
//        $key = encHex.parse(hash.substring(0, 64));
        $iv = hex2bin(substr($hash, 64, 32));
        $key = hex2bin(substr($hash, 0, 64));
        return new Aes($iv, $key);
    }

    static function fromBuffer($buf)
    {
        $len = strlen($buf);
        assert($len === 128, `A Sha512 Buffer should be 64 characters long, instead got {$len}`);
        return self::fromSha512($buf);
    }

    /**
     * @throws {Error} - "Invalid Key, ..."
     * @param  {PrivateKey} private_key - required and used for decryption
     * @param  {PublicKey} public_key - required and used to calcualte the shared secret
     * @param  {string} [nonce = ""] optional but should always be provided and be unique when re-using the same private/public keys more than once.  This nonce is not a secret.
     * @param  {string|Buffer} message - Encrypted message containing a checksum
     * @return mixed
     */
    static function decrypt_with_checksum($private_key, $public_key, $nonce, $message, $legacy = false)
    {

        // Warning: Do not put `nonce = ""` in the arguments, in es6 this will not convert "null" into an emtpy string
        if ($nonce == null) // null or undefined
            $nonce = "";

        $ec = new EC('secp256k1');

        $key1 = $ec->keyFromPrivate($private_key);
        $key2 = $ec->keyFromPublic($public_key, 'hex');

        $S = $key1->derive($key2->getPublic());


        try {
            $hash = hash('sha512', hex2bin($S->toString('hex')));
            $aes = self::fromSeed($nonce . $hash);

            $planebuffer = $aes->decrypt($message);
        } catch (\Exception $ex) { // fallback with a shared secret with no padding

            $planebuffer = "";
        }

        if (!(strlen($planebuffer) >= 4)) {
            throwException("Invalid key, could not decrypt message(1)");
        }

        // DEBUG console.log('... planebuffer',planebuffer)
        $checksum = substr($planebuffer, 0, 4);
        $plaintext = substr($planebuffer, 4);

        $new_checksum = hash('sha256', $plaintext);
        $new_checksum = substr($new_checksum, 0, 4);


        if (!($checksum === $new_checksum)) {
            throwException("Invalid key, could not decrypt message(2)");
        }

        return $plaintext;
    }

    /** Identical to {@link decrypt_with_checksum} but used to encrypt.  Should not throw an error.
     * @param
     * @param
     * @param
     * @return mixed message - Encrypted message which includes a checksum
     */
    public static function encrypt_with_checksum($private_key, $public_key, $nonce, $message)
    {

        // Warning: Do not put `nonce = ""` in the arguments, in es6 this will not convert "null" into an emtpy string

        if ($nonce == null) // null or undefined
            $nonce = "";

        $ec = new EC('secp256k1');

        $key1 = $ec->keyFromPrivate($private_key);
        $key2 = $ec->keyFromPublic($public_key, 'hex');

        $S = $key1->derive($key2->getPublic());


//        $S = private_key . get_shared_secret_v2($public_key);

        // D E B U G
        // console.log('encrypt_with_checksum', {
        //     priv_to_pub: private_key.toPublicKey().toString()
        //     pub: public_key.toPublicKeyString()
        //     nonce: nonce
        //     message: message.length
        //     S: S.toString('hex')
        // })

        $hash = hash('sha512', hex2bin($S->toString('hex')));
        $aes = Aes::fromSeed($nonce . $hash);
        // DEBUG console.log('... S',S.toString('hex'))
        $checksum = substr(hash('sha256', $message), 0, 8);
        $payload = hex2bin($checksum) . $message;
        // DEBUG console.log('... payload',payload.toString())
        return $aes->encrypt($payload);
    }

    /** @private */
    private function _decrypt_word_array($cipher)
    {
        // https://code.google.com/p/crypto-js/#Custom_Key_and_IV
        // see wallet_records.cpp master_key::decrypt_key
        return $this->decrypt(['ciphertext' => $cipher, 'salt' => null], $this->key, ['iv' => $this->iv]);
    }

    /** @private */
    private function _encrypt_word_array($plaintext)
    {
        //https://code.google.com/p/crypto-js/issues/detail?id=85
        return $cipher = $this->encrypt($plaintext, $this->key, ['iv' => $this->iv]);
    }

    /**
     * This method does not use a checksum, the returned data must be validated some other way.
     * @param  $ciphertext {string}
     * @return mixed
     */
    function decrypt($ciphertext)
    {
        assert($ciphertext, "Missing cipher text");
        // hex is the only common format
        $hex = $this->decryptHex($ciphertext);
        return $hex;
    }

    /** This method does not use a checksum, the returned data must be validated some other way.
     * @param  {string} plaintext
     * @return mixed
     */
    public function encrypt($plaintext)
    {
        $hex = $this->encryptHex($plaintext);
        return $hex;
    }

    /** This method does not use a checksum, the returned data must be validated some other way.
     * @param{string|Buffer} plaintext
     * @return mixed hex
     */
    function encryptToHex($plaintext)
    {
        return $this->encryptHex($plaintext);
    }

    /** This method does not use a checksum, the returned data must be validated some other way.
     * @param  {string} cipher - hex
     * @return mixed binary (could easily be readable text)
     */
    function decryptHex($cipher)
    {
        assert($cipher, "Missing cipher text");
        // Convert data into word arrays (used by Crypto)
        return $plainwords = $this->_decrypt_word_array($cipher);
    }

    /** This method does not use a checksum, the returned data must be validated some other way.
     * @arg {string} cipher - hex
     * @return mixed encoded as specified by the parameter
     */
    function decryptHexToBuffer($cipher)
    {
        assert($cipher, "Missing cipher text");
        // Convert data into word arrays (used by Crypto)
        return bin2hex(openssl_decrypt($cipher, 'AES256', $this->key, OPENSSL_RAW_DATA, $this->iv));
    }

    /** This method does not use a checksum, the returned data must be validated some other way.
     * @param  {string} $cipher - hex
     * @param  $encoding [encoding = 'binary'] - a valid Buffer encoding
     * @return mixed encoded as specified by the parameter
     */
    function decryptHexToText($cipher, $encoding = 'binary')
    {
        return $this->decryptHexToBuffer($cipher);
    }

    /** This method does not use a checksum, the returned data must be validated some other way.
     * @param $plainhex {string}  - hex format
     * @return mixed hex
     */
    public function encryptHex($plainhex)
    {
        return bin2hex(openssl_encrypt($plainhex, 'AES256', $this->key, OPENSSL_RAW_DATA, $this->iv));
    }
}

