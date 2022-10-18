<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>
		<?php echo $this->lang->line('docket_number'); ?>
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
	<?php $this->load->view('delivery_dockets/templates/default_footer', $docket); ?>
	</htmlpagefooter>
 
	<sethtmlpagefooter name="litesource_footer" value="on" />
	mpdf-->

<table class="info_header">
	<tr>
		<td class="header">
			<?php echo $this->lang->line('delivery_docket_number'); ?>
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
    <?php if ($docket->invoice_client_order_number) { ?>
    <tr>
        <td class="header" width="25%">
            <?php echo $this->lang->line('purchase_order').':'; ?>
        </td>
        <td class="data">
            <?php echo $docket->invoice_client_order_number; ?>
        </td>
    </tr>
    <?php } ?>
	<tr>
		<td class="header" width="12%">
			<?php echo $this->lang->line('date').':'; ?>
		</td>
		<td class="data">
			<?php echo date('d/m/Y', $docket->docket_date_entered); ?>
		</td>
		<td width="10%"></td>
		<td class="header" width="40%">
			<?php echo $this->lang->line('deliver_to').':'; ?>
		</td>
	</tr>
	<tr>
		<td class="header">
			<?php echo $this->lang->line('attention').':'; ?>
		</td>
		<td class="data">
			<?php echo $docket->contact_name; ?><br />
			<?php echo $docket->client_name; ?>
		</td>
		<td></td>
		<td rowspan="2" class="data delivery_address">
			<?php echo format_delivery_address($address); ?>
		</td>
	</tr>
	<tr>
		<td class="header">
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
		<th class="item_header" width="20%">
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
			<?php echo $this->lang->line('qty_supplied'); ?>
		</th>
		<th class="item_header" width="13%" align="right">
			<?php echo $this->lang->line('qty_received'); ?>
		</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($docket_items as $item) { ?>
	<tr class="item">
		<td class="data">
                    <?= nl2br($item->item_name); ?>
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
			<?php echo format_qty($item->docket_item_qty); ?>
		</td>
		<td align="right">

		</td>
	</tr>
		<?php } ?>
	</tbody>
</table>


</body>
</html>