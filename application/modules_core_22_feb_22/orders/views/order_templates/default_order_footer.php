<table class="footer">
<tr>
	<td rowspan="3"><span class="bold"><?php echo $this->lang->line('contact').': '.$order->from_first_name.' '.$order->from_last_name; ?></span>
		<a href="mailto:<?php echo $order->from_email_address; ?>"><?php echo $order->from_email_address; ?></a>
		&nbsp;|&nbsp;<?php echo $order->from_mobile_number; ?><br />
		<span class="footnote_smaller">Please refer to the above order number on all correspondence</span><br /><br />
	</td>
</td>
</tr>
<tr></tr>
<tr>
	<td valign="bottom" style="text-align: right;"><span class="bold">Page {PAGENO}</span></td>
</tr> 
</table>