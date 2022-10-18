<?php

(defined('BASEPATH')) OR exit('No direct script access allowed');

class Order_Items extends Admin_Controller {

    function __construct() {

        parent::__construct();
        $this->_post_handler();
        $this->load->model('mdl_order_items');
        $this->load->model('mdl_orders');
    }

    function form() {
        $order_item_id = uri_assoc('order_item_id', 4);
        $order_id = uri_assoc('order_id', 4);
        if (!$this->mdl_order_items->validate()) {


            if (!$_POST AND $order_item_id) {

                $this->mdl_order_items->prep_validation($order_item_id);
            }

            $this->load->model('mdl_orders');

            

            $order = $this->mdl_orders->get_by_id($order_id);
            $data = array(
                'order' => $order,
                'order_currency' => $this->mdl_orders->get_order_currency($order_id),
            );
            
            $orderDetail = $this->get_Row('mcb_orders', array('order_id'=>$order_id));
            if( $orderDetail->is_inventory_supplier == '1' ){
                $this->config->set_item('ORDERTO', 'INVENTORYSUPPLIER');
            }
            if($this->config->item('ORDERTO') ==  'INVENTORYSUPPLIER'){
                $data['order_item'] = $this->mdl_orders->get_order_item_detail($order_item_id);
                $this->load->view('order_inventory_item_form', $data);
            }else{
                $data['order_item_qty'] = $this->mdl_order_items->get_qty_based_on_dynamic_or_not(uri_assoc('order_item_id', 4));
                $this->load->view('order_item_form', $data);
            }
            
            
        } else {
            $db_array = $this->mdl_order_items->db_array();
            
            $this->mdl_order_items->save($db_array, $order_item_id);
            //record in history
            $data = array(
                'user_id' => $this->session->userdata('user_id'),
                'order_id' => $order_id,
                'created_date' => date('Y-m-d H:i:s'),
                'order_history_data' => 'Order item added.'
            );
            $this->db->insert('mcb_order_history', $data);
            
            $this->session->set_flashdata('tab_index', 1);

            redirect($this->session->userdata('last_index'));
        }
    }
    
    public function updateOrderItemInv(){
        $order_id = $this->input->post('order_id');
        $this->load->model('mdl_orders');
        if($this->mdl_orders->updateOrderItemInv()){
            $this->session->set_flashdata('custom_success', 'Order item updated.');
        }else{
            $this->session->set_flashdata('custom_error', 'Could not update order item.');
        }
        redirect('orders/edit/order_id/'.$order_id);
    }
    
    function pro_sup_stok_in($order_id) {
        //error_reporting(E_ALL); ini_set('display_errors', 1);
        $this->load->model('mdl_orders');
        $order_number = $this->mdl_orders->getOrderNumber($order_id);
        $order_items = $this->mdl_orders->get_order_itemsbyid($order_id);
        //$productId = $order->product_id;  
        //$itemQuantity = $order->item_qty;
        if (sizeof($order_items) > 0) {
            foreach ($order_items as $o_item) {
                $productId = $o_item->product_id;
                $product_inventory = $this->mdl_orders->get_Where('mcb_products_inventory', array('product_id' => $productId));
                $itemQuantity = $o_item->item_qty;
                $itemLength = $o_item->item_length;
                
                foreach ($product_inventory as $invtry) {
                    
                    $inventory = $this->mdl_orders->get_Row('mcb_inventory_item', array('inventory_id' => $invtry['inventory_id']));
                    if( (($inventory->use_length) == '1') && ($itemLength > '0') ){
                        $addedQty = ($inventory->qty) + $itemQuantity * $invtry['inventory_qty'] * $itemLength;
                        $opn_order_qty = ($inventory->open_order_qty) - ($itemQuantity * $invtry['inventory_qty'] * $itemLength);
                    }else{
                        $addedQty = ($inventory->qty) + $itemQuantity * $invtry['inventory_qty'];
                        $opn_order_qty = ($inventory->open_order_qty) - ($itemQuantity * $invtry['inventory_qty']);
                    }
                    $this->mdl_orders->update('mcb_inventory_item', array('qty' => $addedQty, 'open_order_qty'=>$opn_order_qty), array('inventory_id' => $invtry['inventory_id']));
                    //going to put history if the inventory qty is taken in or stock in
                    // +ve $addedQty
                    $data = array(
                        'history_id' => '0',
                        'inventory_id' => $invtry['inventory_id'],
                        'history_qty' => $itemQuantity * $invtry['inventory_qty'],
                        'notes' => 'Stock In <a href="'.site_url('orders/edit/order_id/'.$order_id).'">Order #'.$order_number.'</a>',
                        'user_id' => $this->session->userdata('user_id'),
                        'created_at' => date('Y-m-d H:i:s')
                    );
                    $this->db->insert('mcb_inventory_history',$data);
                }
            }
        }
        $this->mdl_orders->update('mcb_orders',array('stock_in_status'=>'1'),array('order_id'=>$order_id));
        $this->session->set_flashdata('custom_success', 'Stock In Accepted.');
        redirect($this->session->userdata('last_index'));
    }
    
