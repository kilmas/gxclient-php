<?php
error_reporting(E_ALL);
require_once __DIR__ . '/../vendor/autoload.php';

use GXChain\GXClient\GXClient;

$client = new GXClient();

try {
    /**
     * register(account, activeKey, ownerKey, memoKey, faucet)
     */
    $keyPair = $client->generateKey();
    echo(json_encode($keyPair));
    echo "\n";
    $register = $client->register("xlogic-test120", $keyPair['publicKey'], "", "", "https://testnet.faucet.gxchain.org");
    echo('register:');
    echo "\n";
    echo(json_encode($register));
    echo "\n";
} catch (Exception $e) {
    echo($e->getMessage());
}