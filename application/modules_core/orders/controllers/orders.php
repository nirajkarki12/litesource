<?php

(defined('BASEPATH')) OR exit('No direct script access allowed');

class Orders extends Admin_Controller {

    function __construct() {

        parent::__construct();

        $this->load->model('mdl_orders');
    }

    function index() {

        $this->load->helper('text');
        $this->_post_handler();
        $this->redir->set_last_index();
        $params = array(
            'paginate' => TRUE,
            'limit' => $this->mdl_mcb_data->setting('results_per_page'),
            'page' => uri_assoc('page')
        );
        $order_by = uri_assoc('order_by');
        /*
         * Always include order_date_entered and order_number in the sort  
         */
        $default_order_by = 'FROM_UNIXTIME(order_date_entered) DESC, order_number DESC';
        switch ($order_by) {
            case 'invoice':
                $params['order_by'] = 'invoice_number DESC, ';
                break;
            case 'project':
                $params['order_by'] = 'project_name, ';
                break;
            case 'supplier':
                $params['order_by'] = 'client_name, ';
                break;
            case 'status':
                $params['order_by'] = 'order_status_id, ';
                break;
            case 'order_number':
                $params['order_by'] = 'order_number DESC, ';
                break;
            default:
                $params['order_by'] = '';
        }

        $params['order_by'] .= $default_order_by;

        $data = array(
            'orders' => $this->mdl_orders->get($params),
            'sort_links' => TRUE,
            'order_by' => $params['order_by']
        );

        $this->load->view('index', $data);
    }

    function edit() {
        
//        error_reporting(E_ALL); ini_set('display_errors', 1);
        $tab_index = ($this->session->flashdata('tab_index')) ? $this->session->flashdata('tab_index') : 0;
        $this->_post_handler();
        $this->redir->set_last_index();
        $this->load->helper('form');
        $order_id = uri_assoc('order_id');
        $this->load->model(
                array(
                    'clients/mdl_clients',
                    'clients/mdl_contacts',
                    'addresses/mdl_addresses',
                    'projects/mdl_projects',
                    //'products/mdl_products',
                    //'tax_rates/mdl_tax_rates',
                    //'invoices/mdl_invoices_simple',
                    'invoice_statuses/mdl_invoice_statuses'
                //'users/mdl_users'
                )
        );
        if (!$_POST AND $order_id) {
            $params = array(
                'where' => array(
                    'mcb_orders.order_id' => $order_id
                ),
                //'debug' => TRUE
            );
            $order = $this->mdl_orders->get($params);
            //echo '<pre>'; print_r($order); exit;
            //$this->mdl_addresses->prep_validation($order->order_address_id);
        }
        if (!isset($order)) {
            redirect('dashboard/record_not_found');
        }
        $this->load->helper('text');
        
        $data = array(
            'order' => $order,
            'address' => $this->mdl_addresses->get_by_id($order->order_address_id),
            'contacts' => $this->mdl_contacts->get_client_contacts($order->supplier_id),
            // 'order_items' => $this->mdl_orders->get_order_items($order_id),
            'order_items' => $this->mdl_orders->get_order_items_list($order_id),
            'order_statuses' => $this->mdl_invoice_statuses->get(),
            'projects' => $this->mdl_projects->get_active(),
            'tab_index' => $tab_index,
            'history' => $this->mdl_orders->get_order_history($order_id),
            'order_products_missing_inv' => $this->mdl_orders->order_products_missing_inv($order_id),
            'is_mixed_inv_length' => $this->mdl_orders->getOrderMixedInventoryLengthStatus($order_id),
            //'order_inventory_null' => $this->mdl_orders->order_inventory_null($order_id),
        );
//        echo "<pre>";
//        print_r($data);
//        die();
        
        $this->load->view('order_edit', $data);
    }

    function create() {

        if ($this->input->post('btn_cancel')) {

            redirect('orders');
        }

        if (!$this->mdl_orders->validate_create()) {

            $this->load->model('clients/mdl_clients');
            $this->load->model('projects/mdl_projects');

            $data = array(
                'suppliers' => $this->mdl_clients->get_active_suppliers(),
                'projects' => $this->mdl_projects->get_active(),
            );

            $this->load->view('choose_supplier', $data);
        } else {
            $invoice_id = 0;
            $supplier = $this->common_model->query_as_row('SELECT client_id, client_name, client_tax_rate_id FROM mcb_clients WHERE client_id = "'.$this->input->post('supplier_id').'"');
            $order_id = $this->mdl_orders->create_supplier_order($supplier, $this->input->post('contact_name'), $invoice_id, $this->input->post('project_id'), $this->input->post('order_date_entered'), TRUE,'1', TRUE);
            redirect('orders/edit/order_id/' . $order_id);
        }
    }

