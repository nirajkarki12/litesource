<?php $this->load->view('dashboard/header', array('header_insert'=>'suppliers/details_header')); ?>


<script type="text/javascript">
	$(function(){
		$('#tabs').tabs({ selected: <?php echo $tab_index; ?> });
	});
</script>

<div class="container_10" id="center_wrapper">

	<div class="grid_10" id="content_wrapper">

		<div class="section_wrapper">

			<h3 class="title_black"><?php echo $supplier->client_name; ?>
				
				<form method="post" action="<?php echo site_url($this->uri->uri_string()); ?>" style="display: inline;">
				<input type="submit" name="btn_add_product" style="float: right; margin-top: 10px; margin-right: 10px;" value="<?php echo $this->lang->line('add_product'); ?>" />
				</form>

			</h3>

			<div class="content toggle">

				<div id="tabs">

					<ul>
						<li><a href="#tab_supplier"><?php echo $this->lang->line('supplier'); ?></a></li>
						<li><a href="#tab_products"><?php echo $this->lang->line('products'); ?></a></li>
					</ul>

					<div id="tab_supplier">

						<div class="left_box">

							<dl>
								<dt><?php echo $this->lang->line('street_address'); ?>: </dt>
                            <dd><?php echo $supplier->client_address; ?><?php if ($supplier->client_address_2) { ?><br /><?php echo $supplier->client_address_2;} ?></dd>
							</dl>

							<dl>
								<dt><?php echo $this->lang->line('city'); ?>: </dt>
								<dd><?php echo $supplier->client_city; ?></dd>
							</dl>

							<dl>
								<dt><?php echo $this->lang->line('state'); ?>: </dt>
								<dd><?php echo $supplier->client_state; ?></dd>
							</dl>

							<dl>
								<dt><?php echo $this->lang->line('zip'); ?>: </dt>
								<dd><?php echo $supplier->client_zip; ?></dd>
							</dl>

							<dl>
								<dt><?php echo $this->lang->line('country'); ?>: </dt>
								<dd><?php echo $supplier->client_country; ?></dd>
							</dl>

							<dl>
								<dt><?php echo $this->lang->line('email_address'); ?>: </dt>
								<dd><?php echo auto_link($supplier->client_email_address); ?></dd>
							</dl>

							<dl>
								<dt><?php echo $this->lang->line('web_address'); ?>: </dt>
								<dd><?php echo auto_link($supplier->client_web_address, 'both', TRUE); ?></dd>
							</dl>

							<dl>
								<dt><?php echo $this->lang->line('phone_number'); ?>: </dt>
								<dd><?php echo $supplier->client_phone_number; ?></dd>
							</dl>

							<dl>
								<dt><?php echo $this->lang->line('fax_number'); ?>: </dt>
								<dd><?php echo $supplier->client_fax_number; ?></dd>
							</dl>

							<dl>
								<dt><?php echo $this->lang->line('mobile_number'); ?>: </dt>
								<dd><?php echo $supplier->client_mobile_number; ?></dd>
							</dl>

						</div>

						<div class="right_box">
							<dl>
								<dt><?php echo $this->lang->line('supplier_description'); ?>: </dt>
								<dd><?php echo nl2br($supplier->supplier_description); ?></dd>
							</dl>

						</div>

						<div style="clear: both;">&nbsp;</div>


					</div>


					<div id="tab_products">
						<?php $this->load->view('products/product_table'); ?>
					</div>

				</div>

			</div>

		</div>

	</div>

</div>

<?php $this->load->view('dashboard/footer'); ?>