    function pro_sup_stok_out($order_id) {
        
        $this->load->model('mdl_orders');
        $order_number = $this->mdl_orders->getOrderNumber($order_id);
        $order_items = $this->mdl_orders->get_order_itemsbyid($order_id);
        //$productId = $order->product_id;  
        //$itemQuantity = $order->item_qty;
        if (sizeof($order_items) > 0) {
            foreach ($order_items as $o_item) {
                $productId = $o_item->product_id;
                $product_inventory = $this->mdl_orders->get_Where('mcb_products_inventory', array('product_id' => $productId));
                $itemQuantity = $o_item->item_qty;
                $itemLength = $o_item->item_length;
                foreach ($product_inventory as $invtry) {

                    $inventory = $this->mdl_orders->get_Row('mcb_inventory_item', array('inventory_id' => $invtry['inventory_id']));
                    if(($inventory->use_length) == '1'){
                        $addedQty = ($inventory->qty) - $itemQuantity * $invtry['inventory_qty'] * $itemLength;
                        $opn_order_qty = ($inventory->open_order_qty) + ($itemQuantity * $invtry['inventory_qty'] * $itemLength);
                    }else{
                        $addedQty = ($inventory->qty) - $itemQuantity * $invtry['inventory_qty'];
                        $opn_order_qty = ($inventory->open_order_qty) + ($itemQuantity * $invtry['inventory_qty']);
                    } 
                    $this->mdl_orders->update('mcb_inventory_item', array('qty' => $addedQty, 'open_order_qty'=>$opn_order_qty), array('inventory_id' => $invtry['inventory_id']));
                    
                    //going to put history if the inventory qty is taken out or revert back
                    // -ve $addedQty
                    $data = array(
                        'history_id' => '0',
                        'inventory_id' => $invtry['inventory_id'],
                        'history_qty' => '-'.$itemQuantity * $invtry['inventory_qty'],
                        'notes' => 'Stocked Out (Reverting Stock In) <a href="'.site_url('orders/edit/order_id/'.$order_id).'">Order #'.$order_number.'</a>',
                        'user_id' => $this->session->userdata('user_id'),
                        'created_at' => date('Y-m-d H:i:s')
                    );
                    $this->db->insert('mcb_inventory_history',$data);
                }
            }
        }
        $this->mdl_orders->update('mcb_orders',array('stock_in_status'=>'0'),array('order_id'=>$order_id));
        $this->session->set_flashdata('custom_success', 'Stock out Accepted.');
        redirect($this->session->userdata('last_index'));
    }
    
    
    function inv_sup_stok_in($orderDetail) {
        
        $order_id = $orderDetail->order_id;
        $order_number = $orderDetail->order_number;
        
        $order_inventory_items = $this->query_object("SELECT * FROM `mcb_order_inventory_items` WHERE `order_id`='".$order_id."'");
        foreach ($order_inventory_items as $value) {
            $inventory = $this->mdl_orders->get_Row('mcb_inventory_item', array('inventory_id' => $value->inventory_id));
            if( (($inventory->use_length) == '1') && ($value->item_length > '0') ){
                $addedQty = ($inventory->qty) + $value->item_qty * $value->item_length;
                $opn_order_qty = ($inventory->open_order_qty) - ($value->item_qty * $value->item_length);
            }else{
                $addedQty = ($inventory->qty) + $value->item_qty;
                $opn_order_qty = ($inventory->open_order_qty) - ($value->item_qty);
            }
            $this->mdl_orders->update('mcb_inventory_item', array('qty' => $addedQty, 'open_order_qty'=>$opn_order_qty), array('inventory_id' => $value->inventory_id));
            //going to put history if the inventory qty is taken in or stock in
            // +ve $addedQty
            $data = array(
                'history_id' => '0',
                'inventory_id' => $value->inventory_id,
                'history_qty' => $value->item_qty,
                'notes' => 'Stock In <a href="'.site_url('orders/edit/order_id/'.$order_id).'">Order #'.$order_number.'</a>',
                'user_id' => $this->session->userdata('user_id'),
                'created_at' => date('Y-m-d H:i:s')
            );
            $this->db->insert('mcb_inventory_history',$data);
        }
        $this->mdl_orders->update('mcb_orders',array('stock_in_status'=>'1'),array('order_id'=>$order_id));
        $this->session->set_flashdata('custom_success', 'Stock In Accepted.');
        redirect($this->session->userdata('last_index'));
    }
    
