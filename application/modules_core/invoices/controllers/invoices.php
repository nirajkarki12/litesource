<?php

(defined('BASEPATH')) OR exit('No direct script access allowed');

class Invoices extends Admin_Controller {

    function __construct() {
        parent::__construct();
         //ini_set('display_errors', 1); error_reporting(E_ALL); 
        $this->load->model('mdl_invoices');
        $this->load->model('housing/mdl_housing');
    }
    
    function quote_invoice_index_quote($limit) {
        $invoice_query = "SELECT SQL_CALC_FOUND_ROWS
                            i.invoice_id id, i.client_id c, i.project_id p, i.contact_id ct, i.user_id u, i.invoice_number n, 
                            IF(i.invoice_status_id = 2, IF(i.invoice_due_date < UNIX_TIMESTAMP(), 4, i.invoice_status_id), i.invoice_status_id) s, i.invoice_tax_rate_id t, CONCAT('', FORMAT(t.invoice_total, 2)) a, 
                            i.invoice_client_group_id g, 
                            IF( (i.invoice_date_emailed > '1'), i.invoice_date_emailed, i.invoice_date_entered) e, 
                            i.invoice_due_date d, i.invoice_is_quote q, i.invoice_quote_id qi, q.invoice_number qn, i.invoice_client_order_number po
                            FROM (mcb_invoices AS i)
                            JOIN mcb_invoice_amounts AS t ON t.invoice_id = i.invoice_id
                            LEFT JOIN mcb_invoices AS q ON q.invoice_id = i.invoice_quote_id
                            WHERE `i`.`invoice_is_quote` = '1'
                            ORDER BY i.invoice_date_entered DESC, i.invoice_id DESC
                            LIMIT ".$limit;
        $invoices = $this->common_model->query_as_object($invoice_query);
        $data = array(
            'invoices' => $invoices
        );
        return $data;
    }
    
    function quote_invoice_index_invoice($limit) {
        $invoice_query = "SELECT SQL_CALC_FOUND_ROWS 
                            i.invoice_id id, i.client_id c, i.project_id p, i.contact_id ct, i.user_id u, 
                            i.invoice_number n, IF(i.invoice_status_id = 2, 
                            IF(i.invoice_due_date < UNIX_TIMESTAMP(), 4, i.invoice_status_id), i.invoice_status_id) s, 
                            i.invoice_tax_rate_id t, CONCAT('', FORMAT(t.invoice_total, 2)) a, i.invoice_client_group_id g, 
                            i.invoice_date_entered e, i.invoice_due_date d, i.invoice_is_quote q, i.invoice_quote_id qi, 
                            i.smart_status mc, q.invoice_number qn, (select count(docket_id) 
                            from mcb_delivery_dockets 
                            where mcb_delivery_dockets.invoice_id = i.invoice_id) as docket_count, i.invoice_client_order_number po 
                            FROM (mcb_invoices AS i) JOIN mcb_invoice_amounts AS t ON t.invoice_id = i.invoice_id 
                            LEFT JOIN mcb_invoices AS q ON q.invoice_id = i.invoice_quote_id 
                            WHERE `i`.`invoice_is_quote` = '0' 
                            ORDER BY i.invoice_date_entered DESC, i.invoice_id DESC LIMIT ".$limit;
        
        $invoices = $this->common_model->query_as_object($invoice_query);
        $invoices_arr = array();
        foreach ($invoices as $detail) {
            $detail->s = $this->mdl_invoices->getInvoiceStatusId($detail->id);
            
            $detail->ship_odr = 0;
            //$detail->p_aok = 999999;
            $t_amt = (float) str_replace(",", "", $detail->a);
            $detail->owng_amt = $t_amt;
            $detail->p_a = $this->mdl_invoices->invoice_paid_amount($detail->id);
            if ($detail->p_a > 0) {
                //$detail->p_aok = ((float) ($t_amt) - (float) ($detail->p_a));
                $detail->owng_amt = (float) ($t_amt) - (float) ($detail->p_a);
                if(round($detail->owng_amt, 2) > 0 ){
                    if($detail->s != '3'){
                        $rem_amount = round( ((float) ($t_amt) - (float) ($detail->owng_amt)), 2);
                        if( $rem_amount > 0 ){
                            $detail->ship_odr = $rem_amount;
                        }
                    }
                }
            }
            $detail->owng_amt = number_format($detail->owng_amt, 2);
            if ($detail != NULL) {
                $invoices_arr[] = $detail;
            }
        }
        $data = array(
            'invoices' => $invoices_arr
        );
        return $data;
    }
    
    
    function index() {
    
        $this->_post_handler();
        $this->redir->set_last_index();
        $this->load->helper('text');

        $data = [
            'invoice_statuses' => json_encode($this->get_invoice_status()), 
            'clients' => $this->get_clients()
        ];

        $this->load->view('index', $data);
    }
    
    function get_quote_invoices_JSON_index() {
        // error_reporting(E_ALL); ini_set('display_errors', 1);
        $limit = $this->input->post('limit');
        $is_quote = $this->input->post('is_quote');
        $offset = $this->input->post('offset');
        $filters = $this->input->post('filters');

        $invoice_statuses = $this->get_invoice_status();

        if($filters && is_array($filters) && count($filters) > 0){
            if(array_key_exists('invoice_status', $filters) && $filters['invoice_status'] != 'false'){
                foreach ($invoice_statuses as $status) {
                    $filter_status = $filters['invoice_status'];
                    if(preg_match("/$filter_status/i", $status->invoice_status)){
                        $filters['invoice_status'] = $status->invoice_status_id;
                    }
                }
            }
        }
        if($is_quote == 1){
             $data = $this->mdl_invoices->get_quotes_records($limit, $offset, $filters);
        }else{
             $data = $this->mdl_invoices->get_invoices_records($limit, $offset, $filters);
        }
        // sleep(4);
        echo json_encode($data);
    }
    
    function create() {
    
        $quotes_or_invoices = uri_seg(1);
    
        if ($this->input->post('btn_cancel')) {
            redirect($quotes_or_invoices);
        }
        if (!$this->mdl_invoices->validate_create()) {
            $this->load->model(array('clients/mdl_clients', 'clients/mdl_contacts', 'client_groups/mdl_client_groups'));
            $this->load->helper('text');
            $data = array(
                'clients' => $this->mdl_clients->get_active(),
                'client_groups' => $this->mdl_client_groups->get(),
                'contacts' => $this->mdl_contacts->get(),
                    //'invoice_groups'  =>  $this->mdl_invoice_groups->get()
            );
            $this->load->view('choose_client', $data);
        } else {
            $this->load->module('invoices/invoice_api');
            $package = array(
                'client_id' => $this->input->post('client_id'),
                'client_group_id' => $this->input->post('client_group_id'),
                'contact_name' => $this->input->post('contact_name'),
                'project_name' => $this->input->post('project_name'),
                'invoice_date_entered' => $this->input->post('invoice_date_entered'),
                'invoice_group_id' => $this->input->post('invoice_group_id'),
                'invoice_is_quote' => $this->input->post('invoice_is_quote'),
            );
            $invoice_id = $this->invoice_api->create_invoice($package);
            redirect($quotes_or_invoices . '/edit/invoice_id/' . $invoice_id);
        }
    }

    function delete() {
        $invoice_id = uri_assoc('invoice_id');
        if ($invoice_id) {
            $this->mdl_invoices->delete($invoice_id);
        }
        redirect($this->session->userdata('last_index'));
    }

    function edit() {
        $tab_index = ($this->input->get('tab') != NULL) ? $this->input->get('tab') : (($this->session->flashdata('tab_index')) ? $this->session->flashdata('tab_index') : 0);
        $this->_post_handler();
        $this->redir->set_last_index();
        $this->load->model(
                array(
                    'clients/mdl_clients',
                    'projects/mdl_projects',
                    'clients/mdl_contacts',
                    'client_groups/mdl_client_groups',
                    'payments/mdl_payments',
                    'orders/mdl_orders',
                    'tax_rates/mdl_tax_rates',
                    'invoice_statuses/mdl_invoice_statuses',
                    'templates/mdl_templates',
                    'users/mdl_users',
                    'delivery_dockets/mdl_delivery_dockets'
                )
        );

        $this->load->helper('text');

        $params = array(
            'where' => array(
                'mcb_invoices.invoice_id' => uri_assoc('invoice_id')
            )
        );

        /*
          if (!$this->session->userdata('global_admin')) {

          $params['where']['mcb_invoices.user_id'] = $this->session->userdata('user_id');

          }
         */
        $invoice = $this->mdl_invoices->get($params);

//        echo '<pre>'; print_r($invoice); exit;
        //log_message('INFO', 'Invoice tax rate :'.$invoice->tax_rate_name);

        if (!$invoice) {

            redirect('dashboard/record_not_found');
        }

        $data = array(
            'invoice' => $invoice,
            'internaldetail' => $this->mdl_invoices->getQuoteInternalFromInvoiceId($invoice->invoice_id)['internal_data'],
            'internaldetail_count' => $this->mdl_invoices->getQuoteInternalFromInvoiceId($invoice->invoice_id)['internal_count'],
            'payments' => $this->mdl_invoices->get_invoice_payments($invoice->invoice_id),
            'history' => $this->mdl_invoices->get_invoice_history($invoice->invoice_id),
            //'invoice_items' => $this->mdl_invoices->get_invoice_items($invoice->invoice_id),
            //'invoice_tax_rates' =>  $this->mdl_invoices->get_invoice_tax_rates($invoice->invoice_id),
            //'orders' => $this->mdl_orders->get_invoice_orders($invoice->invoice_id),
            //'dockets' => $this->mdl_delivery_dockets->get_invoice_dockets($invoice->invoice_id),
            'dockets' => $this->mdl_delivery_dockets->get_delvery_dockets_by_invoice_id($invoice->invoice_id),
            'tags' => $this->mdl_invoices->get_invoice_tags($invoice->invoice_id),
            'invoice_status_check' => $this->mdl_invoices->getInvoiceStatusId($invoice->invoice_id),
            'projects' => $this->mdl_projects->get_active(),
            'clients' => $this->mdl_clients->get_active(),
            'contacts' => $this->mdl_contacts->get_client_contacts($invoice->client_id),
            'tax_rates' => $this->mdl_tax_rates->get(),
            'client_groups' => $this->mdl_client_groups->get(),
            'invoice_statuses' => $this->mdl_invoice_statuses->get(),
            'tab_index' => $tab_index,
            'custom_fields' => $this->mdl_fields->get_object_fields(1),
            'users' => $this->mdl_users->get(),
        );
        if ($invoice->invoice_is_quote == '1') {
            if($invoice->invoice_date_emailed > '1'){
               $invoice->invoice_date_entered = $invoice->invoice_date_emailed; 
            }
            // if this is quote..
            $data['invoice_status_check'] = $invoice->invoice_status_id;
        }else{
            $data['docket_payment_amount'] = $this->mdl_invoices->invoice_paid_amount($invoice->invoice_id);
            $data['docket_payment_history'] = $this->mdl_delivery_dockets->docket_payment_history_by_invoice_id($invoice->invoice_id);
        }
        if ($invoice->invoice_is_quote == '0' && $invoice->invoice_quote_id != NULL) {
            $data['orders'] = $this->mdl_orders->get_invoice_orders($invoice->invoice_quote_id);
        } else {
            //this is the invoice not quote
            //invoice_quote_id is because the order is generated based on the quote not final invoice
            //quote is also saved in mcb_invoices
            //The quote and invoice are being saved in same table, with multiple rows
            $data['orders'] = $this->mdl_orders->get_invoice_orders($invoice->invoice_id);
        }
        //// dockets count
        $data['docket_count'] = '0';
        $data['docket_count'] = count($data['dockets']);
        
        //echo '<pre>'; print_r($data['dockets']); exit;
        $this->load->view('invoice_edit', $data);
    }

