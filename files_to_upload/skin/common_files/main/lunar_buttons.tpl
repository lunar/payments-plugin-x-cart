{if $show_lunar_buttons}
<br />
<B>{$lng.lbl_lunar_transaction}:</B> {$order.extra.lunar_txnid}
<br />
{$lng.txt_lunar_button_info}
<br />
<br />
{/if}


{if $show_lunar_buttons.capture eq "Y"}
<input type="button" value="{$lng.lbl_capture}"
    onclick="javascript: if (confirm('{$lng.txt_are_you_sure|wm_remove|escape:javascript}')) 
    self.location = 'order.php?orderid={$order.orderid}&amp;mode=lunar_capture';" />
{/if}

{if $show_lunar_buttons.void eq "Y"}
<input type="button" value="{$lng.lbl_decline}"
    onclick="javascript: if (confirm('{$lng.txt_are_you_sure|wm_remove|escape:javascript}')) 
    self.location = 'order.php?orderid={$order.orderid}&amp;mode=lunar_void';" />
{/if}

{if $show_lunar_buttons.refund eq "Y"}
<input type="button" value="{$lng.lbl_refund}"
    onclick="javascript: if (confirm('{$lng.txt_are_you_sure|wm_remove|escape:javascript}')) 
    self.location = 'order.php?orderid={$order.orderid}&amp;mode=lunar_refund';" />
{/if}

{if $show_lunar_buttons}
<br />
<br />
<br />
{$lng.txt_lunar_status_below}
<br />
<br />
{/if}