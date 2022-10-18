<?php $this->load->view('dashboard/header'); ?>

<div class="grid_7" id="content_wrapper">

	<div class="section_wrapper">

		<h3 class="title_black"><?php echo $this->lang->line('suppliers'); ?><?php $this->load->view('dashboard/btn_add', array('btn_name'=>'btn_add_supplier', 'btn_value'=>$this->lang->line('add_supplier'))); ?></h3>

		<?php $this->load->view('dashboard/system_messages'); ?>

		<div class="content toggle no_padding">
			<p>Sorted By: <?php echo $order_by; ?></p>
			<table>
				<tr>
					<th scope="col" class="first"><?php echo anchor('suppliers/index/order_by/supplier_id', $this->lang->line('id')); ?></th>
					<th scope="col" ><?php echo anchor('suppliers/index/order_by/supplier_sort_index', $this->lang->line('sort_index')); ?></th>
					<th scope="col" ><?php echo anchor('suppliers/index/order_by/name', $this->lang->line('name')); ?></th>
					<th scope="col" class="last"><?php echo $this->lang->line('actions'); ?></th>
				</tr>
				<?php foreach ($suppliers as $supplier) { ?>
				<tr>
					<td class="first"><?php echo $supplier->supplier_id; ?></td>
					<td><?php echo $supplier->supplier_sort_index; ?></td>
					<td nowrap="nowrap"><?php echo $supplier->supplier_name; ?></td>
					<td class="last">
						<a href="<?php echo site_url('suppliers/details/supplier_id/' . $supplier->supplier_id); ?>" title="<?php echo $this->lang->line('view'); ?>">
							<?php echo icon('zoom'); ?>
						</a>
						<a href="<?php echo site_url('suppliers/form/supplier_id/' . $supplier->supplier_id); ?>" title="<?php echo $this->lang->line('edit'); ?>">
							<?php echo icon('edit'); ?>
						</a>
						<a href="<?php echo site_url('suppliers/delete/supplier_id/' . $supplier->supplier_id); ?>" title="<?php echo $this->lang->line('delete'); ?>" onclick="javascript:if(!confirm('<?php echo $this->lang->line('supplier_delete_warning'); ?>')) return false">
							<?php echo icon('delete'); ?>
						</a>
					</td>
				</tr>
				<?php } ?>
			</table>

			<?php if ($this->mdl_suppliers->page_links) { ?>
			<div id="pagination">
				<?php echo $this->mdl_suppliers->page_links; ?>
			</div>
			<?php } ?>

		</div>

	</div>

</div>

<?php //$this->load->view('dashboard/sidebar', array('side_block'=>'suppliers/sidebar')); ?>

<?php $this->load->view('dashboard/footer'); ?>