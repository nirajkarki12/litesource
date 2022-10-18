<?php $this->load->view('dashboard/header'); ?>
<style>
    body, html {
        margin: 0;
        padding: 0;
        /*        overflow:hidden;*/
    }

    .slick-cell.cell-right-align {
        text-align: right;
    }

    input.editor-text {
        width: 100%;
        height: 100%;
        border: 0;
        margin: 0;
        background: transparent;
        outline: 0;
        padding: 0;

    }
    .mygridwrap,.inventoryGridwrap{
        position: relative;
    }
    .loader{
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        width:100%;
        display: none;
        background: #000;
        opacity: 0.4;
        z-index: 999999;
    }
    .loader.loading{
        display: block;
    }
    .loader img{
        position: absolute;
        top: 50%;
        width: auto;
        left: 50%;
    }
    .info-link{
        width: 13px;
        height: 13px;
        background: url('<?php echo base_url() ?>assets/latest/images/info.gif');
        background-repeat: no-repeat;
        display: inline-block;
        margin-left: 10px;
        cursor: pointer;
    }
    
</style>

<script type="text/javascript" src="<?php echo base_url(); ?>assets/jquery/jquery.event.drag-2.0.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/jquery/jquery.cookie.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/jquery/jquery.autogrow-textarea.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/latest/slick.core.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/latest/plugins/slick.autotooltips.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/latest/plugins/slick.rowselectionmodel.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/latest/plugins/slick.cellselectionmodel.js"></script>

<script type="text/javascript" src="<?php echo base_url(); ?>assets/latest/plugins/slick.cellrangedecorator.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/latest/plugins/slick.cellrangeselector.js"></script>

<script type="text/javascript" src="<?php echo base_url(); ?>assets/latest/controls/slick.pager.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/latest/controls/slick.columnpicker.js"></script>

<script type="text/javascript" src="<?php echo base_url(); ?>assets/latest/slick.editors.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/latest/slick.formatters.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/latest/slick.groupitemmetadataprovider.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/latest/slick.grid.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/latest/slick.dataview.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/latest/plugins/slick.checkboxselectcolumn.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/slick/json2.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/jquery/jquery.magnific-popup.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/style/css/magnific-popup.css" />

<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/latest/css/smoothness/jquery-ui-1.11.3.custom.css" />
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/latest/css/smoothness/jquery-ui-1.11.3.custom.css" />
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/latest/slick.grid.css" />
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/latest/slick-default-theme.css" />

<div class="section_wrapper">

    <h3 class="title_black">
        Products - Inventory
    </h3>

    <div class="content toggle no_padding" style="min-height: 600px;">
        <?php $this->load->view('dashboard/system_messages'); ?>
        
        <div id="system_ajax" style="display:none;">
            <div class="success">Successfully updated qty.</div>
        </div>
        
        <style>
            



* {
  font-family: arial;
  font-size: 8pt;
}

/*body {
  background: beige;
  padding: 0;
  margin: 8px;
}*/

h2 {
  font-size: 10pt;
  border-bottom: 1px dotted gray;
}

ul {
  margin-left: 0;
  padding: 0;
  cursor: default;
}

li {
  background: url("../images/arrow_right_spearmint.png") no-repeat center left;
  padding: 0 0 0 14px;

  list-style: none;
  margin: 0;
}

#myGrid {
  background: white;
  outline: 0;
  
}

.grid-header {
  border: 1px solid gray;
  border-bottom: 0;
  border-top: 0;
  background: url('../images/header-bg.gif') repeat-x center top;
  color: black;
  height: 24px;
  line-height: 24px;
}

.grid-header label {
  display: inline-block;
  font-weight: bold;
  margin: auto auto auto 6px;
}

.grid-header .ui-icon {
  margin: 4px 4px auto 6px;
  background-color: transparent;
  border-color: transparent;
}

.grid-header .ui-icon.ui-state-hover {
  background-color: white;
}

.grid-header #txtSearch {
  margin: 0 4px 0 4px;
  padding: 2px 2px;
  -moz-border-radius: 2px;
  -webkit-border-radius: 2px;
  border: 1px solid silver;
}

.options-panel {
  -moz-border-radius: 6px;
  -webkit-border-radius: 6px;
  border: 1px solid silver;
  background: #f0f0f0;
  padding: 4px;
  margin-bottom: 20px;
  width: 320px;
  position: absolute;
  top: 0px;
  left: 650px;
}

/* Individual cell styles */
.slick-cell.task-name {
  font-weight: bold;
  text-align: right;
}

.slick-cell.task-percent {
  text-align: right;
}

.slick-cell.cell-move-handle {
  font-weight: bold;
  text-align: right;
  border-right: solid gray;

  background: #efefef;
  cursor: move;
}

.cell-move-handle:hover {
  background: #b6b9bd;
}

.slick-row.selected .cell-move-handle {
  background: #D5DC8D;
}

.slick-row .cell-actions {
  text-align: left;
}

.slick-row.complete {
  background-color: #DFD;
  color: #555;
}

.percent-complete-bar {
  display: inline-block;
  height: 6px;
  -moz-border-radius: 3px;
  -webkit-border-radius: 3px;
}

