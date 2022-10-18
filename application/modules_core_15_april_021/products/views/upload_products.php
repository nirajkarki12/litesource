<?php $this->load->view('dashboard/header'); ?>

<div class="container_10" id="center_wrapper">
    <div class="grid_7" id="content_wrapper">
        <div class="section_wrapper">
            <h3 class="title_black">
                <?php echo $this->lang->line('upload_product_file'); ?>
                <?php $this->load->view('dashboard/btn_add', array('btn_name' => 'download_sample_import', 'btn_value' => 'Download Sample Import File')); ?>
            </h3>
            <?php $this->load->view('dashboard/system_messages'); ?>
            <div class="content toggle">
                <form method="post" action="<?php echo site_url($this->uri->uri_string()); ?>" enctype="multipart/form-data">
                    <dl>
                        <dt><label><?php echo $this->lang->line('select_file'); ?>: </label></dt>
                        <dd><input type="file" name="userfile" size="20" accept=".csv" /></dd>
                        
                    </dl>
                    <label>
                        <input type="checkbox" id="product_overwrite" name="db_product_overwrite" value="1">Overwrite existing database?
                    </label><br>
                    <input type="submit" id="btn_submit" class="chk_overwrite" name="btn_upload_products" value="<?php echo $this->lang->line('upload_product_file'); ?>" />
                    <input type="submit" id="btn_cancel" name="btn_cancel" value="<?php echo $this->lang->line('cancel'); ?>" />
                </form>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view('dashboard/footer'); ?>

<script type="text/javascript">
    $(document).ready(function () {
        $('.chk_overwrite').on('click', function (e) {
            if (document.getElementById("product_overwrite").checked == true) {
                if (!confirm('This will overwrite the database. Please ensure you have a backup. Are you sure you wish to continue?')) {
                    e.preventDefault();
                }
            }
        });
    });
</script>
