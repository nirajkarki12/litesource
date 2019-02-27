<?php

(defined('BASEPATH')) OR exit('No direct script access allowed');

class Mdl_Inventory_Item extends MY_Model {

    public $stock_msg, $stock_color;

    /*
     * The big Query
     * May need to sort out the join against order_items if it is doing a full scan
     *
      SELECT SQL_CALC_FOUND_ROWS
      mcb_orders.*,
      con.contact_name,
      con.email_address AS contact_email_address,
      tax_rate_percent,
      t.tax_rate_name,
      CONCAT(FORMAT(t.tax_rate_percent, 0),'% ', t.tax_rate_name) tax_rate_percent_name,
      FORMAT(order_sub_total, 2) order_sub_total,
      FORMAT(order_sub_total * IFNULL(t.tax_rate_percent,0)/100,2) tax_total,
      FORMAT(order_sub_total * (1 + IFNULL(t.tax_rate_percent,0)/100),2) order_total,
      IFNULL(mcb_invoices.invoice_number, '-') AS invoice_number,
      mcb_clients.*,
      mcb_currencies.*,
      prj.project_id,
      IFNULL(prj.project_name,'-') AS project_name,
      mcb_users.username,
      mcb_users.last_name AS from_last_name,
      mcb_users.first_name AS from_first_name,
      mcb_users.email_address AS from_email_address,
      mcb_users.mobile_number AS from_mobile_number,
      mcb_invoice_statuses.invoice_status AS order_status,
      mcb_invoice_statuses.invoice_status_type AS order_status_type
      FROM
      mcb_orders
      LEFT JOIN (SELECT order_id, SUM(item_qty * item_supplier_price) order_sub_total FROM `mcb_order_items` GROUP BY order_id) AS i ON i.order_id = mcb_orders.order_id
      JOIN mcb_clients ON	mcb_clients.client_id = mcb_orders.supplier_id
      LEFT JOIN mcb_contacts AS con ON con.contact_id = mcb_orders.contact_id
      JOIN mcb_invoice_statuses ON mcb_invoice_statuses.invoice_status_id = mcb_orders.order_status_id
      JOIN mcb_users ON mcb_users.user_id = mcb_orders.user_id
      LEFT JOIN mcb_invoices ON mcb_invoices.invoice_id = mcb_orders.invoice_id
      LEFT JOIN mcb_projects AS prj ON prj.project_id = mcb_invoices.project_id
      LEFT JOIN mcb_tax_rates AS t ON t.tax_rate_id = mcb_orders.order_tax_rate_id
      JOIN mcb_currencies ON mcb_currencies.currency_id = mcb_clients.client_currency_id
     *
     */

    public function __construct() {

        parent::__construct();
        $this->stock_msg = '';
        $this->stock_color = array();
        $this->table_name = 'mcb_inventory_item';

        $this->primary_key = 'mcb_inventory_item.inventory_id';

        //$this->order_by = 'mcb_orders.order_date_entered DESC, mcb_orders.order_id';
        $this->order_by = 'mcb_inventory_item.inventory_id ASC';


        $this->select_fields = "
        SQL_CALC_FOUND_ROWS
        mcb_inventory_item.*";


        $this->joins = array();


        $this->limit = $this->mdl_mcb_data->setting('results_per_page');
    }

    public function get($params = NULL) {

        //$params['group_by'] = 'mcb_orders.order_id';
        //$params['debug'] = TRUE;
        $inventory = parent::get($params);
        return $inventory;
    }

    public function validate() {
        $id = '';
        if ($this->input->post('inventory_id')) {
            $id = $this->input->post('inventory_id');
        }
		
		$this->form_validation->set_rules('supplier_id', $this->lang->line('supplier_id'), 'required');
		
		if ($this->input->post('inventory_type') == 0) {
        $this->form_validation->set_rules('supplier_code', $this->lang->line('supplier_catalog_number'), 'required');
		$this->form_validation->set_rules('supplier_price', $this->lang->line('product_supplier_price'), 'required');
	    $this->form_validation->set_rules('supplier_description', $this->lang->line('inventory_supplier_decsription'), 'required');
		}
        $this->form_validation->set_rules('name', $this->lang->line('inventory_name'), 'required|is_unique[mcb_inventory_item.name.' . $id . ']');
        $this->form_validation->set_rules('base_price', $this->lang->line('inventory_base_price'), 'required');
        $this->form_validation->set_rules('description', $this->lang->line('inventory_description'), 'required');
 
        $this->form_validation->set_rules('location', $this->lang->line('inventory_location'));
        $this->form_validation->set_rules('use_length', 'Use Length');


        return parent::validate($this);
    }
    
