<div class="inventoryGridwrap">
    <div id="inventoryGrid" style="width:100%;height:500px;padding-right: 10px;"></div>
    <div class="loader">
        <img src="<?php echo base_url().'assets/style/img/loading.gif'; ?>">
    </div>
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

    function comparer(a, b) {
        var x = a[sortCol], y = b[sortCol];

        // compare by order id if other values the same unless
        // already sorting by date in which case secondary sort by id
        if (x == y) {

            x = a.id
            y = b.id;

            // always do secondary sort in descending order
            return (x == y ? 0 : (x > y ? -sortDir : sortDir));

        } else
            return (x > y ? 1 : -1);

    }


    var data = [];
    var columns = [];


    var columns = [
        {id: "inventory_name", name: "Inventory Name", field: "inv", sortField: "inventory_name", width: parseFloat((typeof $.cookie('inventory_name') != 'undefined') ? $.cookie('inv_prod_inventory_name') : '390'), sortable: true, fieldLink: "ii",
            linkUrl: "<?php echo site_url('inventory/form/inventory_id/'); ?>", asyncPostRender: asyncRenderInventoryLink},
        {id: "qty", name: "QTY", field: "qty", sortField: "qty", width: parseFloat((typeof $.cookie('qty') != 'undefined') ? $.cookie('inv_prod_qty') : '160'), sortable: true, editor: TextCellEditor},
        {id: "product_name", name: "Product Name", field: "pn", sortField: "product_name", width: parseFloat((typeof $.cookie('inventory_name') != 'undefined') ? $.cookie('inv_prod_product_name') : '390'), sortable: true, fieldLink: "pi",
            linkUrl: "<?php echo site_url('products/form/product_id/'); ?>", asyncPostRender: asyncRenderProductLink},
        
    ];

    
    var checkboxSelector = new Slick.CheckboxSelectColumn({
        cssClass: "chkbox_inv"
    });
    columns.unshift(checkboxSelector.getColumnDefinition());
    

    var columnFilters = {};

    var options = {
        autoEdit: true,
        enableCellNavigation: true,
        enableColumnReorder: false,
        enableAsyncPostRender: true,
        showHeaderRow: true,
        editable: true
    };
    
    function qtyFormatter(row, cell, value, columnDef, dataContext)
    {
        if(value == null){
            return '1';
        }
        return value;
        
    }


    function updateInventoryDetails(inventoryItem)
    {

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
    
    function asyncRenderProductLink(cellNode, row, dataContext, colDef){
        var f = colDef.field;
        var fl = colDef.fieldLink;
        
        //console.log(dataContext);
        
        if (dataContext[f] == null)
            return;
        
        var name = dataContext[f];
        if(dataContext['pcnt'] > 0){
            name = '<strong>'+dataContext[f]+'</strong>';
        }

        var a = '<a href="' + colDef.linkUrl + '/' + dataContext['pi'] + '">' + name + '</a>';

        $(cellNode).html(a);
    }
    
    function asyncRenderInventoryLink(cellNode, row, dataContext, colDef){
        var f = colDef.field;
        var fl = colDef.fieldLink;
        
        //console.log(dataContext);
        
        if (dataContext[f] == null)
            return;
        
        var name = dataContext[f];
        if(dataContext['icnt'] > 0){
            name = '<strong>'+dataContext[f]+'</strong>';
        }
        
        var a = '<a href="' + colDef.linkUrl + '/' + dataContext['ii'] + '">' + name + '</a>';

        $(cellNode).html(a);
    }
    
    function asyncRenderSupplierLink(cellNode, row, dataContext, colDef){
        var f = colDef.field;
        var fl = colDef.fieldLink;
        
        //console.log(dataContext);
        
        if (dataContext[f] == null)
            return;

        var a = '<a href="' + colDef.linkUrl + '/' + dataContext['supplier_id'] + '">' + dataContext[f] + '</a>';

        $(cellNode).html(a);
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

            $.post("<?php echo site_url('orders/get_orders_only_JSON'); ?>", {
                limit: 10000,
                offset: 0

            }, function (data) {
                if (data == 'session_expired') {
                    window.location.reload();
                }
                dataView.beginUpdate();


                // var orders = data.orders;
                //
                // for (var i = 0, l = orders.length; i < l; i++) {
                //     updateOrderDetails(orders[i]);
                // }
                //
                // dataView.setItems(orders, 'id');

                dataView.endUpdate();


            }, "json");
        } else {
            dataView.refresh();
        }


    }


    function updateHeaderRow() {

        for (var i = 0; i < columns.length; i++) {
            if (columns[i].name == "<input type='checkbox' class='inventory_checkbox'>") {
                return false;
            }
            if (columns[i].id !== "selector") {
                var header = grid.getHeaderRowColumn(columns[i].id);
                //var w = $(header).width() - 4;
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
            //var columnpicker = new Slick.Controls.ColumnPicker(columns, grid, options);

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
                //var column = grid.getColumns()[args.cell];

                //update_inventory(args.item, column.field);
            });


            grid.onColumnsResized.subscribe(function (e, args) {
                for (var i = 0, totI = grid.getColumns().length; i < totI; i++) {
                    var column = grid.getColumns()[i];
                    var grid_col_width = column.width;
                    var cookie = $.cookie('inv_prod_' + column.field, grid_col_width);

                }

            });

            grid.onSort.subscribe(function (e, args) {
                sortDir = args.sortAsc ? 1 : -1;

                if (args.sortCol.sortField == undefined)
                    sortCol = args.sortCol.field;
                else
                    sortCol = args.sortCol.sortField;


                // using native sort with comparer
                // preferred method but can be very slow in IE with huge datasets
                dataView.sort(comparer, args.sortAsc);

            });
            
            updateHeaderRow();
        }


        
    });

</script>