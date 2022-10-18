<style>
    body, html { 
        margin: 0; 
        padding: 0; 
        /*		overflow:hidden;*/
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

    .product_details {
        width: 400px;
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

<script type="text/javascript" src="<?php echo base_url(); ?>assets/latest/plugins/slick.checkboxselectcolumn.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/slick/plugins/slick.checkboxselectcolumn.js"></script>



<div id="product_details" class="content">
    <dl>
        <dt><label><?php echo $this->lang->line('catalog_number'); ?>: </label></dt>
        <dd><input type="text" name="product_name" id="n" /></dd>
    </dl>

    <dl>
        <dt><label><?php echo $this->lang->line('supplier_catalog_number'); ?>: </label></dt>
        <dd><input type="text" name="product_supplier_code" id="c" /></dd>
    </dl>

    <dl>
        <dt id="price_label"><label><?php echo $this->lang->line('product_supplier_price'); ?>: </label></dt>
        <dd><input type="text" name="product_supplier_price" id="b" /></dd>
    </dl>

    <dl>
        <dt><label><?php echo $this->lang->line('product_base_price'); ?>: </label></dt>
        <dd><input type="text" name="product_base_price" id="p" /></dd>
    </dl>

    <dl>
        <dt><label><?php echo $this->lang->line('product_description'); ?>: </label></dt>
        <dd><textarea type="textarea" name="product_description" id="d" /></textarea></dd>
    </dl>

</div>

<div id="prodGrid" style="width:1160px;height:500px;"></div>


<script type="text/javascript">

    $(function () {

        var $modal = $("#product_details");


        $modal.find('#product_description').autogrow();

        $modal.dialog({
            autoOpen: false,
            minWidth: 600
        });

    });

<?php
global $CI;
echo "var currencySymbol = '" . $CI->mdl_mcb_data->setting('currency_symbol') . "';";
?>

    PriceCellFormatter = function (row, cell, value, columnDef, dataContext) {
        return currencySymbol + value;
    };

    SupplierPriceCellFormatter = function (row, cell, value, columnDef, dataContext) {
        var cur_left = dataContext["currency_symbol_left"];
        var cur_right = dataContext["currency_symbol_right"];
        if(typeof cur_right == 'undefined'){
            cur_right = '';
        }
        if(typeof cur_left == 'undefined'){
            cur_left = '';
        }
        
        if( (cur_right == '') && (cur_left == '') ){
            cur_left = '$';
        }
        
        return cur_left + value + cur_right;
    };


    var suppliers;
    var suppliersById = {};
    var postProcessId = [];
    var h_postGetProductDescriptions;

    SupplierCellEditor = function (args) {
        var $input;
        var defaultValue;
        var scope = this;

        this.init = function () {


            $input = $("<INPUT type=text id='supplier' class='editor-text' />")
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
                        source: suppliers

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



    var grid;
    var dataView;
    var supplier_id_filter = <?php echo $supplier_id; ?>;
    var product_name_filter;
    var sortCol = 'supplier_name';
    var sortDir = 1;

    function comparer(a, b) {
        var x = a[sortcol], y = b[sortcol];

        // compare by product id if all else values are the same
        if (x == y) {
            x = a['n'];
            y = b['n'];
        }

        return (x == y ? 0 : (x > y ? 1 : -1));
    }

    var map = {
        'id': 'product_id',
        'n': 'product_name',
        'c': 'product_supplier_code',
        'd': 'product_description',
        'sd': 'product_supplier_description',
        'b': 'product_supplier_price',
        'p': 'product_base_price',
        's': 'supplier_id'
    }

    var columns = [
        //{id:"supplier_name", name:"Supplier", field:"supplier_name", width:100, sortable:true},
        //{id:"client_id", name:"Client", field:"client_id", width:40},
        // client_name is underlying supplier
        // {id: "client_name", name: "LS Supplier", field: "client_name", width: 120, sortable: true, editor: SupplierCellEditor},
        {id: "supplier_name", name: "Supplier", field: "supplier_name", width: 120, sortable: true,editor: SupplierCellEditor},
        {id: "product_name", name: "<?php echo $this->lang->line('catalog_number'); ?>", field: "n", fieldLink: "id",
            linkUrl: "<?php echo site_url('products/form/product_id/'); ?>",
            width: 190, minWidth: 150, sortable: true, editor: TextCellEditor, asyncPostRender: asyncRenderItemLink},
        {id: "product_supplier_code", name: "<?php echo $this->lang->line('supplier_catalog_number'); ?>", field: "c", width: 190, minWidth: 150, sortable: true, editor: TextCellEditor},
        {id: "product_description", name: "Description", field: "d", width: 300, editor: LongTextCellEditor, asyncPostRender: asyncRequestProductDescription},
        {id: "currency_code", name: "Cur", field: "currency_code", width: 40, minWidth: 40},
//        {id: "product_supplier_price", name: "Buy", field: "b", width: 70, cssClass: "cell-right-align", editor: TextCellEditor, formatter: SupplierPriceCellFormatter},
        {id: "product_base_price", name: "AU Trade", field: "p", width: 70, cssClass: "cell-right-align", editor: TextCellEditor, formatter: PriceCellFormatter},
        //{id:"product_active", name:"Active", field:"product_active"},
        //{id:"product_sort_index", name:"Sort Index", field:"product_sort_index"},
    ];
    
    
    var checkboxSelector = new Slick.CheckboxSelectColumn({
        cssClass: "chkbox_pro"
    });
    columns.unshift(checkboxSelector.getColumnDefinition());
    

    var options = {
        editable: true,
        autoEdit: false,
        enableCellNavigation: true,
        enableColumnReorder: false,
        enableAsyncPostRender: true

    };






    function update_product_details_view(item) {

        var $modal = $("#product_details");

        $modal.find('#n').val(item['n']);
        $modal.find('#b').val(item['b']);
        $modal.find('#d').val(item['d']);

        $modal.dialog("open");

        // update value if element corresponding to item property

    }


    function filter(item) {
        var res = true;

        if (supplier_id_filter != 0) {
            res = item.supplier_id == supplier_id_filter;
        }

        if (res && product_name_filter != undefined) {
            res = product_name_filter.test(item.n);
        }

        return res;
    }
    
    function update_product_by_suppliername(item, field){
        $.post("<?php echo site_url('products/ajax_update_product_supplier'); ?>", {
            post_item: JSON.stringify(item)

        }, function (data) {
            if(data == 'session_expired'){
                            window.location.reload();
                        }

            var product = dataView.getItemById(data.product.id);

            update_product_supplier_details(data.product);

            dataView.updateItem(product.id, data.product);

        }, "json");
    }
    ;


    function update_product(item, field)
    {


        $.post("<?php echo site_url('products/ajax_update_product'); ?>", {
            post_item: JSON.stringify(item)

        }, function (data) {
            if(data == 'session_expired'){
                            window.location.reload();
                        }

            var product = dataView.getItemById(data.product.id);

            update_product_supplier_details(data.product);

            dataView.updateItem(product.id, data.product);

        }, "json");

    }
    ;

    function update_product_supplier_details(product)
    {

        var sid = product.s;
        var sidx = suppliersById[sid];
        var supplier = suppliers[sidx];
        product.client_id = sidx;
        if (typeof supplier != 'undefined') {
            product.client_name = supplier.client_name;		//actual supplier name
            product.supplier_name = supplier.supplier_name;	//parent supplier name
            product.supplier_id = supplier.supplier_id;		//parent supplier id
            product.currency_code = supplier.currency_code;
            product.currency_symbol_left = supplier.currency_symbol_left;
            product.currency_symbol_right = supplier.currency_symbol_right;
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

    function asyncGetProductDescriptions()
    {

        if (postProcessId.length > 0) {

            var ids = JSON.stringify(postProcessId);
            postProcessId = [];

            $.post("<?php echo site_url('products/get_products_descriptions_JSON'); ?>", {
                products: ids

            }, function (data) {
                if(data == 'session_expired'){
                            window.location.reload();
                        }

                dataView.beginUpdate();

                for (var i = 0, l = data.length; i < l; i++) {
                    var id = data[i]['product_id'];
                    var item = dataView.getItemById(id);
                    item['d'] = data[i]['product_description'];
                    dataView.updateItem(id, item);
                }

                dataView.endUpdate();

            }, "json");
        }

    }

    // only pull down descriptions if required in view
    function asyncRequestProductDescription(cellNode, row, dataContext, colDef)
    {
        clearTimeout(h_postGetProductDescriptions);

        if (dataContext['d'] === undefined) {
            //console.log('req:'+dataContext['n']);
            postProcessId.push(dataContext['id']);
            h_postGetProductDescriptions = setTimeout(asyncGetProductDescriptions, 500);

        }


    }


    
    function updateHeaderRow() {
        
        for (var i = 0; i < columns.length; i++) {
            
            if (columns[i].id !== "selector") {
                var header = grid.getHeaderRowColumn(columns[i].id);
                var w = columns[i].width - 16;
                
                $(header).empty();
                $("<input type='text'>")
                    .attr("placeholder", (columns[i].name != "<input type='checkbox'>") ? columns[i].name : '')
                    .data("columnId", columns[i].id)
                    .width(w)
                    .keyup(function (e) {
                        var val = $.trim($(this).val());
                        if (val == '')
                            columnFilters[$(this).data("columnId")] = undefined;
                        else {
                            columnFilters[$(this).data("columnId")] = new RegExp(val, 'i');
                        }
                    })
                    .appendTo(header);
            }
        }
    }




    $(document).ready(function () {

        if (grid === undefined) {

            dataView = new Slick.Data.DataView();


            grid = new Slick.Grid($("#prodGrid"), dataView, columns, options);
            //var pager = new Slick.Controls.Pager(dataView, grid, $("#pager"));
            grid.registerPlugin(new Slick.AutoTooltips());
            grid.setSelectionModel(new Slick.RowSelectionModel());
            //grid.setSortColumn("supplier_name", true );

            grid.setSelectionModel(new Slick.RowSelectionModel({selectActiveRow: false}));
            grid.registerPlugin(checkboxSelector);
//            var columnpicker = new Slick.Controls.ColumnPicker(columns, grid, options);

            // wire up model events to drive the grid
            grid.onCellChange.subscribe(function (e, args) {
                //dataView.updateItem(args.item[dataView.getIdProperty()], args.item);
                var column = grid.getColumns()[args.cell];
                if(args.cell == 1){
                    update_product_by_suppliername(args.item, column.field);
                }else{
                    update_product(args.item, column.field);
                }
                
            });

            dataView.onRowCountChanged.subscribe(function (e, args) {
                grid.updateRowCount();
                grid.render();
            });

            dataView.onRowsChanged.subscribe(function (e, args) {
                grid.invalidateRows(args.rows);
                grid.render();

            });

            
             /*grid.onSelectedRowsChanged.subscribe(function(e, args) {
             
             var item = grid.getDataItem(args.rows[0]);
             
             update_product_details_view(item);
             
             });
             
             
             grid.onDblClick.subscribe(function (e) {
             
             
             var item = grid.getDataItem(grid.getCellFromEvent(e).row);
             update_product_details_view(item);
             
             }); */
             
            grid.onSort.subscribe(function (e, args) {
                sortdir = args.sortAsc ? 1 : -1;
                sortcol = args.sortCol.field;


                // using native sort with comparer
                // preferred method but can be very slow in IE with huge datasets
                dataView.sort(comparer, args.sortAsc);

            });
            updateHeaderRow();

        }


        $("#suppliers").change(function () {
            supplier_id_filter = $(this).val();
            dataView.refresh();
        });

        $("#product_name").keyup(function () {

            product_name_filter = new RegExp($(this).val(), 'i');
            dataView.refresh();
        });
        
        <?php $supplierid = (uri_assoc('supplier_id') != NULL)?'?supplier_id='.uri_assoc('supplier_id'):''; ?>
        
        var show_archieve = '';
        <?php if($this->input->get('show_archived') != NULL): ?>
            var show_archieve = '?show_archived=true';
        <?php endif; ?>
        
        <?php if($this->input->get('only_archived') != NULL): ?>
            var show_archieve = '?only_archived=true';
        <?php endif; ?>
        
        
        
        $.post("<?php echo site_url('products/get_products_JSON'.$supplierid); ?>"+show_archieve, {
        }, function (data) {
            if(data == 'session_expired'){
                window.location.reload();
            }
            dataView.beginUpdate();

            suppliers = data.suppliers;
            var products = data.products;

            // Update supplier indexing
            for (var i = 0, l = suppliers.length; i < l; i++) {
                var id = suppliers[i]['client_id'];
                suppliersById[id] = i;
            }


            for (var i = 0, l = products.length; i < l; i++) {
                update_product_supplier_details(products[i]);
            }


            dataView.setItems(products, 'id');
            dataView.setFilter(filter);
            dataView.endUpdate();


        }, "json");
        
        

    });

</script>

