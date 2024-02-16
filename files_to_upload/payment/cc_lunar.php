<?php
/**
 * @category   Payment method
 * @package    Lunar
 * @subpackage Payment interface
 */

if (!defined('XCART_START')) { header("Location: ../"); die("Access denied"); }

if (!class_exists('\\Lunar\\Lunar')) {
    require __DIR__.'/cc_lunar_api/vendor/autoload.php';
}

$api_client = new \Lunar\Lunar($module_params['param01'], null, !!$_COOKIE['lunar_testmode']);

$lunar_log = '';
$lunar_error = '';

if (!empty($lunar_txnid)) {
    try {
        $trans_data = $api_client->payments()->fetch($lunar_txnid);
    } catch (\Lunar\Exception\ApiException $e) {
        $exception_raised = $e->getMessage();
    }
} else {
    $lunar_error = 'No transaction ID provided';
}

if (empty($trans_data)) {
    $lunar_log = 'Something went wrong. Unable to fetch transaction.';
    $lunar_error = 'error_invalid_transaction_data';
}

if (!empty($exception_raised)) {
    $lunar_log .= ' Exception message: '.$exception_raised;
}

$order_captured = false;

if (!$lunar_log && !empty($trans_data['authorisationCreated'])) {

    if ($module_params['use_preauth'] == 'Y') {
        $lunar_log = 'Transaction authorized.';
    } else {
        try {
            $capture_response = $api_client->payments()->capture($lunar_txnid, [
                'amount' => [
                    'currency' => $trans_data['amount']['currency'],
                    'decimal' => (string) $trans_data['amount']['decimal'],
                ]
            ]);
        } catch (\Lunar\Exception\ApiException $e) {
            $exception_raised = $e->getMessage();
        }

        if (!empty($capture_response['captureState']) && 'completed' == $capture_response['captureState']) {
            $lunar_log = 'Transaction finished. Captured.';
            $order_captured = true;
        } else {
            $declined_reason = isset($capture_response['declinedReason']) ? $capture_response['declinedReason']['error'] : '';
            $lunar_log ='Unable to capture. '.$declined_reason;
        }

        if (!empty($exception_raised)) {
            $lunar_log .= ' Exception raised: '.$exception_raised;
        }
    }
} else {
    $lunar_log = 'Transaction error. Empty transaction results.';
    $lunar_error = 'error_invalid_transaction_data';
}

$extra_order_data = [];
$extra_order_data['lunar_txnid'] = $lunar_txnid;

if ($lunar_error){
    $bill_output['code'] = 2;
    $bill_output['billmes'] = "Failed. " . $lunar_log . " Lunar Transaction: " . $lunar_txnid . ".";
    $extra_order_data['capture_status'] = 'F';
} else {
    $bill_output['code'] = 1;
    $bill_output['billmes'] = $lunar_log . " Lunar Transaction: " . $lunar_txnid . ".";

    if ($order_captured){
        $extra_order_data['capture_status'] = 'C';
    } else {
        $bill_output['is_preauth'] = 'Y';
        $extra_order_data['capture_status'] = 'A';
    }
}
