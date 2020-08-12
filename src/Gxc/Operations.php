<?php
/**
 * Created by PhpStorm.
 * User=> Kilmas
 * Date=> 2019/1/31
 * Time=> 13=>31
 */

namespace GXChain\GXClient\Gxc;

class Operations
{

    public static $st_operations = [];

    private static $_operation = null;

    private static $_serializer = [];

    public static function _serializer($name, $_operation)
    {
        if (isset($_serializer[$name])) {
            return self::$_serializer[$name];
        }
        return self::$_serializer[$name] = new Serializer($name, $_operation);
    }

    public static function _type($name, $_type)
    {
        if (isset($_serializer[$name])) {
            return self::$_serializer[$name];
        }
        return self::$_serializer[$name] = $_type;
    }

    public static function serializer($name)
    {
        if (is_object($name)) {
            return $name;
        }
        return self::$_serializer[$name];
    }


    public static function ops()
    {
        return self::$_operation;
    }
}

$predicate = Types::static_variant([
    'account_name_eq_lit_predicate',
    'asset_symbol_eq_lit_predicate',
    'block_id_predicate'
]);

$operation = Types::static_variant([
    'transfer',
    'limit_order_create',
    'limit_order_cancel',
    'call_order_update',
    'fill_order',
    'account_create',
    'account_update',
    'account_whitelist',
    'account_upgrade',
    'account_transfer',
    'asset_create',
    'asset_update',
    'asset_update_bitasset',
    'asset_update_feed_producers',
    'asset_issue',
    'asset_reserve',
    'asset_fund_fee_pool',
    'asset_settle',
    'asset_global_settle',
    'asset_publish_feed',
    'witness_create',
    'witness_update',
    'proposal_create',
    'proposal_update',
    'proposal_delete',
    'withdraw_permission_create',
    'withdraw_permission_update',
    'withdraw_permission_claim',
    'withdraw_permission_delete',
    'committee_member_create',
    'committee_member_update',
    'committee_member_update_global_parameters',
    'vesting_balance_create',
    'vesting_balance_withdraw',
    'worker_create',
    'custom',
    'assert',
    'balance_claim',
    'override_transfer',
    'transfer_to_blind',
    'blind_transfer',
    'transfer_from_blind',
    'asset_settle_cancel',
    'asset_claim_fees',
    'fba_distribute_operation',
    'account_upgrade_merchant',
    'account_upgrade_datasource',
    'stale_data_market_category_create',
    'stale_data_market_category_update',
    'stale_free_data_product_create',
    'stale_free_data_product_update',
    'stale_league_data_product_create',
    'stale_league_data_product_update',
    'stale_league_create',
    'stale_league_update',
    'data_transaction_create',
    'data_transaction_update',
    'data_transaction_pay',
    'account_upgrade_data_transaction_member',
    'data_transaction_datasource_upload',
    'data_transaction_datasource_validate_error',
    'data_market_category_create',
    'data_market_category_update',
    'free_data_product_create',
    'free_data_product_update',
    'league_data_product_create',
    'league_data_product_update',
    'league_create',
    'league_update',
    'datasource_copyright_clear',
    'data_transaction_complain',
    'balance_lock',
    'balance_unlock',
    'proxy_transfer',
    'create_contract',
    'call_contract',
    'update_contract',
    '80' => 'staking_create',
    '81' => 'staking_update',
    '82' => 'staking_claim',
]);

$future_extensions = Types::void();


$public_key = Operations::_serializer("public_key", [
    'key_data' => Types::bytes(33)
]);
// $signature = Operations::_serializer("signature", Types::bytes(65));

$transfer_operation_fee_parameters = Operations::_serializer(
    "transfer_operation_fee_parameters",
    [
        'fee' => Types::uint(64),
        'price_per_kbyte' => Types::uint(32)
    ]
);
$transfer_operation_fee_parameters = Operations::_serializer(
    "transfer_operation_fee_parameters",
    [
        'fee' => Types::uint(64),
        'price_per_kbyte' => Types::uint(32)
    ]);

$limit_order_create_operation_fee_parameters = Operations::_serializer(
    "limit_order_create_operation_fee_parameters",
    [
        'fee' => Types::uint(64)
    ]
);

$limit_order_cancel_operation_fee_parameters = Operations::_serializer(
    "limit_order_cancel_operation_fee_parameters",
    ['fee' => Types::uint(64)]);

$call_order_update_operation_fee_parameters = Operations::_serializer(
    "call_order_update_operation_fee_parameters",
    ['fee' => Types::uint(64)]);

$fill_order_operation_fee_parameters = Operations::_serializer(
    "fill_order_operation_fee_parameters", null);

$account_create_operation_fee_parameters = Operations::_serializer(
    "account_create_operation_fee_parameters",
    [
        'basic_fee' => Types::uint(64),
        'premium_fee' => Types::uint(64),
        'price_per_kbyte' => Types::uint(32)
    ]);

$account_update_operation_fee_parameters = Operations::_serializer(
    "account_update_operation_fee_parameters",
    [
        'fee' => Types::int(64),
        'price_per_kbyte' => Types::uint(32)
    ]);

$account_whitelist_operation_fee_parameters = Operations::_serializer("account_whitelist_operation_fee_parameters",
    ['fee' => Types::int(64)
    ]);

$account_upgrade_operation_fee_parameters = Operations::_serializer("account_upgrade_operation_fee_parameters", ['membership_annual_fee' => Types::uint(64),
    'membership_lifetime_fee' => Types::uint(64)
]);

$account_transfer_operation_fee_parameters = Operations::_serializer("account_transfer_operation_fee_parameters",
    ['fee' => Types::uint(64)
    ]);

$asset_create_operation_fee_parameters = Operations::_serializer("asset_create_operation_fee_parameters", ['symbol3' => Types::uint(64),
    'symbol4' => Types::uint(64),
    'long_symbol' => Types::uint(64),
    'price_per_kbyte' => Types::uint(32)
]);

