<div class="full_box">
    <form action="<?= site_url('delivery_dockets/add_docket_amount') ?>" method="POST">
        
        
        
        <dl>
            <dt><label>Amount Owing: </label></dt>
            <?php if( round(($docket->price_with_tax - array_sum(array_column($docket_payments, 'amount_entered'))),2) < 0){ ?>
            <dd>-<?= display_currency(trim(($docket->price_with_tax - array_sum(array_column($docket_payments, 'amount_entered')) ), '-')) ?></dd>
            <?php }else{ ?>
            <dd><?= display_currency(trim(($docket->price_with_tax - array_sum(array_column($docket_payments, 'amount_entered')) ), '-')) ?></dd>
            <?php } ?>
        </dl>
        
        <dl>
            <dt><label>Amount: <?= currency_symbol() ?></label></dt>
            <dd><input type="text" id="docket_paid_amount" name="amount" /></dd>
        </dl>
        <dl>
            <dt><label>Note: </label></dt>
            <dd><textarea id="docket_paid_note" name="note"></textarea></dd>
        </dl>
        <dl>
            <dt>
                <input type="hidden" value="<?= uri_assoc('docket_id'); ?>" name="docket_id">
                <input type="hidden" name="user_name" value="<?= $docket->username; ?>">
                <input type="submit" value="Add" name="save_add_amount" id="add_docket_payment" disabled="" >
            </dt>
        </dl>
    </form>
    <br><br><br>
    <h3>Payment History</h3>
    <table>
        <thead>
            <tr style="background: black;color: white">
                <td>Amount</td>
                <td>Note</td>
                <td>Date</td>
                <td>User</td>
            </tr>
        </thead>
        <?php if ($docket_payments != NULL) {
            $c = 0; ?>
            <tbody>
    <?php foreach ($docket_payments as $payment) { ?>
                    <tr style="background: #f0f0f0;">
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
//    jQuery('#docket_paid_amount').on("keypress keydown keyup blur input change paste", function (event) {
//        if ((event.which < 32) || (event.which > 126)) {
//            jQuery('#add_docket_payment').removeAttr('disabled');
//            return true;
//        }
//        return jQuery.isNumeric($(this).val() + String.fromCharCode(event.which));
//    });
</script>



<script>
    jQuery('#docket_paid_amount').on("keypress keydown keyup blur input change paste", function (event) {
        let key = Number(event.key);
        var enteredValue = event.key;
        
//        console.log(v);
//        alert(isNaN(key));
//    alert(keyValue);
        
        if( (isNaN(key) != true) || (enteredValue == ".") || (enteredValue == "-") || (event.which < 32) || (event.which > 126) ){
            jQuery('#add_docket_payment').removeAttr('disabled');
            return true;
        }
        else {
          return false;
        }
        
        
        // keydown keyup blur input change paste
    });
</script>