<?php
/**
 * Created by PhpStorm.
 * User: kilmas
 * Date: 2019/1/26
 * Time: 18:43
 */

namespace GXChain\GXClient;

use GXChain\GXClient\Gxc\Operations as ops;
use GXChain\GXClient\Gxc\Chain\ChainTypes;
use GXChain\GXClient\Ecc\Signature;

class TransactionBuilder
{

    private $expire_in_secs = 15;
    private $expire_in_secs_proposal = 24 * 60 * 60;
    private $review_in_secs_committee = 24 * 60 * 60;
    private $head_block_time_string;
    private $committee_min_review;

    protected $signProvider;

    protected $operations;

    protected $chain_id;

    protected $ref_block_num;

    protected $ref_block_prefix;

    protected $expiration;

    protected $signatures;

    protected $signer_private_keys;

    protected $extensions = [];

    protected $rpc;

    protected $tr_buffer;

    protected $signed;

    public function __get($name)
    {
        return $this->$name;
    }

    public function __construct($signProvider = null, $rpc, $chain_id)
    {
        if ($signProvider) {
            // a function,first param is transaction instance,second is chain_id, must return array buffer like [buffer,buffer]
            $this->signProvider = $signProvider;
        }
        if ($rpc) {
            $this->rpc = $rpc;
        }
        if ($chain_id) {
            $this->chain_id = $chain_id;
        }
        $this->ref_block_num = 0;
        $this->ref_block_prefix = 0;
        $this->expiration = 0;
        $this->operations = [];
        $this->signatures = [];
        $this->signer_private_keys = [];
    }

    /**
     * @param {string} name - like "transfer"
     * @param {object} operation - JSON matchching the operation's format
     */
    function add_type_operation($name, $operation)
    {
        $this->add_operation($this->get_type_operation($name, $operation));
    }

    /** Typically this is called automatically just prior to signing.  Once finalized this transaction can not be changed. */
    function finalize()
    {

        if ($this->tr_buffer) {
            throwException("already finalized");
        }
        $r = $this->rpc->query("get_objects", [["2.1.0"]]);
        $this->head_block_time_string = $r[0]['time'];
        if ($this->expiration === 0) {
            $this->expiration = $this->base_expiration_sec() + $this->expire_in_secs;
        }
        $this->ref_block_num = $r[0]['head_block_number'] & 0xFFFF;
        $this->ref_block_prefix = unpack("V", hex2bin($r[0]['head_block_id']), 4)[1];

        $iterable = $this->operations;
        for ($i = 0; $i < count($iterable); $i++) {
            $op = $iterable[$i];
            if (isset($op[1]["finalize"])) {
                ($op[1]["finalize"])();
                // $op[1] . finalize();
            }
        }
        $this->tr_buffer = ops::serializer('transaction')->toBuffer($this);
    }

    /**
     *
     * @return mixed hex transaction ID
     * @throws
     */

    function id()
    {
        if (!$this->tr_buffer) {
            throwException("not finalized");
        }
        return substr(hash('sha256', $this->tr_buffer), 0, 40);
    }

    /**
     *
     * Typically one will use {@link this.add_type_operation} instead.
     * @arg {array} operation - [operation_id, operation]
     * @param array $operation
     */
    function add_operation($operation)
    {
        if ($this->tr_buffer) {
            throwException("already finalized");
        }
        assert($operation, "operation");
        if (!is_array($operation)) {
            throwException("Expecting array [operation_id, operation]");
        }
        array_push($this->operations, $operation);
    }

