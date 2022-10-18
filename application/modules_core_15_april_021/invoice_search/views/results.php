<?php $this->load->view('dashboard/header'); ?>

<?php echo modules::run('invoices/widgets/generate_dialog'); ?>

<div class="grid_10" id="content_wrapper">

	<div class="section_wrapper">

		<h3 class="title_black"><?php echo (($quote_search == 1) ? $this->lang->line('quote_search') : $this->lang->line('invoice_search')); ?>
		</h3>

		<div class="content toggle no_padding">

			<?php $this->load->view('dashboard/system_messages'); ?>

			<?php if ($quote_search == 1) { ?>
			<?php $this->load->view('invoices/quote_table'); ?>
			<?php } else { ?>
			<?php $this->load->view('invoices/invoice_table'); ?>
			<?php } ?>
		</div>

	</div>

</div>

<?php //$this->load->view('dashboard/sidebar', array('side_block'=>'invoices/sidebar')); ?>

<?php $this->load->view('dashboard/footer'); ?>