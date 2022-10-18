<table class="product_table" id="sortable_order_items" style="width: 100%;">

	<tr>
		<th scope="col" class="first" style="width: 15%;"><?php echo $this->lang->line('item_name'); ?></th>
		<th scope="col" style="width: 5%;"><?php echo $this->lang->line('item_type'); ?></th>
		<th scope="col" ><?php echo $this->lang->line('item_description'); ?></th>
		<th scope="col"  style="width: 10%;"><?php echo $this->lang->line('qty_ordered'); ?></th>
		<th scope="col"  style="width: 10%;"><?php echo $this->lang->line('qty_supplied'); ?></th>

	</tr>

	<tbody class="content">
	<?php foreach ($docket_items as $docket_item) { ?>

		<tr>
			<td class="first"><?php echo $docket_item->item_name; ?></td>
			<td><?php echo $docket_item->item_type; ?></td>
			<td><?php echo $docket_item->item_description; ?></td>
			<td><?php echo $docket_item->item_qty; ?></td>
			<td><?php echo $docket_item->docket_item_qty; ?></td>

		</tr>
	<?php } ?>

	</tbody>
</table>