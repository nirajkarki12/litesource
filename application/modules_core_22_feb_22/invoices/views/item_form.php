<?php $this->load->view('dashboard/header'); ?>

<?php $this->load->view('invoices/product_autocomplete'); ?>

<?php $this->load->view('dashboard/jquery_date_picker'); ?>

<div class="grid_10" id="content_wrapper">

	<div class="section_wrapper">

		<h3 class="title_black"><?php echo $this->lang->line('invoice_number') . ' ' . invoice_id($invoice); ?></h3>

		<?php $this->load->view('dashboard/system_messages'); ?>

		<div class="content toggle">
			
			<form method="post" action="<?php echo site_url($this->uri->uri_string()); ?>" name="invoice_item_form">
				
				<dl>
					<dt><label><?php echo $this->lang->line('catalog_number'); ?>: </label></dt>
					<dd><input type="text" name="item_name" id="product_autocomplete" value="<?php echo $this->mdl_items->form_value('item_name'); ?>" /></dd>
				</dl>
				
				<dl>
					<dt><label><?php echo $this->lang->line('item_type'); ?>: </label></dt>
					<dd><input type="text" name="item_type" id="item_type" value="<?php echo $this->mdl_items->form_value('item_type'); ?>" /></dd>
				</dl>
				
				<dl>
					<dt><label><?php echo $this->lang->line('item_description'); ?>: </label></dt>
					<dd><textarea name="item_description" id="item_description" rows="5" cols="40"><?php echo $this->mdl_items->form_value('item_description'); ?></textarea></dd>
				</dl>				
				
				<dl>
					<dt><label><?php echo $this->lang->line('quantity'); ?>: </label></dt>
					<dd><input type="text" name="item_qty" id="item_qty" value="<?php echo format_qty($this->mdl_items->form_value('item_qty')); ?>" /></dd>
				</dl>
				
				<dl>
					<dt><label><?php echo $this->lang->line('unit_price'); ?>: </label></dt>
					<dd><input type="text" name="item_price" id="item_price" value="<?php if($this->mdl_items->form_value('item_price')) { echo format_number($this->mdl_items->form_value('item_price')); } ?>" /></dd>
				</dl>

				<?php foreach ($custom_fields as $field) { ?>
				<dl>
					<dt><label><?php echo $field->field_name ?>: </label></dt>
					<dd><input type="text" id="<?php echo $field->column_name; ?>" name="<?php echo $field->column_name; ?>" value="<?php echo $this->mdl_items->form_value($field->column_name); ?>" /></dd>
				</dl>
				<?php } ?>

				<input type="submit" name="btn_submit_item" id="btn_submit" value="<?php echo $this->lang->line('save_item'); ?>" />
				<input type="submit" name="btn_cancel" id="btn_cancel" value="<?php echo $this->lang->line('cancel'); ?>" />

			</form>

		</div>

	</div>

</div>

<?php $this->load->view('dashboard/footer'); ?>