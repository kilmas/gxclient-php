<?php

require_once __DIR__ . '/vendor/autoload.php';

use Kilmas\GxcRpc\GXClient;


$private_key = "5KXXXX...";
$account_id_or_name = "biteweidu1";
$entry_point = "wss://testnet.gxchain.org";

$client = new GXClient($private_key, $account_id_or_name, $entry_point);

$client->transfer("biteweidu2", "test", "1 GXC", true);

$client->vote(["biteweidu1", "zhuliting"],"GXC", true);

$client->callContract($contract_name = "test", "transfer", $param = ['memo' => ""], "", true);


$client->createContract($contract_name = "testtest1111", $code = "", $abi = [], "0", "0", true);

$client->updateContract($contract_name = "testtest1111", null, $code, $abi, true);

echo $api->getInfo() . PHP_EOL;
echo $api->getObject(1) . PHP_EOL;
echo $api->getObjects([1, 2, 3]);
echo $api->getAccount($account_id_or_name) . PHP_EOL;

// as js
