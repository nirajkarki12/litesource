<?php $this->load->view('dashboard/header'); ?>

<?php $this->load->view('dashboard/jquery_date_picker'); ?>

<div class="grid_10" id="content_wrapper">

	<div class="section_wrapper">

		<h3 class="title_black"><?php echo (uri_assoc('is_quote') ? $this->lang->line('quote_search') : $this->lang->line('invoice_search')); ?></h3>

		<div class="content toggle">

			<form method="post" action="<?php echo site_url($this->uri->uri_string()); ?>">

				<dl>
					<dt><label><?php echo (uri_assoc('is_quote') ? $this->lang->line('quote_number') : $this->lang->line('invoice_number')); ?>: </label></dt>
					<dd><input type="text" name="invoice_number" value="<?php echo $this->mdl_invoice_search->form_value('invoice_number'); ?>" /></dd>
				</dl>

				<dl>
					<dt><label><?php echo $this->lang->line('client_name'); ?>: </label></dt>
					<dd><input type="text" style="width: 390px;" name="client_name" value="<?php echo $this->mdl_invoice_search->form_value('client_name'); ?>" /></dd>
				</dl>

				<dl>
					<dt><label><?php echo $this->lang->line('contact'); ?>: </label></dt>
					<dd><input type="text" style="width: 390px;" name="contact_name" value="<?php echo $this->mdl_invoice_search->form_value('contact_name'); ?>" /></dd>
				</dl>
				
				<dl>
					<dt><label><?php echo $this->lang->line('project'); ?>: </label></dt>
					<dd><input type="text" style="width: 390px;" name="project_name" value="<?php echo $this->mdl_invoice_search->form_value('project_name'); ?>" /></dd>
				</dl>
				<dl>
					<dt><label><?php echo $this->lang->line('product_name'); ?>: </label></dt>
					<dd><input type="text" style="width: 390px;" name="product_name" value="<?php echo $this->mdl_invoice_search->form_value('product_name'); ?>" /></dd>
				</dl>
				<dl>
					<dt><label><?php echo $this->lang->line('product_description'); ?>: </label></dt>
					<dd><input type="text" style="width: 390px;" name="product_description" value="<?php echo $this->mdl_invoice_search->form_value('product_description'); ?>" /></dd>
				</dl>
				
				<?php /*
				<dl>
					<dt><label><?php echo $this->lang->line('output_type'); ?>: </label></dt>
					<dd>
						<select name="output_type" id="output_type">
							<option value="index"><?php echo $this->lang->line('view'); ?></option>
							<option value="html"><?php echo $this->lang->line('html'); ?></option>
							<option value="pdf"><?php echo $this->lang->line('pdf'); ?></option>
							<option value="csv"><?php echo $this->lang->line('csv'); ?></option>
						</select>
					</dd>
				</dl>
				*/ ?>
				
				<input type="submit" id="btn_submit" name="btn_search" value="<?php echo $this->lang->line('search'); ?>" />

				<div style="clear: both;">&nbsp;</div>

			</form>

		</div>

	</div>

</div>

<?php // $this->load->view('dashboard/sidebar', array('side_block'=>'invoices/sidebar')); ?>

<?php $this->load->view('dashboard/footer'); ?>