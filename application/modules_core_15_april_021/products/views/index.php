<?php $this->load->view('dashboard/header'); ?>


<div class="grid_12" id="content_wrapper">

    
	<div class="section_wrapper">

		<h3 class="title_black"><?php echo $this->lang->line('products'); ?>
		<?php //if ($this->session->userdata('global_admin')) { ?>
		<?php $this->load->view('dashboard/btn_add', array('btn_name'=>'btn_add_product', 'btn_value'=>$this->lang->line('add_product'))); ?>
		<?php $this->load->view('dashboard/btn_add', array('btn_name'=>'btn_upload_products', 'btn_value'=>'Upload Product')); ?>
                <?php $this->load->view('dashboard/btn_add', array('btn_name'=>'btn_export_products', 'btn_value'=>$this->lang->line('export_product_data'))); ?>
                    
                <?php if($this->input->get('only_archived') != NULL){ ?>   
                <input type="button" name="applyArichved" id="applyUnArchived" value="Un archive" style="margin-right: 10px; float: right" />
                <?php }else{ ?>
                <input type="button" name="applyArichved" id="applyArichved" value="Archive" style="margin-right: 10px; float: right" />
                <?php } ?>
                
                <select id="productArchive" style="float: right; margin-right: 10px">
                    <option onchange="" value="all-product-show">---All Products---</option>
                    <option onchange="" <?php if($this->input->get('only_archived') != NULL){ echo "selected"; }?> value="archived-product-only">Archived Only</option>
                    <option onchange="" <?php if($this->input->get('show_archived') != NULL){ echo "selected"; }?> value="include-product-archived">Include Archived</option>
                </select> 
                
                <?php //} ?>
			
			
		<select id="suppliers" style="float: right; margin-top: 10px; margin-right: 10px;"/>
			<option value="0">(Supplier)</option>
		<?php foreach ($supplier_with_product as $supplier) { ?>
                    <?php    if($supplier->show_in_product_page=='1'){?>
			<option value="<?php echo $supplier->supplier_id; ?>" <?php if($supplier->supplier_id == $supplier_id) { ?>selected="selected"<?php } ?>><?php echo $supplier->supplier_name; ?></option>
                <?php }} ?>
		</select>
			
		<input type="text" placeholder="<?php echo $this->lang->line('catalog_number'); ?>" id="product_name" style="float: right; margin-top: 10px; margin-right: 10px; width: 200px;"/>
		
		</h3>
	
            
		<div class="content toggle no_padding">

			<?php $this->load->view('dashboard/system_messages'); ?>
                        <?php if(isset($import_log) && $import_log != ''): ?>
                            <div class="success prodlogs"><?php echo $import_log; ?></div>
                        <?php endif; ?>
			<?php $this->load->view('product_list'); ?>
                            
		</div>
            
	</div>

</div>

<?php //$this->load->view('dashboard/sidebar', array('side_block'=>'products/sidebar')); ?>
<script type="text/javascript">
    $(document).ready(function(){
        $('.debug_text').on('click',function(){
            $(this).find('img').toggleClass('show').toggleClass('hide');
            $('.debug_detail').toggleClass('hide');
        });
        
        $("#applyArichved").on('click', function () {
            grid.getEditorLock().commitCurrentEdit();
            //$(this).attr('disabled', 'disabled');
            var prolist = [];
            
            $('.chkbox_pro input').each(function () {
                if ($(this).prop('checked') == true) {
                    var idatr = $(this).attr('id');
                    idatr = idatr.substring(6,idatr.length);
                    prolist.push(idatr);
                }
            });
            
            var prolistdetail = [];
            for (var i = 0; i < prolist.length; i++) {
                prolistdetail.push(dataView.getItemById(prolist[i]));
            }
            
            if (prolistdetail.length == 0) {
                alert('Please select at least one product to archive.');
                $(this).removeAttr('disabled');
            } else {
                
                if (confirm('Are you sure?')) {
                    $('.inventoryGridwrap .loader').addClass('loading');
                    $.ajax({
                        type: 'post',
                        url: "<?php echo site_url('products/doArchive'); ?>",
                        dataType: 'html',
                        data: {prolistdetail: prolistdetail},
                        
                        success: function (data) {
                            window.location.reload();
                        },
                        error: function () {
                            alert('could not get data');
                        }
                    });
                } else {
                    $(this).removeAttr('disabled');
                }
            }
        });
        
        
        
        $("#applyUnArchived").on('click', function () {
            grid.getEditorLock().commitCurrentEdit();
            //$(this).attr('disabled', 'disabled');
            var prolist = [];
            
            $('.chkbox_pro input').each(function () {
                if ($(this).prop('checked') == true) {
                    var idatr = $(this).attr('id');
                    idatr = idatr.substring(6,idatr.length);
                    prolist.push(idatr);
                }
            });
            
            var prolistdetail = [];
            for (var i = 0; i < prolist.length; i++) {
                prolistdetail.push(dataView.getItemById(prolist[i]));
            }
            
            if (prolistdetail.length == 0) {
                alert('Please select at least one product to un-archive.');
                $(this).removeAttr('disabled');
            } else {
                
                if (confirm('Are you sure?')) {
                    $('.inventoryGridwrap .loader').addClass('loading');
                    $.ajax({
                        type: 'post',
                        url: "<?php echo site_url('products/undoArchive'); ?>",
                        dataType: 'html',
                        data: {prolistdetail: prolistdetail},
                        
                        success: function (data) {
                            window.location.reload();
                        },
                        error: function () {
                            alert('could not get data');
                        }
                    });
                } else {
                    $(this).removeAttr('disabled');
                }
            }
        });
        
        
        
        $('#inventoryGrid .slick-headerrow-column input').on('keyup',function(){
            grid.setSelectedRows([]);
        });
        
        $('#productArchive').on('change',function(){
            var productArchive = document.getElementById('productArchive').value;
            if(productArchive == 'include-product-archived'){
                window.location.href= "<?php echo site_url('products?show_archived=true'); ?>";
            }else if(productArchive == 'archived-product-only'){
                window.location.href= "<?php echo site_url('products?only_archived=true'); ?>";
            }else if(productArchive == 'all-product-show'){
                window.location.href="<?= site_url('products') ?>";
            }
        });
        
        
    });
</script>
<?php $this->load->view('dashboard/footer'); ?>