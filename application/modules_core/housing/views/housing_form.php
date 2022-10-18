<?php $this->load->view('dashboard/header'); ?>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
<div class="container_12" id="center_wrapper">

	<div class="grid_12" id="content_wrapper">

		<div class="section_wrapper">

			<h3 class="title_black"><?php echo $this->lang->line('housing_form'); ?></h3>

			<?php $this->load->view('dashboard/system_messages'); ?>

			<div class="content toggle">

				<form method="post" action="<?php echo site_url($this->uri->uri_string()); ?>">
				<dl>
					<dt><label><?php echo $this->lang->line('product_name'); ?>: </label></dt>
					<dd>
						<select name="product_id" id="product_id">
                            <option value="<?= $housing->product_id; ?>" selected="true"><?php echo $housing->item_name; ?></option>
	                    </select>
					</dd>
				</dl>

				<dl>
					<dt><label><?php echo $this->lang->line('housing_label'); ?>: </label></dt>
					<dd>
	                    <select name="link_products[]" id="link_products" multiple="true">
                            <?php foreach ($housing_link_products as $product): ?>
                                <option value="<?= $product->product_id; ?>" selected="true"><?php echo $product->item_name; ?></option>
                            <?php endforeach; ?>
	                    </select>
	                </dd>
				</dl>
				
				<dl>
					<dt><label><?php echo $this->lang->line('notes'); ?></label></dt>
					<dd style="vertical-align: top;"><textarea name="notes" id="notes" rows="5" cols="40"><?php echo form_prep($housing->notes); ?></textarea></dd>
				</dl>
				
				<input type="submit" id="btn_submit" name="btn_submit" value="<?php echo $this->lang->line('submit'); ?>" />
				<input type="submit" id="btn_cancel" name="btn_cancel" value="<?php echo $this->lang->line('cancel'); ?>" />

				</form>

			</div>

		</div>

	</div>
</div>


<?php $this->load->view('dashboard/sidebar'); ?>

<?php $this->load->view('dashboard/footer'); ?>
<script type="text/javascript">
	$(document).ready(function () {
		let housing_products = [<?php echo $housing->link_products;?>];
		let product_id = '<?php echo $housing->product_id;?>';

		$('#product_id').select2({
			ajax: {
			    url: "<?php echo site_url('housing/search_autocomplete'); ?>",
			    type: "POST",
			    dataType: 'json',
			    delay: 250,
			    cache: true,
			    processResults: function (data) {
			      return {
			        results: data.items
			      };
			    }
		  	},
		  	width: '400',
		  	minimumInputLength: 2,
		    placeholder: 'Search products',
		    language: {
			  	inputTooShort: function() {
			  		return 'Please enter 2 or more characters to get list of items to select.';
			  	}
		  	}
		    // templateSelection: formatRepoSelection
		});
		$('#link_products').select2({
			ajax: {
			    url: "<?php echo site_url('housing/search_autocomplete'); ?>",
			    type: "POST",
			    dataType: 'json',
			    delay: 250,
			    cache: true,
			    processResults: function (data) {
			      return {
			        results: data.items
			      };
			    }
		  	},
	  		width: '400',
		  	minimumInputLength: 2,
		    placeholder: 'Search products',
		    language: {
			  	inputTooShort: function() {
			  		return 'Please enter 2 or more characters to get list of items to select.';
			  	}
		  	}
		});
    });
</script>
