
<table class="info_header">
	<tr>
		<td class="header">
			<?php echo ($invoice->invoice_is_quote ? $this->lang->line('quotation') : $this->lang->line('tax_invoice')); ?>
		</td>
		<td class="data">
			<?php echo $invoice->invoice_number; ?>
		</td>
	</tr>
</table>


<table class="info" style="width: 55%;">
	<?php if ($invoice->invoice_client_order_number) { ?>
	<tr>
		<td class="header" width="25%">
			<?php echo $this->lang->line('purchase_order').':'; ?>
		</td>
		<td class="data">
			<?php echo $invoice->invoice_client_order_number; ?>
		</td>
	</tr>
	<?php } ?>
	<tr>
		<td class="header" width="25%">
			<?php echo $this->lang->line('date').':'; ?>
		</td>
		<td class="data">
			<?php echo invoice_date_entered($invoice); ?>
		</td>
	</tr>
	<tr>
		<td class="header">
			<?php echo $this->lang->line('attention').':'; ?>
		</td>
		<td class="data">
			<?php echo invoice_to_contact_name($invoice); ?><br />
			<?php echo invoice_to_client_name($invoice); ?>
		</td>
	</tr>
	<tr>
		<td class="header">
			<?php echo $this->lang->line('project').':'; ?>
		</td>
		<td class="data">
			<?php echo $invoice->project_name; ?>
		</td>
	</tr>


	<tr><td><br/><br/></td></tr>

</table>



<table class="items">
	<thead>
	<tr>
		<th class="item_header" width="5%" align="left">
			<?php echo $this->lang->line('qty'); ?>
		</th>
		<th class="item_header" width="15%">
			<?php echo nl2br($this->lang->line('catalog_number')); ?>
		</th>
		<th class="item_header" width="7%">
			<?php echo nl2br($this->lang->line('item_type')); ?>
		</th>
		<th class="item_header">
			<?php echo nl2br($this->lang->line('description')); ?>
		</th>
		<th class="item_header" width="12%" align="right">
			<?php echo $this->lang->line('price'); ?>
		</th>
		<th class="item_header" width="13%" align="right">
			<?php echo $this->lang->line('total'); ?>
		</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($invoice->invoice_items as $item) { ?>
	<tr class="item">
		<td>
			<?php echo invoice_item_qty($item); ?>
		</td>
		<td>
			<?php echo invoice_item_name($item, 16); ?>
		</td>
		<td class="item_type">
			<?php echo invoice_item_type($item); ?>
		</td>
		<td >
			<?php echo invoice_item_description($item); ?>
		</td>
		<td align="right">
			<?php echo invoice_item_unit_price($item); ?>
		</td>
		<td align="right">
			<?php echo invoice_itemlevel_subtotal($item); ?>
		</td>
	</tr>
		<?php } ?>

	</tbody>

</table>
<br />
<table class="summary">
	<thead>
	<tr>
		<th width="20%"></th>
		<th></th>
		<th witdh="10%"></th>
		<th width="25%"></th>
	</tr>
	</thead>
	<tbody>
	<tr class="divider">
		<td colspan="4"></td>
	</tr>
	<?php if (!$invoice->invoice_is_quote) { ?>
	<tr>
		<td>Banking Details:</td>
		<td colspan="3"></td>
	</tr>
	<tr>
		<td >Company Name:</td>
		<td class="default_detail">LiTEsource and Controls Pty Ltd</td>
		<td colspan="2"></td>
	</tr>
	<tr>
		<td >ABN:</td>
		<td class="default_detail">25 136 501 445</td>
		<td colspan="2"></td>
	</tr>
	<tr>
		<td>Bank BSB:</td>
		<td class="default_detail">ANZ 012241</td>
		<td colspan="2"></td>
	</tr>
	<tr>
		<td>ACCT:</td>
		<td class="default_detail">901354228</td>
		<td colspan="2"></td>
	</tr>
	<tr>
		<td colspan="4"></td>
	</tr>
		<?php } ?>
	<tr>
		<td>Payment Terms:</td>
		<td colspan="3" class="default_detail">
			<?php echo (!$invoice->invoice_is_quote ? invoice_payment_terms($invoice) : $this->lang->line('default_payment_terms')); ?>

		</td>
	</tr>
	<?php if ($invoice->invoice_has_notes) { ?>
	<tr class="divider">
		<td colspan="4"></td>
	</tr>
	<tr>
		<td colspan="3" class="default_notes">Notes:</td>
	</tr>
	<tr>
		<td colspan="3" class="default_notes"><?php echo invoice_notes($invoice); ?></td>
	</tr>
	<?php } ?>

	</tbody>

</table>