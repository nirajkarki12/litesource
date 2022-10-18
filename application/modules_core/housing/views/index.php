<?php $this->load->view('dashboard/header'); ?>

<div class="grid_12" id="content_wrapper">

    <div class="section_wrapper">

        <h3 class="title_black">
            <?php echo $this->lang->line('housing_label'); ?>
            <?php $this->load->view('dashboard/btn_add', array('btn_name' => 'btn_add_housing', 'btn_value' => $this->lang->line('add_new_housing'))); ?>
            <?php $this->load->view('dashboard/btn_add', array('btn_name' => 'btn_bulk_add_housing', 'btn_value' => $this->lang->line('bulk_link_housings'))); ?>
        </h3>
        

        <div class="content toggle no_padding">
            <?php $this->load->view('dashboard/system_messages'); ?>

            <?php $this->load->view('housing_list'); ?>
        </div>

    </div>

</div>

<?php $this->load->view('dashboard/footer'); ?>