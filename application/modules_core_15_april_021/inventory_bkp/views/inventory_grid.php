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
<script type="text/javascript" src="<?php echo base_url(); ?>assets/slick/json2.js"></script>
<!--<script type="text/javascript" src="<?php //echo base_url();  ?>assets/slick/json2.js"></script>-->
<script type="text/javascript" src="<?php echo base_url(); ?>assets/jquery/jquery.magnific-popup.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/latest/plugins/slick.checkboxselectcolumn.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/slick/plugins/slick.checkboxselectcolumn.js"></script>

<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/style/css/magnific-popup.css" />

<div id="inventoryGrid" style="width: 1160px;height: 500px; overflow: hidden;outline: 0px;position: relative;"></div>
<div class="loader" style="    position: absolute;
    top: 40%;
    left: 0;
    width: 100%;
    text-align: center;">
        <img src="<?php echo base_url() . 'assets/style/img/loading.gif'; ?>"> Loading inventories. This may take few seconds.
    </div>
<script type="text/javascript">


    StatusCellFormatter = function (row, cell, value, columnDef, dataContext) {
        return '<span class="status_' + dataContext['s'] + '">' + value + '</span>';

    }

    PriceCellFormatter = function (row, cell, value, columnDef, dataContext) {
        return currencySymbol + value;
    };

    SupplierPriceCellFormatter = function (row, cell, value, columnDef, dataContext) {
        var cur_left = dataContext["currency_symbol_left"];
        var cur_right = dataContext["currency_symbol_right"];
        return cur_left + value + cur_right;
    };


    var suppliers;
    var suppliersById = {};

    var statii;
    var statusById = {};

    var projects;
    var projectsById = {};

    var grid;
    var dataView;

    var dateField = 'e';
    var orderNumField = 'n';
    var sortCol = orderNumField;
    var sortDir = 1;

    var grid;
    var dataView;

    var all_data_loaded = true;

    SupplierCellEditor = function (args) {
        var $input;
        var defaultValue;
        var scope = this;

        this.init = function () {


            $input = $("<INPUT type=text id='supplier' class='editor-text' />")
                    .appendTo(args.container)
                    .bind("keydown.nav", function (e) {
                        if (e.keyCode === $.ui.keyCode.LEFT || e.keyCode === $.ui.keyCode.RIGHT) {
                            e.stopImmediatePropagation();
                        }
                    })
                    .focus()
                    .select()
                    .autocomplete({
                        minLength: 1,
                        source: suppliers

                    })
                    .bind("keydown.nav", function (e) {
                        if (e.keyCode === $.ui.keyCode.DOWN || e.keyCode === $.ui.keyCode.UP) {
                            e.stopImmediatePropagation();
                        }
                    });


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

            //item['client_id'] =

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
    
    function comparer__(a, b) {
        var x = a[sortCol], y = b[sortCol];
    return sortDir * (x === y ? 0 : (x > y ? 1 : -1));

    }

    function comparer(a, b) {
        var x = a[sortCol], y = b[sortCol];
        if($.trim(y) == '' || y == null){
            return 1;
        }
        //console.log(x); console.log(y);
        // compare by invoice date if other values the same unless
        // already sorting by date in which case secondary sort by id
        if (x == y) {

            x = a.id
            y = b.id;

            // always do secondary sort in descending order
            return (x == y ? 0 : (x > y ? -sortDir : sortDir));

        } else
            return (x > y ? 1 : -1);

    }

    function floatcomparer(a, b) {
        var x = parseFloat(a[sortCol]), y = parseFloat(b[sortCol]);
        //console.log(x); console.log(y);
        // compare by invoice date if other values the same unless
        // already sorting by date in which case secondary sort by id
        if (x == y) {

            x = a.id
            y = b.id;



            // always do secondary sort in descending order
            return (x == y ? 0 : (x > y ? -sortDir : sortDir));

        } else
            return (x > y ? 1 : -1);

    }
    
    function pricecomparer(a, b) {
        var vala = a[sortCol];
       
        var valb = b[sortCol];
        
        vala = vala.replace("$", "");
        valb = valb.replace("$", "");
        vala = vala.replace(",", "");
        valb = valb.replace(",", "");
        
        //console.log(vala); console.log(valb);
        
        var x = parseFloat(vala), y = parseFloat(valb);
        //console.log(x); console.log(y);
        // compare by invoice date if other values the same unless
        // already sorting by date in which case secondary sort by id
        if (x == y) {

            x = a.id
            y = b.id;

            // always do secondary sort in descending order
            return (x == y ? 0 : (x > y ? -sortDir : sortDir));
        } else{
            return (x > y ? 1 : -1);
        }

    }

    var itemsToDelete = [];
    var columnFilters = {};

    var columns = [
        {id: "i_t", name: "Type", field: "i_t", sortField: "i_t", width: parseFloat((typeof $.cookie('inventory_grid_i_t') != 'undefined') ? $.cookie('inventory_grid_i_t') : '50'), sortable: true, fieldLink: "id",
            linkUrl: "<?php echo site_url('inventory/form/inventory_id/'); ?>", asyncPostRender: asyncRenderItemLink},
        {id: "sku", name: "Sku", field: "sku", sortField: "sku", width: parseFloat((typeof $.cookie('inventory_grid_sku') != 'undefined') ? $.cookie('inventory_grid_sku') : '90'), sortable: true},
        {id: "supplier_name", name: "Supplier", field: "supplier_name", fieldLink: "supplier_id",
            linkUrl: "<?php echo site_url('clients/details/client_id/'); ?>", width: parseFloat((typeof $.cookie('inventory_grid_supplier_name') != 'undefined') ? $.cookie('inventory_grid_supplier_name') : '150'), sortable: true, asyncPostRender: asyncRenderItemLink, editor: SupplierCellEditor},
        {id: "name", name: "Cat #", field: "name", sortField: "name", width: parseFloat((typeof $.cookie('inventory_grid_name') != 'undefined') ? $.cookie('inventory_grid_name') : '155'), sortable: true, fieldLink: "id", editor: TextCellEditor,
            linkUrl: "<?php echo site_url('inventory/form/inventory_id/'); ?>", asyncPostRender: asyncRenderItemLink},
        {id: "s_c", name: "Supplier Cat #", field: "s_c", sortField: "s_c", width: parseFloat((typeof $.cookie('inventory_grid_s_c') != 'undefined') ? $.cookie('inventory_grid_s_c') : '155'), sortable: true},
        {id: "description", name: "Description", field: "description", sortField: "description", width: parseFloat((typeof $.cookie('inventory_grid_description') != 'undefined') ? $.cookie('inventory_grid_description') : '155'), sortable: true, editor: LongTextCellEditor},

//    {id:"supplier_description", name:"Supplier Description", field:"supplier_description",sortField:"",width:150,sortable:false,editor: TextCellEditor},

        {id: "qty", name: "Qty", field: "qty", sortField: "qty", width: parseFloat((typeof $.cookie('inventory_grid_qty') != 'undefined') ? $.cookie('inventory_grid_qty') : '80'), sortable: true},
        {id: "p_q", name: "Pending Qty", field: "p_q", sortField: "p_q", width: parseFloat((typeof $.cookie('inventory_grid_p_q') != 'undefined') ? $.cookie('inventory_grid_p_q') : '80'),asyncPostRender:asyncRenderPendingQty, sortable: true},
        {id: "o_q", name: "Open Order Qty", sortField: "o_q", field: "o_q", width: parseFloat((typeof $.cookie('inventory_o_q') != 'undefined') ? $.cookie('inventory_o_q') : '80'), asyncPostRender: asyncRenderOpnOrdQty, sortable: true},

        {id: "base_price", name: "Price", field: "base_price", sortField:"base_price", width: parseFloat((typeof $.cookie('inventory_grid_base_price') != 'undefined') ? $.cookie('inventory_grid_base_price') : '100'), sortable: true},
        {id: "s_p", name: "Supplier Price", sortField:"s_p", field: "s_p", width: parseFloat((typeof $.cookie('inventory_grid_s_p') != 'undefined') ? $.cookie('inventory_grid_s_p') : '100'), sortable: true},
                /*
                 {id:"location", name:"Location", field:"location",sortField:"",width:parseFloat((typeof $.cookie('inventory_grid_location') != 'undefined')?$.cookie('inventory_grid_location'):'130'),sortable:false},
                 */
    ];



    var checkboxSelector = new Slick.CheckboxSelectColumn({
        cssClass: "chkbox_inven"
    });

//var groupItemMetadataProvider = new Slick.Data.GroupItemMetadataProvider({ checkboxSelect: true, checkboxSelectPlugin: checkboxSelector });

    columns.unshift(checkboxSelector.getColumnDefinition());





    var options = {
        autoEdit: true,
        enableCellNavigation: true,
        enableColumnReorder: false,
        enableAsyncPostRender: true,
        showHeaderRow: true,
        editable: true
    };


    function updateInventoryDetails(inventoryItem) {

        var dv = inventoryItem['e'] * 1000;
        var d = new Date(dv);
        inventoryItem['inventoryItem_date'] = $.datepicker.formatDate('dd/mm/yy', d);
        // inventoryItem['inventoryItem_status'] = statusById[inventoryItem['s']];
        var id = inventoryItem['supplier_id'];
        var idx = suppliersById[id];
        inventoryItem['supplier_name'] = '(New)'
        if (idx === undefined) {
            inventoryItem['supplier_name'] = '(New)'
        } else {
            var supplier = suppliers[idx];
            inventoryItem['supplier_name'] = supplier['supplier_name'];
        }
    }


    function asyncRenderOpnOrdQty(cellNode, row, dataContext, colDef) {

        //console.log(cellNode);
        //console.log(dataContext[colDef.field]);
        //console.log([colDef.field]);

        var f = colDef.field;
        // var fl = colDef.fieldLink;
        if (dataContext[f] == null){
            var a = '<a data-invnum="' + dataContext['id'] + '" data-effect="mfp-zoom-in" class="inv-docket open-popup" href="<?= site_url() ?>/inventory/getInvOpnOrdrQty/inventory_id/' + dataContext['id'] + '">0</a>';
            $(cellNode).html(a);
        }else{
            var a = '<a data-invnum="' + dataContext['id'] + '" data-effect="mfp-zoom-in" class="inv-docket open-popup" href="<?= site_url() ?>/inventory/getInvOpnOrdrQty/inventory_id/' + dataContext['id'] + '">' + dataContext[f] + '</a>';
            $(cellNode).html(a);
        }
        //return;
        // var a = '<a data-invnum="'+dataContext['id']+'" data-effect="mfp-zoom-in" class="inv-docket open-popup" href="<?= site_url() ?>/inventory/getInvOpnOrdrQty/invoice_id/' + dataContext['id'] + '">' + dataContext['o_qty'] + '</a>';
        
    }
    
    function asyncRenderPendingQty(cellNode, row, dataContext, colDef) {

//        console.log(cellNode);
//        console.log(dataContext);

        var f = colDef.field;
        // var fl = colDef.fieldLink;
        if (dataContext[f] == null)
            return;
        // var a = '<a data-invnum="'+dataContext['id']+'" data-effect="mfp-zoom-in" class="inv-docket open-popup" href="<?= site_url() ?>/inventory/getInvOpnOrdrQty/invoice_id/' + dataContext['id'] + '">' + dataContext['o_qty'] + '</a>';
        var a = '<a data-invnum="' + dataContext['id'] + '" data-effect="mfp-zoom-in" class="inv-docket-pening open-popup-pending" href="<?= site_url() ?>/inventory/getInvPendingrQty/inventory_id/' + dataContext['id'] + '">' + dataContext[f] + '</a>';
        $(cellNode).html(a);
    }


    function update_inventory(item, field)
    {

        $.post("<?php echo site_url('inventory/ajax_update_inventory'); ?>", {

            post_item: JSON.stringify(item)

        }, function (data) {
            if (data == 'session_expired') {
                window.location.reload();
            }
            //console.log(data);
        }, "json");

    }
    ;


    function filter(item)
    {
        var res = true;

        for (var columnId in columnFilters) {
            var cf = columnFilters[columnId];
            if (res && cf !== undefined) {
                var c = grid.getColumns()[grid.getColumnIndex(columnId)];

                res = cf.test(item[c.field]);

            }
        }


        return res;
    }

    function asyncRenderItemLink(cellNode, row, dataContext, colDef)
    {
        var f = colDef.field;
        var fl = colDef.fieldLink;

        if (dataContext[f] == null)
            return;

        var a = '<a href="' + colDef.linkUrl + '/' + dataContext[fl] + '">' + dataContext[f] + '</a>';

        $(cellNode).html(a);

    }

    function applyFilter() {
        
        if (!all_data_loaded) {
            all_data_loaded = true;
            var show_archieved = '';
            <?php if ($this->input->get('show_archived') != NULL): ?>
            var show_archieved = '?show_archived=true';
            <?php endif; ?>
            <?php if ($this->input->get('only_archived') != NULL): ?>
            var show_archieved = '?only_archived=true';
            <?php endif; ?>
            $.post("<?php echo site_url('inventory/get_inventory_JSON'); ?>" + show_archieved, {
                limit: 10000,
                offset: 0
            }, function (data) {
                //console.log(data.suppliers);
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
                var inventoryItems = data.inventory;
                for (var i = 0, l = inventoryItems.length; i < l; i++) {
                    updateInventoryDetails(inventoryItems[i]);
                }
                dataView.setItems(inventoryItems, 'id');
                dataView.setFilter(filter);
                //dataView.sort(comparer, sortDir);
                dataView.endUpdate();
            }, "json");
        } else {
            dataView.refresh();
        }
    }


    function updateHeaderRow() {
        for (var i = 0; i < columns.length; i++) {
            if (columns[i].id !== "selector") {
                var header = grid.getHeaderRowColumn(columns[i].id);
                var w = columns[i].width - 16;
                $(header).empty();
                $("<input type='text'>")
                        .attr("placeholder", (columns[i].name != "<input type='checkbox'>") ? columns[i].name : '')
                        .data("columnId", columns[i].id)
                        .width(w)
                        .keyup(function (e) {
                            var val = $.trim($(this).val());
                            if (val == '')
                                columnFilters[$(this).data("columnId")] = undefined;
                            else {
                                columnFilters[$(this).data("columnId")] = new RegExp(val, 'i');
                            }
                            applyFilter();
                        })
                        .appendTo(header);
            }
        }
    }

    $(document).ready(function () {
        if (grid === undefined) {
            dataView = new Slick.Data.DataView();
            grid = new Slick.Grid($("#inventoryGrid"), dataView, columns, options);
            //var pager = new Slick.Controls.Pager(dataView, grid, $("#pager"));
            grid.registerPlugin(new Slick.AutoTooltips());
            grid.setSelectionModel(new Slick.RowSelectionModel());
            //grid.setSortColumn("order_date", false );
            grid.setSortColumn("order_number", false);
            grid.setSelectionModel(new Slick.RowSelectionModel({selectActiveRow: false}));
            grid.registerPlugin(checkboxSelector);
            dataView.onRowCountChanged.subscribe(function (e, args) {
                grid.updateRowCount();
                grid.render();
            });
            dataView.onRowsChanged.subscribe(function (e, args) {
                grid.invalidateRows(args.rows);
                grid.render();
            });
            grid.onCellChange.subscribe(function (e, args) {
                //dataView.updateItem(args.item[dataView.getIdProperty()], args.item);
                var column = grid.getColumns()[args.cell];
                update_inventory(args.item, column.field);
            });
            grid.onColumnsResized.subscribe(function (e, args) {
                for (var i = 0, totI = grid.getColumns().length; i < totI; i++) {
                    var column = grid.getColumns()[i];
                    var grid_col_width = column.width;
                    var cookie = $.cookie('inventory_grid_' + column.field, grid_col_width);
                }
            });
            grid.onSort.subscribe(function (e, args) {
                sortDir = args.sortAsc ? 1 : -1;
                if (args.sortCol.sortField == undefined)
                    sortCol = args.sortCol.field;
                else
                    sortCol = args.sortCol.sortField;
                
                if (args.sortCol.field == "qty" || args.sortCol.field == "o_qty") {
                    dataView.sort(floatcomparer, args.sortAsc);
                }else if (args.sortCol.field == "base_price" || args.sortCol.field == "s_p") {
                    //console.log('here..');
                    dataView.sort(pricecomparer, args.sortAsc);
                }else {
                    dataView.sort(comparer, args.sortAsc);
                }
            });
            $("#suppliers").change(function () {
                supplier_id_filter = $(this).val();
                dataView.refresh();
            });
            updateHeaderRow();
            resizing_header_js();
        }
        
//        var suppliers_inventory_json = <?php echo $suppliers_inventory ?>;
//        if(suppliers_inventory_json != 'undefined'){
//            dataView.beginUpdate();
//            suppliers = suppliers_inventory_json.suppliers;
//            // Update supplier indexiig
//            for (var i = 0, l = suppliers.length; i < l; i++) {
//                var id = suppliers[i]['supplier_id'];
//                suppliersById[id] = i;
//            }
//            var inventoryItems = suppliers_inventory_json.inventory;
//            for (var i = 0, l = inventoryItems.length; i < l; i++) {
//                updateInventoryDetails(inventoryItems[i]);
//            }
//            dataView.setItems(inventoryItems, 'id');
//            dataView.setFilter(filter);
//            //dataView.sort(comparer, sortDir);
//            dataView.endUpdate();
//        }
        
        
        
        var show_archieved = '';
        <?php if ($this->input->get('show_archived') != NULL): ?>
        var show_archieved = '?show_archived=true';
        <?php endif; ?>
        <?php if ($this->input->get('only_archived') != NULL): ?>
        var show_archieved = '?only_archived=true';
        <?php endif; ?>
            
        $.post("<?php echo site_url('inventory/get_suplier_inventory_index'); ?>" + show_archieved, {
            limit: 300,
        }, function (data) {
            
            //console.log(data);
            
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
            var inventoryItems = data.inventory;
            for (var i = 0, l = inventoryItems.length; i < l; i++) {
                updateInventoryDetails(inventoryItems[i]);
            }
            dataView.setItems(inventoryItems, 'id');
            dataView.setFilter(filter);
            //dataView.sort(comparer, sortDir);
            dataView.endUpdate();
            $('.loader').hide();
        }, 'json');
            
        $(document).on('click', '.open-popup', function (e) {
            var el = $(this);
            e.preventDefault();
            var url = $(this).attr('href');
            $.magnificPopup.open({
                items: {
                    src: '<div class="small-dialog" style="text-align:center;"><img src="<?= base_url() . 'assets/style/img/loading.gif'; ?>" /></div>', // can be a HTML string, jQuery object, or CSS selector
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
                data: {invnum: el.data('invnum')},
                dataType: 'html',
                success: function (data) {
                    $('.small-dialog').html(data);
                }
            });
        });
        
        $(document).on('click', '.open-popup-pending', function (e) {
            var el = $(this);
            e.preventDefault();
            var url = $(this).attr('href');
            $.magnificPopup.open({
                items: {
                    src: '<div class="small-dialog" style="text-align:center;"><img src="<?= base_url() . 'assets/style/img/loading.gif'; ?>" /></div>', // can be a HTML string, jQuery object, or CSS selector
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
                data: {invnum: el.data('invnum')},
                dataType: 'html',
                success: function (data) {
                    $('.small-dialog').html(data);
                }
            });
        });

    });

</script>

