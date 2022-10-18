<style>
    body, html {
        margin: 0;
        padding: 0;
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


    var grid;
    var dataView;

    var dateField = 'e';
    var orderNumField = 'n';
    var sortCol = orderNumField;
    var sortDir = 1;

    var grid;
    var dataView;
    // var url = "<?php //echo site_url('inventory/get_inventory_JSON'); ?>";
    var url = "<?php echo site_url('inventory/get_suplier_inventory_index'); ?>";

    var all_data_loaded = false;

    var offset = 0;
    var limit = 500;
    var total_data = 0;
    var search_params = null;
    var onDataLoading = new Slick.Event();
    var onDataLoaded = new Slick.Event();
    var loadingIndicator = null;
    var show_archieved = '<?php ($this->input->get('show_archived') != NULL) ? '?show_archived=true' : '?only_archived=true'; ?>';
    var suppliers = <?=$suppliers?>;
    var suppliersById = {};
    if(suppliers && suppliers.length > 0) {
       // Update supplier indexiig
        for (let i = 0, l = suppliers.length; i < l; i++) {
            let id = suppliers[i]['supplier_id'];
            suppliersById[id] = i;
        } 
    }
    var _changeInterval = null;
    var currentRequest;

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
        
        var x = parseFloat(vala), y = parseFloat(valb);
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
        {id: "product_type", name: "Type", field: "i_t", sortField: "i_t", width: parseFloat((typeof $.cookie('inventory_grid_i_t') != 'undefined') ? $.cookie('inventory_grid_i_t') : '50'), sortable: true, fieldLink: "id",
            linkUrl: "<?php echo site_url('inventory/form/inventory_id/'); ?>", asyncPostRender: asyncRenderItemLink},
        {id: "sku", name: "Sku", field: "sku", sortField: "sku", width: parseFloat((typeof $.cookie('inventory_grid_sku') != 'undefined') ? $.cookie('inventory_grid_sku') : '90'), sortable: true},
        {id: "supplier_name", name: "Supplier", field: "supplier_name", fieldLink: "supplier_id",
            linkUrl: "<?php echo site_url('clients/details/client_id/'); ?>", width: parseFloat((typeof $.cookie('inventory_grid_supplier_name') != 'undefined') ? $.cookie('inventory_grid_supplier_name') : '150'), sortable: true, asyncPostRender: asyncRenderItemLink, editor: SupplierCellEditor},
        {id: "cat_name", name: "Cat #", field: "name", sortField: "name", width: parseFloat((typeof $.cookie('inventory_grid_name') != 'undefined') ? $.cookie('inventory_grid_name') : '155'), sortable: true, fieldLink: "id", editor: TextCellEditor,
            linkUrl: "<?php echo site_url('inventory/form/inventory_id/'); ?>", asyncPostRender: asyncRenderItemLink},
        {id: "supplier_code", name: "Supplier Cat #", field: "s_c", sortField: "s_c", width: parseFloat((typeof $.cookie('inventory_grid_s_c') != 'undefined') ? $.cookie('inventory_grid_s_c') : '155'), sortable: true},
        {id: "description", name: "Description", field: "description", sortField: "description", width: parseFloat((typeof $.cookie('inventory_grid_description') != 'undefined') ? $.cookie('inventory_grid_description') : '155'), sortable: true, editor: LongTextCellEditor},
        {id: "qty", name: "Qty", field: "qty", sortField: "qty", width: parseFloat((typeof $.cookie('inventory_grid_qty') != 'undefined') ? $.cookie('inventory_grid_qty') : '80'), sortable: true},
        {id: "selector", name: "Pending Qty", field: "p_q", sortField: "p_q", width: parseFloat((typeof $.cookie('inventory_grid_p_q') != 'undefined') ? $.cookie('inventory_grid_p_q') : '80'),asyncPostRender:asyncRenderPendingQty, sortable: true, className: 'abc'},
        {id: "open_order_qty", name: "Open Order Qty", sortField: "o_q", field: "o_q", width: parseFloat((typeof $.cookie('inventory_o_q') != 'undefined') ? $.cookie('inventory_o_q') : '80'), asyncPostRender: asyncRenderOpnOrdQty, sortable: true},

        {id: "base_price", name: "Price", field: "base_price", sortField:"base_price", width: parseFloat((typeof $.cookie('inventory_grid_base_price') != 'undefined') ? $.cookie('inventory_grid_base_price') : '100'), sortable: true},
        {id: "supplier_price", name: "Supplier Price", sortField:"s_p", field: "s_p", width: parseFloat((typeof $.cookie('inventory_grid_s_p') != 'undefined') ? $.cookie('inventory_grid_s_p') : '100'), sortable: true},
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

        var f = colDef.field;
        // var fl = colDef.fieldLink;
        if (dataContext[f] == null)
            return;
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

    function asyncRenderItemLink(cellNode, row, dataContext, colDef)
    {
        var f = colDef.field;
        var fl = colDef.fieldLink;

        if (dataContext[f] == null)
            return;

        var a = '<a href="' + colDef.linkUrl + '/' + dataContext[fl] + '">' + dataContext[f] + '</a>';

        $(cellNode).html(a);

    }

    function updateHeaderRow() {
        for (var i = 0; i < columns.length; i++) {
            let css = null;
            if (columns[i].id == "selector") {
                css = {visibility:'hidden'};
            }
            var header = grid.getHeaderRowColumn(columns[i].id);
            var w = columns[i].width - 16;
            $(header).empty();
            $("<input type='text'>")
                    .attr("placeholder", (columns[i].name != "<input type='checkbox'>") ? columns[i].name : '')
                    .css(css)
                    .data("columnId", columns[i].id)
                    .width(w)
                    .on('change paste input', function (e) {
                        var val = $.trim($(this).val());

                        if (val == '')
                            columnFilters[$(this).data("columnId")] = false;
                        else {
                            columnFilters[$(this).data("columnId")] = val;
                        }
                        if(_changeInterval) clearInterval(_changeInterval);

                        if($(this).is(':focus')){
                            _changeInterval = setInterval(function(){
                                clearInterval(_changeInterval);
                                search_params = null;
                                search_params = columnFilters;
                                all_data_loaded = false;
                                offset = 0;
                                grid.scrollRowIntoView(1, true);
                                total_data = limit;
                                loadInventoryData();
                            }.bind(columnFilters), 500);
                        }
                        
                    })
                    .appendTo(header);
        }
    }

    function loadInventoryData(){
        if (!all_data_loaded) {
            onDataLoading.notify();
            currentRequest = $.ajax({
                type: 'POST',
                dataType: "json",
                url: url,
                data: {
                    limit: limit,
                    offset: offset,
                    filters: search_params
                },
                beforeSend : function()    {           
                    if(currentRequest != null) {
                        currentRequest.abort();
                    }
                },
                success: function(data) {
                    onDataLoaded.notify(data);
                },
                error:function(e){
                  console.log(e.responseText)
                }
            });
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

            //---- on scroll pulling data from ajax call ----------
            grid.onViewportChanged.subscribe(function (e, args) {
                var vp = grid.getViewport();
                console.log('bottom-', vp.bottom, ', offset-', offset, ', limit-', limit, ', total_data-', total_data, ', total_data/1.3-', Math.round(total_data/1.3));

                if((Math.round(total_data/1.3) <= vp.bottom) && !all_data_loaded){
                    total_data += limit;
                    offset += limit;

                    console.log('data pull request');
                    loadInventoryData();
                }
            });

            onDataLoading.subscribe(function () {
                if (!loadingIndicator) {

                    var $g = $("#inventoryGrid");
                    loadingIndicator = $("<div class='loader'><img src='<?php echo base_url();?>assets/style/img/loading.gif' style='vertical-align:sub'> Loading inventories. This may take few seconds.</div>").appendTo($g);

                    loadingIndicator
                        .css({"position": "absolute", "text-align": "center", "border-radius": "10px", "z-index": "1000", "color": "#fff", "cursor": "wait", "background": "#00000099","padding": "20px 15px"})
                        .css("bottom", $g.position().top - 60 + $g.height() / 2 )
                        .css("left", $g.position().left + $g.width() / 2 - loadingIndicator.width() / 2)
                        ;
                }
                loadingIndicator.show();
            });

            onDataLoaded.subscribe(function (e, data) {
                if(data == 'session_expired'){
                    window.location.reload();
                }

                if(data && data.hasOwnProperty("inventory") && data.inventory){
                    let replace_view = false;

                    if(data.hasOwnProperty("total_data")){
                        if(data.total_data < limit){
                            total_data = total_data - limit + data.total_data;
                        }

                        if(data.total_data == 0){
                            replace_view = true;
                        }

                        if(total_data < (offset + limit)){
                            all_data_loaded = true;
                        }
                    }

                    if(offset == 0){
                        replace_view = true;
                    } else{
                        replace_view = false;
                    }

                    dataView.beginUpdate();

                    // Updating inventory
                    var inventoryItems = data.inventory;
                    for (var i = 0, l = inventoryItems.length; i < l; i++) {
                        updateInventoryDetails(inventoryItems[i]);

                        if(!replace_view) dataView.addItem(inventoryItems[i]);
                    }
                    if(replace_view){
                        dataView.setItems(inventoryItems, 'id');
                    }
                    dataView.endUpdate();
                    dataView.refresh();
                    grid.invalidate();
                }
                if(loadingIndicator){
                    loadingIndicator.fadeOut();
                    loadingIndicator = null;
                }
            });

            updateHeaderRow();
            resizing_header_js();
        }

         //---- pulling initial data from ajax call ----------
        if(offset == 0){
            total_data = limit;
            loadInventoryData();
        }
        //---- pulling initial data from ajax call end ----------
            
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

