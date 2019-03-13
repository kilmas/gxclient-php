<?php
error_reporting(E_ALL);
require_once __DIR__ . '/../vendor/autoload.php';

use GXChain\GXClient\GXClient;
use GXChain\GXClient\Ecc\Signature;

$private_key = "5J34qNsM2nrDarhKM5bvsJMqYuQtimv9Cn5ophHq6hZWxwgLv8e";
$account_id_or_name = "xlogic-test112";
$entry_point = "wss://testnet.gxchain.org";

$client = new GXClient($private_key, $account_id_or_name, $entry_point);
try {
    /**
     * getChainID() 
     */
    $chainId = $client->getChainID();
    echo('getChainID:');
    echo "\n";
    echo($chainId);
    echo "\n";

    /**
     * getDynamicGlobalProperties()  
     */
    $dynamicGlobalProperties = $client->getDynamicGlobalProperties();
    echo('getDynamicGlobalProperties:');
    echo "\n";
    echo(json_encode($dynamicGlobalProperties));
    echo "\n";

    /**
     * getBlock(blockHeight) 
     */
    $block = $client->getBlock('11849514');
    echo('getBlock:');
    echo "\n";
    echo(json_encode($block));
    echo "\n";

    /**
     * getObject(object_id)
     */
    $object = $client->getObject('1.3.1');
    echo('getObject:');
    echo "\n";
    echo(json_encode($object));
    echo "\n";

    /**
     * getObjects(object_ids)
     */
    $objects = $client->getObjects(['1.3.1', '1.3.2']);
    echo('getObjects:');
    echo "\n";
    echo(json_encode($objects));
    echo "\n";

    /**
     * transfer(to, memo, amount_asset, broadcast)
     */
    // Set broadcast to false so we could calculate the fee before broadcasting
    $broadcast = true;
    // Sending 1GXC to init0 with memo "GXChain NB"
    $transfer = $client->transfer("init0", "GXChain NB", "1 GXC", $broadcast);
    echo('transfer:');
    echo "\n";
    echo(json_encode($transfer));
    echo "\n";

    /**
     * vote(account_ids, fee_paying_asset, broadcast)
     */
    // Set broadcast to false so we could calculate the fee before broadcasting
    $broadcast = true;
    // Voting for init0
    $vote = $client->vote(["1.2.6"], "GXC", $broadcast);
    echo('vote:');
    echo "\n";
    echo(json_encode($vote));
    echo "\n";
    
    /**
     * broadcast(tx)
     */
    $tx = Object(); // transaction object
    $broadcast = $client->broadcast($tx);
    echo('broadcast:');
    echo "\n";
    echo(json_encode($broadcast));
    echo "\n";
} catch (Exception $e) {
    echo($e->getMessage());
}