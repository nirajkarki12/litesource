<form method="post" action="<?php echo site_url($this->uri->uri_string()); ?>">
<dl>
	<dt><label><?php echo $this->lang->line('active_client'); ?>: </label></dt>
	<dd><input type="checkbox" name="client_active" id="client_active" value="1" <?php if ($this->mdl_clients->form_value('client_active') or (!$_POST and !uri_assoc('client_id'))) { ?>checked="checked"<?php } ?> /></dd>
</dl>


<dl>
	<dt><label><?php echo $this->lang->line('client_name'); ?>: </label></dt>
	<dd><input type="text" name="client_name" id="client_name" value="<?php echo $this->mdl_clients->form_value('client_name'); ?>" /></dd>
</dl>

<dl>
	<dt><label><?php echo $this->lang->line('client_is_supplier'); ?>: </label></dt>
	<dd><input type="checkbox" name="client_is_supplier" id="client_is_supplier" value="1" <?php if ($this->mdl_clients->form_value('client_is_supplier')) { ?>checked="checked"<?php } ?> /></dd>
</dl>
    
    <dl>
	<dt><label>Show on Product page?<?php echo $this->lang->line('show_in_product_page'); ?>: </label></dt>
	<dd><input type="checkbox" name="show_in_product_page" id="show_in_product_page" value="1" <?php if ($this->mdl_clients->form_value('show_in_product_page')) { ?>checked="checked"<?php } ?> /></dd>
</dl>
    
    

<dl>
	<dt><label><?php echo $this->lang->line('parent_supplier'); ?>: </label></dt>
    <dd>
		<select name="parent_client_id" id="parent_client_id">
		<option <?php if($this->mdl_clients->form_value('parent_client_id') == 0) { ?>selected="selected"<?php } ?>>(None)</option>
		<?php foreach ($suppliers as $supplier) { ?>
		<option value="<?php echo $supplier->supplier_id; ?>" <?php if ($this->mdl_clients->form_value('parent_client_id') == $supplier->supplier_id) { ?>selected="selected"<?php } ?>><?php echo $supplier->supplier_name; ?></option>
		<?php } ?>
		</select>
	</dd>
</dl>

<dl>
	<dt><label><?php echo $this->lang->line('client_long_name'); ?>: </label></dt>
	<dd><input type="text" name="client_long_name" id="client_long_name" value="<?php echo $this->mdl_clients->form_value('client_long_name'); ?>" /></dd>
</dl>

<dl>
	<dt><label><?php echo $this->lang->line('client_group'); ?>: </label></dt>
    <dd>
		<select name="client_group_id" id="client_group_id">
		<?php foreach ($client_groups as $client_group) { ?>
		<option value="<?php echo $client_group->client_group_id; ?>" <?php if ($this->mdl_clients->form_value('client_group_id') == $client_group->client_group_id) { ?>selected="selected"<?php } ?>><?php echo $client_group->client_group_name; ?></option>
		<?php } ?>
		</select>
	</dd>
</dl>

<dl>
	<dt><label><?php echo $this->lang->line('currency'); ?>: </label></dt>
    <dd>
		<select name="client_currency_id" id="client_currency_id">
		<?php foreach ($currencies as $currency) { ?>
		<option value="<?php echo $currency->currency_id; ?>" <?php if ($this->mdl_clients->form_value('client_currency_id') == $currency->currency_id) { ?>selected="selected"<?php } ?>><?php echo $currency->currency_name; ?></option>
		<?php } ?>
		</select>
	</dd>
</dl>

<dl>
	<dt><label><?php echo $this->lang->line('tax_rate'); ?>: </label></dt>
	<dd>
		<select name="client_tax_rate_id" id="client_tax_rate_id">
		<?php foreach ($tax_rates as $tax_rate) { ?>
			<option value="<?php echo $tax_rate->tax_rate_id; ?>" <?php if ($selected_tax_rate_id == $tax_rate->tax_rate_id) { ?>selected="selected"<?php } ?>><?php echo $tax_rate->tax_rate_name; ?></option>
		<?php } ?>
		</select>
	</dd>
