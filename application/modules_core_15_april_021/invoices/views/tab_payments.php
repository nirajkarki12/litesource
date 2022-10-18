<div class="full_box">
    <form action="<?= site_url('invoices/add_docket_amounts') ?>" method="POST">
        
        <dl>
            <dt><label>Total Amount Owing: </label></dt>
            <dd id="amnt-owng"><?=tot_invc_owing_amnt_wit_curr($invoice, $docket_payment_amount)?></dd>
        </dl>
        
        <dl>
            <dt><label>Amount: <?= currency_symbol() ?></label></dt>
            <dd><input type="text" id="docket_paid_amount" name="amount" value="" /></dd>
        </dl>
        <dl>
            <dt><label>Note: </label></dt>
            <dd><textarea id="docket_paid_note" name="note"></textarea></dd>
        </dl>
        
        <input type="hidden" name="invoice_id" value="<?=uri_assoc('invoice_id')?>" />
        
        <br>
        <p><strong>Choose Docket:</strong></p>
        <table style="width: 80%;text-align: right;">
            <thead>
                <tr style="background: black;color: white">
                    <td style="text-align: left;">Choose Docket</td>
                    <td style="text-align: left;">Due Days</td>
                    <td>Docket Amount</td>
                    <td>Owing Amount</td>
                    <td>Applied Amount</td>
                    <td>Remaining Amount</td>
                </tr>
            </thead>
            <?php if ($dockets != NULL) { ?>
                <tbody>
                    <?php foreach ($dockets as $docket) {
                        $d_id = $docket->docket_id; 
                        $dokt_woing_with_curr = docket_owing_with_currency($docket);
                        ?>
                        <tr style="background: #f0f0f0;" data-id="<?=$d_id?>">
                            <td style="text-align: left;"><input class="chkd_dkt" type="checkbox" name="paid_ids[]" value="<?=$d_id?>"> <span> <?=$docket->docket_number?></span></td>
                            <input type="hidden" name="all_ids[]" value="<?=$d_id?>">
                            <td style="text-align: left;"><?= docket_due_days($docket); ?></td>
                            <td><?= display_currency($docket->price_with_tax)?></td>
                            <td><?= $dokt_woing_with_curr ?></td>
                            <input type="hidden" id="dow<?=$d_id?>" value="<?= $dokt_woing_with_curr?>">
                            <!-------applied amount------->
                            <td id="apatd<?=$d_id?>">$0.00</td>
                            <input type="hidden" name="paid_amounts[]" id="apainpt<?=$d_id?>" value="$0.00">
                            <!-------applied amount------->
                            <td id="remamnt<?=$d_id?>"><?= $dokt_woing_with_curr?></td>
                        </tr>
                    <?php } ?>
                </tbody>
        <?php } ?>
        </table>
        
        <div>
            <input type="submit" value="Save" name="save_add_amount" id="add_docket_payment" disabled="" >
        </div>
        
    </form>
    <br><br><br>
    <h3>Payment History</h3>
    <table>
        <thead>
            <tr style="background: black;color: white">
                <td>Docket</td>
                <td>Amount</td>
                <td>Note</td>
                <td>Date</td>
                <td>User</td>
            </tr>
        </thead>
        <?php if ($docket_payment_history != NULL) {
            $docket_payment_history = (array)$docket_payment_history;
            $c = 0; ?>
            <tbody>
                <?php foreach ($docket_payment_history as $payment) { ?>
                        <tr style="background: #f0f0f0;">
                            <td><?= $payment['docket_number']; ?></td>
                            <?php if($payment['amount_log'] < 0){ ?>
                            <td><?= '-'.display_currency(trim($payment['amount_log'], '-')) ?></td>
                            <?php }else{ ?>
                            <td><?= display_currency($payment['amount_log']); ?></td>
                            <?php } ?>
                            <td><?= $payment['note']; ?></td>
                            <td><?= date('d/m/Y  H:i', $payment['time']); ?></td>
                            <td><?= $payment['user_name']; ?></td>
                        </tr>
                <?php } ?>
            </tbody>
<?php } ?>
    </table>
</div>
<div style="clear: both;">&nbsp;</div>

