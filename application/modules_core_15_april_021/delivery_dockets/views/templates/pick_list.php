<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>
		<?php echo $this->lang->line('pick_list'); ?>
		<?php echo $docket->docket_number; ?>
	</title>
	<link href="<?php echo base_url(); ?>assets/style/css/output.css" rel="stylesheet" type="text/css" media="all" />

</head>
<body>

<!--mpdf
	
	<htmlpageheader name="litesource_firstpage_header">
	<?php $this->load->view('invoices/invoice_templates/default_firstpage_header', $user); ?>
	</htmlpageheader>
 
	<sethtmlpageheader name="litesource_firstpage_header" value="on" show-this-page="1"/>
	
	mpdf-->

<!--mpdf
	
	<htmlpageheader name="litesource_header">
	<?php $this->load->view('invoices/invoice_templates/default_header', $user); ?>
	</htmlpageheader>
 
	<sethtmlpageheader name="litesource_header" page="OE" value="on" />
	
	mpdf-->

<!--mpdf
	<htmlpagefooter name="litesource_footer">
	<?php $this->load->view('delivery_dockets/templates/pick_list_footer', $docket); ?>
	</htmlpagefooter>
 
	<sethtmlpagefooter name="litesource_footer" value="on" />
	mpdf-->

<table class="info_header">
	<tr>
		<td class="header">
			<?php echo $this->lang->line('pick_list'); ?>
		</td>
		<td class="data">
			<?php echo $docket->docket_number; ?>
		</td>
	</tr>

</table>

<table class="info">

	<tr>
		<td class="header" width="12%">
			<?php echo $this->lang->line('invoice_number').':'; ?>
		</td>
		<td class="data">
			<?php echo $docket->invoice_number; ?>
		</td>
	</tr>
	<tr>
		<td class="header" width="12%">
			<?php echo $this->lang->line('date').':'; ?>
		</td>
		<td class="data">
			<?php echo date('d/m/Y', $docket->docket_date_entered); ?>
		</td>
	</tr>
	<tr>
		<td class="header" width="12%">
			<?php echo $this->lang->line('project').':'; ?>
		</td>
		<td class="data">
			<?php echo $docket->project_name; ?>
		</td>
	</tr>


	<tr><td><br/><br/></td></tr>

</table>

<table class="items">
	<thead>
	<tr>
		<th class="item_header" width="15%">
			<?php echo $this->lang->line('catalog_number'); ?>
		</th>
		<th class="item_header" width="7%">
			<?php echo $this->lang->line('item_type'); ?>
		</th>
		<th class="item_header">
			<?php echo $this->lang->line('description'); ?>
		</th>
		<th class="item_header" width="5%" align="right">
			<?php echo $this->lang->line('qty'); ?>
		</th>
		<th class="item_header" width="12%" align="right">
			<?php echo $this->lang->line('qty_delivered'); ?>
		</th>
		<th class="item_header" width="12%">
			<?php echo $this->lang->line('qty_picked'); ?>
		</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($docket_items as $item) { ?>
	<tr class="item">
		<td class="data">
			<?php echo wordwrap($item->item_name, 16, "\n", true); ?>
		</td>
		<td class="item_type">
			<?php echo $item->item_type; ?>
		</td>
		<td>
			<?php echo $item->item_description; ?>
		</td>
		<td align="right">
			<?php echo format_qty($item->item_qty); ?>
		</td>
		<td align="right">
			<?php echo format_qty($item->delivered_item_qty); ?>
		</td>
		<td align="right" class=<?php echo ($item->docket_item_qty > 0.001 ? "item_pick" : "item_pick_none"); ?> >
			<?php echo format_qty($item->docket_item_qty); ?>
		</td>
	</tr>
		<?php } ?>
	</tbody>
</table>


</body>
</html>