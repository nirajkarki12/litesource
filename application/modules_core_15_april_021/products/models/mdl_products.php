<?php

(defined('BASEPATH')) OR exit('No direct script access allowed');

class Mdl_Products extends MY_Model {

    public function __construct() {

        parent::__construct();

        $this->table_name = 'mcb_products';

        $this->select_fields = "
		SQL_CALC_FOUND_ROWS
		mcb_products.*,
		mcb_clients.client_id AS client_id,
		mcb_clients.client_name AS client_name,
		IFNULL(parent_suppliers.client_id, mcb_clients.client_id) supplier_id,
		IFNULL(parent_suppliers.client_name, mcb_clients.client_name) supplier_name,
		mcb_currencies.currency_code,
		mcb_currencies.currency_symbol_left,
		mcb_currencies.currency_symbol_right,
		CONCAT(
            mcb_currencies.currency_symbol_left,
			FORMAT(mcb_products.product_supplier_price,2),
			mcb_currencies.currency_symbol_right) AS supplier_price";

        $this->joins = array(
            'mcb_clients' => 'mcb_clients.client_id = mcb_products.supplier_id',
            'mcb_currencies' => 'mcb_currencies.currency_id = mcb_clients.client_currency_id',
            'mcb_clients AS parent_suppliers' => array(
                'parent_suppliers.client_id = mcb_clients.parent_client_id',
                'left'
            )
        );

        $this->primary_key = 'mcb_products.product_id';

        $this->order_by = 'supplier_name, product_name';
    }

    public function get_active() {

        $params = array(
            'where' => array(
                'product_active' => 1
            )
        );

        return $this->get($params);
    }

    public function get_product_descriptions($product_ids) {


        $this->db->select('product_id, product_description');
        $this->db->where_in('product_id', $product_ids);


        return $this->db->get($this->table_name)->result();
    }

    public function get_by_supplier($supplier_id) {

        $params = array(
            'where' => array(
                'supplier_id' => $supplier_id,
                'product_active' => 1
            )
        );

        return $this->get($params);
    }

    public function search_by_supplier($supplier_id, $search_string) {

        $params = array(
            'select' => "
		        SQL_CALC_FOUND_ROWS
		        mcb_products.product_id,
				mcb_products.product_name AS value,
				mcb_products.product_supplier_code,
				mcb_products.product_supplier_description,
				mcb_products.product_supplier_price",
            'order_by' => 'product_name'
        );

        $like = "(product_name LIKE '%" . $search_string . "%' OR product_description LIKE '%" . $search_string . "%')";

        $this->db->where('supplier_id', $supplier_id);
        $this->db->where('mcb_products.product_active', 1);
        $this->db->where($like);

        $this->db->limit(10);

        return $this->get($params);
    }

    /*
     * Wild card search for products where the search term
     * will be used to match the supplier, product name or description
     */
/* fixed archive issue.. */
    public function getSearchResults($search_string) {
        
        // this is only for product
//        $qry = "SELECT DISTINCT
//		        p.product_id AS id,
//				p.product_name AS value,
//				p.product_description AS description,
//				p.product_base_price AS base_price 
//                                FROM mcb_products as p 
//                                INNER JOIN mcb_inventory_item as i ON p.supplier_id = i.supplier_id 
//                                WHERE (ifnull(nullif(p.is_arichved,0),'0') = '0') AND (product_name LIKE '%" . $search_string . "%' OR product_description LIKE '%" . $search_string . "%') AND i.inventory_type='1' 
//                                ORDER BY product_name LIMIT 10";
        // this is only for all
        $qry = "SELECT DISTINCT
		        p.product_id AS id,
				p.product_name AS value,
				p.product_description AS description,
				p.product_base_price AS base_price 
                                FROM mcb_products as p 
                                INNER JOIN mcb_inventory_item as i ON p.supplier_id = i.supplier_id 
                                WHERE (p.quote_status<'1') AND (ifnull(nullif(p.is_arichved,0),'0') = '0') AND (product_name LIKE '%" . $search_string . "%' OR product_description LIKE '%" . $search_string . "%') ORDER BY product_name LIMIT 10";
        //exit();
        $q = $this->db->query($qry);
        $q_res = $q->result();
        
        $temp_name = array();
        $final_res = array();
        if( $q_res != NULL ){
            foreach ($q_res as $key => $qr) {
                if( $qr->value != '' ){
                    if( !in_array($qr->value, $temp_name) ){   
                        $temp_name[] = $qr->value;
                        $final_res[] = $qr;
                    }   
                }
            }
        }
        //return $q->result();
        return $final_res;
//        $params = array(
//            'select' => "
//		        SQL_CALC_FOUND_ROWS
//		        mcb_products.product_id AS id,
//				mcb_products.product_name AS value,
//				mcb_products.product_description AS description,
//				mcb_products.product_base_price AS base_price",
//            'order_by' => 'product_name'
//        );
//
//        $like = "(product_name LIKE '%" . $search_string . "%' OR product_description LIKE '%" . $search_string . "%')";
//
//        $this->db->where('mcb_clients.client_active', 1);
//        $this->db->where('mcb_products.product_active', 1);
//        $this->db->where($like);
//
//        $this->db->limit(10);
//
//        return $this->get($params);
    }

