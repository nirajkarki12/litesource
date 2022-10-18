

<?php echo form_open_multipart($this->uri->uri_string()); ?>
<?php $clean = isset($_GET['clean']) ? $_GET['clean'] : '0'; ?>
<input type="hidden" name="clean" value="<?php echo $clean; ?>">
<input type="hidden" name="invoice_item_id" value="<?php echo $this->mdl_inventory_item->form_value('invoice_item_id'); ?>">
<div class="section_wrapper">

    <h3 class="title_black"><?php echo $this->lang->line('inventory_form'); ?></h3>

    <?php $this->load->view('dashboard/system_messages'); ?>

    <div class="content toggle">
        <?php if ( $this->mdl_inventory_item->form_value('inventory_id') > '0') { ?>
            <dl>
                <dt><label>SKU: </label></dt>
                <dd>
                    <input type="text" disabled="" value="<?= $mcb_inventory_item->sku ?>" />
                </dd>
            </dl>
        <?php } ?>

        <?php if (isset($mcb_inventory_item->quantity) && $this->mdl_inventory_item->form_value('inventory_id') > '0') { ?>
            <dl>
                <dt><label>Quantity: </label></dt>
                <dd>
                    <input type="text" disabled="" value="<?= $mcb_inventory_item->quantity ?>" />
                </dd>
            </dl>
        <?php } ?>
        <?php //if (isset($mcb_inventory_item->qty_pending) && $this->mdl_inventory_item->form_value('inventory_id') > '0') { ?>
        <dl>
            <dt><label>Pending Quantity: </label></dt>
            <dd>
                <input type="text" disabled="" value="<?= (float)$mcb_inventory_item->qty_pending ?>" />
            </dd>
        </dl>
        <?php //} ?>

        <dl>
            <dt><label>Inventory Type </label></dt>
            <dd>
                <select name="inventory_type" onChange="show(this.options[this.selectedIndex].value)" id="inventory_type" <?php if ($this->mdl_inventory_item->form_value('inventory_id') > '0' && $this->mdl_inventory_item->form_value('supplier_id') > 0) {
                    //echo 'disabled';
                } ?>>

                    <option value="0" <?php if ($this->mdl_inventory_item->form_value('inventory_type') == 0) { ?>selected="selected"<?php } ?>>Part</option>
                    <option value="1" <?php if ($this->mdl_inventory_item->form_value('inventory_type') == 1) { ?>selected="selected"<?php } ?>>Grouped Product</option>


                </select>
                <?php if ($this->mdl_inventory_item->form_value('inventory_id') > '0' && $this->mdl_inventory_item->form_value('supplier_id') > 0) {
                    //echo '<input type="hidden" name="inventory_type" value="' . $this->mdl_inventory_item->form_value('inventory_type') . '">';
                } ?>
            </dd>

        </dl>
        <dl>
            <dt><label>Category </label></dt>
            <dd>
                <input list="categoryList" id="category_id" value="<?php echo $this->mdl_inventory_item->form_value('category_deleted')!=1?$this->mdl_inventory_item->form_value('category_name'):'' ?>">
                <datalist id="categoryList">
                    <?php  foreach ($categories as $category) { ?>
                        <option data-value="<?php echo $category->category_name; ?>"
                            <?php if ($this->mdl_inventory_item->form_value('category_id') == $category->category_id) { ?>
                                selected="selected"<?php } ?>>
                            <?php echo $category->category_name  ?>
                        </option>
                    <?php } ?>
                </datalist>
                <input type="hidden" name="category_id" id="category_id-hidden" value="<?php echo $this->mdl_inventory_item->form_value('category_name') ?>">



            </dd>

        </dl>

        <dl>
            <dt><label>Hide From Quote </label></dt>
            <dd>
                <?php
                if($mcb_inventory_item->quote_status=='0'){
                    $chk_yes = '';
                    $chk_no = 'checked';
                } else {
                    $chk_yes = 'checked';
                    $chk_no = '';

                }

                ?>
                <input type="radio" name="quote_status" <?php echo $chk_yes?> value="1">Yes
                <input type="radio" name="quote_status" <?php echo $chk_no?> value="0">No

            </dd>

        </dl>

        <dl>
            <dt><label><?php echo $this->lang->line('supplier'); ?>: </label></dt>
            <dd>
                <select name="supplier_id" id="supplier_id">
                    <?php foreach ($suppliers as $supplier) { ?>
                        <option value="<?php echo $supplier->client_id; ?>"
                            <?php if ($this->mdl_inventory_item->form_value('supplier_id') == $supplier->client_id) { ?>
                                selected="selected"<?php } ?>>
                            <?php echo $supplier->client_name . ($supplier->supplier_name != $supplier->client_name ? ' (' . $supplier->supplier_name . ')' : ''); ?>
                        </option>
                    <?php } ?>
                </select>
            </dd>

        </dl>

        <dl>
            <dt><label><?php echo $this->lang->line('inventory_name'); ?>: </label></dt>
            <dd><input type="text" name="name" id="name" value="<?php echo $this->mdl_inventory_item->form_value('name'); ?>" /></dd>
        </dl>

        <dl style="display:none">
            <dt ><label> Inventory ID</label></dt>
            <dd><input type="text" name="inventory_id" id="inventory_id" value="<?php echo $this->mdl_inventory_item->form_value('inventory_id'); ?>" /></dd>
        </dl>

        <dl id="supplier_cat_row">
            <dt><label><?php echo $this->lang->line('supplier_catalog_number'); ?>: </label></dt>
            <dd><input type="text" name="supplier_code" id="supplier_code" value="<?php echo $this->mdl_inventory_item->form_value('supplier_code'); ?>" /></dd>
        </dl>

        <dl id="supplier_price_row">
            <dt id="price_label"><label><?php echo $this->lang->line('product_supplier_price'); ?>: </label></dt>
            <dd><input type="text" name="supplier_price" id="supplier_price" value="<?php echo $this->mdl_inventory_item->form_value('supplier_price'); ?>" /></dd>
        </dl>

        <dl>
            <dt><label><?php echo $this->lang->line('inventory_base_price'); ?>: </label></dt>
            <dd><input type="text" name="base_price" id="base_price" value="<?php echo $this->mdl_inventory_item->form_value('base_price'); ?>" /></dd>
        </dl>

        <dl>
            <dt><label><?php echo $this->lang->line('inventory_location'); ?>: </label></dt>
            <dd><input type="text" name="location" id="location" value="<?php echo $this->mdl_inventory_item->form_value('location'); ?>" /></dd>
        </dl>

        <dl>
            <dt><label><?php echo $this->lang->line('inventory_description'); ?>: </label></dt>
            <dd><textarea class="big_textarea" name="description" id="description"><?php echo $this->mdl_inventory_item->form_value('description'); ?></textarea></dd>
        </dl>

        <dl  id="supplier_description_row">
            <dt><label><?php echo $this->lang->line('inventory_supplier_decsription'); ?>: </label></dt>
            <dd><textarea class="big_textarea" name="supplier_description" id="supplier_description"><?php echo $this->mdl_inventory_item->form_value('supplier_description'); ?></textarea></dd>
        </dl>
        <dl>
            <dt><label>Dynamic: </label></dt>
            <dd><input type="checkbox" name="use_length" id="use_length" value="1" <?php if ($this->mdl_inventory_item->form_value('use_length') or ( $_POST and uri_assoc('inventory_id'))) {
                    echo 'checked';
                } ?> /></dd>
        </dl>

        <!--<dl>-->
        <!--<dt><label>Archived : </label></dt>-->
        <!--<dd><input type="checkbox" name="is_arichved" id="is_arichved" value="1" <?php // if ($this->mdl_inventory_item->form_value('is_arichved') or ( $_POST and uri_assoc('inventory_id'))) { echo 'checked'; } ?> /></dd>-->
        <!--</dl>-->

        <?php /* only show delet if they are without supplier */ if (uri_assoc('inventory_id') != NULL && (int) uri_assoc('inventory_id') > 0 && $this->mdl_inventory_item->form_value('supplier_id') == 0): ?>
            <dl>
                <dt><label><?php echo $this->lang->line('delete'); ?>: </label></dt>
                <dd>
                    <a href="<?php echo site_url('inventory/delete/inventory_id/' . uri_assoc('inventory_id')); ?>" title="<?php echo $this->lang->line('delete'); ?>" onclick="javascript:if (!confirm('<?php echo $this->lang->line('confirm_delete'); ?>'))
                        return false">
                        <?php echo icon('delete'); ?></a>
                </dd>
            </dl>
        <?php endif; ?>
    </div>

    <?php if (($this->mdl_inventory_item->form_value('inventory_type') == 1) && (int) uri_assoc('inventory_id') > 0) { ?>
        <div id="group">
            <dl>
                <dd id="hiddenInput"></dd>
                <dd id="hiddenDeleteInput">
                </dd>
            </dl>
            <input type="hidden" name="product_name" value="<?php echo $this->mdl_inventory_item->form_value('name'); ?>" />
            <h3 class="title_black"><?php echo $this->lang->line('inventory_items_for_unit_product'); ?></h3>
            <div class="content toggle">
                <dl>
                    <dt><label>Inventory Item : </label></dt>
                    <dd>
                        <select class="inventories" id="inventory_list_options">
                            <?php if (sizeof($all_inventory_items) > 0): ?>
                                <?php foreach ($all_inventory_items as $inventory): ?>
                                    <option id="inLn_<?= $inventory->inventory_id ?>" data-len="<?= $inventory->use_length; ?>" value="<?= $inventory->inventory_id; ?>" ><?php echo $inventory->name; ?></option>
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
                        <input type="text" class="qty_inventory_item" id="inventory_qty" value="1" />
                        <div>Parts used per product</div>
                    </dd>
                </dl>

                <dl>
                    <dt></dt>
                    <dd>
                        <button onclick="addInventoryOnList()" type="button">Add</button>
                    </dd>
                </dl>
                <?php $this->load->view('inventory_grid_relation'); ?>
            </div>
        </div>
    <?php }else { ?>
        <div id="part">
            <h3 class="title_black"><?php echo $this->lang->line('inventory_history'); ?></h3>
            <div class="content toggle">
                <?php $inventory_history ? $this->load->view('inventory_history_grid') : null; ?>
                <dl>
                    <dt><label><?php echo $this->lang->line('inventory_history_qty'); ?>: </label></dt>
                    <dd><input type="text" name="history_qty" id="history_qty" value="<?php echo isset($_POST['history_qty']) ? $_POST['history_qty'] : '' ?>" /></dd>
                </dl>
                

                <div id="qtyErrorMsg" style="text-align: center;margin-top: -5px;font-size: 14px;color: red;margin-bottom: 15px;"></div>
                <dl>
                    <dt><label><?php echo $this->lang->line('inventory_history_notes'); ?>: </label></dt>
                    <dd><textarea class="big_textarea" name="notes" id="notes" value=""><?php echo isset($_POST['notes']) ? $_POST['notes'] : '' ?></textarea></dd>
                </dl>
            </div>
        </div>
    <?php } ?>
    <input type="hidden" id="action_type" name="action_type" value="save"/>
    <input type="submit" id="btn_submit" name="btn_submit" value="Save" />&nbsp;&nbsp;&nbsp;

    <?php if ($this->mdl_inventory_item->form_value('inventory_id') > '0') {
        echo '<input type="submit" id="btn_save_continue" name="btn_save_continue" value="Save and Continue" />&nbsp;&nbsp;&nbsp;';
    } ?>

    <button type="reset" id="btn_cancel" name="btn_cancel" value="Cancel">Cancel</button>
</div>
</form>