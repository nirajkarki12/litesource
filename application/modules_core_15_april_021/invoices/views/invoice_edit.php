<?php $this->load->view('dashboard/header', array('header_insert' => 'invoices/invoice_edit_header')); ?>
<?php echo modules::run('invoices/widgets/generate_dialog'); ?>
<?php $this->load->view('dashboard/jquery_date_picker'); ?>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/jquery/jquery.relcopy.js"></script>
<script type="text/javascript">
    $(function () {
        //var append_to_clone = ' <a class="remove" href="#" onclick="$(this).parent().remove(); return false"><?php echo $this->lang->line('delete'); ?></a>';
        //$('a.copy').relCopy({append: append_to_clone});
<?php
if ($invoice->client_id == 0) {
    $tab_index = 1;
}
?>
        $('#tabs').tabs({selected: <?php echo $tab_index; ?>});
    });

</script>

<?php 
$is_quote_invoice = 'invoices';
if( $invoice->invoice_is_quote == 1 ){
    $is_quote_invoice = 'quotes';
}
?>

<div class="grid_12" id="content_wrapper">
    <form method="post" action="<?php echo site_url($this->uri->uri_string()); ?>">
        <div class="section_wrapper">
            <div class="title_black">
                <div class="title_btns">
                    
                    <?php if( $invoice->invoice_is_quote == '0' ){ ?>
                    <!--<button class="btn btn-primary" id="export_csv_selected" style="margin: 14px 10px 0 0 !important;">CSV »</button>-->
                    <?php } ?>
                    
                    <?php if (!$invoice->invoice_is_quote) { ?>
                        <input type="submit" name="btn_quote_to_invoice" style="float: right; margin-top: 10px; margin-right: 10px;" value="<?php echo $this->lang->line('copy_invoice'); ?>" />
                        <?php if (!$this->mdl_mcb_data->setting('disable_invoice_payments')) { ?>
                            <input type="submit" name="btn_add_payment" style="float: right; margin-top: 10px; margin-right: 10px;" value="<?php echo $this->lang->line('enter_payment'); ?>" />
                        <?php } ?>
                    <?php } else { ?>
                        <input type="submit" name="btn_copy_quote" style="float: right; margin-top: 10px; margin-right: 10px;" value="<?php echo $this->lang->line('copy_quote'); ?>" />
                        <input type="submit"  id="btn_quote_to_orders_invoice" name="btn_quote_to_orders_invoice" style="float: right; margin-top: 10px; margin-right: 10px;" value="<?php echo $this->lang->line('quote_to_orders_invoice'); ?>" />
                    <?php } ?>
                        
                        
                    <?php if (!$invoice->invoice_is_quote) { ?>
                    <?php $this->load->view('dashboard/btn_add', array('btn_name' => 'btn_invoice_to_delivery_docket', 'btn_value' => $this->lang->line('docket_create'))); ?>
                    <?php } ?>
                        <?php if (!$invoice->invoice_is_quote) { ?>
                        <button class="btn btn-primary" id="export_csv_selected" style="margin: 14px 10px 0 0 !important;float: right;">CSV »</button>
                        <!--<input type="submit" name="download_invoice_items" style="float: right; margin-top: 10px; margin-right: 10px;" value="CSV »" />-->
                        <?php } else { ?>
                        <input type="submit" name="download_quote_items" style="float: right; margin-top: 10px; margin-right: 10px;" value="CSV »" />
                        <?php } ?>
                    <?php $this->load->view('dashboard/btn_add', array('btn_name' => 'btn_download_pdf', 'btn_value' => $this->lang->line('pdf_download'))); ?>
                    <?php $this->load->view('dashboard/btn_add', array('btn_name' => 'btn_send_email', 'btn_value' => $this->lang->line('send_email'), 'is_quote_invoice'=>$is_quote_invoice)); ?>
                </div>
                <h3><?php echo $invoice->client_name . ' &ndash; ' . ($invoice->invoice_is_quote == 1 ? $this->lang->line('quote_number') : $this->lang->line('invoice_number')) . ' ' . $invoice->invoice_number . ' (' . $invoice->client_group_name . ' Pricing)'; ?></h3>

                <p class="sub_title"><?php echo ($invoice->project_id == 0 ? '' : $invoice->project_name); ?></p>

            </div>

            <?php $this->load->view('dashboard/system_messages'); ?>

            <div class="content toggle">

                <div id="tabs">
                    <ul>
                        <li><a href="#tab_items"><?php echo $this->lang->line('items'); ?></a></li>
                        <li><a href="#tab_general"><?php echo $this->lang->line('summary'); ?></a></li>
                        <?php if (!$this->mdl_mcb_data->setting('disable_invoice_payments') && !$invoice->invoice_is_quote) { ?>
                            <li><a href="#tab_payments"><?php echo $this->lang->line('payments'); ?></a></li>
                        <?php } ?>
                        <li><a href="#tab_notes"><?php echo $this->lang->line('notes'); ?></a></li>
                        <?php if (!$this->mdl_mcb_data->setting('disable_invoice_audit_history')) { ?>
                            <li><a href="#tab_history"><?php echo $this->lang->line('history'); ?></a></li>
                        <?php } ?>
                        <li><a href="#tab_orders"><?php echo $this->lang->line('orders'); ?></a></li>
                        <li><a href="#tab_dockets"><?php echo $this->lang->line('dockets'); ?></a></li>
                        <li><a href="#tab_internal"><?php echo $this->lang->line('internal'); ?> (<?= $internaldetail_count ?>)</a></li>
                        <?php if (!$invoice->invoice_is_quote) { ?>
                        <li><a href="#tab_payments">Payments History</a></li>
                        <?php } ?>
                    </ul>
                    <div id="tab_items">
                        <?php $this->load->view('item_grid'); ?>
                    </div>

                    <div id="tab_general">
                        <?php $this->load->view('tab_general'); ?>
                    </div>

                    <?php if (!$this->mdl_mcb_data->setting('disable_invoice_payments') && !$invoice->invoice_is_quote) { ?>
                        <div id="tab_payments">
                            <?php $this->load->view('payments/table'); ?>
                        </div>
                    <?php } ?>

                    <div id="tab_notes">
                        <?php $this->load->view('tab_notes'); ?>
                    </div>

                    <?php if (!$this->mdl_mcb_data->setting('disable_invoice_audit_history')) { ?>
                        <div id="tab_history">
                            <?php $this->load->view('tab_history'); ?>
                        </div>
                    <?php } ?>

                    <div id="tab_orders">
                        <?php $this->load->view('orders/order_table'); ?>
                    </div>

                    <div id="tab_dockets">
                        <?php $this->load->view('delivery_dockets/delivery_docket_table'); ?>
                    </div>
                    <div id="tab_internal">
                        <?php $this->load->view('internal'); ?>
                    </div>
                    <?php if (!$invoice->invoice_is_quote) { ?>
                        <div id="tab_payments">
                        <?php $this->load->view('tab_payments'); ?>
                        </div>
                    <?php } ?>
                    
                    
                </div>

                <div style="clear: both;">&nbsp;</div>

            </div>

        </div>

    </form>