    function delete() {
        $order_id = uri_assoc('order_id');
        if ($order_id) {
            $this->mdl_orders->delete($order_id);
        }
        redirect($this->session->userdata('last_index'));
    }

    function get($params = NULL) {
        return $this->mdl_orders->get($params);
    }

    function get_orders_JSON($params = NULL) {

        $limit = $this->input->post('limit');
        $offset = $this->input->post('offset');

        $client_params['select'] = "mcb_clients.client_id id, mcb_clients.client_name n, mcb_clients.client_tax_rate_id t, mcb_clients.client_active a";
        //$client_params['debug'] = 1;

        $project_params['select'] = "project_id id, project_name n, project_active a";
        
        $this->load->model(
                array(
                    'clients/mdl_clients',
                    'projects/mdl_projects',
                    //'tax_rates/mdl_tax_rates',
                    //'currencies/mdl_currencies',
                    'invoice_statuses/mdl_invoice_statuses',
                )
        );
        $data = array(
            'suppliers' => $this->mdl_clients->get($client_params),
            //'suppliers'	        => $this->mdl_clients->get_active_suppliers(),
            'projects' => $this->mdl_projects->get($project_params),
            //'tax_rates'		=> $this->mdl_tax_rates->get(),
            //'currencies'		=> $this->mdl_currencies->get(),
            'order_statuses' => $this->mdl_invoice_statuses->get(),
            'orders' => $this->mdl_orders->get_raw($limit, $offset)
        );
        echo json_encode($data);
    }
    
    function generate_pdf() {

        $order_id = uri_assoc('order_id');

        $this->load->library('lib_output');
        //record in history
        $data = array(
            'user_id' => $this->session->userdata('user_id'),
            'order_id' => $order_id,
            'created_date' => date('Y-m-d H:i:s'),
            'order_history_data' => 'Order Pdf viewed.'
        );
        $this->db->insert('mcb_order_history', $data);
        
        $this->lib_output->pdf($order_id, uri_assoc('order_template'));
    }

    function generate_html() {

        $order_id = uri_assoc('order_id');

        $this->load->library('lib_output');

        //$this->mdl_invoices->save_invoice_history($invoice_id, $this->session->userdata('user_id'), $this->lang->line('generated_invoice_html'));

        $this->lib_output->html($order_id, uri_assoc('order_template'));
    }

    function _post_handler() {

        if ($this->input->post('btn_add_order')) {

            redirect('orders/create');
        } elseif ($this->input->post('btn_cancel')) {

            redirect($this->session->userdata('last_index'));
        } elseif ($this->input->post('btn_submit_options_general')) {

            $this->session->set_flashdata('tab_index', 0);

            $this->mdl_orders->save();

            redirect('orders/edit/order_id/' . uri_assoc('order_id'));
        } elseif ($this->input->post('btn_add_order_item')) {

            $this->session->set_flashdata('tab_index', 1);

            redirect('orders/order_items/form/order_id/' . uri_assoc('order_id'));
        } elseif ($this->input->post('btn_delivery_address')) {

            $this->session->set_flashdata('tab_index', 2);

            redirect('addresses/form/order_id/' . uri_assoc('order_id'));
        } elseif ($this->input->post('btn_stock_in')) {

            $this->session->set_flashdata('tab_index', 3);

            redirect('orders/order_items/showstock/order_id/' . uri_assoc('order_id'));
        } elseif ($this->input->post('btn_stock_out')) {

            $this->session->set_flashdata('tab_index', 4);

            redirect('orders/order_items/showstockout/order_id/' . uri_assoc('order_id'));
        } elseif ($this->input->post('btn_download_pdf')) {


            $download_url = 'orders/generate_pdf/order_id/' . uri_assoc('order_id');

            redirect($download_url);
        } elseif ($this->input->post('btn_send_email')) {

            $email_url = 'mailer/order_mailer/form/order_id/' . uri_assoc('order_id');

            redirect($email_url);
        }
    }
    
    /**
     * updating order items directly from home
     */
    public function updateOrderItem(){
        $order_id = $this->input->post('order_id');
        $data['order_supplier_invoice_number'] = $this->input->post('supplier_invoice_number');
        if($this->mdl_orders->updateOrder($order_id,$data)){
            echo 'success';
        }else{
            echo 'fail';
        }
        exit;
    }
    
    
    function getOrderItemsJSON() {
        
        $order_id = $this->input->post('order_id');
        $items = $this->mdl_orders->get_order_items_JSON($order_id);
        
        $data = array(
            'items' => (is_array($items->sql_order_items)?$items->sql_order_items:array()),
            'order_amounts' => $items->sql_order_amount,
        );
        echo json_encode($data);
    }
    
