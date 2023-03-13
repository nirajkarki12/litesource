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



<div id="inventoryGrid" name="inventoryGrid" style="height:300px;"></div>


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
    function buttonFormatter(row, cell, value, columnDef, dataContext) {
        var button = "<button type='button' onclick='deleteAction(" + dataContext.id + ")'>Delete</button>";
        //the id is so that you can identify the row when the particular button is clicked
        return button;
        //Now the row will display your button
    }
    
    CatCellEditor = function (args) {
        var $input;
        var defaultValue;
        var scope = this;

        this.init = function () {


            $input = $("<INPUT type=text id='inventory' class='editor-text' />")
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
                        source: all_inventory_items

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




    var suppliers;
    var suppliersById = {};

    var statii;
    var statusById = {};

    var projects;
    var projectsById = {};

    var dateField = 'e';
    var orderNumField = 'n';
    var sortCol = orderNumField;
    var sortDir = 1;

    var grid;
    var dataView;

    var all_data_loaded = true;
    var inventory_items = <?=$inventory_items?>;
    var all_inventory_items;
    var selectedInventoryIdx = 1;

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


    var columns = [
        {id: "inventory_id", name: "ID", field: "id", sortField: "id", width: 40, sortable: true, fieldLink: "id",
            linkUrl: "<?php echo site_url('inventory/form/inventory_id/'); ?>", asyncPostRender: asyncRenderItemLink},
        {id: "name", name: "Parent Product Cat #", field: "name", sortField: "name", width: 220, sortable: true, fieldLink: "id",
            linkUrl: "<?php echo site_url('inventory/form/inventory_id/'); ?>", asyncPostRender: asyncRenderItemLink, editor: CatCellEditor},
        {id: "supplier_name", name: "Supplier", field: "supplier_name", fieldLink: "supplier_id",
            linkUrl: "<?php echo site_url('clients/details/client_id/'); ?>", width: 150, sortable: true, asyncPostRender: asyncRenderItemLink},
//        {id: "description", name: "Description", field: "description", sortField: "", width: 150, sortable: false},
//        {id: "supplier_code", name: "Supplier Cat. #", field: "supplier_code", sortField: "", width: 150, sortable: false},
//        {id: "supplier_description", name: "Supplier Order Description", field: "supplier_description", sortField: "", width: 150, sortable: false},
        // {id: "qty", name: "Total Inventory Qty", field: "qty", sortField: "", width: 90, sortable: true},
        {id: "inventory_qty", name: "Inventory Qty Used", field: "inventory_qty", sortField: "", width: 130, sortable: true},
//        {id: "base_price", name: "Base Price", field: "base_price", sortField: "", width: 150, sortable: true},
//        {id: "location", name: "Location", field: "location", sortField: "", width: 130, sortable: false},
        {id: "delCol", field: 'del', name: 'Action', width: 100, formatter: buttonFormatter},
    ];

    var options = {
        autoEdit: false,
        enableCellNavigation: true,
        enableColumnReorder: false,
        enableAsyncPostRender: true,
        showHeaderRow: false,
        editable: true
    };
    function deleteAction(product_id) {
        for (var i = 0, l = inventory_items.length; i < l; i++) {
            if (inventory_items[i]['id'] == product_id) {
                inventory_items.splice(i, 1);
                break;
            }
        }
        dataView.beginUpdate();
        dataView.setItems(inventory_items, 'id');
        dataView.endUpdate();
        
        var node = document.createElement("input");
        node.value = product_id;
        node.setAttribute("type", "hidden");
        node.setAttribute("name", "deleteProductInventory[]")
        var e = document.getElementById("hiddenDeletedProductInventory");
        e.appendChild(node);
        return false;


    }

    function updateInventoryDetails(inventoryItem,inventory_qty)
    {
        //console.log(inventoryItem);
        var dv = inventoryItem['e'] * 1000;
        var d = new Date(dv);

        inventoryItem['inventoryItem_date'] = $.datepicker.formatDate('dd/mm/yy', d);
        // inventoryItem['inventoryItem_status'] = statusById[inventoryItem['s']];

        var id = inventoryItem['supplier_id'];
        var idx = suppliersById[id];
        var supplier_name = getSupplierNameById(id);

        if (supplier_name == '') {
            inventoryItem['supplier_name'] = '(New)'
        } else {
            inventoryItem['supplier_name'] = supplier_name;
        }
        
        if(typeof inventory_qty != 'undefined'){
            inventoryItem['inventory_qty'] = (inventory_qty > '1') ? '1/' + inventory_qty : '1' ;
        }else{
            inventoryItem['inventory_qty'] = 0;
        }

    }
    
    function getSupplierNameById(id){
        var client_name = '';
        $.each(suppliers,function(key,obj){
            if(obj.client_id == id){
                client_name = obj.client_name;
            }
        })
        return client_name;
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


    $(document).ready(function () {

        if (grid === undefined) {

            dataView = new Slick.Data.DataView();


            grid = new Slick.Grid($("#inventoryGrid"), dataView, columns, options);
            //var pager = new Slick.Controls.Pager(dataView, grid, $("#pager"));
            grid.registerPlugin(new Slick.AutoTooltips());
            grid.setSelectionModel(new Slick.RowSelectionModel());
            //grid.setSortColumn("order_date", false );
            grid.setSortColumn("order_number", false);


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

        var inv_grid_data = <?=$sup_inv_json?>;
        if(inv_grid_data != null){
            
            dataView.beginUpdate();
            var inventory_name = [];
            all_inventory_items = inv_grid_data.inventory;
            for (var i = 0; i < all_inventory_items.length; i++) {
                inventory_name[i] = all_inventory_items[i]['name'];
            }
            suppliers = inv_grid_data.suppliers;
            // // Update supplier indexing
            for (var i = 0, l = suppliers.length; i < l; i++) {
                var id = suppliers[i]['id'];
                suppliersById[id] = i;
            }
            for (var i = 0, l = inventory_items.length; i < l; i++) {
                updateInventoryDetails(inventory_items[i],inventory_items[i].inventory_qty);
            }

            dataView.setItems(inventory_items, 'id');
            //dataView.sort(comparer, sortDir);
            dataView.endUpdate();
        }
    });

</script>
