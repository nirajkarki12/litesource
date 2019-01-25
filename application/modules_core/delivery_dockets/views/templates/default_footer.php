<table class="footer">
	<tr>
		<td rowspan="3"><span class="bold"><?php echo $this->lang->line('contact').': '.$docket->from_first_name.' '.$docket->from_last_name; ?></span>
			<a href="mailto:<?php echo $docket->from_email_address; ?>"><?php echo $docket->from_email_address; ?></a>
			&nbsp;|&nbsp;<?php echo $docket->from_mobile_number; ?><br />
			<span class="footnote_smaller">Please refer to invoice/docket number <?php echo $docket->invoice_number.'/'.$docket->docket_number; ?> on all correspondence</span><br /><br />
		</td>
		</td>
	</tr>
	<tr></tr>
	<tr>
		<td valign="bottom" style="text-align: right;"><span class="bold">Page {PAGENO}</span></td>
	</tr>
</table>