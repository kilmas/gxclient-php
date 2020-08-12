<?php
/**
 * Created by PhpStorm.
 * User: kilmas
 * Date: 2019/1/26
 * Time: 13:18
 */

namespace GXChain\GXClient;

use Elliptic\EC;
use GXChain\GXClient\Ecc\Signature;
use GXChain\GXClient\Ecc\Ecc;
use GXChain\GXClient\Adapter\Http\HttpInterface;
use GXChain\GXClient\Ecc\Aes;
use GXChain\GXClient\Ecc\Utils;

class GXClient
{

    const DEFUALT_EXPIRE_SEC = 60;
    /**
     * @var HttpInterface
     */
    protected $client;

    protected $private_key;

    protected $account_id_or_name;

    protected $account_id;

    protected $account;

    protected $connected;

    protected $chain_id;

    protected $witness;

    protected $signProvider;

    protected $host;


    public function __construct($private_key = "", $account_id_or_name = "", $entry_point = "wss://node1.gxb.io", $signProvider = null)
    {
        $this->private_key = $private_key;
        $this->account_id_or_name = $account_id_or_name;
        if (preg_match("/^1.2.\d+$/", $account_id_or_name)) {
            $this->account_id = $account_id_or_name;
        } else {
            $this->account = $account_id_or_name;
        }
        $this->connected = false;
        $this->chain_id = "";
        $this->witness = $entry_point;
        $this->signProvider = $signProvider;
        $this->host = str_replace("ws://", "http://", str_replace("wss://", "https://", $this->witness));
        $this->rpc = $this->client = new GxcRpc($this->host);
    }

    /**
     * generate key pair locally
     * @param $brainKey
     * @return array [brainKey: *, privateKey: *, publicKey: *]
     */
    function generateKey($brainKey = "")
    {   
        $ec = new EC('secp256k1');
        
        if ($brainKey === '') {
            $brainKey = Ecc::suggestBrainKey();
        }
        $privateKey = Ecc::seedPrivate($brainKey);;
        $publicKey = Ecc::privateToPublic($privateKey, 'GXC');
        return [
            'brainKey' => $brainKey,
            'privateKey' => $privateKey,
            'publicKey' => $publicKey
        ];
    }

    /**
     * export public key from private key
     * @param privateKey
     * @return string
     */
    function privateToPublic($privateKey)
    {
        return Ecc::privateToPublic($privateKey, 'GXC');
    }

    /**
     * check if public key is valid
     * @param publicKey
     * @return Boolean
     */
    function isValidPublic($publicKey)
    {
        return Ecc::isValidPublic($publicKey, $prefix = 'GXC');
    }

    /**
     * check if private key is valid
     * @param privateKey
     * @return Boolean
     */
    function isValidPrivate($privateKey)
    {
        return Ecc::isValidPrivate($privateKey);
    }

    /**
     * generate sign data by buff and private key
     * @param buff
     * @param privateKey
     * @return string
     */
    function signBuffer($buff, $privateKey)
    {
        return Signature::signBuffer($buff, $privateKey);
    }

    /**
     * register an account by faucet
     * curl ‘https://opengateway.gxb.io/account/register' -H 'Content-type: application/json' -H 'Accept: application/json’ -d ‘{“account”:{“name”:”gxb123”,”owner_key”:”GXC5wQ4RtjouyobBV57vTx7boBj4Kt3BUxZEMsUD3TU369d3C9DqZ”,”active_key”:”GXC7cPVyB9F1Pfiaaxw4nY3xKADo5993hEsTjFs294LKwhqsUrFZs”,”memo_key”:”GXC7cPVyB9F1Pfiaaxw4nY3xKADo5993hEsTjFs294LKwhqsUrFZs”,”refcode”:null,”referrer”:null}}’
     * @param account <String> - Account name
     * @param activeKey <String> - Public Key for account operator
     * @param ownerKey <String> - Public Key for account owner
     * @param memoKey <String> - Public Key for memo
     * @param $faucet
     * @return mixed
     * @throws
     */
    function register($account, $activeKey, $ownerKey, $memoKey, $faucet = "https://opengateway.gxb.io")
    {
        if (!$activeKey) {
            throw new \Exception("active key is required");
        } else {
            return $this->client->register($faucet, ['account' => [
                'name' => $account,
                'active_key' => $activeKey,
                'owner_key' => $ownerKey ? $ownerKey : $activeKey,
                'memo_key' => $memoKey ? $memoKey : $activeKey
            ]]);
        }
    }

