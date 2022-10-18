<style>
    body, html {
        margin: 0;
        padding: 0;
        overflow:scroll;
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
<script type="text/javascript" src="<?php echo base_url(); ?>assets/jquery/jquery.autogrow-textarea.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/slick/slick.core.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/slick/plugins/slick.autotooltips.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/slick/plugins/slick.rowselectionmodel.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/slick/slick.editors.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/slick/slick.grid.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/slick/slick.dataview.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/slick/json2.js"></script>



<div id="inventoryHistoryGrid" style="width:100%; height:300px; max-height:330px"></div>


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

    var grid;
    var dataView;

    var all_data_loaded = true;

    var columnFilters = {};



    var columns = [
//    {id:"inventory_history_id", name:"History Id", field:"id",sortField:"id",width:120,sortable:true},
        {id: "history_qty", name: "Quantity", field: "history_qty", sortField: "", width: 120, sortable: true},
        {id: "notes", name: "Notes", field: "notes", sortField: "", width: 350, sortable: true},
        {id: "user_id", name: "User", field: "user_name", sortField: "", width: 120, sortable: true},
        {id: "created_at", name: "Date", field: "created_at", sortField: "", width: 150, sortable: true}


    ];

    var options2 = {
        autoEdit: true,
        enableCellNavigation: true,
        enableColumnReorder: true,
        enableAsyncPostRender: true,
        showHeaderRow: false
    };
    $(document).ready(function () {

        if (grid === undefined) {

            dataView = new Slick.Data.DataView();


            grid = new Slick.Grid($("#inventoryHistoryGrid"), dataView, columns, options2);
            grid.registerPlugin(new Slick.AutoTooltips());
            grid.setSelectionModel(new Slick.RowSelectionModel());


            dataView.onRowCountChanged.subscribe(function (e, args) {
                grid.updateRowCount();
                grid.render();
            });

            dataView.onRowsChanged.subscribe(function (e, args) {
                grid.invalidateRows(args.rows);
                grid.render();

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
        }

        var inventoryHistory = <?php echo $inventory_history; ?>;
        if(inventoryHistory != 'undefined'){
            dataView.beginUpdate();
            var users = <?php echo $users; ?>;
            //var inventoryItems = data.inventory;
            for (var i = 0; i < inventoryHistory.length; i++) {
                inventoryHistory[i].user_name = "";
                for (var j = 0; j < users.length; j++) {
                    if (users[j].uid == inventoryHistory[i].user_id) {
                        inventoryHistory[i].user_name = users[j].username;
                    }
                }
            }
            dataView.setItems(inventoryHistory, 'id');
            var height = inventoryHistory.length * 25 + 45;
            //Set height Inventory history Grid
            document.getElementById("inventoryHistoryGrid").style.height = height.toString() + "px";
            dataView.endUpdate();
        }
    });

</script>

