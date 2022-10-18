
<div>

    <h1>New items detected in quote. Please confirm or choose the appropriate action and click Continue.</h1>


    <div style="font-size: 20px;padding-left: 1%;">
        <?php
        $c = 0;
        foreach ($new_products as $value) {
            $c++;
            ?>
            <div class="wrap_<?= ($c % 2 == 0) ? 'even' : 'odd'; ?>"
                 <h3><?= $c ?>. <?= $value->item_name ?></h3>

                <table style="width: 100%;">
                    <tr class="rowlink_<?= $c ?>"><td><label><input type="radio" data-cnt="<?= $c ?>" class="radiocheck radio_<?= $c ?>" name="new_item_action<?= $c ?>" value="donot_add"> Do not create this item. I only want to include this in invoice and do not want to process this in orders.</label></td></tr>
                    <tr class="rowlink_<?= $c ?>">
                        <?php if (isset($value->possible_item->name)): ?>
                            <td><label><input data-cnt="<?= $c ?>" type="radio" class="radiocheck radio_<?= $c ?>" name="new_item_action<?= $c ?>" value="relate" data-val="<?= $value->possible_item->inventory_id ?>"><span> Do not create this item. This Item is <a target="_blank" href="<?= site_url('inventory/form/inventory_id/' . $value->possible_item->inventory_id); ?>"><?= $value->possible_item->name; ?></a></span></label></td>
    <?php endif; ?>
                    </tr>
                    <tr class="rowlink_<?= $c ?>"><td><label><input checked type="radio" data-cnt="<?= $c ?>" class="radiocheck radio_<?= $c ?>" name="new_item_action<?= $c ?>" value="create"> Create new item as follow and create order for this item to the selected supplier.</label></td></tr>
                </table>
                <form id="no_inv_form">
                    <table class="main_inputs_<?= $c ?>">


                        <tr class="thread">
<!--                            <td>Item</td>-->
                            <td>Description </td>
                            <!--<td>type </td>-->
                            <td>Price </td>
                            <td width="25">Dynamic</td>
                            <td>Supplier Price </td>
                            <td>Supplier Code </td>
                            <td>Supplier Description</td>
                            <td>Select Supplier </td>
                        </tr>



                        <tr>

    <?php // print_r($value);   ?>
<!--                            <td id="inv_name_<?= $c ?>"><?= $value->item_name ?></td>-->
                        <input class="itm" id="inv_invc_itm_id_<?= $c ?>" type="hidden" value="<?= $value->invoice_item_id ?>">
                        <input class="itm" id="inv_invc_id_<?= $c ?>" type="hidden" value="<?= $value->invoice_id ?>">
                        <td><input class="itm" id="inv_desc_<?= $c ?>" type="text" value="<?= $value->item_description ?>"></td>
                        <!--<td><input id="inv_type_<?= $c ?>" type="text" value="<?= $value->item_type ?>"></td>-->
                        <td><input class="itm" id="inv_price_<?= $c ?>" type="text" value="<?= $value->item_price ?>"></td>
                        <td><input class="itm" id="inv_dynamic_<?= $c ?>" type="checkbox" value=""></td>
                        <td><input class="itm" id="sup_price_<?= $c ?>" type="number" value=""></td>
                        <td><input class="itm" id="sup_code_<?= $c ?>" type="text" value=""></td>
                        <td><input class="itm" id="inv_description_<?= $c ?>" type="text" value=""></td>
                        <td>
                            <select class="itm" id="sup_id_<?= $c ?>">
                                <?php foreach ($suppliers as $supplier) { ?>
                                    <option value="<?= $supplier->client_id ?>"><?= $supplier->client_name ?></option>
    <?php } ?>
                            </select>
                        </td>

                        </tr>
                        <tr><td colspan="8"></td></tr>


                    </table>
                </form>
            </div>
<?php } ?>
        <button id="no_pro_submit" onclick="no_product_submit('<?= count($new_products) ?>')" style="float: right">Continue</button>
    </div>
</div>