    function get_type_operation($name, $operation)
    {
        if ($this->tr_buffer) {
            throwException("already finalized");
        }
        assert(isset($name), "name");
        assert(isset($operation), "operation");
        // assert(ops::serializer($name), "Unknown operation {$name}");
        $_type = ops::serializer($name);
        $operation_id = ChainTypes::$operations[$_type->operation_name];
        if ($operation_id === null) {
            throwException("unknown operation: {$_type->operation_name}");
        }
        if (empty($operation['fee'])) {
            $operation['fee'] = [
                'amount' => 0,
                'asset_id' => 1
            ];
        }
        if ($name === "proposal_create") {
            /*
            * Proposals involving the committee account require a review
            * period to be set, look for them here
            */
            $requiresReview = false;
            $extraReview = 0;

            foreach ($operation['proposed_ops'] as $op) {
                $COMMITTE_ACCOUNT = 0;
                $key = null;

                switch ($op['op'][0]) {
                    case 0: // transfer
                        $key = "from";
                        break;

                    case 6: //account_update
                    case 17: // asset_settle
                        $key = "account";
                        break;

                    case 10: // asset_create
                    case 11: // asset_update
                    case 12: // asset_update_bitasset
                    case 13: // asset_update_feed_producers
                    case 14: // asset_issue
                    case 18: // asset_global_settle
                    case 43: // asset_claim_fees
                        $key = "issuer";
                        break;

                    case 15: // asset_reserve
                        $key = "payer";
                        break;

                    case 16: // asset_fund_fee_pool
                        $key = "from_account";
                        break;

                    case 22: // proposal_create
                    case 23: // proposal_update
                    case 24: // proposal_delete
                        $key = "fee_paying_account";
                        break;

                    case 31: // committee_member_update_global_parameters
                        $requiresReview = true;
                        $extraReview = 60 * 60 * 24 * 13; // Make the review period 2 weeks total
                        break;
                }
                if (isset($op['op'][1][$key]) && $op['op'][1][$key] === $COMMITTE_ACCOUNT) {
                    $requiresReview = true;
                }
            };
            isset($operation['expiration_time']) || ($operation['expiration_time'] = ($this->base_expiration_sec() + $this->expire_in_secs_proposal));
            if ($requiresReview) {
                $operation['review_period_seconds'] = $extraReview + max($this->committee_min_review, $this->review_in_secs_committee);
                /*
                * Expiration time must be at least equal to
                * now + review_period_seconds, so we add one hour to make sure
                */
                $operation['expiration_time'] += (60 * 60 + $extraReview);
            }
        }
        $operation_instance = $_type->fromObject($operation);
        return [$operation_id, $operation_instance];
    }

    /* optional: fetch the current head block */

    function update_head_block()
    {
        $g = $this->rpc->query("get_objects", [["2.0.0"]]);
        $r = $this->rpc->query("get_objects", [["2.1.0"]]);

        $this->head_block_time_string = $r[0]['time'];
        $this->committee_min_review = $g[0]['parameters']['committee_proposal_review_period'];
    }

    /**
     * optional: there is a default expiration
     * @param $sec
     * @return mixed
     */
    function set_expire_seconds($sec)
    {
        if ($this->tr_buffer) {
            throwException("already finalized");
        }
        return $this->expiration = $this->base_expiration_sec() + $sec;

    }

    /* Wraps this transaction in a proposal_create transaction */
    function propose($proposal_create_options)
    {
        if ($this->tr_buffer) {
            throwException("already finalized");
        }
        if (count($this->operations) < 1) {
            throwException("add operation first");
        }

        assert($proposal_create_options, "proposal_create_options");
        assert($proposal_create_options['fee_paying_account'], "proposal_create_options.fee_paying_account");

        $proposed_ops = [];
        foreach ($this->operations as $op) {
            $proposed_ops[] = ['op' => $op];
        }

        $this->operations = [];
        $this->signatures = [];
        $this->signer_private_keys = [];
        $proposal_create_options['proposed_ops'] = $proposed_ops;
        $this->add_type_operation("proposal_create", $proposal_create_options);
        return $this;
    }

    /**
     *optional: the fees can be obtained from the witness node
     * @param $asset_id
     *
     */
    function set_required_fees($asset_id = null)
    {
        //        var fee_pool;
        if ($this->tr_buffer) {
            throwException("already finalized");
        }
        if (!count($this->operations)) {
            throwException("add operations first");
        }
        $operations = [];
        for ($i = 0; $i < count($this->operations); $i++) {
            $op = $this->operations[$i];
            array_push($operations, ops::serializer('operation')->toObject($op));
        }

        if (!$asset_id) {
            $op1_fee = $operations[0][1]['fee'];
            if ($op1_fee && !empty($op1_fee['asset_id'])) {
                $asset_id = $op1_fee['asset_id'];
            } else {
                $asset_id = "1.3.1";
            }
        }
        $fees = $this->rpc->query("get_required_fees", [$operations, $asset_id]);

        $feeAssetPromise = null;
        $coreFees = [];
        if ($asset_id !== "1.3.1") {
            // This handles the fallback to paying fees in BTS if the fee pool is empty.
            $coreFees = $this->rpc->query("get_required_fees", [$operations, "1.3.1"]);
            $assets = $this->rpc->query("get_objects", [[$asset_id]]);
        }

        $asset = isset($assets[0]) ? $assets[0] : null;
        $dynamicObject = ($asset_id !== "1.3.1" && $asset) ? $this->rpc->query("get_objects", [[$asset['dynamic_asset_data_id']]]) : 0;


        self::$flat_assets = [];

        if ($asset_id !== "1.3.1") {
            $fee_pool = $dynamicObject ? $dynamicObject[0]['fee_pool'] : 0;
            $totalFees = 0;
            for ($j = 0, $fee = null; $j < count($coreFees); $j++) {
                $fee = $coreFees[$j];
                $totalFees += $fee['amount'];
            }

            if ($totalFees > intval($fee_pool, 10)) {
                $fees = $coreFees;
                $asset_id = "1.3.1";
            }
        }
        self::flatten($fees);
        self::$asset_index = 0;

        for ($i = 0; $i < count($this->operations); $i++) {
            $this->set_fee($i);
        }
    }

