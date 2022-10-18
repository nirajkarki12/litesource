
<dl>
	<dt><label><?php echo $this->lang->line('supplier_name'); ?>: </label></dt>
	<dd>
		<select name="client_id" id="client_id">
		<?php foreach ($clients as $client) { ?>
		<option value="<?php echo $client->client_id; ?>" <?php if ($this->mdl_suppliers->form_value('client_id') == $client->client_id) { ?>selected="selected"<?php } ?>><?php echo $client->client_name; ?></option>
		<?php } ?>
		</select>
	</dd>
	
</dl>

<dl>
	<dt><label><?php echo $this->lang->line('supplier_short_name'); ?>: </label></dt>
	<dd><input type="text" name="supplier_short_name" id="supplier_short_name" value="<?php echo $this->mdl_suppliers->form_value('supplier_short_name'); ?>" /></dd>
</dl>

<dl>
	<dt><label><?php echo $this->lang->line('supplier_description'); ?>: </label></dt>
	<dd><textarea name="supplier_description" id="supplier_description" rows="5" cols="40"><?php echo form_prep($this->mdl_suppliers->form_value('supplier_description')); ?></textarea></dd>
</dl>


<dl>
	<dt><label><?php echo $this->lang->line('sort_index'); ?>: </label></dt>
	<dd><input type="text" name="supplier_sort_index" id="supplier_sort_index" value="<?php echo $this->mdl_suppliers->form_value('supplier_sort_index'); ?>" /></dd>
</dl>

<input type="submit" id="btn_submit" name="btn_submit" value="<?php echo $this->lang->line('submit'); ?>" />
<input type="submit" id="btn_cancel" name="btn_cancel" value="<?php echo $this->lang->line('cancel'); ?>" />

<div style="clear: both;">&nbsp;</div>