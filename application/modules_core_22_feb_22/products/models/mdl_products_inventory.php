<?php

(defined('BASEPATH')) OR exit('No direct script access allowed');

class Mdl_Products_Inventory extends MY_Model {

    public function __construct() {

        parent::__construct();

        $this->table_name = 'mcb_products_inventory';

        $this->select_fields = "
		SQL_CALC_FOUND_ROWS
		mcb_products_inventory.*";

        $this->primary_key = 'mcb_products_inventory.id';
    }

    public function get_by_product_id($id) {

        $select = "
            SQL_CALC_FOUND_ROWS
               p.*";

        $this->db->select($select, FALSE);
        $this->db->where('p.product_id', $id);

        $query = $this->db->get('mcb_products_inventory AS p');


        return $query->result();
    }

    public function delete() {
        $ids = $this->input->post('deleteInventory');
        $product_name = $this->input->post('product_name');
        $this->load->model('mdl_products');
        $product_id = $this->mdl_products->get_product_by_name($product_name)->product_id;
        foreach ($ids as $id) {
            parent::delete(array('inventory_id' => $id, 'product_id' => $product_id));
        }
    }

    public function save() {
        $selectedInventories = $this->input->post('selectedInventory');
        $qtyInventories = $this->input->post('qtyInventory');
        $product_name = $this->input->post('product_name');
        $this->load->model('mdl_products');
//        $product_id = $this->mdl_products->get_product_by_name($product_name)->product_id;
        
        echo '<pre>';
        print_r($this->mdl_products->get_product_by_name($product_name));
        die;
        
        if (is_array($selectedInventories)) {
            $i = 0;
            foreach ($selectedInventories as $selectedInventory) {
                $inventory_qty = $qtyInventories[$i] ? $qtyInventories[$i] : '1';
                $array = array(
                    'product_id' => $product_id,
                    'inventory_id' => $selectedInventory,
                    'inventory_qty' => $inventory_qty
                );
                parent::save($array, uri_assoc('id'));
                $i++;
            }
        }
    }

}

?>
