
<div class="full_box">
	<dl>
		<dt><label>Docket date: </label></dt>
                <dd><input class="datepicker" type="text" <?php if($docket->docket_delivery_status == '1'){ echo 'disabled';} ?> name="docket_date_entered" value="<?php echo format_date($docket->docket_date_entered); ?>" /></dd>
	</dl>
		
	<dl>
		<dt><label>Invoice date: </label></dt>
		<dd><input class="datepicker" type="text" name="invoice_date" value="<?php echo format_date($docket->invoice_date); ?>" /></dd>
	</dl>
	
	<input type="submit" id="btn_submit" name="btn_submit_options_general" value="<?php echo $this->lang->line('save_options'); ?>" />

</div>


<div style="clear: both;">&nbsp;</div>