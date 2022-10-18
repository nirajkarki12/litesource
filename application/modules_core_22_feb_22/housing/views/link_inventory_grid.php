<div class="inventoryGridwrap">
    <div id="inventoryGrid" style="width:100%;height:500px;padding-right: 10px;"></div>
    <div class="loader">
        <img src="<?php echo base_url() . 'assets/style/img/loading.gif'; ?>">
    </div>
</div>
<script type="text/javascript">
    var selectedInvs = [];

    var grid;
    var dataView;

    var orderNumField = 'n';
    var sortCol = orderNumField;
    var sortDir = 1;

    var grid;
    var dataView;
    
    var all_data_loaded = true;

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
        {id: "supplier_name", name: "Supplier Name", field: "supplier_name", sortField: "supplier_name", width: parseFloat((typeof $.cookie('inventory_grid_id') != 'undefined') ? $.cookie('inventory_grid_id') : '120'), sortable: true, fieldLink: "id",
            linkUrl: "<?php echo site_url('clients/details/client_id/'); ?>", asyncPostRender: asyncRenderSupplierLink},
        {id: "name", name: "Housing Products", field: "n", sortField: "n", width: parseFloat((typeof $.cookie('inventory_grid_name') != 'undefined') ? $.cookie('inventory_grid_name') : '432'), sortable: true, fieldLink: "id",
            linkUrl: "<?php echo site_url('inventory/form/inventory_id/'); ?>", asyncPostRender: asyncRenderItemLink},
    ];
    var checkboxSelector = new Slick.CheckboxSelectColumn({
        cssClass: "chkbox_inv"
    });
    columns.push(checkboxSelector.getColumnDefinition());
    var columnFilters = {};
    var options = {
        autoEdit: true,
        enableCellNavigation: true,
        enableColumnReorder: false,
        enableAsyncPostRender: true,
        showHeaderRow: true,
        editable: true
    };
    
    function filter(item){
        //grid.setSelectedRows([]);
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

    function asyncRenderSupplierLink(cellNode, row, dataContext, colDef) {
        var f = colDef.field;
        var fl = colDef.fieldLink;
        //console.log(dataContext);
        if (dataContext[f] == null)
            return;
        var a = '<a href="' + colDef.linkUrl + '/' + dataContext['s'] + '">' + dataContext[f] + '</a>';
        $(cellNode).html(a);
    }

    function asyncRenderItemLink(cellNode, row, dataContext, colDef) {
        var f = colDef.field;
        var fl = colDef.fieldLink;
        if (dataContext[f] == null)
            return;
        var a = '<a href="' + colDef.linkUrl + '/' + dataContext[fl] + '">' + dataContext[f] + '</a>';
        $(cellNode).html(a);
    }

    function updateHeaderRow() {

        for (var i = 0; i < columns.length; i++) {
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
    
    function applyFilter() {
        dataView.refresh();
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

            grid.onSelectedRowsChanged.subscribe(function () {
                selectedInvs = [];
                var rsels = grid.getSelectedRows();
                for (var k = 0; k < rsels.length; k++) {
                    var item = dataView.getItem(rsels[k]);
                    if (typeof item != 'undefined') {
                        selectedInvs.push(item['id']);
                    }
                }
            });

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
                    var cookie = $.cookie('inventory_grid_' + column.field, grid_col_width);
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
                grid.setSelectedRows([]);
            });
            updateHeaderRow();
        }

        $.post("<?php echo site_url('housing/get_all_inventory'); ?>", {
        }, function (data) {
            if (data == 'session_expired') {
                window.location.reload();
            }
            dataView.beginUpdate();
            var inventoryItems = data.inventory;
            dataView.setItems(inventoryItems, 'id');
            dataView.setFilter(filter);
            //dataView.sort(comparer, sortDir);
            dataView.endUpdate();
        }, "json");
        $('#inventoryGrid .slick-headerrow-column input').on('keyup', function () {
            grid.setSelectedRows([]);
        });
    });

</script>