$asset_update_operation_fee_parameters = Operations::_serializer("asset_update_operation_fee_parameters", ['fee' => Types::uint(64),
    'price_per_kbyte' => Types::uint(32)
]);

$asset_update_bitasset_operation_fee_parameters = Operations::_serializer("asset_update_bitasset_operation_fee_parameters", ['fee' => Types::uint(64)]);

$asset_update_feed_producers_operation_fee_parameters = Operations::_serializer("asset_update_feed_producers_operation_fee_parameters", ['fee' => Types::uint(64)]);

$asset_issue_operation_fee_parameters = Operations::_serializer("asset_issue_operation_fee_parameters", ['fee' => Types::uint(64),
    'price_per_kbyte' => Types::uint(32)
]);

$asset_reserve_operation_fee_parameters = Operations::_serializer("asset_reserve_operation_fee_parameters", ['fee' => Types::uint(64)]);

$asset_fund_fee_pool_operation_fee_parameters = Operations::_serializer("asset_fund_fee_pool_operation_fee_parameters", ['fee' => Types::uint(64)]);

$asset_settle_operation_fee_parameters = Operations::_serializer("asset_settle_operation_fee_parameters", ['fee' => Types::uint(64)]);

$asset_global_settle_operation_fee_parameters = Operations::_serializer("asset_global_settle_operation_fee_parameters", ['fee' => Types::uint(64)]);

$asset_publish_feed_operation_fee_parameters = Operations::_serializer("asset_publish_feed_operation_fee_parameters", ['fee' => Types::uint(64)]);

$witness_create_operation_fee_parameters = Operations::_serializer("witness_create_operation_fee_parameters", ['fee' => Types::uint(64)]);

$witness_update_operation_fee_parameters = Operations::_serializer("witness_update_operation_fee_parameters", ['fee' => Types::int(64)]);

$proposal_create_operation_fee_parameters = Operations::_serializer("proposal_create_operation_fee_parameters", [
    'fee' => Types::uint(64),
    'price_per_kbyte' => Types::uint(32)
]);
$proposal_update_operation_fee_parameters = Operations::_serializer(
    "proposal_update_operation_fee_parameters",
    [
        'fee' => Types::uint(64),
        'price_per_kbyte' => Types::uint(32)
    ]
);

$proposal_delete_operation_fee_parameters = Operations::_serializer("proposal_delete_operation_fee_parameters",
    ['fee' => Types::uint(64)]);

$withdraw_permission_create_operation_fee_parameters = Operations::_serializer(
    "withdraw_permission_create_operation_fee_parameters", ['fee' => Types::uint(64)]);

$withdraw_permission_update_operation_fee_parameters = Operations::_serializer(
    "withdraw_permission_update_operation_fee_parameters", ['fee' => Types::uint(64)]);
$withdraw_permission_claim_operation_fee_parameters = Operations::_serializer(
    "withdraw_permission_claim_operation_fee_parameters", ['fee' => Types::uint(64),
    'price_per_kbyte' => Types::uint(32)
]);


$withdraw_permission_delete_operation_fee_parameters = Operations::_serializer("withdraw_permission_delete_operation_fee_parameters", ['fee' => Types::uint(64)]);

$committee_member_create_operation_fee_parameters = Operations::_serializer("committee_member_create_operation_fee_parameters", ['fee' => Types::uint(64)]);


$committee_member_update_operation_fee_parameters = Operations::_serializer(
    "committee_member_update_operation_fee_parameters", ['fee' => Types::uint(64)]);

$committee_member_update_global_parameters_operation_fee_parameters = Operations::_serializer(
    "committee_member_update_global_parameters_operation_fee_parameters", ['fee' => Types::uint(64)]);

$vesting_balance_create_operation_fee_parameters = Operations::_serializer(
    "vesting_balance_create_operation_fee_parameters", ['fee' => Types::uint(64)]);

$vesting_balance_withdraw_operation_fee_parameters = Operations::_serializer(
    "vesting_balance_withdraw_operation_fee_parameters", ['fee' => Types::uint(64)]);

$worker_create_operation_fee_parameters = Operations::_serializer(
    "worker_create_operation_fee_parameters", ['fee' => Types::uint(64)]);

$custom_operation_fee_parameters = Operations::_serializer("custom_operation_fee_parameters", ['fee' => Types::uint(64),
    'price_per_kbyte' => Types::uint(32)
]);
$assert_operation_fee_parameters = Operations::_serializer(
    "assert_operation_fee_parameters", ['fee' => Types::uint(64)]);
$balance_claim_operation_fee_parameters = Operations::_serializer("balance_claim_operation_fee_parameters", null);

$override_transfer_operation_fee_parameters = Operations::_serializer(
    "override_transfer_operation_fee_parameters", ['fee' => Types::uint(64),
    'price_per_kbyte' => Types::uint(32)
]);

$transfer_to_blind_operation_fee_parameters = Operations::_serializer(
    "transfer_to_blind_operation_fee_parameters", ['fee' => Types::uint(64),
    'price_per_output' => Types::uint(32)
]);

$blind_transfer_operation_fee_parameters = Operations::_serializer(
    "blind_transfer_operation_fee_parameters", ['fee' => Types::uint(64),
    'price_per_output' => Types::uint(32)
]);

$transfer_from_blind_operation_fee_parameters = Operations::_serializer(
    "transfer_from_blind_operation_fee_parameters", ['fee' => Types::uint(64)]);

$asset_settle_cancel_operation_fee_parameters = Operations::_serializer(
    "asset_settle_cancel_operation_fee_parameters", null);

$asset_claim_fees_operation_fee_parameters = Operations::_serializer(
    "asset_claim_fees_operation_fee_parameters", ['fee' => Types::uint(64)]);

