<?php

(defined('BASEPATH')) OR exit('No direct script access allowed');

class Mdl_Order_Items extends MY_Model {

    public function __construct() {

        parent::__construct();
        if($this->config->item('ORDERTO') ==  'INVENTORYSUPPLIER'){
            $this->table_name = 'mcb_order_inventory_items';
            $this->primary_key = 'mcb_order_inventory_items.order_item_id';
        }else{
            $this->table_name = 'mcb_order_items';
            $this->primary_key = 'mcb_order_items.order_item_id';
        }
        

        $this->select_fields = "
		SQL_CALC_FOUND_ROWS *";
    }

    public function validate() {

        $this->form_validation->set_rules('product_id', $this->lang->line('product_id'));
        $this->form_validation->set_rules('item_name', $this->lang->line('item_name'), 'required');
        $this->form_validation->set_rules('item_type', $this->lang->line('item_type'));
        $this->form_validation->set_rules('item_description', $this->lang->line('item_description'), 'required');
        $this->form_validation->set_rules('item_qty', $this->lang->line('quantity'), 'required');
        $this->form_validation->set_rules('item_supplier_price', $this->lang->line('unit_price'), 'required');

        return parent::validate($this);
    }

    public function db_array() {

        $db_array = parent::db_array();

        //echo '<pre>'; print_r($db_array); exit;

        $db_array['order_id'] = uri_assoc('order_id', 4);

        return $db_array;
    }

    public function save($db_array, $order_item_id = NULL) {
        $err_msg = [];
        $warning_msg = [];
        /* Transform these two vars to standard number format */
        $db_array['item_qty'] = standardize_number($db_array['item_qty']);
        $db_array['item_supplier_price'] = standardize_number($db_array['item_supplier_price']);
        $db_array['is_edited'] = '1';
        parent::save($db_array, $order_item_id);

        $db_array = $this->db_array();

        $this->load->model('products/mdl_products_inventory');
        $this->load->model('inventory/mdl_inventory_history');
        $this->load->model('inventory/mdl_inventory_item');

        $inventories = $this->mdl_products_inventory->get_by_product_id($this->input->post('product_id'));
        foreach ($inventories as $inventory) {
//            var_dump($db_array['item_qty']);
//            die();
            $result = $this->mdl_inventory_history->inventory_qty_order_deduct($inventory->inventory_id, "-" . ($inventory->inventory_qty * $db_array['item_qty']), "Order # " . $db_array['order_id']);
            if ($result == 'Negative') {
                $name = $this->mdl_inventory_item->get_by_id($inventory->inventory_id)[0]->name;
                $err_msg[] = $name . " is negative ininventory";
            } elseif ($result == 'Low') {
                $name = $this->mdl_inventory_item->get_by_id($inventory->inventory_id)[0]->name;
//                $warning_msg[] = $name . " is low in inventory";
            }
        }
        if (count($err_msg) > 0) {
            $this->session->set_flashdata('order_error', $err_msg);
        }
        if (count($warning_msg) > 0) {
            $this->session->set_flashdata('order_warning', $warning_msg);
        }
    }

    public function delete($order_item_id) {

        $this->db->where('order_item_id', $order_item_id);
        
        if($this->config->item('ORDERTO') ==  'INVENTORYSUPPLIER'){
            $this->db->delete('mcb_order_inventory_items');
        }else{
            $this->db->delete('mcb_order_items');
        }
        

        $this->session->set_flashdata('success_delete', TRUE);
    }

    public function updateOrderItemPosition($order, $itemid) {
        
        $chkTbl = $this->get_row('mcb_order_inventory_items', array('order_item_id'=>$itemid));
        
        $this->db->where('order_item_id', $itemid);
        $data = array(
            'item_order' => $order
        );
        if($chkTbl != NULL){
            return $this->db->update('mcb_order_inventory_items', $data);
        }else{
            return $this->db->update('mcb_order_items', $data);
        }
        
        //        if($this->config->item('ORDERTO') ==  'INVENTORYSUPPLIER'){
        //            return $this->db->update('mcb_order_inventory_items', $data);
        //        }else{
        //            return $this->db->update('mcb_order_items', $data);
        //        }
    }
    
    function get_qty_based_on_dynamic_or_not($order_item_id) {
        $sql = "select * from mcb_order_items where order_item_id = '" . $order_item_id . "'";

        $q = $this->db->query($sql);
        $res = $q->row();
        if($res->is_edited == '1'){
            return $res->item_qty;
        }elseif((int)$res->item_length > 0){
            return $res->item_length;
        }
        return $res->item_qty;
    }
    
    function get_row($tbl_name, $condition) {    
        $this->db->where($condition);
        $q = $this->db->get($tbl_name);
        $Res = $q->row();
        return $Res ;
    }
    

}

?>
