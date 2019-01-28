
<table class="info_header">
    <tr>
        <td class="header">
            <?php echo ($invoice->invoice_is_quote ? $this->lang->line('quotation') : $this->lang->line('tax_invoice')); ?>
        </td>
        <td class="data">
            <?php echo $invoice->invoice_number . '.' . $docket['docket']->docket_number; ?>
        </td>
    </tr>
</table>

<table class="info" style="width: 55%;">
    <?php if ($invoice->invoice_client_order_number) { ?>
        <tr>
            <td class="header" width="25%">
                <?php echo $this->lang->line('purchase_order') . ':'; ?>
            </td>
            <td class="data">
                <?php echo $invoice->invoice_client_order_number; ?>
            </td>
        </tr>
    <?php } ?>
    <tr>
        <td class="header" width="25%">
            <?php echo $this->lang->line('date') . ':'; ?>
        </td>
        <td class="data">
            <?= date('d/m/Y', $docket['docket']->invoice_date) ?>
            <?php // echo invoice_date_entered($invoice); ?>
        </td>
    </tr>
    <tr>
        <td class="header">
            <?php echo $this->lang->line('attention') . ':'; ?>
        </td>
        <td class="data">
            <?php echo invoice_to_contact_name($invoice); ?><br />
            <?php echo invoice_to_client_name($invoice); ?>
        </td>
    </tr>	
    
    <?php if(isset($email_default_contact)){ 
        if($email_default_contact != ''){ ?> 
        <tr>
            <td class="header">
                Contact:
            </td>
            <td class="data">
                <?= $email_default_contact ?>
            </td>
        </tr>        
    <?php } } else { ?>    
    <?php foreach ($contacts as $contct) {
        if($contct->is_default == '1'){ ?>
        <tr>
            <td class="header">
                Contact:
            </td>
            <td class="data">
                <?= $contct->email_address ?>
            </td>
        </tr>        
    <?php } } ?>
    <?php } ?>
    <tr>
        <td class="header">
            <?php echo $this->lang->line('project') . ':'; ?>
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
        <?php if(sizeof($docket['docket_items']) > 0): ?>
        <?php foreach ($docket['docket_items'] as $item) { ?>
            <tr class="item">
                <td>
                    <?php echo $item->docket_item_qty; ?>
                </td>
                <td>
                    <?php  if($item->item_name == ''){
                        echo '&nbsp;';
                    } else{
                        echo nl2br($item->item_name);
                    } ?>
                </td>
                <td class="item_type">
                    <?php echo nl2br($item->item_type); ?>
                </td>
                <td >
                    <?php echo nl2br($item->item_description); ?>
                </td>
                <td align="right">
                    <?php echo ($item->item_price == 0)?'':display_currency($item->item_price); ?>
                </td>
                <td align="right">
                    <?php echo (($item->item_price*$item->docket_item_qty) == 0)?'':display_currency($item->item_price*$item->docket_item_qty); ?>
                </td>
            </tr>
        <?php } ?>
            <?php endif; ?>

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
                <?php echo $this->lang->line('exclusive_of') . ' ' . $invoice->tax_rate_name; ?>
            </td>
            <td>
                <?php echo docket_invoice_item_subtotal($docket['docket_items']); ?>
            </td>
        </tr>
        <tr>
            <td colspan="2"></td>
            <td>
                <?php echo $invoice->tax_rate_percent_name; ?>
            </td>
            <td>
                <?php echo docket_invoice_itemtax($invoice,$docket['docket_items']); ?>
            </td>
        </tr>
        
        
        <tr>
            <td colspan="2"></td>
            <td>
                <?php echo $this->lang->line('inclusive_of') . ' ' . $invoice->tax_rate_percent_name; ?>
            </td>
            <td>
                <?php echo docket_invoice_total($invoice, $docket['docket_items']); ?>
            </td>
        </tr>
        
        <?php $zeroCheck = docket_paid_zero_ckeck($invoice, $docket['docket_items'], $docket['docket']->price_with_tax); ?>
        <?php if($zeroCheck != TRUE){ ?>
        <tr>
            <?php $paidAmount = docket_paid_total($invoice, $docket['docket_items'], $docket['docket']->price_with_tax); ?>
            <td colspan="2"></td>
            <td>
                 <?php if($paidAmount[0] == '+' ){ echo 'Negative'; } ?> Paid Amount
            </td>
            <td>
                <?= $paidAmount; ?>
            </td>
        </tr>
        <?php } ?>
        
        <tr>
            <td colspan="2"></td>
            <td>
                <?= 'Final Amount Inclusive Of '.$invoice->tax_rate_percent_name ?>
            </td>
            <td>
                <?php echo docket_invoice_total_amount($invoice, $docket['docket_items'], $docket['docket']->price_with_tax); ?>
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
                <td colspan="2"></td>
            </tr>
            <tr>
                <td colspan="4"></td>
            </tr>
        <?php } ?>
        <tr>
            <td>Payment Terms:</td>
            <td colspan="3" class="default_detail">
                <?php // echo (!$invoice->invoice_is_quote ? invoice_payment_terms($invoice) : $this->lang->line('default_payment_terms')); ?>
                30 days EOM - Payment Due 
                <?php
                $date = date('Y-m-d', $docket['docket']->invoice_date);
                $newdate = strtotime ( '+1 month' , strtotime ( $date ) ) ;
                $newdate = date ( 't/m/Y' , $newdate );
                echo $newdate;
                ?>
                
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