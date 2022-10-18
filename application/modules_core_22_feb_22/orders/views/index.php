<?php $this->load->view('dashboard/header'); ?>
<style>
    .slick-headerrow-column.ui-state-default{
        padding: 3px !important;
    }
    .slick-headerrow-column input{
        border-width: 3px;
    }
</style>
<?php echo modules::run('orders/order_widgets/generate_dialog'); ?>
<div class="grid_12" id="content_wrapper">
    <div class="section_wrapper">
        <h3 class="title_black"><?php echo $this->lang->line('orders'); ?>
            <?php
            $this->load->view('dashboard/btn_add', array('btn_name' => 'btn_add_order',
                'btn_value' => $this->lang->line('create_order')
            ));
            ?>
            <form method="post" style="float: right; margin-right: 5px" action="<?= site_url('orders/download_all_csv'); ?>">
                <input type="submit" name="" value="CSV »">
            </form>
        </h3>
        <div class="content toggle no_padding">
            <?php $this->load->view('dashboard/system_messages'); ?>
            <!-- --><?php //$this->load->view('order_table'); ?>
            <?php $this->load->view('order_grid'); ?>
        </div>
    </div>
</div>
<?php /* $this->load->view('dashboard/sidebar', array('side_block'=>'orders/sidebar')); */ ?>
<?php $this->load->view('dashboard/footer'); ?>