    /**
     * fetching latest block each 3 seconds
     */
    private function _latestBlockTask($force)
    {
        if ($this->isTaskStarted && !$force) {
            return false;
        }
        $this->isTaskStarted = true;
    }

    /**
     * get object by id
     * @param object_id
     * @return mixed
     */
    function getObject($object_id)
    {
        // return this._query("get_objects", [[object_id]]).then(results => results[0]);
        $result = $this->_query("get_objects", [[$object_id]]);
        if (isset($result[0]))
            return $result[0];
        else
            return null;
    }

    /**
     * get objects
     * @param {Array} object_ids
     * @return mixed
     */
    function getObjects($object_ids)
    {
        $result = $this->_query("get_objects", [$object_ids]);
        return $result;
    }

    /**
     * get account info by account name
     * @param account_name
     * @return mixed
     */
    function getAccount($account_name)
    {
        return $this->_query("get_account_by_name", [$account_name]);
    }

    /**
     * get current blockchain id
     */
    function getChainID()
    {
        return $this->_query("get_chain_id", []);
    }

    /**
     * get dynamic global properties
     * @returns {*}
     */
    function getDynamicGlobalProperties()
    {
        return $this->_query("get_dynamic_global_properties", []);
    }

    /**
     * get account_ids by public key
     * @param publicKey
     * @return mixed
     */
    function getAccountByPublicKey($publicKey)
    {
        $results = $this->_query("get_key_references", [[$publicKey]]);
        if (empty($results[0])) {
            return null;
        } else {
            return array_unique($results[0]);
        }
    }

    /**
     * get account balances by account name
     * @param account_name
     * @return mixed
     */
    function getAccountBalances($account_name)
    {
        if ($account = $this->getAccount($account_name)) {
            return $this->_query("get_account_balances", [$account['id'], []]);
        }
    }

    /**
     * get asset info by symbol
     * @param symbol
     * @return mixed
     */
    function getAsset($symbol)
    {
        $assets = $this->_query("lookup_asset_symbols", [[$symbol]]);
        if (empty($assets[0])) {
            return null;
        } else {
            return $assets[0];
        }
    }

    /**
     * get block by block height
     * @param blockHeight
     * @return integer
     */
    function getBlock($blockHeight)
    {
        return $this->_query("get_block", [$blockHeight]);
    }

    /**
     * detect new transactions related to this.account_id
     * @param blockHeight
     * @param callback
     */
    function detectTransaction($blockHeight, $callback)
    {

    }