    public function get_product_by_name($name) {

        $this->db->where('product_name', $name);


        $query = $this->db->get($this->table_name);

        if ($query->num_rows() > 0) {

            return $query->row();
            ;
        }

        /*
          $params = array(
          //'debug' => 1,
          'return_row' => 1,
          'where'	=>	array(
          'mcb_products.product_name'	=>	$name
          )
          );

          return $this->get($params);
         */
    }

    public function update_product_supplier($product) {

        $product_id = $product->product_id;


        /*
         * We update the 'real' supplier of the product even though
         * the supplier id shown is the parent
         * ** CONFUSING OH YES. 
         */
        $this->load->model('clients/mdl_clients');

        //$client_id = $product->client_id; // use same if new one not found

        $client = $this->mdl_clients->get_by_name($product->client_name);

        if ($client) {
            $client_id = $client->client_id;
        }

        $db_set = array(
            'supplier_id' => $client_id,
            'product_name' => $product->product_name,
            'product_description' => $product->product_description,
            'product_supplier_code' => $product->product_supplier_code,
            'product_supplier_price' => $product->product_supplier_price,
            'product_base_price' => $product->product_base_price,
            'product_last_changed' => gmdate("Y-m-d H:i:s"),
        );

        $this->db->where('product_id', $product_id);
        $this->db->set($db_set);

        $this->db->update($this->table_name);

        return $this->db->affected_rows();
    }

    public function update_product($product) {

        $product_id = $product->product_id;

        /*
         * We update the 'real' supplier of the product even though
         * the supplier id shown is the parent
         * ** CONFUSING OH YES. 
         */
        $this->load->model('clients/mdl_clients');

        //$client_id = $product->client_id; // use same if new one not found
        $name = (isset($product->client_name) && $product->client_name != '')?$product->client_name:$product->supplier_name;
        $client = $this->mdl_clients->get_by_name($name);

        if ($client) {
            $client_id = $client->client_id;
        }

        $db_set = array(
            'supplier_id' => $client_id,
            'product_name' => $product->product_name,
            'product_description' => $product->product_description,
            'product_supplier_code' => $product->product_supplier_code,
            'product_supplier_price' => $product->product_supplier_price,
            'product_base_price' => $product->product_base_price,
            'product_last_changed' => gmdate("Y-m-d H:i:s"),
        );
        

        $this->db->where('product_id', $product_id);
        $this->db->set($db_set);

        $this->db->update($this->table_name);

        return $this->db->affected_rows();
    }

    public function validate() {

//        $this->form_validation->set_rules('supplier_id', $this->lang->line('supplier_id'), 'required');
        $this->form_validation->set_rules('product_active', $this->lang->line('product_active'));
        $this->form_validation->set_rules('product_dynamic', $this->lang->line('product_dynamic'));
        $this->form_validation->set_rules('product_name', $this->lang->line('product_name'), 'required');
//        $this->form_validation->set_rules('product_supplier_code', $this->lang->line('product_supplier_code'), 'required');
//        $this->form_validation->set_rules('product_supplier_description', $this->lang->line('product_supplier_description'), 'required');
        $this->form_validation->set_rules('product_description', $this->lang->line('product_description'), 'required');
//        $this->form_validation->set_rules('product_supplier_price', $this->lang->line('product_supplier_price'), 'required');
        $this->form_validation->set_rules('product_base_price', $this->lang->line('product_base_price'), 'required');


        return parent::validate($this);
    }

