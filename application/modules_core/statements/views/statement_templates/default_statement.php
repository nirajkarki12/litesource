<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>
        <?php echo $this->lang->line('statement'); ?>
    </title>
    <link href="<?php echo base_url(); ?>assets/style/css/output.css" rel="stylesheet" type="text/css" media="all" />

</head>
<body>

<!--mpdf

	<htmlpageheader name="litesource_firstpage_header">
	<?php $this->load->view('invoices/invoice_templates/default_firstpage_header'); ?>
	</htmlpageheader>

	<sethtmlpageheader name="litesource_firstpage_header" value="on" show-this-page="1"/>

	mpdf-->

<!--mpdf

	<htmlpageheader name="litesource_header">
	<?php $this->load->view('invoices/invoice_templates/default_header', $user); ?>
	</htmlpageheader>

	<sethtmlpageheader name="litesource_header"  value="on" />

	mpdf-->

<!--mpdf
	<htmlpagefooter name="litesource_footer">
	<?php $this->load->view('statements/statement_templates/default_statement_footer', $user); ?>
	</htmlpagefooter>

	<sethtmlpagefooter name="litesource_footer" value="on" />
	mpdf-->

<table class="info_header">
    <tr>
        <td class="header">
            <?php echo $this->lang->line('statement'); ?>
        </td>
    </tr>

</table>

<table class="info">

    <tr>
        <td class="header" width="12%">
            <?php echo $this->lang->line('date').':'; ?>
        </td>
        <td class="data">
            <?php echo date('d/m/Y', $date); ?>
        </td>

    </tr>
    <tr>
        <td class="header">
            <?php echo $this->lang->line('attention').':'; ?>
        </td>
        <td class="data">
            <?php echo $client->client_name; ?>
        </td>

    </tr>
    <tr>
        <td class="header">
            <?php echo $this->lang->line('total_owing').':'; ?>
        </td>
        <td class="data">
            <?php // echo display_currency($total_owed); ?>
            <?php   foreach ($delivery_docket as $value) { $sum+= $value->price_with_tax; }
            echo '$'.format_number($sum); ?>
            
        </td>

    </tr>
    <tr><td><br/><br/></td></tr>

</table>


<!--<table class="info">
    <tr>
        <td class="header">
            <h4>Invoice </h4>
        </td>
    </tr>
</table>


<table class="items">
    
    <thead>
        
        
    <tr>
        <th class="item_header" width="10%" align="left">
            <?php echo $this->lang->line('date'); ?>
        </th>
        <th class="item_header" width="10%" align="right">
            <?php echo $this->lang->line('overdue'); ?>
        </th>
        <th class="item_header" width="10%" align="right">
            <?php echo $this->lang->line('invoice_number'); ?>
        </th>
        <th class="item_header" width="15%">
            <?php echo $this->lang->line('reference'); ?>
        </th>
        <th class="item_header">
            <?php echo $this->lang->line('project'); ?>
        </th>
        <th class="item_header" width="13%" align="right">
            <?php echo $this->lang->line('amount'); ?>
        </th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($statement as $item) { ?>
    <tr class="item">
        <td>
            <?php echo $item->due_date; ?>
        </td>
        <td class="overdue_<?php echo $item->invoice_is_overdue; ?>">
            <?php if ($item->invoice_is_overdue) { echo $item->invoice_days_overdue.' days';} ?>
        </td>
        <td align="right">
            <?php echo $item->invoice_number; ?>
        </td>
        <td class="item_type">
            <?php echo $item->invoice_client_order_number; ?>
        </td>
        <td>
            <?php echo $item->project_name; ?>
        </td>
        <td align="right">
            <?php echo display_currency($item->invoice_total); ?>
        </td>
    </tr>
    <?php } ?>


    </tbody>
</table>-->




<table class="info">
    <tr>
        <td class="header">
            <h4>Invoice </h4>
        </td>
    </tr>
</table>
    
<table class="items">
    
    
    
    <thead>
        
    <tr>
        <th class="item_header" width="10%" align="left">
            <?php echo $this->lang->line('date'); ?>
        </th>
        
        <th class="item_header" width="10%" align="right">
            <?php echo $this->lang->line('overdue'); ?>
        </th>
        
        <th class="item_header" width="15%" align="right">
            <?php echo $this->lang->line('invoice_number'); ?>
        </th>
        
        <th class="item_header" width="15%">
            <?php echo $this->lang->line('reference'); ?>
        </th>
        <th class="item_header">
            <?php echo $this->lang->line('project'); ?>
        </th>
        <th class="item_header" width="13%" align="right">
            <?php echo $this->lang->line('amount'); ?>
        </th>
        
    </tr>
    </thead>
    <tbody>
    <?php foreach ($delivery_docket as $dd) { ?>
    <tr class="item">
        <td>
            <?php $docket_date = date('d/m/Y',$dd->docket_date_entered); echo $docket_date;  ?>
        </td>
        
        <td>
            <?php 
            
            $docket_date = date('Y-m-d',$dd->docket_date_entered);
            $date = "2015-11-17";
            $d_d = date('Y-m-t', strtotime($docket_date. ' + 30 days'));
            // echo $d_d;          

            $now = time();
            //$d_d = date('d/m/Y', strtotime($docket_date. ' + 30 days'));
            
            //echo $d_d.' '.$docket_date; 
            
            $datediff = $now - strtotime($d_d);
            
            $days = floor($datediff / (60 * 60 * 24));
            if($days > 0){
              
                echo '<p style="color:red;">'.$days.' days','</p>';
            }elseif ($days == '0') {
                echo '<p style="color:red;">Last Day</p>';
            }
            
            
            ?>
        </td>
        
        
        
        <td align="right">
            <?php echo $dd->invoice_number; ?>.<?php echo $dd->docket_number; ?>
        </td>
        
        
        <td class="item_type">
            <?php echo $dd->invoice_client_order_number; ?>
        </td>
        <td>
            <?php echo $dd->project_name; ?>
        </td>
        <td align="right">
            <?php echo display_currency($dd->price_with_tax  ); ?>
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

    <tr>
        <td>Banking Details:</td>
        <td colspan="3"></td>
    </tr>
    <tr>
        <td >Company Name:</td>
        <td class="default_detail"><?= $bank_detail->company_name ?></td>
        <td colspan="2"></td>
    </tr>
    <tr>
        <td >ABN:</td>
        <td class="default_detail"><?= $bank_detail->abn ?></td>
        <td colspan="2"></td>
    </tr>
    <tr>
        <td>Bank BSB:</td>
        <td class="default_detail"><?= $bank_detail->bank_rsb ?></td>
        <td colspan="2"></td>
    </tr>
    <tr>
        <td>ACCT:</td>
        <td class="default_detail"><?= $bank_detail->acct ?></td>
        <!--td class="default_detail">597490216</td-->

        <td colspan="2"></td>
    </tr>
    <tr>
        <td>Swift Code:</td>
        <td class="default_detail"><?= $bank_detail->swift_code ?></td>
        
        <td colspan="2"></td>
    </tr>
    <tr>
        <td colspan="4"></td>
    </tr>


    </tbody>

</table>

</body>
</html>