    /**
     * send transfer request to witness node
     * @param to
     * @param memo
     * @param amount_asset
     * @param $broadcast
     * @return mixed
     */
    function transfer($to, $memo, $amount_asset, $broadcast = false, $fee_paying_asset = "GXC")
    {
        $memo_private = $this->private_key;
        $isMemoProvider = false;
        // Check PrivateKey isValid
        if (!Ecc::isValidPrivate($memo_private)) {
            throw new \Exception("Not a Valid PrivateKey");
        }
        // if memo is function, it can receive fromAccount and toAccount, and should return a full memo object
        if (gettype($memo) === "function") {
            $isMemoProvider = true;
        }
        if (!strpos($amount_asset, " ")) {
            throw new \Exception("Incorrect format of asset, eg. \"100 GXC\"");
        } else {
            $assetArr = explode(" ", $amount_asset);
            $amount = intval($assetArr[0]);
            $asset = $assetArr[1];

            $this->_connect();

            $results = $this->_query("get_objects", [[$this->account_id]]);
            $fromAcc = $results[0];

            $toAcc = $this->getAccount($to);
            $assetInfo = $this->getAsset($asset);
            $fee_asset = $this->getAsset($fee_paying_asset);

            if (!$toAcc) {
                throw new \Exception("Account {$to} not exist");
            }
            if (!$assetInfo) {
                throw new \Exception("Asset {$asset} not exist");
            }
            if (!$fee_asset) {
                throw new \Exception("Asset {$fee_paying_asset} not exist");
            }
            $amount = [
                "amount" => $this->_accMult($amount, pow(10, $assetInfo['precision'])),
                "asset_id" => $assetInfo['id']
            ];

            if (!$isMemoProvider) {
                if ($memo) {
                    $memo_from_public = $fromAcc['options']['memo_key'];

                    // The 1s are base58 for all zeros (null)
                    if (preg_match("/111111111111111111111/", $memo_from_public)) {
                        $memo_from_public = null;
                    }

                    $memo_to_public = $toAcc['options']['memo_key'];
                    if (preg_match("/111111111111111111111/", $memo_to_public)) {
                        $memo_to_public = null;
                    }

                    // $fromPrivate = Ecc::privateToPublic($memo_private);

                    if ($memo_from_public != Ecc::privateToPublic($memo_private, 'GXC')) {
                        throw new \Exception("memo signer not exist");
                    }
                }

                if ($memo && $memo_to_public && $memo_from_public) {
                    $nonce = TransactionHelper::unique_nonce_uint64();

                    $memo_object = [
                        'from' => $memo_from_public,
                        'to' => $memo_to_public,
                        'nonce' => $nonce,
                        'message' => Aes::encrypt_with_checksum(
                            Ecc::wifPrivateToPrivateHex($memo_private),
                            Utils::checkDecode(substr($memo_to_public, 3), null),
                            $nonce,
                            $memo
                        )
                    ];
                }
            } else {
                try {
                    $memo_object = memo($fromAcc, $toAcc);
                } catch (\Exception $e) {
                    throw new \Exception($e);
                }
            }

            $tr = $this->_createTransaction();

            $tr->add_operation($tr->get_type_operation("transfer", [
                'fee' => [
                    'amount' => 0,
                    'asset_id' => $fee_asset['id']
                ],
                'from' => $fromAcc['id'],
                'to' => $toAcc['id'],
                'amount' => $amount,
                'memo' => $memo_object
            ]));
            return $this->_processTransaction($tr, $broadcast);
        }
    }

    /**
     * get contract abi by contract_name
     * @param $contract_name
     * @return string abi
     */
    function getContractABI($contract_name)
    {
        $acc = $this->getAccount($contract_name);
        return $acc['abi'];
    }

    /**
     * get contract table by contract_name
     * @param $contract_name
     * @return mixed
     */
    function getContractTable($contract_name)
    {
        return $this->getAccount($contract_name);
    }

    /**
     * fetch contract table record by contract_name and table_name
     * @param $contract_name
     * @param $table_name
     * @param $start
     * @param $limit
     * @return mixed
     */
    function getTableObjects($contract_name, $table_name, $start = 0, $limit = 100)
    {

        $acc = $this->getAccount($contract_name);
        if ($acc) {
            $contract_id = object_id_type($acc['id']) . toString();
            return $this->_query("get_table_objects",
                [$contract_id, $contract_id, string_to_name($table_name), $start, -1, $limit]
            );
        } else {
            throw new \Exception("Contract not found");
        }
    }

    /**
     * deploy smart contract
     * @param $contract_name
     * @param $code
     * @param $abi
     * @param $vm_type
     * @param $vm_version
     * @param $broadcast
     * @return mixed
     */
    function createContract($contract_name, $code, $abi, $vm_type = "0", $vm_version = "0", $broadcast = false, $fee_paying_asset = "GXC")
    {
        $this->_connect();

        $fee_asset = $this->getAsset($fee_paying_asset);
        if (!$fee_asset) {
            throw new \Exception("Asset {$fee_paying_asset} not exist");
        }

        $tr = $this->_createTransaction();
        $tr->add_operation($tr->get_type_operation("create_contract", [
            'fee' => [
                'amount' => 0,
                'asset_id' => $fee_asset['id']
            ],
            'name' => $contract_name,
            'account' => $this->account_id,
            'vm_type' => $vm_type,
            'vm_version' => $vm_version,
            'code' => $code,
            'abi' => $abi
        ]));
        return $this->_processTransaction($tr, $broadcast);
    }