    function inv_sup_stok_out($orderDetail) {
        
        $order_id = $orderDetail->order_id;
        $order_number = $orderDetail->order_number;
        
        $order_inventory_items = $this->query_object("SELECT * FROM `mcb_order_inventory_items` WHERE `order_id`='".$order_id."'");
        foreach ($order_inventory_items as $value) {
            $inventory = $this->mdl_orders->get_Row('mcb_inventory_item', array('inventory_id' => $value->inventory_id));
            if( (($inventory->use_length) == '1') && ($value->item_length > '0') ){
                $addedQty = ($inventory->qty) - $value->item_qty * $value->item_length;
                $opn_order_qty = ($inventory->open_order_qty) + ($value->item_qty * $value->item_length);
            }else{
                $addedQty = ($inventory->qty) - $value->item_qty;
                $opn_order_qty = ($inventory->open_order_qty) + ($value->item_qty);
            }
            $this->mdl_orders->update('mcb_inventory_item', array('qty' => $addedQty, 'open_order_qty'=>$opn_order_qty), array('inventory_id' => $value->inventory_id));
            //going to put history if the inventory qty is taken in or stock in
            // +ve $addedQty
            $data = array(
                'history_id' => '0',
                'inventory_id' => $value->inventory_id,
                'history_qty' => '-'.$value->item_qty,
                'notes' => 'Stock Out <a href="'.site_url('orders/edit/order_id/'.$order_id).'">Order #'.$order_number.'</a>',
                'user_id' => $this->session->userdata('user_id'),
                'created_at' => date('Y-m-d H:i:s')
            );
            $this->db->insert('mcb_inventory_history',$data);
        }
        $this->mdl_orders->update('mcb_orders',array('stock_in_status'=>'0'),array('order_id'=>$order_id));
        $this->session->set_flashdata('custom_success', 'Stock Out Accepted.');
        redirect($this->session->userdata('last_index'));
    }
    
    function showstockout(){
        $order_id = uri_assoc('order_id', 4);
        $orderDetail = $this->get_Row('mcb_orders', array('order_id' => $order_id));
        if($orderDetail->is_inventory_supplier == '1') {
            $this->inv_sup_stok_out($orderDetail);
        } else {
            $this->pro_sup_stok_out($order_id);
        }
    }
    
