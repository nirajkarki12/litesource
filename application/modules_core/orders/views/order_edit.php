<?php $this->load->view('dashboard/header', array('header_insert' => 'orders/order_edit_header')); ?>

<?php echo modules::run('orders/order_widgets/generate_dialog'); ?>

<?php $this->load->view('dashboard/jquery_date_picker'); ?>

<script type="text/javascript">
    $(function () {
        $('#tabs').tabs({selected: <?php echo $tab_index; ?>});
    });
</script>

<div class="grid_12" id="content_wrapper">

    <form method="post" action="<?php echo site_url($this->uri->uri_string()); ?>">

        <div class="section_wrapper">


            <h3 class="title_black"><?php echo $order->client_name . ' &ndash; ' . $this->lang->line('order_number') . ' ' . $order->order_number; ?>
                <?php if ($order->order_status_type == 1) { ?>

                    <?php
                    
                            $order_id = $this->uri->segment(5);
                            $this->load->model('mdl_orders');


                    if ($order->stock_in_status == '1'){ ?>
                    <?php // $this->load->view('dashboard/btn_add', array('btn_name' => 'btn_stock_out', 'btn_value' => $this->lang->line('stock_out'))); ?>
                    <?php }else{ ?>
                        <?php // $this->load->view('dashboard/btn_add', array('btn_name' => 'btn_stock_in', 'btn_value' => $this->lang->line('stock_in'))); ?>
                    <?php } ?>
                
                    
                    <?php $this->load->view('dashboard/btn_add', array('btn_name' => 'btn_delivery_address', 'btn_value' => $this->lang->line('change_delivery_address'))); ?>
                    <?php // $this->load->view('dashboard/btn_add', array('btn_name' => 'btn_add_order_item', 'btn_value' => $this->lang->line('add_order_item'))); ?>
                <?php } ?>
                <?php $this->load->view('dashboard/btn_add', array('btn_name' => 'btn_download_pdf', 'btn_value' => $this->lang->line('pdf_download'))); ?>
                <?php $this->load->view('dashboard/btn_add', array('btn_name' => 'btn_send_email', 'btn_value' => $this->lang->line('send_email'))); ?>
            </h3>
            
            <div class="no_product" style="background: black; color: #fff; padding-bottom: 10px;">
                <?php if(sizeof($order_products_missing_inv) > 0): ?>
                <h4>Products missing inventory</h4>
                <?php foreach($order_products_missing_inv as $mi): ?>
                    <h5 style="font-size: 13px;"><?php echo '<a href="'.site_url('products/form/product_id/'.$mi->product_id).'" style="color: white; text-decoration: underline;">'.$mi->product_name.'</a>'; ?></h5>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <?php $this->load->view('dashboard/system_messages'); ?>

            <div class="content toggle">

                <div id="tabs">
                    <ul>
                        <li><a href="#tab_general"><?php echo $this->lang->line('summary'); ?></a></li>
                        <li><a href="#tab_items"><?php echo $this->lang->line('items'); ?></a></li>
                        <li><a href="#tab_delivery"><?php echo $this->lang->line('delivery'); ?></a></li>
                        <li><a href="#tab_history"><?php echo $this->lang->line('history'); ?></a></li>
                    </ul>
                    <div id="tab_general">
                        <?php $this->load->view('tab_general'); ?>
                    </div>
                    <div id="tab_items">
                        <?php $this->load->view('order_item_table'); ?>
                    </div>
                    <div id="tab_delivery">
                        <div class="delivery_detail">
                            <h3>Delivery</h3>
                            <?php $this->load->view('addresses/address_details'); ?>
                        </div>
                    </div>					
                    <div id="tab_history">
                        <?php $this->load->view('order_history'); ?>
                    </div>
                    
                   
                    
                </div>

                <div style="clear: both;">&nbsp;</div>

            </div>

        </div>

    </form>

</div>
<script type="text/javascript">
    $(document).ready(function () {
        $('table.order_det_items tbody').sortable({
            axis: 'y',
            update: function (event, ui) {
                var data = $(this).sortable('serialize');

                // POST to server using $.post or $.ajax
                $.ajax({
                    data: data,
                    type: 'POST',
                    url: '<?php echo site_url() . '/orders/order_items/sortitems' ?>',
                    success: function (data) {
                        if (data == 'session_expired') {
                            window.location.reload();
                        }
                    }
                });
            }
        });
    });
    
    
</script>
<?php $this->load->view('dashboard/footer'); ?>