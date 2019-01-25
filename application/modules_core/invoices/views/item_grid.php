<style>




    #contact { 
        -webkit-user-select: none; /* Chrome/Safari */        
        -moz-user-select: none; /* Firefox */
        -ms-user-select: none; /* IE10+ */
        margin: 4em auto;
        width: 100px; 
        height: 30px; 
        line-height: 30px;
        background: teal;
        color: white;
        font-weight: 700;
        text-align: center;
        cursor: pointer;
        border: 1px solid white;
    }



    #invPopForm_loader { 
        display: none;
        border: 1px solid black; 
        padding: 1em;
        width: 45%;
        text-align: center;
        background: #fff;
        position: absolute;
        top:50%;
        left:50%;
        transform: translate(-50%,-50%);
        -webkit-transform: translate(-50%,-50%);
        z-index: 2;
    }

    #contact:hover { background: #666; }
    #contact:active { background: #444; }

    #invPopForm { 
        display: none;
        border: 15px solid white; 
        padding: 1em;
        width: 65%;
        /*width: 912px;*/
        /*text-align: center;*/
        max-height: 610px; 
        background: rgb(240, 240, 240);
        position: fixed;
        /*top:-8%;*/
        top:60px;
        left:18%;
        outline: 0px;
        /*  transform: translate(-50%,-50%);
          -webkit-transform: translate(-50%,-50%);*/
        z-index: 1;
        overflow: scroll;
    }

    #invPopForm form dl dt{
        margin: 0%;
    }

    #invPopForm form dl dt label{
        float: left;
    }
    #invPopForm form dl dd{
        margin: 0%;
        display: table;
    }

    .formBtn { 
        width: 140px;
        display: inline-block;

        background: teal;
        color: #fff;
        font-weight: 100;
        font-size: 1.2em;
        border: none;
        height: 30px;
    }









    body, html {
        margin: 0;
        padding: 0;
        /*		overflow:hidden;*/
    }

    .silk-red-color {
        color: red;
    }
    .silk-orange-color {
        color: orange;
    }
    
    .light-blue-color{
        color: dodgerblue;
    }

    .slick-columnpicker{
        display: none !important;
    }

    .slick-cell.cell-right-align {
        text-align: right;
    }

    .options-panel {
        -moz-border-radius: 3px;
        -webkit-border-radius: 3px;
        border: 1px solid silver;
        background: #f0f0f0;
        padding: 4px;
        smargin-bottom: 20px;
        swidth:320px;
        sposition:absolute;
        float: right;
        stop:500px;
        left:0px;
        font-size: 12pt;
        font-weight: bold;
    }

    .recycle-bin {
        width: 120px;
        border: 1px solid gray;
        background: beige;
        padding: 4px;
        font-size: 12pt;
        font-weight: bold;
        color: black;
        text-align: center;
        -moz-border-radius: 10px;
    }

    .mygridwrap{
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
    .content {
        background: #fff;
        padding: 20px;
        border: 1px solid #dadada;
        margin-bottom: 20px;
    }



</style>

<script type="text/javascript" src="<?php echo base_url(); ?>assets/jquery/jquery.event.drag-2.0.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/jquery/jquery.event.drop-2.0.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/slick/slick.core.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/slick/plugins/slick.autotooltips.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/slick/plugins/slick.checkboxselectcolumn.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/slick/plugins/slick.rowselectionmodel.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/slick/plugins/slick.rowmovemanager.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/slick/slick.editors.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/slick/slick.grid.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/slick/slick.dataview.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/slick/json2.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/jquery/jquery.magnific-popup.min.js"></script>

<script type="text/javascript" src="<?php echo base_url(); ?>assets/slick/plugins/slick.cellrangeselector.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/slick/plugins/slick.cellcopymanager.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/slick/plugins/slick.cellselectionmodel.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/slick/controls/slick.columnpicker.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/slick/slick.formatters.js"></script>

<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/style/css/magnific-popup.css" />
<script type="text/javascript" src="<?php echo base_url(); ?>assets/jquery/jquery.cookie.js"></script>
<style>
    .slick-cell-checkboxsel {
        background: #f0f0f0;
        border-right-color: silver;
        border-right-style: solid;
    }
</style>
<div id="infoMessage"><?php echo $this->session->flashdata('message'); ?></div>
<button class="btn btn-primary" id="deletelineitemselected" style="margin-bottom: 20px;">Delete Selected Items</button>

<?php if ($_SERVER['HTTP_HOST'] == 'invoice.litesource.io') { ?>
    <button class="btn btn-primary popup" id="addInventory" style="margin-bottom: 20px;" data-href="/index.php/inventory/form/?clean=1"   data-title="Add Inventory" data-width="880" data-height="600" >Add Inventory</button>
<?php } else { ?>
    <button class="btn btn-primary popup" id="addInventory" style="margin-bottom: 20px;" data-href="/invoice/index.php/inventory/form/?clean=1"   data-title="Add Inventory" data-width="880" data-height="600" >Add Inventory</button>
<?php } ?>

<div class="mygridwrap">
    <div id="itemGrid" style="width:1080px;height:500px;"></div>
    <div class="loader">
        <img src="<?php echo base_url() . 'assets/style/img/loading.gif'; ?>" />
    </div>
</div>
<!--<div id="dropzone" class="recycle-bin">Recycle Bin</div>-->

<div class="options-panel">
    <p>
        <?php echo $this->lang->line('subtotal'); ?>: <span id="invoice_item_subtotal"></span> |
        <?php echo $this->lang->line('tax'); ?>: <span id="invoice_item_tax"></span> |
        <?php echo $this->lang->line('total'); ?>: <span id="invoice_total"></span>
    </p>
</div>

























<!--<div id="contact">Contact</div>-->
<div id="invPopForm" class="ui-dialog-content ui-widget-content">
    <div id="invPopForm_loader">Please Wait...</div>
    <div style="width:80%; margin:0 auto;">

        <div  style="width: 128%;padding: 1%;background: #000;margin-left: -15%;">
            <strong style="color: white">Add Inventory</strong>
            <div onclick="i_popup_cancel()" style="color: white; background: #e1e1e1; cursor: pointer; float: right; padding-left: 4px;padding-right: 4px;padding-top: 2px;padding-bottom: 2px;">
                X
            </div>
        </div>
        <br>

        <!--<form action="#" style="padding-top: 2%;">-->
        <div class="content">
            <h3 class="title_black">Inventory Form</h3>
            <br><br>
            <input type="hidden" id="i_invoice_item_id" name="invoice_item_id">
            <dl>
                <dt><label><?php echo $this->lang->line('supplier'); ?>: </label></dt>
                <dd>
                    <select name="supplier_id" id="i_supplier_id">
                        <?php
                        foreach ($clients as $supplier) {
                            if ($supplier->client_is_supplier == '1') {
                                ?>
                                <option value="<?= $supplier->client_id; ?>"><?= $supplier->client_name . ($supplier->supplier_name != $supplier->client_name ? ' (' . $supplier->supplier_name . ')' : ''); ?> </option>
    <?php }
} ?>
                    </select>
                </dd>
            </dl>

            <dl>
                <dt><label><?php echo $this->lang->line('inventory_name'); ?>: </label></dt>
                <dd><input type="text" name="name" id="i_name"/></dd>
            </dl>

            <dl id="supplier_cat_row">
                <dt><label><?php echo $this->lang->line('supplier_catalog_number'); ?>: </label></dt>
                <dd><input type="text" name="supplier_code" id="i_supplier_code" value="" /></dd>
            </dl>
            <dl id="supplier_price_row">
                <dt id="price_label"><label><?php echo $this->lang->line('product_supplier_price'); ?>: </label></dt>
                <dd><input type="text" name="supplier_price" id="i_supplier_price" value="" /></dd>
            </dl>
            <dl>
                <dt><label><?php echo $this->lang->line('inventory_base_price'); ?>: </label></dt>
                <dd><input type="text" name="base_price" id="i_price" value="" /></dd>
            </dl>
            <dl>
                <dt><label><?php echo $this->lang->line('inventory_location'); ?>: </label></dt>
                <dd><input type="text" name="location" id="i_location" value="" /></dd>
            </dl>
            <dl>
                <dt><label><?php echo $this->lang->line('inventory_description'); ?>: </label></dt>
                <dd><textarea class="big_textarea" name="description" id="i_description"></textarea></dd>
            </dl>
            <dl id="supplier_description_row">
                <dt><label><?php echo $this->lang->line('inventory_supplier_decsription'); ?>: </label></dt>
                <dd><textarea class="big_textarea" name="supplier_description" id="i_supplier_description"></textarea></dd>
            </dl>
            <dl>
                <dt><label>Dynamic: </label></dt>
                <dd><input type="checkbox" name="use_length" id="i_use_length" value="1"/></dd>
            </dl>
            <br>
        </div>
        <div class="content">
            <div>
                <h3 class="title_black"><?php echo $this->lang->line('inventory_history'); ?></h3>
                <div class="content toggle">

                    <dl>
                        <dt><label><?php echo $this->lang->line('inventory_history_qty'); ?>: </label></dt>
                        <dd><input type="number" name="history_qty" id="i_history_qty" value="" /></dd>
                    </dl>
                    <!--<div id="qtyErrorMsg" style="text-align: center;margin-top: -5px;font-size: 14px;color: red;margin-bottom: 15px;"></div>-->
                    <dl>
                        <dt><label><?php echo $this->lang->line('inventory_history_notes'); ?>: </label></dt>
                        <dd><textarea style="width: 63%;" class="big_textarea" name="notes" id="i_notes" value=""></textarea></dd>
                    </dl>
                </div>
            </div>

            <div onclick="i_popup_submit()" style="padding: 1%; background: #e1e1e1; margin-left: 91%; cursor: pointer">
                submit
            </div>
        </div>
        <!--</form>-->
    </div>
</div>





<script type="text/javascript">

<?php
global $CI;
echo "var currencySymbol = '" . $CI->mdl_mcb_data->setting('currency_symbol') . "';";
?>



    function i_popup_submit() {

        document.getElementById("invPopForm_loader").style.display = "block";

        var name = document.getElementById("i_name").value;
        var description = document.getElementById("i_description").value;
        var base_price = document.getElementById("i_price").value;

        var supplier_id = document.getElementById("i_supplier_id").value;
        var supplier_code = document.getElementById("i_supplier_code").value;
        var supplier_price = document.getElementById("i_supplier_price").value;
        var location = document.getElementById("i_location").value;

        var supplier_description = document.getElementById("i_supplier_description").value;
        var use_length = document.getElementById("i_use_length").value;
        var history_qty = document.getElementById("i_history_qty").value;
        var notes = document.getElementById("i_notes").value;

        var invoice_item_id = document.getElementById("i_invoice_item_id").value;

        var p_data = {name: name, description: description, base_price: base_price, supplier_id: supplier_id, supplier_code: supplier_code, supplier_price: supplier_price, supplier_description: supplier_description, use_length: use_length, history_qty: history_qty, notes: notes, invoice_item_id: invoice_item_id, location: location};

        $("#invPopForm").fadeOut();

        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo site_url('inventory/ajax_add'); ?>',
            data: p_data,
            success: function (data) {
                if (data.status == true) {
                    window.location.reload();
                } else {
                    alert(data.msg);
                    document.getElementById("invPopForm_loader").style.display = "none";
                }

            }
        });

    }

    function i_popup_cancel() {
        $("#invPopForm").fadeOut();
    }







