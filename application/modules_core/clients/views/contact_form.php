<?php $this->load->view('dashboard/header'); ?>

<div class="container_10" id="center_wrapper">

	<div class="grid_7" id="content_wrapper">

		<div class="section_wrapper">

			<h3 class="title_black"><?php echo $client->client_name . ' - ' . $this->lang->line('contact_form'); ?></h3>

			<?php $this->load->view('dashboard/system_messages'); ?>

			<div class="content toggle">

				<form method="post" action="<?php echo site_url($this->uri->uri_string()); ?>">

				<dl>
					<dt><label><?php echo $this->lang->line('contact_active'); ?>: </label></dt>
					<dd><input type="checkbox" name="contact_active" id="contact_active" value="1" <?php if ($this->mdl_contacts->form_value('contact_active') or (!$_POST and !uri_assoc('contact_id',4))) { ?>checked="checked"<?php } ?> /></dd>
				</dl>
					
				<dl>
					<dt><label><?php echo $this->lang->line('contact_name'); ?>: </label></dt>
					<dd><input type="text" name="contact_name" id="contact_name" value="<?php echo $this->mdl_contacts->form_value('contact_name'); ?>" /></dd>
				</dl>

				<dl>
					<dt><label><?php echo $this->lang->line('email_address'); ?>: </label></dt>
					<dd><input type="text" name="email_address" id="email_address" value="<?php echo $this->mdl_contacts->form_value('email_address'); ?>" /></dd>
				</dl>

				<dl>
					<dt><label><?php echo $this->lang->line('mobile_number'); ?>: </label></dt>
					<dd><input type="text" name="mobile_number" id="mobile_number" value="<?php echo $this->mdl_contacts->form_value('mobile_number'); ?>" /></dd>
				</dl>
					
				<dl>
					<dt><label><?php echo $this->lang->line('street_address'); ?>: </label></dt>
					<dd><input type="text" name="address" id="address" value="<?php echo $this->mdl_contacts->form_value('address'); ?>" /></dd>
				</dl>

				<dl>
					<dt><label><?php echo $this->lang->line('street_address_2'); ?>: </label></dt>
					<dd><input type="text" name="address_2" id="address_2" value="<?php echo $this->mdl_contacts->form_value('address_2'); ?>" /></dd>
				</dl>

				<dl>
					<dt><label><?php echo $this->lang->line('city'); ?>: </label></dt>
					<dd><input type="text" name="city" id="city" value="<?php echo $this->mdl_contacts->form_value('city'); ?>" /></dd>
				</dl>

				<dl>
					<dt><label><?php echo $this->lang->line('state'); ?>: </label></dt>
					<dd><input type="text" name="state" id="state" value="<?php echo $this->mdl_contacts->form_value('state'); ?>" /></dd>
				</dl>

				<dl>
					<dt><label><?php echo $this->lang->line('zip'); ?>: </label></dt>
					<dd><input type="text" name="zip" id="zip" value="<?php echo $this->mdl_contacts->form_value('zip'); ?>" /></dd>
				</dl>

				<dl>
					<dt><label><?php echo $this->lang->line('country'); ?>: </label></dt>
					<dd><input type="text" name="country" id="country" value="<?php echo $this->mdl_contacts->form_value('country'); ?>" /></dd>
				</dl>

				<dl>
					<dt><label><?php echo $this->lang->line('phone_number'); ?>: </label></dt>
					<dd><input type="text" name="phone_number" id="phone_number" value="<?php echo $this->mdl_contacts->form_value('phone_number'); ?>" /></dd>
				</dl>

				<dl>
					<dt><label><?php echo $this->lang->line('fax_number'); ?>: </label></dt>
					<dd><input type="text" name="fax_number" id="fax_number" value="<?php echo $this->mdl_contacts->form_value('fax_number'); ?>" /></dd>
				</dl>

				<dl>
					<dt><label><?php echo $this->lang->line('web_address'); ?>: </label></dt>
					<dd><input type="text" name="web_address" id="web_address" value="<?php echo $this->mdl_contacts->form_value('web_address'); ?>" /></dd>
				</dl>

				<dl>
					<dt><label><?php echo $this->lang->line('notes'); ?></label></dt>
					<dd style="vertical-align: top;"><textarea name="notes" id="notes" rows="5" cols="40"><?php echo form_prep($this->mdl_contacts->form_value('notes')); ?></textarea></dd>
				</dl>
				
				<input type="submit" id="btn_submit" name="btn_submit" value="<?php echo $this->lang->line('submit'); ?>" />
				<input type="submit" id="btn_cancel" name="btn_cancel" value="<?php echo $this->lang->line('cancel'); ?>" />

				</form>

			</div>

		</div>

	</div>
</div>

<?php $this->load->view('dashboard/sidebar'); ?>

<?php $this->load->view('dashboard/footer'); ?>