    function updateOrderItemJSON() {
        
        $order_id = $this->input->post('order_id');
        $item = json_decode($this->input->post('item'));
        
//        echo '<pre>';
//        print_r($item);
//        die;
        
        $items = $this->mdl_orders->update_order_items_json($order_id, $item);
        
        $data = array(
            'item' => $items->updateData,
            'order_amounts' => $items->order_amounts,
        );
        echo json_encode($data);
    }
    
    function deleteOrderItemsJSON() {
        $order_id = json_decode($this->input->post('order_id'));
        $order_item_ids = $this->input->post('order_item_ids');
        
        $chk = $this->mdl_orders->get_Row('mcb_order_inventory_items', array('order_id' => $order_id));
        if ($chk != NULL) {
            $tableName = 'mcb_order_inventory_items';
        }else{
            $tableName = 'mcb_order_items';
        }
        if ($order_item_ids != '') {
            $order_item_ids = explode(',', $order_item_ids);
            if (sizeof($order_item_ids) > 0) {
                foreach ($order_item_ids as $order_item_id) {
                    $order_item_id = trim($order_item_id);
                    if ($order_item_id != '') {
                        $condition = array('order_item_id' => $order_item_id);
                        $this->mdl_orders->m_Delete($tableName, $condition);
                    }
                }
            }
        }
        $data = array(
            'order_amounts' => $this->mdl_orders->total_order_amount($order_id)
        );
        echo json_encode($data);
    }
    
    function addNewOrderItemJSON() {
        
        $order_id = $this->input->post('order_id');
        $item = json_decode($this->input->post('item'));
        
        $new_item = $this->mdl_orders->addNewOrderItemJSON($order_id, $item);
        
        $data = array(
            'item' => $new_item->addedData,
            'order_amounts' => $new_item->order_amounts
        );
        echo json_encode($data);
        
        
    }
    
    function setItemsSortOrderJSON() {
        $order_id = $this->input->post('order_id');
        $items = explode(",", $this->input->post('sort_order'));
        if(sizeof($items) > 0){
            $i = 0;
            foreach($items as $item){
                $this->mdl_orders->updateOrderItemPosition($i,$item);
                $i++;
            }
        }
        echo TRUE;
        exit;
    }
    
    
    
    function supplier_inventory_name_auto_complete_JSON() {
        
        $search_term = $this->input->post('term');
        $supplier_id = $this->input->post('supplier_id');
        $data = array(
            'search_results' => $this->mdl_orders->getSearchInventoryNameResults($search_term, $supplier_id));
        echo json_encode($data);
    }
    
    function custom_qry_1() {
       
        $all_inventry = $this->mdl_orders->query_object('SELECT * FROM `mcb_inventory_item`');
        $c = 0;
        foreach ($all_inventry as $value) {
            
            $data = array('item_supplier_code'=>$value->supplier_code, 'item_supplier_description'=>$value->supplier_description);
            $condition = array('inventory_id'=>$value->inventory_id);
            
//            echo '<pre>';
//            print_r($data);
//            print_r($condition);
            
            $this->mdl_orders->update('mcb_order_inventory_items', $data, $condition);
            echo $c++.' ----->>>> '.$value->inventory_id.'<br>';
        }
        
    }
    
    function insert_id_by_name() {
        
        $all_inventry_itms = $this->mdl_orders->query_object('SELECT * FROM `mcb_order_inventory_items`');
        
        $c = 0;
        foreach ($all_inventry_itms as $value) {
            
            if($value->inventory_id == '0'){
                
                echo '<br>'.$c++.'--->>'.$name_inven = $this->mdl_orders->get_Row('mcb_order_inventory_items', array('item_name' => $value->item_name))->item_name;
                
                $r = $this->mdl_orders->get_Row('mcb_inventory_item', array('name' => $name_inven));
                
                $data=array(
                    'inventory_id'=>$r->inventory_id,
                    'item_supplier_code'=>$r->supplier_code,
                    'item_supplier_description'=>$r->supplier_description,
                );
                
                $data2=array(
//                    'inventory_id'=>$r->inventory_id,
                    'item_supplier_code'=>$value->item_name,
                    'item_supplier_description'=>$value->item_description,
                );
                
                
                
                if($r != NULL){
                    // $this->mdl_orders->update('mcb_order_inventory_items', $data, array('item_name' => $value->item_name));
                }else{
                    // $this->mdl_orders->update('mcb_order_inventory_items', $data2, array('item_name' => $value->item_name));
                }
                
                
                echo '<br>id ---'.$r->inventory_id;
                echo '<br>order id ---'.$value->order_id;
                echo '<br>name---'.$r->name;
                echo '<br>supplier_code---'.$r->supplier_code;
                echo '<br>';
                echo '<br>';
                
            }
            
            
        }
        
        
    }
    function php_info() {
        echo phpinfo();
    }
}

?>