    function get_invoice_inventories($invoice_items) {
        // error_reporting(E_ALL); ini_set('display_errors', 1);
        //========= get all inventories items===========
        
        //// for pending qty
        $sqlCnt = "select SUM(IF(mis.status='1', mis.qty_pending, 0)) as pending_qty, mpi.product_id from mcb_item_stock as mis inner join mcb_products_inventory as mpi on mis.inventory_id=mpi.inventory_id GROUP BY mpi.product_id";
        $mcb_item_stock_db = $this->common_model->query_as_object($sqlCnt);
        $mcb_item_stock_arr = array();
        if( $mcb_item_stock_db != NULL ){
            foreach ($mcb_item_stock_db as $stock) {
                if( !isset($mcb_item_stock_arr[$stock->product_id]) ){
                    $mcb_item_stock_arr[$stock->product_id] = $stock;
                }
            }
        }
        
        
        $all_inventory = array();
        if (($invoice_items) !== FALSE) {
            foreach ($invoice_items as $i_item) {
                
                //echo '<pre>'; print_r($i_item);
                $as_invoice_item_id = "";
                if( isset($i_item->invoice_item_id) ){
                    $as_invoice_item_id = "{$i_item->invoice_item_id} AS invoice_item_id, ";
                }
                
                if ($i_item->product_id > 0) {
                    
                    if ($i_item->item_length > 0) {
                        $i_item->item_qty = $i_item->item_qty * $i_item->item_length;
                    }
                    
                    //check if item is group or part
                    $ispart = $this->checkItemtype($i_item->product_id);
                    // this is grouped
                    if ($ispart == '1') {
                        $qry = "SELECT {$as_invoice_item_id} ii.inventory_id, ii.name, ii.description, ii.use_length, ii.supplier_code, ii.supplier_description, ii.supplier_price, "
                                . "pi.product_id, (pi.inventory_qty*" . $i_item->item_qty . ") AS order_qty, (select SUM(IF(mis.status='1', mis.qty_pending, 0)) from mcb_item_stock as mis where mis.inventory_id=ii.inventory_id) as pending_qty,"
                                . "ii.supplier_id, ii.qty AS available_qty, "
                                . $this->db->escape($i_item->item_name). " AS invc_item_name, '" . $i_item->item_length . "' AS invc_item_length, '" . $i_item->item_type . "' AS invc_item_type, "
                                . "'" . $i_item->item_type . "' AS invc_item_type, " . $this->db->escape($i_item->item_description) . " AS invc_item_description, "
                                . "'" . $i_item->item_per_meter . "' AS invc_item_per_meter, '" . $i_item->item_price . "' AS invc_item_price, ii.base_price,"
                                . "(SELECT SUM( (oit.item_qty - oit.partial_qty) * IF(oit.item_length > 0,oit.item_length,1)) 
                                    -- SUM( oit.item_qty * IF(oit.item_length > 0,oit.item_length,1)) 
                                    FROM `mcb_order_inventory_items` AS oit 
                                    LEFT JOIN mcb_orders AS ord ON (oit.order_id = ord.order_id) WHERE oit.inventory_id = ii.inventory_id AND 
                                    -- ord.order_status_id != '3' 
                                    ord.order_status_id = '2' AND oit.stock_status!='1') as open_order_qty, " 
                                //. "(SELECT SUM( oit.item_qty * IF(oit.item_length > 0,oit.item_length,1)) FROM `mcb_order_inventory_items` AS oit LEFT JOIN mcb_orders AS ord ON (oit.order_id = ord.order_id) WHERE oit.inventory_id = ii.inventory_id AND ord.order_status_id != '3' AND ord.order_status_id != '1') as open_order_qty, "
                                . "'" . $i_item->item_index . "' AS invc_item_index, '" . $i_item->item_qty . "' AS invc_item_qty "
                                . "FROM mcb_inventory_item as ii "
                                . "INNER JOIN mcb_products_inventory AS pi ON ii.inventory_id= pi.inventory_id "
                                . "WHERE pi.product_id='" . $i_item->product_id . "' AND ii.is_arichved != '1'";
                    } else {
                        //this is part
                        $qry = "SELECT {$as_invoice_item_id} ii.inventory_id, ii.name, ii.description, ii.use_length, ii.supplier_code, ii.supplier_description, ii.supplier_price, "
                                . "ii.inventory_id as product_id, " . $i_item->item_qty . " AS order_qty, (select SUM(IF(mis.status='1', mis.qty_pending, 0)) from mcb_item_stock as mis where mis.inventory_id=ii.inventory_id) as pending_qty, "
                                . "ii.supplier_id, ii.qty AS available_qty, "
                                . $this->db->escape($i_item->item_name). " AS invc_item_name, '" . $i_item->item_length . "' AS invc_item_length, '" . $i_item->item_type . "' AS invc_item_type, "
                                . "'" . $i_item->item_type . "' AS invc_item_type, " . $this->db->escape($i_item->item_description) . " AS invc_item_description, "
                                . "'" . $i_item->item_per_meter . "' AS invc_item_per_meter, '" . $i_item->item_price . "' AS invc_item_price, ii.base_price, "
                                . "(SELECT SUM( (oit.item_qty - oit.partial_qty) * IF(oit.item_length > 0,oit.item_length,1)) 
                                    -- SUM( oit.item_qty * IF(oit.item_length > 0,oit.item_length,1)) 
                                    FROM `mcb_order_inventory_items` AS oit 
                                    LEFT JOIN mcb_orders AS ord ON (oit.order_id = ord.order_id) WHERE oit.inventory_id = ii.inventory_id AND 
                                    -- ord.order_status_id != '3' 
                                    ord.order_status_id = '2' AND oit.stock_status!='1') as open_order_qty, "
                                //. "(SELECT SUM( oit.item_qty * IF(oit.item_length > 0,oit.item_length,1)) FROM `mcb_order_inventory_items` AS oit LEFT JOIN mcb_orders AS ord ON (oit.order_id = ord.order_id) WHERE oit.inventory_id = ii.inventory_id AND ord.order_status_id != '3' AND ord.order_status_id != '1') as open_order_qty, "
                                . "'" . $i_item->item_index . "' AS invc_item_index, '" . $i_item->item_qty . "' AS invc_item_qty "
                                . "FROM mcb_inventory_item as ii "
                                . "WHERE ii.inventory_id='" . $i_item->product_id . "'";
                    }
                    
                    $product_inventory = $this->common_model->query_as_array($qry);
                    //print_r($product_inventory); echo '_____';
                    if (is_array($product_inventory) && sizeof($product_inventory) > 0) {
                        $this->load->model("inventory/mdl_inventory_item");
                        foreach ($product_inventory as $inventory) {
                            
                            /// getting pending qty
                            $inv_pros = array();
                            $qry_rip_chk = $this->common_model->query_as_array("SELECT mpi.product_id AS pid, mpi.inventory_id AS iid, mcb_inventory_item.inventory_type AS type FROM `mcb_products_inventory` AS mpi LEFT JOIN mcb_inventory_item ON mpi.product_id = mcb_inventory_item.inventory_id WHERE mpi.`inventory_id` = '{$inventory['inventory_id']}'");
                            if ($qry_rip_chk != NULL) {
                                foreach ($qry_rip_chk as $ip) {
                                    if( ($ip['pid'] == $ip['iid']) || ($ip['type'] > '0') ){
                                        $inv_pros[] = $ip['pid'];
                                    }
                                }
                            }
                            $inventory['pending_qty'] = $this->mdl_inventory_item->get_pending_qty($inventory['inventory_id'], $inv_pros);
                            //if( isset($mcb_item_stock_arr[$inventory['inventory_id']]) ){
                                //$inventory['pending_qty'] = $mcb_item_stock_arr[$inventory['inventory_id']]->pending_qty;
                            //}
                            $inventory['opnorder_minus_pending'] = $inventory['pending_qty'] - $inventory['open_order_qty'];
                            
                            if (count($all_inventory) > 0) {
                                $key = array_search($inventory['inventory_id'], array_column($all_inventory, 'inventory_id'));
                                if ($key === FALSE) {
                                    array_push($all_inventory, $inventory);
                                } else {

                                    //If Length && Type are same for the same Item, we add else we won't
                                    if ($all_inventory[$key]['invc_item_type'] == $inventory['invc_item_type'] && $all_inventory[$key]['invc_item_length'] == $inventory['invc_item_length']) {
                                        $all_inventory[$key]['order_qty'] = $all_inventory[$key]['order_qty'] + $inventory['order_qty'];
                                    } else {
                                        array_push($all_inventory, $inventory);
                                    }
                                }
                            } else {
                                array_push($all_inventory, $inventory);
                            }
                        }
                    }
                }
            }
        }
        //exit;
        return $all_inventory;
    }
    
    function get_invoice_inventories_to_create_order($invoice_items) {
        // error_reporting(E_ALL); ini_set('display_errors', 1);
        //========= get all inventories items===========
        
        //// for pending qty
        $sqlCnt = "select SUM(IF(mis.status='1', mis.qty_pending, 0)) as pending_qty, mpi.product_id from mcb_item_stock as mis inner join mcb_products_inventory as mpi on mis.inventory_id=mpi.inventory_id GROUP BY mpi.product_id";
        $mcb_item_stock_db = $this->common_model->query_as_object($sqlCnt);
        $mcb_item_stock_arr = array();
        if( $mcb_item_stock_db != NULL ){
            foreach ($mcb_item_stock_db as $stock) {
                if( !isset($mcb_item_stock_arr[$stock->product_id]) ){
                    $mcb_item_stock_arr[$stock->product_id] = $stock;
                }
            }
        }
        
        
        $all_inventory = array();
        if (($invoice_items) !== FALSE) {
            foreach ($invoice_items as $i_item) {
                
                //echo '<pre>'; print_r($i_item);
                $as_invoice_item_id = "";
                if( isset($i_item->invoice_item_id) ){
                    $as_invoice_item_id = "{$i_item->invoice_item_id} AS invoice_item_id, ";
                }
                
                if ($i_item->product_id > 0) {
                    
                    if ($i_item->item_length > 0) {
                        $i_item->item_qty = $i_item->item_qty * $i_item->item_length;
                    }
                    
                    //check if item is group or part
                    $ispart = $this->checkItemtype($i_item->product_id);
                    // this is grouped
                    if ($ispart == '1') {
                        $qry = "SELECT {$as_invoice_item_id} ii.inventory_id, ii.name, ii.description, ii.use_length, ii.supplier_code, ii.supplier_description, ii.supplier_price, "
                                . "pi.product_id, (pi.inventory_qty*" . $i_item->item_qty . ") AS order_qty, (select SUM(IF(mis.status='1', mis.qty_pending, 0)) from mcb_item_stock as mis where mis.inventory_id=ii.inventory_id) as pending_qty,"
                                . "ii.supplier_id, ii.qty AS available_qty, "
                                . $this->db->escape($i_item->item_name). " AS invc_item_name, '" . $i_item->item_length . "' AS invc_item_length, '" . $i_item->item_type . "' AS invc_item_type, "
                                . "'" . $i_item->item_type . "' AS invc_item_type, " . $this->db->escape($i_item->item_description) . " AS invc_item_description, "
                                . "'" . $i_item->item_per_meter . "' AS invc_item_per_meter, '" . $i_item->item_price . "' AS invc_item_price, ii.base_price,"
                                
                                . "(SELECT SUM( (oit.item_qty - oit.partial_qty) * IF(oit.item_length > 0,oit.item_length,1)) 
                            -- SUM( oit.item_qty * IF(oit.item_length > 0,oit.item_length,1)) 
                            FROM `mcb_order_inventory_items` AS oit 
                            LEFT JOIN mcb_orders AS ord ON (oit.order_id = ord.order_id) WHERE oit.inventory_id = ii.inventory_id AND 
                            -- ord.order_status_id != '3' 
                            ord.order_status_id = '2' AND oit.stock_status!='1') as open_order_qty, "    
                                
//                                . "(SELECT SUM( oit.item_qty * IF(oit.item_length > 0,oit.item_length,1)) FROM `mcb_order_inventory_items` AS oit LEFT JOIN mcb_orders AS ord ON (oit.order_id = ord.order_id) WHERE oit.inventory_id = ii.inventory_id AND ord.order_status_id != '3' AND ord.order_status_id != '1') as open_order_qty, "
                                . "'" . $i_item->item_index . "' AS invc_item_index, '" . $i_item->item_qty . "' AS invc_item_qty "
                                . "FROM mcb_inventory_item as ii "
                                . "INNER JOIN mcb_products_inventory AS pi ON ii.inventory_id= pi.inventory_id "
                                . "WHERE pi.product_id='" . $i_item->product_id . "' AND ii.is_arichved != '1'";
                    } else {
                        //this is part
                        $qry = "SELECT {$as_invoice_item_id} ii.inventory_id, ii.name, ii.description, ii.use_length, ii.supplier_code, ii.supplier_description, ii.supplier_price, "
                                . "ii.inventory_id as product_id, " . $i_item->item_qty . " AS order_qty, (select SUM(IF(mis.status='1', mis.qty_pending, 0)) from mcb_item_stock as mis where mis.inventory_id=ii.inventory_id) as pending_qty, "
                                . "ii.supplier_id, ii.qty AS available_qty, "
                                . $this->db->escape($i_item->item_name). " AS invc_item_name, '" . $i_item->item_length . "' AS invc_item_length, '" . $i_item->item_type . "' AS invc_item_type, "
                                . "'" . $i_item->item_type . "' AS invc_item_type, " . $this->db->escape($i_item->item_description) . " AS invc_item_description, "
                                . "'" . $i_item->item_per_meter . "' AS invc_item_per_meter, '" . $i_item->item_price . "' AS invc_item_price, ii.base_price, "
                                
                                . "(SELECT SUM( (oit.item_qty - oit.partial_qty) * IF(oit.item_length > 0,oit.item_length,1)) 
                            -- SUM( oit.item_qty * IF(oit.item_length > 0,oit.item_length,1)) 
                            FROM `mcb_order_inventory_items` AS oit 
                            LEFT JOIN mcb_orders AS ord ON (oit.order_id = ord.order_id) WHERE oit.inventory_id = ii.inventory_id AND 
                            -- ord.order_status_id != '3' 
                            ord.order_status_id = '2' AND oit.stock_status!='1') as open_order_qty, "    
                                
//                                . "(SELECT SUM( oit.item_qty * IF(oit.item_length > 0,oit.item_length,1)) FROM `mcb_order_inventory_items` AS oit LEFT JOIN mcb_orders AS ord ON (oit.order_id = ord.order_id) WHERE oit.inventory_id = ii.inventory_id AND ord.order_status_id != '3' AND ord.order_status_id != '1') as open_order_qty, "
                                . "'" . $i_item->item_index . "' AS invc_item_index, '" . $i_item->item_qty . "' AS invc_item_qty "
                                . "FROM mcb_inventory_item as ii "
                                . "WHERE ii.inventory_id='" . $i_item->product_id . "'";
                    }
                    
                    $product_inventory = $this->common_model->query_as_array($qry);
                    
                    
                    
                    
                    
                    //print_r($product_inventory); echo '_____';
                    if (is_array($product_inventory) && sizeof($product_inventory) > 0) {
                        $this->load->model("inventory/mdl_inventory_item");
                        foreach ($product_inventory as $inventory) {
                            
                            /// getting pending qty
                            $inv_pros = array();
                            $qry_rip_chk = $this->common_model->query_as_array("SELECT mpi.product_id AS pid, mpi.inventory_id AS iid, mcb_inventory_item.inventory_type AS type FROM `mcb_products_inventory` AS mpi LEFT JOIN mcb_inventory_item ON mpi.product_id = mcb_inventory_item.inventory_id WHERE mpi.`inventory_id` = '{$inventory['inventory_id']}'");
                            if ($qry_rip_chk != NULL) {
                                foreach ($qry_rip_chk as $ip) {
                                    if( ($ip['pid'] == $ip['iid']) || ($ip['type'] > '0') ){
                                        $inv_pros[] = $ip['pid'];
                                    }
                                }
                            }
                            $inventory['pending_qty'] = $this->mdl_inventory_item->get_pending_qty($inventory['inventory_id'], $inv_pros);
                            //if( isset($mcb_item_stock_arr[$inventory['inventory_id']]) ){
                                //$inventory['pending_qty'] = $mcb_item_stock_arr[$inventory['inventory_id']]->pending_qty;
                            //}
                            $inventory['opnorder_minus_pending'] = $inventory['pending_qty'] - $inventory['open_order_qty'];
                            
                            if (count($all_inventory) > 0) {
                                $key = array_search($inventory['inventory_id'], array_column($all_inventory, 'inventory_id'));
                                if ($key === FALSE) {
                                    array_push($all_inventory, $inventory);
                                } else {
                                    
                                    //// for non dynamic
                                    if( $inventory['use_length'] != '1' ){                                        
                                        //If Length && Type are same for the same Item, we add else we won't
//                                        if ($all_inventory[$key]['invc_item_type'] == $inventory['invc_item_type'] && $all_inventory[$key]['invc_item_length'] == $inventory['invc_item_length']) {
                                        $all_inventory[$key]['order_qty'] = $all_inventory[$key]['order_qty'] + $inventory['order_qty'];
                                        $all_inventory[$key]['invc_item_qty'] = $all_inventory[$key]['invc_item_qty'] + $inventory['invc_item_qty'];
                                            
                                    }else{
                                        
                                        
                                        //If Length && Type are same for the same Item, we add else we won't
//                                        if ($all_inventory[$key]['invc_item_type'] == $inventory['invc_item_type'] && $all_inventory[$key]['invc_item_length'] == $inventory['invc_item_length']) {
//                                            $all_inventory[$key]['order_qty'] = $all_inventory[$key]['order_qty'] + $inventory['order_qty'];
//                                        } else {
//                                            array_push($all_inventory, $inventory);
//                                        }
                                        array_push($all_inventory, $inventory);
                                    }
                                    
                                    
//                                    echo '<pre>';
//                                    print_r($key);
//                                    print_r($all_inventory);
//                                    print_r($inventory);
////                                    print_r($key);
//                                    die;
                                    
                                    
                                }
                            } else {
                                array_push($all_inventory, $inventory);
                            }
                        }
                    }
                }
            }
        }
        