<div class="overlay-content popup1" style="text-align: center; width: 60%; left: 40%; height: 40%; padding-top: 8%;">
    <p style="font-size: 17px;margin-bottom: 10px;"></p>
    <button class="hq_btn close-syn_yes">Hand Stock</button>
    <button class="hq_btn close-syn_no">As Per Quote</button>
    <button class="hq_btn close-btn">Cancel</button>
</div>

<style>
    .wrap_even{border-top: 2px solid black; padding-top: 15px;}
    .overlay-content h1 {
        font-size: 17px;
    }

    .overlay-content .wrap_odd ,.overlay-content .wrap_even{
        font-size: 12px;
    }
    .thread{
        font-weight: bold;
    }
</style>


<script type="text/javascript">


    $(document).ready(function () {
        $('.close-syn_yes').click(function () {
            window.location.href = "<?php echo site_url('invoices/quote_to_orders_invoice/invoice_id/' . uri_assoc('invoice_id') . '?hand_stock=1'); ?>";
        });
        $('.close-syn_no').click(function () {
            window.location.href = "<?php echo site_url('invoices/quote_to_orders_invoice/invoice_id/' . uri_assoc('invoice_id')); ?>";
        });

        $('.radiocheck').on('change', function () {
            var cnt = $(this).data('cnt');
            if ($(this).val() == 'create') {
                $('.main_inputs_' + cnt).find('.itm').prop('disabled', false);
            } else {
                $('.main_inputs_' + cnt).find('.itm').prop('disabled', true);
            }
        });
    })


    function showPopup(whichpopup) {
        if (whichpopup != '_new_product') {
            $('.popup_new_product').hide();
        }
        var docHeight = $(document).height();
        var scrollTop = $(window).scrollTop();
        $('.overlay-bg').show().css({'height': docHeight});
        $('.popup' + whichpopup).show().css({'top': scrollTop + 20 + 'px'});
    }





    function no_product_submit(total_count) {
        
        /////////////mmmmmmmmmmmmmmmmm
        //$('#no_pro_submit').attr('disabled', true);

        var invoice_id = [];
        var invoice_item_id = [];
        var name = [];
        var supplier_id = [];
        var description = [];
        var base_price = [];
        var supplier_price = [];
        var supplier_code = [];
        var supplier_description = [];
        var use_length = [];
        var i_data = [];

        var i_action = [];

        for (let count = 1; count <= total_count; ++count) {

            var action = $('input[name="new_item_action' + count + '"]:checked').val();
            i_action.push(action);
            
            if (action == 'create') {

                var invc_itm_id = $('#inv_invc_itm_id_' + count).val();
                var invc_id = $('#inv_invc_id_' + count).val();
                var sup_id = $('#sup_id_' + count + ' option:selected').val();
                var sup_name = $('#sup_id_' + count + ' option:selected').text();
                var nam = $("#inv_name_" + count).html();
                var desc = $('#inv_desc_' + count).val();
                // var typ = $('#inv_type_'+count).val();
                var pric = $('#inv_price_' + count).val();
                var dyn = document.getElementById("inv_dynamic_" + count).checked;
                if (dyn == false) {
                    dyn = '0';
                } else {
                    dyn = '1';
                }
                var s_price = $('#sup_price_' + count).val();
                var s_code = $('#sup_code_' + count).val();
                
                var s_desc = $('#inv_description_' + count).val();
                
                if ((s_code == '')) {
                    alert('Please enter supplier code for ' + nam);
                    $('#no_pro_submit').removeAttr('disabled', true);
                    return false;
                }
                
                if (!(parseInt(s_price,10) >= 0)) {
                    alert('Please enter supplier price for ' + nam);
                    $('#no_pro_submit').removeAttr('disabled', true);
                    return false;
                }
                
                if ((sup_name == '') || (sup_id < '0')) {
                    alert('Please select supplier for ' + nam);
                    $('#no_pro_submit').removeAttr('disabled', true);
                    return false;
                }
                
                
                invoice_item_id.push(invc_itm_id);
                invoice_id.push(invc_id);
                name.push(nam);
                supplier_id.push(sup_id);
                description.push(desc);
                base_price.push(pric);
                supplier_code.push(s_code);
                supplier_price.push(s_price);
                supplier_description.push(s_desc);
                use_length.push(dyn);
            }else if(action == 'relate'){
                var itemid = $('input[name="new_item_action' + count + '"]:checked').data('val');
                invoice_item_id.push('');
                invoice_id.push('');
                name.push(itemid);
                supplier_id.push('');
                description.push('');
                base_price.push('');
                supplier_code.push('');
                supplier_price.push('');
                supplier_description.push('');
                use_length.push('');
            }else{
                invoice_item_id.push('');
                invoice_id.push('');
                name.push('');
                supplier_id.push('');
                description.push('');
                base_price.push('');
                supplier_code.push('');
                supplier_price.push('');
                supplier_description.push('');
                use_length.push('');
            }
            
        }
        
        
        var i_data = {i_action: i_action,name: name, supplier_id: supplier_id, description: description, base_price: base_price, supplier_price: supplier_price, supplier_description: supplier_description, supplier_code: supplier_code, use_length: use_length, invoice_item_id: invoice_item_id, invoice_id: invoice_id};
        
        
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo site_url('inventory/ajax_from_quote_add'); ?>',
            data: i_data,
            success: function (data) {

                if (data.status == 'success') {

                    $('#btn_quote_to_orders_invoice').attr('disabled', true);
                    //window.location.href = "<?php echo site_url('invoices/quote_to_orders_invoice/invoice_id/' . $invoice->invoice_id); ?>";
                    
                    event.preventDefault();
                    $.ajax({
                        url: "<?php echo site_url('invoices/check_order_creation/invoice_id/' . uri_assoc('invoice_id').'?step=stuck_yes_no'); ?>",
                        dataType: 'json',
                        type: 'POST',
                        success: function (data) {


                            if (data.result == 'no_items_selected') {
                                //alert(data.detail);
                                $('#btn_quote_to_orders_invoice').attr('disabled', false);
                            } else if (data.result == 'new_item_detected') {
                                $('.popup_new_product div').html('');
                                $('.popup_new_product div').append(data.detail);
                                var selectedPopup = '_new_product';
                                showPopup(selectedPopup);
                                $('#btn_quote_to_orders_invoice').attr('disabled', false);

                                $('#btn_quote_to_orders_invoice').attr('disabled', false);
                            } else if (data.result == 'stuck_yes_no') {
                                $('.popup1 p').html('');
                                $('.popup1 p').append(data.detail);
                                var selectedPopup = '1';
                                showPopup(selectedPopup);
                                $('#btn_quote_to_orders_invoice').attr('disabled', false);
                            } else if (data.result == 'no_suppliers') {
                                //alert(data.detail);
                                $('#btn_quote_to_orders_invoice').attr('disabled', false);
                            } else if (data.result == 'problem') {
                                if (confirm(data.detail)) {
                                    window.location.href = "<?php echo site_url('invoices/quote_to_orders_invoice/invoice_id/' . $invoice->invoice_id); ?>";
                                } else {
                                    $('#btn_quote_to_orders_invoice').attr('disabled', false);
                                }
                            } else if (data.result == 'problem_redirect') {
                                if (confirm(data.detail)) {
                                    window.location.href = "<?php echo site_url('invoices/quote_to_orders_invoice/invoice_id/' . $invoice->invoice_id . '?create_invoice=1'); ?>";
                                } else {
                                    window.location.href = "<?php echo site_url('invoices/quote_to_orders_invoice/invoice_id/' . $invoice->invoice_id . '?update_product=1'); ?>";
                                }
                            } else {
                                window.location.href = "<?php echo site_url('invoices/quote_to_orders_invoice/invoice_id/' . $invoice->invoice_id); ?>";
                            }
                        }
                    })





                    //window.location.reload();
                }
//                else{
//                    alert(data.msg);
//                    document.getElementById("invPopForm_loader").style.display = "none";
//                }
            }
        });
    }
</script>