<?php if ($_SERVER['HTTP_HOST'] == 'invoice.litesource.io') { ?>
        var open_inventory_form = '<button class="btn btn-primary popup" id="addInventory" style="margin-bottom: 20px;" data-href="/index.php/inventory/form/?clean=1"   data-title="Add Inventory" data-width="880" data-height="600" >Add Inventory</button>';
<?php } else { ?>
        var open_inventory_form = '<button class="btn btn-primary popup" id="addInventory" style="margin-bottom: 20px;" data-href="/invoice/index.php/inventory/form/?clean=1"   data-title="Add Inventory" data-width="880" data-height="600" >Add Inventory</button>';
<?php } ?>

    PriceCellFormatter = function (row, cell, value, columnDef, dataContext) {
        if (value == '0.00') {
            return '';
        } else if (value == null) {
            return '';
        } else {
            if (value < 0) {
                value = value * (-1);
                return '-' + currencySymbol + value;
            }
            return currencySymbol + value;
        }
    };

    PerMeterPriceCellFormatter = function (row, cell, value, columnDef, dataContext) {
        if (value == '0.00') {
            return '';
        } else {
            return currencySymbol + value;
        }
    };

    QuantityCellFormatter = function (row, cell, value, columnDef, dataContext) {

//        console.log(dataContext);
        if ((dataContext['item_name'] != '') && (value == '0.00')) {
            return '0.00';
        } else if ((value == '') || (value == '0.00')) {
            return '';
        } else {
            return parseFloat(value);
        }
    };

    function add_inv_item_popup(i_name, i_description, i_price, i_invoice_item_id, i_qty) {


//        $('#contact').click(function() {
        $('#invPopForm').fadeToggle();
//        });

        //var i_description = JSON.parse(i_description);

        //----- for name----
        var w1 = i_name.split("-<span>");
        var w2 = i_name.split("</span>mm");
        if ((w1[1] != null) && (w2[0] != null)) {
            var i_name = (w1[0].concat('{mm}')).concat(w2[1]);
        } else {
            var i_name = i_name;
        }

        //----- for description----
        var w12 = i_description.split("-<span>");
        var w22 = i_description.split("</span>mm");
        
        if ((w12[1] != null) && (w22[0] != null)) {
            var i_description = (w12[0].concat('{mm}')).concat(w22[1]);
        } else {
            var i_description = i_description;
        }

        var i_name = (i_name.replace(/<\/span>/g, "")).replace(/-<span>/g, "");

        document.getElementById("i_invoice_item_id").value = i_invoice_item_id;
        document.getElementById("i_name").value = i_name;
        document.getElementById("i_description").value = i_description.replace(/<span>/gi, "");
        document.getElementById("i_price").value = i_price;

        var container = $("#invPopForm");

//            if (!container.is(e.target) // if the target of the click isn't the container...
//                && container.has(e.target).length === 0) // ... nor a descendant of the container
//            {
//                container.fadeOut();
//            }

    }