    /**
     * update smart contract
     * @param contract_name
     * @param newOwner
     * @param code
     * @param abi
     * @param $broadcast
     * @return mixed
     */
    function updateContract($contract_name, $newOwner = null, $code, $abi, $broadcast = false, $fee_paying_asset = "GXC")
    {
        $this->_connect();

        $fee_asset = $this->getAsset($fee_paying_asset);
        if (!$fee_asset) {
            throw new \Exception("Asset {$fee_paying_asset} not exist");
        }

        $results[0] = $this->getAccount($contract_name);
        if ($newOwner) {
            $results[1] = $this->getAccount($newOwner);
        }
        $tr = $this->_createTransaction();
        $opt = [
            'fee' => [
                'amount' => 0,
                'asset_id' => $fee_asset['id']
            ],
            'owner' => $this->account_id,
            'contract' => $results[0]['id'],
            'code' => $code,
            'abi' => $abi
        ];
        if ($newOwner) {
            $opt['new_owner'] = $results[1]['id'];
        }
        $tr->add_operation($tr->get_type_operation("update_contract", $opt));
        return $this->_processTransaction($tr, $broadcast);
    }

    /**
     * call smart contract method
     * @param $contract_name {String} - The name of the smart contract
     * @param $method_name {String} - Method/Action name
     * @param $params {JSON} - parameters
     * @param $amount_asset "100 GXC" - The amount of asset for payable action
     * @param $broadcast {Boolean} - Broadcast the transaction or just return a serialized transaction
     * @return mixed
     */
    function callContract($contract_name, $method_name, $params, $amount_asset, $broadcast = false, $fee_paying_asset = "GXC")
    {
        $this->_connect();
        if ($amount_asset) {
            if (!strpos($amount_asset, " ")) {
                throw new \Exception("Incorrect format of asset, eg. \"100 GXC\"");
            }
        }
        $amount = $amount_asset ? floatval(explode(" ", $amount_asset)[0]) : 0;
        $asset = $amount_asset ? explode(" ", $amount_asset)[1] : "GXC";

        $acc = $this->getAccount($contract_name);
        $assetInfo = $this->getAsset($asset);
        $fee_asset = $this->getAsset($fee_paying_asset);

        if (!$assetInfo) {
            throw new \Exception("Asset {$asset} not exist");
        }
        if (!$fee_asset) {
            throw new \Exception("Asset {$fee_paying_asset} not exist");
        }

        $amount = [
            'amount' => $this->_accMult($amount, pow(10, $assetInfo['precision'])),
            'asset_id' => $assetInfo['id']
        ];
        if ($acc) {
            $abi = $acc['abi'];
            $act = [
                'method_name' => $method_name,
                'data' => \GXChain\GXClient\Gxc\TxSerialize::serializeCallData($method_name, $params, $abi)
            ];

            $tr = $this->_createTransaction();
            $opts = [
                "fee" => [
                    "amount" => 0,
                    "asset_id" => $fee_asset['id']
                ],
                "account" => $this->account_id,
                "contract_id" => $acc['id'],
                "method_name" => $act['method_name'],
                "data" => $act['data']
            ];

            if (!empty($amount['amount'])) {
                $opts['amount'] = $amount;
            }
            $tr->add_operation($tr->get_type_operation("call_contract", $opts));
            return $this->_processTransaction($tr, $broadcast);
        } else {
            throw new \Exception("Contract not found");
        }
    }

    /**
     * vote for accounts
     * @param account_ids - An array of account_id to vote
     * @param $fee_paying_asset - The asset to pay the fee
     * @param $broadcast
     * @return mixed
     */
    function vote($accounts, $fee_paying_asset = "GXC", $broadcast = false)
    {
        if ($this->_connect()) {
            $_accounts = [];
            foreach ($accounts as $a) {
                $_account = $this->getObject($a);
                $_accounts[] = $_account;
                $account_ids[] = $_account['id'];
            }
            $accs = $this->_query("get_objects", [[$this->account_id, "2.0.0"]]);
            $acc = $accs[0];
            $globalObject = $accs[1];
            $fee_asset = $this->getAsset($fee_paying_asset);

            if (!$acc) {
                throw new \Exception("account_id {$this->account_id} not exist");
            }
            if (!$fee_asset) {
                throw new \Exception("Asset {$fee_paying_asset} not exist");
            }
            $new_options = [
                'memo_key' => $acc['options']['memo_key'],
                'voting_account' => empty($acc['options']['voting_account']) ? "1.2.5" : $acc['options']['voting_account']
            ];
            $votes = [];
            $res1 = [];
            $res2 = [];
            foreach ($account_ids as $account_id) {
                $res1[] = $this->_query("get_witness_by_account", [$account_id]);
                $res2[] = $this->_query("get_committee_member_by_account", [$account_id]);
            }
            $votes = array_merge($res1, $res2);
            $votes = array_filter($votes);
            $votes = array_map(function ($v) {
                return $v['vote_id'];
            }, $votes);
            $new_options['votes'] = array_unique(array_merge($votes, $acc['options']['votes']));
            $num_witness = 0;
            $num_committee = 0;
            foreach ($new_options['votes'] as $v) {
                $vote_type = explode(":", $v)[0];
                if ($vote_type == 0) {
                    $num_committee += 1;
                }
                if ($vote_type == 1) {
                    $num_witness += 1;
                }
            };
            $new_options['num_committee'] = min($num_committee, $globalObject['parameters']['maximum_committee_count']);
            $new_options['num_witness'] = min($num_witness, $globalObject['parameters']['maximum_witness_count']);
            $sort_options = [];

            foreach ($new_options['votes'] as $value) {
                $a_split = explode(":", $value);
                $sort_options[$a_split[1]] = $value;
            }
            ksort($sort_options);
            $i = 0;
            $new_options['votes'] = [];
            foreach ($sort_options as $value) {
                $new_options['votes'][$i++] = $value;
            }

            $tr = $this->_createTransaction();

            $tr->add_operation($tr->get_type_operation("account_update", [
                'fee' => [
                    'amount' => 0,
                    'asset_id' => $fee_asset['id']
                ],
                'account' => $this->account_id,
                'new_options' => $new_options,
            ]));
            return $this->_processTransaction($tr, $broadcast);
        }

    }