//        echo '<pre>';
//        print_r($all_inventory);
//        die;
        
        //exit;
        return $all_inventory;
    }

    public function checkItemtype($id) {
        $sql = "select inventory_type from mcb_inventory_item where inventory_id = '" . $id . "'";
        $query = $this->db->query($sql);
        $detail = $query->row();
        return $detail->inventory_type;
    }
    
    function queryRow($qry) {
        $q = $this->db->query($qry);
        return $q->row();
    }
    
    function extract_all_supliers($all_inventories) {
        
        $supplierids = array_unique(array_column($all_inventories, 'supplier_id'));
        $suppliers = array();
        foreach ($supplierids as $suplier_id) {
            $suppliers[] = $this->queryRow('SELECT client_id, client_name, client_tax_rate_id FROM mcb_clients WHERE client_id = "'.$suplier_id.'"');
        }
        return $suppliers;
    }
    
    function check_order_creation() {

        //error_reporting(E_ALL); ini_set('display_errors', 1);
        $invoice_id = uri_assoc('invoice_id');
//        die;
        $invoice_items = $this->common_model->get_all_as_object('mcb_invoice_items', array('invoice_id' => $invoice_id));

        //========= get all invoice items===========
        if (($invoice_items) !== FALSE) {
            //=========check for new product============
            $this->load->model('products/mdl_products');
        

            if (!isset($_GET['step'])) {
                $is_new_product = FALSE;
                $new_products = array();
              
                //detect possible dynamic products
                $possible_dynamic_products = array();
                
                foreach ($invoice_items as $i_item) {
      
                    // for dynamic product
                    $item_name = span_to_mm($i_item->item_name, $i_item->item_length, 1000);
                 

                    if ($i_item->item_qty != '0.00' && $i_item->product_id == '0' && $i_item->item_qty > 0) {
                     
                        $existing = $this->mdl_products->checkDuplicate_product_supplier_code_with_return($item_name);
                        
                        if ($existing) { // and matching supplier code
                            if (($existing->is_arichved == '1')) {
                                // $new_products .= ' '.$item_name.',';
                                $i_item->possible_item = $this->detectPossibleItem($i_item->item_name);
                                array_push($new_products, $i_item);
                                $is_new_product = TRUE;
                            }
                        } elseif ($existing == FALSE) {
                            //$new_products .= ' '.$item_name.',';
                            $i_item->possible_item = $this->detectPossibleItem($i_item->item_name);
                            array_push($new_products, $i_item);
                            $is_new_product = TRUE;
                        }
                    }

                    //issue - product is deleted after creating quote
                    if ($i_item->product_id != '0' && $i_item->item_qty > 0) {
                        $sql = 'select * from mcb_products where product_id = "' . $i_item->product_id . '"';
                        $query = $this->db->query($sql);
                        $res = $query->row();
                        if ($res->is_arichved == '1' || $query->num_rows() == 0) {
                            $i_item->possible_item = $this->detectPossibleItem($i_item->item_name);
                            array_push($new_products, $i_item);
                            $is_new_product = TRUE;
                        }
                    }
                }

                //echo '<pre>'; print_r($new_products); exit;

                
                
                if ($is_new_product == TRUE) {
//                echo 'dddd';
//                die;
                    echo json_encode(array('result' => 'problem_redirect', 'detail' => 'New products detected, Click OK to create invoice or Cancel to update new products.'));
                    exit;
                    // $this->no_product_form($new_products);
                    //echo json_encode(array('result' => 'new_item_detected','detail' => 'Following Items were not found in inventory:'. rtrim($new_products,',').'. Please add first.')); exit;
                }
            }

            //========== check for orders =========
            $is_supplier = TRUE;
            $is_enough_items = FALSE;

//            echo '<pre>';
//            print_r($invoice_items);
            $all_inventories = $this->get_invoice_inventories($invoice_items);
     
            $all_supliers = $this->extract_all_supliers($all_inventories);

            if ($all_inventories != NULL) {
                $sufficient_items = '';
                $insufficient_items = '';
                $suff_sn = 1;
                foreach ($all_inventories as $inventorie) {
                    
                    //---supplier----
                    if (!((int) $inventorie['supplier_id'] > '0') || $inventorie['supplier_id'] == NULL) {
                        $is_supplier = FALSE;
                        break;
                    }
                    
                    $insuf_chk = ($inventory['order_qty'] - $inventory['available_qty'])+$inventory['opnorder_minus_pending'];
                    if( $insuf_chk > '0' ){
                        $insufficient_items .= $inventorie['name'] . ', ';
                    }else{
                        $sufficient_items .= '<p style="font-size: 13px;font-weight: 900;margin-bottom: 1px;margin-top: 1px;">' . $suff_sn++ . ') ' . $inventorie['name'] . '</p> ';
                    }
//                    if ($inventorie['order_qty'] > ($inventorie['available_qty'] - $inventorie['pending_qty'])) {
//                        $insufficient_items .= $inventorie['name'] . ', ';
//                    } else {
//                        $sufficient_items .= '<p style="font-size: 13px;font-weight: 900;margin-bottom: 1px;margin-top: 1px;">' . $suff_sn++ . ') ' . $inventorie['name'] . '</p> ';
//                    }
                }
                
                //---supplier check----
                if ($is_supplier == FALSE) {
                    echo json_encode(array('result' => 'no_suppliers', 'detail' => 'Please fix inventory with no supplier.'));
                    exit;
                }
                if (($insufficient_items != '') || ($sufficient_items != '')) {
                    $sup_html = '<h6>Please select the Suppliers to use as per Hand Stock (Default will be as per Quote)</h6><br>';
                    foreach ($all_supliers as $suplier_id) {
                        $sup_html .= '<input type="checkbox" name="suplier_ids[]" value="'.$suplier_id->client_id.'"> '.$suplier_id->client_name.'<br>';
                    }
                    $s_arrs = array(
                        'result' => 'stuck_yes_no',
                        'detail'=>$sup_html
                    );
                    echo json_encode($s_arrs); exit;
                }
            }
        } else {
            echo json_encode(array('result' => 'no_items_selected', 'detail' => 'Please Select Items First.'));
            exit;
        }
        echo json_encode(array('result' => 'success', 'detail' => ''));
        exit;
    }

    function detectPossibleItem($name) {
        $name = reverse_mm($name);

        $sql = 'select * from mcb_inventory_item where name = "' . $name . '" and is_arichved != "1"';
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            return $query->row();
        }

        return $name;
    }

    function no_product_form($new_products) {

        $data['suppliers'] = $this->common_model->get_all_as_object('mcb_clients', array('client_is_supplier' => '1'), '`client_name` ASC');
        $data['new_products'] = $new_products;
        $html = $this->load->view('invoices/new_quote_product_form', $data, TRUE);
        echo json_encode(
                array(
                    'result' =>
                    'new_item_detected',
                    'detail' => $html
                )
        );
        exit;
    }

