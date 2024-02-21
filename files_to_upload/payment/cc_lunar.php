<?php
/**
 * @category   Payment method
 * @package    Lunar
 * @subpackage Payment interface
 */

//  $payment_method_processor = 'cc_lunar.php';

if (!class_exists('\\Lunar\\Lunar')) {
    require __DIR__.'/cc_lunar_api/vendor/autoload.php';
}

$lunar_test_mode = !!$_COOKIE['lunar_testmode'];

$api_client = new \Lunar\Lunar($module_params['param01'], null, $lunar_test_mode);

if ( $_SERVER['REQUEST_METHOD'] == 'POST' && defined('XCART_START')) {

    $currencyCode = 'USD';

    $lunar_args = [
        'integration' => [
            'key' => $module_params['param02'],
            'name' => 'SHOP',
            'logo' => $module_params['param03'],
        ],
        'amount' => [
            'currency' => $currencyCode,
            'decimal' => (string) 53.97,
        ],
        'custom' => [
            'orderId' => '',
            'products' => [],
            'customer' => [
                'name' => '',
                'email' => '',
                'phoneNo' => '',
                'address' => '',
                'ip' => '',
            ],
            'platform' => [
                'name' => 'X-Cart',
                'version' => '',
            ],
            'lunarPluginVersion' => '1.0.0',
        ],
        'redirectUrl' => 'http://'.$xcart_https_host.'/payment/cc_lunar.php?lunar_method=card'.'&order_id='.'100',
        'preferredPaymentMethod' => 'card',
    ];

    if ($module_params['param04']) {
        $lunar_args['mobilePayConfiguration'] = [
            'configurationID' => $module_params['param04'],
            'logo' => $module_params['param03'],
        ];
    }

    if ($lunar_test_mode) {
        $lunar_args['test'] = func_lunar_get_test_object($currencyCode);
    }

    try {
        $payment_intent_id = $api_client->payments()->create($lunar_args);
        setCookie('_lunar_intent_id', $payment_intent_id);
    } catch(\Lunar\Exception\ApiException $e) {
        trigger_error($e->getMessage());
    }

    if (!empty($payment_intent_id)) {
        trigger_error('error_create_intent');
    }

    $redirect_url = 'https://pay.lunar.money/?id='.$payment_intent_id;
    if ($lunar_test_mode) {
        $redirect_url = 'https://hosted-checkout-git-develop-lunar-app.vercel.app/?id='.$payment_intent_id;  
    }

    func_header_location($redirect_url);


} elseif ( $_SERVER['REQUEST_METHOD'] == 'GET' && !empty($_GET['lunar_method'])) {
   
    x_load('payment', 'http');
 
    $module_params = func_get_pm_params('cc_lunar.php');

    $lunar_log = '';
    $lunar_error = '';

    $lunar_txnid = 'd5ba1c37-ca97-5b23-ba00-2665e148d73d';

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
        $bill_output['billmes'] = "Failed. " . $lunar_log . " Lunar Transaction: " . $lunar_txnid;
        $extra_order_data['capture_status'] = 'F';
    } else {
        $bill_output['code'] = 1;
        $bill_output['billmes'] = $lunar_log . " Lunar Transaction: " . $lunar_txnid;

        if ($order_captured){
            $extra_order_data['capture_status'] = 'C';
        } else {
            $bill_output['is_preauth'] = 'Y';
            $extra_order_data['capture_status'] = 'A';
        }
    }
}


function func_lunar_get_test_object($currency = 'DKK')
{
    return [
        "card"        => [
            "scheme"  => "supported",
            "code"    => "valid",
            "status"  => "valid",
            "limit"   => [
                "decimal"  => "50500.99",
                "currency" => $currency,
                
            ],
            "balance" => [
                "decimal"  => "50500.99",
                "currency" => $currency,
                
            ]
        ],
        "fingerprint" => "success",
        "tds"         => [
            "fingerprint" => "success",
            "challenge"   => true,
            "status"      => "authenticated"
        ],
    ];
}