$fee_parameters =
    Types::static_variant([
        $transfer_operation_fee_parameters,
        $limit_order_create_operation_fee_parameters,
        $limit_order_cancel_operation_fee_parameters,
        $call_order_update_operation_fee_parameters,
        $fill_order_operation_fee_parameters,
        $account_create_operation_fee_parameters,
        $account_update_operation_fee_parameters,
        $account_whitelist_operation_fee_parameters,
        $account_upgrade_operation_fee_parameters,
        $account_transfer_operation_fee_parameters,
        $asset_create_operation_fee_parameters,
        $asset_update_operation_fee_parameters,
        $asset_update_bitasset_operation_fee_parameters,
        $asset_update_feed_producers_operation_fee_parameters,
        $asset_issue_operation_fee_parameters,
        $asset_reserve_operation_fee_parameters,
        $asset_fund_fee_pool_operation_fee_parameters,
        $asset_settle_operation_fee_parameters,
        $asset_global_settle_operation_fee_parameters,
        $asset_publish_feed_operation_fee_parameters,
        $witness_create_operation_fee_parameters,
        $witness_update_operation_fee_parameters,
        $proposal_create_operation_fee_parameters,
        $proposal_update_operation_fee_parameters,
        $proposal_delete_operation_fee_parameters,
        $withdraw_permission_create_operation_fee_parameters,
        $withdraw_permission_update_operation_fee_parameters,
        $withdraw_permission_claim_operation_fee_parameters,
        $withdraw_permission_delete_operation_fee_parameters,
        $committee_member_create_operation_fee_parameters,
        $committee_member_update_operation_fee_parameters,
        $committee_member_update_global_parameters_operation_fee_parameters,
        $vesting_balance_create_operation_fee_parameters,
        $vesting_balance_withdraw_operation_fee_parameters,
        $worker_create_operation_fee_parameters,
        $custom_operation_fee_parameters,
        $assert_operation_fee_parameters,
        $balance_claim_operation_fee_parameters,
        $override_transfer_operation_fee_parameters,
        $transfer_to_blind_operation_fee_parameters,
        $blind_transfer_operation_fee_parameters,
        $transfer_from_blind_operation_fee_parameters,
        $asset_settle_cancel_operation_fee_parameters,
        $asset_claim_fees_operation_fee_parameters
    ]);

$fee_schedule = Operations::_serializer("fee_schedule", ['parameters' => Types::set($fee_parameters),
    'scale' => Types::uint(32)
]);

$void_result = Operations::_serializer("void_result", null);

$asset = Operations::_serializer("asset", [
    'amount' => Types::int(64),
    'asset_id' => Types::protocol_id_type("asset")
]);


$signed_block = Operations::_serializer("signed_block", ['previous' => Types::bytes(20),
    'timestamp' => Types::time_point_sec(),
    'witness' => Types::protocol_id_type("witness"),
    'transaction_merkle_root' => Types::bytes(20),
    'extensions' => Types::set($future_extensions),
    'witness_signature' => Types::bytes(65),
    'transactions' => Types::array('processed_transaction')
]);

$block_header = Operations::_serializer("block_header", ['previous' => Types::bytes(20),
    'timestamp' => Types::time_point_sec(),
    'witness' => Types::protocol_id_type("witness"),
    'transaction_merkle_root' => Types::bytes(20),
    'extensions' => Types::set($future_extensions)
]);

$signed_block_header = Operations::_serializer("signed_block_header", ['previous' => Types::bytes(20),
    'timestamp' => Types::time_point_sec(),
    'witness' => Types::protocol_id_type("witness"),
    'transaction_merkle_root' => Types::bytes(20),
    'extensions' => Types::set($future_extensions),
    'witness_signature' => Types::bytes(65)
]);

$memo_data = Operations::_serializer("memo_data", [
    'from' => Types::public_key(),
    'to' => Types::public_key(),
    'nonce' => Types::uint(64),
    'message' => Types::bytes()
]);

$transfer = Operations::_serializer("transfer",
    [
        'fee' => $asset,
        'from' => Types::protocol_id_type("account"),
        'to' => Types::protocol_id_type("account"),
        'amount' => $asset,
        'memo' => Types::optional($memo_data),
        'extensions' => Types::set($future_extensions)
    ]);

$limit_order_create = Operations::_serializer("limit_order_create",
    [
        'fee' => $asset,
        'seller' => Types::protocol_id_type("account"),
        'amount_to_sell' => $asset,
        'min_to_receive' => $asset,
        'expiration' => Types::time_point_sec(),
        'fill_or_kill' => Types::bool(),
        'extensions' => Types::set($future_extensions)
    ]);

$limit_order_cancel = Operations::_serializer("limit_order_cancel", ['fee' => $asset,
    'fee_paying_account' => Types::protocol_id_type("account"),
    'order' => Types::protocol_id_type("limit_order"),
    'extensions' => Types::set($future_extensions)
]);

$call_order_update = Operations::_serializer("call_order_update", ['fee' => $asset,
    'funding_account' => Types::protocol_id_type("account"),
    'delta_collateral' => $asset,
    'delta_debt' => $asset,
    'extensions' => Types::set($future_extensions)
]);

$fill_order = Operations::_serializer("fill_order", ['fee' => $asset,
    'order_id' => Types::object_id_type(),
    'account_id' => Types::protocol_id_type("account"),
    'pays' => $asset,
    'receives' => "asset"
]);

$authority = Operations::_serializer("authority", [
    'weight_threshold' => Types::uint(32),
    'account_auths' => Types::map((Types::protocol_id_type("account")), (Types::uint(16))),
    'key_auths' => Types::map((Types::public_key()), (Types::uint(16))),
    'address_auths' => Types::map((Types::address()), (Types::uint(16)))
]);

$account_options = Operations::_serializer("account_options", ['memo_key' => Types::public_key(),
    'voting_account' => Types::protocol_id_type("account"),
    'num_witness' => Types::uint(16),
    'num_committee' => Types::uint(16),
    'votes' => Types::set(Types::vote_id()),
    'extensions' => Types::set($future_extensions)
]);

