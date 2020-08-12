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
     * getAsset(symbol)
     */
    $asset = $client->getAsset("GXC");
    echo('getAsset:');
    echo "\n";
    echo(json_encode($asset));
    echo "\n";
} catch (Exception $e) {
    echo($e->getMessage());
}