/* Slick.Editors.Text, Slick.Editors.Date */
input.editor-text {
  width: 100%;
  height: 100%;
  border: 0;
  margin: 0;
  background: transparent;
  outline: 0;
  padding: 0;

}

.ui-datepicker-trigger {
  margin-top: 2px;
  padding: 0;
  vertical-align: top;
}

/* Slick.Editors.PercentComplete */
input.editor-percentcomplete {
  width: 100%;
  height: 100%;
  border: 0;
  margin: 0;
  background: transparent;
  outline: 0;
  padding: 0;

  float: left;
}

.editor-percentcomplete-picker {
  position: relative;
  display: inline-block;
  width: 16px;
  height: 100%;
  background: url("../images/pencil.gif") no-repeat center center;
  overflow: visible;
  z-index: 1000;
  float: right;
}

.editor-percentcomplete-helper {
  border: 0 solid gray;
  position: absolute;
  top: -2px;
  left: -9px;
  background: url("../images/editor-helper-bg.gif") no-repeat top left;
  padding-left: 9px;

  width: 120px;
  height: 140px;
  display: none;
  overflow: visible;
}

.editor-percentcomplete-wrapper {
  background: beige;
  padding: 20px 8px;

  width: 100%;
  height: 98px;
  border: 1px solid gray;
  border-left: 0;
}

.editor-percentcomplete-buttons {
  float: right;
}

.editor-percentcomplete-buttons button {
  width: 80px;
}

.editor-percentcomplete-slider {
  float: left;
}

.editor-percentcomplete-picker:hover .editor-percentcomplete-helper {
  display: block;
}

.editor-percentcomplete-helper:hover {
  display: block;
}

/* Slick.Editors.YesNoSelect */
select.editor-yesno {
  width: 100%;
  margin: 0;
  vertical-align: middle;
}

/* Slick.Editors.Checkbox */
input.editor-checkbox {
  margin: 0;
  height: 100%;
  padding: 0;
  border: 0;
}

.slick-columnpicker {
  border: 1px solid #718BB7;
  background: #f0f0f0;
  padding: 6px;
  -moz-box-shadow: 2px 2px 2px silver;
  -webkit-box-shadow: 2px 2px 2px silver;
  box-shadow: 2px 2px 2px silver;
  min-width: 100px;
  cursor: default;
}

.slick-columnpicker li {
  list-style: none;
  margin: 0;
  padding: 0;
  background: none;
}

.slick-columnpicker input {
  margin: 4px;
}

.slick-columnpicker li a {
  display: block;
  padding: 4px;
  font-weight: bold;
}

.slick-columnpicker li a:hover {
  background: white;
}
        </style>
        <body>
            <div class="morefilters" style="margin-top: 20px; margin-left: 20px;">
                <div class="one">
                    <a href="<?php echo site_url() ?>/inventory/link_to_product">Many to Many</a> | 
                    <a href="<?php echo site_url() ?>/inventory/one_to_one_product_inv">One to One</a> | 
                    <a href="<?php echo site_url() ?>/inventory/product_inventory"><strong style="text-decoration: underline;">Product-Inventory</strong></a> |
                    <a href="<?php echo site_url() ?>/inventory/product_inventory_duplication">Product-Inventory-Duplication</a>
                </div>
                <!--                <div class="one">
                                    <label><input type="checkbox" id="showunlinedinventory" /><span>Show Unlinked Inventory</span></label>
                                </div>
                                <div class="two">
                                    <label><input type="checkbox" id="showunlinedproducts" /><span>Show Unlinked Products</span></label>
                                </div>-->
                <!--                <div class="three">
                                    <label><input type="checkbox" id="show" /><span>Show Inventory and Products with same name</span></label>
                                </div>-->
            </div>
<!--            <div class="filterarea">
                <input type="button" name="applyinventorytoprod" id="applyinventorytoprod" value="Apply" />
            </div>-->
            <div class="row" style="margin-top: 30px;">
                <div class="link_inventory_grid">
                    <?php $this->load->view('product_inventory_grid'); ?>
                </div>

            </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        
        $("#applyinventorytoprod").on('click', function () {
            grid.getEditorLock().commitCurrentEdit();
            //$(this).attr('disabled', 'disabled');
            var invlist = [];
            
            $('.chkbox_inv input').each(function () {
                if ($(this).prop('checked') == true) {
                    var idatr = $(this).attr('id');
                    idatr = idatr.substring(6,idatr.length);
                    invlist.push(idatr);
                }
            });
            
            var invlistdetail = [];
            for (var i = 0; i < invlist.length; i++) {
                invlistdetail.push(dataView.getItemById(invlist[i]));
            }
            
            if (invlistdetail.length == 0) {
                alert('Please select items first.');
                $(this).removeAttr('disabled');
            } else {
                if (confirm('Are you sure?')) {
               
                    $('.inventoryGridwrap .loader').addClass('loading');
                    
                    $.ajax({
                        type: 'post',
                        url: "<?php echo site_url('inventory/updateinvprodrelation?type=one'); ?>",
                        dataType: 'html',
                        data: {invlistdetail: invlistdetail},
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
    });

</script>

<?php $this->load->view('dashboard/footer'); ?>