$account_create = Operations::_serializer("account_create", ['fee' => $asset,
    'registrar' => Types::protocol_id_type("account"),
    'referrer' => Types::protocol_id_type("account"),
    'referrer_percent' => Types::uint(16),
    'name' => Types::string(),
    'owner' => $authority,
    'active' => $authority,
    'options' => $account_options,
    'extensions' => Types::set($future_extensions)
]);

$account_update = Operations::_serializer("account_update", ['fee' => $asset,
    'account' => Types::protocol_id_type("account"),
    'owner' => Types::optional($authority),
    'active' => Types::optional($authority),
    'new_options' => Types::optional($account_options),
    'extensions' => Types::set($future_extensions)
]);

$staking_create = Operations::_serializer("staking_create", ['fee' => $asset,
    'owner'                                                            => Types::protocol_id_type("account"),
    'trust_node'                                                       => Types::protocol_id_type("witness"),
    'amount'                                                           => $asset,
    'program_id'                                                       => Types::string(),
    'weight'                                                           => Types::uint(32),
    'staking_days'                                                     => Types::uint(32),
    'extensions'                                                       => Types::set($future_extensions),
]);

$staking_update = Operations::_serializer("staking_update", ['fee' => $asset,
    'owner'                                                            => Types::protocol_id_type("account"),
    'trust_node'                                                       => Types::protocol_id_type("witness"),
    'staking_id'                                                       => Types::protocol_id_type("staking"),
    'extensions'                                                       => Types::set($future_extensions),
]);

$staking_claim = Operations::_serializer("staking_claim", ['fee' => $asset,
    'owner'                                                          => Types::protocol_id_type("account"),
    'staking_id'                                                     => Types::protocol_id_type("staking"),
    'extensions'                                                     => Types::set($future_extensions),
]);


$account_whitelist = Operations::_serializer("account_whitelist", ['fee' => $asset,
    'authorizing_account' => Types::protocol_id_type("account"),
    'account_to_list' => Types::protocol_id_type("account"),
    'new_listing' => Types::uint(8),
    'extensions' => Types::set($future_extensions)
]);

$account_upgrade = Operations::_serializer("account_upgrade", ['fee' => $asset,
    'account_to_upgrade' => Types::protocol_id_type("account"),
    'upgrade_to_lifetime_member' => Types::bool(),
    'extensions' => Types::set($future_extensions)
]);

$fba_distribute_operation = Operations::_serializer("fba_distribute_operation", [
    'fee' => $asset,
    'extensions' => Types::set($future_extensions)
]);

$account_transfer = Operations::_serializer("account_transfer",
    [
        'fee' => $asset,
        'account_id' => Types::protocol_id_type("account"),
        'new_owner' => Types::protocol_id_type("account"),
        'extensions' => Types::set($future_extensions)
    ]);

$price = Operations::_serializer("price", ['base' => $asset,
    'quote' => "asset"
]);

$asset_options = Operations::_serializer("asset_options", [
    'max_supply' => Types::int(64),
    'market_fee_percent' => Types::uint(16),
    'max_market_fee' => Types::int(64),
    'issuer_permissions' => Types::uint(16),
    'flags' => Types::uint(16),
    'core_exchange_rate' => "price",
    'whitelist_authorities' => Types::set(Types::protocol_id_type("account")),
    'blacklist_authorities' => Types::set(Types::protocol_id_type("account")),
    'whitelist_markets' => Types::set(Types::protocol_id_type("asset")),
    'blacklist_markets' => Types::set(Types::protocol_id_type("asset")),
    'description' => Types::string(),
    'extensions' => Types::set($future_extensions)
]);

$bitasset_options = Operations::_serializer("bitasset_options", [
    'feed_lifetime_sec' => Types::uint(32),
    'minimum_feeds' => Types::uint(8),
    'force_settlement_delay_sec' => Types::uint(32),
    'force_settlement_offset_percent' => Types::uint(16),
    'maximum_force_settlement_volume' => Types::uint(16),
    'short_backing_asset' => Types::protocol_id_type("asset"),
    'extensions' => Types::set($future_extensions)
]);

$asset_create = Operations::_serializer("asset_create", ['fee' => $asset,
    'issuer' => Types::protocol_id_type("account"),
    'symbol' => Types::string(),
    'precision' => Types::uint(8),
    'common_options' => $asset_options,
    'bitasset_opts' => Types::optional($bitasset_options),
    'is_prediction_market' => Types::bool(),
    'extensions' => Types::set($future_extensions)
]);

$asset_update = Operations::_serializer("asset_update", ['fee' => $asset,
    'issuer' => Types::protocol_id_type("account"),
    'asset_to_update' => Types::protocol_id_type("asset"),
    'new_issuer' => Types::optional(Types::protocol_id_type("account")),
    'new_options' => "asset_options",
    'extensions' => Types::set($future_extensions)
]);

$asset_update_bitasset = Operations::_serializer("asset_update_bitasset", ['fee' => $asset,
    'issuer' => Types::protocol_id_type("account"),
    'asset_to_update' => Types::protocol_id_type("asset"),
    'new_options' => $bitasset_options,
    'extensions' => Types::set($future_extensions)
]);

$asset_update_feed_producers = Operations::_serializer("asset_update_feed_producers", ['fee' => $asset,
    'issuer' => Types::protocol_id_type("account"),
    'asset_to_update' => Types::protocol_id_type("asset"),
    'new_feed_producers' => Types::set(Types::protocol_id_type("account")),
    'extensions' => Types::set($future_extensions)
]);

$asset_issue = Operations::_serializer("asset_issue", [
    'fee' => $asset,
    'issuer' => Types::protocol_id_type("account"),
    'asset_to_issue' => $asset,
    'issue_to_account' => Types::protocol_id_type("account"),
    'memo' => Types::optional($memo_data),
    'extensions' => Types::set($future_extensions)
]);