    public function delete($product_id) {

        //Can only delete product if never sold via invoicing 
        //$this->load->model('products/mdl_products');

        /* Delete the supplier record */
        parent::delete(array('product_id' => $product_id));

        /*
         * Delete any related products, but use the product model so records
         * related to the product are also deleted
         */

        /*
          $this->db->select('product_id');

          $this->db->where('supplier_id', $supplier_id);

          $products = $this->db->get('mcb_products')->result();

          foreach ($products as $product) {

          $this->mdl_products->delete($product->product_id);

          }
         */
    }

    public function save() {

        $db_array = parent::db_array();

        if (!$this->input->post('product_active')) {

            $db_array['product_active'] = 0;
        }
        if (!$this->input->post('product_dynamic')) {

            $db_array['product_dynamic'] = 0;
			
        }
		
			$db_array['is_arichved'] = "0";
        return parent::save($db_array, uri_assoc('product_id'));
    }

    public function getProductList($where = '') {
        $sql = "SELECT mcb_products.product_id id,mcb_products.product_name n, mcb_products.product_supplier_code c, mcb_products.product_supplier_price b, mcb_products.product_base_price p, mcb_products.supplier_id s
                FROM (mcb_products)
                left JOIN mcb_clients ON mcb_clients.client_id = mcb_products.supplier_id AND mcb_products.supplier_id != '0' 
                left JOIN mcb_currencies ON mcb_currencies.currency_id = mcb_clients.client_currency_id
                LEFT JOIN mcb_clients AS parent_suppliers ON parent_suppliers.client_id = mcb_clients.parent_client_id " . $where . " 
                ORDER BY mcb_clients.client_name, product_name";

        $q = $this->db->query($sql);

        return $q->result();
    }

    public function getProductDetail($product_id) {
        $sql = "SELECT SQL_CALC_FOUND_ROWS
 mcb_products.*, mcb_clients.client_id AS client_id, mcb_clients.client_name AS client_name, IFNULL(parent_suppliers.client_id, mcb_clients.client_id) supplier_id, IFNULL(parent_suppliers.client_name, mcb_clients.client_name) supplier_name, mcb_currencies.currency_code, mcb_currencies.currency_symbol_left, mcb_currencies.currency_symbol_right, CONCAT(
 mcb_currencies.currency_symbol_left, FORMAT(mcb_products.product_supplier_price, 2), mcb_currencies.currency_symbol_right) AS supplier_price
FROM (mcb_products)
LEFT JOIN mcb_clients ON mcb_clients.client_id = mcb_products.supplier_id
LEFT JOIN mcb_currencies ON mcb_currencies.currency_id = mcb_clients.client_currency_id
LEFT JOIN mcb_clients AS parent_suppliers ON parent_suppliers.client_id = mcb_clients.parent_client_id
WHERE mcb_products.product_name != '' AND `mcb_products`.`product_id` = '" . $product_id . "'
ORDER BY supplier_name, product_name";

        
        $q = $this->db->query($sql);
        $result = $q->row_array();
        return $result;
    }

    public function getAllProductsEvenWithZeroSupplier($condition) {
        $sql = "SELECT mcb_products.product_id id, mcb_clients.client_name supplier_name ,mcb_products.product_name n, mcb_products.product_supplier_code c, 
            mcb_products.product_supplier_price b, mcb_products.product_base_price p, mcb_products.supplier_id s, mcb_products.is_arichved
            FROM (mcb_products)
            left JOIN mcb_clients ON mcb_clients.client_id = mcb_products.supplier_id
            left JOIN mcb_currencies ON mcb_currencies.currency_id = mcb_clients.client_currency_id
            LEFT JOIN mcb_clients AS parent_suppliers ON parent_suppliers.client_id = mcb_clients.parent_client_id 
            ".$condition." ORDER BY mcb_clients.client_name, product_name";

        $q = $this->db->query($sql);
        return $q->result();
    }
    
