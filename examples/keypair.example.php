<?php

require_once __DIR__ . '/../vendor/autoload.php';

use GXChain\GXClient\GXClient;

$private_key = "5J34qxxxx...";
$account_id_or_name = "xlogic";
$entry_point = "wss://testnet.gxchain.org";

$client = new GXClient($private_key, $account_id_or_name, $entry_point);

try {
    /**
     * generateKey(brainKey) 
     */
    $keyPair = $client->generateKey();
    echo('generateKey:');
    echo "\n";
    echo(json_encode($keyPair));
    echo "\n";

    /**
     * privateToPublic(privateKey)
     */
    $publicKey = $client->privateToPublic($keyPair['privateKey']);
    echo('privateToPublic:');
    echo "\n";
    echo(json_encode($publicKey));
    echo "\n";

    /**
     * isValidPublic(publicKey)
     */
    $validPublic = $client->isValidPublic($keyPair['publicKey']);
    echo('privateToPublic:');
    echo "\n";
    echo(json_encode($validPublic));
    echo "\n";

    /**
     * isValidPrivate(privateKey) 
     */
    $validPrivate = $client->isValidPrivate($keyPair['privateKey']);
    echo('privateToPublic:');
    echo "\n";
    echo(json_encode($validPrivate));
    echo "\n";
} catch (Exception $e) {
    echo($e->getMessage());
}