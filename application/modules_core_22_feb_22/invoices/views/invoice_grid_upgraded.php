<style>
    .slick-headerrow-columns {
        height: 32px;
    }

    .slick-headerrow-column input {
        margin: 0;
        padding: 2;
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
        z-index: 99;
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

    .cell-title {
        font-weight: bold;
    }

    .cell-effort-driven {
        text-align: center;
    }

    .toggle {
        height: 9px;
        width: 9px;
        display: inline-block;
    }

    .toggle.expand {
        background: url(http://mleibman.github.io/SlickGrid/images/expand.gif) no-repeat center center;
    }

    .toggle.collapse {
        background: url(http://mleibman.github.io/SlickGrid/images/collapse.gif) no-repeat center center;
    }

</style>






<script type="text/javascript" src="<?php echo base_url(); ?>assets/latest/lib/jquery.event.drag-2.2.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/latest/slick.core.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/latest/slick.formatters.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/latest/plugins/slick.autotooltips.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/latest/plugins/slick.rowselectionmodel.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/latest/slick.editors.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/latest/slick.grid.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/latest/slick.dataview.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/slick/json2.js"></script>

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


<div id="invGrid" style="width:1160px;height:500px;"></div>


<script type="text/javascript">

    //$('#expander').accordion();

    var statii;
    var statusById = {};

    var clients;
    var clientsById = {};

    var contacts;
    var contactsById = {};

    var projects;
    var projectsById = {};
    
    var invoices = {};

    var grid;
    var dataView;

    var dateField = 'e';
    var sortCol = dateField;
    var sortDir = 1;

    var invoice_url = "<?php echo uri_seg(1); ?>";

    var global_is_quote = <?php echo (uri_seg_is('quotes') ? 1 : 0); ?>;
    var all_data_loaded = false;

    function comparer(a, b) {
        var x = a[sortCol], y = b[sortCol];

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


    var editURL = "<?php echo site_url(uri_seg(1) . '/edit/invoice_id'); ?>";


    var columns = [
        {id: "invoice_status", name: "Status", field: "invoice_status", sortField: "s", width: 100, sortable: true, formatter: statusFormatter},
        {id: "invoice_number", name: "#", field: "n", fieldLink: "id",
            linkUrl: editURL,linkUrlDock:"<?php echo site_url('delivery_dockets/edit/docket_id'); ?>", width: 80, sortable: true, asyncPostRender: asyncRenderItemLink_dock},
        {id: "invoice_date", name: "Date", field: "invoice_date", sortField: "e", width: 80, sortable: true},
        {id: "client_name", name: "Client", field: "client_name", fieldLink: "c",
            linkUrl: "<?php echo site_url('clients/details/client_id/'); ?>", width: 280, sortable: true, asyncPostRender: asyncRenderItemLink},
        {id: "client_po#", name: "Client Po#", field: "po", width: 100, sortable: true},
        {id: "invoice_quote", name: "Quote #", field: "qn", fieldLink: "qi",
            linkUrl: "<?php echo site_url('quotes/edit/invoice_id/'); ?>", width: 70, sortable: true, asyncPostRender: asyncRenderItemLink},
        {id: "project_name", name: "Project", field: "project_name", fieldLink: "p",
            linkUrl: "<?php echo site_url('projects/details/project_id/'); ?>", width: 280, sortable: true, asyncPostRender: asyncRenderItemLink},
        {id: "project_specifier", name: "Specifier", field: "project_specifier", width: 150, sortable: true},
        {id: "invoice_amount", name: "Total", field: "a", width: 95, cssClass: "column-total"},
    ];

    var options = {
        autoEdit: false,
        enableCellNavigation: true,
        enableColumnReorder: false,
        enableAsyncPostRender: true,
        showHeaderRow: true
    };


    function setupDetails() {

    }


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


    function statusFormatter_(row, cell, value, columnDef, dataContext)
    {

        return '<span class="status_' + dataContext['s'] + '">' + value + '</span>';

    }

    function statusFormatter(row, cell, value, columnDef, dataContext) {
        value = value.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
        var spacer = "<span style='display:inline-block;height:1px;width:" + (15 * dataContext["indent"]) + "px'></span>";
        var idx = dataView.getIdxById(dataContext.id);
        if (invoices[idx + 1] && invoices[idx + 1].indent > invoices[idx].indent) {
            if (dataContext._collapsed) {
                return spacer + " <span class='toggle expand'></span>&nbsp;" + '<span class="status_' + dataContext['s'] + '">' + value + '</span>';
            } else {
                return spacer + " <span class='toggle collapse'></span>&nbsp;" + '<span class="status_' + dataContext['s'] + '">' + value + '</span>';
            }
        } else {
            return spacer + " <span class='toggle'></span>&nbsp;" + '<span class="status_' + dataContext['s'] + '">' + value + '</span>';
        }
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
    
    function asyncRenderItemLink_dock(cellNode, row, dataContext, colDef)
    {
        var f = colDef.field;
        var fl = colDef.fieldLink;

        if (dataContext[f] == null)
            return;
        
        if(typeof dataContext['type'] != 'undefined' && dataContext['type'] == 'docket'){
            var a = '<a href="' + colDef.linkUrlDock + '/' + dataContext[fl] + '">' + dataContext[f] + '</a>';
            $(cellNode).html(a);
        }else{
            var a = '<a href="' + colDef.linkUrl + '/' + dataContext[fl] + '">' + dataContext[f] + '</a>';
            $(cellNode).html(a);
        }

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

    function updateInvoiceDetails(invoice)
    {

        var dv = invoice['e'] * 1000;
        var d = new Date(dv);

        invoice['invoice_date'] = $.datepicker.formatDate('dd/mm/yy', d);
        invoice['invoice_status'] = statusById[invoice['s']];

        var id = invoice['c'];
        var idx = clientsById[id];

        if (idx === undefined) {
            invoice['client_name'] = '(deleted)'
        } else {
            var client = clients[idx];
            invoice['client_name'] = client['n'];
        }

        id = invoice['ct'];
        idx = contactsById[id];

        if (idx === undefined) {
            invoice['contact_name'] = '(deleted)'

        } else {
            var contact = contacts[idx];
            invoice['contact_name'] = contact['n'];

        }

        id = invoice['p'];
        idx = projectsById[id];

        if (idx === undefined) {
            invoice['project_name'] = '(deleted)'

        } else {
            var project = projects[idx];
            invoice['project_name'] = project['n'];
            invoice['project_specifier'] = project['s'];

        }


    }

    function applyFilter() {


        if (!all_data_loaded) {
            all_data_loaded = true;

            $.post("<?php echo site_url('invoices/get_invoices_only_JSON'); ?>", {
                is_quote: global_is_quote,
                limit: 10000,
                offset: 0

            }, function (data) {
                if (data == 'session_expired') {
                    window.location.reload();
                }
                dataView.beginUpdate();

                var invoices = data.invoices;

                for (var i = 0, l = invoices.length; i < l; i++) {
                    updateInvoiceDetails(invoices[i]);
                }

                dataView.setItems(invoices, 'id');

                dataView.endUpdate();


            }, "json");
        } else {
            dataView.refresh();
        }


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

                        .keyup(function (e) {

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



    $(document).ready(function () {

        if (grid === undefined) {

            dataView = new Slick.Data.DataView();

            if (global_is_quote == 1) {
                console.log('slice columns');
                columns.splice(4, 1); //Remove Client PO# column on quotes

            } else {
                columns.splice(7, 1); //Remove Project Specifer on invoices
            }

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

            grid.onClick.subscribe(function (e, args) {
                console.log('jtoit...');
                console.log($(e.target));
                if ($(e.target).hasClass("toggle")) {
                    console.log(args.row);
                    var item = dataView.getItem(args.row);
                    console.log(item);
                    if (item) {
                        if (!item._collapsed) {
                            item._collapsed = true;
                        } else {
                            item._collapsed = false;
                        }

                        dataView.updateItem(item.id, item);
                    }
                    e.stopImmediatePropagation();
                }
            });



            /*
             grid.onSelectedRowsChanged.subscribe(function (e) {
             //var rec = grid.getCellFromEvent(e);
             
             //openDetails();
             
             
             });
             */

            grid.onDblClick.subscribe(function (e) {
                var rec = grid.getCellFromEvent(e);

                openDetails();


            });


            updateHeaderRow();



        }


        $.post("<?php echo site_url('invoices/get_invoices_JSON'); ?>", {
            is_quote: global_is_quote,
            limit: 100,
            offset: 0

        }, function (data) {
            if (data == 'session_expired') {
                window.location.reload();
            }
            dataView.beginUpdate();

            clients = data.clients;
            contacts = data.contacts;
            projects = data.projects;
            statii = data.invoice_statuses;

            invoices = data.invoices;

            // Update status indexing
            for (var i = 0, l = statii.length; i < l; i++) {
                var id = statii[i]['invoice_status_id'];
                statusById[id] = statii[i]['invoice_status'];
            }

            // Update client indexing
            for (var i = 0, l = clients.length; i < l; i++) {
                var id = clients[i]['id'];
                clientsById[id] = i;
            }

            // Update contact indexing
            for (var i = 0, l = contacts.length; i < l; i++) {
                var id = contacts[i]['id'];
                contactsById[id] = i;
            }

            // Update project indexing
            for (var i = 0, l = projects.length; i < l; i++) {
                var id = projects[i]['id'];
                projectsById[id] = i;
            }

            for (var i = 0, l = invoices.length; i < l; i++) {
                updateInvoiceDetails(invoices[i]);
            }

            dataView.setItems(invoices, 'id');
            //console.log(invoices);
            dataView.setFilter(filter);
            //dataView.sort(comparer, sortDir);
            dataView.endUpdate();


        }, "json");



    });

</script>

