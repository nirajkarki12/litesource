<style>
    .slick-headerrow-columns {
        height: 32px;
    }

    .slick-headerrow-column input {
        margin: 0;
        padding: 0px;
        border-width: 2px;
    }

    .column-total {
        text-align: right;
    }

    .options-panel {
        -moz-border-radius: 6px;
        -webkit-border-radius: 6px;
        border: 1px solid silver;
        background: #f0f0f0;
        padding: 4px;
        margin-bottom: 20px;
        width: 320px;
        position: absolute;
        top:105px;
        left: 1260px;
    }

    .item-details-form {
        z-index: 10000;
        display: inline-block;
        border: 1px solid black;
        margin: 8px;
        padding: 10px;
        background: #efefef;
        -moz-box-shadow: 0px 0px 15px black;
        -webkit-box-shadow: 0px 0px 15px black;
        box-shadow: 0px 0px 15px black;

        position: absolute;
        top: 10px;
        left: 150px;
    }

    .item-details-form-buttons {
        float: right;
    }

    .item-details-label {
        margin-left: 10px;
        margin-top: 20px;
        display: block;
        font-weight: bold;
    }

    .item-details-editor-container {
        width: 300px;
        height: 20px;
        border: 1px solid silver;
        background: white;
        display: block;
        margin: 10px;
        margin-top: 4px;
        padding: 0;
        padding-left: 4px;
        padding-right: 4px;
    }
    .text-center{
        text-align: center;
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

<script src="http://ajax.microsoft.com/ajax/jquery.templates/beta1/jquery.tmpl.min.js"></script>

<script id="itemDetailsTemplate" type="text/x-jquery-tmpl">
    <div class='options-panel'>
    {{each fields}}
    <div class='item-details-label'>
    ${name}
    </div>
    <div class='item-details-editor-container' data-editorid='${field}'>${invoice[field]}</div>
    {{/each}}


    <hr/>
    <div class='item-details-form-buttons'>
    <button data-action='close'>Close</button>
    </div>
    </div>
</script>


<div id="invGrid" style="width: 1160px;height: 500px; overflow: hidden;outline: 0px;position: relative;"></div>

<script type="text/javascript">

    //$('#expander').accordion();

    var statusById = {};

    var grid;
    var dataView;

    var dateField = 'e';
    var sortCol = dateField;
    var sortDir = 1;

    var invoice_url = "<?php echo uri_seg(1); ?>";

    var global_is_quote = <?php echo (uri_seg_is('quotes') ? 1 : 0); ?>;
    var all_data_loaded = false;
    var offset = 0;
    var limit = 500;
    var onDataLoading = new Slick.Event();
    var onDataLoaded = new Slick.Event();
    var loadingIndicator = null;
    var total_data = 0;

    // if(global_is_quote){
        var url = "<?php echo site_url('invoices/get_quote_invoices_JSON_index'); ?>";
    // } else{
        // var url = "<?php //echo site_url('invoices/get_invoices_only_JSON_withDocket'); ?>";
    // }
    var search_params = null;
    var statii = <?=$invoice_statuses?>;

    var ready_to_update = false;
    var _changeInterval = null;
    var currentRequest;

    PriceCellFormatter = function (row, cell, value, columnDef, dataContext) {
        
        var s = value;
        if(s.substr(0,1) == "-") {
            s = s.substr(1);
            return '-$' + s;
        }else{
            return '$' + s;
        }
    };
    

    function comparer(a, b) {
        var x = a[sortCol], y = b[sortCol];
        //console.log(x); console.log(y);
        // compare by invoice date if other values the same unless
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

    var invoiceFields = [
        {field: "invoice_status", name: "Status"},
        {field: "id", name: "#"},
        {field: "invoice_date", name: "Date"},
        {field: "client_name", name: "Client"},
        {field: "project_name", name: "Project"},
        {field: "contact_name", name: "Contact"},
    ]
    var editURL = "<?= site_url(uri_seg(1) . '/edit/invoice_id'); ?>";
    var inv_amtt = "Total Amount";
<?php $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; 

if (strpos($actual_link, 'quotes') !== false) { ?>
    inv_amtt = "Total";
    <?php
}

?>

    var columns = [
        {id: "invoice_status", name: "Status", field: "invoice_status", sortField: "s", width: 60, sortable: true, formatter: statusFormatter},
        {id: "invoice_number", name: "#", field: "n", fieldLink: "id",
            linkUrl: editURL, width: 80, sortable: true, asyncPostRender: asyncRenderItemLink},
        <?php if (uri_seg(1) == 'invoices'): ?>
        {id: "docket_count", name: "Dockets", field: "docket_count", width: 80, cssClass: "docket-count text-center",asyncPostRender: asyncRenderDocketCount},
        <?php endif; ?>
        {id: "invoice_date", name: "Date", field: "invoice_date", sortField: "e", width: 80, sortable: true},
        {id: "client_name", name: "Client", field: "client_name", fieldLink: "c",
            linkUrl: "<?= site_url('clients/details/client_id/'); ?>", width: 240, sortable: true, asyncPostRender: asyncRenderItemLink},
                
        <?php if (uri_seg(1) == 'invoices'){ ?>
        {id: "client_state", name: "State", field: "cl_s", fieldLink: "c", width: 100, sortable: true, linkUrl: "<?= site_url('clients/details/client_id/'); ?>", asyncPostRender: asyncRenderItemLink, editor: TextCellEditor},
        {id: "client_po#", name: "Client Po#", field: "po", width: 100, sortable: true},
        <?php } ?>
        {id: "invoice_quote", name: "Quote #", field: "qn", fieldLink: "qi", linkUrl: "<?= site_url('quotes/edit/invoice_id/'); ?>", width: 70, sortable: true, asyncPostRender: asyncRenderItemLink},
        
        {id: "project_name", name: "Project", field: "project_name", fieldLink: "p", linkUrl: "<?= site_url('projects/details/project_id/'); ?>", width: 310, sortable: true, asyncPostRender: asyncRenderItemLink},
                
        {id: "project_specifier", name: "Specifier", field: "project_specifier", width: 150, sortable: true},
        {id: "invoice_amount", name: inv_amtt, field: "a", width: 95, cssClass: "column-total", formatter: PriceCellFormatter},
        <?php if (uri_seg(1) == 'invoices'){ ?>
        {id: "owing_amount", name: 'Total Owing', field: "owng_amt", width: 95, cssClass: "column-total", formatter: PriceCellFormatter,},
        <?php } ?>        
    ];

    var options = {
        autoEdit: false,
        editable: true,
        enableCellNavigation: true,
        enableColumnReorder: false,
        enableAsyncPostRender: true,
        showHeaderRow: true,
        forceFitColumns: true,
        syncColumnCellResize: true
    };


    function openDetails() {

        var $modal = $("#itemDetailsTemplate")

                .tmpl({
                    invoice: grid.getDataItem(grid.getActiveCell().row),
                    fields: invoiceFields
                })

                .appendTo("body");


        $modal.find("[data-action=close]").click(function () {
            $modal.remove();
        });


    }


    function statusFormatter(row, cell, value, columnDef, dataContext)
    {

        return '<span class="status_' + dataContext['s'] + '">' + value + '</span>';

    }

    function asyncRenderDocketCount(cellNode, row, dataContext, colDef){
        
        var f = colDef.field;
        var fl = colDef.fieldLink;

        if (dataContext[f] == null)
            return;

        var a = '<a data-invoicenum="'+dataContext['n']+'" data-effect="mfp-zoom-in" class="inv-docket open-popup" href="<?php echo site_url() ?>/invoices/getInvoiceDockets/invoice_id/' + dataContext['id'] + '">' + dataContext[f] + '</a>';
        
        $(cellNode).html(a);
        update_paid_amount_clr();
    }

    function asyncRenderItemLink(cellNode, row, dataContext, colDef) {
        var f = colDef.field;
        var fl = colDef.fieldLink;
        if (dataContext[f] == null)
            return;
        var a = '<a href="' + colDef.linkUrl + '/' + dataContext[fl] + '">' + dataContext[f] + '</a>';
        $(cellNode).html(a);
    }
    
    function updateInvoiceDetails(invoice){

        var dv = invoice['e'] * 1000;
        var d = new Date(dv);

        for (var i = 0, l = statii.length; i < l; i++) {
            var id = statii[i]['invoice_status_id'];
            statusById[id] = statii[i]['invoice_status'];
        }

        invoice['invoice_date'] = $.datepicker.formatDate('dd/mm/yy', d);
        invoice['invoice_status'] = statusById[invoice['s']];
        invoice['client_name'] = (invoice['c']) ? ((invoice['c'] > '0') ? invoice['cl_n'] : '(deleted)') : '';
        invoice['contact_name'] = (invoice['ct']) ? ((invoice['ct'] > '0') ? invoice['co_n'] : '(deleted)') : '';
        invoice['project_name'] = (invoice['p']) ? ((invoice['p'] > '0') ? invoice['pr_n'] : '(deleted)') : '';
        invoice['project_specifier'] = (invoice['p']) ? ((invoice['p'] > '0') ? invoice['pr_s'] : '(deleted)') : '';
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

                    .on('change paste input', function (e) {

                        var val = $.trim($(this).val());

                        if (val == '') {
                            if($(this).data("columnId") == 'client_po#'){
                                columnFilters['client_po'] = false;
                            }else{
                                columnFilters[$(this).data("columnId")] = false;
                            }
                        } else {
                            if($(this).data("columnId") == 'client_po#'){
                                columnFilters['client_po'] = val;
                            }else{
                                columnFilters[$(this).data("columnId")] = val;
                            }
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
                                getQuoteInvoicesJSON();
                            }.bind(columnFilters), 500);
                        }
                        // applyFilter();
                    })
                    .appendTo(header);
            }
        }

    }

