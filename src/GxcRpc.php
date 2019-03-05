<?php
/**
 * Created by PhpStorm.
 * User: kilmas
 * Date: 2019/1/27
 * Time: 15:38
 */

namespace GXChain\GXClient;

use GuzzleHttp\Client as Guzzle;
use GXChain\GXClient\Exception\HttpException;

class GxcRpc
{

    private $callID = 0;
    private $client = null;
    private $service;

    public function __construct($service)
    {
        $this->service = $service;
        $this->client = new Guzzle;
    }

    public static function instance($service)
    {
        return new GxcRpc($service);
    }

    function query($method, $params)
    {
        try {
            $response = $this->client->post($this->service, ['headers' => ['Accept' => 'application/json'], 'json' => [
                'jsonrpc' => "2.0",
                'method' => $method,
                'params' => $params,
                "id" => ++$this->callID
            ]]);
        } catch (\Exception $t) {
            throw new HttpException("POST Request failed: {$t->getMessage()}");
        }

        $arr = json_decode((string)$response->getBody(), true);
        return isset($arr['result']) ? $arr['result'] : $arr['error'];
    }

    function broadcast($tx)
    {
        try {
            $response = $this->client->post($this->service, ['json' => [
                'jsonrpc' => "2.0",
                'method' => "call",
                'params' => [2, "broadcast_transaction_synchronous", [$tx]],
                'id' => ++$this->callID
            ]]);
        } catch (\Throwable $t) {
            throw new HttpException("POST Request failed: {$t->getMessage()}");
        }
        $arr = json_decode((string)$response->getBody(), true);
        return isset($arr['result']) ? $arr['result'] : $arr['error'];
    }
}