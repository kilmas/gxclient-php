<?php

use GXChain\GXClient\GXClient;

class GXClientTest extends PHPUnit_Framework_TestCase
{
    public function testApiReturn()
    {
        $private_key = "5KXXXX...";
        $account_id_or_name = "biteweidu1";
        $entry_point = "wss://testnet.gxchain.org";

        $client = new GXClient($private_key, $account_id_or_name, $entry_point);

        $client->transfer("biteweidu2", "test", "1 GXC", true);
    }
}
