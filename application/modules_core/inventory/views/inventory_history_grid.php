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

    let site_url = "<?=  site_url();?>";
    function notesFormatter(row, cell, value, columnDef, dataContext) {
        let notes = dataContext.notes;
        if(notes != ''){
            notes = notes.replace("_SITE_URL_", site_url);
        }
        return notes;
        //Now the row will display your notes
    }

    var history_grid;
    var history_dataView;

    var history_columns = [
//    {id:"inventory_history_id", name:"History Id", field:"id",sortField:"id",width:120,sortable:true},
        {id: "history_qty", name: "Quantity", field: "history_qty", sortField: "", width: 120, sortable: true},
        {id: "notes", name: "Notes", field: "notes", sortField: "", width: 400, sortable: true, formatter: notesFormatter},
        {id: "user_id", name: "User", field: "user_name", sortField: "", width: 120, sortable: true},
        {id: "created_at", name: "Date", field: "created_at", sortField: "", width: 170, sortable: true}


    ];

    var options2 = {
        autoEdit: true,
        enableCellNavigation: true,
        enableColumnReorder: true,
        enableAsyncPostRender: true,
        showHeaderRow: false
    };
    $(document).ready(function () {

        if (history_grid === undefined) {

            history_dataView = new Slick.Data.DataView();


            history_grid = new Slick.Grid($("#inventoryHistoryGrid"), history_dataView, history_columns, options2);
            history_grid.registerPlugin(new Slick.AutoTooltips());
            history_grid.setSelectionModel(new Slick.RowSelectionModel());


            history_dataView.onRowCountChanged.subscribe(function (e, args) {
                history_grid.updateRowCount();
                history_grid.render();
            });

            history_dataView.onRowsChanged.subscribe(function (e, args) {
                history_grid.invalidateRows(args.rows);
                history_grid.render();

            });

            history_grid.onSort.subscribe(function (e, args) {
                sortDir = args.sortAsc ? 1 : -1;

                if (args.sortCol.sortField == undefined)
                    sortCol = args.sortCol.field;
                else
                    sortCol = args.sortCol.sortField;
                // using native sort with comparer
                // preferred method but can be very slow in IE with huge datasets
                history_dataView.sort(comparer, args.sortAsc);
            });
        }

        var inventoryHistory = <?php echo isset($inventory_history) ? $inventory_history : 'undefined'; ?>;
        if(inventoryHistory != 'undefined' && (typeof inventoryHistory != 'undefined')){
            history_dataView.beginUpdate();
            var users = <?php echo isset($users) ? $users : 'undefined'; ?>;
            //var inventoryItems = data.inventory;
            for (var i = 0; i < inventoryHistory.length; i++) {
                inventoryHistory[i].user_name = "";
                for (var j = 0; j < users.length; j++) {
                    if (users[j].uid == inventoryHistory[i].user_id) {
                        inventoryHistory[i].user_name = users[j].username;
                    }
                }
            }
            history_dataView.setItems(inventoryHistory, 'id');
            var height = inventoryHistory.length * 25 + 45;
            //Set height Inventory history Grid
            document.getElementById("inventoryHistoryGrid").style.height = height.toString() + "px";
            history_dataView.endUpdate();
        } else{
            $(document).find('#inv_history').html('');
        }
    });

</script>

