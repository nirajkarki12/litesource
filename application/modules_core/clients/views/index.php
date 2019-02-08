<?php $this->load->view('dashboard/header'); ?>

<div class="grid_12" id="content_wrapper">

	<div class="section_wrapper">

		<h3 class="title_black">
			<?php echo $this->lang->line('clients'); ?>
			<?php $this->load->view('dashboard/btn_add', array('btn_name'=>'btn_add_client', 'btn_value'=>$this->lang->line('add_client'))); ?>
		</h3>

		<div class="content toggle no_padding">
			<?php $this->load->view('dashboard/system_messages'); ?>

			<?php $this->load->view('client_list'); ?>
		</div>

	</div>

</div>

<?php //$this->load->view('dashboard/sidebar', array('side_block'=>'clients/sidebar')); ?>

<?php $this->load->view('dashboard/footer'); ?>