    function m_m_link_product() {
//        $sql = "SELECT mcb_products.product_id id, mcb_clients.client_name supplier_name ,mcb_products.product_name n, 
//            mcb_products.product_supplier_code c, 
//            mcb_products.product_supplier_price b, mcb_products.product_base_price p, mcb_products.supplier_id s,
//            (SELECT COUNT(*) 
//            FROM mcb_products_inventory_raw as ii 
//            WHERE ii.product_id = mcb_products.product_id order by ii.inventory_id) as inventorycount
//            FROM (mcb_products)
//            left JOIN mcb_clients ON mcb_clients.client_id = mcb_products.supplier_id
//            left JOIN mcb_currencies ON mcb_currencies.currency_id = mcb_clients.client_currency_id
//            LEFT JOIN mcb_clients AS parent_suppliers ON parent_suppliers.client_id = mcb_clients.parent_client_id 
//            where mcb_products.product_name != '' AND mcb_products.is_arichved != '1'
//            ORDER BY mcb_clients.client_name, product_name";
        
        $sql = "SELECT mii.inventory_id AS id, mcb_clients.client_name AS supplier_name, "
                . "mii.name AS n, mii.supplier_code AS c, mii.base_price AS p, mii.supplier_price AS b, mii.supplier_id AS s, "
                . "(SELECT COUNT(*) FROM `mcb_products_inventory` WHERE mcb_products_inventory.product_id = mii.inventory_id) AS inventorycount "
                . "FROM `mcb_inventory_item` AS mii "
                . "left JOIN mcb_clients ON mcb_clients.client_id = mii.supplier_id "
                . "WHERE `mii`.`inventory_type` = '1' AND `mii`.`is_arichved` != '1'";
        
        $q = $this->db->query($sql);
//        echo $this->db->last_query();;
        $result = $q->result();
        return $result;
    }
    
    public function getAllProductsEvenWithZeroSupplierLink() {
        $sql = "SELECT mcb_products.product_id id, mcb_clients.client_name supplier_name ,mcb_products.product_name n, 
            mcb_products.product_supplier_code c, 
            mcb_products.product_supplier_price b, mcb_products.product_base_price p, mcb_products.supplier_id s,
            (SELECT COUNT(*) 
            FROM mcb_products_inventory as ii 
            WHERE ii.product_id = mcb_products.product_id order by ii.inventory_id) as inventorycount
            FROM (mcb_products)
            left JOIN mcb_clients ON mcb_clients.client_id = mcb_products.supplier_id
            left JOIN mcb_currencies ON mcb_currencies.currency_id = mcb_clients.client_currency_id
            LEFT JOIN mcb_clients AS parent_suppliers ON parent_suppliers.client_id = mcb_clients.parent_client_id 
            where mcb_products.product_name != '' AND mcb_products.is_arichved != '1'
            ORDER BY mcb_clients.client_name, product_name";
        $q = $this->db->query($sql);
        
        //echo $this->db->last_query();;
        
        $result = $q->result();
        
//        echo '<pre>';
//        print_r($result);
        
        
        return $result;
//        
//        $fin = array();
//        if (sizeof($result) > 0) {
//            foreach ($result as $res) {
//
//                $inventories = $this->getInventoriesofProduct($res->id);
//                if (sizeof($inventories) > 0) {
//                    $popHtml = '<div class="inv-detail">';
//                    $popHtml .= '<h2>'.$res->n.'</h2><br/>';
//
//                    $popHtml .= '<h3>Inventory Items:</h3>';
//                    $popHtml .= '<table class="table table-bordered order-prod-list">';
//
//                    $popHtml .= '<thead>';
//                    $popHtml .= '<tr>';
//                    $popHtml .= '<td>S.N</td>';
//                    $popHtml .= '<td>Inventory Item Name</td>';
//                    $popHtml .= '<td>Product Relation Qty</td>';
//                    $popHtml .= '</tr>';
//                    $popHtml .= '</thead>';
//                    $popHtml .= '<tbody>';
//                    $i = 1;
//                    foreach($inventories as $inv){
//                        $popHtml .= '<tr>';
//                        $popHtml .= '<td>'.$i.'</td>';
//                        $popHtml .= '<td>'.$inv->name.'</td>';
//                        $popHtml .= '<td>'.$inv->inventory_qty.'</td>';
//                        $popHtml .= '</tr>';
//                        $i++;
//                    }
//                    
//                    $popHtml .= '</tbody></table></div>';
//                    
//                    $res->n = '<strong>' . $res->n . '</strong><a data-effect="mfp-zoom-in" data-message=\''.$popHtml.'\' class="inv-detail open-popup"><span class="info-link"></span></a>';
//                }
//                $fin[] = $res;
//            }
//        }
//
//        return $fin;
    }

