<?php $this->load->view('dashboard/header'); ?>



<?php $this->load->view('dashboard/jquery_clear_password'); ?>

<div class="container_10" id="center_wrapper">

	<div class="grid_7" id="content_wrapper">

		<div class="section_wrapper">

			<h3 class="title_black"><?php echo $this->lang->line('client_form'); ?></h3>

			<?php $this->load->view('dashboard/system_messages'); ?>

			<div class="content toggle">

				
			<?php $this->load->view('tab_details'); ?>
				

			</div>

		</div>

	</div>
</div>

<?php $this->load->view('dashboard/sidebar', array('side_block'=>'clients/sidebar')); ?>

<?php $this->load->view('dashboard/footer'); ?>