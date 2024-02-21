<?php
/**
 * @category   Payment method
 * @package    Lunar
 * @subpackage Payment interface
 */

$lunar_method_processor = 'cc_lunar.php';

if (!class_exists('\\Lunar\\Lunar')) {
    require __DIR__.'/cc_lunar_api/vendor/autoload.php';
}

/**
 * REDIRECT
 */
if ( $_SERVER['REQUEST_METHOD'] == 'POST' && defined('XCART_START')) {

    $api_client = new \Lunar\Lunar($module_params['param01'], null, !!$_COOKIE['lunar_testmode']);
    
    $currencyCode = 'USD';
    $order_id = $orderids[0];

    $products = [];
    foreach($cart['products'] as $p) {
        $products[] = [
            'ID' => $p['productcode'],
            'Name' => $p['product'],
            'Quantity'  => $p['amount'],
        ];
    }

    $lunar_args = [
        'integration' => [
            'key' => $module_params['param02'],
            'name' => 'SHOP',
            'logo' => $module_params['param03'],
        ],
        'amount' => [
            'currency' => $currencyCode,
            'decimal' => (string) $cart['total_cost'],
        ],
        'custom' => [
            'orderId' => $order_id,
            'products' => $products,
            'customer' => [
                'name' => '',
                'email' => '',
                'phoneNo' => '',
                'address' => '',
                'ip' => func_get_valid_ip($_SERVER["REMOTE_ADDR"]),
            ],
            'platform' => [
                'name' => 'X-Cart',
                'version' => $version,
            ],
            'lunarPluginVersion' => '1.0.0',
        ],
        'redirectUrl' => $xcart_catalogs['customer'].'/payment/'.$lunar_method_processor
                            .'?lunar_method=card'.'&orderids='.$order_id,
        'preferredPaymentMethod' => 'card',
    ];

    if ($module_params['param04']) {
        $lunar_args['mobilePayConfiguration'] = [
            'configurationID' => $module_params['param04'],
            'logo' => $module_params['param03'],
        ];
    }

    $redirect_url = 'https://pay.lunar.money/?id=';
    if (!!$_COOKIE['lunar_testmode']) {
        $redirect_url = 'https://hosted-checkout-git-develop-lunar-app.vercel.app/?id='; 
        $lunar_args['test'] = func_lunar_get_test_object($currencyCode);
    }

    try {
        $payment_intent_id = $api_client->payments()->create($lunar_args);
        x_session_register('_lunar_intent_id', $payment_intent_id);
    } catch(\Lunar\Exception\ApiException $e) {
        $error_message = $e->getMessage();
        func_lunar_debug_log($error_message);
    }

    if (empty($payment_intent_id)) {
        $error_message = 'There was an error creating payment intent';
        func_lunar_debug_log($error_message);
    }

    if (!empty($error_message)) {
        $top_message = ['type' => 'E'];
        $top_message['content'] = $error_message;
        func_header_location($xcart_catalogs['customer'] . '/cart.php?mode=checkout');
    }

    func_header_location($redirect_url.$payment_intent_id);

/**
 * RETURN
 */
} elseif ($_SERVER['REQUEST_METHOD'] == 'GET' && !empty($_GET['lunar_method'])) {
    
    require __DIR__.'/auth.php';

    if (empty($lunar_method_processor)) {
        $lunar_method_processor = basename($_SERVER['SCRIPT_FILENAME']);
    }

    if (!func_is_active_payment($lunar_method_processor)) {
        exit;
    }

    $module_params = func_get_pm_params($lunar_method_processor);

    $api_client = new \Lunar\Lunar($module_params['param01'], null, !!$_COOKIE['lunar_testmode']);

    $lunar_error = '';

    $lunar_txnid = x_session_get_var('_lunar_intent_id');

    if (!empty($lunar_txnid)) {
        try {
            $trans_data = $api_client->payments()->fetch($lunar_txnid);
        } catch (\Lunar\Exception\ApiException $e) {
            $exception_raised = $e->getMessage();
            func_lunar_debug_log($exception_raised);
        }
    } else {
        $lunar_error = 'No transaction ID provided';
    }

    if (empty($trans_data)) {
        $lunar_error = 'Something went wrong. Unable to fetch transaction.';
    }

    if (!empty($exception_raised)) {
        $lunar_error .= ' Exception message: '.$exception_raised;
    }

    $order_captured = false;

    if (!$lunar_error && !empty($trans_data['authorisationCreated'])) {

        if ($module_params['use_preauth'] == 'Y') {
            $lunar_error = 'Transaction authorized.';

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
                $lunar_log = 'Unable to capture. '.$declined_reason;
            }

            if (!empty($exception_raised)) {
                $lunar_log .= ' Exception raised: '.$exception_raised;
            }

            if (!empty($lunar_log)) {
                func_lunar_debug_log($lunar_log);
            }
        }
    } else {
        $lunar_error = 'Transaction error. Empty transaction results.';
    }

    $extra_order_data = [];
    $extra_order_data['lunar_txnid'] = $lunar_txnid;

    if ($lunar_error){
        $bill_output['code'] = 2;
        $bill_output['billmes'] = "Failed. " . $lunar_error . " Lunar Transaction: " . $lunar_txnid;
        $extra_order_data['capture_status'] = 'F';

        $top_message = ['type' => 'E', 'content' => $bill_output['billmes']];
        func_header_location($xcart_catalogs['customer'] . '/cart.php?mode=checkout');
    } else {
        $bill_output['code'] = 1;
        $bill_output['billmes'] = "Lunar Transaction: " . $lunar_txnid;

        if ($order_captured){
            $extra_order_data['capture_status'] = 'C';
        } else {
            $bill_output['is_preauth'] = 'Y';
            $extra_order_data['capture_status'] = 'A';
        }
    }

    $orderids = [filter_var($_GET['orderids'], FILTER_VALIDATE_INT)];

    if ($orderids) {
        require $xcart_dir . '/payment/payment_ccend.php';
    } else {
        $top_message = ['type' => 'E', 'content' => 'Incorrect or no order id provided'];
        func_header_location($xcart_catalogs['customer'] . '/cart.php?mode=checkout');
    }
}

/**
 * 
 */
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

/**
 * 
 */
function func_lunar_debug_log($log_msg)
{
    x_log_add('payment', $log_msg);
}