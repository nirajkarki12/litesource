<style>
	.slick-headerrow-column.ui-state-default{
        padding: 0 3px !important;
    }
    .slick-headerrow-column input{
        border-width: 3px;
    }
    .editColumn{
    	cursor: pointer;
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


<div id="housingGrid" style="width: 100%;height: 500px; overflow: hidden;outline: 0px;position: relative;"></div>
<div class="loader" style="position: absolute;
    top: 40%;
    left: 0;
    width: 100%;
    text-align: center;">
    <img src="<?php echo base_url() . 'assets/style/img/loading.gif'; ?>"> Loading data. This may take few seconds.
</div>

<script type="text/javascript">
var grid;
var dataView;

var sortCol = 'product_name';
var sortDir = 1;
var columnFilters = {};

var columns = [

	{id:"product_name", name:"<?php echo $this->lang->line('product_name'); ?>", field:"product_name", fieldLink:"product_id",
		linkUrl: "<?php echo site_url('inventory/form/inventory_id/'); ?>",
		width:200, minWidth:150,sortable:true, asyncPostRender: asyncRenderItemLink},
	{id:"link_products", name:"<?php echo $this->lang->line('housing_label'); ?>", field:"link_products", width:700, minWidth:400, sortable:true},
	{id:"notes", name:"<?php echo $this->lang->line('notes'); ?>", field:"notes", sortable:true, width:170, minWidth:150},
	{id:"selector", name:"<?php echo $this->lang->line('actions'); ?>", formatter: showEditIcon, width:100, minWidth:100},
];

var options = {
	editable: false,
	autoEdit: false,
	enableCellNavigation: true,
	enableColumnReorder: false,
	enableAsyncPostRender: true,
	showHeaderRow: true

};

function showEditIcon(field) {
    return '<span class="editColumn"><?php echo icon("edit");?></span>';
}

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


		grid = new Slick.Grid($("#housingGrid"), dataView, columns, options);
		grid.registerPlugin(new Slick.AutoTooltips());
		grid.setSelectionModel(new Slick.RowSelectionModel());
		grid.setSortColumn("product_name", true );

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

		grid.onClick.subscribe(function(e, args) {
			if(args.cell == '3'){
				var item = dataView.getItem(args.row);
				window.location.href = "<?php echo site_url('housing/form/housing_id'); ?>/" + item.housing_id;
			}
		});

		updateHeaderRow();
        resizing_header_js();
	}



	$.post("<?php echo site_url('housing/get_housing_JSON'); ?>",{

	}, function(data) {
        if(data == 'session_expired'){
            window.location.reload();
        }
		dataView.beginUpdate();

		var housings = data.housings;

		dataView.setItems(housings, 'housing_id');

		dataView.setFilter(filter);
		dataView.endUpdate();
        $('.loader').hide();

	}, "json");
});

</script>