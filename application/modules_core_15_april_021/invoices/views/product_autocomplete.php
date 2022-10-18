<script type="text/javascript">

	$(document).ready(function(){

		function log( message ) {
			$( "<div/>" ).text( message ).prependTo( "#log" );
			$( "#log" ).attr( "scrollTop", 0 );
		}
		
		function get_product_data( prod_name ) {
			$.post("<?php echo site_url('products/jquery_product_data'); ?>",{
				
				product_name: prod_name

			}, function(product_data) {
                            if(product_data == 'session_expired'){
                            window.location.reload();
                        }

				var json_data = "product = " + product_data;

				eval(json_data);

				//$('#item_name').val(product.product_name);
				$('#item_price').val(product.product_base_price);
				$('#item_description').val(product.product_description);
				$('#item_qty').val(1);

			});
			return false;
			
		};
		
		
		$("#product_autocomplete").autocomplete({
		    minLength: 3,
			source: function (req, resp) {
                                $.ajax({
                                    url: "<?php echo site_url('products/jquery_search_autocomplete'); ?>",
                                    dataType: 'json',
                                    type: 'POST',
                                    data: req,
                                    success: function (data) {
                                        if(data == 'session_expired'){
                            window.location.reload();
                        }
                                        resp(data.search_results);
                                    }
                                });
                            },
                            select: function (event, ui) {
                                get_product_data(ui.item.label);
                            }
                        });
        

		$("#product_autocomplete").change( function(){
			get_product_data($("#product_autocomplete").val());
			
		});



	});

</script>