<?php $this->load->view('dashboard/header', array('header_insert'=>'projects/details_header')); ?>

<script type="text/javascript" src="<?php echo base_url(); ?>assets/jquery/jquery.autogrow-textarea.js"></script>

<script type="text/javascript">

	$(document).ready(function(){
		
		$('#project_description').autogrow();
			
	
	});

</script>

<script type="text/javascript">
	$(function(){
		$('#tabs').tabs({ selected: <?php echo $tab_index; ?> });
	});
</script>

<div class="container_12" id="center_wrapper">

	<div class="grid_12" id="content_wrapper">

		<div class="section_wrapper">

			<h3 class="title_black"><?php echo $project->project_name; ?>
				
			</h3>
			
			<?php $this->load->view('dashboard/system_messages'); ?>
			
			<div class="content toggle">

				<div id="tabs">

					<ul>
						<li><a href="#tab_details"><?php echo $this->lang->line('project'); ?></a></li>
						
						<li><a href="#tab_quotes"><?php echo $this->lang->line('quotes'); ?></a></li>
						<?php if ($this->session->userdata('global_admin')) { ?>
						<li><a href="#tab_invoices"><?php echo $this->lang->line('invoices'); ?></a></li>						
						<li><a href="#tab_orders"><?php echo $this->lang->line('orders'); ?></a></li>
						<?php } ?>
					</ul>
					
					<div id="tab_details">	
						<?php $this->load->view('tab_details'); ?>
					</div>

					<div id="tab_quotes">						
						<?php $this->load->view('invoices/quote_table'); ?>					
					</div>
					
					<?php if ($this->session->userdata('global_admin')) { ?>
					<div id="tab_invoices">						
						<?php $this->load->view('invoices/invoice_table_project'); ?>						
					</div>	
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