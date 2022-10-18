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
	<?php $this->load->view('invoices/invoice_templates/default_firstpage_header', $docket['user']); ?>
	</htmlpageheader>
 
	<sethtmlpageheader name="litesource_firstpage_header" value="on" show-this-page="1"/>
	
	mpdf-->

	<!--mpdf
	
	<htmlpageheader name="litesource_header">
	<?php $this->load->view('invoices/invoice_templates/default_header', $docket['user']); ?>
	</htmlpageheader>
 
	<sethtmlpageheader name="litesource_header" page="OE" value="on" />
	
	mpdf-->
	<!--mpdf
	<htmlpagefooter name="litesource_footer">
	<?php $this->load->view('invoices/invoice_templates/default_invoice_footer', $invoice); ?>
	</htmlpagefooter>
 
	<sethtmlpagefooter name="litesource_footer" value="on" />
	mpdf-->
		
	<?php $this->load->view('invoices/invoice_templates/default_docket_invoice_data', array('invoice' => $invoice,'docket' => $docket)); ?>
	</body>
</html>