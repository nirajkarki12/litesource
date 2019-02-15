<?php $this->load->view('dashboard/header'); ?>
<?php echo modules::run('orders/order_widgets/generate_dialog'); ?>

<div class="grid_12" id="content_wrapper">

	<div class="section_wrapper">

		<h3 class="title_black"><?php echo $this->lang->line('orders'); ?>
			<?php $this->load->view('dashboard/btn_add', 
                                array('btn_name'=>'btn_add_order',
                                    'btn_value'=>$this->lang->line('create_order')
                                )); ?>
		</h3>

		<div class="content toggle no_padding">

			<?php $this->load->view('dashboard/system_messages'); ?>

<!--			--><?php //$this->load->view('order_table'); ?>
            <?php $this->load->view('order_grid'); ?>

		</div>

	</div>

</div>

<?php /* $this->load->view('dashboard/sidebar', array('side_block'=>'orders/sidebar')); */ ?>

<?php $this->load->view('dashboard/footer'); ?>