<form method="post" action="<?php echo site_url($this->uri->uri_string()); ?>">

	<dl>
		<dt><label><?php echo $this->lang->line('project_name'); ?>: </label></dt>
		<dd><input type="text" name="project_name" id="project_name" value="<?php echo $this->mdl_projects->form_value('project_name'); ?>" /></dd>
	</dl>

	<dl>
		<dt><label><?php echo $this->lang->line('project_specifier'); ?>: </label></dt>
		<dd><input type="text" name="project_specifier" id="project_specifier" value="<?php echo $this->mdl_projects->form_value('project_specifier'); ?>" /></dd>
	</dl>

	<dl>
		<dt><label><?php echo $this->lang->line('project_description'); ?>: </label></dt>
		<dd><textarea class="big_textarea" name="project_description" id="project_description"><?php echo $this->mdl_projects->form_value('project_description'); ?></textarea></dd>
	</dl>

	<dl>
		<dt><label><?php echo $this->lang->line('project_active'); ?>: </label></dt>
		<dd><input type="checkbox" name="project_active" id="project_active" value="1" <?php if ($this->mdl_projects->form_value('project_active') or (!$_POST and !uri_assoc('project_id'))) { ?>checked="checked"<?php } ?> /></dd>
	</dl>

	<input type="submit" id="btn_submit" name="btn_submit" value="<?php echo $this->lang->line('submit'); ?>" />
	<input type="submit" id="btn_cancel" name="btn_cancel" value="<?php echo $this->lang->line('cancel'); ?>" />

</form>