</div>

<?php $this->load->view('dashboard/footer'); ?>

<script type="text/javascript">

    $(document).ready(function () {

        function showPopup(whichpopup) {
            var docHeight = $(document).height();
            var scrollTop = $(window).scrollTop();
            $('.overlay-bg').show().css({'height': docHeight});
            $('.popup' + whichpopup).show().css({'top': scrollTop + 20 + 'px'});
        }
        function closePopup() {
            $('.overlay-bg, .overlay-content').hide();
        }
        $('.show-popup').click(function (event) {
            event.preventDefault();
            var selectedPopup = $(this).data('showpopup');
            showPopup(selectedPopup);
        });
        $('.close-btn, .overlay-bg').click(function () {
            closePopup();
        });

        $('.close-syn_yes').click(function () {
            window.location.href = "<?php echo site_url('invoices/quote_to_orders_invoice/invoice_id/' . $invoice->invoice_id . '?hand_stock=1'); ?>";
        });
        $('.close-syn_no').click(function () {
            window.location.href = "<?php echo site_url('invoices/quote_to_orders_invoice/invoice_id/' . $invoice->invoice_id); ?>";
        });

        $(document).keyup(function (e) {
            if (e.keyCode == 27) {
                closePopup();
            }
        });

        //---------- aJax------
        $('#btn_quote_to_orders_invoice').on('click', function (e) {

            //$('#btn_quote_to_orders_invoice').attr('disabled',true);
            e.preventDefault();
            $.ajax({
                url: "<?php echo site_url('invoices/check_order_creation/invoice_id/' . $invoice->invoice_id); ?>",
                dataType: 'json',
                type: 'POST',
                success: function (data) {

                    if (data.result == 'no_items_selected') {
                        alert(data.detail);
                        $('#btn_quote_to_orders_invoice').attr('disabled', false);
                    } else if (data.result == 'new_item_detected') {
                        $('.popup_new_product div').html('');
                        $('.popup_new_product div').append(data.detail);
                        var selectedPopup = '_new_product';
                        showPopup(selectedPopup);
                        $('#btn_quote_to_orders_invoice').attr('disabled', false);
                        $('#btn_quote_to_orders_invoice').attr('disabled', false);
                    } else if (data.result == 'stuck_yes_no') {
                        $('.popup1 .sup_cntnt').html('');
                        $('.popup1 .sup_cntnt').append(data.detail);
                        var selectedPopup = '1';
                        showPopup(selectedPopup);
                        $('#btn_quote_to_orders_invoice').attr('disabled', false);
                    } else if (data.result == 'no_suppliers') {
                        alert(data.detail);
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
        });
    });

</script>



<style>
    .overlay-bg {
        display: none;
        position: absolute;
        top: 0;
        left: 0;
        height:100%;
        width: 100%;
        cursor: pointer;
        z-index: 1000; /* high z-index */
        background: #000; /* fallback */
        background: rgba(0,0,0,0.75);
    }
    .overlay-content {
        display: none;
        background: #fff;
        padding: 1%;
        width: 35%;
        position: absolute;
        top: 15% !important;
        left: 55%;
        margin: 0 0 0 -20%; /* add negative left margin for half the width to center the div */
        cursor: default;
        z-index: 10001;
        border-radius: 2px;
        box-shadow: 0 0 5px rgba(0,0,0,0.9);
    }
    .hq_btn {
        cursor: pointer;
        border: 1px solid #333;
        padding: 15px 66px;
        background: #a9e7f9; /* fallback */
        background: -moz-linear-gradient(top,  #a9e7f9 0%, #77d3ef 4%, #05abe0 100%);
        background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#a9e7f9), color-stop(4%,#77d3ef), color-stop(100%,#05abe0));
        background: -webkit-linear-gradient(top,  #a9e7f9 0%,#77d3ef 4%,#05abe0 100%);
        background: -o-linear-gradient(top,  #a9e7f9 0%,#77d3ef 4%,#05abe0 100%);
        background: -ms-linear-gradient(top,  #a9e7f9 0%,#77d3ef 4%,#05abe0 100%);
        background: linear-gradient(to bottom,  #a9e7f9 0%,#ef82771f 4%,#cea8a866 100%);
        border-radius: 2px;
        box-shadow: 0 0 4px rgba(0,0,0,0.3);
    }
    .close-btn:hover {
        background: red;
    }
    .close-syn_yes:hover {
        background: #e3e3e3;
    }
    .close-syn_no:hover {
        background: #e3e3e3;
    }
    /* media query for most mobile devices */
    @media only screen and (min-width: 0px) and (max-width: 480px){
        .overlay-content {
            width: 96%;
            margin: 0 2%;
            left: 0;
        }
    }    
    .popup1 button{
        /*    margin-right: 6%;
            margin-left: 11%;*/
    }
    .popup_new_product{
        width: 95%;
        left: 22%
    }
    .pop_edit_invc_design{
        text-align: center; 
        width: 60%; 
        left: 40%; 
        /*height: 40%;*/ 
        padding-top: 3%;
    }
</style>

<div class="overlay-bg">
</div>
<!--<div class="overlay-content popup1 pop_edit_invc_design">
    <p style="font-size: 17px;margin-bottom: 10px;"></p>
    <button class="hq_btn close-syn_yes">Hand Stock</button>
    <button class="hq_btn close-syn_no">As Per Quote</button>
    <button class="hq_btn close-btn">Cancel</button>
</div>-->

<div class="overlay-content popup1 pop_edit_invc_design">
    <form method="post" action="<?=site_url('invoices/quote_to_orders_invoice/invoice_id/' . $invoice->invoice_id)?>">
        <div style="width: 60%; margin-left: 30%;text-align: start;">
            <div class="sup_cntnt"></div>
            <br>
            <input type="submit" value="Submit" class="hq_btn">
            <input type="reset" value="Cancel" class="hq_btn close-btn">
        </div>
    </form>
</div>

<div class="overlay-content popup_new_product">
    <div>
        <p style="font-size: 17px;margin-bottom: 10px;"></p>
        <button class="hq_btn close-syn_yes">Hand Stock</button>
        <button class="hq_btn close-syn_no">As Per Quote</button>
        <button class="hq_btn close-btn">Cancel</button>
    </div>
</div>

