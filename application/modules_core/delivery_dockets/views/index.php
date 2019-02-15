<?php $this->load->view('dashboard/header'); ?>

<?php //echo modules::run('orders/order_widgets/generate_dialog'); ?>

<div class="grid_12" id="content_wrapper">

	<div class="section_wrapper">

		<h3 class="title_black"><?php echo $this->lang->line('dockets'); ?>
			<?php $this->load->view('dashboard/btn_add', array('btn_name'=>'btn_add_docket', 'btn_value'=>$this->lang->line('create_docket'))); ?>
		</h3>

		<div class="content toggle no_padding">

			<?php $this->load->view('dashboard/system_messages'); ?>

			<?php $this->load->view('delivery_docket_table'); ?>

		</div>

	</div>

</div>


<?php $this->load->view('dashboard/footer'); ?>