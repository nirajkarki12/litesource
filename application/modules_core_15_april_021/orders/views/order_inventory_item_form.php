<?php $this->load->view('dashboard/header'); ?>

<script type="text/javascript" src="<?php echo base_url(); ?>assets/slick/json2.js"></script>

<div class="grid_12" id="content_wrapper">

	<div class="section_wrapper">

		<h3 class="title_black"><?php echo $this->lang->line('order_number') . ' ' . $order->order_number; ?></h3>

		<?php $this->load->view('dashboard/system_messages'); ?>

		<div class="content toggle">
			
			<form method="post" action="<?php echo site_url('orders/order_items/updateOrderItemInv'); ?>" name="order_item_form">

				<dl>
					<dd>
                                            <input type="hidden" name="inventory_id" id="inventory_id" value="" />
                                            <?php if(isset($order_item->inventory_id)): ?>
                                            <input type="hidden" name="inventory_id" id="inventory_id" value="<?php echo $order_item->inventory_id; ?>" />
                                            <?php else: ?>
                                            <input type="hidden" name="neworderitem" value="1" />
                                            <?php endif; ?>
                                            <input type="hidden" name="order_item_id" id="order_item_id" value="<?php if(isset($order_item->order_item_id)) echo $order_item->order_item_id; ?>" />
                                            <input type="hidden" name="order_id" id="order_id" value="<?php echo $order->order_id; ?>" />
                                        </dd>
				</dl>
				
				<dl>
					<dt><label>Inventory Name: </label></dt>
					<dd><input type="text" required="required" placeholder="Inventory Name" name="inventory_name" id="catalog_number" value="<?php if(isset($order_item->item_name)) echo str_replace(array('<span>','</span>'), '', $order_item->item_name); ?>"/></dd>
				</dl>

				<dl>
					<dt><label><?php echo $this->lang->line('item_type'); ?>: </label></dt>
					<dd><input type="text" autocomplete="off" name="item_type" id="item_type" value="<?php if(isset($order_item->item_type)) echo $order_item->item_type; ?>" /></dd>
				</dl>
				
				<dl>
					<dt><label><?php echo $this->lang->line('item_description'); ?>: </label></dt>
					<dd><textarea name="item_description" id="item_description" rows="5" cols="40"><?php if(isset($order_item->item_description)) echo str_replace(array('<span>','</span>'), '', $order_item->item_description); ?></textarea></dd>
				</dl>				
				
				<dl>
					<dt><label><?php echo $this->lang->line('quantity'); ?>: </label></dt>
					<dd><input type="text" autocomplete="off" name="item_qty" id="item_qty" value="<?php if(isset($order_item->total_qty)) echo $order_item->total_qty; ?>" /></dd>
				</dl>
				
				<dl>
					<dt><label><?php echo $this->lang->line('price').'('.$order_currency->currency_code.')'; ?>: </label></dt>
					<dd><input type="text" autocomplete="off" name="item_supplier_price" required="required" id="item_supplier_price" value="<?php if(isset($order_item->item_supplier_price)) echo $order_item->item_supplier_price; ?>" /></dd>
				</dl>

                            
                            
				<input type="submit" name="btn_submit_item" id="btn_submit" value="<?php echo $this->lang->line('save_item'); ?>" />
				<input type="submit" name="btn_cancel" id="btn_cancel" value="<?php echo $this->lang->line('cancel'); ?>" />

			</form>

		</div>

	</div>

</div>

<script type="text/javascript">
	
	$(document).ready(function(){
		
		$( "#catalog_number" ).autocomplete({
			minLength: 3,
			source: function(req, resp){
				$.ajax({
					url: "<?php echo site_url('inventory/jquery_inventory_by_supplier/supplier_id').'/'.$order->supplier_id; ?>",
					dataType: 'json',
					type: 'POST',
					data: req,
					success: function(data){
                                            if(data == 'session_expired'){
                            window.location.reload();
                        }
                                            resp(data.inventory);
                                        }
				})
			},
			focus: function( event, ui ) {
				$( "#catalog_number" ).val( ui.item.label );
					return false;
			},
			select: function( event, ui ) {
				$( "#product_id" ).val( ui.item.product_id );
                                $( "#inventory_id").val( ui.item['id']);
				$( "#item_name").val( ui.item['product_supplier_code']);
				$( "#item_qty" ).val( 1);
				$( "#item_description" ).val( ui.item['product_supplier_description'] );
				$( "#item_supplier_price" ).val( ui.item['product_supplier_price'] );

				return false;
			}

		})
		.data("autocomplete")._renderItem =  function(ul, item) {
			return $( "<li></li>" )
				.data( "item.autocomplete", item )
				.append( "<a>" + item.value + "<br>" + item.product_supplier_description + "</a>" )
				.appendTo( ul );
		};
	   
	});
	
</script>


<?php $this->load->view('dashboard/footer'); ?>

