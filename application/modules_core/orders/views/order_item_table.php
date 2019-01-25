<style>
    body, html {
        margin: 0;
        padding: 0;
        /*		overflow:hidden;*/
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


<div class="mygridwrap">
    <div id="itemGrid" style="width:1080px;height:500px;"></div>
    <div class="loader">
        <img src="<?php echo base_url().'assets/style/img/loading.gif'; ?>" />
    </div>
</div>
<!--<div id="dropzone" class="recycle-bin">Recycle Bin</div>-->

<div class="options-panel">
    <p>
        <?php echo $this->lang->line('subtotal'); ?>: <span id="amount_order_subtotal"></span> |
        <?php echo $this->lang->line('tax'); ?>: <span id="order_item_tax"></span> |
        <?php echo $this->lang->line('total'); ?>: <span id="order_total"></span>
    </p>
</div>

<script type="text/javascript">

<?php
global $CI;
echo "var currencySymbol = '" . $CI->mdl_mcb_data->setting('currency_symbol') . "';";
?>

    PriceCellFormatter = function (row, cell, value, columnDef, dataContext) {
        if(value == '0.00'){
            return '';
        }else{
            return currencySymbol + value;
        }
    };

    PerMeterPriceCellFormatter = function (row, cell, value, columnDef, dataContext) {
        if(value == '0.00'){
            return '';
        }else{
            return currencySymbol + value;
        }
    };

    QuantityCellFormatter = function (row, cell, value, columnDef, dataContext) {
        if( (value == '') || (value == '0.00') ){
            return '';
        }else{
            return parseFloat(value);
        }
    };
    
    LengthCellFormatter = function (row, cell, value, columnDef, dataContext) {
        if( (value == '') || (value == '0') ){
            return '';
        }else{
            return value;
        }
    };
    
    IntegerCellFormatter = function (row, cell, value, columnDef, dataContext) {
        return parseInt(value);
    };
    
    LengthCellEditor = function (args){
//        console.log(args);
        var $input;
        var defaultValue;
        var scope = this;
        this.init = function () {
            
            if((args['item']['item_use_length'] != '1' )){
                alert('Inventory is not using use length. Please Select Use Length First.');
                $input = $("<INPUT type='number' disabled class='editor-text' />");
            }else{
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
    
    PerMeterCellEditor = function (args){
        var $input;
        var defaultValue;
        var scope = this;
        this.init = function () {
            var attarDisable = '';
            
            attarDisable = 'disabled';
            
            $input = $("<INPUT type=number class='editor-text' />");
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
    
    QuantityCellEditor = function (args){
        var $input;
        var defaultValue;
        var scope = this;
        this.init = function () {
            var attarDisable = '';
            if(args['item']['stock_status'] == '1'){
                attarDisable = 'disabled';
            }
            $input = $("<INPUT type=number class='editor-text' "+attarDisable+" />");
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
    
    DescriptionCellEditor = function (args) {
        
            var $input, $wrapper;
            var defaultValue;
            var scope = this;
            
            this.init = function() {
                
                var $container = $("body");
                if( (args['item']['order_item_id']) > '0' ){
                    
                    $wrapper = $("<DIV style='z-index:10000;position:absolute;background:white;padding:5px;border:3px solid gray; -moz-border-radius:10px; border-radius:10px;'/>")
                        .appendTo($container);
                    $input = $("<TEXTAREA hidefocus rows=5 style='backround:white;width:250px;height:80px;border:0;outline:0'>")
                        .appendTo($wrapper);
                    $("<DIV style='text-align:right'><BUTTON>Save</BUTTON><BUTTON>Cancel</BUTTON></DIV>")
                        .appendTo($wrapper);
                }else{
                    $wrapper = $("")
                        .appendTo($container);
                        
                    $input = $("")
                        .appendTo($wrapper);

                }
                
                $wrapper.find("button:first").bind("click", this.save);
                $wrapper.find("button:last").bind("click", this.cancel);
                $input.bind("keydown", this.handleKeyDown);

                scope.position(args.position);
                $input.focus().select();
            };

            this.handleKeyDown = function(e) {
                if (e.which == $.ui.keyCode.ENTER && e.ctrlKey) {
                    scope.save();
                }
                else if (e.which == $.ui.keyCode.ESCAPE) {
                    e.preventDefault();
                    scope.cancel();
                }
                else if (e.which == $.ui.keyCode.TAB && e.shiftKey) {
                    e.preventDefault();
                    grid.navigatePrev();
                }
                else if (e.which == $.ui.keyCode.TAB) {
                    e.preventDefault();
                    grid.navigateNext();
                }
            };

            this.save = function() {
                args.commitChanges();
            };

            this.cancel = function() {
                $input.val(defaultValue);
                args.cancelChanges();
            };

            this.hide = function() {
                $wrapper.hide();
            };

            this.show = function() {
                $wrapper.show();
            };

            this.position = function(position) {
                $wrapper
                    .css("top", position.top - 5)
                    .css("left", position.left - 5)
            };

            this.destroy = function() {
                $wrapper.remove();
            };

            this.focus = function() {
                $input.focus();
            };

            this.loadValue = function(item) {
                $input.val(defaultValue = item[args.column.field]);
                $input.select();
            };

            this.serializeValue = function() {
                return $input.val();
            };

            this.applyValue = function(item,state) {
                item[args.column.field] = state;
            };

            this.isValueChanged = function() {
                return (!($input.val() == "" && defaultValue == null)) && ($input.val() != defaultValue);
            };

            this.validate = function() {
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
            
            var old_qty = args.item.item_qty;
            
            //console.log(args['item']['stock_status']);
            
            var attarDisable = '';
            if(args['item']['stock_status'] == '1'){
                attarDisable = 'disabled';
            }
            
            $input = $("<INPUT type=text id='product' "+attarDisable+" class='editor-text' />")
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
                            req['supplier_id'] = '<?= $order->supplier_id ?>';
                            $.ajax({
                                url: "<?= site_url('orders/supplier_inventory_name_auto_complete_JSON'); ?>",
                                dataType: 'json',
                                type: 'POST',
                                data: req,
                                success: function (data) {
                                    if (data == 'session_expired') {
                                        window.location.reload();
                                    }				
                                    resp(data.search_results);
                                }
                            });
                        },
                        focus: function (event, ui) {
                             console.log(ui);
                            $("#product").val(ui.item.item_name);
                            return false;
                        }
                        
                    })
                    .bind("keydown.nav", function (e) {
                        if (e.keyCode === $.ui.keyCode.DOWN || e.keyCode === $.ui.keyCode.UP) {
                            e.stopImmediatePropagation();
                        }
                    });
                    
                    $input.data("autocomplete")._renderItem = function (ul, item) {
                        return $("<li></li>")
                            .data("item.autocomplete", item)
                            .append("<a>" + item.item_name +"<br>" + item.item_description + "</a>")
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
//			console.log(item);
            defaultValue = item[args.column.field] || "";
            $input.val(defaultValue);
            
            // console.log($input);
            
            $input[0].defaultValue = defaultValue;
            $input.select();
        };

        this.serializeValue = function () {
            return $input.val();
        };

        this.applyValue = function (item, state) {
            
//            console.log(args);
//            console.log(item);
            
            item[args.column.field] = state;
            // nasty hard code here to fix someway
//            item.item_description = $desc.val();
//            item.item_supplier_price = $price.val();
        };

        this.isValueChanged = function () {
            return (!($input.val() == "" && defaultValue == null)) && ($input.val() != defaultValue);
        };

        this.validate = function () {
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
    var rowIndexToDelete = [];
    var stockEditURL = "<?= site_url('orders/order_items/order_item_stock_change'); ?>";

    var columns = [
        <?php if($order->is_inventory_supplier == '1'){ ?>
            {id: "item_name", behavior: "move", width: 175, minWidth: 150, name: "Cat. #", field: "item_name", editor: ProductCellEditor, validator: requiredFieldValidator},
            {id: "item_supplier_code", behavior: "move", width: 175, minWidth: 150, name: "Supplier Cat. #", field: "item_supplier_code", editor: TextCellEditor},
        <?php }else{ ?>
            {id: "item_name", behavior: "move", width: 250, minWidth: 150, name: "Item", field: "item_name", editor: ProductCellEditor, validator: requiredFieldValidator},
        <?php } ?> 
        {id: "item_type", width: 100, name: "<?php echo $this->lang->line('item_type'); ?>", field: "item_type", sortable: true, editor: TextCellEditor},
        <?php if($order->is_inventory_supplier == '1'){ ?>
            {id: "item_supplier_description", width: 210, minWidth: 200, name: "Supplier Description", field: "item_supplier_description", editor: DescriptionCellEditor},
        <?php }else{ ?>
            {id: "item_description", width: 310, minWidth: 250, name: "<?php echo $this->lang->line('item_description'); ?>", field: "item_description", editor: DescriptionCellEditor},
        <?php } ?> 
        
        {id: "item_per_meter", width: 55, name: "Per Meter", field: "item_per_meter", cssClass: "cell-right-align", editor: PerMeterCellEditor},
        {id: "item_length", width: 53, name: "Length", field: "item_length", cssClass: "cell-right-align", editor: LengthCellEditor, formatter: LengthCellFormatter},
        {id: "item_qty", width: 35, name: "<?php echo $this->lang->line('qty'); ?>", field: "item_qty", cssClass: "cell-right-align", editor: QuantityCellEditor, formatter: QuantityCellFormatter},        
        {id: "item_supplier_price", width: 75, name: "<?= $this->lang->line('price').'('.$order->currency_code.')'; ?>", field: "item_supplier_price", cssClass: "cell-right-align", editor: QuantityCellEditor, formatter: PerMeterPriceCellFormatter},
        
//        {id: "item_price", width: 80, name: "<?php echo $this->lang->line('unit_price'); ?>", field: "item_price", cssClass: "cell-right-align", editor: TextCellEditor, formatter: PriceCellFormatter},
        {id: "item_subtotal", width: 95, name: "<?= $this->lang->line('subtotal').'('.$order->currency_code.')'; ?>", field: "item_subtotal", cssClass: "cell-right-align", formatter: PriceCellFormatter},
        {id: "order_item_id", name: "Stock", field: "stock_action", fieldLink: "order_item_id",
            linkUrl: stockEditURL, width: 80, asyncPostRender: asyncRenderItemLink}    
//        {id: "item_action", name: "", field: "item_action", cssClass: "cell-center-align", formatter: addRowIcon}
    ];

    var options = {
        autoEdit: false,
        editable: true,
        enableCellNavigation: true,
        enableColumnReorder: false,
        asyncEditorLoading: false,
        enableAddRow: true,
        enableRowReordering: true,
        enableAsyncPostRender: true,
    };
    var checkboxSelector = new Slick.CheckboxSelectColumn({
        cssClass: "slick-cell-checkboxsel deleteline"
    });
    columns.unshift(checkboxSelector.getColumnDefinition());
    
    function asyncRenderItemLink(cellNode, row, dataContext, colDef) {
        
        // console.log(cellNode);
        var f = colDef.field;
        var fl = colDef.fieldLink;
        if (dataContext[f] == null)
            return;
        var a = '<a style="padding: 4px;padding-left: 8px;background: #e0e0e0;\n\
                            padding-right: 8px;"\n\
                            href="' + colDef.linkUrl + '/<?= uri_assoc('order_id'); ?>/' + dataContext[fl] + '"\n\
                            onclick="warnStockQty('+dataContext['item_available_qty']+')">' + dataContext[f] + '</a>';
        $(cellNode).html(a);
    }
    
    function addRowIcon() {
        return '<span class="addRowBelow"><img src="<?php echo base_url(); ?>assets/style/img/rowadd.png"/></span>';
    }
    function getProductData(item) {
        
//        console.log(item);
        
        $.post("<?php echo site_url('products/jquery_product_data'); ?>", {
            invoice_item_id: item.invoice_item_id,
            product_name: item.item_name
        }, function (data) {
            if (data == 'session_expired') {
                window.location.reload();
            }
            //var json_data = "product = " + product_data;
            //eval(json_data);
            invoice_item_id = data.invoice_item_id;
            var new_item = dataView.getItemById(invoice_item_id);
            new_item.product_id = product.product_id;
            new_item.item_name = product.product_name;
            new_item.item_description = product.product_description;
            new_item.item_price = product.product_base_price;
            dataView.updateItem(item.invoice_item_id, new_item);
        });
        return false;
    };
    /*
     *   Special processing if item quantity = -1 since this will generate a subtotal
     *   so update description with default "Subtotal:"
     */
    function checkItemSpecialValues(item) {
        
        
        
        if ((item.item_qty == SUBTOTAL_QTY) && (item.item_description == '')) {
            item.item_description = SUBTOTAL_DESC
        }
        
//        console.log(checkItemSpecialValues);
        
//        console.log(item);
//        console.log(checkItemSpecialValues);
        
        return item;
    }
    
    function addNewOrderItem(item) {
        
        $.post("<?php echo site_url('orders/addNewOrderItemJSON'); ?>", {
            order_id: <?= uri_assoc('order_id'); ?>,
            item: JSON.stringify(item)
    
        }, function (data) {
            if (data == 'session_expired') {
                window.location.reload();
            }
            if (data == "Not enough") {
                window.location.reload();
            }
            var elements = document.getElementsByClassName("error");
            for (var i = 0; i < elements.length; i++) {
                elements[i].parentNode.removeChild(elements[i]);
            }
            var new_item = data.item;
            dataView.addItem(new_item);
            display_order_amounts(data.order_amounts);
            grid.editActiveCell();

        }, "json");
    };


    function updateOrderItem(item, field){
    
        var post_data = checkItemSpecialValues(item); 
//        console.log(post_data);
        $.post("<?php echo site_url('orders/updateOrderItemJSON'); ?>", {
            order_id: <?php echo uri_assoc('order_id'); ?>,
            item: JSON.stringify(post_data)
            
        }, function (data) {
            if (data == 'session_expired') {
                window.location.reload();
            }
            if (data == "Not enough") {
                window.location.reload();
            }
            var elements = document.getElementsByClassName("error");
            for (var i = 0; i < elements.length; i++) {
                elements[i].parentNode.removeChild(elements[i]);
            }
            var item = dataView.getItemById(data.item.order_item_id);
            dataView.updateItem(item.order_item_id, data.item);
            display_order_amounts(data.order_amounts);
        }, "json");
    };

    function updateOrderItemSortOrder() {

        var sortOrder = [];
        var idProperty = dataView.getIdProperty();
        var data = dataView.getItems();

        for (var i = 0, l = data.length; i < l; ++i) {
            sortOrder[i] = dataView.getItemByIdx(i)[idProperty];
        }
        // send new sort order to server for row updates
        $.post("<?php echo site_url('orders/setItemsSortOrderJSON'); ?>", {
            order_id: <?php echo uri_assoc('order_id'); ?>,
            sort_order: sortOrder.toString()

        }, function (data) {
            if (data == 'session_expired') {
                window.location.reload();
            }

        }, "json");

    }

    function display_order_amounts(order_amounts) {
        $('#amount_order_subtotal').html(order_amounts.order_sub_total);
        $('#order_item_tax').html(order_amounts.tax_total);
        $('#order_total').html(order_amounts.order_total);
    }
    
    function deleteOredrItem(allItems) {
        var order_item_ids = '';
        for (var i = 0; i < allItems.length; i++) {
            var item = allItems[i];
            order_item_ids += item.order_item_id + ',';
        }
        $.post("<?php echo site_url('orders/deleteOrderItemsJSON'); ?>", {
            order_id: <?php echo uri_assoc('order_id'); ?>,
            order_item_ids: order_item_ids

        }, function (data) {
            if (data == 'session_expired') {
                window.location.reload();
            }
            $('.mygridwrap .loader').removeClass('loading');
            display_order_amounts(data.order_amounts);
            dataView.endUpdate();
            grid.invalidate();
            grid.setSelectedRows([]);
        }, "json");
    };

    function requiredFieldValidator(name) {
        if (name == null || name == undefined || !name.length)
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

//            grid.getActiveCell()
            //grid.registerPlugin(checkboxSelector);

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
                    //console.log('row[' + i + '] = ' + item.id);
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
                dataView.setItems(data, 'id');
                grid.resetActiveCell();
                grid.setSelectedRows(selectedRows);
                dataView.endUpdate();
                updateOrderItemSortOrder();
            });
            grid.registerPlugin(moveRowsPlugin);
            grid.onActiveCellChanged.subscribe(function (e, args) {
            
            });
            grid.onSort.subscribe(function (e, args) {
                sortDir = args.sortAsc ? 1 : -1;
                sortCol = args.sortCol.field;
                sortNumeric = args.sortCol.sortNumeric;
                dataView.sort(comparer, args.sortAsc);
                updateOrderItemSortOrder();
            });

            grid.onDragInit.subscribe(function (e, dd) {
                // prevent the grid from cancelling drag'n'drop by default
                e.stopImmediatePropagation();
            });
            // wire up model events to drive the grid
            grid.onCellChange.subscribe(function (e, args) {
                //dataView.updateItem(args.item[dataView.getIdProperty()], args.item);
                var column = grid.getColumns()[args.cell];
                updateOrderItem(args.item, column.field);
            });
            grid.onKeyDown.subscribe(function (e, args) {
                var handled = e.isImmediatePropagationStopped();

                if (!handled) {
                    if (e.shiftKey && !e.altKey && !e.ctrlKey) {

                        // SHIFT + I to insert row
                        if (e.which == 73) {
                            e.stopPropagation();
                            // console.log('Insert item above');
                        }
                    }
                }
            });

            grid.onAddNewRow.subscribe(function (e, args) {
                // set a psuedo id for now
                var order_item_id = new Date().getTime();
                var item = {
                    "order_id":'<?= uri_assoc('order_id'); ?>',
                    "product_id": 0,
                    "item_length": 0,
                    "item_name": "",
                    "item_description": "",
                    "item_per_meter": "",
                    "item_supplier_code": "",
                    "item_supplier_description": "",
                    "item_supplier_price": "",
                    "item_qty": 1,
                    "item_price": 0,
                    "item_type": "",
                    "item_index": dataView.getLength()};
                $.extend(item, args.item);
                //dataView.addItem(item);
                addNewOrderItem(item);
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
        $.post("<?php echo site_url('orders/getOrderItemsJSON'); ?>", {
            order_id: <?php echo uri_assoc('order_id'); ?>
        }, function (data) {
            if (data == 'session_expired') {
                window.location.reload();
            }
            dataView.beginUpdate();
            display_order_amounts(data.order_amounts);
            dataView.setItems(data.items, 'id');
            dataView.endUpdate();
            
        }, "json");
        
        
        $(document).on('click', '.addRowBelow', addRowMiddle);
        
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
                itemsToDelete.push(data.order_item_id);
            }
        }

        if (itemsToDelete.length > 0) {
            if (confirm('Are you sure you wish to delete the selected item?')) {
                $('.mygridwrap .loader').addClass('loading');
                dataView.beginUpdate();
                var delItems = [];
                for (var i = 0; i < itemsToDelete.length; i++) {
                    var item = dataView.getItemById(itemsToDelete[i]);
                    if (typeof item != 'undefined') {
                        dataView.deleteItem(item.order_item_id);
                        delItems.push(item);
                    }
                }
                dataView.endUpdate();
                deleteOredrItem(delItems);
                itemsToDelete = [];
            }
        } else {
            alert('Please select items first.');
        }
    });

    function addRowMiddle() {
        var dataAll = dataView.getItems();
        var currRowNum = grid.getActiveCell().row;
        var order_item_id = new Date().getTime();
        var item = {
            "order_item_id": order_item_id,
            "product_id": 0,
            "item_length": "",
            "item_name": "",
            "item_description": "",
            "item_per_meter": "",
            "item_supplier_code": "",
            "item_supplier_description": "",
            "item_supplier_price": "",
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
        $.post("<?php echo site_url('orders/addNewOrderItemJSON'); ?>", {
            order_id: <?php echo uri_assoc('order_id'); ?>,
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
//            var item = dataView.getItemById(data.item.order_item_id);
//            console.log(data.item.order_item_id);
//            dataView.updateItem(item.order_item_id, data.item);

            display_order_amounts(data.order_amounts);
            updateOrderItemSortOrder();
        }, "json");
    }
    
</script>
<script type="text/javascript">
    function warnStockQty(qt){
//        if(qt < 0){
//            var wsqConf = confirm("It has negetive quantity. Do you wanna continue?");
//            if (wsqConf != true) {
//                event.preventDefault();
//            }
//        }
    }
</script>
