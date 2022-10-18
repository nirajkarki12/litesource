<style>
    body, html {
        margin: 0;
        padding: 0;
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

<div id="orderGrid" style="width: 1160px;height: 500px; overflow: hidden;outline: 0px;position: relative;"></div>

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

    var grid;
    var dataView;

    var dateField = 'e';
    var orderNumField = 'n';
    var sortCol = orderNumField;
    var sortDir = 1;

    var grid;
    var dataView;

    var all_data_loaded = false;
    var url = "<?php echo site_url('orders/get_orders_JSON'); ?>";

    var offset = 0;
    var limit = 500;
    var total_data = 0;
    var search_params = null;
    var onDataLoading = new Slick.Event();
    var onDataLoaded = new Slick.Event();
    var loadingIndicator = null;

    var projects = <?=$projects?>;
    var projectsById = {};
    // Update project indexing
    for (var i = 0, l = projects.length; i < l; i++) {
        var id = projects[i]['id'];
        projectsById[id] = i;
    }

    var suppliers = <?=$suppliers?>;
    var suppliersById = {};
    // Update supplier indexing
    if(suppliers && suppliers.length > 0) {
        for (let i = 0, l = suppliers.length; i < l; i++) {
            let id = suppliers[i]['id'];
            suppliersById[id] = i;
        } 
    }
    
    var statii = <?=$order_statuses?>;
    var statusById = {};
    // Update status indexing
    for (var i = 0, l = statii.length; i < l; i++) {
        var id = statii[i]['invoice_status_id'];
        statusById[id] = statii[i]['invoice_status'];
    }

    var _changeInterval = null;
    var currentRequest;

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


var columnFilters = {};

var editURL = "<?php echo site_url(uri_seg(1).'/edit/order_id'); ?>";


var columns = [
    {id:"order_status", name:"Status", field:"order_status", sortField:"s", width:60, sortable:true, formatter: StatusCellFormatter},
    {id:"order_number", name:"Order #", field:"n", fieldLink:"id",
        linkUrl: editURL, width:80, sortable:true, asyncPostRender : asyncRenderItemLink},
    {id:"order_date", name:"Date", field:"order_date", sortField:"e", width:80, sortable:true},
    {id:"supplier_name", name:"Supplier", field:"supplier_name", fieldLink:"c",
        linkUrl: "<?php echo site_url('clients/details/client_id/'); ?>", width:260, sortable:true, asyncPostRender : asyncRenderItemLink},
    {id:"order_quote", name:"Quote #", field:"qn", fieldLink:"qi",
        linkUrl: "<?php echo site_url('quotes/edit/invoice_id/'); ?>", width:70, sortable:true, asyncPostRender : asyncRenderItemLink},
    {id:"project_name", name:"Project", field:"project_name", fieldLink:"p",
        linkUrl: "<?php echo site_url('projects/details/project_id/'); ?>", width:280, sortable:true, asyncPostRender : asyncRenderItemLink},
            {id:"cur", name:"Cur", field:"cur", fieldLink:"cur", width:100, sortable:true},
    {id:"total", name:"Total", field:"total", fieldLink:"p", width:100, sortable:true},
    {id:"supplier_invoice_number", name:"Supplier Invoice Number", field:"supplier_invoice_number", fieldLink:"p", width:130, sortable:true, editor: TextCellEditor}

    //{id:"order_amount", name:"Amount", field:"a", width:95, cssClass: "column-total"}

];

var options = {
    autoEdit: false,
    editable: true,
    enableCellNavigation: true,
    enableColumnReorder: false,
    enableAsyncPostRender: true,
    showHeaderRow: true
};


function updateOrderDetails(order)
{

    var dv = order['e']*1000;
    var d = new Date(dv);

    order['order_date'] = $.datepicker.formatDate('dd/mm/yy', d);
    order['order_status'] = statusById[order['s']];

    var id = order['c'];
    var idx = suppliersById[id];

    if (idx === undefined) {
        order['supplier_name'] = '(deleted)'
    } else {
        var supplier = suppliers[idx];
        order['supplier_name'] = supplier['n'];
    }


    id = order['p'];
    idx = projectsById[id];

    if (idx === undefined) {
        order['project_name'] = '(deleted)'

    } else {
        var project = projects[idx];
        order['project_name'] = project['n'];

    }


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

                .on('change paste input', function(e) {

                    var val = $.trim($(this).val());

                    if (val == '')
                        columnFilters[$(this).data("columnId")] = false;
                    else {
                        columnFilters[$(this).data("columnId")] = val;
                    }
                    if(_changeInterval) clearInterval(_changeInterval);

                    if($(this).is(':focus')){

                        _changeInterval = setInterval(function(){
                            clearInterval(_changeInterval);
                            search_params = null;
                            search_params = columnFilters;
                            all_data_loaded = false;
                            offset = 0;
                            grid.scrollRowIntoView(1, true);
                            total_data = limit;
                            loadOrdersData();
                        }.bind(columnFilters), 500);
                    }

                    // applyFilter();

                })
                .appendTo(header);
        }
    }

}

