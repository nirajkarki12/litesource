<?php $this->load->view('dashboard/header'); ?>

<div class="grid_12" id="content_wrapper">

	<div class="section_wrapper">

		<h3 class="title_black"><?php echo $this->lang->line('client_group_form'); ?></h3>

		<?php $this->load->view('dashboard/system_messages'); ?>

		<div class="content toggle">

			<form method="post" action="<?php echo site_url($this->uri->uri_string()); ?>">

				<dl>
					<dt><label><?php echo $this->lang->line('client_group_name'); ?>: </label></dt>
					<dd><input type="text" name="client_group_name" id="client_group_name" value="<?php echo $this->mdl_client_groups->form_value('client_group_name'); ?>" /></dd>
				</dl>

				<dl>
					<dt><label><?php echo $this->lang->line('client_group_discount_percent'); ?>: </label></dt>
					<dd><input type="text" name="client_group_discount_percent" id="client_group_discount_percent" value="<?php echo $this->mdl_client_groups->form_value('client_group_discount_percent'); ?>" /></dd>
				</dl>

				<input type="submit" id="btn_submit" name="btn_submit" value="<?php echo $this->lang->line('submit'); ?>" />
				<input type="submit" id="btn_cancel" name="btn_cancel" value="<?php echo $this->lang->line('cancel'); ?>" />

			</form>

		</div>

	</div>

</div>

<?php $this->load->view('dashboard/footer'); ?>