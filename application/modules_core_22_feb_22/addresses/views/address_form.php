
<form method="post" action="<?php echo site_url($this->uri->uri_string()); ?>">

	<dl>
		<dd><input type="hidden" name="address_id" id="address_id" value="<?php echo $this->mdl_addresses->form_value('address_id'); ?>" /></dd>
	</dl>

	<dl>
		<dt><label><?php echo $this->lang->line('contact_name'); ?>: </label></dt>
		<dd><input type="text" name="address_contact_name" id="address_contact_name" value="<?php echo $this->mdl_addresses->form_value('address_contact_name'); ?>" /></dd>
	</dl>

	<dl>
		<dt><label><?php echo $this->lang->line('street_address'); ?>: </label></dt>
		<dd><input type="text" name="address_street_address" id="address_street_address" value="<?php echo $this->mdl_addresses->form_value('address_street_address'); ?>" /></dd>
	</dl>

	<dl>
		<dt><label><?php echo $this->lang->line('street_address_2'); ?>: </label></dt>
		<dd><input type="text" name="address_street_address_2" id="address_street_address_2" value="<?php echo $this->mdl_addresses->form_value('address_street_address_2'); ?>" /></dd>
	</dl>

	<dl>
		<dt><label><?php echo $this->lang->line('city'); ?>: </label></dt>
		<dd><input type="text" name="address_city" id="address_city" value="<?php echo $this->mdl_addresses->form_value('address_city'); ?>" /></dd>
	</dl>

	<dl>
		<dt><label><?php echo $this->lang->line('state'); ?>: </label></dt>
		<dd><input type="text" name="address_state" id="address_state" value="<?php echo $this->mdl_addresses->form_value('address_state'); ?>" /></dd>
	</dl>

	<dl>
		<dt><label><?php echo $this->lang->line('postcode'); ?>: </label></dt>
		<dd><input type="text" name="address_postcode" id="address_postcode" value="<?php echo $this->mdl_addresses->form_value('address_postcode'); ?>" /></dd>
	</dl>

	<dl>
		<dt><label><?php echo $this->lang->line('country'); ?>: </label></dt>
		<dd><input type="text" name="address_country" id="address_country" value="<?php echo $this->mdl_addresses->form_value('address_country'); ?>" /></dd>
	</dl>

	<dl>
		<dt><label><?php echo $this->lang->line('address_active'); ?>: </label></dt>
		<dd><input type="checkbox" name="address_active" id="address_active" value="1" <?php if ($this->mdl_addresses->form_value('address_active') or (!$_POST and !uri_assoc('address_id'))) { ?>checked="checked"<?php } ?> /></dd>
	</dl>

	<dl>
		<dt><label><?php echo $this->lang->line('address_defaultable'); ?>: </label></dt>
		<dd><input type="checkbox" name="address_defaultable" id="address_defaultable" value="1" <?php if ($this->mdl_addresses->form_value('address_defaultable')) { ?>checked="checked"<?php } ?> /></dd>
	</dl>

	<input type="submit" id="btn_submit_address" name="btn_submit_address" value="<?php echo $this->lang->line('submit'); ?>" />
	<input type="submit" id="btn_cancel" name="btn_cancel" value="<?php echo $this->lang->line('cancel'); ?>" />

</form>
