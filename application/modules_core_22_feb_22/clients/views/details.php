<?php $this->load->view('dashboard/header', array('header_insert'=>'clients/details_header')); ?>

<?php echo modules::run('invoices/widgets/generate_dialog'); ?>
<script type="text/javascript">
	$(function(){
		$('#tabs').tabs({ selected: <?php echo $tab_index; ?> });
	});
</script>

<div class="container_12" id="center_wrapper">

	<div class="grid_12" id="content_wrapper">

		<div class="section_wrapper">

			<h3 class="title_black"><?php echo $client->client_name; ?>
				
				<form method="post" action="<?php echo site_url($this->uri->uri_string()); ?>" style="display: inline;">
                                    <a href="<?php echo site_url($this->uri->uri_string()); ?>"  onclick="return confirm('Are you sure you want to delete?');"><input type="submit" name="btn_delete_client" style="float: right; margin-top: 10px; margin-right: 10px;" value="<?php echo $this->lang->line('delete_client'); ?>"></a>
				<input type="submit" name="btn_add_contact" style="float: right; margin-top: 10px; margin-right: 10px;" value="<?php echo $this->lang->line('add_contact'); ?>" />
                    <?php $this->load->view('dashboard/btn_add', array('btn_name'=>'btn_statement_download_pdf', 'btn_value'=>$this->lang->line('pdf_statement_download'))); ?>
                                
				</form>
                            

			</h3>
			
			<?php $this->load->view('dashboard/system_messages'); ?>
			
			<div class="content toggle">

				<div id="tabs">

					<ul>
						<li><a href="#tab_details"><?php echo $this->lang->line('client'); ?></a></li>
						<li><a href="#tab_contacts"><?php echo $this->lang->line('contacts'); ?></a></li>
						<li><a href="#tab_quotes"><?php echo $this->lang->line('quotes'); ?></a></li>
<!--						<li><a href="#tab_invoices"><?php echo $this->lang->line('invoices'); ?></a></li>-->
                                                <li><a href="#tab_docket"><?php echo $this->lang->line('dockets'); ?></a></li>
						<?php if ($client->client_is_supplier == 1) { ?>
						<li><a href="#tab_orders"><?php echo $this->lang->line('orders'); ?></a></li>
						<?php } ?>
					</ul>
					
					<div id="tab_details">
						
						<?php $this->load->view('tab_details'); ?>
					</div>

					<div id="tab_contacts">
						
						<?php $this->load->view('contact_table'); ?>

					</div>

					<div id="tab_quotes">
						
						<?php $this->load->view('invoices/quote_table'); ?>
						
					</div>
					
<!--					<div id="tab_invoices">
						
						<?php //$this->load->view('invoices/invoice_table'); ?>
						
					</div>-->
                                    	
					<div id="tab_docket">
						
						<?php $this->load->view('invoices/docket_table'); ?>
						
					</div>
                                    
					<?php if ($client->client_is_supplier == 1) { ?>
					<div id="tab_orders">
						<?php $this->load->view('orders/order_table'); ?>
					</div>
					<?php } ?>
				</div>

			</div>

		</div>

	</div>

</div>

<?php $this->load->view('dashboard/footer'); ?>