    public function get_raw($whereCondition = NULL, $limit = NULL, $offset = NULL) {
        $limittxt = '';
        if($limit){
            $limittxt = 'LIMIT '.$limit;
        }
        $offsettxt = '';
        if($offset){
            $offsettxt = 'OFFSET '.$limit;
        }
        
        $sql = "SELECT SQL_CALC_FOUND_ROWS
			c.client_name, (case when i.inventory_type = '0' then 'Part' else 'Product Group' END) as inventory_type,
                        i.inventory_id id, i.name, 
                        (IF(i.open_order_qty > '0', i.open_order_qty, moi.qty)) as qty_o, 
                        i.supplier_id, i.is_arichved, i.use_length, 
                        i.description, CONCAT('$', FORMAT(i.supplier_price, 2)) supplier_price, i.supplier_code,
			CONCAT('$', FORMAT(i.base_price, 2)) base_price,
                        i.supplier_description, i.location,
                        
                        (IF(i.inventory_type = '1', (SELECT MIN(`mcb_inventory_item`.`qty`) FROM `mcb_inventory_item`
                        LEFT JOIN `mcb_products_inventory` ON `mcb_inventory_item`.`inventory_id` = `mcb_products_inventory`.`inventory_id`
                        WHERE `mcb_products_inventory`.`product_id` = i.inventory_id), i.qty)) as qty
                        
                        FROM mcb_inventory_item AS i 
			left join mcb_clients c on c.client_id=i.supplier_id
                        left join mcb_inventory_open_order_qty as moi on i.inventory_id = moi.inventory_id
                        ".$whereCondition." ORDER BY c.client_name ASC ".$limittxt.' '.$offsettxt;
        
        $query = $this->db->query($sql);
	
        $res = $query->result();
        
//        echo '<pre>';
//        print_r($res);
//        exit;
        foreach ($res as $value2) {
            
            $value2->qty_o = $this->get_opn_ordr_qty_total($value2->id);
//            if($value2->){
//                
//            }
            if((int)$value2->qty == 0 || $value2->qty == NULL){
                $value2->qty = '0';
            }
            
            if((int)$value2->qty_o == 0 || $value2->qty_o == NULL){
                $value2->qty_o = '0';
            }
            
            if($value2->base_price == NULL){
                $value2->base_price = '$0.00';
            }
            
            if($value2->supplier_price == NULL){
                $value2->supplier_price = '$0.00';
            }
            
            
            $arr[] = $value2;
            
        }
//        echo '<pre>';
//        print_r($arr);
//        die;
        return $arr;
        
    }
    
    
    function get_opn_ordr_qty_total($inv_id) {
        
        $sql = "SELECT SUM(IF( (oit.item_length>0),(oit.item_qty*oit.item_length),(oit.item_qty))) AS o_qty "
                . "FROM `mcb_order_inventory_items` AS oit "
                . "LEFT JOIN mcb_orders AS ord ON oit.order_id = ord.order_id "
                . "WHERE oit.inventory_id = '".$inv_id."' AND ord.order_status_id = '2'";
        
//        echo $sql;
        $q = $this->db->query($sql);
        return $q->result()[0]->o_qty;
        
    }
    function get_opn_ordr_qty($inv_id) {
        
        $sql = "SELECT oit.order_item_id, oit.item_qty, oit.item_length, oit.order_id, oit.inventory_id, oit.stock_status, "
                . "ord.order_status_id, ord.order_number "
                . "FROM `mcb_order_inventory_items` AS oit "
                . "LEFT JOIN mcb_orders AS ord ON oit.order_id = ord.order_id "
                . "WHERE oit.inventory_id = '".$inv_id."' AND ord.order_status_id = '2'";
        
//        echo $sql;
        $q = $this->db->query($sql);
        return $q->result();
        
    }
    
    
    public function getUnlinedInventorylist($limit, $offset){
        $sql = "SELECT SQL_CALC_FOUND_ROWS
                i.inventory_id id, i.name, i.supplier_id, i.description, CONCAT('$', FORMAT(i.base_price, 2)) base_price, i.qty, i.supplier_code, i.supplier_description, i.location
                FROM (mcb_inventory_item AS i) where i.inventory_id NOT IN (select inventory_id from mcb_products_inventory group by inventory_id) ORDER BY i.name ASC";

        $q = $this->db->query($sql);
        
        echo '<pre>';
        print_r($q);
        exit;
        
        return $q->result();
    }
    
    
    public function get_inventory_by_productId($id) {
        $select = "SQL_CALC_FOUND_ROWS
            i.inventory_id id,
            p.inventory_qty,
            i.use_length,
            i.name,
            i.supplier_id,
            i.description,
            i.base_price,
            i.qty,
            i.supplier_code,
            i.supplier_description,
            i.location";
        $this->db->select($select, FALSE);
        $this->db->order_by('i.inventory_id DESC');
        $this->db->where('p.product_id', $id);
        $query = $this->db->get('mcb_inventory_item AS i join mcb_products_inventory_raw AS p
            on i.inventory_id = p.inventory_id');
        return $query->result();
    }

    public function get_qty_by_id($id) {
        $select = "
            SQL_CALC_FOUND_ROWS
               i.qty";

        $this->db->select($select, FALSE);
        $this->db->order_by('i.inventory_id DESC');
        $this->db->where('i.inventory_id', $id);

        $query = $this->db->get('mcb_inventory_item AS i');


        return $query->result();
    }

    /**
     * geting the inventory items of a product
     * @param type $product_id
     * @return type object
     */
    public function getInventoryItems($product_id) {
        $this->db->select('pi.inventory_id,ii.qty,ii.name,pi.inventory_qty');
        $this->db->where('pi.product_id', $product_id);
        $this->db->where('pi.inventory_id !=', '0');
        $this->db->from('mcb_products_inventory as pi');
        $this->db->join('mcb_inventory_item as ii', 'ii.inventory_id = pi.inventory_id', 'inner');
        $q = $this->db->get();
        return $q->result();
    }

    /**
     * //getting the quantity in stock
      //this will check the same amount in each inventory items
      //every inventory items should have that amount
      //if there are no inventory items, it will return NULL
     * @param type $product_id
     * @param type $item_qty
     * @return type
     */
    public function getStockQuantiy($product_id, $item_qty, $product_name, $itemQ = array()) {

        //get the number of inventory items
        if ((int) $itemS->product_id > 0) {
            $inventory_items = $this->getInventoryItems($product_id);
            //check if the particular invoice_item_id has already inserted qty which is quite more, going to reduct it assume
            $invoice_item_id_qty = $this->getInvoiceItemIdQtyAlredyInserted($itemQ->invoice_item_id);
            if (sizeof($inventory_items) > 0) {
                foreach ($inventory_items as $item) {
                    // {$item->qty: total_quantity_of_inventory_item} :: {$item_qty: user_submitted_number_of_products} :: {relation between unit product and that particular inventory item} 
                    if (((float) ($item->qty + $invoice_item_id_qty)) - ((float) $item_qty * $item->inventory_qty) < LOW_STOCK) {
                        $this->stock_msg .= $item->name . " stock quantity is low. Total available quantity is " . $item->qty;
                        $this->stock_color[] = 'yellow';
                    } elseif (((float) $item_qty * $item->inventory_qty) > (float) ($item->qty + $invoice_item_id_qty)) {
                        $this->stock_msg .= "Not enough " . $item->name . " inventory item for the product " . $product_name;
                        $this->stock_color[] = 'red';
                    } else {
                        $this->stock_msg .= $item->name . " stock quantity is " . $item->qty;
                        $this->stock_color[] = 'green';
                    }
                }
            } else {
                $this->stock_msg .= 'There are no inventory items for the product ' . $product_name;
                $this->stock_color[] = 'red';
            }
        } else {
            $this->stock_msg .= '<p>Product not defined ' . $itemS->item_name . '</p>';
            $this->stock_color[] = 'yellow';
        }

        return $this->prepareResult();
    }

    public function prepareResult() {
        if (in_array('red', $this->stock_color)) {
            $color = 'red';
        } elseif (in_array('yellow', $this->stock_color)) {
            $color = 'yellow';
        } else {
            $color = 'green';
        }
        return array(
            'color' => $color,
            'message' => $this->stock_msg
        );
    }

    public function getInvoiceItemIdQtyAlredyInserted($invoice_item_id) {
        $this->db->select('item_qty');
        $this->db->where('invoice_item_id', $invoice_item_id);
        $q = $this->db->get('mcb_invoice_items');
        $res = $q->row();
        if (isset($res->item_qty))
            return $res->item_qty;
        return 0;
    }

    public function isMaxOrderQty($product_name) {

        $select = "SQL_CALC_FOUND_ROWS
            MIN(i.qty) as max_qty";
        $this->db->select($select, FALSE);
        $this->db->order_by('i.inventory_id DESC');
        $this->db->where('pd.product_name', $product_name);
        $query = $this->db->get('mcb_inventory_item AS i join mcb_products_inventory AS p
            on i.inventory_id = p.inventory_id join mcb_products as pd on p.product_id = pd.product_id');

        return $query->result();
    }

    public function get_id_by_name($name) {
        $select = "
            SQL_CALC_FOUND_ROWS
               i.inventory_id";

        $this->db->select($select, FALSE);
        $this->db->where('i.name', $name);

        $query = $this->db->get('mcb_inventory_item AS i');


        return $query->result();
    }

    public function get_by_id($id) {
        $select = "
            SQL_CALC_FOUND_ROWS
               i.*";

        $this->db->select($select, FALSE);
        $this->db->where('i.inventory_id', $id);

        $query = $this->db->get('mcb_inventory_item AS i');


        return $query->result();
    }

    public function update_qty($id, $qty) {
        $old_qty = $this->get_qty_by_id($id)[0]->qty;
        $new_qty = floatval($old_qty) + floatval($qty);
        $db_set = array(
            'Qty' => (string) $new_qty
        );

        $this->db->where('inventory_id', $id);
        $this->db->set($db_set);

        $this->db->update($this->table_name);
    }

    public function delete($inventory_id) {
        parent::delete(array('inventory_id' => $inventory_id));
    }

    public function update_inventory($inventory) {

        $inventory_id = $inventory->inventory_id;
        echo json_encode($inventory);
        $db_set = array(
            'name' => $inventory->name,
            'supplier_code' => $inventory->supplier_code,
            'description' => $inventory->description,
            'base_price' => $inventory->base_price,
            'supplier_code' => $inventory->supplier_code,
            'supplier_description' => $inventory->supplier_description,
            'location' => $inventory->location,
            'supplier_id' => $inventory->supplier_id
        );

        $this->db->where('inventory_id', $inventory_id);
        $this->db->set($db_set);

        $this->db->update($this->table_name);

        return $this->db->affected_rows();
    }

    public function save_duplicate_products() {
        if (!$this->input->post('product_duplicate'))
            return;
        $db_array = parent::db_array();
        $array = array('supplier_id' => $db_array['supplier_id'],
            'name' => $db_array['product_name'],
            'description' => $db_array['product_description'],
            'base_price' => $db_array['product_base_price'],
            'supplier_code' => $db_array['product_supplier_code'],
            'supplier_description' => $db_array['product_supplier_description'],
            'supplier_price' => $db_array['product_supplier_price']);
        parent::save($array, uri_assoc('inventory_id'));
    }
    
    
    function save_duplicate_product($product_id) {
        if (!$this->input->post('product_duplicate')){
            return;
        }
//        $product_id = uri_assoc('product_id');
        $data = array(
            'supplier_id' => $this->input->post('supplier_id'),
            'name' => $this->input->post('product_name'),
            'description' => $this->input->post('product_description'),
            'base_price' => (float)$this->input->post('product_base_price'),
            'supplier_code' => $this->input->post('product_supplier_code'),
            'supplier_description' => $this->input->post('product_supplier_description'),
            'supplier_price' => $this->input->post('product_supplier_price'),
        );
        $invDetail = $this->get_Row('mcb_inventory_item', array('name'=> $this->input->post('product_name')));
        if($invDetail != NULL ){
            $this->update('mcb_inventory_item', $data, array('inventory_id'=>($invDetail->inventory_id)));
            $c = array('product_id'=>$product_id, 'inventory_id'=>$invDetail->inventory_id);
            $r = $this->get_Row('mcb_products_inventory', $c);
            if(($r == NULL) || ($r == '')){
                $this->insert('mcb_products_inventory', array('product_id'=>$product_id, 'inventory_id'=>$invDetail->inventory_id, 'inventory_qty'=>'1') );
            }
            $inventoryId = $invDetail->inventory_id;
            $msg = 'Updated Inventory: <a style="color: white;" href="'. site_url().'/inventory/form/inventory_id/'.$inventoryId.'">'.$this->input->post('product_name').'</a>';
            $this->session->set_flashdata('custom_success', $msg);
            return;
            
        } else {
            $iid = $this->insert('mcb_inventory_item', $data);
            $c = array('product_id'=>$product_id, 'inventory_id'=>$iid);
            $r = $this->get_Row('mcb_products_inventory', $c);
            if(($r == NULL) || ($r == '')){
                $this->insert('mcb_products_inventory', array('product_id'=>$product_id, 'inventory_id'=>$iid, 'inventory_qty'=>'1') );
            }
            $inventoryId = $iid;
            $msg = 'Duplicated Inventory: <a style="color: white;" href="'. site_url().'/inventory/form/inventory_id/'.$inventoryId.'">'.$this->input->post('product_name').'</a>';
            $this->session->set_flashdata('custom_success', $msg);
            
            if( (uri_assoc('product_id') > 0) ){
                redirect(site_url().'/inventory/form/inventory_id/'.$inventoryId);
            } else {
               redirect(site_url('products/form/product_id/'.$product_id)); 
            }   
        }
    }
    
    public function makeDuplicateProduct($product_id) {
        $proDetail = $this->get_Row('mcb_products', array('product_id'=>$product_id));
        $data = array(
            'supplier_id' => $proDetail->supplier_id,
            'name' => $proDetail->product_name,
            'description' => $proDetail->product_description,
            'base_price' => $proDetail->product_base_price,
            'supplier_code' => $proDetail->product_supplier_code,
            'supplier_description' => $proDetail->product_supplier_description,
            'supplier_price' => $proDetail->product_supplier_price,
        );
        $iid = $this->insert('mcb_inventory_item', $data);
        $this->insert('mcb_products_inventory', array('product_id'=>$product_id, 'inventory_id'=>$iid, 'inventory_qty'=>'1') );
    }

    public function save() {
        
//        echo '<pre>';
//        print_r($_POST);
//        print_r($this->input->post('name'));
//        die;


			
        $name = $this->input->post('name');
		$supplier_id = $this->input->post('supplier_id');
        $duplicate_inventory = $this->is_duplicate_inventory($name,$supplier_id);
	
        if ($duplicate_inventory) {
            $inventory_id = $duplicate_inventory->inventory_id;
			var_dump($this->input->post('is_arichved'));
		
			
            $data = array(
				'inventory_type' => $this->input->post('inventory_type'),
                'supplier_id' => $this->input->post('supplier_id'),
                'name' => $name,
                'description' => $this->input->post('description'),
                'base_price' => (float)$this->input->post('base_price'),
//                'qty' => '0',
                'supplier_code' => $this->input->post('supplier_code'),
                'supplier_description' => $this->input->post('supplier_description'),
                'supplier_price' => $this->input->post('supplier_price'),
                'location' => $this->input->post('location'),
                'inevntory_last_changed' => date('Y-m-d H:i:s'),
                'use_length' => $this->input->post('use_length'),
//                'inventory_type' => $this->input->post('inventory_type'),
                'is_arichved' => $this->input->post('is_arichved'),
            );
				
			
			
		
//            if($_POST['history_qty'] > 0){
//                $data['qty']='0';
//            }
            $this->db->where('inventory_id',$inventory_id);
            $succ = $this->db->update('mcb_inventory_item',$data); 
            if($succ){
                $this->session->set_flashdata('success_save', TRUE);
                return TRUE;
            }else{
                $this->session->set_flashdata('success_save', FALSE);
                return FALSE;
            }
			$i_id = "";
        } 
		else {
            
            $hq = $_POST['history_qty'];
            $hn = $_POST['notes'];
            
            $db_array = parent::db_array();
			

            unset($db_array['history_qty']);
            unset($db_array['notes']);
			
			  
            if ($db_array['inventory_id'] == '')
                unset($db_array['inventory_id']);
                $db_array['qty'] = $hq;
                $db_array['inventory_type'] = $this->input->post('inventory_type');
				
		
            $i_id = parent::save($db_array, uri_assoc('inventory_id'));
            
            
            $data = array(
                'history_id' => '0',
                'inventory_id' => $i_id,
                'history_qty' => $hq,
                'notes' => $hn,
                'user_id' => $this->session->userdata('user_id'),
                'created_at' => date('Y-m-d H:i:s')
            );
			
			
			
			
            $this->addHistoryItem($data);
            
        }
		
		
		
		return $i_id;
    }
    
    private function is_duplicate_inventory($name,$supplier_id) {
        $this->db->where('name', $name);
		  $this->db->where('supplier_id', $supplier_id);
        $q = $this->db->get('mcb_inventory_item');
		
        if ($q->num_rows() > 0) {
            $res = $q->row();
            return $res;
        }
        return FALSE;
    }
    /*update the inventory and the product relation(link)*/
    public function updateinvprodrelation($invlistdetail, $prodlistdetail) {
        if ($this->input->get('type') == 'one') {
            if (sizeof($invlistdetail) > 0) {
                foreach ($invlistdetail as $inventory) {
                    if((float)$inventory['qty'] > 0){
                        $succ = $this->updateinventoryRel($inventory['ii'], $inventory['qty'], $inventory['pi']);
                    }
                }
            }
        } else {
            if (sizeof($invlistdetail) > 0) {
                foreach ($invlistdetail as $inventory) {

                    if (sizeof($prodlistdetail) > 0) {
                        foreach ($prodlistdetail as $product) {
                            $this->updateinventoryRel($inventory['id'], $inventory['qty'], $product['id']);
                        }
                    }
                }
            }
        }

        return TRUE;
    }

    public function updateinventoryRel($inventoryid,$qty,$productid){
        if((float)$qty <= 1){
            $qty = 1;
        }
        //check if already existing the relation, gonna update the qty in that case
        $check = $this->checkIfAlreadyRelation($productid,$inventoryid);
        if(!$check){
            $data = array(
                'product_id' => $productid,
                'inventory_id' => $inventoryid,
                'inventory_qty' => $qty
            );
            return $this->db->insert('mcb_products_inventory_raw',$data);
        }else{
            $this->db->where('id',$check);
            $upd = array(
                'inventory_qty' => $qty
            );
            return $this->db->update('mcb_products_inventory_raw',$upd);
        }
    }
    
    private function checkIfAlreadyRelation($productid,$inventoryid){
        $where = array(
            'product_id' => $productid,
            'inventory_id' => $inventoryid,
        );
        $this->db->where($where);
        $q = $this->db->get('mcb_products_inventory');
        if($q->num_rows() > 0){
            $res = $q->row();
            return $res->id;
        }
        return FALSE;
    }
    
    public function get_one_to_one_prod_inv(){
        $sql = "select (select count(mpi.product_id) "
                . "from mcb_products_inventory as mpi "
                . "where mpi.product_id = p.product_id) as pcnt, "
                . "(select count(mpi2.inventory_id) "
                . "from mcb_products_inventory as mpi2 "
                . "where mpi2.inventory_id = i.inventory_id) as icnt, "
                . "i.inventory_id as ii, p.product_id as pi, "
                . "CONCAT(i.inventory_id,'_', p.product_id) AS id, "
                . "tpi.inventory_qty as qty, i.name as inv, p.product_name as pn "
                . "from mcb_inventory_item as i inner join mcb_products as p on i.name = p.product_name "
                . "left join mcb_products_inventory as tpi on "
                . "(i.inventory_id = tpi.inventory_id and p.product_id = tpi.product_id) "
                . "where p.product_active = '1' AND p.is_arichved = '0'";
        $q = $this->db->query($sql);
        $result = $q->result();
        $fin = array();
        if(sizeof($result) > 0){
            foreach($result as $res){
                if($res->icnt > 0){
                    $res->inv = '<b>'.$res->inv.'</b>';
                }
                if($res->pcnt > 0){
                    $res->pn = '<b>'.$res->pn.'</b>';
                }
                $fin[] = $res;
            }
        }
        return $fin;
    }
    
    function get_product_for_duplicate() {
        
        $sql = "SELECT product_id as id, product_id as ii, product_name as pn, product_name as inv, "
                . "product_id as pi, product_id as qty "
                . "FROM `mcb_products` "
                . "WHERE product_name NOT IN (SELECT name FROM `mcb_inventory_item`) AND is_arichved != '1'";
        $q = $this->db->query($sql);
        $result = $q->result();
        return $result;
    }
    
    public function get_product_inventory(){
        $sql = "SELECT mpi.id, mp.product_name as pn,mp.product_id as pi, "
                . "mii.inventory_id as ii, mii.name as ivn, mpi.inventory_qty as qty "
                . "from mcb_products_inventory as mpi inner join mcb_products as mp "
                . "on (mpi.product_id = mp.product_id AND mp.product_name != '') "
                . "inner join mcb_inventory_item as mii "
                . "on (mpi.inventory_id = mii.inventory_id AND mii.name != '') "
                . "where mp.product_active = '1' AND mp.is_arichved != '1' "
                . "order by mp.product_name asc";
        $q = $this->db->query($sql);
        $result = $q->result();
        return $result;
    }
    
    public function getCurrentQtyById($inventory_id){
        $this->db->select('qty');
        $this->db->where('inventory_id',$inventory_id);
        $q = $this->db->get('mcb_inventory_item');
        $res = $q->row();
        return $res->qty;
    }
    
    
    function update($tbl_name, $data, $condition) {
        
            $this->db->where($condition);
        $this->db->update($tbl_name, $data);
    }
    
    function insert($table, $data) {
	
        $this->db->insert($table, $data);
        return $this->db->insert_id();
    }
    
    public function get_Row($tbl_name, $condition) {
        
            $this->db->where($condition);
        $q = $this->db->get($tbl_name);
        return $q->row();
    }
    
    function get_where($table_name, $condition = '', $order = '', $limit = '') {
        
        if ($order != '') {
            $this->db->order_by($order);
        }
        if ($limit != '') {
            $this->db->limit($limit);
        }
        if ($condition != '') {
            $this->db->where($condition);
        }
        return $this->db->get($table_name)->result();      
    }
    
    function cust_qry($sql) {
        
//        $sql = "SELECT product_id as id, product_id as ii, product_name as pn, product_name as inv, "
//                . "product_id as pi, product_id as qty "
//                . "FROM `mcb_products` "
//                . "WHERE product_name NOT IN (SELECT name FROM `mcb_inventory_item`)";
        $q = $this->db->query($sql);
        $result = $q->result();
        return $result;
    }
    
    public function addHistoryItem($data){
        return $this->db->insert('mcb_inventory_history',$data);
    }
    
    public function search_inventory_by_supplier($supplier_id, $search_string) {
        $sql = "SELECT i.inventory_id AS id, i.name AS value, "
                . "i.supplier_code as product_supplier_code, "
                . "i.description as product_supplier_description, "
                . "i.supplier_price as product_supplier_price "
                . "FROM mcb_inventory_item as i "
                . "WHERE i.name LIKE '%".$search_string."%'";
        return $this->db->query($sql)->result();
    }
    
    //inventory
    public function updateinvprodrelation1inv($invlistdetail) {
        foreach ($invlistdetail as $inventory) {
            $succ = $this->db->delete('mcb_products_inventory_raw', array('inventory_id' => $inventory['id']));
        }
        if ($succ == FALSE)
            return FALSE;
        else
            return TRUE;
    }
    //product
    public function updateinvprodrelation1pro($prodlistdetail) {
        foreach ($prodlistdetail as $product) {
            $succ = $this->db->delete('mcb_products_inventory_raw', array('product_id' => $product['id']));
        }
        if ($succ == FALSE)
            return FALSE;
        else
            return TRUE;
    }

}

?>