function loadOrdersData(){
    if (!all_data_loaded) {
        onDataLoading.notify();
        currentRequest = $.ajax({
            type: 'POST',
            dataType: "json",
            url: url,
            data: {
                limit: limit,
                offset: offset,
                filters: search_params
            },
            beforeSend : function()    {           
                if(currentRequest != null) {
                    currentRequest.abort();
                }
            },
            success: function(data) {
                onDataLoaded.notify(data);
            },
            error:function(e){
              console.log(e.responseText)
            }
        });
    }
}

$(document).ready(function(){

    if (grid === undefined) {

        dataView = new Slick.Data.DataView();


        grid = new Slick.Grid($("#orderGrid"), dataView, columns, options);
        //var pager = new Slick.Controls.Pager(dataView, grid, $("#pager"));
        grid.registerPlugin(new Slick.AutoTooltips());
        grid.setSelectionModel(new Slick.RowSelectionModel());
        //grid.setSortColumn("order_date", false );
        grid.setSortColumn("order_number", false );


        dataView.onRowCountChanged.subscribe(function(e,args) {
            grid.updateRowCount();
            grid.render();
        });

        dataView.onRowsChanged.subscribe(function(e,args) {
            grid.invalidateRows(args.rows);
            grid.render();

        });

        grid.onSort.subscribe(function(e,args) {
            sortDir = args.sortAsc ? 1 : -1;
            if (args.sortCol.sortField == undefined)
                sortCol = args.sortCol.field;
            else
                sortCol = args.sortCol.sortField;
            // using native sort with comparer
            // preferred method but can be very slow in IE with huge datasets
            dataView.sort(comparer, args.sortAsc);
        });
        // wire up model events to drive the grid
        grid.onCellChange.subscribe(function (e, args) {
            //dataView.updateItem(args.item[dataView.getIdProperty()], args.item);
            var column = grid.getColumns()[args.cell];
            updateOrderItem(args.item, column.field);
        });

        //---- on scroll pulling data from ajax call ----------
        grid.onViewportChanged.subscribe(function (e, args) {
            var vp = grid.getViewport();
            console.log('bottom-', vp.bottom, ', offset-', offset, ', limit-', limit, ', total_data-', total_data, ', total_data/1.3-', Math.round(total_data/1.3));

            if((Math.round(total_data/1.3) <= vp.bottom) && !all_data_loaded){
                total_data += limit;
                offset += limit;

                console.log('data pull request');
                loadOrdersData();
            }
        });

        onDataLoading.subscribe(function () {
            if (!loadingIndicator) {

                var $g = $("#orderGrid");
                loadingIndicator = $("<div class='loader'><img src='<?php echo base_url();?>assets/style/img/loading.gif' style='vertical-align:sub'> Loading orders. This may take few seconds.</div>").appendTo($g);

                loadingIndicator
                    .css({"position": "absolute", "text-align": "center", "border-radius": "10px", "z-index": "1000", "color": "#fff", "cursor": "wait", "background": "#00000099","padding": "20px 15px"})
                    .css("bottom", $g.position().top - 60 + $g.height() / 2 )
                    .css("left", $g.position().left + $g.width() / 2 - loadingIndicator.width() / 2)
                    ;
            }
            loadingIndicator.show();
        });

        onDataLoaded.subscribe(function (e, data) {

            if(data == 'session_expired'){
                window.location.reload();
            }

            if(data && data.hasOwnProperty("orders") && data.orders){
                let replace_view = false;

                if(data.hasOwnProperty("total_data")){
                    if(data.total_data < limit){
                        total_data = total_data - limit + data.total_data;
                    }

                    if(data.total_data == 0){
                        replace_view = true;
                    }

                    if(total_data < (offset + limit)){
                        all_data_loaded = true;
                    }
                }

                if(offset == 0){
                    replace_view = true;
                } else{
                    replace_view = false;
                }

                dataView.beginUpdate();

                // Updating orders
                var orders = data.orders;
                for (var i = 0, l = orders.length; i < l; i++) {
                    updateOrderDetails(orders[i]);

                    if(!replace_view) dataView.addItem(orders[i]);
                }
                if(replace_view){
                    dataView.setItems(orders, 'id');
                }
                dataView.endUpdate();
                dataView.refresh();
                grid.invalidate();
            }
            if(loadingIndicator){
                loadingIndicator.fadeOut();
                loadingIndicator = null;
            }
        });
        updateHeaderRow();
        resizing_header_js();
    }

    //---- pulling initial data from ajax call ----------
    if(offset == 0){
        total_data = limit;
        loadOrdersData();
    }
    //---- pulling initial data from ajax call end ----------

    

});

    /*
     *   Special processing if item quantity = -1 since this will generate a subtotal
     *   so update description with default "Subtotal:"
     */
    function checkItemSpecialValues(item) {

        if ((item.item_qty == SUBTOTAL_QTY) && (item.item_description == '')) {
            item.item_description = SUBTOTAL_DESC
        }

        return item;
    }

    function updateOrderItem(item, field){
        
        $.post("<?php echo site_url('orders/updateOrderItem'); ?>", {
            order_id: item.id,
            supplier_invoice_number: item.supplier_invoice_number

        }, function (data) {
            if(data == 'session_expired'){
                            window.location.reload();
                        }

        }, "json");
    }

</script>

