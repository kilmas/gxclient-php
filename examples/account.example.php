<?php
error_reporting(E_ALL);
require_once __DIR__ . '/../vendor/autoload.php';

use GXChain\GXClient\GXClient;

$private_key = "5J34qxxxx...";
$account_id_or_name = "xlogic";
$entry_point = "wss://testnet.gxchain.org";

$client = new GXClient($private_key, $account_id_or_name, $entry_point);

try {
    /**
     * getAccount(account_name)
     */
    $accountByName = $client->getAccount("init0");
    echo('getAccount:');
    echo "\n";
    echo(json_encode($accountByName));
    echo "\n";

    /**
     * getAccountByPublicKey(publicKey) 
     */
    $public_key =  $client->privateToPublic($private_key);
    $accountByPublic = $client->getAccountByPublicKey($public_key);
    echo('getAccountByPublicKey:');
    echo "\n";
    echo(json_encode($accountByPublic));
    echo "\n";

    /**
     * getAccountBalances(account_name) 
     */
    $balances = $client->getAccountBalances("init0");
    echo('getAccountBalances:');
    echo "\n";
    echo(json_encode($balances));
    echo "\n";
} catch (Exception $e) {
    echo($e->getMessage());
}