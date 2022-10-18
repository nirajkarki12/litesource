<?php $this->load->view('dashboard/header'); ?>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/jquery/jquery.autogrow-textarea.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
<script type="text/javascript">

    var suppliers = <?php echo $suppliers_json; ?>;
    var price_label_prefix = '<?php echo $this->lang->line('product_supplier_price'); ?>';
    function supplier_change() {
        var i = $("#supplier_id").attr("selectedIndex");
//
        var supplier = suppliers[i]

        $('#price_label').html('<label>' + price_label_prefix + ' (' + supplier.currency_code + ')</label>');
    }

    $(document).ready(function () {

        $('#product_supplier_description').autogrow();
        $('#product_description').autogrow();
        /*
         $("#supplier_id").bind('change', function(){
         supplier_change();
         
         });
         supplier_change();
         */


    });

</script>

<div class="container_10" id="center_wrapper">

    <div class="grid_7" id="content_wrapper">

        <div class="section_wrapper">

            <h3 class="title_black"><?php echo $this->lang->line('product_form'); ?></h3>

            <?php $this->load->view('dashboard/system_messages'); ?>

            <div class="content toggle">

                <?php echo form_open_multipart($this->uri->uri_string()); ?>
                
                
                
                <dl>
                    <dt><label><?php echo $this->lang->line('catalog_number'); ?>: </label></dt>
                    <dd><input type="text" name="product_name" id="product_name" value="<?php if(set_value('product_name')){ echo set_value('product_name'); }else{ echo $product_detail['product_name']; } ?>" /></dd>
                </dl>

                <dl>
                    <dt><label><?php echo $this->lang->line('product_base_price'); ?>: </label></dt>
                    <dd><input type="text" name="product_base_price" id="product_base_price" value="<?php if(set_value('product_base_price')){ echo set_value('product_base_price'); }else{ echo $product_detail['product_base_price']; } ?>" /></dd>
                </dl>

                <dl>
                    <dt><label><?php echo $this->lang->line('product_description'); ?>: </label></dt>
                    <dd><textarea class="big_textarea" name="product_description" id="product_supplier_description"><?php if(set_value('product_description')){ echo set_value('product_description'); }else{ echo $product_detail['product_description']; } ?></textarea></dd>
                </dl>
                
                <dl>
                    <dt><label><?php echo $this->lang->line('product_active'); ?>: </label></dt>
                    <dd><input type="checkbox" name="product_active" id="client_active" value="1" <?php if ($product_detail['product_active'] or ( !$_POST and ! uri_assoc('product_id'))) { ?>checked="checked"<?php } ?> /></dd>
                </dl>

                <dl>
                    <dt><label>Dynamic Product: </label></dt>
                    <dd><input type="checkbox" name="product_dynamic" id="product_dynamic" value="1" <?php if ($product_detail['product_dynamic'] == '1' ) { ?>checked="checked"<?php } ?> /></dd>
                </dl>

                <dl>
                    <dt><label><?php echo $this->lang->line('product_duplicate'); ?>: </label></dt>
                    <dd><input type="checkbox" name="product_duplicate" id="product_duplicate" value="1" /></dd>
                </dl>
                <?php if(uri_assoc('product_id')): ?>
                <dl>
                    <dt><label><?php echo $this->lang->line('delete'); ?>: </label></dt>
                    <dd>
                        <a href="<?php echo site_url('products/delete/product_id/' . uri_assoc('product_id')); ?>" title="<?php echo $this->lang->line('delete'); ?>" onclick="javascript:if (!confirm('<?php echo $this->lang->line('confirm_delete'); ?>'))
                                    return false">
                            <?php echo icon('delete'); ?></a>
                    </dd>
                </dl>
                <?php endif; ?>
                <dl>
                    <dd id="hiddenInput">
                    <dd id="hiddenDeleteInput">
                    </dd>
                </dl>


                <?php
                /*
                  <dl>
                  <dt><label><?php echo $this->lang->line('product_image'); ?>: </label></dt>
                  <dd><input type="text" name="product_image" id="product_image" value="<?php echo $this->mdl_products->form_value('product_image'); ?>" />
                  echo anchor(site_url('products/upload_image/supplier_id/' . $this->mdl_products->form_value('supplier_id') . '/product_id/' . uri_assoc('product_id')), $this->lang->line('upload_image'));
                  echo form_upload('userfile');
                  echo form_submit('btn_upload', $this->lang->line('upload_image'));
                  </dd>
                  </dl>
                 */
                ?>
            </div>
            <?php if(uri_assoc('product_id')): ?>
            <h3 class="title_black"><?php echo $this->lang->line('inventory_items_for_unit_product'); ?></h3>
            <div class="content toggle">
                <dl>
                    <dt><label>Inventory Item : </label></dt>
                    <dd>
                        <select name="selected_inventory_id" class="inventories" id="inventory_list_options">
                            <?php if (sizeof($all_inventory_items) > 0): ?>
                                <?php foreach ($all_inventory_items as $inventory): ?>
                            <option id="inLn_<?= $inventory->id ?>" data-len="<?= $inventory->use_length; ?>" data-dyn="<?= $product_detail['product_dynamic']; ?>" value="<?= $inventory->id; ?>" ><?php echo $inventory->name; ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        
                        <p class="dd_intro_text">
                      
                        <span id="inventory_item_info"></span>
                        </p>
                    </dd>

                </dl>
                <dl>
                    <dt><label>Quantity : </label></dt>
                    <dd>
                        <input type="text" class="qty_inventory_item" id="qty_inventory_item" name="qty_inventory_item" value="1" />
                          <div>Parts used per product</div>
                    </dd>
                </dl>
                <dl>
                    <dt></dt>
                    <dd>
                        <button onclick="addInventoryList()" type="button">Add</button>
                    </dd>
                </dl>
                <?php $this->load->view('inventory_grid'); ?>
                
            </div>
            <?php endif; ?>
            <div class="content toggle">
                <input style="margin:15px 10px 0px 190px" onclick="checkDynamic()" type="submit" id="btn_submit" name="btn_submit" value="<?php echo $this->lang->line('submit'); ?>" />
                <input type="submit" id="btn_cancel" name="btn_cancel" value="<?php echo $this->lang->line('cancel'); ?>" />
            </div>
        </div>

        </form>
    </div>
</div>

<?php $this->load->view('dashboard/footer'); ?>

<script type="text/javascript">
    
function checkDynamic(){
    var dynChk = document.getElementById('product_dynamic').checked;
    if(dynChk == true){   
        var answer = confirm("Please ensure that the related inventory that uses length is flagged accordingly.");
        if (answer) {
            //some code
        }
        else {
            event.preventDefault();
        }
    }
}

$(document).ready(function(){
    
    $('#inventory_list_options').on('change keypress keyup blur input',function (event) {
        var inventoryId = $('#inventory_list_options').val();
        var lnChk = $('#inLn_'+inventoryId).attr('data-len');
        var lnDyn = $('#inLn_'+inventoryId).attr('data-dyn');
        if( (lnChk == '1') && (lnDyn == '1')  ){
            $('.qty_inventory_item').attr('disabled', 'disabled');
            $('.qty_inventory_item').attr('value', '1');
        }else{
            $('.qty_inventory_item').removeAttr('disabled');
        }
    });
});








</script>
