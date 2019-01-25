<table style="width: 100%;">
		<tr>

			<th width="%" scope="col" class="first"><?php echo $this->lang->line('status'); ?></th>
            <?php if (isset($sort_links)) { ?>

				<th width="9%" scope="col"><?php echo anchor('invoices/index/order_by/invoice_id', $this->lang->line('invoice_number')); ?></th>
				<th width="8%" scope="col"><?php echo anchor('invoices/index/order_by/date', $this->lang->line('date')); ?></th>
				<th width="20%" scope="col" class="client"><?php echo anchor('invoices/index/order_by/client', $this->lang->line('client')); ?></th>
				<th width="9%" scope="col"><?php echo $this->lang->line('quote_number'); ?></th>
				<th scope="col"><?php echo anchor('invoices/index/order_by/project', $this->lang->line('project')); ?></th>										
				<th scope="col" class="last"><?php echo $this->lang->line('actions'); ?></th>
				
            <?php } else { ?>
				
				
                                <th width="9%" scope="col">Invoice #</th>
				<!--<th width="9%" scope="col"><?php echo (uri_seg_is('invoices') ? $this->lang->line('invoice_number') : $this->lang->line('quote_number')); ?></th>-->
				<th width="8%" scope="col"><?php echo $this->lang->line('date'); ?></th>
				<th width="20%" scope="col" class="client"><?php echo $this->lang->line('client'); ?></th>			
				<th width="9%" scope="col"><?php echo $this->lang->line('quote_number'); ?></th>
				<th scope="col"><?php echo $this->lang->line('project'); ?></th>
				<th width="14%" scope="col" class="last"><?php echo $this->lang->line('actions'); ?></th>
				
            <?php } ?>
		</tr>
		<?php foreach ($invoices as $invoice) { ?>

			<tr>
                                <?php // if($invoice->smart_status == '1'){ ?>
                            <!--<td class="first status_3">Closed</td>-->
                                <?php // }else{ ?>
                                <td class="first status_<?php if ($invoice->invoice_is_overdue) { ?>4<?php } else { echo $invoice->invoice_status_type; } ?>"><?php echo ($invoice->invoice_is_overdue) ? $this->lang->line('overdue') : $invoice->invoice_status; ?></td>
                                <?php // } ?>
				
				<td>
					<a href="<?php echo site_url('invoices/edit/invoice_id/' . $invoice->invoice_id); ?>" title="<?php echo $this->lang->line('edit'); ?>">
						<?php echo invoice_id($invoice); ?>
					</a>
				
				</td>
				<td><?php echo format_date($invoice->invoice_date_entered); ?></td>
				<td class="client">
					<?php if ($invoice->client_name) {
						echo anchor('clients/details/client_id/' . $invoice->client_id, character_limiter($invoice->client_name, 25)); 
					}?>
				</td>

				<td>
					<?php if ($invoice->invoice_quote_id) { ?>
                                            
                                            <a href="<?php echo base_url().'quotes/edit/invoice_id/' . $invoice->invoice_quote_id; ?>"><?php echo $invoice->invoice_quote_number; ?></a>
                                            
                                        <?php } ?> 			
				</td>
				<td class="client"><?php echo anchor('projects/details/project_id/' . $invoice->project_id, character_limiter($invoice->project_name, 40)); ?></td>
				<td class="last">
					<a href="<?php echo site_url('invoices/edit/invoice_id/' . $invoice->invoice_id); ?>" title="<?php echo $this->lang->line('edit'); ?>">
						<?php echo icon('edit'); ?>
					</a>
					<a href="<?php echo site_url('mailer/invoice_mailer/form/invoice_id').'/'.$invoice->invoice_id; ?>">
						<?php echo icon('generate_email'); ?>
					</a>
					<a href="<?php echo site_url('invoices/generate_pdf/invoice_id').'/'.$invoice->invoice_id; ?>">
						<?php echo icon('pdf'); ?>
					</a>
					<?php if (!$this->mdl_mcb_data->setting('disable_delete_links')) { ?>
					<a href="<?php echo site_url('invoices/delete/invoice_id/' . $invoice->invoice_id); ?>" title="<?php echo $this->lang->line('delete'); ?>" onclick="javascript:if(!confirm('<?php echo $this->lang->line('confirm_delete'); ?>')) return false">
						<?php echo icon('delete'); ?>
					</a>
					<?php } ?>				
				</td>
			</tr>

		<?php } ?>
</table>

<?php if ($this->mdl_invoices->page_links) { ?>
<div id="pagination">
	<?php echo $this->mdl_invoices->page_links; ?>
</div>
<?php } ?>