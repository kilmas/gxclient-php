<?php

require_once __DIR__ . '/../vendor/autoload.php';

use GXChain\GXClient\GXClient;

$private_key = "5KXXXX...";
$account_id_or_name = "biteweidu1";
$entry_point = "wss://testnet.gxchain.org";

$client = new GXClient($private_key, $account_id_or_name, $entry_point);

$keyPair = $client->generateKey();
var_dump($keyPair);

