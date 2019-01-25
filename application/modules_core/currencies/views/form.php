<?php $this->load->view('dashboard/header'); ?>

<div class="grid_10" id="content_wrapper">

	<div class="section_wrapper">

		<h3 class="title_black"><?php echo $this->lang->line('currencies'); ?></h3>

		<?php $this->load->view('dashboard/system_messages'); ?>

		<div class="content toggle">

			<form method="post" action="<?php echo site_url($this->uri->uri_string()); ?>">

				<dl>
					<dt><label><?php echo $this->lang->line('currency_name'); ?>: </label></dt>
					<dd><input type="text" name="currency_name" id="currency_name" value="<?php echo $this->mdl_currencies->form_value('currency_name'); ?>" /></dd>
				</dl>

				<dl>
					<dt><label><?php echo $this->lang->line('currency_code'); ?>: </label></dt>
					<dd><input type="text" name="currency_code" id="currency_code" value="<?php echo $this->mdl_currencies->form_value('currency_code'); ?>" /></dd>
				</dl>
				
				<dl>
					<dt><label><?php echo $this->lang->line('currency_symbol'); ?>: </label></dt>
					<dd><input type="text" name="currency_symbol" id="currency_symbol" value="<?php echo $this->mdl_currencies->form_value('currency_symbol'); ?>" /></dd>
				</dl>
				
				<input type="submit" id="btn_submit" name="btn_submit" value="<?php echo $this->lang->line('submit'); ?>" />
				<input type="submit" id="btn_cancel" name="btn_cancel" value="<?php echo $this->lang->line('cancel'); ?>" />

			</form>

		</div>

	</div>

</div>

<?php $this->load->view('dashboard/footer'); ?>