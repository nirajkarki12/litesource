<div class="mygridwrap">
    <div id="myGrid" style="width:600px;height:500px;"></div>
    <div class="loader">
        <img src="<?php echo base_url().'assets/style/img/loading.gif'; ?>" />
    </div>
</div>
<script>
    var grid2 = [];
    var selectedPds = [];
    var columns2 = [
        {id: "supplier_name", name: "Supplier Name", field: "supplier_name", sortField: "supplier_name", width: parseFloat((typeof $.cookie('inventory_grid_id') != 'undefined') ? $.cookie('inventory_grid_id') : '120'), sortable: true, fieldLink: "id",
            linkUrl: "<?php echo site_url('clients/details/client_id/'); ?>", asyncPostRender: asyncRenderSupplierLink},
        {id: "name", name: "Product Name", field: "n", sortField: "n", width: parseFloat((typeof $.cookie('inventory_grid_name') != 'undefined') ? $.cookie('inventory_grid_name') : '432'), sortable: true, fieldLink: "id", editor: TextCellEditor,
            linkUrl: "<?php echo site_url('inventory/form/inventory_id/'); ?>", asyncPostRender: asyncRenderItemLinkProd},
    ];

    var options2 = {
        autoEdit: false,
        enableCellNavigation: true,
        enableColumnReorder: false,
        enableAsyncPostRender: true,
        showHeaderRow: true,
        editable: false
    };

    var columnFilters2 = {};

    var checkboxSelector2 = new Slick.CheckboxSelectColumn({
        cssClass: "chkbox_prod",
    });
    
    columns2.unshift(checkboxSelector2.getColumnDefinition());
    
    function asyncRenderItemLinkProd(cellNode, row, dataContext, colDef)
    {
        var f = colDef.field;
        var fl = colDef.fieldLink;

        if (dataContext[f] == null)
            return;
        
        var icon = '';
        if(dataContext['inventorycount'] > 0){
            icon = '<a data-name="'+dataContext['n']+'" href="<?php echo site_url('housing/gethousingpop') ?>?id='+dataContext['id']+'" data-effect="mfp-zoom-in" data-message="" class="inv-detail open-popup"><span class="info-link"></span></a>';
        }
        
        var a = '<a href="' + colDef.linkUrl + '/' + dataContext[fl] + '">' + dataContext[f] + '</a>'+icon;
        $(cellNode).html(a);
    }

    function filter2(item)
    {
        var res = true;
        for (var columnId in columnFilters2) {
            var cf = columnFilters2[columnId];
            if (res && cf !== undefined) {
                var c = grid2.getColumns()[grid2.getColumnIndex(columnId)];
                res = cf.test(item[c.field]);
            }
        }
        return res;
    }

    function updateHeaderRow2() {

        for (var i = 0; i < columns2.length; i++) {
            
            if (columns2[i].id !== "selector") {
                var header = grid2.getHeaderRowColumn(columns2[i].id);
                //var w = $(header).width() - 4;
                var w = columns2[i].width - 16;
                $(header).empty();
                $("<input type='text'>")
                        .attr("placeholder", columns2[i].name)
                        .data("columnId", columns2[i].id)
                        .width(w)

                        .keyup(function (e) {

                            var val = $.trim($(this).val());

                            if (val == '')
                                columnFilters2[$(this).data("columnId")] = undefined;
                            else {
                                columnFilters2[$(this).data("columnId")] = new RegExp(val, 'i');

                            }

                            applyFilter2();

                        })

                        .appendTo(header);
            }
        }

    }

    function applyFilter2() {
        dataView2.refresh();
    }

    $(document).ready(function () {
        
        dataView2 = new Slick.Data.DataView();
        grid2 = new Slick.Grid($("#myGrid"), dataView2, columns2, options2);
        grid2.setSelectionModel(new Slick.RowSelectionModel({selectActiveRow: false}));
        grid2.registerPlugin(checkboxSelector2);
        //var columnpicker2 = new Slick.Controls.ColumnPicker(columns2, grid2, options2);
        
        grid2.onSelectedRowsChanged.subscribe(function() {
            var rsels = grid2.getSelectedRows();
            selectedPds = [];
            for(var k = 0; k< rsels.length; k++){
                var item = dataView2.getItem(rsels[k]);
                if(typeof item != 'undefined'){
                    selectedPds.push(item.id);
                }
            }
        });
        
        $.post("<?php echo site_url('housing/get_all_housing_inventory'); ?>", {
        }, function (data) {
            if (data == 'session_expired') {
                window.location.reload();
            }
            var inventory = data.inventory;
            dataView2.setItems(inventory, 'id');
            dataView2.setFilter(filter2);
        }, "json");
        
        dataView2.onRowCountChanged.subscribe(function (e, args) {
            grid2.updateRowCount();
            grid2.render();
        });
        updateHeaderRow2();
        resizing_header_linktoproduct1_js();
        dataView2.onRowsChanged.subscribe(function (e, args) {
            grid2.invalidateRows(args.rows);
            grid2.render();
        });
        
        grid2.onSort.subscribe(function (e, args) {
            sortDir = args.sortAsc ? 1 : -1;
            if (args.sortCol.sortField == undefined)
                sortCol2 = args.sortCol2.field;
            else
                sortCol2 = args.sortCol.sortField;
            // using native sort with comparer
            // preferred method but can be very slow in IE with huge datasets
            dataView2.sort(comparer, args.sortAsc);
            grid2.setSelectedRows([]);
        });
        
        $('#myGrid .slick-headerrow-column input').on('keyup',function(){
            grid2.setSelectedRows([]);
        });
    });


</script>