$asset_reserve = Operations::_serializer("asset_reserve", [
    'fee' => $asset,
    'payer' => Types::protocol_id_type("account"),
    'amount_to_reserve' => $asset,
    'extensions' => Types::set($future_extensions)
]);

$asset_fund_fee_pool = Operations::_serializer("asset_fund_fee_pool", ['fee' => $asset,
    'from_account' => Types::protocol_id_type("account"),
    'asset_id' => Types::protocol_id_type("asset"),
    'amount' => Types::int(64),
    'extensions' => Types::set($future_extensions)
]);

$asset_settle = Operations::_serializer("asset_settle", ['fee' => $asset,
    'account' => Types::protocol_id_type("account"),
    'amount' => $asset,
    'extensions' => Types::set($future_extensions)
]);

$asset_global_settle = Operations::_serializer("asset_global_settle", ['fee' => $asset,
    'issuer' => Types::protocol_id_type("account"),
    'asset_to_settle' => Types::protocol_id_type("asset"),
    'settle_price' => "price",
    'extensions' => Types::set($future_extensions)
]);

$price_feed = Operations::_serializer("price_feed", ['settlement_price' => "price",
    'maintenance_collateral_ratio' => Types::uint(16),
    'maximum_short_squeeze_ratio' => Types::uint(16),
    'core_exchange_rate' => "price"
]);

$asset_publish_feed = Operations::_serializer("asset_publish_feed", ['fee' => $asset,
    'publisher' => Types::protocol_id_type("account"),
    'asset_id' => Types::protocol_id_type("asset"),
    'feed' => "price_feed",
    'extensions' => Types::set($future_extensions)
]);

$witness_create = Operations::_serializer("witness_create", ['fee' => $asset,
    'witness_account' => Types::protocol_id_type("account"),
    'url' => Types::string(),
    'block_signing_key' => Types::public_key()
]);

$witness_update = Operations::_serializer("witness_update", ['fee' => $asset,
    'witness' => Types::protocol_id_type("witness"),
    'witness_account' => Types::protocol_id_type("account"),
    'new_url' => Types::optional(Types::string()),
    'new_signing_key' => Types::optional(Types::public_key())
]);


$proposal_update = Operations::_serializer("proposal_update", ['fee' => $asset,
    'fee_paying_account' => Types::protocol_id_type("account"),
    'proposal' => Types::protocol_id_type("proposal"),
    'active_approvals_to_add' => Types::set(Types::protocol_id_type("account")),
    'active_approvals_to_remove' => Types::set(Types::protocol_id_type("account")),
    'owner_approvals_to_add' => Types::set(Types::protocol_id_type("account")),
    'owner_approvals_to_remove' => Types::set(Types::protocol_id_type("account")),
    'key_approvals_to_add' => Types::set(Types::public_key()),
    'key_approvals_to_remove' => Types::set(Types::public_key()),
    'extensions' => Types::set($future_extensions)
]);

$proposal_delete = Operations::_serializer("proposal_delete", ['fee' => $asset,
    'fee_paying_account' => Types::protocol_id_type("account"),
    'using_owner_authority' => Types::bool(),
    'proposal' => Types::protocol_id_type("proposal"),
    'extensions' => Types::set($future_extensions)
]);

$withdraw_permission_create = Operations::_serializer("withdraw_permission_create", ['fee' => $asset,
    'withdraw_from_account' => Types::protocol_id_type("account"),
    'authorized_account' => Types::protocol_id_type("account"),
    'withdrawal_limit' => $asset,
    'withdrawal_period_sec' => Types::uint(32),
    'periods_until_expiration' => Types::uint(32),
    'period_start_time' => Types::time_point_sec()
]);

$withdraw_permission_update = Operations::_serializer("withdraw_permission_update", ['fee' => $asset,
    'withdraw_from_account' => Types::protocol_id_type("account"),
    'authorized_account' => Types::protocol_id_type("account"),
    'permission_to_update' => Types::protocol_id_type("withdraw_permission"),
    'withdrawal_limit' => $asset,
    'withdrawal_period_sec' => Types::uint(32),
    'period_start_time' => Types::time_point_sec(),
    'periods_until_expiration' => Types::uint(32)
]);

$withdraw_permission_claim = Operations::_serializer("withdraw_permission_claim",
    [
        'fee' => $asset,
        'withdraw_permission' => Types::protocol_id_type("withdraw_permission"),
        'withdraw_from_account' => Types::protocol_id_type("account"),
        'withdraw_to_account' => Types::protocol_id_type("account"),
        'amount_to_withdraw' => $asset,
        'memo' => Types::optional($memo_data)
    ]);

$withdraw_permission_delete = Operations::_serializer("withdraw_permission_delete", ['fee' => $asset,
    'withdraw_from_account' => Types::protocol_id_type("account"),
    'authorized_account' => Types::protocol_id_type("account"),
    'withdrawal_permission' => Types::protocol_id_type("withdraw_permission")
]);

$committee_member_create = Operations::_serializer("committee_member_create", ['fee' => $asset,
    'committee_member_account' => Types::protocol_id_type("account"),
    'url' => Types::string()
]);

$committee_member_update = Operations::_serializer("committee_member_update", ['fee' => $asset,
    'committee_member' => Types::protocol_id_type("committee_member"),
    'committee_member_account' => Types::protocol_id_type("account"),
    'new_url' => Types::optional(Types::string())
]);

