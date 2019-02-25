# The PHP SDK for the GXC RPC API

A PHP wrapper for the GXC Chain RPC API.

## Docs

You can check out the [official docs](https://gxchain.github.io/gxclient-node/api/) but 
beware that some of the newer methods are missing. Also, some of the examples in those 
docs use outdated syntax `(╯°□°）╯︵ ┻━┻`

## Installing

```php
composer require kilmas/gxcrpc
```

## Usage

There is a shiny factory method to auto instantiate all dependencies: 

```php
require_once __DIR__ . '/vendor/autoload.php';

use Kilmas\GxcRpc\GXClient;

// your private_key
$private_key = "5KXXXX...";
// your account
$account_id_or_name = "biteweidu1";
$entry_point = "wss://testnet.gxchain.org";
$broadcast = true;

$client = new GXClient($private_key, $account_id_or_name, $entry_point);

// transfer
$client->transfer("biteweidu2", "test", "1 GXC", true);

// vote
$client->vote(["biteweidu1", "zhuliting"],"GXC", true);

/**
 * deploy smart contract
 * @param $contract_name
 * @param $code hex data as "0061736d010000000197..."
 * @param $abi json
 * @param $vm_type
 * @param $vm_version
 * @param $broadcast
 * @return mixed
 */
$client->createContract($contract_name = "contract_name", $code = "", $abi = [], "0", "0", $broadcast);

// updateContract
$client->updateContract($contract_name = "contract_name", null, $code = "", $abi, true);

// callContract
$client->callContract($contract_name = "contract_name", $method = "transfer", $param = ['memo' => ""], $amount_asset = "1 GXC", $broadcast);

// same as gxclient-node
$client->getChainID();
$client->getObject(1);
$client->getObjects([1, 2, 3]);
$client->getAccount($account_id_or_name);
```

### Get Info

All read only Chain API methods same as js, but not async, all rpc are sync

[gxclient-node/api](https://gxchain.github.io/gxclient-node/api/) 

## Contributing

All contributions are welcome! GXC TO DA MOON!!!

## License

Free for everyone!

MIT License
