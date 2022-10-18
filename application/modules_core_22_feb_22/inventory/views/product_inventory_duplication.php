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
        background: url('<?php echo base_url() ?>assets/slick/images/info.gif');
        background-repeat: no-repeat;
        display: inline-block;
        margin-left: 10px;
        cursor: pointer;
    }
    
</style>

<script type="text/javascript" src="<?php echo base_url(); ?>assets/jquery/jquery.event.drag-2.0.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/jquery/jquery.cookie.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/jquery/jquery.autogrow-textarea.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/slick/slick.core.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/slick/plugins/slick.autotooltips.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/slick/plugins/slick.rowselectionmodel.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/slick/slick.editors.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/slick/slick.grid.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/slick/slick.dataview.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/slick/plugins/slick.checkboxselectcolumn.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/slick/json2.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/slick/json2.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/jquery/jquery.magnific-popup.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/style/css/magnific-popup.css" />

<div class="section_wrapper">

    <h3 class="title_black">
        Product-Inventory Duplication
    </h3>

    <div class="content toggle no_padding" style="min-height: 600px;">
        <?php $this->load->view('dashboard/system_messages'); ?>
        
        <div id="system_ajax" style="display:none;">
            <div class="success">Successfully applied to product.</div>
        </div>
        
        <style>
            table {
                font-family: arial, sans-serif;
                border-collapse: collapse;
                width: 100%;
            }

            td, th {
                border: 1px solid #dddddd;
                text-align: left;
                padding: 8px;
            }

            tr:nth-child(even) {
                background-color: #dddddd;
            }
            .col-md-6{
                width: 50%;
                float: left;
            }
            #inventoryGrid .slick-headerrow-column.c3,#myGrid .slick-headerrow-column.c0 input{
                visibility: hidden;
            }
            .filterarea{
                text-align: center;
            }
            .filterarea input{

                margin-top: 15px;
                padding: 6px 22px;
            }
            #center_wrapper{
                padding-bottom: 100px;
            }
/*            a{
                font-weight: normal;
            }*/
        </style>
        <body>
            <div class="morefilters" style="margin-top: 20px; margin-left: 20px;">
                <div class="one">
                    <a href="<?php echo site_url() ?>/inventory/link_to_product">Many to Many</a> | 
                    <a href="<?php echo site_url() ?>/inventory/one_to_one_product_inv">One to One</a> | 
                    <a href="<?php echo site_url() ?>/inventory/product_inventory">Product-Inventory</a> |
                    <a href="<?php echo site_url() ?>/inventory/product_inventory_duplication"><strong style="text-decoration: underline;">Product-Inventory-Duplication</strong></a>
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
            <div class="filterarea">
                <input type="button" name="applyinventorytoprod" id="applyinventorytoprod" value="Apply" />
            </div>
            <div class="row" style="margin-top: 30px;">
                <div class="link_inventory_grid">
                    <?php $this->load->view('product_inventory_duplication_grid'); ?>
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
            $('.chkbox_pro input').each(function () {
                if ($(this).prop('checked') == true) {
                    var idatr = $(this).attr('id');
                    idatr = idatr.substring(6,idatr.length);
                    invlist.push(idatr);
                }
            });
            
            var prolistdetail = [];
            for (var i = 0; i < invlist.length; i++) {
                prolistdetail.push(dataView.getItemById(invlist[i]));
            }
            
            if (prolistdetail.length == 0) {
                alert('Please select items first.');
                $(this).removeAttr('disabled');
            } else {
                if (confirm('Are you sure?')) {
                    $('.inventoryGridwrap .loader').addClass('loading');
                    $.ajax({
                        type: 'post',
                        url: "<?php echo site_url('inventory/update_product_duplication?type=one'); ?>",
                        dataType: 'html',
                        data: {prolistdetail: prolistdetail},
                        success: function (data) {
                            window.location.reload();
//                            console.log(data);
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