<table id="sortable_invoice_items" style="width: 100%;">
	
	<tr>
		<th scope="col" class="first" style="width: 25%;"><?php echo $this->lang->line('item_name'); ?></th>
		<th scope="col" style="width: 10%;"><?php echo $this->lang->line('item_type'); ?></th>
		<th scope="col" style="width: 35%;"><?php echo $this->lang->line('item_description'); ?></th>
		<th scope="col" style="width: 10%;"><?php echo $this->lang->line('quantity'); ?></th>
		<th scope="col"  style="width: 10%;"><?php echo $this->lang->line('unit_price'); ?></th>
		<th scope="col" class="last" style="width: 10%;"><?php echo $this->lang->line('actions'); ?></th>
	</tr>

	<tbody class="content">
	<?php foreach ($invoice_items as $invoice_item) {
		if(!uri_assoc('invoice_item_id', 4) OR uri_assoc('invoice_item_id', 4) <> $invoice_item->invoice_item_id) { ?>
		<tr>
			<td class="first"><?php echo $invoice_item->item_name; ?></td>
			<td><?php echo $invoice_item->item_type; ?></td>
			<td><?php echo character_limiter($invoice_item->item_description, 40); ?></td>
			<td><?php echo format_qty($invoice_item->item_qty); ?></td>
			<td><?php echo display_currency($invoice_item->item_price); ?></td>
			<td class="last">
				<a href="<?php echo site_url('invoices/items/form/invoice_id/' . uri_assoc('invoice_id') . '/invoice_item_id/' . $invoice_item->invoice_item_id); ?>" title="<?php echo $this->lang->line('edit'); ?>">
					<?php echo icon('edit'); ?>
				</a>
				<a href="<?php echo site_url('invoices/items/delete/invoice_id/' . uri_assoc('invoice_id') . '/invoice_item_id/' . $invoice_item->invoice_item_id); ?>" title="<?php echo $this->lang->line('delete'); ?>" onclick="javascript:if(!confirm('<?php echo $this->lang->line('confirm_delete'); ?>')) return false">
					<?php echo icon('delete'); ?>
				</a>
			</td>
		</tr>
	<?php } } ?>
		
	</tbody>
</table>