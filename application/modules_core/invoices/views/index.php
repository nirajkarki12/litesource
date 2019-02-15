<?php $this->load->view('dashboard/header'); ?>

<?php echo modules::run('invoices/widgets/generate_dialog');?>

<div class="grid_12" id="content_wrapper">

	<div class="section_wrapper">

		<h3 class="title_black">
        <?php echo $this->lang->line(uri_seg(1)); ?>

			<?php $this->load->view('dashboard/btn_add', array('btn_name'=>(uri_seg_is('invoices')) ? 'btn_add_invoice' : 'btn_add_quote', 'btn_value'=>(uri_seg_is('invoices')) ? $this->lang->line('create_invoice') : $this->lang->line('create_quote'))); ?>

			<?php //$this->load->view('dashboard/btn_add', array('btn_name'=>(!uri_assoc('is_quote')) ? 'btn_add_invoice' : 'btn_add_quote', 'btn_value'=>(!uri_assoc('is_quote')) ? $this->lang->line('create_invoice') : $this->lang->line('create_quote'))); ?>
		
			<?php //$this->load->view('dashboard/btn_add', array('btn_name'=>(!uri_assoc('is_quote')) ? 'btn_invoice_search' : 'btn_quote_search', 'btn_value'=>(!uri_assoc('is_quote')) ? $this->lang->line('invoice_search') : $this->lang->line('quote_search'))); ?>
		
		</h3>

		<div class="content toggle no_padding">

			<?php $this->load->view('dashboard/system_messages');  ?>

			<?php $this->load->view('invoice_grid'); ?>

		</div>

	</div>

</div>

<?php // $this->load->view('dashboard/sidebar', array('side_block'=>'invoices/sidebar')); ?>

<?php $this->load->view('dashboard/footer'); ?>