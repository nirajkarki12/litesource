<?php

(defined('BASEPATH')) OR exit('No direct script access allowed');

class Inventory extends Admin_Controller {

    function __construct() {

        parent::__construct();
        $this->_post_handler();

        $this->load->model('mdl_inventory_item');
        $this->load->model('mdl_inventory_history');
        $this->load->model('mdl_category');
        $this->load->model('mdl_inventory_import');

    }
    
    function index() {
        $this->load->helper('text');
        $this->load->model('clients/mdl_clients');
        $this->redir->set_last_index();
        //$data['suppliers_inventory'] = json_encode($this->get_suplier_inventory_index());
        // $data['suppliers_inventory'] = json_encode(array());

        $data['suppliers'] = json_encode($this->mdl_clients->get_active_suppliers());
        
        if ($this->input->get('history_id') != NULL && (int) $this->input->get('history_id') > 0) {
            $this->load->model('mdl_inventory_import');
            $data['import_log'] = $this->mdl_inventory_import->getLogMessage($this->input->get('history_id'));
        }
        
        $this->load->view('index', $data);
    }

    function ajax_update_inventory() {
        $item = json_decode($this->input->post('post_item'));
        $supplier_name = $item->supplier_name;

        $this->load->model('clients/mdl_clients');
        $supplier_id = $this->mdl_clients->get_by_name($supplier_name)->supplier_id;
        $inventory->inventory_id = $item->id;
        $inventory->name = $item->name;
        $inventory->supplier_code = $item->supplier_code;
        $inventory->supplier_description = $item->supplier_description;
        $inventory->description = $item->description;
        $inventory->base_price = $item->base_price;
        $inventory->location = $item->location;
        $inventory->supplier_id = $supplier_id;

        $qty = $item->qty;
        $this->load->model('mdl_inventory_item');
        $this->load->model('mdl_inventory_history');
        $this->mdl_inventory_history->ajax_insert($item->id, $qty);
        $result = $this->mdl_inventory_item->update_inventory($inventory);

        return $result;
    }

    function update_inv_rel() {
        error_reporting(E_ALL);
        ini_set('display_errors', '1');
        $inventory = json_decode($this->input->post('post_item'));
        $this->load->model('mdl_inventory_item');
        if ((float) $inventory->qty > 0) {
            $this->mdl_inventory_item->updateinventoryRel($inventory->ii, $inventory->qty, $inventory->pi);
        }
        echo TRUE;
        exit;
    }

    function get_inventory_JSON($params = NULL) {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        ini_set('memory_limit', '256M');
        $limit = $this->input->post('limit');
        $offset = $this->input->post('offset');
        $this->load->model('clients/mdl_clients');

        if ($this->input->get('show_archived') == TRUE) {
            $where = '';
            $data = array(
                'suppliers' => $this->mdl_clients->get_active_suppliers(),
                'inventory' => $this->mdl_inventory_item->get_raw($where, $limit, $offset)
            );
        } else if ($this->input->get('only_archived') == TRUE) {
            $where = 'WHERE i.is_arichved = "1"';
            $data = array(
                'suppliers' => $this->mdl_clients->get_active_suppliers(),
                'inventory' => $this->mdl_inventory_item->get_raw($where, $limit, $offset)
            );
        } else {
            $where = 'WHERE i.is_arichved != "1"';
            $data = array(
                'suppliers' => $this->mdl_clients->get_active_suppliers(),
                'inventory' => $this->mdl_inventory_item->get_raw($where, $limit, $offset)
            );
        }
//        echo '<pre>';
//        print_r($data['inventory']);
//        die;
        echo json_encode($data);
    }

