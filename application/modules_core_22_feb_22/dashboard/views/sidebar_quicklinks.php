<div class="section_wrapper">

	<h3 class="title_black"><?php echo $this->lang->line('quicklinks'); ?></h3>

	<ul class="quicklinks content toggle">
		<li><?php echo anchor('invoices/create/quote', $this->lang->line('create_quote')); ?></li>
		<li><?php echo anchor('invoices/index/is_quote/1', $this->lang->line('view_quotes')); ?></li>
		<li><?php echo anchor('invoices/create', $this->lang->line('create_invoice')); ?></li>
		<li><?php echo anchor('invoices/index', $this->lang->line('view_invoices')); ?></li>
		<?php if (!$this->mdl_mcb_data->setting('disable_invoice_payments')) { ?>
		<li><?php echo anchor('payments/form', $this->lang->line('enter_payment')); ?></li>
		<?php } ?>
		
		<li><?php echo anchor('invoice_search', $this->lang->line('search')); ?></li>

		<?php if ($this->session->userdata('global_admin')) { ?>
		<li class="last"><?php echo anchor('settings', $this->lang->line('system_settings')); ?></li>
		<?php } ?>
	</ul>

</div>