<script>
    
    function amount_format(amount){
        
        if( amount < 0 ){
            amount = amount.split('-').join('');
            return '-$'+amount.replace(/\d(?=(\d{3})+\.)/g, '$&,');
        }else{
            return '$'+amount.replace(/\d(?=(\d{3})+\.)/g, '$&,');
        }
    }
    
    jQuery('.chkd_dkt').on("change", function (event) {
        
        event.preventDefault();
        var this_row = $(this);
        var entred_amnt = parseFloat($('#docket_paid_amount').val().split(',').join('')).toFixed(2);
        if( entred_amnt == 'NaN' ){
            entred_amnt = parseFloat('0.00').toFixed(2);
        }
        
        var numItems = $('.chkd_dkt').length;
        var id = this_row.closest("tr").attr('data-id');
        var owng_amnt = parseFloat($('#dow'+id).val().split(',').join('').split('$').join('')).toFixed(2);
        
        if ( this_row.is(":checked") ){    
            if( entred_amnt == 0 ){
                this_row.prop('checked', false); 
                return false;
            }
            jQuery('#add_docket_payment').removeAttr('disabled');
            var rem_amnt = (parseFloat(entred_amnt)- parseFloat(owng_amnt)).toFixed(2);    
            if(rem_amnt >= 0){
                
                
                
//                console.log(rem_amnt);
                
//                owng_amnt = owng_amnt.replace(/\d(?=(\d{3})+\.)/g, '$&,');
//                rem_amnt = rem_amnt.replace(/\d(?=(\d{3})+\.)/g, '$&,');
                
                $('#apatd'+id).html(amount_format(owng_amnt));
                $('#apainpt'+id).val(amount_format(owng_amnt));
                $('#docket_paid_amount').val(rem_amnt.replace(/\d(?=(\d{3})+\.)/g, '$&,'));
                $('#docket_paid_amount').attr('disabled',true);
                $('#remamnt'+id).html('$0.00');
                
                if(numItems > 0){
                    var ids_arr = [];
                    for (index = 0; index < numItems; ++index) {
                        var i_id = $('.chkd_dkt').get(index).value;
                        var r_amnt = parseFloat($('#remamnt'+i_id).text().split(',').join('').split('$').join('')).toFixed(2);
                        ids_arr.push(r_amnt);
                    }
                    var sum = 0.00;
                    var numbers = ids_arr
                    for (var i = 0; i < numbers.length; i++) {
                        sum = (parseFloat(sum) + parseFloat(numbers[i])).toFixed(2);
                    }
                    if( sum == 0 ){
                        if(rem_amnt != 0){
                            var temp_re_amnt = (parseFloat(-1)* parseFloat(rem_amnt)).toFixed(2);
                            $('#remamnt'+id).html(amount_format(temp_re_amnt));
                            var temp_owing_amnt = (parseFloat(owng_amnt) + parseFloat(rem_amnt)).toFixed(2);   
                            $('#apatd'+id).html(amount_format(temp_owing_amnt));
                            $('#apainpt'+id).val(amount_format(temp_owing_amnt));
                        }       
                    }
                }
            } else {
                
                var r_a = (parseFloat(owng_amnt)- parseFloat(entred_amnt)).toFixed(2);
                $('#apatd'+id).html(amount_format(entred_amnt));
                $('#apainpt'+id).val(amount_format(entred_amnt));
                $('#remamnt'+id).html(amount_format(r_a));
                $('#docket_paid_amount').val('0.00');
                $('#docket_paid_amount').attr('disabled',true);
            }
        }else{
            var apld_amnt = parseFloat($('#apainpt'+id).val().split(',').join('').split('$').join('')).toFixed(2);
            var r_amnt = parseFloat($('#remamnt'+id).text().split(',').join('').split('$').join('')).toFixed(2);
            
            var r_amnt1 = ( parseFloat(r_amnt)+ parseFloat(apld_amnt)).toFixed(2);
            if( r_amnt < 0 ){
                var returened_amount = (parseFloat(entred_amnt)+ parseFloat(apld_amnt) + parseFloat(r_amnt)).toFixed(2);
            }else{
                var returened_amount = (parseFloat(entred_amnt)+ parseFloat(apld_amnt)).toFixed(2);
            }
            
            $('#remamnt'+id).html(amount_format(r_amnt1));
            $('#apatd'+id).html('$0.00');
            $('#apainpt'+id).val('$0.00');
            $('#docket_paid_amount').val(returened_amount);
            $('#docket_paid_amount').attr('disabled',true);
            
            if(numItems > 0){
                var unchecked_arr = [];
                for (index = 0; index < numItems; ++index) {
                    var is_checked = $('.chkd_dkt').get(index).checked;
                    if( is_checked === false ){
                        unchecked_arr.push(0);
                    }
                }   
                var unchecked_count = unchecked_arr.length;
                if(numItems == unchecked_count){
                    jQuery('#docket_paid_amount').removeAttr('disabled');
                    $('#add_docket_payment').attr('disabled',true);
                }else{
                    jQuery('#add_docket_payment').removeAttr('disabled');
                }
            }
        }
    });
    
    
    jQuery('#docket_paid_amount').on("keypress keydown keyup blur input change paste", function (event) {
        let key = Number(event.key);
        var enteredValue = event.key;   
        if( (isNaN(key) != true) || (enteredValue == ".") || (enteredValue == "-") || (event.which < 32) || (event.which > 126) ){
            //jQuery('#add_docket_payment').removeAttr('disabled');
            return true;
        }
        else {
          return false;
        }
    });
</script>
<?php

//echo '<pre>';
//print_r($payments);
//print_r($invoice);
//die;