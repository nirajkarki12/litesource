<?php $this->load->view('dashboard/header'); ?>

<?php $this->load->view('dashboard/jquery_date_picker'); ?>

<script type="text/javascript">

	
	function supplier_change() {
		var selectedValue = $("#supplier_id").find(":selected").val();
			
		get_client_contacts(selectedValue);
	}
	
	function get_client_contacts(client_id) {
		$.post("<?php echo site_url('clients/ajax_get_contacts'); ?>",{
			client_id: client_id
		}, function(data) {
                    if(data == 'session_expired'){
                            window.location.reload();
                        }
				
			$("#contact_name").autocomplete({ source: data.contacts});
		
		}, "json");
		
	};
	
	$(document).ready(function(){
	
		
		$("#supplier_id").bind('change', function(){
			supplier_change();
			
		});
		supplier_change();
		
	
	});

</script>

<div class="grid_7" id="content_wrapper">

	<div class="section_wrapper">

		<h3 class="title_black"><?php echo $this->lang->line('create_order'); ?></h3>

		<div class="content toggle">
			
			<form method="post" action="<?php echo site_url($this->uri->uri_string()); ?>">

				<dl>
					<dt><label><?php echo $this->lang->line('date'); ?>: </label></dt>
					<dd><input id="datepicker" type="text" name="order_date_entered" value="<?php echo date($this->mdl_mcb_data->setting('default_date_format')); ?>" /></dd>
				</dl>

				<dl>
					<dt><label><?php echo $this->lang->line('supplier'); ?>: </label></dt>
					<dd>
						<select name="supplier_id" id="supplier_id">
						<?php foreach ($suppliers as $supplier) { ?>
						<option value="<?php echo $supplier->client_id; ?>" <?php if ($this->mdl_orders->form_value('supplier_id') == $supplier->client_id) { ?>selected="selected"<?php } ?>><?php echo $supplier->client_name; ?></option>
						<?php } ?>
						</select>
					</dd>
				</dl>
				<dl>
					<dt><label><?php echo $this->lang->line('contact'); ?>: </label></dt>
					<dd>
						<input name="contact_name" type="text" id="contact_name" />
				
					</dd>
					
				</dl>
				<dl>
					<dt><label><?php echo $this->lang->line('project'); ?>: </label></dt>		
					<dd>
						
						<select name="project_id">
							<option value="0"></option>
							
							<?php foreach ($projects as $project) { ?>
							<option value="<?php echo $project->project_id; ?>" ><?php echo $project->project_name; ?></option>
							<?php } ?>

						</select>
						

					</dd>
				</dl>
				<input type="submit" id="btn_submit" name="btn_submit" value="<?php echo $this->lang->line('create_order'); ?>" />
				<input type="submit" id="btn_cancel" name="btn_cancel" value="<?php echo $this->lang->line('cancel'); ?>" />

			</form>

		</div>

	</div>

</div>

<?php // $this->load->view('dashboard/sidebar', array('side_block'=>'orders/sidebar')); ?>

<?php $this->load->view('dashboard/footer'); ?>