$chain_parameters = Operations::_serializer("chain_parameters", ['current_fees' => $fee_schedule,
    'block_interval' => Types::uint(8),
    'maintenance_interval' => Types::uint(32),
    'maintenance_skip_slots' => Types::uint(8),
    'committee_proposal_review_period' => Types::uint(32),
    'maximum_transaction_size' => Types::uint(32),
    'maximum_block_size' => Types::uint(32),
    'maximum_time_until_expiration' => Types::uint(32),
    'maximum_proposal_lifetime' => Types::uint(32),
    'maximum_asset_whitelist_authorities' => Types::uint(8),
    'maximum_asset_feed_publishers' => Types::uint(8),
    'maximum_witness_count' => Types::uint(16),
    'maximum_committee_count' => Types::uint(16),
    'maximum_authority_membership' => Types::uint(16),
    'reserve_percent_of_fee' => Types::uint(16),
    'network_percent_of_fee' => Types::uint(16),
    'lifetime_referrer_percent_of_fee' => Types::uint(16),
    'cashback_vesting_period_seconds' => Types::uint(32),
    'cashback_vesting_threshold' => Types::int(64),
    'count_non_member_votes' => Types::bool(),
    'allow_non_member_whitelists' => Types::bool(),
    'witness_pay_per_block' => Types::int(64),
    'worker_budget_per_day' => Types::int(64),
    'max_predicate_opcode' => Types::uint(16),
    'fee_liquidation_threshold' => Types::int(64),
    'accounts_per_fee_scale' => Types::uint(16),
    'account_fee_scale_bitshifts' => Types::uint(8),
    'max_authority_depth' => Types::uint(8),
    'extensions' => Types::set($future_extensions)
]);

$committee_member_update_global_parameters = Operations::_serializer("committee_member_update_global_parameters",
    [
        'fee' => $asset,
        'new_parameters' => $chain_parameters
    ]);

$linear_vesting_policy_initializer = Operations::_serializer("linear_vesting_policy_initializer", [
    'begin_timestamp' => Types::time_point_sec(),
    'vesting_cliff_seconds' => Types::uint(32),
    'vesting_duration_seconds' => Types::uint(32)
]);

$cdd_vesting_policy_initializer = Operations::_serializer("cdd_vesting_policy_initializer", [
    'start_claim' => Types::time_point_sec(),
    'vesting_seconds' => Types::uint(32)
]);

$vesting_balance_create = Operations::_serializer("vesting_balance_create", [
    'fee' => $asset,
    'creator' => Types::protocol_id_type("account"),
    'owner' => Types::protocol_id_type("account"),
    'amount' => $asset,
    'policy' => Types::static_variant([
        $linear_vesting_policy_initializer,
        $cdd_vesting_policy_initializer
    ])
]);
$vesting_balance_withdraw = Operations::_serializer("vesting_balance_withdraw", ['fee' => $asset,
    'vesting_balance' => Types::protocol_id_type("vesting_balance"),
    'owner' => Types::protocol_id_type("account"),
    'amount' => $asset
]);
$refund_worker_initializer = Operations::_serializer("refund_worker_initializer", null);


$vesting_balance_worker_initializer = Operations::_serializer("vesting_balance_worker_initializer", [
    'pay_vesting_period_days' => Types::uint(16)]);

$burn_worker_initializer = Operations::_serializer("burn_worker_initializer", null);
$worker_create = Operations::_serializer("worker_create",
    [
        'fee' => $asset,
        'owner' => Types::protocol_id_type("account"),
        'work_begin_date' => Types::time_point_sec(),
        'work_end_date' => Types::time_point_sec(),
        'daily_pay' => Types::int(64),
        'name' => Types::string(),
        'url' => Types::string(),
        'initializer' => Types::static_variant([
            $refund_worker_initializer,
            $vesting_balance_worker_initializer,
            $burn_worker_initializer
        ])
    ]);

$custom = Operations::_serializer("custom",
    [
        'fee' => $asset,
        'payer' => Types::protocol_id_type("account"),
        'required_auths' => Types::set(Types::protocol_id_type("account")),
        'id' => Types::uint(16),
        'data' => Types::bytes()
    ]);

$account_name_eq_lit_predicate = Operations::_serializer("account_name_eq_lit_predicate", [
    'account_id' => Types::protocol_id_type("account"),
    'name' => Types::string()
]);

$asset_symbol_eq_lit_predicate = Operations::_serializer("asset_symbol_eq_lit_predicate", [
    'asset_id' => Types::protocol_id_type("asset"),
    'symbol' => Types::string()
]);

$block_id_predicate = Operations::_serializer("block_id_predicate",
    [
        'id' => Types::bytes(20)
    ]);

$assert = Operations::_serializer("assert", [
    'fee' => $asset,
    'fee_paying_account' => Types::protocol_id_type("account"),
    'predicates' => Types::array($predicate),
    'required_auths' => Types::set(Types::protocol_id_type("account")),
    'extensions' => Types::set($future_extensions)
]);

$balance_claim = Operations::_serializer("balance_claim", ['fee' => $asset,
    'deposit_to_account' => Types::protocol_id_type("account"),
    'balance_to_claim' => Types::protocol_id_type("balance"),
    'balance_owner_key' => Types::public_key(),
    'total_claimed' => $asset
]);

$override_transfer = Operations::_serializer("override_transfer", ['fee' => $asset,
    'issuer' => Types::protocol_id_type("account"),
    'from' => Types::protocol_id_type("account"),
    'to' => Types::protocol_id_type("account"),
    'amount' => $asset,
    'memo' => Types::optional($memo_data),
    'extensions' => Types::set($future_extensions)
]);

$stealth_confirmation = Operations::_serializer("stealth_confirmation", [
    'one_time_key' => Types::public_key(),
    'to' => Types::optional($public_key),
    'encrypted_memo' => Types::bytes()
]);

$blind_output = Operations::_serializer("blind_output", ['commitment' => Types::bytes(33),
    'range_proof' => Types::bytes(),
    'owner' => $authority,
    'stealth_memo' => Types::optional($stealth_confirmation)
]);

$transfer_to_blind = Operations::_serializer("transfer_to_blind", ['fee' => $asset,
    'amount' => $asset,
    'from' => Types::protocol_id_type("account"),
    'blinding_factor' => Types::bytes(32),
    'outputs' => Types::array($blind_output)
]);

