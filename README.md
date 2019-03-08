# gxclient-php
A client to interact with gxchain implemented in PHP
<p>
 <a href='javascript:;'>
   <img width="300px" src='https://raw.githubusercontent.com/gxchain/gxips/master/assets/images/task-gxclient.png'/>
 </a>
 <a href='javascript:;'>
   <img width="300px" src='https://raw.githubusercontent.com/gxchain/gxips/master/assets/images/task-gxclient-en.png'/>
 </a>
</p> 

# Supported Versions
PHP7.0

# Install

You can install this library via Composer:

```
composer require gxchain/gxclient
```
# APIs
- [x] [Keypair API](#keypair-api)
- [x] [Chain API](#chain-api)
- [x] [Faucet API](#faucet-api)
- [x] [Account API](#account-api)
- [x] [Asset API](#asset-api)
- [x] [Contract API](#contract-api)


## Constructors

``` php
//init GXClient
new GXClient($private_key, $account_id_or_name, $entry_point);
```

## Keypair API

``` php
//generate key pair locally
function generateKey(String $brainKey);
//export public key from private key
function privateToPublic(String $privateKey);
//check if public key is valid
function isValidPublic(String $publicKey);
//check if private key is valid
function isValidPrivate(String $privateKey);
```

## Chain API

``` php
//get current blockchain id
function getChainID();
//get dynamic global properties 
function getDynamicGlobalProperties();
//get block object
function getObject(String $object_id);
//get block objects
function getObjects(Array $object_ids);
// get block by block height
function getBlock(Integer $blockHeight);
//send transfer request to entryPoint node
function transfer(String $to, String $memo, String $amount_asset, Boolean $broadcast);
//vote for accounts
function vote(Array $accounts, String $fee_paying_asset, Boolean $broadcast);
//broadcast transaction
function broadcast(Object $tx)
```

## Faucet API

``` php
//register gxchain account
function register(String $account, String $activeKey, String $ownerKey, String $memoKey, String $faucet);
```
## Account API

``` php
// get account info by account name
function getAccount(String $account_name);
//get account_ids by public key
function getAccountByPublicKey(String $publicKey);
//get account balances by account name
function getAccountBalances(String $account_name);
```

## Asset API

``` php
//get asset info by symbol
function getAsset(String $symbol);
```

## Contract API

``` php
// call smart contract method
function callContract(String $contract_name, String $method_name, Object $params, String $amount_asset, Boolean $broadcast);
// create smart contract method
function createContract(String $contract_name, String $code, Object $abi, String $vm_type, String $vm_version, Boolean $broadcast);
// update smart contract method
function updateContract(String $contract_name, String $newOwner, String $code, Object $abi, Boolean $broadcast);
//get contract table by contract_name
function getContractTable(String $contract_name) 
//get contract abi by contract_name
function getContractABI(String $contract_name) 
public List<Table> getContractTable(String contractName);
//get contract table objects
function getTableObjects(String $contract_name, String $table_name, Integer $start, Integer $limit) 
```

# Usage

、、、php
require "vendor/autoload.php";

use GXChain\GXClient\GXClient;

$client = new GXClient();

$keyPair = $client->generateKey();

echo(json_encode($keyPair));
、、、

For more examples, please refer to the examples directory.

# Other

- It's very welcome for developers to translate this project into different programing languages
- We are looking forward to your pull requests
