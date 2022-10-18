<table style="width: 100%;">
		<tr>

            <?php if (isset($sort_links)) { ?>
			<th width="9%" scope="col" class="first"><?php echo anchor('orders/index/order_by/status',  $this->lang->line('status')); ?></th>
			<th width="10%" scope="col"><?php echo anchor('orders/index/order_by/order_number', $this->lang->line('order_number')); ?></th>
			<th width="8%" scope="col"><?php echo anchor('orders/index/order_by/date', $this->lang->line('date')); ?></th>
			<th width="15%" scope="col" class="client"><?php echo anchor('orders/index/order_by/supplier', $this->lang->line('supplier')); ?></th>
			<th width="9%" scope="col"><?php echo anchor('orders/index/order_by/invoice', $this->lang->line('quote_number')); ?></th>
			<th scope="col"><?php echo anchor('orders/index/order_by/project', $this->lang->line('project')); ?></th>
			<th width="9%" scope="col" class="col_amount"><?php echo $this->lang->line('amount'); ?></th>
			<th width="15%" scope="col" class="last"><?php echo $this->lang->line('actions'); ?></th>
            <?php } else { ?>
			<th width="9%" scope="col" class="first"><?php echo $this->lang->line('status'); ?></th>
			<th scope="col"><?php echo $this->lang->line('order_number'); ?></th>
			<th scope="col"><?php echo $this->lang->line('date'); ?></th>
			<th scope="col" class="client"><?php echo $this->lang->line('supplier'); ?></th>
			<th scope="col"><?php echo $this->lang->line('quote_number'); ?></th>
			<th scope="col"><?php echo $this->lang->line('project'); ?></th>
			<th scope="col" class="col_amount"><?php echo $this->lang->line('amount'); ?></th>
			<th scope="col" class="last"><?php echo $this->lang->line('actions'); ?></th>
            <?php } ?>
		</tr>
		<?php foreach ($orders as $order) { 
                    if($order->order_date_emailed > '1'){
                        $order->order_date_entered = $order->order_date_emailed;
                    } ?>

			<tr>
				<td class="first status_<?php echo $order->order_status_type; ?>"><?php echo $order->order_status; ?></td>
				<td><?php echo anchor('orders/edit/order_id/' . $order->order_id, $order->order_number); ?></td>					
				<td><?php echo format_date($order->order_date_entered); ?></td>
				<td class="client"><?php echo anchor('clients/details/client_id/' . $order->supplier_id, character_limiter($order->client_name, 25)); ?></td>
				<td>
					<?php if ($order->invoice_id) { ?>
					<?php if ($order->invoice_is_quote) { ?>
					<?php echo anchor('quotes/edit/invoice_id/' . $order->invoice_id, $order->invoice_number); ?>
					<?php } else { ?>
					<?php echo anchor('invoices/edit/invoice_id/' . $order->invoice_id, $order->invoice_number); ?>
					<?php } ?>
					<?php } ?>
				</td>
				<td>
					<?php if ($order->project_name) { ?>
					<?php echo anchor('projects/details/project_id/' . $order->project_id, character_limiter($order->project_name, 30)); ?>
					<?php } ?>
				</td>
				<td class="col_amount"><?php echo $order->currency_symbol_left.$order->order_total.$order->currency_symbol_right; ?></td>
				<td class="last">
					<a href="<?php echo site_url('orders/edit/order_id/' . $order->order_id); ?>" title="<?php echo $this->lang->line('edit'); ?>">
						<?php echo icon('edit'); ?>
					</a>
					<a href="<?php echo site_url('mailer/order_mailer/form/order_id').'/'.$order->order_id.'/order_template/default'; ?>">
						<?php echo icon('generate_email'); ?>
					</a>
					<a href="<?php echo site_url('orders/generate_pdf/order_id').'/'.$order->order_id; ?>">
						<?php echo icon('pdf'); ?>
					</a>
					<?php if (!$this->mdl_mcb_data->setting('disable_delete_links')) { ?>
					<a href="<?php echo site_url('orders/delete/order_id/' . $order->order_id); ?>" title="<?php echo $this->lang->line('delete'); ?>" onclick="javascript:if(!confirm('<?php echo $this->lang->line('confirm_delete'); ?>')) return false">
						<?php echo icon('delete'); ?>
					</a>
					<?php } ?>	
					
				</td>
			</tr>

		<?php } ?>
</table>

<?php if ($this->mdl_orders->page_links) { ?>
<div id="pagination">
	<?php echo $this->mdl_orders->page_links; ?>
</div>
<?php } ?>