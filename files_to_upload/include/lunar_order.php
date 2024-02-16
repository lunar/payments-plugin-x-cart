<?php

/**
 * @category   Payment method
 * @package    Lunar
 * @subpackage Lib
 */

if (!defined('XCART_SESSION_START')) {
    header("Location: ../");
    die("Access denied");
}

$exception_raised = '';
$lunar_error = '';

if (!empty($order['paymentid'])) {
    $payment_cc_processor = func_query_first_cell("SELECT processor FROM $sql_tbl[ccprocessors] WHERE paymentid='$order[paymentid]'");
}

if (
    !empty($payment_cc_processor) && $payment_cc_processor == 'cc_lunar.php'
    && !empty($order['extra']['lunar_txnid']) && in_array($order['status'], ['A', 'P'])
) {
    if (!class_exists('\\Lunar\\Lunar')) {
        require __DIR__ . '/cc_lunar_api/vendor/autoload.php';
    }

    $module_params = func_query_first("SELECT param01, param02 FROM $sql_tbl[ccprocessors] WHERE paymentid='$order[paymentid]'");

    $api_client = new \Lunar\Lunar($module_params['param01'], null, !!$_COOKIE['lunar_testmode']);
    
    $lunar_txnid = $order['extra']['lunar_txnid'];

    if (!empty($lunar_txnid)) {
        try {
            $trans_data = $api_client->payments()->fetch($lunar_txnid);
        } catch (\Lunar\Exception\ApiException $e) {
            $exception_raised = $e->getMessage();
        }
    } else {
        $lunar_error = 'No transaction ID provided';
    }

    if (!empty($trans_data) && !$exception_raised && !$lunar_error) {

        if (empty($mode)) {
            $mode = '';
        }

        if (!in_array($mode, ['lunar_capture', 'lunar_void', 'lunar_refund'])) {

            $show_lunar_buttons = [];

            if ($order['status'] == 'P') {
                $show_lunar_buttons['refund'] = 'Y';

            }

            if ($order['status'] == 'A') {
                $show_lunar_buttons['capture'] = 'Y';
                $show_lunar_buttons['void'] = 'Y';
            }

            $smarty->assign('show_lunar_buttons', $show_lunar_buttons);
        } else {
            // in_[$mode, ['lunar_capture', 'lunar_void', 'lunar_refund')

            if ($mode == "lunar_capture") {
                $status = "P";
                $mode = process_lunar_transaction($api_client, $order, 'capture');
            }

            if ($mode == "lunar_refund") {
                $status = "R";
                $mode = process_lunar_transaction($api_client, $order, 'refund');
            }

            if ($mode == "lunar_void") {
                $status = "D";
                $mode = process_lunar_transaction($api_client, $order, 'cancel');
            }

            if ($mode == "status_change") {
                func_change_order_status($orderid, $status);

                // must be called after FUnC_change_order_status
                XCOrderTracking::sendNotification();

                $top_message = [
                    'content' => func_get_langvar_by_name('txt_order_has_been_changed')
                ];
            }


        }
    } else {
        $error_message = '';

        if (!empty($exception_raised)) {
            $error_message = $exception_raised;
        }
        
        if (!empty($lunar_error)) {
            $error_message = $lunar_error;
        }

        if ($error_message) {
            $top_message = [
                'content' => $error_message
            ];

            func_header_location("order.php?orderid=" . $orderid);
        }

    }
}

/**
 * helper function (DRY)
 */
function process_lunar_transaction($api_client, $order, $action_type)
{
    $lunar_data = [
        'amount' => [
            'currency' => $order['extra']['lunar_currency'],
            'decimal' => (string) $order['total'],
        ]
    ];

    try {
        $api_response = $api_client->payments()->capture($order['extra']['lunar_txnid'], $lunar_data);
    } catch (\Lunar\Exception\ApiException $e) {
        $exception_raised = $e->getMessage();
    }

    if (!empty($api_response["{$action_type}State"]) && 'completed' == $api_response["{$action_type}State"]) {
        return "status_change";
    }
    
    $mode = '';
    if (!empty($exception_raised)) {
        $mode = $exception_raised;
    } elseif (!empty($api_response['declinedReason'])) {
        $mode = $api_response['declinedReason']['error'];
    } else {
        $mode = "lunar_mode_error";
    }

    return $mode;
}
