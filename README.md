# The PHP SDK for the GXC RPC API

A PHP wrapper for the GXC Chain RPC API.

## Background

You can check out the [official docs](https://gxchain.github.io/gxclient-node/api/) but 
beware that some of the newer methods are missing. Also, some of the examples in those 
docs use outdated syntax `(╯°□°）╯︵ ┻━┻`

## Installing

```php
composer require Kilmas/GxcRpc
```

## Configuration

Create a dotenv `.env` file in the project root, with your favourite RPC API host. You can use
`.env.example` as a template:

```
cp .env.example .env
```

## Usage

There is a shiny factory method to auto instantiate all dependencies: 

```php
require_once __DIR__ . '/vendor/autoload.php';

use Kilmas\GxcRpc\GXClient;


$private_key = "5KXXXX...";
$account_id_or_name = "biteweidu1";
$entry_point = "wss://testnet.gxchain.org";

$client = new GXClient($private_key, $account_id_or_name, $entry_point);

$client->transfer("biteweidu2", "test", "1 GXC", true);
```

## Examples

To get you started, there is a simple example runner which covers all API commands.

Just run this via cli to see example output for all commands:

```
cd examples
php chain.php
```

## API Methods

All read only Chain API methods are covered.

### Get Info

same as js, but not async, all rpc are sync 

## Contributing

All contributions are welcome! GXC TO DA MOON!!!



## License

Free for everyone!

MIT License