$(document).ready(function () {
    if (grid === undefined) {
        dataView = new Slick.Data.DataView();
        
        grid = new Slick.Grid($("#invGrid"), dataView, columns, options);
        grid.registerPlugin(new Slick.AutoTooltips());
        grid.setSelectionModel(new Slick.RowSelectionModel());
        grid.setSortColumn("invoice_date", false);
        dataView.onRowCountChanged.subscribe(function (e, args) {
            grid.updateRowCount();
            grid.render();
        });
        dataView.onRowsChanged.subscribe(function (e, args) {
            grid.invalidateRows(args.rows);
            grid.render();
            update_paid_amount_clr1(args);
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

        grid.onCellChange.subscribe(function (e, args) {
            updateInvoiceItem(args.item);
        });

        //---- on scroll pulling data from ajax call ----------
        grid.onViewportChanged.subscribe(function (e, args) {
            var vp = grid.getViewport();
            console.log('bottom-', vp.bottom, ', offset-', offset, ', limit-', limit, ', total_data-', total_data, ', total_data/1.3-', Math.round(total_data/1.3));

            if((Math.round(total_data/1.3) <= vp.bottom) && !all_data_loaded){
                total_data += limit;
                offset += limit;

                console.log('data pull request')
                getQuoteInvoicesJSON();
            }
        });

        onDataLoading.subscribe(function () {
            if (!loadingIndicator) {

                var $g = $("#invGrid");
                loadingIndicator = $("<div class='loader'><img src='<?php echo base_url();?>assets/style/img/loading.gif' style='vertical-align:sub'> Loading "+(global_is_quote ? 'quotes' : 'invoices') +". This may take few seconds.</div>").appendTo($g);

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

            if(data && data.hasOwnProperty("invoices") && data.invoices){
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
                // Updating invoices only
                var invoices = data.invoices;
                for (var i = 0, l = invoices.length; i < l; i++) {
                    updateInvoiceDetails(invoices[i]);
                    
                    if(!replace_view) dataView.addItem(invoices[i]);
                }
                if(replace_view){
                    dataView.setItems(invoices, 'id');
                }

                dataView.endUpdate();
                dataView.refresh();
                grid.invalidate();
                ready_to_update = true;
                update_paid_amount_clr();
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
            getQuoteInvoicesJSON();
        }
        //---- pulling initial data from ajax call end ----------

    $(document).on('click', '.open-popup', function (e) {
        var el = $(this);
        e.preventDefault();
        var url = $(this).attr('href');

        $.magnificPopup.open({
            items: {
                src: '<div class="small-dialog" style="text-align:center;"><img src="<?php echo base_url().'assets/style/img/loading.gif'; ?>" /></div>', // can be a HTML string, jQuery object, or CSS selector
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

        $.ajax({
            type: 'POST',
            url: url,
            data: {invoicenum: el.data('invoicenum')},
            dataType: 'html',
            success: function (data) {
                $('.small-dialog').html(data);
            }
        });
    });

    $(document).on('click','.btnopennewtab',function(e){
        e.preventDefault();
        var url = $(this).parent('form').attr('action');
        window.open(url,'_blank');
    });
});

function getQuoteInvoicesJSON(){
    if (!all_data_loaded) {
        onDataLoading.notify();
        currentRequest = $.ajax({
            type: 'POST',
            dataType: "json",
            url: url,
            data: {
                is_quote: global_is_quote,
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
    
    
function updateInvoiceItemState(client_id, client_state){
    var invoices = dataView.getItems();
    dataView.beginUpdate();
    for (var i = 0, l = invoices.length; i < l; i++) {   
        var item = dataView.getItemById( invoices[i].id );
        if( client_id == item.c ){
            item.cs = client_state;
            dataView.updateItem(item.id, item);
        }
    }
    dataView.endUpdate();
}
    
function updateInvoiceItem( item ){    
    var client_state = item.cs;
    var client_id = item.c;
    $.post("<?= site_url('clients/update_client_state'); ?>", {
        client_id: client_id,
        client_state: client_state
    }, function (data) {
        if(data.status == true){
            updateInvoiceItemState(client_id, client_state)
            if (!ready_to_update) {
                setTimeout(function(){ updateInvoiceItemState(client_id, client_state); }, 7000);
                setTimeout(function(){ updateInvoiceItemState(client_id, client_state); }, 9000);
            }
        } else {
            dataView.refresh();
        }
    }, "json");
}

    
    
function update_paid_amount_clr(){
    var invoices = dataView.getItems();
    for (var i = 0, l = invoices.length; i < l; i++) {   
        var row_id = dataView.getRowById( invoices[i].id );
        if( row_id !=='undefined'){
            var item = dataView.getItem( row_id );
            if((typeof item !== 'undefined')){
                if(item.ship_odr > 0){
                    ( $(".slick-row[row='"+row_id+"']").css({'background':'#ffd8d1'}) );
                }
            }
        }
    }
}   

function update_paid_amount_clr1(args){
    
    for (var i = 0, l = ((args.rows).length); i < l; i++) {   
        var rr = dataView.getItem(i);
        if(rr.ship_odr > 0){
            ( $(".slick-row[row='"+i+"']").css({'background':'#ffd8d1'}) );
        }
    }
}
    
    
</script>

