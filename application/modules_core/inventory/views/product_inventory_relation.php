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
        Delink To Products

    </h3>

    <div class="content toggle no_padding" style="min-height: 600px;">
        <?php $this->load->view('dashboard/system_messages'); ?>
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
        </style>
        <body>
<!--            <div class="morefilters" style="margin-top: 20px; margin-left: 20px;">
                <div class="one">
                    <a href="<?php echo site_url() ?>/inventory/link_to_product"><strong style="text-decoration: underline;">Many to Many</strong></a> | 
                    <a href="<?php echo site_url() ?>/inventory/one_to_one_product_inv">One to One</a> | 
                    <a href="<?php echo site_url() ?>/inventory/product_inventory">Product-Inventory</a> |
                    <a href="<?php echo site_url() ?>/inventory/product_inventory_duplication">Product-Inventory-Duplication</a>
                </div>
                <div class="one">
                    <label><input type="checkbox" id="showunlinedinventory" /><span>Show Unlinked Inventory</span></label>
                </div>
                <div class="two">
                    <label><input type="checkbox" id="showunlinedproducts" /><span>Show Unlinked Products</span></label>
                </div>
                                <div class="three">
                                    <label><input type="checkbox" id="show" /><span>Show Inventory and Products with same name</span></label>
                                </div>
            </div>-->
            
            <div class="filterarea">
                <input type="button" name="applyinventorytoprod" id="applyinventorytoprod" value="Clear" />
            </div>
            <div class="row" style="margin-top: 30px;">
                <div class="col-md-6 link_inventory_grid">
                    <?php $this->load->view('link_inventory_grid'); ?>
                </div>
                <div class="col-md-6 link_product_grid">
                    <?php $this->load->view('link_product_grid'); ?>
                </div>
            </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        $("#applyinventorytoprod").on('click', function () {
            $(this).attr('disabled', 'disabled');

            var prodlistdetail = [];
            var selectedRows = [];
            
            for(var m = 0; m<selectedPds.length; m++){
                if(!inArray(selectedPds[m],selectedRows)){
                    selectedRows.push(selectedPds[m]);
                }
            }
            
            for (var k = 0; k < selectedRows.length; k++) {
                var data = dataView2.getItemById(selectedRows[k]);
                if (typeof data != 'undefined') {
                    var tinv = {};
                    tinv['id'] = data.id;
                    prodlistdetail.push(tinv);
                }
            }
            //console.log(prodlistdetail);
            
            var invlist = [];
            for(var m = 0; m<selectedInvs.length; m++){
                if(!inArray(selectedInvs[m],invlist)){
                    invlist.push(selectedInvs[m]);
                }
            }
            
            var invlistdetail = [];
            for (var i = 0; i < invlist.length; i++) {
                var tinv = {};
                var d = dataView.getItemById(invlist[i]);
                tinv['id'] = d['id'];
                tinv['qty'] = d['qty'];
                invlistdetail.push(tinv);
            }
            //console.log(invlistdetail); return false;
            
//            console.log(invlistdetail);
//            console.log(prodlistdetail);
            
            ///invlistdetail = JSON.stringify(invlistdetail);
            //prodlistdetail = JSON.stringify(prodlistdetail);
            
//            console.log(invlistdetail);
//            console.log(prodlistdetail);
//            event.preventDefault();
            
            if (invlistdetail.length == 0) {
                alert('Please select inventory.');
                $(this).removeAttr('disabled');
            } else if (prodlistdetail.length == 0) {
                alert('Please select product.');
                $(this).removeAttr('disabled');
            } else {
                if (confirm('Are you sure?')) {
                    $('.inventoryGridwrap .loader').addClass('loading');
                    $('.mygridwrap .loader').addClass('loading');
                    $.ajax({
                        type: 'post',
                        url: "<?php echo site_url('inventory/delinkproductinventory'); ?>",
                        dataType: 'html',
                        data: {pd: prodlistdetail,id: invlistdetail},
                        success: function (data) {
                        // alert(data);
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

        $('#showunlinedinventory').on('click', function () {
            $('.inventoryGridwrap .loader').addClass('loading');
            if ($(this).prop('checked') == true) {
                $.ajax({
                    url: "<?php echo site_url('inventory/get_unlinked_inventory_list'); ?>",
                    dataType: 'json',
                    type: 'POST',
                    success: function (data) {
                        if (data == 'session_expired') {
                            window.location.reload();
                        }
                        dataView.beginUpdate();


                        suppliers = data.suppliers;
                        // Update supplier indexiig

                        for (var i = 0, l = suppliers.length; i < l; i++) {
                            var id = suppliers[i]['supplier_id'];
                            suppliersById[id] = i;
                        }

                        //
                        // // Update project indexing
                        // for (var i = 0, l = projects.length; i < l; i++) {
                        //     var id = projects[i]['id'];
                        //     projectsById[id] = i;
                        // }

                        var inventoryItems = data.inventory;
                        for (var i = 0, l = inventoryItems.length; i < l; i++) {
                            updateInventoryDetails(inventoryItems[i]);
                        }
                        dataView.setItems(inventoryItems, 'id');
                        dataView.setFilter(filter);
                        //dataView.sort(comparer, sortDir);
                        dataView.endUpdate();
                        $('.inventoryGridwrap .loader').removeClass('loading');
                    }
                });

            } else {
                $.ajax({
                    url: "<?php echo site_url('inventory/get_inventory_list'); ?>",
                    dataType: 'json',
                    type: 'POST',
                    success: function (data) {
                        if (data == 'session_expired') {
                            window.location.reload();
                        }
                        dataView.beginUpdate();


                        suppliers = data.suppliers;
                        // Update supplier indexiig

                        for (var i = 0, l = suppliers.length; i < l; i++) {
                            var id = suppliers[i]['supplier_id'];
                            suppliersById[id] = i;
                        }

                        //
                        // // Update project indexing
                        // for (var i = 0, l = projects.length; i < l; i++) {
                        //     var id = projects[i]['id'];
                        //     projectsById[id] = i;
                        // }

                        var inventoryItems = data.inventory;
                        for (var i = 0, l = inventoryItems.length; i < l; i++) {
                            updateInventoryDetails(inventoryItems[i]);
                        }
                        dataView.setItems(inventoryItems, 'id');
                        dataView.setFilter(filter);
                        //dataView.sort(comparer, sortDir);
                        dataView.endUpdate();
                        $('.inventoryGridwrap .loader').removeClass('loading');
                    }
                });
            }
        });

        $('#showunlinedproducts').on('click', function () {
            $('.mygridwrap .loader').addClass('loading');
            if ($(this).prop('checked') == true) {
                $.ajax({
                    type: 'POST',
                    url: "<?php echo site_url('products/getUnlinkedProducts'); ?>",
                    dataType: 'json',
                    success: function (data) {
                        if (data == 'session_expired') {
                            window.location.reload();
                        }
                        var products = data.products;

                        dataView2.setItems(products, 'id');
                        dataView2.setFilter(filter2);
                        $('.mygridwrap .loader').removeClass('loading');
                    }
                });
            } else {
                $.post("<?php echo site_url('products/get_products_JSON_link'); ?>", {
                }, function (data) {
                    if (data == 'session_expired') {
                        window.location.reload();
                    }
                    var products = data.products;

                    dataView2.setItems(products, 'id');
                    dataView2.setFilter(filter2);
                    $('.mygridwrap .loader').removeClass('loading');
                }, "json");
            }
        });

        $(document).on('click', '.open-popup', function (e) {
            var el = $(this);
            e.preventDefault();
            var url = $(this).attr('href');

            $.magnificPopup.open({
                items: {
                    src: '<div class="small-dialog" style="text-align:center;"><img src="<?php echo base_url().'assets/style/img/loading.gif'; ?>" /></div>', // can be a HTML string, jQuery object, or CSS selector
                    type: 'inline',
                    fixedContentPos: false,
                    fixedBgPos: true,
                    overflowY: 'auto',
                    closeBtnInside: true,
                    preloader: false,
                    midClick: true,
                    removalDelay: 300,
                    mainClass: 'my-mfp-zoom-in'
                },
                callbacks: {
                    beforeOpen: function () {
                        this.st.mainClass = el.attr('data-effect');
                    }
                },
            });

            $.ajax({
                type: 'POST',
                url: url,
                data: {name: el.data('name')},
                dataType: 'html',
                success: function (data) {
                    $('.small-dialog').html(data);
                }
            });
            
        });

    });
    function inArray(needle, haystack) {
        var length = haystack.length;
        for(var i = 0; i < length; i++) {
            if(haystack[i] == needle) return true;
        }
        return false;
    }
</script>

<?php $this->load->view('dashboard/footer'); ?>