//    function add_inv_item_popup(i_name, i_description, i_price, i_invoice_item_id, i_qty){
//        
//        
//        console.log(i_name);
//        
//        console.log(i_description);
//        
//        
//        
//        
//        console.log(i_description);
//    }

    ItemNameCellFormatter = function (row, cell, value, columnDef, dataContext) {
        // console.log(dataContext);
        //console.log(value);
        var supp_id = (dataContext['supplier_id']);
		var inv_supp = (dataContext['inv_supp']);
        if ((dataContext['is_removed'] == 1) && (dataContext['item_qty'] != '0.00')) {
            var i_invoice_item_id = dataContext.invoice_item_id;
            //var i_name =   "'"+dataContext.item_name+"'";

            var i_name = '"' + dataContext.item_name + '"';
//            var i_name = dataContext.item_name;

            var i_description = '' + dataContext.item_description + '';
            //var i_description = '"'+dataContext.item_description+'"';

            var i_price = dataContext.item_price;
            var i_qty = dataContext.item_qty;

            //i_name = JSON.stringify(i_name);
            i_description = JSON.stringify(i_description);


            //console.log(i_description);

            // return "<div class ='silk-red-color' onclick='add_inv_item_popup("+i_name+", "+i_description+", "+i_price+", "+i_invoice_item_id+", "+i_qty+")'>" + value + "</div>";
            if (dataContext['is_archived'] == 1) {
                return "<div class ='silk-orange-color'>" + value + "</div>";

            }  else {
                return "<div class ='silk-red-color'>" + value + "</div>";
            }
       // } else if( (inv_supp && (inv_supp == 0 ||inv_supp == null)  && ((dataContext['item_qty'] != '0.00') || parseInt(dataContext['item_qty']) != '0') )){
                //alert('none');
               // return "<div class ='light-blue-color'>" + value + "</div>";
       // } else if (!supp_id && (dataContext['item_qty'] != '0.00' || parseInt(dataContext['item_qty']) != '0')) {
			 // return "<div class ='light-blue-color'>" + value + "</div>";
			}else{
            return value;
        }
    };

    IntegerCellFormatter = function (row, cell, value, columnDef, dataContext) {
        return parseInt(value);
    };

    ItemLengthCellEditor = function (args) {
//        console.log(args);        
        var $input;
        var defaultValue;
        var scope = this;
        this.init = function () {

            if ((args['item']['product_dynamic'] != '1') && args['item']['is_removed'] != '1') {
                alert('Product is not dynamic. Please select a dynamic product first.');
                $input = $("<INPUT type='number' disabled class='editor-text' />");
            } else {
                $input = $("<INPUT type=number class='editor-text' />");
            }

            $input.bind("keydown.nav", function (e) {
                if (e.keyCode === $.ui.keyCode.LEFT || e.keyCode === $.ui.keyCode.RIGHT) {
                    e.stopImmediatePropagation();
                }
            });
            $input.appendTo(args.container);
            $input.focus().select();
        };
        this.destroy = function () {
            $input.remove();
        };
        this.focus = function () {
            $input.focus();
        };
        this.loadValue = function (item) {
            defaultValue = item[args.column.field];
            $input.val(defaultValue);
            $input[0].defaultValue = defaultValue;
            $input.select();
        };
        this.serializeValue = function () {
            return parseFloat($input.val()) || 0;
        };
        this.applyValue = function (item, state) {
            item[args.column.field] = state;
        };
        this.isValueChanged = function () {
            return (!($input.val() == "" && defaultValue == null)) && ($input.val() != defaultValue);
        };
        this.validate = function () {
            if (isNaN($input.val()))
                return {
                    valid: false,
                    msg: "Please enter a valid number"
                };
            return {
                valid: true,
                msg: null
            };
        };
        this.init();
    };

    QuantityCellEditor = function (args) {
        // console.log(args['item']);    
        var $input;
        var defaultValue;
        var scope = this;
        this.init = function () {
//            $input = $("<INPUT type=number class='editor-text' />");
//            $input.bind("keydown.nav", function (e) {
//                if (e.keyCode === $.ui.keyCode.LEFT || e.keyCode === $.ui.keyCode.RIGHT) {
//                    e.stopImmediatePropagation();
//                }
//            });
//            $input.appendTo(args.container);
//            $input.focus().select();
            $input = $("<INPUT type=number class='editor-text' />");
            $input.bind("keypress.nav", function (e) {
                let key = Number(e.key);
                var enteredValue = e.key;
                if ((isNaN(key) != true) || (enteredValue == ".") || (enteredValue == "-") || (e.which < 32) || (e.which > 126)) {
                } else {
                    return false;
                }
            });
            $input.appendTo(args.container);
            $input.focus().select();
        };
        this.destroy = function () {
            $input.remove();
        };
        this.focus = function () {
            $input.focus();
        };
        this.loadValue = function (item) {
            defaultValue = item[args.column.field];
            $input.val(defaultValue);
            $input[0].defaultValue = defaultValue;
            $input.select();
        };
        this.serializeValue = function () {
            if ($input.val() == '') {
                return '';
            } else {
                return parseFloat($input.val()) || 0;
            }
        };
        this.applyValue = function (item, state) {
            item[args.column.field] = state;
        };
        this.isValueChanged = function () {
            return (!($input.val() == "" && defaultValue == null)) && ($input.val() != defaultValue);
        };
        this.validate = function () {
            if (isNaN($input.val()))
                return {
                    valid: false,
                    msg: "Please enter a valid number"
                };
            return {
                valid: true,
                msg: null
            };
        };
        this.init();
    };
    ProductCellEditor = function (args) {
        var $input;
        var $desc, $price;
        var defaultValue;
        var scope = this;
        this.init = function () {
            $input = $("<INPUT type=text id='product' class='editor-text' />")
                    .appendTo(args.container)
                    .bind("keydown.nav", function (e) {
                        if (e.keyCode === $.ui.keyCode.LEFT || e.keyCode === $.ui.keyCode.RIGHT) {
                            e.stopImmediatePropagation();
                        }
                    })
                    .focus()
                    .select()
                    .autocomplete({
                        minLength: 3,
                        source: function (req, resp) {
                            $.ajax({
                                url: "<?php echo site_url('products/jquery_search_autocomplete'); ?>",
                                dataType: 'json',
                                type: 'POST',
                                data: req,
                                success: function (data) {
                                    if (data == 'session_expired') {
                                        window.location.reload();
                                    }
                                    resp(data.search_results);
                                }
                            })
                        },
                        focus: function (event, ui) {
                            $("#product").val(ui.item.label);
                            return false;
                        }
//                        ,
//                         select: function( event, ui ) {
//                         $( "#product_base_price" ).val( ui.item.base_price );
//                         $( "#product_description" ).val( ui.item.description );
//                         
//                         return false;
//                         }
                    })
                    .bind("keydown.nav", function (e) {
                        if (e.keyCode === $.ui.keyCode.DOWN || e.keyCode === $.ui.keyCode.UP) {
                            e.stopImmediatePropagation();
                        }
                    });


            $input.data("autocomplete")._renderItem = function (ul, item) {
                return $("<li></li>")
                        .data("item.autocomplete", item)
                        .append("<a>" + item.value + "<br>" + item.description + "</a>")
                        .appendTo(ul);

            };
        };

        this.destroy = function () {
            $input.remove();
        };

        this.focus = function () {
            $input.focus();
        };

        this.getValue = function () {
            return $input.val();
        };

        this.setValue = function (val) {
            $input.val(val);
        };

        this.loadValue = function (item) {
            defaultValue = item[args.column.field] || "";
            $input.val(defaultValue);
            $input[0].defaultValue = defaultValue;
            $input.select();
        };

        this.serializeValue = function () {
            return $input.val();
        };

        this.applyValue = function (item, state) {
            item[args.column.field] = state;
            // nasty hard code here to fix someway
            //item.item_description = $desc.val();
            //item.item_price = $price.val();
        };

        this.isValueChanged = function () {
            return (!($input.val() == "" && defaultValue == null)) && ($input.val() != defaultValue);
        };

        this.validate = function () {

//            console.log(args.item);
//            return false;

            if (args.column.validator) {
                var validationResults = args.column.validator($input.val());
                if (!validationResults.valid)
                    return validationResults;
            }

            return {
                valid: true,
                msg: null
            };
        };

        this.init();
    };

    var grid;
    var dataView;
    var selectedRowIds = [];
    var sortCol = "item_type";
    var sortDir = 1;
    var sortNumeric = false;
    var SUBTOTAL_DESC = "Subtotal:";
    var SUBTOTAL_QTY = -1;
    var itemsToDelete = [];
    var rowIndexToDelete = []


    var columns = [
        /*
         {id:"invoice_item_id",
         behavior: "move",
         //selectable: false,
         resizable: false,
         width:50,
         name:"< ?php echo $this->lang->line('id'); ?>",
         field:"invoice_item_id"},
         */
//        {id: "item_sn", width: 50, name: " ", field: "item_sn", cssClass: "checkitem", formatter: checkboxinput},

        {id: "item_qty",
            width: parseFloat((typeof $.cookie('item_grid_item_qty') != 'undefined') ? $.cookie('item_grid_item_qty') : '40'),
            name: "<?php echo $this->lang->line('qty'); ?>", field: "item_qty", cssClass: "cell-right-align", editor: QuantityCellEditor, formatter: QuantityCellFormatter},
        {id: "item_name",
            width: parseFloat((typeof $.cookie('item_grid_item_name') != 'undefined') ? $.cookie('item_grid_item_name') : '200'),
            behavior: "move", minWidth: 50,
            name: "<?php echo $this->lang->line('catalog_number'); ?>", field: "item_name", editor: ProductCellEditor, validator: requiredFieldValidator, formatter: ItemNameCellFormatter},
        {id: "item_type",
            width: parseFloat((typeof $.cookie('item_grid_item_type') != 'undefined') ? $.cookie('item_grid_item_type') : '100'),
            name: "<?php echo $this->lang->line('item_type'); ?>", field: "item_type", sortable: true, editor: TextCellEditor},
        {id: "item_description",
            width: parseFloat((typeof $.cookie('item_grid_item_description') != 'undefined') ? $.cookie('item_grid_item_description') : '285'),
            minWidth: 50, name: "<?php echo $this->lang->line('item_description'); ?>", field: "item_description", editor: LongTextCellEditor},
        {id: "item_length",
            width: parseFloat((typeof $.cookie('item_grid_item_length') != 'undefined') ? $.cookie('item_grid_item_length') : '90'),
            name: "Length (MT)", field: "item_length", cssClass: "cell-right-align", editor: ItemLengthCellEditor},
        {id: "item_per_meter",
            width: parseFloat((typeof $.cookie('item_grid_item_per_meter') != 'undefined') ? $.cookie('item_grid_item_per_meter') : '70'),
            name: "Per Metre", field: "item_per_meter", cssClass: "cell-right-align", editor: ItemLengthCellEditor, formatter: PerMeterPriceCellFormatter},
        {id: "item_price",
            width: parseFloat((typeof $.cookie('item_grid_item_per_meter') != 'undefined') ? $.cookie('item_grid_item_per_meter') : '80'),
            name: "<?php echo $this->lang->line('unit_price'); ?>", field: "item_price", cssClass: "cell-right-align", editor: TextCellEditor, formatter: PriceCellFormatter},
        {id: "item_subtotal",
            width: parseFloat((typeof $.cookie('item_grid_item_subtotal') != 'undefined') ? $.cookie('item_grid_item_subtotal') : '90'),
            name: "<?php echo $this->lang->line('item_subtotal'); ?>", field: "item_subtotal", cssClass: "cell-right-align", formatter: PriceCellFormatter},
        {id: "stock_status",
            width: parseFloat((typeof $.cookie('item_grid_stock_status') != 'undefined') ? $.cookie('item_grid_stock_status') : '50'),
            name: "<?php echo $this->lang->line('inventory'); ?>", field: "stock_status", cssClass: "stock-status"},
        {id: "item_action",
            width: parseFloat((typeof $.cookie('item_grid_item_action') != 'undefined') ? $.cookie('item_grid_item_action') : '45'),
            name: "", field: "item_action", cssClass: "cell-center-align", formatter: addRowIcon}
    ];

    var options = {
        autoEdit: false,
        editable: true,
        enableCellNavigation: true,
        enableColumnReorder: false,
        asyncEditorLoading: false,
        enableAddRow: true,
        enableRowReordering: true

    };
    var checkboxSelector = new Slick.CheckboxSelectColumn({
        cssClass: "slick-cell-checkboxsel deleteline"
    });


    columns.unshift(checkboxSelector.getColumnDefinition());

    function addRowIcon() {
        return '<span class="addRowBelow"><img src="<?php echo base_url(); ?>assets/style/img/rowadd.png"/></span>';
    }


    function getProductData(item) {
        $.post("<?php echo site_url('products/jquery_product_data'); ?>", {
            invoice_item_id: item.invoice_item_id,
            product_name: item.item_name

        }, function (data) {
            if (data == 'session_expired') {
                window.location.reload();
            }
            invoice_item_id = data.invoice_item_id;
            var new_item = dataView.getItemById(invoice_item_id);
            new_item.product_id = product.product_id;
            new_item.item_name = product.product_name;
            new_item.item_description = product.product_description;
            new_item.item_price = product.product_base_price;
            dataView.updateItem(item.invoice_item_id, new_item);
        });
        return false;
    }
    ;

    /*
     *   Special processing if item quantity = -1 since this will generate a subtotal
     *   so update description with default "Subtotal:"
     */
    function checkItemSpecialValues(item) {


//        console.log(item);

        if ((item.item_qty == SUBTOTAL_QTY) && (item.item_description == '')) {
            item.item_description = SUBTOTAL_DESC
        }

        return item;
    }

    // request new item be added to the invoice
    // using initial values already entered
    function addNewInvoiceItem(item) {

        // console.log(item);
//        item = checkItemSpecialValues(item);

        $.post("<?php echo site_url('invoices/addNewInvoiceItem'); ?>", {
            invoice_id: <?php echo uri_assoc('invoice_id'); ?>,
            item: JSON.stringify(item)

        }, function (data) {
            if (data == 'session_expired') {
                window.location.reload();
            }
            //var new_item = $();
            //$.extend(new_item,data.item);

            if (data == "Not enough") {
                window.location.reload();
            }

            var elements = document.getElementsByClassName("error");
            for (var i = 0; i < elements.length; i++) {
                elements[i].parentNode.removeChild(elements[i]);
            }

//            console.log(data);
            var new_item = data.item;
            //console.log(new_item);
            if (new_item != false) {
                dataView.addItem(new_item);
            } else {
                alert("Something went wrong..");
            }


            display_invoice_amounts(data.invoice_amounts);
            grid.editActiveCell();

        }, "json");
    }
    ;


    function updateInvoiceItem(item, field) {
        //console.log(item);

        /*
         *	TODO - only send the field being updated
         **/
        //console.log(item);
        var post_data = checkItemSpecialValues(item);

//        if( (post_data['product_dynamic']) != '1' ){
//            alert('Product is not dynamic. Please select a dynamic product first.');
//            window.location.reload();
//            e.preventDefault();
//            
//        }

        $.post("<?php echo site_url('invoices/updateInvoiceItem'); ?>", {
            invoice_id: <?php echo uri_assoc('invoice_id'); ?>,
            item: JSON.stringify(post_data)

        }, function (data) {
            if (data == 'session_expired') {
                window.location.reload();
            }
            //console.log(data);
            /*
             var invoice_item_amounts = data.invoice_item_amounts;
             var item = dataView.getItemById(invoice_item_amounts.invoice_item_id);
             item.item_subtotal = invoice_item_amounts.item_subtotal;
             item.item_tax = invoice_item_amounts.item_tax;
             item.item_total = invoice_item_amounts.item_total;
             */
            if (data == "Not enough") {
                window.location.reload();
            }
            var elements = document.getElementsByClassName("error");
            for (var i = 0; i < elements.length; i++) {
                elements[i].parentNode.removeChild(elements[i]);
            }
            // console.log(data.item);
            if (data != null) {
                var item = dataView.getItemById(data.item.l_data.invoice_item_id);
                dataView.updateItem(item.invoice_item_id, data.item.l_data);
                display_invoice_amounts(data.invoice_amounts);
                if (data.item.status == false) {
                    alert('Something went wrong..');
                }
            }
        }, "json");
    }
    ;

    function updateInvoiceItemSortOrder() {

        var sortOrder = [];
        var idProperty = dataView.getIdProperty();
        var data = dataView.getItems();

        for (var i = 0, l = data.length; i < l; ++i) {
            sortOrder[i] = dataView.getItemByIdx(i)[idProperty];
        }

        // send new sort order to server for row updates
        $.post("<?php echo site_url('invoices/setItemsSortOrder'); ?>", {
            invoice_id: <?php echo uri_assoc('invoice_id'); ?>,
            sort_order: sortOrder.toString()

        }, function (data) {
            if (data == 'session_expired') {
                window.location.reload();
            }

        }, "json");

    }

    function display_invoice_amounts(invoice_amounts) {
        $('#invoice_item_subtotal').html(invoice_amounts.invoice_item_subtotal);
        $('#invoice_item_tax').html(invoice_amounts.invoice_item_tax);
        $('#invoice_total').html(invoice_amounts.invoice_total);
    }


    function deleteInvoiceItem(allItems) {
        var invoice_item_ids = '';
        for (var i = 0; i < allItems.length; i++) {
            var item = allItems[i];
            invoice_item_ids += item.invoice_item_id + ',';
        }
        $.post("<?php echo site_url('invoices/deleteInvoiceItemAll'); ?>", {
            invoice_id: <?php echo uri_assoc('invoice_id'); ?>,
            invoice_item_id: invoice_item_ids

        }, function (data) {
            if (data == 'session_expired') {
                window.location.reload();
            }
            $('.mygridwrap .loader').removeClass('loading');
            display_invoice_amounts(data.invoice_amounts);

            dataView.endUpdate();
            grid.invalidate();
            grid.setSelectedRows([]);
        }, "json");
    }
    ;

    function requiredFieldValidator(value) {
        if (value == null || value == undefined || !value.length)
            return {valid: false, msg: "This is a required field"};
        else
            return {valid: true, msg: null};
    }

    function comparer(a, b) {
        var x = a[sortCol], y = b[sortCol];

        if (sortNumeric)
            return (x - y)
        else
            return (x == y ? 0 : (x > y ? 1 : -1));
    }

    $(document).ready(function () {

        if (grid === undefined) {

            dataView = new Slick.Data.DataView();

            grid = new Slick.Grid($("#itemGrid"), dataView, columns, options);
//            grid.registerPlugin(new Slick.AutoTooltips());
            grid.setSelectionModel(new Slick.RowSelectionModel({selectActiveRow: false}));
            grid.registerPlugin(checkboxSelector);

            var columnpicker = new Slick.Controls.ColumnPicker(columns, grid, options);

            var moveRowsPlugin = new Slick.RowMoveManager();


            moveRowsPlugin.onMoveRows.subscribe(function (e, args) {
                var extractedRows = [], left, right;
                var rows = args.rows;
                var insertBefore = args.insertBefore;
                var data = dataView.getItems();

                left = data.slice(0, insertBefore);
                right = data.slice(insertBefore, data.length);


                for (var i = 0; i < rows.length; i++) {
                    var item = data[rows[i]];
                    //console.log('row[' + i + '] = ' + item.invoice_item_id);
                    extractedRows.push(item);
                }

                // sort in reverse so that as items are removed
                // from the left or right partitions correctly
                rows.sort(function (a, b) {
                    return b - a
                });

                // now extract the selected rows from
                // either the left or right partition
                for (var i = 0; i < rows.length; i++) {
                    var row = rows[i];

                    if (row < insertBefore)
                        left.splice(row, 1)
                    else
                        right.splice(row - insertBefore, 1);

                }

                // finally insert the selected rows at the insertion point
                data = left.concat(extractedRows.concat(right));

                // make sure the rows we just moved remain selected
                var selectedRows = [];
                for (var i = 0; i < rows.length; i++)
                    selectedRows.push(left.length + i);

                dataView.beginUpdate();
                dataView.setItems(data, 'invoice_item_id');
                grid.resetActiveCell();
                grid.setSelectedRows(selectedRows);
                dataView.endUpdate();


                updateInvoiceItemSortOrder();


            });

            grid.registerPlugin(moveRowsPlugin);

            grid.onActiveCellChanged.subscribe(function (e, args) {

            });


            grid.onColumnsResized.subscribe(function (e, args) {
                for (var i = 0, totI = grid.getColumns().length; i < totI; i++) {
                    var column = grid.getColumns()[i];
                    var grid_col_width = column.width;
                    var cookie = $.cookie('item_grid_' + column.field, grid_col_width);
                }
            });

            grid.onSort.subscribe(function (e, args) {
                sortDir = args.sortAsc ? 1 : -1;
                sortCol = args.sortCol.field;
                sortNumeric = args.sortCol.sortNumeric;
                dataView.sort(comparer, args.sortAsc);
                updateInvoiceItemSortOrder();

            });

            grid.onDragInit.subscribe(function (e, dd) {
                // prevent the grid from cancelling drag'n'drop by default
                e.stopImmediatePropagation();
            });

            // wire up model events to drive the grid
            grid.onCellChange.subscribe(function (e, args) {
                //console.log(args);
                //dataView.updateItem(args.item[dataView.getIdProperty()], args.item);
                var column = grid.getColumns()[args.cell];

                updateInvoiceItem(args.item, column.field);
            });

            grid.onClick.subscribe(function (e, args) {

            });

            grid.onKeyDown.subscribe(function (e, args) {
                var handled = e.isImmediatePropagationStopped();

                if (!handled) {
                    if (e.shiftKey && !e.altKey && !e.ctrlKey) {



                        // SHIFT + I to insert row
                        if (e.which == 73) {
                            e.stopPropagation();
                            //console.log('Insert item above');
                        }
                    }
                }
            });

            grid.onAddNewRow.subscribe(function (e, args) {
                // set a psuedo id for now
                var invoice_item_id = new Date().getTime();

                var item = {"invoice_item_id": invoice_item_id,
                    "product_id": 0,
                    "item_length": "",
                    "item_name": "",
                    "item_description": "",
                    "item_per_meter": "",
                    "item_qty": 1,
                    "item_price": 0,
                    "item_type": "",
                    "item_index": dataView.getLength()};

                $.extend(item, args.item);

                //dataView.addItem(item);
                addNewInvoiceItem(item);


            });

            dataView.onRowCountChanged.subscribe(function (e, args) {
                grid.updateRowCount();
                grid.render();
            });


            dataView.onRowsChanged.subscribe(function (e, args) {
                //console.log(args);
                grid.invalidateRows(args.rows);
                grid.render();
            });

            grid.onSelectedRowsChanged.subscribe(function (e, args) {

                var selrows = args.rows;
            });


        }

        $.post("<?php echo site_url('invoices/getItemsJSON'); ?>", {
            invoice_id: <?php echo uri_assoc('invoice_id'); ?>

        }, function (data) {
            if (data == 'session_expired') {
                window.location.reload();
            }

            //console.log(data.items);

            dataView.beginUpdate();
            display_invoice_amounts(data.invoice_amounts);
            dataView.setItems(data.items, 'invoice_item_id');
            dataView.endUpdate();

            // Need to scroll last row into view
            //activate new item row
            //$('.slick-cell:first').click();

        }, "json");



        $(document).on('click', '.addRowBelow', addRowMiddle);

//        $(document).on('click', '.deleteline', function(e){
//            e.preventDefault();
//            return false;
//        });

        $(document).on('click', '.open-popup', function () {
            var el = $(this);
            $.magnificPopup.open({
                items: {
                    src: '<div class="small-dialog">' + $(this).data('message') + '</div>', // can be a HTML string, jQuery object, or CSS selector
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
        });

    });

    function additemsToDelete(elem) {

    }

    $(document).on('click', '#deletelineitemselected', function (e) {



        var selectedRows = grid.getSelectedRows();
        e.preventDefault();
        var itemsToDelete = [];

        for (var k = 0; k < selectedRows.length; k++) {
            var data = dataView.getItem(selectedRows[k]);
            if (typeof data != 'undefined') {
                itemsToDelete.push(data.invoice_item_id);
            }
        }

        if (itemsToDelete.length > 0) {
            if (confirm('Are you sure you wish to delete the selected items?')) {
                $('.mygridwrap .loader').addClass('loading');
                dataView.beginUpdate();
                var delItems = [];
                for (var i = 0; i < itemsToDelete.length; i++) {
                    var item = dataView.getItemById(itemsToDelete[i]);
                    if (typeof item != 'undefined') {
                        dataView.deleteItem(item.invoice_item_id);
                        delItems.push(item);
                    }
                }
                dataView.endUpdate();
                deleteInvoiceItem(delItems);
                itemsToDelete = [];
            }
        } else {
            alert('Please select items first.');
        }
    });

    function addRowMiddle() {
        var dataAll = dataView.getItems();

        var currRowNum = grid.getActiveCell().row;

        var invoice_item_id = new Date().getTime();
        var item = {
            "invoice_item_id": invoice_item_id,
            "product_id": 0,
            "item_length": "",
            "item_name": "",
            "item_description": "",
            "item_per_meter": "",
            "item_qty": 1,
            "item_price": '0.00',
            'item_subtotal': '0.00',
            "item_type": "",
            "item_index": position
        };
        //position idx where new row would be added
        var position = (currRowNum + 1);
        //array.splice(index,howmany,item1,.....,itemX)     


        //need to add that to database
        $.post("<?php echo site_url('invoices/addNewInvoiceItem'); ?>", {
            invoice_id: <?php echo uri_assoc('invoice_id'); ?>,
            item: JSON.stringify(item)

        }, function (data) {
            if (data == 'session_expired') {
                window.location.reload();
            }
            //var new_item = $();
            //$.extend(new_item,data.item);

            if (data == "Not enough") {
                window.location.reload();
            }
            var elements = document.getElementsByClassName("error");
            for (var i = 0; i < elements.length; i++) {
                elements[i].parentNode.removeChild(elements[i]);
            }
            dataAll.splice(position, 0, data.item);
            dataView.setItems(dataAll);
            grid.render();
            dataView.endUpdate();
            grid.invalidate();
            grid.setSelectedRows([]);
            grid.editActiveCell();
//            var item = dataView.getItemById(data.item.invoice_item_id);
//            console.log(data.item.invoice_item_id);
//            dataView.updateItem(item.invoice_item_id, data.item);

            display_invoice_amounts(data.invoice_amounts);
            updateInvoiceItemSortOrder();
        }, "json");
    }

    function addRowMiddle_() {

        var items = dataView.getItems();
        // console.log(items);

        var currRowNum = grid.getActiveCell().row;
        var position = (currRowNum + 1);

        var invoice_item_id = new Date().getTime();

        var item = {"invoice_item_id": invoice_item_id,
            "product_id": 0,
            "item_length": "",
            "item_name": "",
            "item_description": "",
            "item_per_meter": "",
            "item_qty": 1,
            "item_price": 0,
            "item_type": "",
            "item_index": position
        };
        addNewInvoiceItem(item, position);
    }
</script>

