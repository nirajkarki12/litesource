<div id="productGrid" style="width:100%;height:500px;padding-right: 10px;"></div>

<script type="text/javascript">


StatusCellFormatter = function(row, cell, value, columnDef, dataContext) {
    return '<span class="status_' + dataContext['s'] + '">' + value + '</span>';
    
}

PriceCellFormatter = function(row, cell, value, columnDef, dataContext) {
    return currencySymbol + value;
};

SupplierPriceCellFormatter = function(row, cell, value, columnDef, dataContext) {
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

var prodgrid;
var dataView;

var dateField = 'e';
var orderNumField = 'n';
var sortCol = orderNumField;
var sortDir = 1;

var all_data_loaded = true;

SupplierCellEditor = function(args) {
		var $input;
		var defaultValue;
		var scope = this;

		this.init = function() {


			$input = $("<INPUT type=text id='supplier' class='editor-text' />")
				.appendTo(args.container)
				.bind("keydown.nav", function(e) {
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
				.bind("keydown.nav", function(e) {
					if (e.keyCode === $.ui.keyCode.DOWN || e.keyCode === $.ui.keyCode.UP) {
						e.stopImmediatePropagation();
					}
				});


	    };

		this.destroy = function() {
			$input.remove();
		};

		this.focus = function() {
			$input.focus();
		};

		this.getValue = function() {
			return $input.val();
		};

		this.setValue = function(val) {
			$input.val(val);
		};

		this.loadValue = function(item) {
			defaultValue = item[args.column.field] || "";
			$input.val(defaultValue);
			$input[0].defaultValue = defaultValue;
			$input.select();
		};

		this.serializeValue = function() {
			return $input.val();
		};

		this.applyValue = function(item,state) {
			item[args.column.field] = state;

			//item['client_id'] =

		};

		this.isValueChanged = function() {
			return (!($input.val() == "" && defaultValue == null)) && ($input.val() != defaultValue);
		};

		this.validate = function() {
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

function Prodcomparer(a, b) {
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


var columns= [
    {id:"inventory_id", name:"Id", field:"id",sortField:"id",width:parseFloat((typeof $.cookie('inventory_grid_id') != 'undefined')?$.cookie('inventory_grid_id'):'40'),sortable:true,fieldLink :"id",
         linkUrl: "<?php echo site_url('inventory/form/inventory_id/') ;?>", asyncPostRender : asyncRenderItemLink},
    {id:"name", name:"Inventory Name", field:"name",sortField:"name",width:parseFloat((typeof $.cookie('inventory_grid_name') != 'undefined')?$.cookie('inventory_grid_name'):'150'),sortable:true,fieldLink :"id", editor: TextCellEditor,
         linkUrl: "<?php echo site_url('inventory/form/inventory_id/') ;?>", asyncPostRender : asyncRenderItemLink},
    
//    {id:"supplier_description", name:"Supplier Description", field:"supplier_description",sortField:"",width:150,sortable:false,editor: TextCellEditor},
    {id:"qty", name:"Qty", field:"qty",sortField:"",width:parseFloat((typeof $.cookie('inventory_grid_qty') != 'undefined')?$.cookie('inventory_grid_qty'):'80'),sortable:true, editor: TextCellEditor},
    

];



var columnFilters = {};






var options = {
    autoEdit: true,
    enableCellNavigation: true,
    enableColumnReorder: false,
    enableAsyncPostRender: true,
    showHeaderRow: true,
    editable:true
};


function ProdupdateInventoryDetails(inventoryItem)
{

    var dv = inventoryItem['e']*1000;
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


	function Produpdate_inventory(item, field)
	{

		$.post("<?php echo site_url('inventory/ajax_update_inventory'); ?>",{

			post_item: JSON.stringify(item)

        }, function(data) {
            if(data == 'session_expired'){
                            window.location.reload();
                        }
            console.log(data);
		}, "json");

	};


function Prodfilter(item)
{
    var res = true;

    for (var columnId in columnFilters) {
        var cf = columnFilters[columnId];
        if (res && cf !== undefined) {
            var c = prodgrid.getColumns()[prodgrid.getColumnIndex(columnId)];

            res = cf.test(item[c.field]);

        }
    }


    return res;
}

function ProdasyncRenderItemLink(cellNode, row, dataContext, colDef)
{
    var f = colDef.field;
    var fl = colDef.fieldLink;

    if (dataContext[f] == null)
        return;

    var a = '<a href="' + colDef.linkUrl + '/' + dataContext[fl] + '">'+ dataContext[f] + '</a>';

    $(cellNode).html(a);

}

function ProdapplyFilter() {


    if (!all_data_loaded) {
        all_data_loaded = true;

        $.post("<?php echo site_url('orders/get_orders_only_JSON'); ?>",{

            limit: 10000,
            offset: 0

        }, function(data) {
            if(data == 'session_expired'){
                            window.location.reload();
                        }
            dataViewProd.beginUpdate();


            // var orders = data.orders;
            //
            // for (var i = 0, l = orders.length; i < l; i++) {
            //     updateOrderDetails(orders[i]);
            // }
            //
            // dataViewProd.setItems(orders, 'id');

            dataViewProd.endUpdate();


        }, "json");
    } else {
        dataViewProd.refresh();
    }


}


function updateHeaderRowProd() {


    for (var i = 0; i < columns.length; i++) {

        if (columns[i].id !== "selector") {
            var header = prodgrid.getHeaderRowColumn(columns[i].id);
            //var w = $(header).width() - 4;
            var w = columns[i].width - 16;
            $(header).empty();
            $("<input type='text'>")
                .attr("placeholder", columns[i].name)
                .data("columnId", columns[i].id)
                .width(w)

                .keyup( function(e) {

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

$(document).ready(function(){

    if (prodgrid === undefined) {

        dataViewProd = new Slick.Data.DataView();


        prodgrid = new Slick.Grid($("#productGrid"), dataView, columns, options);
        //var pager = new Slick.Controls.Pager(dataView, grid, $("#pager"));
        prodgrid.registerPlugin(new Slick.AutoTooltips());
        prodgrid.setSelectionModel(new Slick.RowSelectionModel());
        //prodgrid.setSortColumn("order_date", false );
        prodgrid.setSortColumn("order_number", false );


        dataViewProd.onRowCountChanged.subscribe(function(e,args) {
            prodgrid.updateRowCount();
            prodgrid.render();
        });

        dataViewProd.onRowsChanged.subscribe(function(e,args) {
            prodgrid.invalidateRows(args.rows);
            prodgrid.render();

        });
              	prodgrid.onCellChange.subscribe(function(e,args) {
				//dataViewProd.updateItem(args.item[dataViewProd.getIdProperty()], args.item);
				var column = prodgrid.getColumns()[args.cell];

				update_inventory(args.item, column.field);
			});
                        
                        
                        prodgrid.onColumnsResized.subscribe(function Prod(e, args) {
                            for(var i = 0, totI = prodgrid.getColumns().length; i < totI; i++){
                            var column = prodgrid.getColumns()[i];
                            var grid_col_width=column.width;
                            var cookie=$.cookie('inventory_grid_'+column.field, grid_col_width);
                            
                        }
     
                       });
                       
//                       if (typeof $.cookie('the_cookie') === 'undefined'){
//                           alert('no cookie');
//    }
//    else{
//     alert('cookie');   
//    }
                         
     
			
//                        
                        
        prodgrid.onSort.subscribe(function(e,args) {
            sortDir = args.sortAsc ? 1 : -1;

            if (args.sortCol.sortField == undefined)
                sortCol = args.sortCol.field;
            else
                sortCol = args.sortCol.sortField;


            // using native sort with comparer
            // preferred method but can be very slow in IE with huge datasets
            dataViewProd.sort(comparer, args.sortAsc);

        });

        $("#suppliers").change(function() {
            supplier_id_filter = $(this).val();
			dataViewProd.refresh();
		});

        updateHeaderRowProd();

    }


    $.post("<?php echo site_url('inventory/get_inventory_JSON'); ?>",{

    }, function(data) {
        if(data == 'session_expired'){
                            window.location.reload();
                        }
        dataViewProd.beginUpdate();


        suppliers = data.suppliers;
        // Update supplier indexiig

        for (var i = 0, l = suppliers.length; i < l; i++) {
            var id = suppliers[i]['supplier_id'];
            suppliersById[id] = i;
        }

        //
        // // Update project indexing
        // for (var i = 0, l = projects.length; i < l; i++) {
        //     var id = projects[i]['id'];
        //     projectsById[id] = i;
        // }

        var inventoryItems = data.inventory;
        for (var i = 0, l = inventoryItems.length; i < l; i++) {
            updateInventoryDetails(inventoryItems[i]);
        }

        dataViewProd.setItems(inventoryItems, 'id');
        dataViewProd.setFilter(filter);
        //dataViewProd.sort(comparer, sortDir);
        dataViewProd.endUpdate();


    }, "json");



});

</script>