    /**
     * calculate fee of a operation
     * @param $operation
     * @param $feeAssetId
     * @return mixed
     */
    function fee($operation, $feeAssetId = "1.3.1")
    {
        return $this->_query("get_required_fees", [$operation, $feeAssetId]);
    }

    /**
     * get staking programs
     */
    public function getStakingPrograms()
    {
        $result = $this->_query("get_objects", [['2.0.0']]);
        if (isset($result[0])) {
            foreach ($result[0]['parameters']['extensions'] as $key => $value) {
                if ($value[0] == 11) {
                    return $value[1]['params'];
                }
            }
        } else {
            return null;
        }
    }

    /**
     * @param {String} to - trust node account name
     * @param {Number} amount - the amount of GXC to staking
     * @param {String} program_id - the staking program id
     * @param {Boolean} $broadcast
     * @param {Array} options[fee_symbol]  - e.g: 'GXC'
     * @returns {Promise<any>}
     */
    public function createStaking($to, $amount, $program_id, $broadcast, $options = ['fee_symbol' => 'GXC'])
    {
        $this->_connect();
        $trustNodeAccount = $this->getAccount($to);
        if (!$trustNodeAccount) {
            throw new \Exception("Account {$to} not exist");
        }
        $tustNodeInfo = $this->_query("get_witness_by_account", [$trustNodeAccount['id']]);
        if (!$tustNodeInfo) {
            throw new \Exception("Account {$to} is not a trustnode");
        }
        $program_staking_arr = $this->getStakingPrograms();
        $program             = [];
        foreach ($program_staking_arr as $key => $value) {
            if ($value[0] == $program_id) {
                $program = $value;
            }
        }
        if (!$program) {
            throw new \Exception("Program {$program_id} not exist");
        }
        if (!$program[1]['is_valid']) {
            throw new \Exception("Program {$program_id} disabled");
        }

        $feeInfo = $this->getAsset($options['fee_symbol']);

        if (!$feeInfo) {
            throw new \Exception("Asset {$options['fee_symbol']} not exist");
        }

        $amount = intval($amount);

        $tr = $this->_createTransaction();

        $tr->add_operation($tr->get_type_operation("staking_create", [
            'fee'          => [
                'amount'   => 0,
                'asset_id' => $feeInfo['id'],
            ],
            'owner'        => $this->account_id,
            'trust_node'   => $tustNodeInfo['id'],
            'program_id'   => "{$program_id}",
            'amount'       => [
                'amount'   => $this->_accMult($amount, pow(10, 5)),
                'asset_id' => '1.3.1',
            ],
            'weight'       => $program[1]['weight'],
            'staking_days' => $program[1]['staking_days'],
        ]));
        return $this->_processTransaction($tr, $broadcast);
    }
    /**
     * @param {String} to - trust node account name
     * @param {String} staking_id - the staking id
     * @param {Boolean} $broadcast
     * @param {Array} options[fee_symbol]  - e.g: 'GXC'
     * @returns {Promise<any>}
     */
    public function updateStaking($to, $staking_id, $broadcast = false, $options = ['fee_symbol' => 'GXC'])
    {
        $this->_connect();
        $trustNodeAccount = $this->getAccount($to);
        if (!$trustNodeAccount) {
            throw new \Exception("Account {$to} not exist");
        }
        $tustNodeInfo = $this->_query("get_witness_by_account", [$trustNodeAccount['id']]);
        if (!$tustNodeInfo) {
            throw new \Exception("Account {$to} is not a trustnode");
        }
        $feeInfo = $this->getAsset($options['fee_symbol']);

        if (!$feeInfo) {
            throw new \Exception("Asset {$options['fee_symbol']} not exist");
        }

        $tr = $this->_createTransaction();

        $tr->add_operation($tr->get_type_operation("staking_update", [
            'fee'        => [
                'amount'   => 0,
                'asset_id' => $feeInfo['id'],
            ],
            'owner'      => $this->account_id,
            'trust_node' => $tustNodeInfo['id'],
            'staking_id' => $staking_id,
        ]));
        return $this->_processTransaction($tr, $broadcast);
    }

