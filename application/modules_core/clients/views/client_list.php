<style>

	.slick-headerrow-columns {
		height: 32px;
	}


	.slick-headerrow-column input {
		margin: 0;
		padding: 2;
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


<div id="clientGrid" style="width:100%;height:500px;"></div>


<script type="text/javascript">

<?php
global $CI;
echo "var currencySymbol = '". $CI->mdl_mcb_data->setting('currency_symbol') ."';";
?>



var taxRates;
var taxRatesById = {};

var grid;
var dataView;

var sortCol = 'n';
var sortDir = 1;
var columnFilters = {};

var columns = [


	{id:"client_name", name:"<?php echo $this->lang->line('name'); ?>", field:"n", fieldLink:"id",
		linkUrl: "<?php echo site_url('clients/details/client_id'); ?>",
		width:390, minWidth:150, sortable:true, asyncPostRender: asyncRenderItemLink},
	{id:"client_group_name", name:"<?php echo $this->lang->line('client_group_name'); ?>", field:"gn",
		linkUrl: "<?php echo site_url('clients/details/client_id'); ?>",
		width:100, minWidth:100, sortable:true},
	{id:"client_currency", name:"Cur", field:"cur", width:40, minWidth:40, sortable:true},
	{id:"tax_rate_name", name:"<?php echo $this->lang->line('tax_rate'); ?>", field:"tax_rate_name", width:80, minWidth:80, sortable:true},
	{id:"client_is_supplier", name:"Is Supplier", field:"cis", width:90, minWidth:90, sortable:true, formatter: checkmarkFormatter},
	{id:"parent_name", name:"Parent", field:"pn", width:110, minWidth:110, sortable:true},
    {id:"total_due", name:"Total Due", field:"dp", width:150, minWidth:110, sortable:true, cssClass:"cell-right-align", formatter: PriceCellFormatter},
//    {id:"total_inv_due", name:"Total Invoice Due", field:"t", width:150, minWidth:110, sortable:true, cssClass:"cell-right-align", formatter: PriceCellFormatter},

];

var options = {
	editable: false,
	autoEdit: false,
	enableCellNavigation: true,
	enableColumnReorder: false,
	enableAsyncPostRender: true,
	showHeaderRow: true

};

function comparer(a, b) {
	var x = a[sortcol], y = b[sortcol];

	// compare by name if all else values are the same
	if (x == y) {
		x = a['n'];
		y = b['n'];
	}

	return (x == y ? 0 : (x > y ? 1 : -1));
}



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

function checkmarkFormatter(row, cell, value, columnDef, dataContext) {
	return value==1 ? "<img src='<?php echo base_url(); ?>/assets/slick/images/tick.png'>" : "";
}

function PriceCellFormatter(row, cell, value, columnDef, dataContext) {

    return value ? currencySymbol + value.toFixed(2) : "";
};

function update_client_details(client)
{

	var idx = taxRatesById[client['tid']];
	var taxRate = taxRates[idx];

    client['t']= 0.00 + (+client['t']);
	client['tax_rate_name'] = taxRate['tax_rate_name'];

}

function asyncRenderItemLink(cellNode, row, dataContext, colDef)
{
	var f = colDef.field;
	var fl = colDef.fieldLink;

	if (dataContext[f] == null)
		return;

	var a = '<a href="' + colDef.linkUrl + '/' + dataContext[fl] + '">'+ dataContext[f] + '</a>';

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

					dataView.refresh();

				})

				.appendTo(header);
		}
	}

}

$(document).ready(function(){

	if (grid === undefined) {

		dataView = new Slick.Data.DataView();


		grid = new Slick.Grid($("#clientGrid"), dataView, columns, options);
		grid.registerPlugin(new Slick.AutoTooltips());
		grid.setSelectionModel(new Slick.RowSelectionModel());
		grid.setSortColumn("client_name", true );

		dataView.onRowCountChanged.subscribe(function(e,args) {
			grid.updateRowCount();
			grid.render();
		});

		dataView.onRowsChanged.subscribe(function(e,args) {
			grid.invalidateRows(args.rows);
			grid.render();

		});

		grid.onSort.subscribe(function(e,args) {
			sortdir = args.sortAsc ? 1 : -1;
			sortcol = args.sortCol.field;


			// using native sort with comparer
			// preferred method but can be very slow in IE with huge datasets
			dataView.sort(comparer, args.sortAsc);

		});

		updateHeaderRow();

	}



	$.post("<?php echo site_url('clients/get_clients_JSON'); ?>",{

	}, function(data) {
            if(data == 'session_expired'){
                            window.location.reload();
                        }
		dataView.beginUpdate();

		taxRates = data.tax_rates;
		var clients = data.clients;

		// Update client group indexing
		for (var i = 0, l = taxRates.length; i < l; i++) {
			var id = taxRates[i]['tax_rate_id'];
			taxRatesById[id] = i;
		}


		for (var i = 0, l = clients.length; i < l; i++) {
			update_client_details(clients[i]);
		}


		dataView.setItems(clients, 'id');


		dataView.setFilter(filter);
		dataView.endUpdate();


	}, "json");



});

</script>

