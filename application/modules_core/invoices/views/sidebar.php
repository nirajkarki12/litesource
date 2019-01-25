<div class="section_wrapper">

	<h3 class="title_white"><?php echo $this->lang->line('projects'); ?></h3>

	<ul class="quicklinks content toggle">
		<li><?php echo anchor('invoices/create/quote', $this->lang->line('create_quote')); ?></li>
		<li><?php echo anchor('invoices/index/is_quote/1', $this->lang->line('view_quotes')); ?></li>
		<li><?php echo anchor('orders/create', $this->lang->line('create_order')); ?></li>
		<li><?php echo anchor('orders/index', $this->lang->line('view_orders')); ?></li>
		<li><?php echo anchor('invoices/create', $this->lang->line('create_invoice')); ?></li>
		<li><?php echo anchor('invoices/index', $this->lang->line('view_invoices')); ?></li>
		
		
		<?php /*
		<li><?php echo anchor('invoice_items', $this->lang->line('invoice_items')); ?></li>
		 */?>
		<li><?php echo anchor('invoice_search', $this->lang->line('search')); ?></li>
	</ul>

</div>