    function showstock(){    
        $order_id = uri_assoc('order_id', 4);
        $orderDetail = $this->get_Row('mcb_orders', array('order_id' => $order_id));
        if($orderDetail->is_inventory_supplier == '1') {
            $this->inv_sup_stok_in($orderDetail);
        } else {
            $this->pro_sup_stok_in($order_id);
        }
    }
    
    
    function order_item_stock_change($order_id, $item_id) {
        
        $orderDetail = $this->mdl_orders->get_Row('mcb_orders', array('order_id' => $order_id));
        if ($orderDetail->is_inventory_supplier == '1') {
            
            $this->inv_sup_item_stok_change($orderDetail, $item_id, $this->input->post('partial_qty'));
        } else {
            
            $this->pro_sup_item_stok_change($orderDetail, $item_id, $this->input->post('partial_qty'));
        }
        
        $this->session->set_flashdata('tab_index', 1);
        redirect(site_url('orders/edit/order_id/'.$order_id));
    }
    
    
    function inv_sup_item_stok_change($orderDetail, $item_id, $qty = 0) {
        $order_id = $orderDetail->order_id;
        $order_number = $orderDetail->order_number;
        
        $value = $this->mdl_orders->get_Row('mcb_order_inventory_items', array('order_item_id' => $item_id));

        $totalQty = $value->item_qty;
        $value->item_qty = $qty ?: $value->partial_qty;
        $new_qty = $value->stock_status == '0' ? $value->partial_qty + $qty : 0;
        
        if ($value->stock_status == '0') {
            $inventory = $this->mdl_orders->get_Row('mcb_inventory_item', array('inventory_id' => $value->inventory_id));
            
            if($inventory != NULL){
                
                if( (($inventory->use_length) == '1') && ($value->item_length > '0') ){
                    $value->item_qty = ($value->item_qty * $value->item_length);
                    $opn_order_qty = ($inventory->open_order_qty) - ($value->item_qty * $value->item_length);
                }
                $this->mdl_orders->update('mcb_inventory_item', 
                        array(
                            'qty' => ($inventory->qty) + ($value->item_qty),
                            'open_order_qty'=> ($inventory->open_order_qty) - ($value->item_qty)
                            ),
                        array('inventory_id' => $value->inventory_id));
                //going to put history if the inventory qty is taken in or stock in
                // +ve $addedQty
                $data = array(
                    'history_id' => '0',
                    'inventory_id' => $value->inventory_id,
                    'history_qty' => $value->item_qty,
                    'notes' => 'Stock In <a href="'.site_url('orders/edit/order_id/'.$order_id).'">Order #'.$order_number.'</a>',
                    'user_id' => $this->session->userdata('user_id'),
                    'created_at' => date('Y-m-d H:i:s')
                );
                $this->db->insert('mcb_inventory_history',$data);
                $value->stock_status = ($new_qty - $totalQty >= 0) ? '1' : '0';
                $this->session->set_flashdata('custom_success', 'Stock In Accepted.');
            } else {
                $this->session->set_flashdata('custom_error', 'This item is missing or removed.');
            }
            
            
        } else {
            $inventory = $this->mdl_orders->get_Row('mcb_inventory_item', array('inventory_id' => $value->inventory_id));
            
            if($inventory != NULL){
                
                if( (($inventory->use_length) == '1') && ($value->item_length > '0') ){
                    $value->item_qty = $value->item_qty * $value->item_length;
                }
                $this->mdl_orders->update('mcb_inventory_item', 
                        array('qty' => ($inventory->qty)-($value->item_qty),
                                'open_order_qty'=>($inventory->open_order_qty) + ($value->item_qty)),
                        array('inventory_id' => $value->inventory_id));
                //going to put history if the inventory qty is taken in or stock in
                // +ve $addedQty

                $data = array(
                    'history_id' => '0',
                    'inventory_id' => $value->inventory_id,
                    'history_qty' => '-'.$value->item_qty,
                    'notes' => 'Stock Out <a href="'.site_url('orders/edit/order_id/'.$order_id).'">Order #'.$order_number.'</a>',
                    'user_id' => $this->session->userdata('user_id'),
                    'created_at' => date('Y-m-d H:i:s')
                );
                $this->db->insert('mcb_inventory_history',$data);
                $value->stock_status = '0';
                $this->session->set_flashdata('custom_success', 'Stock Out Accepted.');
            }else{
                $this->session->set_flashdata('custom_error', 'This item is missing or removed.');
            }
            
        }
        $this->mdl_orders->update('mcb_order_inventory_items', array('stock_status' => $value->stock_status, 'partial_qty' => $new_qty), array('order_item_id' => $item_id));

        $this->change_order_status('mcb_order_inventory_items', $order_id);

    }
    
