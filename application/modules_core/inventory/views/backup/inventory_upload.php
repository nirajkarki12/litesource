<?php $this->load->view('dashboard/header'); ?>
<?php $this->load->view('dashboard/jquery_date_picker'); ?>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>


<div class="container_10" id="center_wrapper">
    <div class="grid_7" id="content_wrapper">

        <div class="section_wrapper">

            <h3 class="title_black"><?php echo $this->lang->line('export_inventory'); ?></h3>

            <?php $this->load->view('dashboard/system_messages'); ?>

            <div class="content toggle">
                <form method="post" action="<?php echo site_url($this->uri->uri_string()); ?>" >
                    <input type="hidden" name="type" value="0">
<!--                     <dl>
                        <dt><label>Type</label></dt>
                        <dd><select name="type">
                                <option value="0">Parts</option>
                                <option value="1">Grouped Products</option>
                            </select>
                        </dd>
                    </dl>-->
                    <dl>
                        <dt><label><?php echo $this->lang->line('changed_since_date'); ?>: </label></dt>
                        <dd><input class="datepicker" type="text" name="changed_since_date" value="" /></dd>
                    </dl>
                    <dl>
                        <dt><label><?php echo $this->lang->line('supplier_text'); ?>: </label></dt>
                        <dd>
                            <select name="supplier_list[]" class="supplier" id="supplier_multiselect" multiple="multiple">
                                <?php if(sizeof($suppliers) > 0): ?>
                                <?php foreach($suppliers as $supplier): ?>
                                <option value="<?php echo $supplier->client_id; ?>" ><?php echo $supplier->client_name; ?></option>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <br>
                            <label>
                                <input type="checkbox" id="export_include_archived" name="export_include_archived" value="1">Include Archived ?
                            </label>
                        </dd>
                    </dl>

                    <input type="submit" id="btn_submit" name="btn_export_inventory" value="<?php echo $this->lang->line('export_inventory'); ?>" />
                    <input type="submit" id="btn_cancel" name="btn_cancel" value="<?php echo $this->lang->line('cancel'); ?>" />
                </form>

            </div>

        </div>



    </div>
</div>









<div class="container_10" id="center_wrapper">
    <div class="grid_7" id="content_wrapper">

        <div class="section_wrapper">

            <h3 class="title_black"><?php echo $this->lang->line('inventory_upload'); ?>
                <?php // $this->load->view('dashboard/btn_add', array('btn_name' => 'download_sample_import', 'btn_value' => 'Download Sample Import File')); ?>
            </h3>

            <?php $this->load->view('dashboard/system_messages'); ?>

            <div class="content toggle">

                <form method="post" action="<?php echo site_url($this->uri->uri_string()); ?>" enctype="multipart/form-data">

                    <dl>
                        <dt><label><?php echo $this->lang->line('select_file'); ?>: </label></dt>
                        <dd><input type="file" name="userfile" size="20" /></dd>
                    </dl>
                    
                    <label>
                        <input type="checkbox" id="product_type" name="db_inventory_overwrite" value="1">Overwrite database(only parts)
                    </label><br>
                    
                    <input type="submit" id="btn_submit" name="btn_upload_inventory" class="chk_inv_type"  value="<?php echo $this->lang->line('inventory_upload'); ?>" />
                    <input type="submit" id="btn_cancel" name="btn_cancel" value="<?php echo $this->lang->line('cancel'); ?>" />

                </form>

            </div>

        </div>

    </div>
</div>

<?php $this->load->view('dashboard/footer'); ?>

<script type="text/javascript">
    $(document).ready(function () {
        $('#supplier_multiselect').select2();
    });
</script>

<script type="text/javascript">
    $(document).ready(function () {
        $('.chk_inv_type').on('click', function (e) {
            if (document.getElementById("product_type").checked == true) {
                if (!confirm('This will overwrite the database. Please ensure you have a backup. Are you sure you wish to continue?')) {
                    e.preventDefault();
                }
            }
        });
    });
</script>