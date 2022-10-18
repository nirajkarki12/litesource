<table class="category_table" style="width: 100%;">
		<tr>
			<th scope="col" class="first"><?php echo $this->lang->line('status'); ?></th>
			<th width="37%" scope="col"><?php echo anchor('categorys/index/order_by/category_name', $this->lang->line('category_name')); ?></th>
			<th width="8%" scope="col" class="last"><?php echo $this->lang->line('actions'); ?></th>
		</tr>
		<?php foreach ($category as $category_row) { ?>
			<tr>
				<td class="first entity_<?php echo $category_row->category_status_value; ?>"><?php echo $category_row->category_status_value; ?></td>
				<td ><?php echo $category_row->category_name; ?></td>

				<td class="last">
					<a href="<?php echo site_url('inventory/category_details/category_id/' . $category_row->category_id); ?>" title="<?php echo $this->lang->line('edit'); ?>">
						<?php echo icon('edit'); ?>
					</a>
					<?php if (!$this->mdl_mcb_data->setting('disable_delete_links')) { ?>
					<a href="<?php echo site_url('inventory/category_delete/category_id/' . $category_row->category_id); ?>" title="<?php echo $this->lang->line('delete'); ?>" onclick="javascript:if(!confirm('<?php echo $this->lang->line('confirm_delete'); ?>')) return false">
						<?php echo icon('delete'); ?>
					</a>
					<?php } ?>	
					
				</td>
			</tr>

		<?php } ?>
</table>

<?php if ($this->mdl_category->page_links) { ?>
<div id="pagination">
	<?php echo $this->mdl_category->page_links; ?>
</div>
<?php } ?>