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
  </style>
<div id="myGrid" style="width:50%;height:500px;padding-left: 15px;"></div>
<script>
    var grid2;
    var columns2 = [
        {id: "id", name: "Id", field: "id",width: '100'},
        {id: "n", name: "Product Name", field: "n",width: '400'},
    ];
    var options2 = {
        autoEdit: true,
        enableCellNavigation: true,
        enableColumnReorder: false,
        enableAsyncPostRender: true,
        showHeaderRow: true,
        editable: true
    };
    $(document).ready(function () {

        $.post("<?php echo site_url('products/get_products_JSON'); ?>", {
        }, function (data) {
            if (data == 'session_expired') {
                window.location.reload();
            }
            var products = data.products;
            grid2 = new Slick.Grid($("#myGrid"), products, columns2, options2);

        }, "json");
    });


</script>