    function pro_sup_item_stok_change($orderDetail, $item_id, $qty = 0) {
        
        $order_id = $orderDetail->order_id;
        $order_number = $orderDetail->order_number;        
        $value = $this->mdl_orders->get_Row('mcb_order_items', array('order_item_id' => $item_id));
        //$itemQuantity = $value->item_qty;
        $itemQuantity = $qty ?: $value->partial_qty;
        $new_qty = $value->stock_status == '0' ? $value->partial_qty + $qty : 0;
        
        $itemLength = $value->item_length;
        
        if ($value->stock_status == '0') {
            
            $product_inventory = $this->mdl_orders->get_Where('mcb_products_inventory', array('product_id' => $value->product_id));
            
            foreach ($product_inventory as $invtry) {
                $inventory = $this->mdl_orders->get_Row('mcb_inventory_item', array('inventory_id' => $invtry['inventory_id']));
                if( (($inventory->use_length) == '1') && ($itemLength > '0') ){
                    $addedQty = ($inventory->qty) + $itemQuantity * $invtry['inventory_qty'] * $itemLength;
                    $opn_order_qty = ($inventory->open_order_qty) - ($itemQuantity * $invtry['inventory_qty'] * $itemLength);
                }else{
                    $addedQty = ($inventory->qty) + $itemQuantity * $invtry['inventory_qty'];
                    $opn_order_qty = ($inventory->open_order_qty) - ($itemQuantity * $invtry['inventory_qty']);
                }
                $this->mdl_orders->update('mcb_inventory_item', array('qty' => $addedQty, 'open_order_qty'=>$opn_order_qty), array('inventory_id' => $invtry['inventory_id']));
                //going to put history if the inventory qty is taken in or stock in
                // +ve $addedQty
                $data = array(
                    'history_id' => '0',
                    'inventory_id' => $invtry['inventory_id'],
                    'history_qty' => $itemQuantity * $invtry['inventory_qty'],
                    'notes' => 'Stock In <a href="'.site_url('orders/edit/order_id/'.$order_id).'">Order #'.$order_number.'</a>',
                    'user_id' => $this->session->userdata('user_id'),
                    'created_at' => date('Y-m-d H:i:s')
                );
                $this->db->insert('mcb_inventory_history',$data);
            }
            $value->stock_status = ($new_qty - $value->item_qty >= 0) ? '1' : '0';
            $this->session->set_flashdata('custom_success', 'Stock In Accepted.');
            
        } else {
            
            $product_inventory = $this->mdl_orders->get_Where('mcb_products_inventory', array('product_id' => $value->product_id));
            foreach ($product_inventory as $invtry) {
                $inventory = $this->mdl_orders->get_Row('mcb_inventory_item', array('inventory_id' => $invtry['inventory_id']));
                if( (($inventory->use_length) == '1') && ($itemLength > '0') ){
                    $addedQty = ($inventory->qty) - $itemQuantity * $invtry['inventory_qty'] * $itemLength;
                    $opn_order_qty = ($inventory->open_order_qty) + ($itemQuantity * $invtry['inventory_qty'] * $itemLength);
                }else{
                    $addedQty = ($inventory->qty) - $itemQuantity * $invtry['inventory_qty'];
                    $opn_order_qty = ($inventory->open_order_qty) + ($itemQuantity * $invtry['inventory_qty']);
                }
                $this->mdl_orders->update('mcb_inventory_item', array('qty' => $addedQty, 'open_order_qty'=>$opn_order_qty), array('inventory_id' => $invtry['inventory_id']));
                //going to put history if the inventory qty is taken in or stock in
                // +ve $addedQty
                $data = array(
                    'history_id' => '0',
                    'inventory_id' => $invtry['inventory_id'],
                    'history_qty' => '-'.$itemQuantity * $invtry['inventory_qty'],
                    'notes' => 'Stock Out <a href="'.site_url('orders/edit/order_id/'.$order_id).'">Order #'.$order_number.'</a>',
                    'user_id' => $this->session->userdata('user_id'),
                    'created_at' => date('Y-m-d H:i:s')
                );
                $this->db->insert('mcb_inventory_history',$data);
            }
            $value->stock_status = '0';
            $this->session->set_flashdata('custom_success', 'Stock In Accepted.');
        }
        $this->mdl_orders->update('mcb_order_items', array('stock_status' => $value->stock_status, 'partial_qty' => $new_qty), array('order_item_id' => $item_id));  
        
        $this->change_order_status('mcb_order_items', $order_id);
    }
    
