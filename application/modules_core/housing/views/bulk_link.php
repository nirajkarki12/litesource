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
    #inventoryGrid .slick-headerrow-columns .c2 input,#myGrid .slick-headerrow-column.c0 input{
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
        <?=$this->lang->line('bulk_link_housings');?>
    </h3>

    <div class="content toggle no_padding" style="min-height: 600px;">
        <?php $this->load->view('dashboard/system_messages'); ?>
        <div class="filterarea">
            <input type="button" name="applyinventorytoprod" id="applyinventorytoprod" value="Apply" />
            <input type="button" name="unlinkhousing" id="unlinkhousing" value="Unlink Housing" />
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
        $("#unlinkhousing").on('click', function () {
            $(this).attr('disabled', 'disabled');

            let prodlistdetail = [];
            let selectedRows = [];
            
            for(let m = 0; m<selectedPds.length; m++){
                if(!inArray(selectedPds[m],selectedRows)){
                    selectedRows.push(selectedPds[m]);
                }
            }
            
            for (let k = 0; k < selectedRows.length; k++) {
                let data = dataView2.getItemById(selectedRows[k]);
                if (typeof data != 'undefined') {
                    prodlistdetail.push(data.id);
                }
            }
            
            let invlist = [];
            for(let m = 0; m<selectedInvs.length; m++){
                if(!inArray(selectedInvs[m],invlist)){
                    invlist.push(selectedInvs[m]);
                }
            }
            
            var invlistdetail = [];
            for (let i = 0; i < invlist.length; i++) {
                let d = dataView.getItemById(invlist[i]);
                invlistdetail.push(d['id']);
            }
            if (prodlistdetail.length == 0) {
                alert('Please select Inventory to unlink.');
                $(this).removeAttr('disabled');
            } else {
                if (confirm('Are you sure?')) {
                    $('.inventoryGridwrap .loader').addClass('loading');
                    $('.mygridwrap .loader').addClass('loading');
                    $.ajax({
                        type: 'post',
                        url: "<?php echo site_url('housing/unlinkhousing'); ?>",
                        dataType: 'html',
                        data: {link_products: invlistdetail,product_ids: prodlistdetail },
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
        
        
        $("#applyinventorytoprod").on('click', function () {
            $(this).attr('disabled', 'disabled');

            let prodlistdetail = [];
            let selectedRows = [];
            
            for(let m = 0; m<selectedPds.length; m++){
                if(!inArray(selectedPds[m],selectedRows)){
                    selectedRows.push(selectedPds[m]);
                }
            }
            
            for (let k = 0; k < selectedRows.length; k++) {
                var data = dataView2.getItemById(selectedRows[k]);
                if (typeof data != 'undefined') {
                    prodlistdetail.push(data.id);
                }
            }
            
            var invlist = [];
            for(let m = 0; m<selectedInvs.length; m++){
                if(!inArray(selectedInvs[m],invlist)){
                    invlist.push(selectedInvs[m]);
                }
            }
            
            var invlistdetail = [];
            for (let i = 0; i < invlist.length; i++) {
                var d = dataView.getItemById(invlist[i]);
                invlistdetail.push(d['id']);
            }
            if (invlistdetail.length == 0) {
                alert('Please select Housing Inventory.');
                $(this).removeAttr('disabled');
            } else if (prodlistdetail.length == 0) {
                alert('Please select Inventory to link.');
                $(this).removeAttr('disabled');
            } else {
                if (confirm('Are you sure?')) {
                    $('.inventoryGridwrap .loader').addClass('loading');
                    $('.mygridwrap .loader').addClass('loading');
                    $.ajax({
                        type: 'post',
                        url: "<?php echo site_url('housing/updateprodrelation'); ?>",
                        dataType: 'html',
                        data: {link_products: invlistdetail,product_ids: prodlistdetail },
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
    
    $(document).ready(function () {
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