    private static $asset_index = 0, $flat_assets = [];

    private static function flatten($obj)
    {
        if (is_array($obj) && isset($obj[0])) {
            for ($k = 0, $item = null; $k < count($obj); $k++) {
                $item = $obj[$k];
                self::flatten($item);
            }
        } else {
            array_push(self::$flat_assets, $obj);
        }
        return;
    }

    private function set_fee($i)
    {
        $this->operations[$i][1];
        if (!$this->operations[$i][1]['fee'] || $this->operations[$i][1]['fee']['amount'] === 0
            || ($this->operations[$i][1]['fee']['amount'] === "0")// Long
        ) {
            $this->operations[$i][1]['fee'] = self::$flat_assets[self::$asset_index];
        }
        self::$asset_index++;
        if (isset($this->operations[$i][1]['proposed_ops'])) {
            $result = [];
            for ($y = 0; $y < count($this->operations[$i][1]['proposed_ops']); $y++)
                array_push($result, $this->set_fee($this->operations[$i][1]['proposed_ops'][$y]['op'][1]));

            return $result;
        }
    }

    function add_signer($private_key, $public_key = null)
    {
        array_push($this->signer_private_keys, [$private_key, $public_key]);
    }


    function sign()
    {

        if (!$this->tr_buffer) {
            throwException("not finalized");
        }
        if ($this->signed) {
            throwException("already signed");
        }

        if (!$this->signProvider) {
            if (!count($this->signer_private_keys)) {
                throwException("Transaction was not signed. Do you have a private key? [no_signers]");
            }
            $end = count($this->signer_private_keys);
            for ($i = 0; 0 < $end ? $i < $end : $i > $end; 0 < $end ? $i++ : $i++) {
                list($private_key, $public_key) = $this->signer_private_keys[$i];
                $sig = Signature::signBuffer(
                    $this->chain_id . bin2hex($this->tr_buffer),
                    $private_key,
                    $public_key
                );
                array_push($this->signatures, $sig);
            }
        } else {
            try {
                $this->signatures = $this->signProvider($this, $this->chain_id);
            } catch (\Exception $err) {
                return;
            }
        }

        $this->signer_private_keys = [];
        $this->signed = true;
    }

    function serialize()
    {
        return ops::serializer('signed_transaction')->toObject($this);
    }

    function toObject()
    {
        return ops::serializer('signed_transaction')->toObject($this);
    }

    function broadcast()
    {
        if ($this->tr_buffer) {
            return $this->_broadcast();
        } else {
            $this->finalize();
            return $this->_broadcast();
        }
    }

    private function base_expiration_sec()
    {
        $head_block_sec = ceil($this->getHeadBlockDate());
        $now_sec = ceil(time());
        // The head block time should be updated every 3 seconds.  If it isn't
        // then help the transaction to expire (use head_block_sec)
        if ($now_sec - $head_block_sec > 30) {
            return $head_block_sec;
        }
        // If the user's clock is very far behind, use the head block time.
        return max($now_sec, $head_block_sec);
    }

    private function _broadcast()
    {
        try {
            if (!$this->signed) {
                $this->sign();
            }
        } catch (\Exception $err) {
            return;
        }
        if (!$this->tr_buffer) {
            throwException("not finalized");
        }
        if (!count($this->signatures)) {
            throwException("not signed");
        }
        if (!count($this->operations)) {
            throwException("no operations");
        }
        $tr_object = ops::serializer('signed_transaction')->toObject($this);

        return $this->rpc->broadcast($tr_object);
    }

    private function getHeadBlockDate()
    {
        return $this->timeStringToDate($this->head_block_time_string);
    }

    private function timeStringToDate($time_string)
    {
        if (!$time_string)
            return strtotime("1970-01-01 00:00:00");
        return strtotime($time_string);
    }
}