</dl>

<dl>
	<dt><label><?php echo $this->lang->line('tax_id_number'); ?>: </label></dt>
	<dd><input type="text" name="client_tax_id" id="client_tax_id" value="<?php echo $this->mdl_clients->form_value('client_tax_id'); ?>" /></dd>
</dl>

<dl>
	<dt><label><?php echo $this->lang->line('street_address'); ?>: </label></dt>
	<dd><input type="text" name="client_address" id="client_address" value="<?php echo $this->mdl_clients->form_value('client_address'); ?>" /></dd>
</dl>

<dl>
	<dt><label><?php echo $this->lang->line('street_address_2'); ?>: </label></dt>
	<dd><input type="text" name="client_address_2" id="client_address_2" value="<?php echo $this->mdl_clients->form_value('client_address_2'); ?>" /></dd>
</dl>

<dl>
	<dt><label><?php echo $this->lang->line('city'); ?>: </label></dt>
	<dd><input type="text" name="client_city" id="client_city" value="<?php echo $this->mdl_clients->form_value('client_city'); ?>" /></dd>
</dl>

<dl>
	<dt><label><?php echo $this->lang->line('state'); ?>: </label></dt>
	<dd><input type="text" name="client_state" id="client_state" value="<?php echo $this->mdl_clients->form_value('client_state'); ?>" /></dd>
</dl>

<dl>
	<dt><label><?php echo $this->lang->line('zip'); ?>: </label></dt>
	<dd><input type="text" name="client_zip" id="client_zip" value="<?php echo $this->mdl_clients->form_value('client_zip'); ?>" /></dd>
</dl>

<dl>
	<dt><label><?php echo $this->lang->line('country'); ?>: </label></dt>
	<dd><input type="text" name="client_country" id="client_country" value="<?php echo $this->mdl_clients->form_value('client_country'); ?>" /></dd>
</dl>

<dl>
	<dt><label><?php echo $this->lang->line('phone_number'); ?>: </label></dt>
	<dd><input type="text" name="client_phone_number" id="client_phone_number" value="<?php echo $this->mdl_clients->form_value('client_phone_number'); ?>" /></dd>
</dl>

<dl>
	<dt><label><?php echo $this->lang->line('fax_number'); ?>: </label></dt>
	<dd><input type="text" name="client_fax_number" id="client_fax_number" value="<?php echo $this->mdl_clients->form_value('client_fax_number'); ?>" /></dd>
</dl>

<dl>
	<dt><label><?php echo $this->lang->line('mobile_number'); ?>: </label></dt>
	<dd><input type="text" name="client_mobile_number" id="client_mobile_number" value="<?php echo $this->mdl_clients->form_value('client_mobile_number'); ?>" /></dd>
</dl>

<dl>
	<dt><label><?php echo $this->lang->line('email_address'); ?>: </label></dt>
	<dd><input type="text" name="client_email_address" id="client_email_address" value="<?php echo $this->mdl_clients->form_value('client_email_address'); ?>" /></dd>
</dl>

<dl>
	<dt><label><?php echo $this->lang->line('web_address'); ?>: </label></dt>
	<dd><input type="text" name="client_web_address" id="client_web_address" value="<?php echo $this->mdl_clients->form_value('client_web_address'); ?>" /></dd>
</dl>

<dl>
	<dt><label><?php echo $this->lang->line('notes'); ?>: </label></dt>
	<dd><textarea name="client_notes" id="client_notes" rows="5" cols="40"><?php echo form_prep($this->mdl_clients->form_value('client_notes')); ?></textarea></dd>
</dl>

<dl>
<input type="submit" id="btn_submit" name="btn_submit" value="<?php echo $this->lang->line('submit'); ?>" />
<input type="submit" id="btn_cancel" name="btn_cancel" value="<?php echo $this->lang->line('cancel'); ?>" />
</dl>

<div style="clear: both;">&nbsp;</div>
</form>