    // Changing status of orders table to open(1) if any of its child's stock_status is 0 & order_status_id is 3 or close(3) if all of its child's stock status is 1
    function change_order_status($table_name, $order_id) {
        if(!$table_name || !$order_id) return;
        $sql = "select order_id from `$table_name` where order_id = '$order_id'";

        $all_records = $this->db->query($sql);
        // $stock_in = $this->db->query($sql. " AND stock_status = '0'");
        $stock_out = $this->db->query($sql. " AND stock_status = '1'");
        
        $total_records = $all_records->num_rows();
        // $total_stock_in = $stock_in->num_rows();
        $total_stock_out = $stock_out->num_rows();

        // if($total_stock_in > 0) {
        //     $this->mdl_orders->update('mcb_orders', array('order_status_id' => 1), array('order_id' => $order_id, 'order_status_id' => 3));
        //     if($this->db->affected_rows() > 0) {
        //         $data = array(
        //             'user_id' => $this->session->userdata('user_id'),
        //             'order_id' => $order_id,
        //             'created_date' => date('Y-m-d H:i:s'),
        //             'order_history_data' => 'Order Status changed to Open (Automatically due to item stock changed to Stock In).'
        //         );
        //         $this->db->insert('mcb_order_history', $data);
        //     }
        // }elseif($total_records == $total_stock_out) {
        if($total_records == $total_stock_out) {
            $this->mdl_orders->update('mcb_orders', array('order_status_id' => 3), array('order_id' => $order_id));
            if($this->db->affected_rows() > 0){
                $data = array(
                    'user_id' => $this->session->userdata('user_id'),
                    'order_id' => $order_id,
                    'created_date' => date('Y-m-d H:i:s'),
                    'order_history_data' => 'Order Status changed to Closed (Automatically due to all stock item changed to Stock Out).'
                );
                $this->db->insert('mcb_order_history', $data);
            }
        }
    }
    
    function delete() {
        $order_item_id = uri_assoc('order_item_id', 4);
        if ($order_item_id) {
            log_message('INFO', 'Deleting order_item: ' . $order_item_id);
            $this->mdl_order_items->delete($order_item_id);
        }
        $this->session->set_flashdata('tab_index', 1);
        redirect($this->session->userdata('last_index'));
    }

    function _post_handler() {
        if ($this->input->post('btn_cancel')) {
            redirect($this->session->userdata('last_index'));
        } elseif ($this->input->post('btn_add_order_item')) {
            redirect('order_items/form');
        }
    }
    
    function sortitems(){
        $items = $this->input->post('item');
        if(sizeof($items) > 0){
            $i = 0;
            foreach($items as $item){
                $this->mdl_order_items->updateOrderItemPosition($i,$item);
                $i++;
            }
        }
        echo TRUE;
        exit;
    }
    
    function get_Row($table, $condition) {
        $data = $this->mdl_orders->get_Row($table,$condition);
        return $data;
    }
    
    function query_object($qry) {
        return $this->mdl_orders->query_object($qry);
    }
}

?>