$blind_input = Operations::_serializer("blind_input", ['commitment' => Types::bytes(33),
    'owner' => "authority"
]);

$blind_transfer = Operations::_serializer("blind_transfer", ['fee' => $asset,
    'inputs' => Types::array($blind_input),
    'outputs' => Types::array($blind_output)
]);

$transfer_from_blind = Operations::_serializer("transfer_from_blind", ['fee' => $asset,
    'amount' => $asset,
    'to' => Types::protocol_id_type("account"),
    'blinding_factor' => Types::bytes(32),
    'inputs' => Types::array($blind_input)
]);

$asset_settle_cancel = Operations::_serializer("asset_settle_cancel", ['fee' => $asset,
    'settlement' => Types::protocol_id_type("force_settlement"),
    'account' => Types::protocol_id_type("account"),
    'amount' => $asset,
    'extensions' => Types::set($future_extensions)
]);

$asset_claim_fees = Operations::_serializer("asset_claim_fees", ['fee' => $asset,
    'issuer' => Types::protocol_id_type("account"),
    'amount_to_claim' => $asset,
    'extensions' => Types::set($future_extensions)
]);

$type_def = Operations::_serializer("type_def", ['new_type_name' => Types::string(),
    'type' => Types::string()
]);

$field_def = Operations::_serializer("field_def", ['name' => Types::string(),
    'type' => Types::string()
]);

$struct_def = Operations::_serializer("struct_def", ['name' => Types::string(),
    'base' => Types::string(),
    'fields' => Types::set($field_def)
]);

$action_def = Operations::_serializer("action_def", ['name' => Types::name_type(),
    'type' => Types::string(),
    'payable' => Types::bool()
]);

$table_def = Operations::_serializer("table_def", ['name' => Types::name_type(),
    'index_type' => Types::string(),
    'key_names' => Types::set(Types::string()),
    'key_types' => Types::set(Types::string()),
    'type' => Types::string()
]);

$clause_pair = Operations::_serializer('clause_pair', [
    'id' => Types::string(),
    'body' => Types::string()
]);

$error_message = Operations::_serializer("error_message", ['error_code' => Types::uint(64),
    'error_msg' => Types::string()
]);

$abi_def = Operations::_serializer("abi_def", ['version' => Types::string(),
    'types' => Types::set($type_def),
    'structs' => Types::set($struct_def),
    'actions' => Types::set($action_def),
    'tables' => Types::set($table_def),
    'error_messages' => Types::set($error_message),
    'abi_extensions' => Types::set($future_extensions)
]);

$contract_asset = Operations::_serializer('contract_asset', [
    'amount' => Types::int(64),
    'asset_id' => Types::uint(64)
]);

$create_contract = Operations::_serializer("create_contract", [
    'fee' => $asset,
    'name' => Types::string(),
    'account' => Types::protocol_id_type("account"),
    'vm_type' => Types::string(),
    'vm_version' => Types::string(),
    'code' => Types::bytes(),
    'abi' => $abi_def,
    'extensions' => Types::set($future_extensions)
]);

$call_contract = Operations::_serializer("call_contract", [
    'fee' => $asset,
    'account' => Types::protocol_id_type("account"),
    'contract_id' => Types::protocol_id_type("account"),
    'amount' => Types::optional($asset),
    'method_name' => Types::name_type(),
    'data' => Types::bytes(),
    'extensions' => Types::set($future_extensions)
]);

$update_contract = Operations::_serializer("update_contract",
    ['fee' => $asset,
        'owner' => Types::protocol_id_type("account"),
        'new_owner' => Types::optional(Types::protocol_id_type("account")),
        'contract' => Types::protocol_id_type("account"),
        'code' => Types::bytes(),
        'abi' => $abi_def,
        'extensions' => Types::set($future_extensions)
    ]);
//// ---------------------
////  data products & leagues, not implemented yet, since now
//// ---------------------

$account_upgrade_merchant = Operations::_serializer("account_upgrade_merchant", []);

$account_upgrade_datasource = Operations::_serializer("account_upgrade_datasource", []);

$account_upgrade_data_transaction_member = Operations::_serializer("account_upgrade_data_transaction_member", []);

$stale_data_market_category_create = Operations::_serializer("stale_data_market_category_create", []);

$stale_data_market_category_update = Operations::_serializer('stale_ata_market_category_update', []);

$stale_ata_market_category_update = Operations::_serializer("stale_ata_market_category_update", []);

$stale_free_data_product_create = Operations::_serializer("stale_free_data_product_create", []);

$stale_free_data_product_update = Operations::_serializer("stale_free_data_product_update", []);

$free_data_product_update = Operations::_serializer("free_data_product_update", []);

$stale_league_data_product_create = Operations::_serializer("stale_league_data_product_create", []);

$stale_league_data_product_update = Operations::_serializer("stale_league_data_product_update", []);

$stale_league_create = Operations::_serializer("stale_league_create", []);

$stale_league_update = Operations::_serializer("stale_league_update", []);

$data_market_category_create = Operations::_serializer("data_market_category_create", []);

$data_market_category_update = Operations::_serializer("data_market_category_update", []);

$free_data_product_create = Operations::_serializer("free_data_product_create", []);

$free_data_product_update = Operations::_serializer("free_data_product_update", []);

$league_data_product_create = Operations::_serializer("league_data_product_create", []);

$league_data_product_update = Operations::_serializer("league_data_product_update", []);

$league_create = Operations::_serializer("league_create", []);

$league_update = Operations::_serializer("league_update", []);

$datasource_copyright_clear = Operations::_serializer("datasource_copyright_clear", []);

$data_transaction_complain = Operations::_serializer("data_transaction_complain", []);

