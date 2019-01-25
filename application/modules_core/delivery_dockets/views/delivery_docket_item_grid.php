<style>


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
<script type="text/javascript" src="<?php echo base_url(); ?>assets/slick/slick.core.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/slick/plugins/slick.autotooltips.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/slick/plugins/slick.rowselectionmodel.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/slick/slick.editors.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/slick/slick.grid.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/slick/slick.dataview.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/slick/json2.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/jquery/jquery.magnific-popup.min.js"></script>

<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/style/css/magnific-popup.css" />
<div id="itemGrid" style="width:1080px;height:500px;"></div>


<script type="text/javascript">

	QuantityCellFormatter = function(row, cell, value, columnDef, dataContext) {
            
            if( (dataContext.item_name == '') && (dataContext.item_description == '') ){
                return '';
            }else if(typeof value == 'undefined'){
                return '0';
            }else{
                return parseFloat(value);
            }
	};

	QuantityCellEditor = function(args)
	{
		var $input;
		var defaultValue;
		var scope = this;

		this.init = function() {
			$input = $("<INPUT type=text class='editor-text' />");

			$input.bind("keydown.nav", function(e) {
				if (e.keyCode === $.ui.keyCode.LEFT || e.keyCode === $.ui.keyCode.RIGHT) {
					e.stopImmediatePropagation();
				}
			});

			$input.appendTo(args.container);
			$input.focus().select();
		};

		this.destroy = function() {
			$input.remove();
		};

		this.focus = function() {
			$input.focus();
		};

		this.loadValue = function(item) {
			defaultValue = item[args.column.field];
			$input.val(defaultValue);
			$input[0].defaultValue = defaultValue;
			$input.select();
		};

		this.serializeValue = function() {
			return parseFloat($input.val()) || 0;
		};

		this.applyValue = function(item,state) {
			item[args.column.field] = state;
		};

		this.isValueChanged = function() {
			return (!($input.val() == "" && defaultValue == null)) && ($input.val() != defaultValue);
		};

		this.validate = function() {
			if (isNaN($input.val()))
				return {
					valid: false,
					msg: "Please enter a valid number"
				};

			return {
				valid: true,
				msg: null
			};
		};

		this.init();
	};

var grid;
var dataView;

var sortCol = 'n';
var sortDir = 1;
var columnFilters = {};

var columns = [

	{id:"item_name",
		behavior: "move",
		width:220, minWidth:220, name:"<?php echo $this->lang->line('catalog_number'); ?>", field:"item_name", sortable:true},

	{id:"item_type", width:200, name:"<?php echo $this->lang->line('item_type'); ?>", field:"item_type", sortable:true},
	{id:"item_description", width:350, minWidth:350, name:"<?php echo $this->lang->line('item_description'); ?>", field:"item_description"},

	{id:"item_qty", width:80, name:"<?php echo $this->lang->line('qty_ordered'); ?>", field:"item_qty", sortable:true, sortNumeric:true, cssClass:"cell-right-align", formatter:QuantityCellFormatter},
        <?php if($first_docket == FALSE): ?>
        {id:"already_supplied", width:80, name:"Already Supplied", field:"already_supplied", cssClass:"cell-right-align"},
        <?php endif; ?>
	{id:"docket_item_qty", width:80, name:"<?php echo $this->lang->line('qty_supplied'); ?>", field:"docket_item_qty", sortable:true, sortNumeric:true, cssClass:"cell-right-align", editor:QuantityCellEditor, formatter:QuantityCellFormatter},
        


];

var options = {
	editable: true,
	autoEdit: false,
	enableCellNavigation: true,
	enableColumnReorder: false

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

function updateDocketItem(item, field)
{

	var post_data = item;

	$.post("<?php echo site_url('delivery_dockets/update_docket_item'); ?>",{

		docket_id: <?php echo uri_assoc('docket_id'); ?>,
		docket_item: JSON.stringify(post_data)

	}, function(data) {
            if(data == 'session_expired'){
                            window.location.reload();
                        }
		var docket_item = dataView.getItemById(data.docket_item.docket_item_id);
		dataView.updateItem(docket_item.docket_item_id, data.docket_item);
                grid.render();

	}, "json");
};

$(document).ready(function(){

$(document).on('click', '.open-popup', function () {
            var el = $(this);
            $.magnificPopup.open({
                items: {
                    src: '<div class="small-dialog">' + $(this).data('message') + '</div>', // can be a HTML string, jQuery object, or CSS selector
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
        });

	if (grid === undefined) {

		dataView = new Slick.Data.DataView();


		grid = new Slick.Grid($("#itemGrid"), dataView, columns, options);
		grid.registerPlugin(new Slick.AutoTooltips());
		grid.setSelectionModel(new Slick.RowSelectionModel());
		//grid.setSortColumn("client_name", true );

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

		// wire up model events to drive the grid
		grid.onCellChange.subscribe(function(e,args) {
			//dataView.updateItem(args.item[dataView.getIdProperty()], args.item);
			var column = grid.getColumns()[args.cell];

			updateDocketItem(args.item, column.field);
		});



	}



	$.post("<?php echo site_url('delivery_dockets/get_docket_items_JSON'); ?>",{
		docket_id: <?php echo uri_assoc('docket_id'); ?>

	}, function(data) {
            if(data == 'session_expired'){
                            window.location.reload();
                        }
		dataView.beginUpdate();


		var docket_items = data.docket_items;


		dataView.setItems(docket_items, 'docket_item_id');


		//dataView.setFilter(filter);
		dataView.endUpdate();


	}, "json");



});

</script>

