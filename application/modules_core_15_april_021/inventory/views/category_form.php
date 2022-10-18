<?php $this->load->view('dashboard/header'); ?>

<div class="container_10" id="center_wrapper">

	<div class="grid_7" id="content_wrapper">

		<div class="section_wrapper">

			<h3 class="title_black"><?php echo $this->lang->line('category_form'); ?></h3>

			<?php $this->load->view('dashboard/system_messages'); ?>

			<div class="content toggle">

                <form method="post" action="<?php echo site_url($this->uri->uri_string()); ?>">
				
					<dl>
						<dt><label><?php echo $this->lang->line('category_name'); ?>: </label></dt>
						<dd><input type="text" name="category_name" id="category_name" value="<?php echo $this->mdl_category->form_value('category_name'); ?>" /></dd>
					</dl>

					<dl>
						<dt><label><?php echo $this->lang->line('category_status'); ?>: </label></dt>
						<dd><input type="checkbox" name="category_status" id="category_status" value="1" <?php if ($this->mdl_category->form_value('category_status') or (!$_POST and !uri_assoc('category_id'))) { ?>checked="checked"<?php } ?>/></dd>
					</dl>
										
					<input type="submit" id="btn_submit" name="btn_submit" value="<?php echo $this->lang->line('submit'); ?>" />
					<input type="submit" id="btn_cancel" name="btn_cancel" value="<?php echo $this->lang->line('cancel'); ?>" />

				</form>

			</div>

		</div>

	</div>
</div>

<?php //$this->load->view('dashboard/sidebar', array('side_block'=>'categorys/sidebar')); ?>

<?php $this->load->view('dashboard/footer'); ?>