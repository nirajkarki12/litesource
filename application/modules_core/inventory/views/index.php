<?php $this->load->view('dashboard/header');?>


<div class="grid_12" id="content_wrapper">

    <h3 class="title_black"><?php echo $this->lang->line('inventory'); ?>    
        <?php $this->load->view('dashboard/btn_add', array('btn_name' => 'btn_add_inventory', 'btn_value' => $this->lang->line('add_inventory'))); ?>
        <?php $this->load->view('dashboard/btn_add', array('btn_name' => 'btn_upload_inventory', 'btn_value' => 'Export/Import Inventory Data File')); ?>
        <?php // $this->load->view('dashboard/btn_add', array('btn_name' => 'btn_export_inventory', 'btn_value' => $this->lang->line('export_inventory'))); ?>
        <!--<select id="inventoryArchive" style="float: right;margin-right: 10px;height: 22px;">-->
            <!--<option onchange="" value="all-inventory-show">---All Inventory---</option>-->
            <!--<option onchange="" <?php // if($this->input->get('only_archived') != NULL){ echo "selected"; }?> value="archived-inventory-only">Archived Only</option>-->
            <!--<option onchange="" <?php // if($this->input->get('show_archived') != NULL){ echo "selected"; }?> value="include-inventory-archived">Include Archived</option>-->
        <!--</select>--> 
        
        
        
        <?php // if($this->input->get('only_archived') != NULL){ ?>   
        <!--<input type="button" name="applyArichved" id="applyUnArchived" value="Un archive" style="margin-right: 10px; float: right" />-->
        <?php // }else{ ?>
        <!--<input type="button" name="applyArichved" id="applyArichved" value="Archive" style="margin-right: 10px; float: right" />-->
        <?php // } ?>
        <!--<input type="button" name="applyArichved" id="applyArichved" value="Archive" style="margin-right: 10px; float: right" />-->
        <input type="button" name="applyDuplicate" id="applyDuplicate" value="Duplicate" style="margin-right: 10px; float: right" />
        
        <input type="button" id="applyInvDelete" value="Delete" style="margin-right: 10px; float: right" />
        
    </h3>

    <div class="content toggle no_padding">


        <!--			--><?php //$this->load->view('order_table');  ?>
        <?php $this->load->view('dashboard/system_messages'); ?>
        <?php if(isset($import_log) && $import_log != ''): ?>
                            <div class="success prodlogs"><?php echo $import_log; ?></div>
                        <?php endif; ?>
        <?php $this->load->view('inventory_grid'); ?>

    </div>




</div>

<?php /* $this->load->view('dashboard/sidebar', array('side_block'=>'orders/sidebar')); */ ?>
<script type="text/javascript">
    $(document).ready(function(){
        $('.debug_text').on('click',function(){
            $(this).find('img').toggleClass('show').toggleClass('hide');
            $('.debug_detail').toggleClass('hide');
        });
        
        // ------  duplicate inventory ---------
        $("#applyDuplicate").on('click', function () {
            var selectedRows = grid.getSelectedRows();
            //event.preventDefault();
            var inven_detail = [];
            for (var k = 0; k < selectedRows.length; k++) {
                var data = dataView.getItem(selectedRows[k]);
                if (typeof data != 'undefined') {
                    // console.log(data);
                    inven_detail.push(data);
                }
            }
            
            if (inven_detail.length == 0) {
                alert('Please select at least one inventory to archive.');
                $(this).removeAttr('disabled');
            } else {
                //console.log(inven_detail);
                if (confirm('Are you sure?')) {
                    $('.inventoryGridwrap .loader').addClass('loading');
                    $.ajax({
                        type: 'post',
                        url: "<?php echo site_url('inventory/doDuplicate'); ?>",
                        dataType: 'html',
                        data: {inven_detail: inven_detail},
                        
                        success: function (data) {
                            //
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
        
        
        
        
        
        
        // ------  duplicate inventory ---------
        $("#applyInvDelete").on('click', function () {
            var selectedRows = grid.getSelectedRows();
            //event.preventDefault();
            var inven_detail = [];
            for (var k = 0; k < selectedRows.length; k++) {
                var data = dataView.getItem(selectedRows[k]);
                if (typeof data != 'undefined') {
                    // console.log(data);
                    inven_detail.push(data);
                }
            }
            
            if (inven_detail.length == 0) {
                alert('Please select at least one inventory to archive.');
                $(this).removeAttr('disabled');
            } else {
                //console.log(inven_detail);
                if (confirm('Are you sure?')) {
                    $('.inventoryGridwrap .loader').addClass('loading');
                    $.ajax({
                        type: 'post',
                        url: "<?php echo site_url('inventory/doInventoryDelete'); ?>",
                        dataType: 'html',
                        data: {inven_detail: inven_detail},
                        
                        success: function (data) {
                            //
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
        
        
        
        
        
        
        
        $("#applyArichved").on('click', function () {
            var selectedRows = grid.getSelectedRows();
            //event.preventDefault();
            var inven_detail = [];
            for (var k = 0; k < selectedRows.length; k++) {
                var data = dataView.getItem(selectedRows[k]);
                if (typeof data != 'undefined') {
                    // console.log(data);
                    inven_detail.push(data);
                }
            }
            
            if (inven_detail.length == 0) {
                alert('Please select at least one inventory to archive.');
                $(this).removeAttr('disabled');
            } else {
                //console.log(inven_detail);
                if (confirm('Are you sure?')) {
                    $('.inventoryGridwrap .loader').addClass('loading');
                    $.ajax({
                        type: 'post',
                        url: "<?php echo site_url('inventory/doArchive'); ?>",
                        dataType: 'html',
                        data: {inven_detail: inven_detail},
                        
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
            var selectedRows = grid.getSelectedRows();
            //event.preventDefault();
            var inven_detail = [];
            for (var k = 0; k < selectedRows.length; k++) {
                var data = dataView.getItem(selectedRows[k]);
                if (typeof data != 'undefined') {
                    // console.log(data);
                    inven_detail.push(data);
                }
            }
            
            if (inven_detail.length == 0) {
                alert('Please select at least one product to un-archive.');
                $(this).removeAttr('disabled');
            } else {
                
                if (confirm('Are you sure?')) {
                    $('.inventoryGridwrap .loader').addClass('loading');
                    $.ajax({
                        type: 'post',
                        url: "<?php echo site_url('inventory/undoArchive'); ?>",
                        dataType: 'html',
                        data: {inven_detail: inven_detail},
                        
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
        
        $('#inventoryArchive').on('change',function(){
            var inventoryArchive = document.getElementById('inventoryArchive').value;
            if(inventoryArchive == 'include-inventory-archived'){
                window.location.href= "<?php echo site_url('inventory?show_archived=true'); ?>";
            }else if(inventoryArchive == 'archived-inventory-only'){
                window.location.href= "<?php echo site_url('inventory?only_archived=true'); ?>";
            }else{
                window.location.href="<?= site_url('inventory') ?>";
            }
        });
        
        
    });
</script>
<?php $this->load->view('dashboard/footer'); ?>
