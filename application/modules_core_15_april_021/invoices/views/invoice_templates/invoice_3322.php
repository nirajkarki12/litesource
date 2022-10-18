<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>
        <?php echo $this->lang->line('tax_invoice'); ?>&nbsp;
        <?php echo invoice_id($invoice); ?>
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
	<?php $this->load->view('invoices/invoice_templates/default_invoice_footer', $invoice); ?>
	</htmlpagefooter>
 
	<sethtmlpagefooter name="litesource_footer" value="on" />
	mpdf-->


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


<table class="info" style="width: 80%;">
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
        <td class="header" width="15%">
            <?php echo $this->lang->line('date').':'; ?>
        </td>
        <td class="data">
            <?php echo invoice_date_entered($invoice); ?>
        </td>
        <td class="header" width="15%">&nbsp;</td>
    </tr>
    <tr>
        <td class="header">Invoice To:
        </td>
        <td class="data">
            St George Finance Ltd<br />
            ABN 99 001 094 471<br />
            Level 4, 1 Chifley Square<br />
            SYDNEY NSW 2000<br /><br />
        </td>
        <td class="header">Delivery To:
        </td>
        <td class="data">
            JBJ Trading Pty Ltd<br />
            ABN 41 159 937 792<br />
            Shop 5040 "Westfield"<br />
            500 Oxford Street<br />
            BONDI JUNCTION NSW 2022<br />
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
    <tr>
        <td><?php echo $this->lang->line('total'); ?></td>
        <td></td>
        <td>
            <?php echo $this->lang->line('exclusive_of').' '.$invoice->tax_rate_name; ?>
        </td>
        <td>
            <?php echo invoice_item_subtotal($invoice); ?>
        </td>
    </tr>
    <tr>
        <td colspan="2"></td>
        <td>
            <?php echo $invoice->tax_rate_percent_name; ?>
        </td>
        <td>
            <?php echo invoice_itemtax($invoice); ?>
        </td>
    </tr>
    <tr>
        <td colspan="2"></td>
        <td>
            <?php echo $this->lang->line('inclusive_of').' '.$invoice->tax_rate_percent_name; ?>
        </td>
        <td>
            <?php echo invoice_total($invoice); ?>
        </td>
    </tr>


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
        <!--td class="default_detail">901354228</td-->
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
        <td colspan="3" class="default_notes"><?php echo invoice_notes($invoice); ?></td>
    </tr>
        <?php } ?>

    </tbody>

</table>


</body>
</html>