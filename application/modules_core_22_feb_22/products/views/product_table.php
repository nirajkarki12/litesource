<table class="product_table" style="width: 100%;">
		<tr>
			<th width="17%" scope="col" class="first"><?php echo anchor('products/index/order_by/product_name', $this->lang->line('product_name')); ?></th>
			<th width="10%" scope="col" class="supplier"><?php echo anchor('products/index/order_by/supplier', $this->lang->line('supplier')); ?></th>
			<th width="45%" scope="col" ><?php echo $this->lang->line('product_description'); ?></th>
			<th width="10%" scope="col"><?php echo anchor('products/index/order_by/product_supplier_price', $this->lang->line('product_supplier_price')); ?></th>
			<th width="10%" scope="col"><?php echo anchor('products/index/order_by/product_base_price', $this->lang->line('product_base_price')); ?></th>
			<th width="8%" scope="col" class="last"><?php echo $this->lang->line('actions'); ?></th>
		</tr>
		<?php foreach ($products as $product) { ?>
			<tr>
				<td class="first entity_<?php echo $product->product_active; ?>"><?php echo $product->product_name; ?></td>
				<td><?php echo $product->supplier_name; ?></td>
				<td><?php echo $product->product_description; ?></td>
				<td class="col_price"><?php echo $product->supplier_price; ?></td>
				<td class="col_price"><?php echo display_currency($product->product_base_price); ?></td>
				<td class="last">
					<a href="<?php echo site_url('products/form/product_id/' . $product->product_id); ?>" title="<?php echo $this->lang->line('edit'); ?>">
						<?php echo icon('edit'); ?>
					</a>
					<a href="<?php echo site_url('products/delete/product_id/' . $product->product_id); ?>" title="<?php echo $this->lang->line('delete'); ?>" onclick="javascript:if(!confirm('<?php echo $this->lang->line('confirm_delete'); ?>')) return false">
						<?php echo icon('delete'); ?>
					</a>
				</td>
			</tr>

		<?php } ?>
</table>

<?php if ($this->mdl_products->page_links) { ?>
<div id="pagination">
	<?php echo $this->mdl_products->page_links; ?>
</div>
<?php } ?>