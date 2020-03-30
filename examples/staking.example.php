<?php
error_reporting(E_ALL);
require_once __DIR__ . '/../vendor/autoload.php';

use GXChain\GXClient\GXClient;

$private_key = "5J34qxxxx...";
$account_id_or_name = "xlogic";
$entry_point = "wss://testnet.gxchain.org";

$client = new GXClient($private_key, $account_id_or_name, $entry_point);

try {
    $arr = $client->getStakingPrograms();
    print_r($arr);
    die;

    /**
     * @param {String} to - trust node account name
     * @param {Number} amount - the amount of GXC to staking
     * @param {String} program_id - the staking program id
     * @param {Object} options
     * @param {Array} options[fee_symbol]  - e.g: 'GXC'
     * @returns {Promise<any>}
    */
    $arr = $client->createStaking('gxcmoon-dev', 50, 5, true);
    print_r($arr);
    die;

    /**
     * @param {String} to - trust node account name
     * @param {String} staking_id - the staking id
     * @param {Object} options
     * @param {Array} options[fee_symbol]  - e.g: 'GXC'
     * @returns {Promise<any>}
    */
    $arr = $client->updateStaking('stakingtest20', '1.27.xxx', true);
    print_r($arr);
    die;

    /**
     * @param {String} to - trust node account name
     * @param {String} staking_id - the staking id
     * @param {Object} options
     * @param {Array} options[fee_symbol]  - e.g: 'GXC'
     * @returns {Promise<any>}
    */
    $arr = $client->claimStaking('1.27.xxx', true);
    print_r($arr);
    die;

} catch (Exception $e) {
    echo($e->getMessage());
}