<div class="inventoryGridwrap">
    <div id="myGrid" style="width:100%;height:500px;"></div>
    <div class="loader">
        <img src="<?php echo base_url() . 'assets/style/img/loading.gif'; ?>">
    </div>
</div>
<style>
        .slick-headerrow-column {
      background: #87ceeb;
      text-overflow: clip;
      -moz-box-sizing: border-box;
      box-sizing: border-box;
    }
    .slick-headerrow-column input {
      margin: 0;
      padding: 0;
      width: 100%;
      height: 100%;
      -moz-box-sizing: border-box;
      box-sizing: border-box;
    }
    .pn-hide{
        font-size: 0;
    }
    .slick-group .pn-hide{
            font-size: 100%;
            font-weight: bold;
    }
</style>
<script type="text/javascript">

    QuantityCellEditor = function (args)
    {
        var $input;
        var defaultValue;
        var scope = this;

        this.init = function () {
            $input = $("<INPUT type=number class='editor-text' />");

            $input.bind("keydown.nav", function (e) {
                if (e.keyCode === $.ui.keyCode.LEFT || e.keyCode === $.ui.keyCode.RIGHT) {
                    e.stopImmediatePropagation();
                }
            });

            $input.appendTo(args.container);
            $input.focus().select();
        };

        this.destroy = function () {
            $input.remove();
        };

        this.focus = function () {
            $input.focus();
        };

        this.loadValue = function (item) {
            defaultValue = item[args.column.field];
            $input.val(defaultValue);
            $input[0].defaultValue = defaultValue;
            $input.select();
        };

        this.serializeValue = function () {
            return parseFloat($input.val()) || 0;
        };

        this.applyValue = function (item, state) {
            item[args.column.field] = state;
        };

        this.isValueChanged = function () {
            return (!($input.val() == "" && defaultValue == null)) && ($input.val() != defaultValue);
        };

        this.validate = function () {
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

    var columnFilters = {};
    
    var dataView;
    var grid;
    var data = [];
    var columns = [
        {id: "n", name: "Product Name", field: "pn", width: 200, sortable: true,cssClass: "pn-hide"},
        {id: "ivn", name: "Inventory Name", field: "ivn", width: 600, minWidth: 200, cssClass: "cell-title", sortable: true},
        {id: "qty", name: "QTY", field: "qty", width: 200, sortable: true, editor: QuantityCellEditor},
    ];
    var options = {
        enableCellNavigation: true,
        editable: true,
        showHeaderRow: true,
        headerRowHeight: 30,
        
    explicitInitialization: true
        //explicitInitialization: true
    };
    var sortcol = "title";
    var sortdir = 1;
    var percentCompleteThreshold = 0;
    var prevPercentCompleteThreshold = 0;
    function avgTotalsFormatter(totals, columnDef) {
        var val = totals.avg && totals.avg[columnDef.field];
        if (val != null) {
            return "avg: " + Math.round(val) + "%";
        }
        return "";
    }
    
    
    function sumTotalsFormatter(totals, columnDef) {
        var val = totals.sum && totals.sum[columnDef.field];
        if (val != null) {
            return "total: " + ((Math.round(parseFloat(val) * 100) / 100));
        }
        return "";
    }
    function myFilter(item, args) {
        return item["percentComplete"] >= args.percentComplete;
    }
    function percentCompleteSort(a, b) {
        return a["percentComplete"] - b["percentComplete"];
    }
    function comparer(a, b) {
        var x = a[sortcol], y = b[sortcol];
        return (x == y ? 0 : (x > y ? 1 : -1));
    }
    function groupByProductName() {
        dataView.setGrouping({
            getter: "pn",
            formatter: function (g) {
                return g.value + "  <span style='color:green'>(" + g.count + " items)</span>";
            },
            aggregateCollapsed: false,
        });
    }
    function groupByDurationOrderByCount(aggregateCollapsed) {
        dataView.setGrouping({
            getter: "duration",
            formatter: function (g) {
                return "Duration:  " + g.value + "  <span style='color:green'>(" + g.count + " items)</span>";
            },
            comparer: function (a, b) {
                return a.count - b.count;
            },
            aggregators: [
                new Slick.Data.Aggregators.Avg("percentComplete"),
                new Slick.Data.Aggregators.Sum("cost")
            ],
            aggregateCollapsed: aggregateCollapsed,
            lazyTotalsCalculation: true
        });
    }
    function groupByDurationEffortDriven() {
        dataView.setGrouping([
            {
                getter: "duration",
                formatter: function (g) {
                    return "Duration:  " + g.value + "  <span style='color:green'>(" + g.count + " items)</span>";
                },
                aggregators: [
                    new Slick.Data.Aggregators.Sum("duration"),
                    new Slick.Data.Aggregators.Sum("cost")
                ],
                aggregateCollapsed: true,
                lazyTotalsCalculation: true
            },
            {
                getter: "effortDriven",
                formatter: function (g) {
                    return "Effort-Driven:  " + (g.value ? "True" : "False") + "  <span style='color:green'>(" + g.count + " items)</span>";
                },
                aggregators: [
                    new Slick.Data.Aggregators.Avg("percentComplete"),
                    new Slick.Data.Aggregators.Sum("cost")
                ],
                collapsed: true,
                lazyTotalsCalculation: true
            }
        ]);
    }
    function groupByDurationEffortDrivenPercent() {
        dataView.setGrouping([
            {
                getter: "duration",
                formatter: function (g) {
                    return "Duration:  " + g.value + "  <span style='color:green'>(" + g.count + " items)</span>";
                },
                aggregators: [
                    new Slick.Data.Aggregators.Sum("duration"),
                    new Slick.Data.Aggregators.Sum("cost")
                ],
                aggregateCollapsed: true,
                lazyTotalsCalculation: true
            },
            {
                getter: "effortDriven",
                formatter: function (g) {
                    return "Effort-Driven:  " + (g.value ? "True" : "False") + "  <span style='color:green'>(" + g.count + " items)</span>";
                },
                aggregators: [
                    new Slick.Data.Aggregators.Sum("duration"),
                    new Slick.Data.Aggregators.Sum("cost")
                ],
                lazyTotalsCalculation: true
            },
            {
                getter: "percentComplete",
                formatter: function (g) {
                    return "% Complete:  " + g.value + "  <span style='color:green'>(" + g.count + " items)</span>";
                },
                aggregators: [
                    new Slick.Data.Aggregators.Avg("percentComplete")
                ],
                aggregateCollapsed: true,
                collapsed: true,
                lazyTotalsCalculation: true
            }
        ]);
    }
    
 function filter(item) {
    for (var columnId in columnFilters) {
      if (columnId !== undefined && columnFilters[columnId] !== "") {
        var c = grid.getColumns()[grid.getColumnIndex(columnId)];
        
        var m = item[c.field];
        if(m){
            m = m.toLowerCase();
        }else{
            return false;
        }
        
        var n = columnFilters[columnId];
        if(n){
            n = n.toLowerCase();
        }else{
            return false;
        }
        
        if(m.indexOf(n) >=0){
            return true;
        }
        return false;
      }
    }
    return true;
  }
  
    function loadData() {
        
        $.post("<?php echo site_url('inventory/get_product_inventory'); ?>", {
        }, function (data) {
            if (data == 'session_expired') {
                window.location.reload();
            }
            dataView.beginUpdate();
            grid.init();
            dataView.setItems(data, 'id');
            dataView.setFilter(filter);
            //dataView.sort(comparer, sortDir);
            
            groupByProductName();
            dataView.endUpdate();
            $("#gridContainer").resizable();
            
        }, "json");
        
    }
    $(".grid-header .ui-icon")
            .addClass("ui-state-default ui-corner-all")
            .mouseover(function (e) {
                $(e.target).addClass("ui-state-hover")
            })
            .mouseout(function (e) {
                $(e.target).removeClass("ui-state-hover")
            });
    $(function () {
        var groupItemMetadataProvider = new Slick.Data.GroupItemMetadataProvider();
        dataView = new Slick.Data.DataView({
            groupItemMetadataProvider: groupItemMetadataProvider,
            inlineFilters: true
        });
        grid = new Slick.Grid("#myGrid", dataView, columns, options);
        // register the group item metadata provider to add expand/collapse group handlers
        grid.registerPlugin(groupItemMetadataProvider);
        grid.setSelectionModel(new Slick.CellSelectionModel());
        
        
        //grid.init();
        
        grid.onSort.subscribe(function (e, args) {
            sortdir = args.sortAsc ? 1 : -1;
            sortcol = args.sortCol.field;
            if ($.browser.msie && $.browser.version <= 8) {
                // using temporary Object.prototype.toString override
                // more limited and does lexicographic sort only by default, but can be much faster
                var percentCompleteValueFn = function () {
                    var val = this["percentComplete"];
                    if (val < 10) {
                        return "00" + val;
                    } else if (val < 100) {
                        return "0" + val;
                    } else {
                        return val;
                    }
                };
                // use numeric sort of % and lexicographic for everything else
                dataView.fastSort((sortcol == "percentComplete") ? percentCompleteValueFn : sortcol, args.sortAsc);
            }
            else {
                // using native sort with comparer
                // preferred method but can be very slow in IE with huge datasets
                dataView.sort(comparer, args.sortAsc);
            }
        });
        
         $(grid.getHeaderRow()).delegate(":input", "change keyup", function (e) {
            var columnId = $(this).data("columnId");
            if (columnId != null) {
              columnFilters[columnId] = $.trim($(this).val());
              dataView.refresh();
            }
          });
          grid.onHeaderRowCellRendered.subscribe(function(e, args) {
              $(args.node).empty();
              $("<input type='text'>")
                 .data("columnId", args.column.id)
                 .val(columnFilters[args.column.id])
                 .appendTo(args.node);
          });
        
        //updateHeaderRow();
        
        // wire up model events to drive the grid
        dataView.onRowCountChanged.subscribe(function (e, args) {
            grid.updateRowCount();
            grid.render();
        });
        dataView.onRowsChanged.subscribe(function (e, args) {
            grid.invalidateRows(args.rows);
            grid.render();
        });
        
        grid.onCellChange.subscribe(function (e, args) {
            //dataView.updateItem(args.item[dataView.getIdProperty()], args.item);
            var column = grid.getColumns()[args.cell];

            update_inventory(args.item, column.field);
        });
        
        var h_runfilters = null;
        // wire up the slider to apply the filter to the model
        $("#pcSlider,#pcSlider2").slider({
            "range": "min",
            "slide": function (event, ui) {
                Slick.GlobalEditorLock.cancelCurrentEdit();
                if (percentCompleteThreshold != ui.value) {
                    window.clearTimeout(h_runfilters);
                    h_runfilters = window.setTimeout(filterAndUpdate, 10);
                    percentCompleteThreshold = ui.value;
                }
            }
        });
        function filterAndUpdate() {
            var isNarrowing = percentCompleteThreshold > prevPercentCompleteThreshold;
            var isExpanding = percentCompleteThreshold < prevPercentCompleteThreshold;
            var renderedRange = grid.getRenderedRange();
            dataView.setFilterArgs({
                percentComplete: percentCompleteThreshold
            });
            dataView.setRefreshHints({
                ignoreDiffsBefore: renderedRange.top,
                ignoreDiffsAfter: renderedRange.bottom + 1,
                isFilterNarrowing: isNarrowing,
                isFilterExpanding: isExpanding
            });
            dataView.refresh();
            prevPercentCompleteThreshold = percentCompleteThreshold;
        }
        // initialize the model after all the events have been hooked up
        
        loadData();
        
    });
    
    function update_inventory(item, field)
    {
        $.post("<?php echo site_url('inventory/update_inv_rel_pi'); ?>", {
            post_item: JSON.stringify(item)
            
        }, function (data) {
            if (data == 'session_expired') {
                window.location.reload();
            }
            $('#system_ajax').show();
            //console.log(data);
        }, "json");

    }
    
        function updateHeaderRow() {

        for (var i = 0; i < columns.length; i++) {
//            if (columns[i].name == "<input type='checkbox'>") {
//                return false;
//            }
            if (columns[i].id !== "selector") {
                var header = grid.getHeaderRowColumn(columns[i].id);
                //var w = $(header).width() - 4;
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
                            applyFilter();
                        })
                        .appendTo(header);
            }
        }

    }
</script>