-- cleanup old setup --
DELETE FROM xcart_ccprocessors WHERE processor='cc_lunar.php';
DELETE FROM xcart_payment_countries WHERE processor='cc_lunar.php';
INSERT INTO xcart_payment_countries VALUES ('cc_lunar.php','','','');

DELETE FROM xcart_ccprocessors WHERE processor='cc_lunar_card.php';
INSERT INTO `xcart_ccprocessors` (
    `module_name`, `type`, `processor`, `template`,
    `param01`, `param02`, `param03`, `param04`, `param05`, `param06`, `param07`, `param08`, `param09`,
    `disable_ccinfo`, `is_check`, `is_refund`, `c_template`, `cmpi`, `use_preauth`, `has_preauth`)
VALUES ('Lunar Card', 'C', 'cc_lunar_card.php', 'cc_lunar.tpl',
    '', '', '', '', '', '', '', 'card', 'USD',
    'Y', '', '', 'payments/cc_lunar.tpl', '', 'Y', '');

DELETE FROM xcart_ccprocessors WHERE processor='cc_lunar_mobilepay.php';
INSERT INTO `xcart_ccprocessors` (
    `module_name`, `type`, `processor`, `template`,
    `param01`, `param02`, `param03`, `param04`, `param05`, `param06`, `param07`, `param08`, `param09`,
    `disable_ccinfo`, `is_check`, `is_refund`, `c_template`, `cmpi`, `use_preauth`, `has_preauth`)
VALUES ('Lunar Mobilepay', 'C', 'cc_lunar_mobilepay.php', 'cc_lunar.tpl',
    '', '', '', '', '', '', '', 'mobilePay', 'USD',
    'Y', '', '', 'payments/cc_lunar.tpl', '', 'Y', '');

DELETE FROM xcart_payment_countries WHERE processor='cc_lunar_card.php';
INSERT INTO xcart_payment_countries VALUES ('cc_lunar_card.php','','','');
DELETE FROM xcart_payment_countries WHERE processor='cc_lunar_mobilepay.php';
INSERT INTO xcart_payment_countries VALUES ('cc_lunar_mobilepay.php','','','');

REPLACE INTO xcart_languages SET code='en', name='lbl_lunar_card_title_text', value='Card', topic='Text';
REPLACE INTO xcart_languages SET code='en', name='lbl_lunar_card_title_link', value='<a target="_blank" href="languages.php?language={{shop_language}}&topic=&filter=lbl_lunar_card_title_text">Modify this here</a>.', topic='Labels';
REPLACE INTO xcart_languages SET code='en', name='lbl_lunar_card_description_text', value='Secure payment with card via © Lunar', topic='Text';
REPLACE INTO xcart_languages SET code='en', name='lbl_lunar_card_description_link', value='<a target="_blank" href="languages.php?language={{shop_language}}&topic=&filter=lbl_lunar_card_description_text">Modify this here</a>.', topic='Labels';

REPLACE INTO xcart_languages SET code='en', name='lbl_lunar_mobilepay_title_text', value='MobilePay', topic='Text';
REPLACE INTO xcart_languages SET code='en', name='lbl_lunar_mobilepay_title_link', value='<a target="_blank" href="languages.php?language={{shop_language}}&topic=&filter=lbl_lunar_mobilepay_title_text">Modify this here</a>.', topic='Labels';
REPLACE INTO xcart_languages SET code='en', name='lbl_lunar_mobilepay_description_text', value='Secure payment with MobilePay via © Lunar', topic='Text';
REPLACE INTO xcart_languages SET code='en', name='lbl_lunar_mobilepay_description_link', value='<a target="_blank" href="languages.php?language={{shop_language}}&topic=&filter=lbl_lunar_mobilepay_description_text">Modify this here</a>.', topic='Labels';

REPLACE INTO xcart_languages SET code='en', name='lbl_lunar_transaction', value='Lunar Transaction ID', topic='Labels';
REPLACE INTO xcart_languages SET code='en', name='txt_lunar_button_info', value='You can change the transaction here by clicking appropriate button and this transaction will be updated in the Lunar account. Order status of this order in your store will be changed automatically.', topic='Text';
REPLACE INTO xcart_languages SET code='en', name='txt_lunar_status_below', value='Or you can modify the transaction in the Lunar dashboard at first. After that, be sure to change the status of this order below.', topic='Text';
REPLACE INTO xcart_languages SET code='en', name='lbl_lunar_checkout_mode_label', value='Checkout mode', topic='Labels';
REPLACE INTO xcart_languages SET code='en', name='lbl_lunar_app_key', value='App Key', topic='Labels';
REPLACE INTO xcart_languages SET code='en', name='lbl_lunar_public_key', value='Public Key', topic='Labels';
REPLACE INTO xcart_languages SET code='en', name='lbl_lunar_logo_url', value='Logo URL', topic='Labels';
REPLACE INTO xcart_languages SET code='en', name='lbl_lunar_configuration_id', value='Configuration ID', topic='Labels';
REPLACE INTO xcart_languages SET code='en', name='lbl_lunar_payment_title', value='Payment title', topic='Labels';
REPLACE INTO xcart_languages SET code='en', name='lbl_lunar_payment_description', value='Description', topic='Labels';
REPLACE INTO xcart_languages SET code='en', name='lbl_lunar_cc_info', value='After submitting your order you will be redirected to hosted checkout page.', topic='Labels';
REPLACE INTO xcart_languages SET code='en', name='lbl_lunar_currency', value='Payment currency', topic='Labels';