//    function check_order_creation_old() {
//
//        error_reporting(E_ALL);
//        ini_set('display_errors', 1);
//        $this->load->model('orders/mdl_orders');
//        $invoice_id = uri_assoc('invoice_id');
//        $dont_create_order_enough_stock = TRUE;
//        $dont_create_order_unlinked_products = FALSE;
//        $invoice_items = $this->mdl_orders->get_invoice_itemsbyid($invoice_id);
//
//        $this->load->model('products/mdl_products');
//        $redirect_to_product = FALSE;
//        $dynamic_pro_has_no_inv_length_count = 0;
//        $dynamic_pro_has_no_inv_length_product_name = '';
//        if (sizeof($invoice_items) > 0) {
//
//            foreach ($invoice_items as $i_item) {
//
//                $product_dynamic = FALSE;
//                if ($i_item->product_dynamic == '1') {
//                    $product_dynamic = TRUE;
//                }
//                $qry = 'SELECT ii.inventory_id, ii.use_length, mcb_products_inventory.product_id '
//                        . 'FROM mcb_inventory_item as ii '
//                        . 'INNER JOIN mcb_products_inventory ON ii.inventory_id=mcb_products_inventory.inventory_id '
//                        . 'WHERE mcb_products_inventory.product_id="' . $i_item->product_id . '"';
//                $pro_inv = $this->mdl_orders->query($qry);
//                $is_inv_length = FALSE;
//                foreach ($pro_inv as $value0) {
//                    if ($value0['use_length'] == '1') {
//                        $is_inv_length = TRUE;
//                    }
//                }
//                if (($product_dynamic == TRUE) && ($is_inv_length == FALSE)) {
//                    $dynamic_pro_has_no_inv_length_count++;
//                    $new_name = str_replace(array('<span>', '</span>'), '', $i_item->item_name);
//                    $dynamic_pro_has_no_inv_length_product_name .= $new_name . ', ';
//                }
//                $item_qty = $i_item->item_qty;
//                $item_length = $i_item->item_length;
//                //The product was not created at the time when the quote was created
//                //but later before converting to order/invoice, we create the product and might have linked
//                //so at this step, if the product id is 0, and if there is product name, we will actually get the exact product_id by the product name
//
//
//                $existing = $this->mdl_products->checkDuplicate_product_supplier_code_with_return($i_item->item_name);
//
//                //i added this but it will never wokr because of supplier code not existing.. 
//                //ive added a link between invoiceitem and invenotry item to update productid
////                                echo '<pre>';
////                                print_r($existing);
//
//                if ($existing) { // and matching supplier code
//                    if (($existing->is_arichved == '1')) {
//                        $redirect_to_product = TRUE;
//                        break;
//                    }
//                    $i_item->product_id = $existing->product_id;
//                    $i_item->supplier_id = $existing->supplier_id;
//                }
//                if (($i_item->supplier_id == '0') && ($i_item->item_name == '') && ($i_item->item_length == '') && ($i_item->item_type == '')) {
//                    $redirect_to_product = FALSE;
//                } else if (($i_item->product_id <= '0')) {
//                    $redirect_to_product = TRUE;
//                    break;
//                }
//                if (($i_item->item_name != '')) {
//
//                    $linked_invs = $this->mdl_orders->get_related_invs($i_item->product_id);
//
//                    if (sizeof($linked_invs) > 0) {
//                        foreach ($linked_invs as $li) {
//
//                            if (($i_item->product_dynamic == '1') && ( $li->use_length == '1' ) && ( ($item_length != '') || ($item_length != '0') )) {
//                                $item_qty = $item_qty * $item_length;
//                            }
//                            if ($li->qty < $li->inventory_qty * $item_qty) {
//                                $dont_create_order_enough_stock = FALSE;
//                            }
//                        }
//                    } else {
//                        $dont_create_order_unlinked_products = TRUE;
//                    }
//                }
//            }
//        }
//
////        echo $dont_create_order_enough_stock;
////        
////        echo 'ggggg';
////        die;
//
//
//        if (!sizeof($invoice_items) > 0) {
//            echo json_encode(array('result' => 'no_items_selected', 'detail' => 'Please Select Items First.'));
//            exit;
//        }
//
////        $r_i_chk = $this->check_related_inventory($invoice_items);
////        if($r_i_chk != FALSE){
////            echo json_encode(array('result' => 'problem_inv_link','detail' => utf8_encode('Products '.$r_i_chk.'  has no linked inventory. Please link to at least one inventory before converting to order.'))); exit;
////        }
//
//        if ($redirect_to_product) {
//            echo json_encode(array('result' => 'problem_redirect', 'detail' => 'New products detected, Click OK to create invoice or Cancel to update new products.'));
//            exit;
//        }
//        if ($dynamic_pro_has_no_inv_length_count > '0') {
//            echo json_encode(array('result' => 'problem', 'detail' => 'Please note that ' . $dynamic_pro_has_no_inv_length_product_name . ' has no related inventory using length. Are you sure you wish to continue?'));
//            exit;
//        }
////        if(isset($dont_create_order_unlinked_products) && $dont_create_order_unlinked_products == TRUE):
////            echo json_encode(array('result' => 'problem','detail' => 'Not all products have inventory, would you like to continue?')); exit;
////        elseif(isset($dont_create_order_enough_stock) && $dont_create_order_enough_stock == TRUE):
////            echo json_encode(array('result' => 'problem','detail' => 'There is sufficient stock available, no orders will be created.')); exit;
////        endif;
//
//
//        if (isset($dont_create_order_enough_stock) && $dont_create_order_enough_stock == TRUE) {
//            echo json_encode(array('result' => 'problem', 'detail' => 'There is sufficient stock available, no orders will be created.'));
//            exit;
//        }
//
//        echo json_encode(array('result' => 'success', 'detail' => ''));
//        exit;
//    }

    function update_invoice_items_product_ids($invoice_id) {
        $invoice_items = $this->common_model->get_all_as_object('mcb_invoice_items', array('invoice_id' => $invoice_id));
        if ($invoice_items != NULL) {
            log_message('INFO', 'Updating mcb_invoice_items.product_id for invoice: ' . $invoice_id);
            foreach ($invoice_items as $i_item) {
                $item_name = span_to_mm($i_item->item_name, $i_item->item_length, 1000);
                $product = $this->common_model->get_row('mcb_products', array('product_name' => $item_name, 'is_arichved !=' => '1'));
                if ($product != NULL) {
                    $this->common_model->update('mcb_invoice_items', array('product_id' => $product->product_id), array('invoice_item_id' => $i_item->invoice_item_id));
                }
            }
        }
    }

    function check_related_inventory($invoice_items) {
        $no_inv_product_name = '';
        if (sizeof($invoice_items) > 0) {
            foreach ($invoice_items as $i_item) {
                $qry = 'SELECT ii.inventory_id, ii.use_length, mcb_products_inventory.product_id '
                        . 'FROM mcb_inventory_item as ii '
                        . 'INNER JOIN mcb_products_inventory ON ii.inventory_id=mcb_products_inventory.inventory_id '
                        . 'WHERE mcb_products_inventory.product_id="' . $i_item->product_id . '"';
                $pro_inv = $this->mdl_orders->query($qry);
                if (($pro_inv == NULL) && ($i_item->item_name != '') && ($i_item->item_qty != '0.00')) {
                    $new_name = str_replace(array('<span>', '</span>'), '', $i_item->item_name);
                    // $no_inv_product_name .= '<a href="'. site_url('products/form/product_id/'.$i_item->product_id).'">'.$new_name.'</a>, ';
                    $no_inv_product_name .= $new_name . ', ';
                }
            }
            if ($no_inv_product_name == '') {
                return FALSE;
            } else {
                return $no_inv_product_name;
            }
        }
        return FALSE;
    }

    public function deleteinternalnote() {
        $itemid = uri_assoc('internal_id');
        $invoice_id = uri_assoc('invoice_id');
        $this->session->set_flashdata('tab_index', 6);
        if ($this->mdl_invoices->deleteinternalnote($itemid)) {
            $this->session->set_flashdata('custom_success', "Internal note successfully deleted.");
        } else {
            $this->session->set_flashdata('custom_error', "Something went wrong while deleting internal note. Please try again later.");
        }
        redirect('quotes/edit/invoice_id/' . $invoice_id);
    }

    public function getinvoiceinternals() {
        $invoice_id = uri_assoc('invoice_id');
        if (!(int) $invoice_id < 1) {
            $internaldetail = $this->mdl_invoices->getQuoteInternalFromInvoiceId($invoice_id);
            echo json_encode($internaldetail);
            exit;
        }
        echo FALSE;
        exit;
    }

    public function addinternalnote() {
        $invoice_id = uri_assoc('invoice_id');
        if (!(int) $invoice_id < 1) {
            if ($this->mdl_invoices->addinternalnote($invoice_id)) {
                $this->session->set_flashdata('custom_success', "Internal note successfully added.");
            } else {
                $this->session->set_flashdata('custom_error', "Something went wrong while adding internal note. Please try again later.");
            }
            $this->session->set_flashdata('tab_index', 6);
            redirect('quotes/edit/invoice_id/' . $invoice_id);
        } else {
            $this->session->set_flashdata('custom_error', "Invoice not found.");
        }
        redirect($this->session->userdata('last_index'));
    }

    function getItemsJSON() {

        $invoice_id = $this->input->post('invoice_id');

        $items = $this->mdl_invoices->get_invoice_items($invoice_id);
        $data = array(
            'items' => $items,
            'invoice_amounts' => $this->mdl_invoices->get_invoice_amounts($invoice_id)
        );
        echo json_encode($data);
    }

    function addNewInvoiceItem() {
        $this->load->model("inventory/mdl_inventory_item");
        $invoice_id = $this->input->post('invoice_id');

        $item = json_decode($this->input->post('item'));
        $parent_index = (isset($item->parent_index) && $item->parent_index) ? $item->parent_index : '0';

        if (isset($item->name) && $item->name) {
            $max = $this->mdl_inventory_item->isMaxOrderQty($item->item_name)[0]->max_qty;
       
            
            if (floatval($max) < floatval($item->item_qty) && $max != null) {
                $this->session->set_flashdata('custom_error', "Not enough " . $item->item_name . " item in inventory");
                echo json_encode("Not enough");
                return;
            }
        }

        $item->product_id = (isset($item->product_id) ? $item->product_id : 0);
        $item->item_index = isset($item->item_index) ? $item->item_index : '';
        $item->invoice_id = $invoice_id;

        $new_item = $this->mdl_invoices->add_new_invoice_item($item);

        $housings = $this->mdl_housing->get_item_housing($item->product_id);
        $modal_html = null;
        if ($housings) {
            $modal_html = '<input type="hidden" name="invoice_id" value="' .$item->invoice_id .'">';
            $modal_html .= '<input type="hidden" name="parent_index" value="' .$parent_index .'">';
            foreach ($housings as $housing) {
                $modal_html .= '<label><input type="checkbox" name="product_ids[]" value="'.$housing->product_id.'"> '.$housing->item_name.'</label>';
            }
        }


        //if there is new product then we are going to add it
        //$this->load->model('products/mdl_products');
        //$new_item = $this->mdl_invoices->add_invoice_item($invoice_id, $product_id, $item->item_name, $item->item_description, $item->item_qty, $item->item_length, $item->item_per_meter, $item->item_price, $item_indx);
        $data = array(
            'item' => $new_item,
            'invoice_amounts' => $this->mdl_invoices->get_invoice_amounts($invoice_id),
            'modal_html' => $modal_html,
                //'invoice_item_amounts'    => $this->mdl_invoices->get_invoice_item_amounts($new_item->invoice_item_id),
        );
        echo json_encode($data);
    }

    function get_invoice_amounts() {
        $invoice_id = $this->input->post('invoice_id');
        $data = array(
            'invoice_amounts' => $this->mdl_invoices->get_invoice_amounts($invoice_id)
        );
        echo json_encode($data);
    }

    function updateInvoiceItem() {
         //ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
        $this->load->model("inventory/mdl_inventory_item");
        $invoice_id = $this->input->post('invoice_id');
        $item = json_decode($this->input->post('item'));
        $show_housing = $this->input->post('showHousing');
        //there must be product id to compare, we will not be using item_name, instead
        //each product has a unique product id
        $this->load->model('products/mdl_products');

        $updated_item = $this->mdl_invoices->update_invoice_item($invoice_id, $item);

        $product_id = $updated_item['l_data']->product_id;
        $modal_html = null;
        $parent_index = (isset($item->parent_index) && $item->parent_index) ? $item->parent_index : '0';

        if($show_housing == 'true') {
            $housings = $this->mdl_housing->get_item_housing($product_id);

            if ($housings) {
                $modal_html = '<input type="hidden" name="invoice_id" value="' .$item->invoice_id .'">';
                $modal_html .= '<input type="hidden" name="item_qty" value="' .$item->item_qty .'">';
                $modal_html .= '<input type="hidden" name="parent_index" value="' .$parent_index .'">';
                
                foreach ($housings as $housing) {
                    $modal_html .= '<label><input type="checkbox" name="product_ids[]" value="'.$housing->product_id.'"> '.$housing->item_name.'</label>';
                }
            }
        }

        $data = array(
            'item' => $updated_item,
            'invoice_amounts' => $this->mdl_invoices->get_invoice_amounts($invoice_id),
            'modal_html' => $modal_html,
                //'invoice_item_amounts'    => $this->mdl_invoices->get_invoice_item_amounts($item->invoice_item_id),
        );
        //to update delivery docket amount
        //$this->load->model("delivery_dockets/mdl_delivery_dockets");
        //$this->mdl_delivery_dockets->updatedocket1($data);
        //$this->mdl_delivery_dockets->updateDocketPrice($data);
        echo json_encode($data);
        exit;
    }

    function deleteInvoiceItemAll() {
        $invoice_id = json_decode($this->input->post('invoice_id'));
        $invoice_item_ids = $this->input->post('invoice_item_id');
        if ($invoice_item_ids != '') {
            $invoice_item_ids = explode(',', $invoice_item_ids);
            if (sizeof($invoice_item_ids) > 0) {
                foreach ($invoice_item_ids as $invoice_item_id) {
                    $invoice_item_id = trim($invoice_item_id);
                    if ($invoice_item_id != '') {
                        $this->deleteInvoiceItem($invoice_id, $invoice_item_id);
                    }
                }
            }
        }
        $this->get_invoice_amounts();
    }

    function deleteInvoiceItem($invoice_id, $invoice_item_id) {
        return $this->mdl_invoices->delete_invoice_item($invoice_id, $invoice_item_id);
    }

    function setItemsSortOrder() {

        $invoice_id = $this->input->post('invoice_id');
        $sort_order = $this->input->post('sort_order');
        $this->mdl_invoices->set_invoice_items_sort_order($invoice_id, $sort_order);
        //echo json_encode($this->mdl_invoices->get_invoice_items($invoice_id));
    }

    function generate_pdf() {
        
        $invoice_id = uri_assoc('invoice_id');
        $this->mdl_invoices->save_invoice_history($invoice_id, $this->session->userdata('user_id'), $this->lang->line('generated_invoice_pdf'));
        $this->load->library('lib_output');
        $this->lib_output->pdf($invoice_id, uri_assoc('invoice_template'));
    }

    function generate_html() {
        $invoice_id = uri_assoc('invoice_id');
        $this->load->library('invoices/lib_output');
        $this->mdl_invoices->save_invoice_history($invoice_id, $this->session->userdata('user_id'), $this->lang->line('generated_invoice_html'));
        $this->lib_output->html($invoice_id, uri_assoc('invoice_template'));
    }

    function recalculate() {
        
        $this->load->model('mdl_invoice_amounts');
        $invoice_id = uri_assoc('invoice_id');
        $this->mdl_invoice_amounts->delete_invoice_item_amounts($invoice_id);
        $this->mdl_invoice_amounts->adjust($invoice_id);
        if ($invoice_id) {
            $seg1 = $this->uri->segment(1);
            redirect($seg1 . '/edit/invoice_id/' . $invoice_id);
        } else {
            redirect('settings');
        }
    }

    function copy_invoice($create_new_quote, $redirect = TRUE, $add_item_stock = true) {
        $invoice_id = uri_assoc('invoice_id');
        $invoice_date_entered = format_date(time());

        $new_invoice_id = $this->mdl_invoices->copy_invoice($invoice_id, $invoice_date_entered, $create_new_quote, $redirect);
//        $invoice_detail = $this->common_model->get_row('mcb_invoices', array('invoice_id' => $invoice_id));
//        if( $invoice_detail->invoice_is_quote != '1' ){
//            $this->add_pending_qty($new_invoice_id, 'add-invoice');
//        }
        
        if($new_invoice_id > '0'){
            $this->add_pending_qty($new_invoice_id, 'add-invoice', $add_item_stock);
        }
        
        //update pending quantity
       /* $invoice_items = $this->common_model->get_all_as_object('mcb_invoice_items', array('invoice_id' => $invoice_id));
        $all_inventories = $this->get_invoice_inventories($invoice_items);
        
        foreach ($all_inventories as $inventory){
            $this->mdl_inventory_history->inventory_qty_order_deduct($id, $qty, $notes);
        } */
        
        
        
        if ($new_invoice_id && isset($_GET['create_invoice']) && $_GET['create_invoice'] == '1') {
            redirect('invoices/edit/invoice_id/' . $new_invoice_id);
        }
        if ($redirect) {
            //this is not while coming from convert to order/invoice but coming from copy quote
            if ($new_invoice_id) {
                redirect('quotes/edit/invoice_id/' . $new_invoice_id);
            } else {
                $this->session->set_flashdata('custom_success', 'Something went wrong while copying quote. Please try again later.');
                redirect('quotes/edit/invoice_id/' . $invoice_id);
            }
        }
        return $new_invoice_id;

        /*
         *
          if (!$this->mdl_invoices->validate_copy_invoice()) {

          $this->load->model('mdl_invoice_groups');

          $data = array(
          'invoice_groups'  =>  $this->mdl_invoice_groups->get(),
          'invoice'         =>  $this->mdl_invoices->get_by_id($invoice_id)
          );

          $this->load->view('quote_to_invoice', $data);

          }

          else {

          $this->mdl_invoices->copy_invoice($invoice_id, $this->input->post('invoice_date_entered'), $this->input->post('invoice_group_id'), $create_new_quote);

          redirect('invoices/edit/invoice_id/' . $invoice_id);

          }
         */
    }

    function add_pending_qty($invoice_id, $type='', $add_item_stock = true) {
        
        $invoice_items = $this->common_model->get_all_as_object('mcb_invoice_items', array('invoice_id' => $invoice_id));
        $all_inventories = $this->get_invoice_inventories($invoice_items);
        
        if( $all_inventories != NULL ){
            
            foreach ($all_inventories as $inventorie) {
                $id = $inventorie['inventory_id'];
                $order_qty = $inventorie['order_qty'];
                $invoice_item_id = $inventorie['invoice_item_id'];
                if($add_item_stock) {
                    $data = array(
                        'inventory_id'=>$id,
                        'qty_pending'=>$order_qty,
                        'relevent_item_field'=>'invoice_item_id',
                        'relevent_item_id'=>$invoice_item_id,
                        'qty_update_source'=>'add-quote',
                    );
                    // if( $type =='add-invoice' ){
                    if($type){
                        $data['qty_update_source'] = $type;
                    }
                    $this->common_model->insert('mcb_item_stock', $data);
                }
            }
        }
        return TRUE;
    }
    
    function quote_to_orders_invoice() {
        //error_reporting(E_ALL); ini_set('display_errors', TRUE); ini_set('display_startup_errors', TRUE);
        //checking if any product or line items are not linked to any suppliers
        //in that case we will redirect to product page
        $invoice_id = uri_assoc('invoice_id');
        $redirect_to_product = FALSE;
        $this->load->model('orders/mdl_orders');
        $invoice_items = $this->mdl_orders->get_invoice_itemsbyid($invoice_id);

        $this->load->model(
                array(
                    'clients/mdl_clients',
                    'projects/mdl_projects',
                    'clients/mdl_contacts',
                    'client_groups/mdl_client_groups',
                    'payments/mdl_payments',
                    'orders/mdl_orders',
                    'tax_rates/mdl_tax_rates',
                    'invoice_statuses/mdl_invoice_statuses',
                    'templates/mdl_templates',
                    'users/mdl_users',
                    'delivery_dockets/mdl_delivery_dockets'
                )
        );
        $this->load->helper('text');

        $params = array(
            'where' => array(
                'mcb_invoices.invoice_id' => $invoice_id
            )
        );
        $invoice = $this->mdl_invoices->get($params);
        
        $this->load->model('products/mdl_products');
        if (sizeof($invoice_items) > 0) {
        
            $new_product_count = 0;
            foreach ($invoice_items as $i_item) {

                //if there is new product then we are going to add it
                if ($i_item->item_qty > 0) {
                    //checking duplication

                    $item_name = span_to_mm($i_item->item_name, $i_item->item_length, 1000);
                    $existing = $this->mdl_products->checkDuplicate_product_supplier_code_with_return($item_name);
        

                    //var_dump($item_name,span_to_mm($i_item->item_name, $i_item->item_length, 1000),$existing);
                    // ----create new product if not existing---------- 
                    if ((!$existing)) {

                        $product_array = array(
                            //'name' => $i_item->item_name,
                            'name' => $item_name,
                            'description' => span_to_mm($i_item->item_description, $i_item->item_length, 1000),
                            'base_price' => (float) ($i_item->item_price + $i_item->item_price * $invoice->client_group_discount_percent / 100),
                            'invoice_item_id' => $i_item->invoice_item_id //when we update this product if this exists.. update the product_id
                        );
                        //// price if dynamic
                        if( ($i_item->item_length != '') && ($i_item->item_per_meter != (0)) ){
                            $product_array['base_price']=(float) ($i_item->item_per_meter + $i_item->item_per_meter * $invoice->client_group_discount_percent / 100);
                        }
                        
                        //
                        //                    //$product_id = $this->mdl_products->addNewProductFromQuote($product_array);
                        
                        $product_id = $this->mdl_products->addNewProductFromQuoteToInventory($product_array);

                        if ($product_id) {
                            //also update the quote items
                            $sql = "update mcb_invoice_items set product_id = '" . $product_id . "' where invoice_item_id = '" . $i_item->invoice_item_id . "'";
                            $this->db->query($sql);
                        }
                        $new_product_count++;
                        $i_item->product_id = $product_id;
                    } else {
                        $i_item->product_id = $existing->product_id;
                        $i_item->supplier_id = $existing->supplier_id;
                    }

                    //check if any of the products are not linked to inventory
                    if (($i_item->supplier_id == '0' || $i_item->supplier_id == NULL) && isset($_GET['update_product']) && $_GET['update_product'] == '1') {
                        //echo '<pre>'; print_r($i_item); exit;
                        $redirect_to_product = TRUE;
                        //break;
                    }
                }
            }

            log_message('INFO', 'Created  ' . $new_product_count . ' new products for invoice_id: ' . $invoice_id);
        }


        if ($redirect_to_product) {
            $this->session->set_flashdata('custom_error', 'Please assign supplier and inventory information before proceeding.');
            redirect('inventory');
        }
        
        $this->quote_to_orders();
        $this->copy_invoice(0, FALSE);
        
        redirect('quotes/edit/invoice_id/' . $invoice_id);
    }

    function quote_to_invoice() {

        $this->copy_invoice(0);
    }

    function quote_to_orders() {

        $invoice_id = uri_assoc('invoice_id');
        $this->load->model('orders/mdl_orders');
        $existing_orders = $this->mdl_orders->get_invoice_orders($invoice_id);
        /*
         * Only create orders if none exist for this quote.
         *
         * At this stage user will have to delete all existing orders against
         * a quote if they wish to re-create. I.e it will not try and
         * add/updating existing order items
         */
        if (count($existing_orders) == 0) {
            $this->session->set_flashdata('tab_index', 4);
            //-------update invoice items---------
            $this->update_invoice_items_product_ids($invoice_id);
            // $this->mdl_orders->update_invoice_item_product_ids($invoice_id);            
            //check if all the qty are in stock, we  will not be creating order
            $create_order = FALSE;
//            $invoice_items = $this->common_model->get_all_as_object('mcb_invoice_items', array('invoice_id' => $invoice_id));
            $qry = "SELECT mii.use_length, mi.* "
                    . " FROM mcb_invoice_items mi"
                    . " JOIN mcb_inventory_item mii on mii.`inventory_id` = mi.`product_id`"
                    . " WHERE mi.`invoice_id` = '$invoice_id'";
            $invoice_items = $this->common_model->query_as_object($qry);

            if (sizeof($invoice_items) > 0) {
                //========== get all inventories =========
                $all_inventories = $this->get_invoice_inventories_to_create_order($invoice_items);
                
//                echo '<pre>';
//                print_r($all_inventories);
//                die;
                
                if (sizeof($all_inventories) > 0) {
                    //----check for handstock suppliers
                    $hand_stock_suppliers = array();
                    if( isset($_POST['suplier_ids']) ){
                        if(count($_POST['suplier_ids']) > 0){
                            $hand_stock_suppliers = $_POST['suplier_ids'];
                        }
                    }
                    
                    $insufficient_inventories = array();
                    foreach ($all_inventories as $inventory) {
                        if( $hand_stock_suppliers != NULL ){
                            //----check for handstock
                            if (in_array($inventory['supplier_id'], $hand_stock_suppliers)) {
                                $log_qty = ($inventory['order_qty'] - $inventory['available_qty'])+$inventory['opnorder_minus_pending'];
                                //$inventory['opnorder_minus_pending'] -= $inventory['order_qty'];
                                //$log_qty  =  $inventory['available_qty'] - $inventory['opnorder_minus_pending'];
                                if ($log_qty > 0) {
                                    $inventory['order_qty'] = $log_qty;
                                    
                                    array_push($insufficient_inventories, $inventory);
                                }else{
                                    // this qty is used up and will be reduced when finalizing delivery
                                    $data = array(
                                        'history_id' => '0',
                                        'inventory_id' => $inventory['inventory_id'],
                                        'history_qty' => $inventory['order_qty'],
                                        'notes' => 'Used as hand stock supply via <a style="text-decoration: underline;" href="'.site_url('quotes/edit/invoice_id/'.$invoice_id).'">Quote</a>',
                                        'user_id' => $this->session->userdata('user_id'),
                                        'created_at' => date('Y-m-d H:i:s')
                                    );
                                    // $this->db->insert('mcb_inventory_history',$data); //hand stock log seems confusion, disabled
                                }       
                            }else{
                                array_push($insufficient_inventories, $inventory);
                            }
                        } else {
                            array_push($insufficient_inventories, $inventory);
                        }
                    }
                    $all_inventories = $insufficient_inventories;
                    $create_order = TRUE;
                }
                
                $all_inventories = $this->merge_duplicate_array($all_inventories);
                
//                echo '<pre>';
//                print_r($all_inventories);
//                die;
                
                //------- process to create order-------------
                if ($create_order) {
                    $finres = $this->mdl_orders->create_invoice_orders($invoice_id, $all_inventories, $invoice_items);
                    if (!$finres['status']) {
                        // if orders not created, must need to fix up some products that
                        // don't yet have a supplier (supplier_id = -1)
                        $this->session->set_flashdata('custom_error', 'Quote copied successfully.<br/>' . $finres['message']);
                        redirect('invoices/index');
                    }

                    //report to a table about missing products and inventory missing qty
                    $all_orders = $finres['all_orders'];
                    //$this->mdl_orders->reportMissingProductAndInventoryForOrder($all_orders,$inventory_missing_qty,$products_missing_inventory);
//                    $invoice_items = $this->mdl_orders->get_invoice_itemsbyid($invoice_id);
//                    if (sizeof($invoice_items) > 0) {
//                        foreach ($invoice_items as $i_item) {
//                            $item_qty = $i_item->item_qty;
//                            $linked_invs = $this->mdl_orders->get_related_invs($i_item->product_id);
//                            if (sizeof($linked_invs) > 0) {
//                                foreach ($linked_invs as $li) {
//                                    udpate_open_order_qty($li->inventory_id);
//                                }
//                            }
//                        }
//                    }
                } else {
                    $this->session->set_flashdata('custom_success', 'Order was not created. <br/>');
                    //redirect('quotes/edit/invoice_id/' . $invoice_id);
                }
            }
        }
        $this->session->set_flashdata('custom_success', 'Quote copied successfully. <br/> Quote conversion successful.');
        return $invoice_id;
        //redirect('quotes/edit/invoice_id/' . $invoice_id);
    }
    
    function merge_duplicate_array($array = []) {
        
        if( $array !=NULL ){
            foreach ($array as $key => $value) {
                if( $value['use_length'] != '1' ){
                    $array[$key]['invc_item_price'] = $value['base_price'];
                }
            }
        }
        return $array;
//        echo '<pre>';
//        print_r($array);
//        die;
//        
//        
//        
//        $outputArray = [];
//        foreach ($array as $key => $innerArray) {
//            if($innerArray['use_length'] == 1) {
//                $outputArray[] = $innerArray;
//                continue;
//            }
//            if( !isset($outputArray[$innerArray['inventory_id']]) ){
//                $outputArray[$innerArray['inventory_id']] = $innerArray;
//            }else{   
//                $outputArray[$innerArray['inventory_id']]['order_qty'] += $innerArray['order_qty'];
//            }
//            $outputArray[$innerArray['inventory_id']]['invc_item_price']=$innerArray['base_price'];
//        }
//        if( $outputArray != NULL ){
//            $outputArray = array_values($outputArray);
//        }
//        return $outputArray;
    }
    
    function copy_quote() {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        $this->copy_invoice(1, true, false);

        /*
          if ($this->input->post('btn_cancel')) {

          redirect('invoices');

          }

          if (!$this->mdl_invoices->validate_create()) {


          $this->load->model(array('clients/mdl_clients', 'clients/mdl_contacts'));

          $this->load->helper('text');
          $invoice_id = uri_assoc('invoice_id');

          $data = array(
          'clients'         =>  $this->mdl_clients->get_active(),
          'contacts'            =>  $this->mdl_contacts->get(),
          'invoice'         =>  $this->mdl_invoices->get_by_id($invoice_id)
          );

          $this->load->view('choose_client', $data);

          }

          else {

          $this->load->module('invoices/invoice_api');

          $package = array(
          'client_id'               =>  $this->input->post('client_id'),
          'contact_name'            =>  $this->input->post('contact_name'),
          'project_name'            =>  $this->input->post('project_name'),
          'invoice_date_entered'    =>  $this->input->post('invoice_date_entered'),
          'invoice_group_id'        =>  $this->input->post('invoice_group_id'),
          'invoice_is_quote'        =>  $this->input->post('invoice_is_quote')
          );

          $invoice_id = $this->invoice_api->create_invoice($package);



          redirect('invoices/edit/invoice_id/' . $invoice_id);

          }
         */
    }

    function invoice_to_delivery_docket() {

        $this->session->set_flashdata('tab_index', 4);

        $invoice_id = uri_assoc('invoice_id');

        $this->load->model('delivery_dockets/mdl_delivery_dockets');

        $docket_date_entered = time();
        $docket_id = $this->mdl_delivery_dockets->create_invoice_docket($invoice_id, $docket_date_entered, false);
        if ($docket_id) {
            redirect('delivery_dockets/edit/docket_id/' . $docket_id);
        } else {
            // No new delivery docket created - just view the existing ones
            redirect('invoices/edit/invoice_id/' . $invoice_id);
        }
    }

    function _post_handler() {

        if ($this->input->post('btn_add_payment')) {

            redirect('payments/form/invoice_id/' . uri_assoc('invoice_id'));
        } elseif ($this->input->post('btn_add_contact')) {

            $this->session->set_flashdata('tab_index', 1);

            $invoice = $this->mdl_invoices->get_by_id(uri_assoc('invoice_id'));

            redirect('clients/contacts/details/client_id/' . $invoice->client_id);
        } elseif ($this->input->post('btn_add_invoice')) {

            redirect('invoices/create');
        } elseif ($this->input->post('btn_add_quote')) {

            redirect('quotes/create/quote');
        } elseif ($this->input->post('btn_invoice_search')) {

            redirect('invoice_search/index/is_quote/0');
        } elseif ($this->input->post('btn_quote_search')) {

            redirect('invoice_search/index/is_quote/1');
        } elseif ($this->input->post('btn_cancel')) {

            redirect('invoices/index');
        } elseif ($this->input->post('btn_submit_options_general') or $this->input->post('btn_submit_notes')) {

            if ($this->input->post('btn_submit_options_general')) {

                //$this->session->set_flashdata('tab_index', 1);
            } elseif ($this->input->post('btn_submit_notes')) {

                $this->session->set_flashdata('tab_index', 2);
                $this->load->model('mdl_invoice_tags');
                $tags = $this->input->post('tags');
                $this->mdl_invoice_tags->save_tags(uri_assoc('invoice_id'), $tags);
            }

            $this->mdl_invoices->save_invoice_options($this->mdl_fields->get_object_fields(1));

            $this->recalculate();

            //$this->load->model('mdl_invoice_amounts');
            //$this->mdl_invoice_amounts->adjust(uri_assoc('invoice_id'));

            redirect(uri_seg(1) . '/edit/invoice_id/' . uri_assoc('invoice_id'));
        } elseif ($this->input->post('btn_quote_to_orders')) {
            redirect('invoices/quote_to_orders/invoice_id/' . uri_assoc('invoice_id'));
        } elseif ($this->input->post('btn_quote_to_invoice')) {

            redirect('invoices/quote_to_invoice/invoice_id/' . uri_assoc('invoice_id'));
        } elseif ($this->input->post('btn_quote_to_orders_invoice')) {
            redirect('invoices/quote_to_orders_invoice/invoice_id/' . uri_assoc('invoice_id'));
        } elseif ($this->input->post('btn_copy_quote')) {

            redirect('quotes/copy_quote/invoice_id/' . uri_assoc('invoice_id'));
        } elseif ($this->input->post('btn_invoice_to_delivery_docket')) {

            redirect('invoices/invoice_to_delivery_docket/invoice_id/' . uri_assoc('invoice_id'));
        } elseif ($this->input->post('btn_download_pdf')) {

            $download_url = 'invoices/generate_pdf/invoice_id/' . uri_assoc('invoice_id');

            redirect($download_url);
        } elseif ($this->input->post('btn_send_email')) {

            $email_url = 'mailer/invoice_mailer/form/invoice_id/' . uri_assoc('invoice_id');
            if( isset($_POST['is_quote_invoice']) ){
                $email_url = 'mailer/invoice_mailer/invoice_quote_mail_form/invoice_id/' . uri_assoc('invoice_id').'?type='.$_POST['is_quote_invoice'];
            }
            redirect($email_url);
        }elseif ( $this->input->post('download_quote_items') ) {
            $this->download_quote_items( uri_assoc('invoice_id') );
        }elseif ( $this->input->post('download_invoice_items') ) {
            $this->download_invoice_items( uri_assoc('invoice_id') );
        }
    }
    
    function estimate_pending_qty(){
        
        
        
//       $pending_data = $this->db->query("SELECT pri.inventory_id,SUM(qi.item_qty)-SUM(di.docket_item_qty) AS qty_pending FROM mcb_invoice_items AS qi INNER JOIN mcb_products_inventory AS pri
//                 ON qi.product_id = pri.product_id INNER JOIN mcb_delivery_docket_items AS di ON 
//                 di.invoice_item_id = qi.invoice_item_id GROUP BY pri.inventory_id
//         "); 
//       $this->db->query("CREATE TABLE IF NOT EXISTS `mcb_item_stock` (
//          `stock_id` int(11) NOT NULL AUTO_INCREMENT,
//          `inventory_id` int(11) DEFAULT NULL,
//          `qty_update_source` varchar(255) DEFAULT NULL,
//          `relevent_item_field` varchar(255) DEFAULT NULL,
//          `relevent_item_id` int(11) DEFAULT 0,
//          `qty_pending` int(11) DEFAULT 0,
//          PRIMARY KEY (`stock_id`)
//            )
//            ");
//       foreach($pending_data->result() AS $row){
//           $find_row = $this->db->query("SELECT * FROM mcb_item_stock WHERE inventory_id = $row->inventory_id");
//        
//           if($find_row->num_rows()==0){
//               $this->db->query("INSERT INTO mcb_item_stock(inventory_id,qty_pending) VALUES ($row->inventory_id,$row->qty_pending)");
//               
//           }else{
//               $this->db->query("DELETE FROM mcb_item_stock WHERE inventory_id=$row->inventory_id");
//               $this->db->query("INSERT INTO mcb_item_stock(inventory_id,qty_pending) VALUES ($row->inventory_id,$row->qty_pending)");
//           }
//          
//       }
//        
//        
//       /* $this->db->query("INSERT INTO mcb_item_stock(inventory_id,qty_pending)
//                 SELECT pri.inventory_id,SUM(qi.item_qty)-SUM(di.docket_item_qty) AS qty_pending FROM mcb_invoice_items AS qi INNER JOIN mcb_products_inventory AS pri
//                 ON qi.product_id = pri.product_id INNER JOIN mcb_delivery_docket_items AS di ON 
//                 di.invoice_item_id = qi.invoice_item_id GROUP BY pri.inventory_id
//                ON DUPLICATE KEY UPDATE
//                `pri.inventory_id` = VALUES(pri.inventory_id);
//                 "); */
//                    
//        $this->load->view('pending_items',array('success'=>'Pending qty updated'));
    }
    function get_invoices_only_JSON($params = NULL) {

        $is_quote = $this->input->post('is_quote');
        $limit = $this->input->post('limit');
        $offset = $this->input->post('offset');
        
        $data = array(
            'invoices' => $this->mdl_invoices->get_no_joins($is_quote, $limit, $offset)
        );
        
        echo json_encode($data);
    }

    function get_invoices_only_JSON_withDocket($params = NULL) {

        $is_quote = $this->input->post('is_quote');
        $limit = $this->input->post('limit');
        $offset = $this->input->post('offset');


        $data = array(
            'invoices' => $this->mdl_invoices->get_no_joins_withdocket($is_quote, $limit, $offset)
        );

        echo json_encode($data);
    }

    function get_invoices_JSON_invoice($params = NULL) {
        $is_quote = $this->input->post('is_quote');
        $limit = $this->input->post('limit');
        $offset = $this->input->post('offset');

        $client_params['select'] = "mcb_clients.client_id id, mcb_clients.client_name n, mcb_clients.client_active a";
        $contact_params['select'] = "contact_id id, contact_name n, contact_active a";
        $project_params['select'] = "project_id id, project_name n, project_specifier s, project_active a";


        $this->load->model(
                array(
                    'clients/mdl_clients',
                    'projects/mdl_projects',
                    'clients/mdl_contacts',
                    'client_groups/mdl_client_groups',
                    'tax_rates/mdl_tax_rates',
                    'invoice_statuses/mdl_invoice_statuses',
                    'users/mdl_users'
                )
        );


        $data = array(
            'clients' => $this->mdl_clients->get($client_params),
            'projects' => $this->mdl_projects->get($project_params),
            'contacts' => $this->mdl_contacts->get($contact_params),
            //'tax_rates'           =>  $this->mdl_tax_rates->get(),
            //'client_groups'       =>  $this->mdl_client_groups->get(),
            'invoice_statuses' => $this->mdl_invoice_statuses->get(),
            //'users'             =>  $this->mdl_users->get(),
            'invoices' => $this->mdl_invoices->get_no_joins_invoice($is_quote, $limit, $offset)
        );


        echo json_encode($data);
    }

    function get_invoices_JSON_withDocket() {
        $is_quote = $this->input->post('is_quote');
        $limit = $this->input->post('limit');
        $offset = $this->input->post('offset');

        $client_params['select'] = "mcb_clients.client_id id, mcb_clients.client_name n, mcb_clients.client_active a";
        $contact_params['select'] = "contact_id id, contact_name n, contact_active a";
        $project_params['select'] = "project_id id, project_name n, project_specifier s, project_active a";


        $this->load->model(
                array(
                    'clients/mdl_clients',
                    'projects/mdl_projects',
                    'clients/mdl_contacts',
                    'client_groups/mdl_client_groups',
                    'tax_rates/mdl_tax_rates',
                    'invoice_statuses/mdl_invoice_statuses',
                    'users/mdl_users'
                )
        );


        $data = array(
            'clients' => $this->mdl_clients->get($client_params),
            'projects' => $this->mdl_projects->get($project_params),
            'contacts' => $this->mdl_contacts->get($contact_params),
            //'tax_rates'           =>  $this->mdl_tax_rates->get(),
            //'client_groups'       =>  $this->mdl_client_groups->get(),
            'invoice_statuses' => $this->mdl_invoice_statuses->get(),
            //'users'             =>  $this->mdl_users->get(),
            'invoices' => $this->mdl_invoices->get_no_joins_withdocket($is_quote, $limit, $offset)
        );
        echo json_encode($data);
    }

    function getInvoiceDockets() {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        $invoice_id = uri_assoc('invoice_id');
        $invoicenum = $this->input->post('invoicenum');

        $this->load->model('delivery_dockets/mdl_delivery_dockets');
        // $dockets = $this->mdl_delivery_dockets->get_invoice_dockets($invoice_id);
        $dockets = $this->mdl_delivery_dockets->get_delvery_dockets_by_invoice_id($invoice_id);

//        $delDock =$this->mdl_delivery_dockets->statusck($invoice_id);
//        
//         echo'<pre>';
//            var_dump($dockets);die();

        if (sizeof($dockets) > 0) {
            $popHtml = '<div class="inv-detail">';
            $popHtml .= '<h2>Invoice #: ' . $invoicenum . '</h2><br/>';

            $popHtml .= '<h3>Delivery Dockets:</h3>';
            $popHtml .= '<table class="table table-bordered order-prod-list">';

            $popHtml .= '<thead>';
            $popHtml .= '<tr>';
            $popHtml .= '<td>S.N</td>';
            $popHtml .= '<td>Docket Number</td>';
            $popHtml .= '<td>Status</td>';
            $popHtml .= '<td>Delivery Status</td>';
            $popHtml .= '<td>Actions</td>';
            $popHtml .= '</tr>';
            $popHtml .= '</thead>';
            $popHtml .= '<tbody>';
            $i = 1;
            foreach ($dockets as $inv) {

                $status = ($inv->invoice_sent == '1') ? 'Sent' : 'Not Sent';

                $popHtml .= '<tr>';
                $popHtml .= '<td>' . $i . '</td>';
                $popHtml .= '<td>' . $inv->docket_number . '</td>';
                $popHtml .= '<td>' . $status . '</td>';

                $popHtml .= '<td scope="col">';
                if (($inv->docket_delivery_status) == '0') {
                    $popHtml .= 'Undelivered';
                } else if (($inv->docket_delivery_status) == '1') {
                    $popHtml .= 'Delivered';
                }
                $popHtml .= '</td>';

                $popHtml .= '<td class="last">
                    
                <a href="' . site_url() . '/delivery_dockets/generatedocketinvoice/docket_id/' . $inv->docket_id . '">Generate Invoice</a>
                
                <a href="' . site_url() . '/delivery_dockets/senddocketinvoice/docket_id/' . $inv->docket_id . '">Send invoice</a><br>';

                if (($inv->docket_delivery_status) == 0 || $inv->docket_delivery_status == NULL) {

                    $popHtml .= '<a href="' . site_url() . '/delivery_dockets/finalizedelivery/docket_id/' . $inv->docket_id . '">Finalise Delivery</a>';
                } else {
                    $popHtml .= '<a href="' . site_url() . '/delivery_dockets/canceldelivery/docket_id/' . $inv->docket_id . '">Cancel Delivery</a>';
                }

                $popHtml . '<a href="' . site_url() . '/delivery_dockets/edit/docket_id/' . $inv->docket_id . '" title="Edit">
                    <img src="' . base_url() . '/assets/style/img/icons/edit.png" alt="">                </a>
                <a href="' . site_url() . '/delivery_dockets/generate_pdf/docket_id/' . $inv->docket_id . '/docket_template/pick_list">
                    <img src="' . base_url() . '/assets/style/img/icons/picking.png" alt="">                </a>
                <a href="' . site_url() . '/delivery_dockets/generate_pdf/docket_id/' . $inv->docket_id . '">
                <img src="' . base_url() . '/assets/style/img/icons/pdf.png" alt="">                </a>

                                        <a href="' . site_url() . '/delivery_dockets/delete/docket_id/' . $inv->docket_id . '" title="Delete" onclick="javascript:if (!confirm(\'Are you sure you want to delete this record?\'))
                                return false">
        <img src="' . base_url() . '/assets/style/img/icons/delete.png" alt="">                    </a>
    
            </td>';
                $popHtml .= '</tr>';
                $i++;
            }

            $popHtml .= '</tbody></table></div>';
        } else {
            echo 'There are no delivery dockets for this invoice.';
            exit;
        }
        echo $popHtml;
        exit;
    }

    function get_invoices_JSON($params = NULL) {

        $is_quote = $this->input->post('is_quote');
        $limit = $this->input->post('limit');
        $offset = $this->input->post('offset');

        $client_params['select'] = "mcb_clients.client_id id, mcb_clients.client_name n, mcb_clients.client_active a";
        $contact_params['select'] = "contact_id id, contact_name n, contact_active a";
        $project_params['select'] = "project_id id, project_name n, project_specifier s, project_active a";


        $this->load->model(
                array(
                    'clients/mdl_clients',
                    'projects/mdl_projects',
                    'clients/mdl_contacts',
                    'client_groups/mdl_client_groups',
                    'tax_rates/mdl_tax_rates',
                    'invoice_statuses/mdl_invoice_statuses',
                    'users/mdl_users'
                )
        );


        $data = array(
            'clients' => $this->mdl_clients->get($client_params),
            'projects' => $this->mdl_projects->get($project_params),
            'contacts' => $this->mdl_contacts->get($contact_params),
            //'tax_rates'           =>  $this->mdl_tax_rates->get(),
            //'client_groups'       =>  $this->mdl_client_groups->get(),
            'invoice_statuses' => $this->mdl_invoice_statuses->get(),
            //'users'             =>  $this->mdl_users->get(),
            'invoices' => $this->mdl_invoices->get_no_joins($is_quote, $limit, $offset)
        );
        echo json_encode($data);
    }

    function get_invoices_by_product_JSON() {

        $is_quote = $this->input->post('is_quote');
        $item_name = $this->input->post('item_name');
        $item_desc = $this->input->post('item_description');

        $data = array(
            'invoices' => $this->mdl_invoices->search_by_product($is_quote, $item_name, $item_desc)
        );
        echo json_encode($data);
    }
    public function getMinimumPossibleProduct() {
        $this->load->model('orders/mdl_orders');
        $min = $this->mdl_orders->getMinimumPossibleProduct('69875');
      
    }
    
    function download_all_csv(){
        
        $clients = $this->common_model->query_as_array('SELECT client_id id,  client_name n, client_state cs, client_active a FROM mcb_clients ORDER BY client_name ASC');
        //$contact = $this->common_model->query_as_array('SELECT contact_id id, contact_name n, contact_active a FROM mcb_contacts ORDER BY contact_name ASC');
        $project = $this->common_model->query_as_array('SELECT project_id id, project_name pn, project_specifier ps, project_active a FROM mcb_projects ORDER BY project_name ASC');
        $invoice_statuses = $this->common_model->query_as_array('SELECT * FROM mcb_invoice_statuses');
        $data = $this->quote_invoice_index_invoice(20000)['invoices'];
        $res_obj_arr = [];
        foreach ($data as $va) {   
            /////////////status//////////////
            $status_id_key = array_search($va->s,  array_column($invoice_statuses, 'invoice_status_id') );
            $va->s = $invoice_statuses[$status_id_key]['invoice_status'];
            
            /////////////clients and state//////////////
            $client_id_key = array_search($va->c,  array_column($clients, 'id') );
            $va->c = $clients[$client_id_key]['n'];
            $va->c_state = $clients[$client_id_key]['cs'];
            if( ($va->c == '') || ($va->c == NULL) ){
                $va->c = '(deleted)';
            }
            
            /////////////projects and specifier//////////////
            $project_id_key = array_search($va->p,  array_column($project, 'id') );
            $va->p = $project[$project_id_key]['pn'];
            $va->ps = $project[$project_id_key]['ps'];    
            $res_obj_arr[] = $va;
        }
        $this->mdl_invoices->download_all_csv($res_obj_arr);
    }
    
    function download_all_csv_by_custom(){
        
        //ini_set('display_errors',1); error_reporting(E_ALL);
                
        
//        echo '<pre>';
//        print_r($_POST);
//        die;
        
        $invoice_statuses = $this->common_model->query_as_array('SELECT * FROM mcb_invoice_statuses');        
        $state_where = "";
        $sort_order_ = "i.invoice_id";
        $sort_by_ = "DESC";
//        if($_POST['sort_field'] == 'c_state'){
//            $sort_order_ = "c_tbl.client_state";
//        }elseif($_POST['sort_field'] == 'cn'){
//            $sort_order_ = "c_tbl.client_name";
//        }elseif($_POST['sort_field'] == 'pn'){
//            $sort_order_ = "p_tbl.project_name";
//        }elseif($_POST['sort_field'] == 'e_date'){
//            $sort_order_ = "i.invoice_date_entered";
//        }elseif($_POST['sort_field'] == 'o_amount'){
//            $sort_order_ = "t.invoice_total";
//        }else{
//            $sort_order_ = "i.invoice_id";
//        }
        
        if($_POST['sort_field'] == ""){
            $sort_order_ = "i.invoice_id";
        }elseif($_POST['sort_field'] == "o_amount"){
            $sort_order_ = "i.invoice_id";
        }else{
            $sort_order_ = $_POST['sort_field'];
        }
        
        
        if( $_POST['sort_by'] == 'SORT_ASC' ){
            $sort_by_ = "ASC";
        }
        
        if( isset($_POST['selected_state']) ){
            if( count($_POST['selected_state']) > 0 ){
                $sq_states = "";
                foreach ($_POST['selected_state'] as $s_state) {
                    $sq_states .= "'{$s_state}', ";
                }
                $sq_states = rtrim(trim($sq_states), ',');
                if($sq_states != ""){
                    $state_where = "AND c_tbl.client_state IN ({$sq_states})";
                }
            }
        }
        
        $invoice_query = "SELECT SQL_CALC_FOUND_ROWS 
                            i.invoice_id id, 
                            i.client_id c, 
                            c_tbl.client_name, 
                            c_tbl.client_state, 
                            i.project_id p, 
                            p_tbl.project_name, 
                            p_tbl.project_specifier, 
                            i.contact_id ct, 
                            i.user_id u, 
                            i.invoice_number n, IF(i.invoice_status_id = 2, 
                            IF(i.invoice_due_date < UNIX_TIMESTAMP(), 4, i.invoice_status_id), i.invoice_status_id) s, 
                            i.invoice_tax_rate_id t, 
                            CONCAT('', FORMAT(t.invoice_total, 2)) a, 
                            i.invoice_client_group_id g, 
                            i.invoice_date_entered e, 
                            i.invoice_due_date d, 
                            i.invoice_is_quote q, 
                            i.invoice_quote_id qi, 
                            i.smart_status mc, 
                            q.invoice_number qn, 
                            (select count(docket_id) from mcb_delivery_dockets where mcb_delivery_dockets.invoice_id = i.invoice_id) as docket_count, 
                            i.invoice_client_order_number po 
                            FROM (mcb_invoices AS i) 
                            JOIN mcb_invoice_amounts AS t ON t.invoice_id = i.invoice_id 
                            LEFT JOIN mcb_invoices AS q ON q.invoice_id = i.invoice_quote_id 
                            LEFT JOIN mcb_clients AS c_tbl ON c_tbl.client_id = i.client_id 
                            LEFT JOIN mcb_projects AS p_tbl ON p_tbl.project_id = i.project_id 
                            WHERE `i`.`invoice_is_quote` = '0' {$state_where}
                            ORDER BY {$sort_order_} {$sort_by_}";
                            
        $invoices = $this->common_model->query_as_array($invoice_query);
        $res_obj_arr = [];
        foreach ((array)$invoices as $va) { 
            if( isset($va['id']) ){
                $va = (array)$va;
                if( ($va['client_name'] == '') || ($va['client_name'] == NULL) ){
                    $va['client_name'] = '(deleted)';
                }
                /////////////status//////////////
                $va['s'] = $this->mdl_invoices->getInvoiceStatusId($va['id']);    
                $status_id_key = array_search($va['s'],  array_column($invoice_statuses, 'invoice_status_id') );
                $va['s'] = $invoice_statuses[$status_id_key]['invoice_status'];
                
                $va['a_tocomp'] = str_replace(",", "", $va['a']);
                
                /// internal notes
                $va['inot'] = '';
                $i_note = $this->mdl_invoices->getQuoteInternalFromInvoiceId($va['id']);
                if( isset($i_note['internal_count']) && ( $i_note['internal_count'] >'0' ) ){
                    if( $i_note['internal_data'] != NULL ){   
                        $i_note['internal_data'] = array_reverse($i_note['internal_data']);
                        foreach ($i_note['internal_data'] as $k=> $note) {
                            if(trim($note->note) != '' ){
                                $note->created_date = strtotime($note->created_date); 
                                $note->created_date = date('Y-m-d', $note->created_date);
                                $note->note = trim(str_replace(',', '', $note->note));
                                $va['inot'] .= "by {$note->username} on {$note->created_date}:  {$note->note}, ";
                            }
                        }
                        $va['inot'] = rtrim($va['inot'], ', ');
                    }
                }
                $res_obj_arr[] = $va;
            }
        }
        
        if($_POST['sort_field'] == "o_amount"){
            $price = array_column($res_obj_arr, 'a_tocomp');
            if( $_POST['sort_by'] == 'SORT_ASC' ){
                array_multisort($price, SORT_ASC, $res_obj_arr);
            }else{
                array_multisort($price, SORT_DESC, $res_obj_arr);
            }
        }
        $this->mdl_invoices->download_all_csv_by_custom($res_obj_arr);
    }
    
    function add_docket_amounts() {
        
        if( isset($_POST['paid_ids']) ){
            
            
//            echo '<pre>';
//            print_r($_POST['paid_amounts']);
//            die;
            
            $note = $_POST['note'];
            $all_ids = $_POST['all_ids'];
            $paid_ids = $_POST['paid_ids'];
            $paid_amounts = $_POST['paid_amounts'];
            $username = $this->common_model->get_row('mcb_users',array('user_id'=>$this->session->userdata('user_id')))->username;
            $invoice_id = $_POST['invoice_id'];;
            if(count($paid_ids) > 0 ){
                $this->load->model('delivery_dockets/mdl_delivery_dockets');
                foreach ($all_ids as $key => $id) {    
                    if(in_array($id, $paid_ids) ){
                        $amount = $paid_amounts[$key];
                        $amount = trim(str_replace(array(",","$"),"",$amount));
                        $data = array(
                            'docket_id' => $id,
                            'note' => $note,
                            'amount_entered' => $amount,
                            'amount_log' => $amount,
                            'time' => time(),
                            'user_name'=> $username
                        );
                        
                        if($amount != 0){
                            $this->mdl_delivery_dockets->add_docket_amount($data);
                        }
                    }
                }       
            }
        }
        $this->session->set_flashdata('tab_index', 7);
        redirect(site_url('invoices/edit/invoice_id/'.$invoice_id));
    }
    
    function add_docket_amount() {
        
        $invoice_id = $this->input->post('invoice_id');
        $docket_id = $this->input->post('docket_id');
        $amount = $this->input->post('amount');
        $note = $this->input->post('note');
        $user_name = $this->input->post('user_name');
        $username = $this->common_model->get_row('mcb_users',array('user_id'=>$this->session->userdata('user_id')))->username;
        $data = array(
            'docket_id' => $docket_id,
            'note' => $note,
            'amount_entered' => $amount,
            'amount_log' => $amount,
            'time' => time(),
            'user_name'=> $username
        );
        
        if( ($amount == '0') || ($amount == '0.0') || ($amount == '0.00') || ($amount == '') || ($amount == NULL) ){
            $this->session->set_flashdata('custom_error', 'Please set valid amount.');
        }else if( ($docket_id <= '0') || ($docket_id == NULL) || ($docket_id == '') ){
            $this->session->set_flashdata('custom_error', 'docket id is missing.');
        }else{
            $this->load->model('delivery_dockets/mdl_delivery_dockets');
            $this->mdl_delivery_dockets->add_docket_amount($data);
        }
        $this->session->set_flashdata('tab_index', 7);
        redirect(site_url('invoices/edit/invoice_id/'.$invoice_id));
    }
    
    function ml_tst($mail_id= NULL){
        error_reporting(E_ALL); 
        ini_set('display_errors', 1);
        // the message
        $msg = "litesource test success";
        // use wordwrap() if lines are longer than 70 characters
        $msg = wordwrap($msg,70);
        if($mail_id != NULL){
            $mail_i = $mail_id;
        }else{
            $mail_i = "roka.manoj12@gmail.com";
        }
        // send email
        var_dump(mail($mail_i,"My subject",$msg));
    }
    
    function download_quote_items( $id ) {
        $items = $this->mdl_invoices->get_quote_invoice_items_for_download( $id );
        $data = array(
            'items' => $items,
            'invoice_amounts' => $this->mdl_invoices->get_invoice_amounts_to_download( $id )
        );
        $this->mdl_invoices->download_quote_invoice_items( $id, ucfirst('quote'), $data );
        exit();
    }
    
    function download_invoice_items( $id ) {
        $items = $this->mdl_invoices->get_quote_invoice_items_for_download( $id );
        $data = array(
            'items' => $items,
            'invoice_amounts' => $this->mdl_invoices->get_invoice_amounts_to_download( $id )
        );
        $this->mdl_invoices->download_quote_invoice_items( $id, ucfirst( 'invoice' ), $data );
        exit();
    }
    
    function export_selected_items() {
        
        $delimiter = ',';
        $enclosure = '"';
        header("Content-Transfer-Encoding: UTF-8");
        header('Content-type: text/csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        $file = fopen('php://output', 'w');
        
        $heading = array(
            'Qty',
            'Cat. #',
            'Type',
            'Description',
            'Length',
            'Per Meter',
            'Unit Price',
            'Subtotal'
        );
        fputcsv($file, $heading, $delimiter, $enclosure);
        
        if( isset($_POST['items_to_csv_export']) ){
            $items = $_POST['items_to_csv_export'];
            foreach ($items as $item) {
                $item = (object)$item;
                if( ($item->item_qty == '0.00') && ($item->item_name == '') ){
                    $item->item_qty = "";
                }
                if( $item->item_per_meter == '0.00' ){
                    $item->item_per_meter = "";
                }
                if( $item->item_price == '0.00' ){
                    $item->item_price = "";
                }else{
                    $item->item_price = display_currency( $item->item_price );
                }
                if( $item->item_subtotal == '0.00' ){
                    $item->item_subtotal = "";
                } else {
                    $item->item_subtotal = display_currency( $item->item_subtotal );
                }
                if(($item->item_type == '') || ($item->item_type == NULL)|| ($item->item_type == 'null') ){
                    $item->item_type = "";
                }
                $item->item_name = str_replace(array( '<span>', '</span>', ), ' ', $item->item_name);
                $item->item_description = str_replace(array( '<span>', '</span>', ), ' ', $item->item_description);
                
                $line = array(
                    ($item->item_qty),
                    ($item->item_name),
                    ($item->item_type),
                    ($item->item_description),
                    ($item->item_length),
                    ($item->item_per_meter),
                    ($item->item_price),
                    ($item->item_subtotal)
                );
                fputcsv($file, $line, $delimiter, $enclosure);                
            }
        }
        
        
//        if( isset($_POST['js_internal_notes_arr']) && ($_POST['js_internal_notes_arr'] != NULL) ){
//            
//            //// gap of two lines////////
//            for ($x = 0; $x <= 4; $x++) {
//                $line = array(
//                    (''),
//                    (''),
//                    (''),
//                    (''),
//                    (''),
//                    (''),
//                    (''),
//                    (''),
//                );
//                if( $x == '3' ){
//                    $line = array(
//                        ('--'),
//                        ('Internal Notes'),
//                        ('--'),
//                        ('--'),
//                        ('--'),
//                        (''),
//                        (''),
//                        (''),
//                    );
//                }
//                fputcsv($file, $line, $delimiter, $enclosure);
//            }
//            
//            if( $_POST['js_internal_notes_arr'] != NULL ){
//                foreach ($_POST['js_internal_notes_arr'] as $key => $note) {
//                    $line = array(
//                        ($note['sn']),
//                        ($note['note']),
//                        ($note['user']),
//                        ($note['date']),
//                        (''),
//                        (''),
//                        (''),
//                        (''),
//                    );
//                    fputcsv($file, $line, $delimiter, $enclosure);
//                }   
//            }    
//        }
        
        fclose($file);
        echo json_encode($file);
        exit;
    }

    function get_invoice_status() {
        return $this->common_model->query_as_object('SELECT * FROM mcb_invoice_statuses');
    }

    function get_clients(){
        $clients = $this->common_model->query_as_object('SELECT client_id id,  client_name n, client_state cs, client_active a FROM mcb_clients ORDER BY client_name ASC');
        if( $clients != NULL ){
            foreach ($clients as $key => $c) {
                $clients[$key]->n = trim(trim($c->n, "'"));
            }
        }
        return $clients;
    }
    
    function test_function(){


        echo "Today's date is :"; 
        $today = date("d/m/Y"); 
        echo $today; 
        echo '<br>';
        echo "The time is " . date("h:i:sa");
        exit;
    }
} ?>