    /**
     * @param {String} staking_id - the staking id
     * @param {Boolean} $broadcast
     * @param {Array} options[fee_symbol]  - e.g: 'GXC'
     * @returns {Promise<any>}
     */
    public function claimStaking($staking_id, $broadcast, $options = ['fee_symbol' => 'GXC'])
    {
        $this->_connect();
        $feeInfo = $this->getAsset($options['fee_symbol']);

        if (!$feeInfo) {
            throw new \Exception("Asset {$options['fee_symbol']} not exist");
        }
        $tr = $this->_createTransaction();

        $tr->add_operation($tr->get_type_operation("staking_claim", [
            'fee'        => [
                'amount'   => 0,
                'asset_id' => $feeInfo['id'],
            ],
            'owner'      => $this->account_id,
            'staking_id' => $staking_id,
        ]));
        return $this->_processTransaction($tr, $broadcast);
    }
    /**
     * accurate multiply - fix the accurate issue of javascript
     * @param arg1
     * @param arg2
     * @return integer
     * @throws
     */
    private function _accMult($arg1, $arg2)
    {
        $m = 0;
        $s1 = (string)$arg1;
        $s2 = (string)$arg2;

        $arr = explode('.', $s1);
        isset($arr[1]) ? $m += strlen($arr[1]) : 0;

        $arr = explode('.', $s2);
        isset($arr[1]) ? $m += strlen($arr[1]) : 0;

        return intval(str_replace(".", "", $s1)) * intval(str_replace(".", "", $s2)) / pow(10, $m);
    }

    private function _connect()
    {
        if ($this->connected) {
            return true;
        } else {
            $acc = $this->getAccount($this->account);
            $this->chain_id = $this->getChainID();
            if ($acc && $this->chain_id) {
                $this->account_id = $acc['id'];
                $this->connected = true;
                return true;
            } else {
                $this->connected = false;
                return false;
            }
        }
    }

    private function _query($method, $params)
    {
        return $this->client->query($method, $params);

    }

    /**
     * WARNING: This function have to be used after connected
     * @return mixed
     * @private
     */
    private function _createTransaction()
    {
        $tr = null;
        if (!$this->connected) {
            throw new \Exception("_createTransaction have to be invoked after _connect()");
        }
        if ($this->signProvider) {
            $tr = new TransactionBuilder($this->signProvider, $this->rpc, $this->chain_id);
        } else {
            $tr = new TransactionBuilder(null, $this->rpc, $this->chain_id);
        }

        return $tr;
    }

    /**
     * process transaction
     * @param tr
     * @param broadcast
     * @return mixed
     */
    private function _processTransaction($tr, $broadcast)
    {
        $tr->update_head_block();
        $tr->set_required_fees();
        if (!$this->signProvider) {
            $this->private_key && $tr->add_signer($this->private_key);
        }
        $tr->set_expire_seconds(self::DEFUALT_EXPIRE_SEC);
        if ($broadcast) {
            return $tr->broadcast();
        } else {
            $tr->finalize();
            $tr->sign();
            return $tr->serialize();
        }
    }

    function broadcast($tx)
    {
        return $this->rpc->broadcast($tx);
    }
}