$balance_lock = Operations::_serializer("balance_lock", ['fee' => $asset,
    'account' => Types::protocol_id_type("account"),
    'create_date_time' => Types::time_point_sec(),
    'program_id' => Types::string(),
    'amount' => $asset,
    'lock_days' => Types::uint(32),
    'interest_rate' => Types::uint(32),
    'memo' => Types::string(),
    'extensions' => Types::set($future_extensions)
]);

$balance_unlock = Operations::_serializer('balance_unlock', [
    'fee' => $asset,
    'account' => Types::protocol_id_type("account"),
    'lock_id' => Types::protocol_id_type("lock_balance"),
    'extensions' => Types::set($future_extensions)
]);

//
//// ---------------------
////  data trasaction
//// ---------------------
//
$data_transaction_create = Operations::_serializer("data_transaction_create", ['request_id' => Types::string(),
    'product_id' => Types::object_id_type(),
    'version' => Types::string(),
    'params' => Types::string(),
    'fee' => $asset,
    'requester' => Types::protocol_id_type('account'),
    'create_date_time' => Types::time_point_sec(),
    'league_id' => Types::optional(Types::protocol_id_type('league')),
    'extensions' => Types::set($future_extensions)
]);
$data_transaction_update = Operations::_serializer("data_transaction_update", ['request_id' => Types::string(),
    'new_status' => Types::uint(8),
    'fee' => $asset,
    'new_requester' => Types::protocol_id_type('account'),
    'memo' => Types::string(),
    'extensions' => Types::set($future_extensions)
]);

$data_transaction_pay = Operations::_serializer("data_transaction_pay", ['fee' => $asset,
    'from' => Types::protocol_id_type('account'),
    'to' => Types::protocol_id_type('account'),
    'amount' => $asset,
    'request_id' => Types::string(),
    'extensions' => Types::set($future_extensions)
]);

$data_transaction_datasource_upload = Operations::_serializer("data_transaction_datasource_upload",
    [
        'request_id' => Types::string(),
        'requester' => Types::protocol_id_type('account'),
        'datasource' => Types::protocol_id_type('account'),
        'fee' => $asset,
        'extensions' => Types::set($future_extensions)
    ]);

$data_transaction_datasource_validate_error = Operations::_serializer("data_transaction_datasource_validate_error",
    [
        'request_id' => Types::string(),
        'datasource' => Types::protocol_id_type('account'),
        'fee' => $asset,
        'extensions' => Types::set($future_extensions)
    ]);

$proxy_transfer_params = Operations::_serializer("proxy_transfer_params", [
    'from' => Types::protocol_id_type('account'), // 从该帐户转帐，转帐数量为amount
    'to' => Types::protocol_id_type('account'), // 转帐至该帐户
    'proxy_account' => Types::protocol_id_type('account'), // 代理记帐方
    'amount' => $asset,
    'percentage' => Types::uint(16), // amount的百分比，转至proxy_account
    'memo' => Types::string(), // string
    'expiration' => Types::time_point_sec() // 授权过期时间， 也是signatures的有效期，expiration < now + maximum_time_until_expiration
]);

$signed_proxy_transfer_params = Operations::_serializer("signed_proxy_transfer_params",
    [
        'from' => Types::protocol_id_type('account'), // 从该帐户转帐，转帐数量为amount
        'to' => Types::protocol_id_type('account'), // 转帐至该帐户
        "proxy_account" => Types::protocol_id_type('account'), // 代理记帐方
        'amount' => $asset,
        'percentage' => Types::uint(16), // amount的百分比，转至proxy_account
        'memo' => Types::string(), // string
        'expiration' => Types::time_point_sec(), // 授权过期时间， 也是signatures的有效期，expiration < now + maximum_time_until_expiration
        'signatures' => Types::array(Types::bytes(65))
    ]);

$proxy_transfer = Operations::_serializer("proxy_transfer", ['proxy_memo' => Types::string(),
    'fee' => $asset,
    'request_params' => "signed_proxy_transfer_params",
    'extensions' => Types::set($future_extensions)
]);

$op_wrapper = Operations::_serializer("op_wrapper", ['op' => $operation]);

$proposal_create = Operations::_serializer("proposal_create", ['fee' => $asset,
    'fee_paying_account' => Types::protocol_id_type("account"),
    'expiration_time' => Types::time_point_sec(),
    'proposed_ops' => Types::array($op_wrapper),
    'review_period_seconds' => Types::optional(Types::uint(32)),
    'extensions' => Types::set($future_extensions)
]);

Operations::_type("operation", $operation);

$processed_transaction = Operations::_serializer("processed_transaction", [
    'ref_block_num' => Types::uint(16),
    'ref_block_prefix' => Types::uint(32),
    'expiration' => Types::time_point_sec(),
    'operations' => Types::array($operation),
    'extensions' => Types::set($future_extensions),
    'signatures' => Types::array(Types::bytes(65)),
    'operation_results' => Types::array(Types::static_variant([
        $void_result,
        Types::object_id_type(),
        $asset
    ]))
]);

$transaction = Operations::_serializer("transaction",
    [
        'ref_block_num' => Types::uint(16),
        'ref_block_prefix' => Types::uint(32),
        'expiration' => Types::time_point_sec(),
        'operations' => Types::array($operation),
        'extensions' => Types::set($future_extensions)
    ]
);

$signed_transaction = Operations::_serializer("signed_transaction",
    [
        'ref_block_num' => Types::uint(16),
        'ref_block_prefix' => Types::uint(32),
        'expiration' => Types::time_point_sec(),
        'operations' => Types::array($operation),
        'extensions' => Types::set($future_extensions),
        'signatures' => Types::array(Types::bytes(65))
    ]);

$stealth_memo_data = Operations::_serializer("stealth_memo_data", [
    'from' => Types::optional(Types::public_key()),
    'amount' => $asset,
    'blinding_factor' => Types::bytes(32),
    'commitment' => Types::bytes(33),
    'check' => Types::uint(32)
]);


$signature = Operations::_type("signature", Types::bytes(65));
