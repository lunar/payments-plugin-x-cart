## INSTALLATION INSTRUCTIONS:

### I. 
Upload files from the `files_to_upload` folder to the X-cart root directory.
 

### II.
Apply SQL patch from the `lunar_sql.sql` file to the store database.


### III.
Modify the following files:

1. `admin/order.php`

Find the following line: `if ($mode == 'status_change') {`

Add before:
```php
/** Lunar: start */
require $xcart_dir . '/include/lunar_order.php';
/** Lunar: end */
```


2. `skin/'{YOUR_SKIN}' OR 'common_files'/main/history_order.tpl`

Find the following line: `{if $order.fmf and $order.fmf.blocked}`

Add before:
```php
{* Lunar: start *}
{if $order.extra.lunar_txnid ne ""}
    {include file="main/lunar_buttons.tpl"}
{/if}
{* Lunar: end *}
```

### IV.
Clear X-Cart cache.

    You can do it here: YOUR_XCART_STORE_URL/admin/tools.php#cleartmp

 
### V.
Now you can enable the 'Lunar' payment gateway.

    1)
    Open the following page in the X-cart admin area:
    Top menu 'Settings' tab -> 'Payment methods' page -> 'Payment gateways' tab.

    2)
    Select the 'Lunar' in the 'Payment gateways' selectbox and click the 'Add' button.

    3)
    Find your newly added 'Lunar' payment gateway in the 'Payment methods' tab.
    (It should be at the bottom of page before the 'Apply changes' button.)

    4)
    Check the checkbox near the 'Lunar' payment gateway and click the 'Apply changes' button.

    5)
    Click to the 'Configure' link near the 'Credit Card processor: Lunar' sentence.

    6)
    In the opened 'Lunar' page configure your payment processing gateway and click the 'Update' button.

        Note:
        To capture the authorized amount or void the transaction, login to the Lunar payment gateway backoffice and go to the edit order screen. 
        Scroll down to the customer notes, and bellow them you will find buttons to capture/decline the payment

    7)
    Place a test order and go to the admin area and find your placed test order there.
    On the 'Order details' page you need to find the '+ Payment gateway log' link. It is under the 'Customer notes' and 'Status' fields.
    Click to this '+ Payment gateway log' link and you will see your order 'Transaction ID' there.

    8)
    Open the Lunar payment gateway backoffice site (https://lunar.app/)
    By 'Transaction ID' value you will find any order there.


Enjoy!