    function get_suplier_inventory_index() {
        $limit = $this->input->post('limit');
        $offset = $this->input->post('offset');
        $filters = $this->input->post('filters');

        $db_data = $this->mdl_inventory_item->get_suplier_inventory($limit, $offset, $filters);

        $inventory = $db_data['inventory'];

        $all_qtys = $this->common_model->query_as_array("SELECT 
                mpi.product_id AS id,
                (IF(i.inventory_type = '1', (SELECT COALESCE(COALESCE(SUM(`qty`), '0.00'), '0.00') FROM `mcb_inventory_group` WHERE `product_id` = i.inventory_id), MIN(i.qty))) as qty
                FROM mcb_products_inventory mpi 
                LEFT JOIN `mcb_inventory_item` i ON (mpi.product_id = i.inventory_id)
                GROUP BY mpi.product_id
                ");
        
        /// set clinet
        $tmp_arr = [];
        if( $all_qtys != NULL ){
            foreach($all_qtys as $qty){
                if(!isset($tmp_arr[$qty['id']])){
                    $tmp_arr[$qty['id']] = $qty['qty'];
                }
            }
        }
        
        $qry_rip_chk = $this->db->query("SELECT 
                                            mpi.product_id AS pid, 
                                            mpi.inventory_id AS iid, 
                                            mpi.inventory_qty AS inventory_qty, 
                                            mcb_inventory_item.inventory_type AS type 
                                            FROM `mcb_products_inventory` AS mpi 
                                            LEFT JOIN mcb_inventory_item
                                            ON mpi.product_id = mcb_inventory_item.inventory_id");
        $inv_pros = array();
        if( $qry_rip_chk ){
            $res_rip_chk = $qry_rip_chk->result_array();
            if ($res_rip_chk != NULL) {
                foreach ($res_rip_chk as $ip) {
                    if( !isset($inv_pros[$ip['iid']]) ){
                        $inv_pros[$ip['iid']] = array();
                    }
                    if( ($ip['pid'] == $ip['iid']) || ($ip['type'] > '0') ){
                        // $inv_pros[$ip['iid']][] = $ip['pid'];
                        $inv_pros[$ip['iid']][] = array('id' => $ip['pid'], 'inventory_qty' => $ip['inventory_qty']);
                    }
                }
            }
        }
        
        foreach ($inventory as $key => $invent) {
            if( $invent->i_t == 'Product Group'){
                if(array_key_exists($invent->id, $tmp_arr))
                {
                    $invent->qty = $tmp_arr[$invent->id];
                    $invent->p_q = $this->mdl_inventory_item->get_pending_qty_group_by_inv_id($invent->id);
                }
            }else{
                
                $invent->p_q = 0;
                if( isset($inv_pros[$invent->id])  && ($inv_pros[$invent->id] != NULL) ){
                    $invent->p_q = $this->mdl_inventory_item->get_pending_qty_by_inv_id($invent->id, $inv_pros[$invent->id]);
                }
            }
        }
        // function sortByOrder($a, $b) {
        //     return strcmp($a->client_name, $b->client_name);
        // }
        // usort($inventory, 'sortByOrder');
        $db_data['inventory'] = $inventory;

        echo json_encode($db_data);
    }
    
    function get_suplier_inventory_index_bak() {
        $limit = $this->input->post('limit');
        $offset = $this->input->post('offset');
        $filters = $this->input->post('filters');

        $db_data = $this->mdl_inventory_item->get_suplier_inventory($limit, $offset, $filters);

        $inventory = $db_data['inventory'];

        $all_qtys = $this->common_model->query_as_array("SELECT 
                mpi.product_id AS id,
                MIN(i.qty) AS qty
                FROM mcb_products_inventory mpi 
                LEFT JOIN `mcb_inventory_item` i ON (mpi.inventory_id = i.inventory_id)
                GROUP BY mpi.product_id
                ");
        
        /// set clinet
        $tmp_arr = [];
        if( $all_qtys != NULL ){
            foreach($all_qtys as $qty){
                if(!isset($tmp_arr[$qty['id']])){
                    $tmp_arr[$qty['id']] = $qty['qty'];
                }
            }
        }
        
        $qry_rip_chk = $this->db->query("SELECT 
                                            mpi.product_id AS pid, 
                                            mpi.inventory_id AS iid, 
                                            mpi.inventory_qty AS inventory_qty, 
                                            mcb_inventory_item.inventory_type AS type 
                                            FROM `mcb_products_inventory` AS mpi 
                                            LEFT JOIN mcb_inventory_item
                                            ON mpi.product_id = mcb_inventory_item.inventory_id");
        $inv_pros = array();
        if( $qry_rip_chk ){
            $res_rip_chk = $qry_rip_chk->result_array();
            if ($res_rip_chk != NULL) {
                foreach ($res_rip_chk as $ip) {
                    if( !isset($inv_pros[$ip['iid']]) ){
                        $inv_pros[$ip['iid']] = array();
                    }
                    if( ($ip['pid'] == $ip['iid']) || ($ip['type'] > '0') ){
                        // $inv_pros[$ip['iid']][] = $ip['pid'];
                        $inv_pros[$ip['iid']][] = array('id' => $ip['pid'], 'inventory_qty' => $ip['inventory_qty']);
                    }
                }
            }
        }
        
        foreach ($inventory as $key => $invent) {
            if( $invent->i_t == 'Product Group'){
                if(array_key_exists($invent->id, $tmp_arr))
                {
                    $invent->qty = $tmp_arr[$invent->id];
                    $invent->p_q = $this->mdl_inventory_item->get_pending_qty_group_by_inv_id($invent->id);
                }
            }else{
                
                $invent->p_q = 0;
                if( isset($inv_pros[$invent->id])  && ($inv_pros[$invent->id] != NULL) ){
                    $invent->p_q = $this->mdl_inventory_item->get_pending_qty_by_inv_id($invent->id, $inv_pros[$invent->id]);
                }
            }
        }
        // function sortByOrder($a, $b) {
        //     return strcmp($a->client_name, $b->client_name);
        // }
        // usort($inventory, 'sortByOrder');
        $db_data['inventory'] = $inventory;

        echo json_encode($db_data);
    }
    
    function get_inventory_list() {
        $limit = $this->input->post('limit');
        $offset = $this->input->post('offset');
        $this->load->model('clients/mdl_clients');
        $where = "WHERE i.is_arichved != '1'";
        $inventory = $this->mdl_inventory_item->get_raw($where, $limit, $offset);
        $finInv = array();
        if (sizeof($inventory) > 0) {
            foreach ($inventory as $inv) {
                $inv->qty = '';
                $finInv[] = $inv;
            }
        }
        $data = array(
            'suppliers' => $this->mdl_clients->get_active_suppliers(),
            'inventory' => $finInv
        );
        echo json_encode($data);
    }

    function get_unlinked_inventory_list() {
//        error_reporting(E_ALL);
//        ini_set('display_errors', 1);
        $limit = $this->input->post('limit');
        $offset = $this->input->post('offset');
        $this->load->model('clients/mdl_clients');
        $this->load->model('inventory/mdl_inventory_item');

        $inventory = $this->mdl_inventory_item->getUnlinedInventorylist($limit, $offset);

        $finInv = array();
        if (sizeof($inventory) > 0) {
            foreach ($inventory as $inv) {
                $inv->qty = 1;
                $finInv[] = $inv;
            }
        }

        $data = array(
            'suppliers' => $this->mdl_clients->get_active_suppliers(),
            'inventory' => $finInv
        );
        echo json_encode($data);
    }

    function ajax_from_quote_add() {
        //error_reporting(E_ALL); ini_set('display_errors', 1);
        
        $c = 0;
        $items = array();
        foreach ($_POST as $values) {
            $item = array(
                'name' => $_POST['name'][$c],
                'supplier_id' => $_POST['supplier_id'][$c],
                'description' => $_POST['description'][$c],
                'base_price' => $_POST['base_price'][$c],
                'supplier_price' => $_POST['supplier_price'][$c],
                'supplier_description' => $_POST['supplier_description'][$c],
                'use_length' => $_POST['use_length'][$c],
                'supplier_code' => $_POST['supplier_code'][$c],
                'invoice_id' => $_POST['invoice_id'][$c],
                'invoice_item_id' => $_POST['invoice_item_id'][$c],
                'i_action' => $_POST['i_action'][$c],
            );
            array_push($items, $item);
            $c++;
            if (count($values) == $c) {
                break;
            }
        }



        foreach ($items as $inventory) {
            $invoice_item_id = $inventory['invoice_item_id'];
            $invoice_id = $inventory['invoice_id'];
            
            unset($inventory['invoice_id']);
            unset($inventory['invoice_item_id']);
            
            if ($inventory['i_action'] == 'create') {
                unset($inventory['i_action']);
                
                $inventory_id = $this->common_model->insert('mcb_inventory_item', $inventory);
                if(!((float)$inventory_id > 0)){
                    echo json_encode(
                            array(
                                'status' => 'fail',
                                'detail' => 'Could not create inventory'
                            )
                    );
                    exit;
                }
                
                $sql = "UPDATE mcb_invoice_items SET product_id= '" . $inventory_id . "', item_name = '".$inventory['name']."', item_price = '".$inventory['base_price']."' WHERE invoice_item_id = '" . $invoice_item_id . "'";
                $this->common_model->just_query($sql);
                
            } elseif ($inventory['i_action'] == 'relate') {
                
                $rel_item_id = $inventory['name'];
                $sql = "UPDATE mcb_invoice_items SET product_id= '" . $rel_item_id . "' WHERE invoice_item_id = '" . $invoice_item_id . "'";
                $this->common_model->just_query($sql);
                
            } else {
                //do not add
            }
        }
        
        echo json_encode(
                array(
                    'status' => 'success',
                    'detail' => ''
                )
        );
        exit;
    }

    function ajax_add() {
	
        $arr = array(
            'status' => FALSE,
            'msg' => 'Please fill up all fields.'
        );
        if ($this->mdl_inventory_item->validate() && $this->mdl_inventory_history->validate()) {
            $inv_id = $this->mdl_inventory_item->save();
            $this->mdl_inventory_history->save();
            $invoice_item_id = $this->input->post('invoice_item_id'); //if added from quote then update productid  with new productid
            if ($inv_id != NULL && $invoice_item_id != "") {
                $sql = "UPDATE mcb_invoice_items SET product_id=" . $inv_id . " WHERE invoice_item_id =" . $invoice_item_id . " ";
                $this->common_model->just_query($sql);
                $arr = array(
                    'status' => TRUE,
                    'msg' => 'success.'
                );
            }
        } else {
            $arr = array(
                'status' => FALSE,
                'msg' => 'Please fill up all fields.'
            );
        }
        echo json_encode($arr);
    }
    
    function form() {
        if ($this->mdl_inventory_item->validate() && $this->mdl_inventory_history->validate()) {
            //saving things in history
            $inventory_id = $this->input->post('inventory_id');
            $category = $this->input->post('category_id');
            
            $category_id = $this->mdl_inventory_import->getCategoryIdFromName($category);

            if(!$category_id && !empty($category)){

                $category_id = $this->mdl_inventory_import->createCategory($category);

            }
         /*   if(!is_numeric($category) && !empty($category)){
                $category_id = $this->mdl_inventory_import->createCategory($category);
            }else{
                $category_id = $category;
            } */
            
            if ($inventory_id != NULL) {
                
                $product_name = $this->input->post('product_name');
                if ($product_name != '') {
                    
                    $product_id = $this->common_model->get_row('mcb_products', array('product_name' => $product_name))->product_id;
                    foreach ($this->input->post('deleteInventory') as $value) {
                        $this->common_model->delete('mcb_products_inventory_raw', array('product_id' => $product_id, 'inventory_id' => $value));
                    }
                    $selectedInventories = $this->input->post('selectedInventory');
                    $qtyInventories = $this->input->post('qtyInventory');
                    if (is_array($selectedInventories)) {
                        $i = 0;
                        foreach ($selectedInventories as $selectedInventory) {
                            $inventory_qty = $qtyInventories[$i] ? $qtyInventories[$i] : '1';
                            $array = array(
                                'product_id' => $product_id,
                                'inventory_id' => $selectedInventory,
                                'inventory_qty' => $inventory_qty,

                            );
                            $this->common_model->insert('mcb_products_inventory_raw', $array);
                            $i++;
                        }
                    }
                }
                if(isset($_POST['deleteProductInventory'])){
                    foreach ($this->input->post('deleteProductInventory') as $delete_p_id) {
                        $this->common_model->delete('mcb_products_inventory_raw', array('product_id' => $delete_p_id, 'inventory_id' => $inventory_id));
                    }
                }
                
            }

            
            
            if (($this->input->post('use_length') == '1') && ($this->input->post('inventory_id') > '0')) {
                $inv_pro = $this->mdl_inventory_item->get_where('mcb_products_inventory_raw', array('inventory_id' => $this->input->post('inventory_id')));
                if ($inv_pro != NULL) {
                    $this->mdl_inventory_item->update('mcb_products_inventory_raw', array('inventory_qty' => '1'), array('inventory_id' => $this->input->post('inventory_id')));
                }
            }
            
            /* add item in stock and item tables if not duplicate */
            $inv_id = $this->mdl_inventory_item->save($category_id);
          
            
            //---- add sku to inventory-----
            if( $inv_id > 0 ){

                $sku_name = "SKU-{$inv_id}";
                $this->common_model->update('mcb_inventory_item',array('category_id' => $category_id, 'sku'=>$sku_name), array('inventory_id'=>$inv_id));
            }
            
            $this->mdl_inventory_history->save();

            //update teh related invoice id so they can get supplier when they convert..
            $invoice_item_id = $this->input->post('invoice_item_id'); //if added from quote then update productid  with new productid

            if ($inventory_id != NULL && $invoice_item_id != "") {

                $sql = "UPDATE mcb_invoice_items SET product_id=" . $inventory_id . " WHERE invoice_item_id =" . $invoice_item_id . " ";
                $q = $this->db->query($sql);
            }

            $clean = $this->input->post("clean");
            if ($inventory_id == NULL) {

                if ($this->input->post('inventory_type') == '1' && $inv_id != "") { //if 1 redirect back so they can add relationship
                    if ($clean == '1') {
                        redirect(site_url('inventory/form/inventory_id/' . $inv_id . '/?clean=' . $clean));
                    } else {
                        redirect(site_url('inventory/form/inventory_id/' . $inv_id));
                    }
                } else {
                    if ($clean == '1') { //stay on page
                        redirect(site_url('inventory/form/inventory_id/' . $inv_id . '/?clean=' . $clean));
                    } else {
                        redirect($this->session->userdata('last_index'));
                    }
                }
            } else {
                
                if(isset($_POST['action_type']) && $_POST['action_type'] == 'continue'){
                    
                    $sql = "select inventory_id from mcb_inventory_item where supplier_id IS NULL order by inventory_id desc";
                    $query = $this->db->query($sql);
                    $res = $query->result();
                    if(sizeof($res) > 0){
                        $inv_lat = $res[0]->inventory_id;
                        redirect(site_url('inventory/form/inventory_id/' . $inv_lat));
                        exit;
                    }
                }
                
                // redirect($this->session->userdata('last_index'));
                if ($clean == '1') { //stay on page
                    redirect(site_url('inventory/form/inventory_id/' . $inventory_id . '/?clean=' . $clean));
                } else {
                    redirect(site_url('inventory/form/inventory_id/' . $inventory_id));
                }
            }
        } else {
            $this->load->helper('form');

            if (!$_POST AND uri_assoc('inventory_id')) {
                
                $this->mdl_inventory_item->prep_validation(uri_assoc('inventory_id'));
            }
//            $id = $this->mdl_inventory_item->form_value($inventory_id);
            $id = uri_assoc('inventory_id');
            
            $inventory_items = NULL;
            // $quote_inventory_items = NULL;
            // $inventory_history = NULL;
            if( $id > '0' ){
                $inventory_as_row = $this->common_model->query_as_row("
                    SELECT *,
                    st.stock_id,
                    SUM(IF(st.status='1',st.qty_pending,'0')) as qty_pending, 
                    (IF(i.inventory_type = '1', (SELECT COALESCE(SUM(`qty`), '0.00') FROM `mcb_inventory_group` WHERE `product_id` = i.inventory_id)
                        , i.qty)) AS quantity 
                    FROM mcb_inventory_item AS i  
                    LEFT JOIN mcb_item_stock as st  on i.inventory_id = st.inventory_id  
                    WHERE i.inventory_id='" . $id . "' 
                    GROUP BY st.inventory_id  ORDER BY st.stock_id");
                
                
                if ($inventory_as_row->inventory_type == '1') {
                    $prod_id = $this->common_model->get_row('mcb_products', array('product_name' => $inventory_as_row->name))->product_id;
                    $pro_invns_qry = "SELECT SQL_CALC_FOUND_ROWS i.inventory_id id, p.inventory_qty, i.use_length, i.name, i.supplier_id, i.description, i.base_price, i.qty, i.supplier_code, i.supplier_description, i.location
                                FROM (mcb_inventory_item AS i 
                                inner join mcb_products_inventory AS p                        
                                on i.inventory_id = p.inventory_id

                              )
                                WHERE `p`.`product_id` = '{$prod_id}'
                                ORDER BY i.inventory_id DESC";
                    $inventory_items = $this->common_model->query_as_object($pro_invns_qry);
                } else{
                    $pro_invns_qry = "SELECT SQL_CALC_FOUND_ROWS p.product_id id, p.inventory_id pid, p.inventory_qty, i.use_length, i.name, i.supplier_id, i.description, i.base_price, i.qty, i.supplier_code, i.supplier_description, i.location
                                    FROM mcb_inventory_item i
                                    INNER JOIN mcb_products_inventory AS p ON i.inventory_id = p.product_id
                                    WHERE p.inventory_id = '{$id}' AND i.inventory_id != '{$id}' ORDER BY i.inventory_id DESC";
                    $inventory_items = $this->common_model->query_as_object($pro_invns_qry);
                }
        
                $inventory_as_row->qty_pending = $this->mdl_inventory_item->get_pending_qty($id);
                
                // $quote_inventory_items_qry = "SELECT 
                //                        iv.invoice_id,
                //                        iv.invoice_number,
                //                        mp.project_name,
                //                        ii.*,
                //                        ia.item_total
                //                 FROM   mcb_invoices AS iv
                //                        INNER JOIN mcb_invoice_items AS ii
                //                                ON ii.invoice_id = iv.invoice_id
                //                        INNER JOIN mcb_invoice_item_amounts AS ia
                //                                ON ia.invoice_item_id = ii.invoice_item_id
                //                        INNER JOIN mcb_products AS p
                //                                ON ii.product_id = p.product_id
                //                        INNER JOIN mcb_products_inventory AS pi
                //                                ON pi.product_id = p.product_id
                //                        INNER JOIN mcb_inventory_item i
                //                                ON i.inventory_id = pi.inventory_id
                //                        LEFT JOIN mcb_projects AS mp
                //                                 ON mp.project_id = iv.project_id
                //                 WHERE  invoice_is_quote = 1";
                // $quote_inventory_items_qry = $quote_inventory_items_qry." AND i.inventory_id ={$id}";
                // $quote_inventory_items_qry = $quote_inventory_items_qry." ORDER BY iv.invoice_date_entered DESC";
                
                // $quote_inventory_items = $this->common_model->query_as_object($quote_inventory_items_qry);
                // $inventory_history = $this->mdl_inventory_history->get_history_by_iventoryId($id);
            }
            
            $this->load->helper('text');
            // $this->load->model('users/mdl_users');
            $this->load->model('clients/mdl_clients');
            
            // $users = $this->mdl_users->get_raw();
            $suppliers = $this->mdl_clients->get_active_suppliers();
            $categories = $this->mdl_category->get_active_categories();
            
            $inventory_part_type = $this->common_model->query_as_object('SELECT *, inventory_id AS id, "Part" AS inventory_type FROM mcb_inventory_item WHERE is_arichved != "1" AND inventory_type = "0"');
            
            $grid_sup_inv_data = array(
                'suppliers' => $suppliers,
                'inventory' => $inventory_part_type,
                'categories'=>$categories);
            
            ini_set("memory_limit",-1);

            $data = array(
                'suppliers' => $suppliers,
                'categories'=>$categories,
                'suppliers_json' => json_encode($suppliers),
                // 'users' => json_encode($users),
                // 'inventory_history' => $inventory_history ? json_encode($inventory_history) : '',
                'mcb_inventory_item' => $inventory_as_row,
                'all_inventory_items' => $inventory_part_type,
                'inventory_items' => isset($inventory_items) ? json_encode($inventory_items) : '',
                'sup_inv_json'=> json_encode($grid_sup_inv_data),
                // 'quote_inventory_items'=> isset($quote_inventory_items) ? $quote_inventory_items : null,
            );
            
            $this->load->view('form', $data);
        }
    }

    function get_inventory_history(){
        $id = $this->input->post('id');

        if($id > '0'){
            $this->load->model('users/mdl_users');
            $users = $this->mdl_users->get_raw();

            $inventory_history = $this->mdl_inventory_history->get_history_by_iventoryId($id);

            $data['inventory_history'] = (count($inventory_history) > 0) ? json_encode($inventory_history) : null;
            $data['users'] = (count($users) > 0) ? json_encode($users) : null;

            echo json_encode(['history' => $this->load->view('inventory_history_grid', $data , true)]);
        }
    }

    function get_inventory_quotes(){
        $id = $this->input->post('id');

        if($id > '0'){
            $quote_inventory_items_qry = "SELECT 
                                   iv.invoice_id,
                                   iv.invoice_number,
                                   mp.project_name,
                                   ii.*,
                                   ia.item_total
                            FROM   mcb_invoices AS iv
                                   INNER JOIN mcb_invoice_items AS ii
                                           ON ii.invoice_id = iv.invoice_id
                                   INNER JOIN mcb_invoice_item_amounts AS ia
                                           ON ia.invoice_item_id = ii.invoice_item_id
                                   INNER JOIN mcb_products AS p
                                           ON ii.product_id = p.product_id
                                   INNER JOIN mcb_products_inventory AS pi
                                           ON pi.product_id = p.product_id
                                   INNER JOIN mcb_inventory_item i
                                           ON i.inventory_id = pi.inventory_id
                                   LEFT JOIN mcb_projects AS mp
                                            ON mp.project_id = iv.project_id
                            WHERE  invoice_is_quote = 1";
            $quote_inventory_items_qry = $quote_inventory_items_qry." AND i.inventory_id ={$id}";
            $quote_inventory_items_qry = $quote_inventory_items_qry." ORDER BY iv.invoice_date_entered DESC";

            $data = $this->load->view('quote_inventory_item', ['quote_inventory_items' => $this->common_model->query_as_object($quote_inventory_items_qry)], true);

            echo json_encode(['html' => $data]);
        }
    }

    function inventory_has_relations(){
        $id = $this->input->post('id');

        if($id > '0'){
            $quote_inventory_items_qry = "SELECT 
                                   iv.invoice_id,
                                   iv.invoice_number
                            FROM   mcb_invoices AS iv
                                   INNER JOIN mcb_invoice_items AS ii
                                           ON ii.invoice_id = iv.invoice_id
                                   INNER JOIN mcb_invoice_item_amounts AS ia
                                           ON ia.invoice_item_id = ii.invoice_item_id
                                   INNER JOIN mcb_products AS p
                                           ON ii.product_id = p.product_id
                                   INNER JOIN mcb_products_inventory AS pi
                                           ON pi.product_id = p.product_id
                            WHERE  1 = 1";
            $quote_inventory_items_qry = $quote_inventory_items_qry." AND pi.inventory_id ={$id}";
            $quote_inventory_items = $this->common_model->query_as_object($quote_inventory_items_qry);

            echo json_encode(['status' => ($quote_inventory_items ? true : false)]);
        }
    }

    function get_part_type_items(){
        $search_term = $this->input->post('term');
        $data = $this->common_model->query_as_object("SELECT inventory_id, name, use_length  FROM mcb_inventory_item WHERE is_arichved != '1' AND inventory_type = '0' AND (name LIKE '" . $search_term . "%' OR description LIKE '" . $search_term . "%') LIMIT 50");
        echo json_encode($data);
    }

    function _post_handler() {
        if ($this->input->post('btn_add_inventory')) {

            redirect('inventory/form');
        }
        if ($this->input->post('btn_add_housing')) {

            redirect('housing/form');
        }
        if ($this->input->post('btn_upload_inventory')) {
            redirect('inventory/upload_inventory');
        }
        if ($this->input->post('btn_export_inventory')) {
            redirect('inventory/export_inventory');
        }
        if ($this->input->post('btn_add_category')) {

            redirect('inventory/category_form');

        }
        elseif ($this->input->post('btn_cancel')) {

            redirect($this->session->userdata('last_index'));

        }
    }

    function delete() {

        if (uri_assoc('inventory_id')) {

            $this->mdl_inventory_item->delete(uri_assoc('inventory_id'));
        }

        $this->redir->redirect('inventory');
    }

    public function rollback() {
        $this->load->model('inventory/mdl_inventory_import');
        $data = array(
            'import_history' => $this->mdl_inventory_import->getImportHistory(),
        );
        $this->load->view('import_history', $data);
    }
    
    function product_inventory_duplication() {
        $this->load->view('product_inventory_duplication');
    }
    
    function get_link_part_type() {
        $inventory_qry = "SELECT inventory_id id, name, supplier_id 
                    FROM mcb_inventory_item AS i  
                    left join mcb_clients c on c.client_id=i.supplier_id
                    WHERE is_arichved != '1' AND inventory_type != '1' 
                    ORDER BY c.client_name ASC ";
        $inventory = $this->common_model->query_as_object($inventory_qry);
        $data = array(
            'inventory' => $inventory
        );
        echo json_encode($data);
    }
    
    function get_link_group_type() {
        $sql = "SELECT mii.inventory_id AS id, mcb_clients.client_name AS supplier_name, "
                . "mii.name AS n, mii.supplier_code AS c, mii.base_price AS p, mii.supplier_price AS b, mii.supplier_id AS s, "
                . "(SELECT COUNT(*) FROM `mcb_products_inventory` WHERE mcb_products_inventory.product_id = mii.inventory_id) AS inventorycount "
                . "FROM `mcb_inventory_item` AS mii "
                . "left JOIN mcb_clients ON mcb_clients.client_id = mii.supplier_id "
                . "WHERE `mii`.`inventory_type` = '1' AND `mii`.`is_arichved` != '1'";
        
        $data = array(
            'products' => $this->common_model->query_as_object($sql),
        );
        echo json_encode($data);
    }
    
    function link_to_product() {
        $active_supplier_qry = "SELECT SQL_CALC_FOUND_ROWS mcb_clients.*, 
                                mcb_client_groups.client_group_name, mcb_clients.client_name value, /*for jqueryui autocomplete*/ 
                                IFNULL(parent_suppliers.client_id, mcb_clients.client_id) supplier_id, 
                                IFNULL(parent_suppliers.client_name, mcb_clients.client_name) supplier_name, 
                                t.total_due, mcb_currencies.* 
                                FROM (mcb_clients) 
                                JOIN mcb_currencies ON mcb_currencies.currency_id = mcb_clients.client_currency_id 
                                LEFT JOIN mcb_clients AS parent_suppliers ON parent_suppliers.client_id = mcb_clients.parent_client_id 
                                LEFT JOIN mcb_client_groups ON mcb_client_groups.client_group_id = mcb_clients.client_group_id 
                                LEFT JOIN (SELECT c.client_id, SUM(a.invoice_total) total_due 
                                FROM mcb_invoices i JOIN mcb_clients c ON c.client_id = i.client_id 
                                JOIN mcb_invoice_amounts a ON a.invoice_id = i.invoice_id 
                                WHERE i.invoice_is_quote = 0 AND i.invoice_status_id = 2 
                                GROUP BY i.client_id) AS t ON t.client_id = mcb_clients.client_id 
                                WHERE `mcb_clients`.`client_active` = 1 AND `mcb_clients`.`client_is_supplier` = 1 
                                ORDER BY mcb_clients.client_name";
        $data['suppliers'] = json_encode($this->common_model->query_as_object($active_supplier_qry));
        $this->load->view('link_to_product', $data);
    }

    function add_category(){
        $this->load->helper('text');

        $this->redir->set_last_index();

        $params = array(
            'paginate'	=>	TRUE,
            'limit'		=>	$this->mdl_mcb_data->setting('results_per_page'),
            'page'		=>	uri_assoc('page', $segment = 4),
            'where'     => 'category_deleted !=1'
        );



        $order_by = uri_assoc('order_by');



        switch ($order_by) {
            case 'category_id':
                $params['order_by'] = 'category_id';
                break;
            default:
                $params['order_by'] = 'category_name';
        }


        $data = array(
            'category' => $this->mdl_category->get($params),
            'order_by' => $params['order_by']
        );

        $this->load->view('category_index',$data);
    }
    function category_delete() {

        if (uri_assoc('category_id')) {

            $this->mdl_category->delete(uri_assoc('category_id'));

        }

        $this->redir->redirect('inventory/add_category');

    }


    function category_details() {
        if ($this->mdl_category->validate()) {
            $this->mdl_category->save();
            redirect($this->session->userdata('last_index'));
        }
        else {
            $this->load->helper('form');
            $category_id = uri_assoc('category_id');
            if (!$_POST AND $category_id) {
                $this->mdl_category->prep_validation($category_id);
            }
            $this->load->helper('text');
            $category_params = array(
                'where'	=>	array(
                    'mcb_category.category_id'	=>	$category_id
                )
            );
            $category = $this->mdl_category->get($category_params);
            $data = array(
                'category'	=>	$category,
            );
            $this->load->view('category_form', $data);

        }
    }

    function category_form(){
        if ($this->mdl_category->validate()) {


            $this->mdl_category->save();

            redirect($this->session->userdata('last_index'));

        }
        else {
            $this->load->helper('form');
            if (!$_POST AND uri_assoc('category_id')) {
                $this->mdl_category->prep_validation(uri_assoc('category_id'));
            }
            $this->load->helper('text');
            $this->load->view('category_form');
        }
    }


    public function one_to_one_product_inv() {
        $this->load->view('one_to_one_product_inv');
    }

    public function dorollback($historyid = FALSE) {
        $this->load->model('inventory/mdl_inventory_import');
        if ($historyid) {
            if ($this->mdl_inventory_import->dorollback($historyid)) {
                $this->session->set_flashdata('custom_success', 'Rollback successful.');
            } else {
                $this->session->set_flashdata('custom_error', 'Error while rolling back.');
            }
        }
        redirect('inventory/rollback');
    }
    
    public function updateinvprodrelation() {
        ini_set('max_execution_time', 3000);
        $invlistdetail = $this->input->post('id');
        $prodlistdetail = $this->input->post('pd');
        $this->load->model('inventory/mdl_inventory_item');

        if ($this->mdl_inventory_item->updateinvprodrelation($invlistdetail, $prodlistdetail)) {
            $this->session->set_flashdata('custom_success', 'Successfully applied to product.');
            echo 'success';
            exit;
        }
        $this->session->set_flashdata('custom_error', 'Error while applying.');
        echo 'fail';
        exit;
    }

    public function update_product_duplication() {
        ini_set('max_execution_time', 3000);
        $prolistdetail = $this->input->post('prolistdetail');
        foreach ($prolistdetail as $value) {
            $this->mdl_inventory_item->makeDuplicateProduct($value['id']);
        }
        $this->session->set_flashdata('custom_success', 'Successfully duplicated to inventory items.');
        echo 'success';
        exit;
    }

    public function get_one_to_one_prod_inv() {
        $this->load->model('inventory/mdl_inventory_item');
        $one_to_one_list = $this->mdl_inventory_item->get_one_to_one_prod_inv();
        echo json_encode($one_to_one_list);
        exit;
//        echo "<pre>";
//        echo print_r($one_to_one_list); exit;
    }

    function get_product_for_duplicate() {
        $this->load->model('inventory/mdl_inventory_item');
        $one_to_one_list = $this->mdl_inventory_item->get_product_for_duplicate();
        echo json_encode($one_to_one_list);
        exit;
//        echo "<pre>";
//        print_r($one_to_one_list);
//        die;   
    }

    public function product_inventory() {
        $this->load->view('product_inventory');
    }

    public function get_product_inventory() {
        $this->load->model('inventory/mdl_inventory_item');
        $get_product_inventory = $this->mdl_inventory_item->get_product_inventory();
        echo json_encode($get_product_inventory);
        exit;
    }

    public function update_inv_rel_pi() {
        //error_reporting(E_ALL); ini_set('display_errors', '1');
        $inventory = json_decode($this->input->post('post_item'));
        $this->load->model('mdl_inventory_item');
        if ((float) $inventory->qty > 0) {
            $this->mdl_inventory_item->updateinventoryRel($inventory->ii, $inventory->qty, $inventory->pi);
        }
        echo TRUE;
        exit;
    }

    public function open_o_qty() {
        error_reporting(E_ALL);
        ini_set('display_errors', '1');
        set_time_limit(0);

        if (isset($_GET['update'])) {
            $this->load->model('mdl_inventory_item');

            $limit = $_GET['limit'];
            $offset = $_GET['offset'];
            $where = '';
            $inventories = $this->mdl_inventory_item->get_raw($where, $limit, $offset);

            $this->load->helper('mcb_app');

            //echo '<pre>'; print_r($inventories); exit;

            if (sizeof($inventories) > 0) {
                foreach ($inventories as $inv) {
                    var_dump(udpate_open_order_qty($inv->id));
                    echo '<br/>';
                }
            }
            echo 'success';
        }
    }

    function doDuplicate() {
        $this->load->model('inventory/Mdl_Inventory_Item');

        $err_msg = "";
        $succ_msg = "";

        if ($_POST) {
            $arry = $this->input->post('inven_detail');
            foreach ($arry as $value) {
                $inv_detail = $this->common_model->get_row('mcb_inventory_item', array('inventory_id' => $value['id']));
                if ($inv_detail != NULL) {
                    $inv_detail->qty = '0.00';
                    $inv_detail->inventory_id = NULL;
                    $inv_detail->name = $inv_detail->name . '-copy';
                    $data = (array) $inv_detail;
                    $i_itemd = $this->common_model->insert('mcb_inventory_item', $data);
                    if ($i_itemd > 0) {
                        $sku_name = "SKU-{$i_itemd}";
                        $this->common_model->update('mcb_inventory_item', array('sku'=>$sku_name), array('inventory_id'=>$i_itemd));
                        $succ_msg .= $value['name'] . " has been duplicated.<br>";
                    } else {
                        $err_msg .= $value['name'] . " couldn't not be duplicated.<br>";
                    }
                } else {
                    $err_msg .= $value['name'] . " couldn't not be duplicated.<br>";
                }
            }
        }
        $this->session->set_flashdata('custom_success', $succ_msg);
        $this->session->set_flashdata('custom_error', $err_msg);
        echo 'success';
        exit;
    }

    public function doArchive() {
        $this->load->model('inventory/Mdl_Inventory_Item');
        if ($_POST) {
            $arry = $this->input->post('inven_detail');
            foreach ($arry as $value) {

                $detail = $this->Mdl_Inventory_Item->get_Row('mcb_inventory_item', array('inventory_id' => $value['id']));

                if ($detail != '') {
                    $this->Mdl_Inventory_Item->update('mcb_inventory_item', array('is_arichved' => '1'), array('inventory_id' => $detail->inventory_id));
                }
            }
            $this->session->set_flashdata('custom_success', 'Successfully archived inventory.');
            echo 'success';
            exit;
        }
        $this->session->set_flashdata('custom_error', 'Error while applying.');
        echo 'fail';
        exit;
    }

    public function undoArchive() {
        $this->load->model('inventory/Mdl_Inventory_Item');
        if ($_POST) {
            $arry = $this->input->post('inven_detail');
            foreach ($arry as $value) {

                $detail = $this->Mdl_Inventory_Item->get_Row('mcb_inventory_item', array('inventory_id' => $value['id']));

                if ($detail != '') {
                    $this->Mdl_Inventory_Item->update('mcb_inventory_item', array('is_arichved' => '0'), array('inventory_id' => $detail->inventory_id));
                }
            }
            $this->session->set_flashdata('custom_success', 'Successfully un-arichved inventory.');
            echo 'success';
            exit;
        }
        $this->session->set_flashdata('custom_error', 'Error while applying.');
        echo 'fail';
        exit;
    }

    function jquery_inventory_by_supplier() {
        $supplier_id = uri_assoc('supplier_id');
        $search_term = $this->input->post('term');

        $data = array(
            'inventory' => $this->mdl_inventory_item->search_inventory_by_supplier($supplier_id, $search_term)
        );
        echo json_encode($data);
    }
    
    function getInvOpnOrdrQty() {
        
        $invnum = $this->input->post('invnum');
        $this->load->model('delivery_dockets/mdl_delivery_dockets');
        $this->load->model('inventory/Mdl_Inventory_Item');
        
        $inven = $this->Mdl_Inventory_Item->get_Row('mcb_inventory_item', array('inventory_id' => $invnum));
        if ($inven->supplier_code != '') {
            $sup_name = '(' . $inven->supplier_code . ')';
        } else {
            $sup_name = '';
        }
        
        $dockets = $this->mdl_inventory_item->get_opn_ordr_qty_ajax($invnum);

//        echo '<pre>';
//        print_r($dockets);
//        die;
        
        
        if(sizeof($dockets) > 0) {
            $popHtml = '<div class="inv-detail">';
            $popHtml .= '<h4>' . $inven->name . '  ' . $sup_name . '</h4><br/>';

            $popHtml .= '<h3>Orders:</h3>';
            $popHtml .= '<table class="table table-bordered order-prod-list">';

            $popHtml .= '<thead>';
            $popHtml .= '<tr>';
            $popHtml .= '<td>S.N</td>';
            $popHtml .= '<td>Order #</td>';
            $popHtml .= '<td>Length</td>';
            $popHtml .= '<td>Quantity</td>';
            $popHtml .= '</tr>';
            $popHtml .= '</thead>';
            $popHtml .= '<tbody>';
            $i = 1;
            $sum = 0;
            foreach ($dockets as $inv) {

                if( $inv['open_order_qty'] != '0'  ){
                    
                    if( $inv['stock_status'] != '1'  ){
                        $length = ($inv['item_length'] > '0') ? $inv['item_length'] : '';
                        $popHtml .= '<tr>';
                        $popHtml .= '<td>' . $i . '</td>';
                        $popHtml .= '<td><a href="' . site_url('orders/edit/order_id/' . $inv['order_id']) . '" target="_new">' . $inv['order_number'] . '</a></td>';
                        $popHtml .= '<td>' . $length . '</td>';
                        $popHtml .= '<td>' . $inv['open_order_qty'] . '</td>';
                        $popHtml .= '</tr>';
                        $i++;
                        $sum += $inv['open_order_qty'];
                    }
                    
                    
                }
            }
            $popHtml .= '</tbody></table></div>';
            $popHtml .= '<div style="float: right;margin-right: 1%;">Total Quantity : ' . $sum . '</div>';
            echo $popHtml;
            exit;
        }else {
            echo 'There are no open order quantity.';
            exit;
        }
 
    }

    function getInvPendingrQty() {

        // error_reporting(E_ALL);
        // ini_set('display_errors', 1);

    
        $invnum = $this->input->post('invnum');
        $this->load->model('delivery_dockets/mdl_delivery_dockets');
        $this->load->model('inventory/Mdl_Inventory_Item');

        if(!$pendingQtyDetails = $this->mdl_inventory_item->getPendingQuantityData($invnum)) {
            $pendingQtyDetails = $this->mdl_inventory_item->getPendingQuantity($invnum);
        }

        $this->load->model('inventory/Mdl_Inventory_Item');
        $inven = $this->Mdl_Inventory_Item->get_Row('mcb_inventory_item', array('inventory_id' => $invnum));
        if ($inven->supplier_code != '') {
            $sup_name = '(' . $inven->supplier_code . ')';
        } else {
            $sup_name = '';
        }
        
        if(sizeof($pendingQtyDetails) != NULL) {
            $popHtml = '<div class="inv-detail">';
            $popHtml .= '<h4>' . $inven->name . '  ' . $sup_name . '</h4><br/>';

            $popHtml .= '<h3>Pending Quantities:</h3>';
            $popHtml .= '<table class="table table-bordered order-prod-list">';

            $popHtml .= '<thead>';
            $popHtml .= '<tr>';
            $popHtml .= '<td>S.N</td>';
            $popHtml .= '<td>Incident</td>';
            //$popHtml .= '<td>Length</td>';
            $popHtml .= '<td>Quantity</td>';
            $popHtml .= '<td>Use Detail</td>';
            
            $popHtml .= '</tr>';
            $popHtml .= '</thead>';
            $popHtml .= '<tbody>';
            $i = 1;
            $sum = 0;
            foreach ($pendingQtyDetails as $inv) {
                if( $inv['incident'] =='Invoice item' ){
                    $popHtml .= '<tr>';
                    $popHtml .= '<td>' . $i . '</td>';
                    $popHtml .= '<td>' .$inv['incident']. '</td>';
                    //$popHtml .= '<td>' . $inv['item_length'] . '</td>';
                    $popHtml .= '<td>' . floatval($inv['item_qty']) . '</td>';
                    $popHtml .= '<td><a href="' . site_url('invoices/edit/invoice_id/' . $inv['invoice_id']) . '" target="_blank">Invoice #' .  $inv['invoice_number'] . '</a></td>';
                    $popHtml .= '</tr>';
                    $sum += $inv['item_qty'];
                    $i++;
                }else{
                    
                    $inv['item_qty'] = (-1)*$inv['item_qty'];
                    $popHtml .= '<tr>';
                    $popHtml .= '<td>' . $i . '</td>';
                    $popHtml .= '<td>' .$inv['incident']. '</td>';
                    //$popHtml .= '<td>' . $inv['item_length'] . '</td>';
                    $popHtml .= '<td>' . $inv['item_qty'] . '</td>';
                    $popHtml .= '<td><a href="' . site_url('delivery_dockets/edit/docket_id/' . $inv['docket_id']) . '" target="_blank">Delivery Docket #' .  $inv['docket_number'] . '</a></td>';
                    $popHtml .= '</tr>';
                    //if( $inv['item_length'] == '' ){
                        $sum += $inv['item_qty'];
                    //}else{
                        //$sum += $inv['item_qty']*($inv['item_length']);
                    //}
                    $i++;
                }
            }            
            $popHtml .= '</tbody><tfoot><tr><td colspan="2">Total (Including length calculation)</td><td style="border-right: none;">' . $sum . '</td><td style="border-left: none;"></td></tr></tfoot>';
            $popHtml .= '</table></div>';
            echo $popHtml;
            exit;
        }else {
            echo 'There are no pending quantity for this item.';
            exit;
        }
        
    }
    
    public function doInventoryDelete() {
        $this->load->model('inventory/Mdl_Inventory_Item');
        if ($_POST) {
            $arry = $this->input->post('inven_detail');
            foreach ($arry as $value) {
                $sql ="DELETE i_item, i_history, i_pro_inv_raw "
                        . "FROM mcb_inventory_item AS i_item "
                        . "LEFT JOIN mcb_inventory_history AS i_history ON i_history.inventory_id = i_item.inventory_id "
                        . "LEFT JOIN mcb_products_inventory_raw AS i_pro_inv_raw ON i_pro_inv_raw.inventory_id = i_item.inventory_id "
                        . "WHERE i_item.inventory_id = ".$value['id'].";";
                $this->common_model->just_query($sql);
            }
            $this->session->set_flashdata('custom_success', 'Successfully deleted inventory.');
            echo 'success';
            exit;
        }
        $this->session->set_flashdata('custom_error', 'Error while applying.');
        echo 'fail';
        exit;
    }
    public function product_inventory_relation() {
        $this->load->view('product_inventory_relation');
    }
    //clear link of inventory to product
    public function delinkproductinventoryinv() {
        ini_set('max_execution_time', 3000);
        $invlistdetail = $this->input->post('id');
        $this->load->model('inventory/mdl_inventory_item');
        if ($this->mdl_inventory_item->updateinvprodrelation1inv($invlistdetail)) {
            $this->session->set_flashdata('custom_success', 'Successfully delinked');
            echo 'success';
            exit;
        }
        $this->session->set_flashdata('custom_error', 'Error while delinking.');
        // echo 'fail';
        exit;
    }
    //clear link of product to inventory
    public function delinkproductinventorypro() {
        ini_set('max_execution_time', 3000);
        $prodlistdetail = $this->input->post('pd');
        $this->load->model('inventory/mdl_inventory_item');
        if ($this->mdl_inventory_item->updateinvprodrelation1pro($prodlistdetail)) {
            $this->session->set_flashdata('custom_success', 'Successfully delinked.');
            echo 'success';
            exit;
        }
        $this->session->set_flashdata('custom_error', 'Error while delinking.');
        // echo 'fail';
        exit;
    }
    
    function viewinventorypopup() {
        $proid = $this->input->post('proid');
        $itname = $this->input->post('itname');
        $this->stock_msg = '';
        $this->stock_color = array();
        $popHtml = '<div class="inv-detail">';
        $popHtml .= '<h2>' . $itname . '</h2><br/>';
        $popHtml .= '<div class="prod-link"><a href="' . site_url() . '/inventory/form/inventory_id/' . $proid . '" target="_blank">Add Inventory | Edit Product</a></div>';
        $popHtml .= '<h3>Inventory Items:</h3>';
        $popHtml .= '<table class="table table-bordered order-prod-list">';
        $popHtml .= '<thead>';
        $popHtml .= '<tr>';
        $popHtml .= '<td>S.N</td>';
        $popHtml .= '<td>Inventory Item Name</td>';
        $popHtml .= '<td>Qty</td>';
        $popHtml .= '<td>Required Qty</td>';
        $popHtml .= '</tr>';
        $popHtml .= '</thead>';
        $popHtml .= '<tbody>';
        $inventory_items = $this->mdl_inventory_item->getInventoryItems($proid);
        if (sizeof($inventory_items) > 0) {
            $cnt = 1;
            foreach ($inventory_items as $item) {
                $popHtml .= '<tr><td>' . $cnt . '</td>';
                $popHtml .= '<td>' . $item->name . '</td>';
                $popHtml .= '<td>' . floor($item->qty) . '</td>';
                $popHtml .= '<td>' . $item->inventory_qty . '</td>';
                if (((float) ($item->qty + $invoice_item_id_qty)) - ((float) $itemS->item_qty * $item->inventory_qty) < LOW_STOCK) {
                    $this->stock_msg .= '<p>' . $item->name . " stock quantity is low. Total available quantity is " . $item->qty . '</p>';
                    $this->stock_color[] = 'yellow';
                } elseif (((float) $itemS->item_qty * $item->inventory_qty) > (float) ($item->qty + $invoice_item_id_qty)) {
                    $this->stock_msg .= "<p>Not enough " . $item->name . " inventory item for the product " . $item->item_name . '</p>';
                    $this->stock_color[] = 'red';
                } else {
                    $this->stock_msg .= '<p>' . $item->name . " stock quantity is " . $item->qty . '</p>';
                    $this->stock_color[] = 'green';
                }
                $popHtml .= '</tr>';
                $cnt++;
            }
        } else {
            $popHtml .= '<tr><td colspan="4">There are no inventory items.</td></tr>';
            $this->stock_msg .= '<p>There are no inventory items for the product ' . $itemS->item_name . '</p>';
            $this->stock_color[] = 'red';
        }
        $popHtml .= '</tbody></table></div>';
        echo $popHtml;
    }
    
    function update_o_qty_once() {
        $q = "SELECT * FROM `mcb_inventory_item`";
        $rr = $this->common_model->query_as_object($q);
        foreach ($rr as $value) {
            $qty = $this->mdl_inventory_item->get_opn_ordr_qty_total($value->inventory_id);
            if($qty != NULL){
                $this->common_model->update('mcb_inventory_item', array('o_qty'=>$qty), array('inventory_id'=>$value->inventory_id));
            }
        }
        echo 'finish';
    }
    
    function one_time() {
        
        $q = "SELECT * FROM `mcb_inventory_item` WHERE `sku` is null";
        $rr = $this->common_model->query_as_object($q);
        foreach ($rr as $value) {
            $sku_name = "SKU-$value->inventory_id";
            $this->common_model->update('mcb_inventory_item', array('sku'=>$sku_name), array('inventory_id'=>$value->inventory_id));
        }
        echo 'finish';
    }




}

?>
