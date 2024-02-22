{if $usertype eq 'A'}
<h1>{$module_data.module_name}</h1>

{$lng.txt_cc_configure_top_text}

<br />
{* <br /><input type="button" name="lunar_signup" value="{$lng.lbl_register}"
  onclick="javascript: window.open('https://lunar.app/personal');" /><br /> *}
<br />

{capture name=dialog}
<form action="cc_processing.php?cc_processor={$smarty.get.cc_processor|escape:" url"}" method="post">

  <table cellspacing="10">

    <tr>
      <td>{$lng.lbl_lunar_app_key}:</td>
      <td><input type="text" name="param01" size="70" required value="{$module_data.param01|escape}" /></td>
    </tr>

    <tr>
      <td>{$lng.lbl_lunar_public_key}:</td>
      <td><input type="text" name="param02" size="70" required value="{$module_data.param02|escape}" /></td>
    </tr>

    <tr>
      <td>{$lng.lbl_lunar_logo_url}:</td>
      <td><input type="text" name="param03" size="70" required value="{$module_data.param03|escape}" /></td>
    </tr>

    {if $paymentMethodCode == 'lunar_mobilepay'}
    <tr>
      <td>{$lng.lbl_lunar_configuration_id}:</td>
      <td><input type="text" name="param04" size="70" required value="{$module_data.param04|escape}" /></td>
    </tr>
    {/if}

    <tr>
      <td>{$lng.lbl_lunar_checkout_mode_label}</td>
      <td>
        <select name="use_preauth">
          <option value="Y" {if $module_data.use_preauth eq 'Y' } selected="selected" {/if}>
            {$lng.lbl_auth_method}
          </option>
          <option value="" {if $module_data.use_preauth eq '' } selected="selected" {/if}>
            {$lng.lbl_auth_and_capture_method}
          </option>
        </select>
        ({$lng.lbl_use_preauth_method})
      </td>
    </tr>

    <tr>
      <td>{$lng.lbl_lunar_payment_title}:</td>
      <td>
        {$lng.lbl_lunar_PaymentInfoTitle|substitute:"shop_language":$shop_language}
        {* <input type="text" name="param06" size="36" value="{$module_data.param06|escape|replace:" \\":""}" /> *}
      </td>
    </tr>

    <tr>
      <td>{$lng.lbl_lunar_payment_description}:</td>
      <td>
        {$lng.lbl_lunar_PaymentInfoDescription|substitute:"shop_language":$shop_language}
        {* <input type="text" name="param07" size="72" value="{$module_data.param07|escape|replace:" \\":""}" /> *}
      </td>
    </tr>

    <tr>
      <td>{$lng.lbl_lunar_currency}:</td>
      <td>
        <select name="param09">
          {if $currencies}
            {foreach item=currency from=$currencies}
              <option value="{$currency.code}" {if $module_data.param09 eq $currency.code } selected="selected" {/if}>
                {$currency.code}
              </option>
            {/foreach}
          {/if}
        </select>
        (The currency used when the customer pay for an order)
      </td>
    </tr>

  </table>
  <br /><br />
  <input type="submit" value="{$lng.lbl_update|strip_tags:false|escape}" />
</form>
{/capture}

{include file="dialog.tpl" title=$lng.lbl_ch_settings content=$smarty.capture.dialog extra='width="100%"'}

{else}
  {$lng.lbl_lunar_cc_info}
{/if}