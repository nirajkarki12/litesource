<?php $this->load->view('dashboard/header'); ?>
<?php // echo modules::run('invoices/widgets/generate_dialog');?>
<div class="grid_12" id="content_wrapper">
    <div class="section_wrapper">
        <h3 class="title_black">
            <?php echo $this->lang->line(uri_seg(1)); ?>
            <?php $this->load->view('dashboard/btn_add', array('btn_name' => (uri_seg_is('invoices')) ? 'btn_add_invoice' : 'btn_add_quote', 'btn_value' => (uri_seg_is('invoices')) ? $this->lang->line('create_invoice') : $this->lang->line('create_quote'))); ?>
            <?php //$this->load->view('dashboard/btn_add', array('btn_name'=>(!uri_assoc('is_quote')) ? 'btn_add_invoice' : 'btn_add_quote', 'btn_value'=>(!uri_assoc('is_quote')) ? $this->lang->line('create_invoice') : $this->lang->line('create_quote'))); ?>    
            <?php //$this->load->view('dashboard/btn_add', array('btn_name'=>(!uri_assoc('is_quote')) ? 'btn_invoice_search' : 'btn_quote_search', 'btn_value'=>(!uri_assoc('is_quote')) ? $this->lang->line('invoice_search') : $this->lang->line('quote_search'))); ?>
            <?php if (uri_seg(1) == 'invoices'){ ?>
            <form method="post" style="float: right; margin-right: 5px" action="<?= site_url('invoices/download_all_csv');?>">
                <input type="reset" onclick="choose_option_to_csv()" value="CSV »">
                <!--<input type="submit" name="" value="CSV »">-->
            </form>
            <?php } ?>
        </h3>
        <div class="content toggle no_padding">
            <?php $this->load->view('dashboard/system_messages'); ?>
            <?php $this->load->view('invoice_grid'); ?>
        </div>
    </div>
</div>
<?php $this->load->view('dashboard/footer'); ?>

<?php 
//$c_stt = '<option value="all">--all--</option>';
$c_stt = '';
$clien_state = array_to_groupby('cs', (json_decode( $quote_invoice )->clients));
foreach ($clien_state as $val) {
    if($val['cs'] != ''){
        $c_stt .=  '<option value="'.$val['cs'].'">'.$val['cs'].'</option>';
    }
}

//echo '<pre>';
//print_r($c_stt);

?>

<script type="text/javascript">

function choose_option_to_csv(){
    
    var this_form_url = '<?=site_url("invoices/download_all_csv_by_custom");?>';
    var cs_options = '<?=$c_stt?>';
    
    jQuery('#lite_temp_model').remove();
    var lite_model_div = document.createElement("div");
    lite_model_div.setAttribute("id", "lite_temp_model");
    lite_model_div.setAttribute("class", "lite_model");
    document.body.appendChild(lite_model_div);
    var lite_model = jQuery("#lite_temp_model").get(0);
    lite_model.style.display = "block";
    var bcr_popup_content = '<form action="'+this_form_url+'" method="post"> \n\
                            <div class="lite_model-header"><span class="lite_model_close" onclick="lite_close_popup()">&times;</span>\n\
                                Choose the required options:\n\
                            </div>\n\
                            <div class="lite_model-body">\n\
                                <div style="width:100%; display: inline-flex;">\n\
                                    <div style="width:50%;">\n\
                                        <h6>Choose State</h6>\n\
                                        <select name="selected_state[]" multiple>\n\
                                        '+cs_options+'\n\
                                        </select>\n\
                                    </div>\n\
                                    <div style="width:50%;text-align: right;">\n\
                                        <div>\n\
                                            <h6>Sort Field</h6>\n\
                                            <select name="sort_field">\n\
                                                <option value="">---none---</option>\n\
                                                <option value="c_tbl.client_name">Client Name</option>\n\
                                                <option value="p_tbl.project_name">project Name</option>\n\
                                                <option value="c_tbl.client_state">State</option>\n\
                                                <option value="i.invoice_date_entered">Date</option>\n\
                                                <option value="o_amount">Total Amount</option>\n\
                                            </select>\n\
                                        </div>\n\
                                        <div>\n\
                                            <h6>Sort Direction</h6>\n\
                                            <select name="sort_by">\n\
                                                <option value="SORT_DESC">DESC</option>\n\
                                                <option value="SORT_ASC">ASC</option>\n\
                                            </select>\n\
                                        </div>\n\
                                        <div>\n\
                                            <input type="submit" value="Download">\n\
                                        </div>\n\
                                    </div>\n\
                                </div>\n\
                            </div>\n\
                            </form>\n\
                            <div class="lite_model-footer"></div>';
    lite_model_div.innerHTML = '<div class="lite_model-content">'+bcr_popup_content+'</div>';
    
    
}
function lite_close_popup(){
    jQuery('#lite_temp_model').remove();
}
//window.onclick = function (event) {
//    var lite_model = jQuery("#lite_temp_model").get(0);
//    if (event.target == lite_model) {
//        jQuery('#lite_temp_model').remove();
//    }
//}

</script>