<table class="project_table" style="width: 100%;">
		<tr>
			<th scope="col" class="first"><?php echo $this->lang->line('status'); ?></th>
			<th width="37%" scope="col"><?php echo anchor('projects/index/order_by/project_name', $this->lang->line('project_name')); ?></th>
			<th width="50%" scope="col"><?php echo anchor('projects/index/order_by/project_specifier', $this->lang->line('project_specifier')); ?></th>
			<th width="8%" scope="col" class="last"><?php echo $this->lang->line('actions'); ?></th>
		</tr>
		<?php foreach ($projects as $project) { ?>
			<tr>
				<td class="first entity_<?php echo $project->project_active; ?>"><?php echo $project->project_status; ?></td>
				<td ><?php echo $project->project_name; ?></td>
				<td><?php echo $project->project_specifier; ?></td>
				<td class="last">
					<a href="<?php echo site_url('projects/details/project_id/' . $project->project_id); ?>" title="<?php echo $this->lang->line('edit'); ?>">
						<?php echo icon('edit'); ?>
					</a>
					<?php if (!$this->mdl_mcb_data->setting('disable_delete_links')) { ?>
					<a href="<?php echo site_url('projects/delete/project_id/' . $project->project_id); ?>" title="<?php echo $this->lang->line('delete'); ?>" onclick="javascript:if(!confirm('<?php echo $this->lang->line('confirm_delete'); ?>')) return false">
						<?php echo icon('delete'); ?>
					</a>
					<?php } ?>	
					
				</td>
			</tr>

		<?php } ?>
</table>

<?php if ($this->mdl_projects->page_links) { ?>
<div id="pagination">
	<?php echo $this->mdl_projects->page_links; ?>
</div>
<?php } ?>