    public function getInventoriesofProduct($productid) {
        $sql = "select r.inventory_id,i.name,r.inventory_qty "
                . "from mcb_products_inventory as r "
                . "left join mcb_inventory_item as i on i.inventory_id = r.inventory_id "
                . "where r.product_id = '" . $productid . "' and i.name != ''"
                . "group by i.inventory_id";
        $q = $this->db->query($sql);
        return $q->result();
    }

    public function getAllProductsUnlinked() {
        $sql = "SELECT mcb_products.product_id id, mcb_clients.client_name supplier_name ,mcb_products.product_name n, mcb_products.product_supplier_code c, 
            mcb_products.product_supplier_price b, mcb_products.product_base_price p, mcb_products.supplier_id s
            FROM (mcb_products)
            left JOIN mcb_clients ON mcb_clients.client_id = mcb_products.supplier_id
            left JOIN mcb_currencies ON mcb_currencies.currency_id = mcb_clients.client_currency_id
            LEFT JOIN mcb_clients AS parent_suppliers ON parent_suppliers.client_id = mcb_clients.parent_client_id where mcb_products.product_id not in (select product_id from mcb_products_inventory order by product_id) AND mcb_products.product_name != ''  
            ORDER BY mcb_clients.client_name, product_name";
            $q = $this->db->query($sql);
        $result = $q->result();
        return $result;
    }
    
    public function checkDuplicate_product_supplier_code($product_name){
        $product_name = trim($product_name);
        $this->db->where('product_name',$product_name);
        $q = $this->db->get('mcb_products');
        
        if($q->num_rows() > 0)
            return TRUE;
        return FALSE;
    }
    
	//get the latest one .. should fix future problems..
    public function checkDuplicate_product_supplier_code_with_return($product_name){
        $product_name = trim($product_name);
        $this->db->where('product_name',$product_name);
        //$this->db->where(array('product_name'=>$product_name, 'is_arichved !='=>'1'));    
		$this->db->order_by('product_id', 'DESC');
        $q = $this->db->get('mcb_products');
        
        if($q->num_rows() > 0)
            return $q->row();
        return FALSE;
    }
    
    public function addNewProductFromQuote($product_array){
        $this->db->insert('mcb_products',$product_array);
		
        return $this->db->insert_id();
		
    }
	
    public function addNewProductFromQuoteToInventory($product_array){
        
        $this->db->insert('mcb_inventory_item',$product_array);
	$inv_id = $this->db->insert_id();	
        if($inv_id > 0){
            $sku_name = "SKU-{$inv_id}";
            $this->common_model->update('mcb_inventory_item', array('sku'=>$sku_name), array('inventory_id'=>$inv_id));
        }
        return $inv_id;
    }
    
    public function getProductDetailById($product_id){
        $sql = "SELECT mcb_products.product_id id, mcb_products.product_name n, mcb_products.product_supplier_code c, mcb_products.product_supplier_price b, mcb_products.product_base_price p, mcb_products.supplier_id s, mcb_products.product_description d
                FROM (mcb_products)
                left JOIN mcb_clients ON mcb_clients.client_id = mcb_products.supplier_id
                left JOIN mcb_currencies ON mcb_currencies.currency_id = mcb_clients.client_currency_id
                LEFT JOIN mcb_clients AS parent_suppliers ON parent_suppliers.client_id = mcb_clients.parent_client_id
                WHERE `mcb_products`.`product_id` = '".$product_id."'
                ORDER BY mcb_clients.client_name, product_name";
        
        $q = $this->db->query($sql);
        return $q->row();
    }

    
    
    function update($tbl_name, $data, $id_update) {
        
            $this->db->where($id_update);
        $this->db->update($tbl_name, $data);
    }
    
    
    public function get_Row($tbl_name, $condition) {
        
            $this->db->where($condition);
        $q = $this->db->get($tbl_name);
        return $q->row();
    }
    
    
    
    
}

?>