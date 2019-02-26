<?php

require_once __DIR__ . '/vendor/autoload.php';

use GXChain\GXClient\GXClient;


$private_key = "5KXXXX...";
$account_id_or_name = "biteweidu1";
$entry_point = "wss://testnet.gxchain.org";

$client = new GXClient($private_key, $account_id_or_name, $entry_point);

$client->transfer("biteweidu2", "test", "1 GXC", true);

$client->vote(["biteweidu1", "zhuliting"],"GXC", true);

// callContract
$client->callContract($contract_name = "test", "transfer", $param = ['memo' => ""], "", true);

// createContract
$client->createContract($contract_name = "testtest1111", $code = "", $abi = [], "0", "0", true);

// updateContract
$client->updateContract($contract_name = "testtest1111", null, $code, $abi, true);

// same as gxclient-node
$client->getChainID();
$client->getObject(1);
$client->getObjects([1, 2, 3]);
$client->getAccount($account_id_or_name);

