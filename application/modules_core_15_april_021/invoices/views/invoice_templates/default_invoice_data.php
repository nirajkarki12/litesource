
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
            <?php echo invoice_date_entered($invoice); ?>
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
            <th class="item_header" width="15%">
                <?php echo nl2br($this->lang->line('item_type')); ?>
            </th>
            <th class="item_header">
                <?php echo nl2br($this->lang->line('description')); ?>
            </th>			
            <th class="item_header" width="8%" align="right">
                <?php echo $this->lang->line('price'); ?>
            </th>
            <th class="item_header" width="9%" align="right">
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
                <?php echo $this->lang->line('exclusive_of') . ' ' . $invoice->tax_rate_name; ?>
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
                <?php echo $this->lang->line('inclusive_of') . ' ' . $invoice->tax_rate_percent_name; ?>
            </td>
            <td>
                <?php echo invoice_total($invoice); ?>
            </td>
        </tr>
        
        
        
        <?php  
        
        if (!$invoice->invoice_is_quote) { 
            
            $paid_amounts = round((-1)*$docket_payment_amount, 2);
            $paid_amount = $paid_amounts;
            if( $paid_amount < 0 ){
                $paid_amount = "-".display_currency((-1)*$paid_amount);
            } elseif($paid_amount == '0.00') {
                $paid_amount = display_currency(0.00);
            }else{
                $paid_amount = display_currency($paid_amount);
            }

            $total_invc_amnt = round($invoice->invoice_total, 2);
            $rem_invc_amnt = $total_invc_amnt + $paid_amounts;

            if( $rem_invc_amnt < 0 ){
                $rem_invc_amnt = "-".display_currency((-1)*$rem_invc_amnt);
            } elseif($rem_invc_amnt == '0.00') {
                $rem_invc_amnt = display_currency(0.00);
            }else{
                $rem_invc_amnt = display_currency($rem_invc_amnt);
            }

            ?>
        
        <tr>
            <td colspan="2"></td>
            <td>
                Paid Amount:
            </td>
            <td>
                <?=$paid_amount ?>
            </td>
        </tr>
        
        <tr>
            <td colspan="2"></td>
            <td>
                Final Amount <?=$this->lang->line('inclusive_of') . ' ' . $invoice->tax_rate_percent_name?>: 
            </td>
            <td>
                <?=$rem_invc_amnt ?>
            </td>
        </tr>    
    <?php } ?>
        
        
        

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
                <?php echo (!$invoice->invoice_is_quote ? invoice_payment_term($invoice) : $this->lang->line('default_payment_terms')); ?>

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