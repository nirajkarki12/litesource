<table class="footer">
<tr>
	<td rowspan="3"><span class="bold"><?php echo $this->lang->line('contact').': '.$invoice->from_first_name.' '.$invoice->from_last_name; ?></span>
		<a href="mailto:<?php echo $invoice->from_email_address; ?>"><?php echo $invoice->from_email_address; ?></a>
		&nbsp;|&nbsp;<?php echo invoice_from_mobile_number($invoice); ?><br />
		<span class="footnote_smallerx">Please refer to the above tax invoice number on all correspondence</span><br /><br />
	</td>
</td>
</tr>
<tr></tr>
<tr>
	<td valign="bottom" style="text-align: right;"><span class="bold">Page {PAGENO}</span></td>
</tr> 
</table>