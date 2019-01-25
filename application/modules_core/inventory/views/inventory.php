<?php

(defined('BASEPATH')) OR exit('No direct script access allowed');

class Inventory extends Admin_Controller {

    function __construct() {

        parent::__construct();
        $this->_post_handler();

        $this->load->model('mdl_inventory_item');
        $this->load->model('mdl_inventory_history');
    }

    function index() {

        $this->load->helper('text');
        $this->redir->set_last_index();
        $params = array(
            'paginate' => TRUE,
            'limit' => $this->mdl_mcb_data->setting('results_per_page'),
            'page' => uri_assoc('page')
        );

        $default_order_by = 'inventory_id DESC';

        $params['order_by'] .= $default_order_by;

        $data = array(
            'inventory' => $this->mdl_inventory_item->get($params),
            'sort_links' => TRUE,
            'order_by' => $params['order_by']
        );
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
    
    function update_inv_rel(){
        error_reporting(E_ALL); ini_set('display_errors', '1');
        $inventory = json_decode($this->input->post('post_item'));
        $this->load->model('mdl_inventory_item');
        if ((float) $inventory->qty > 0) {
            $this->mdl_inventory_item->updateinventoryRel($inventory->ii, $inventory->qty, $inventory->pi);
        }
        echo TRUE; exit;
    }

    function get_inventory_JSON($params = NULL) {

        $limit = $this->input->post('limit');
        $offset = $this->input->post('offset');
        $this->load->model('clients/mdl_clients');


        $data = array(
            'suppliers' => $this->mdl_clients->get_active_suppliers(),
            'inventory' => $this->mdl_inventory_item->get_raw($limit, $offset)
        );


        echo json_encode($data);
    }

    function get_inventory_list() {
        $limit = $this->input->post('limit');
        $offset = $this->input->post('offset');
        $this->load->model('clients/mdl_clients');

        $inventory = $this->mdl_inventory_item->get_raw($limit, $offset);
        $finInv = array();
        if (sizeof($inventory) > 0) {
            foreach ($inventory as $inv) {
                $inv->qty = 1;
                $finInv[] = $inv;
            }
        }

        //echo '<pre>'; print_r($finInv); exit;

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

    function form() {


        if ($this->mdl_inventory_item->validate() && $this->mdl_inventory_history->validate()) {

            $this->mdl_inventory_item->save();
            $this->mdl_inventory_history->save();

            redirect($this->session->userdata('last_index'));
        } else {
            $this->load->helper('form');

            if (!$_POST AND uri_assoc('inventory_id')) {

                $this->mdl_inventory_item->prep_validation(uri_assoc('inventory_id'));
            }

            $this->load->model('clients/mdl_clients');
            $this->load->model('users/mdl_users');
            $id = $this->mdl_inventory_item->form_value(inventory_id);
            $inventory_history = $this->mdl_inventory_history->get_history_by_iventoryId($id);
            $this->load->helper('text');
            $suppliers = $this->mdl_clients->get_active_suppliers();
            $users = $this->mdl_users->get_raw();
            $data = array(
                'suppliers' => $suppliers,
                'suppliers_json' => json_encode($suppliers),
                'inventory_history' => json_encode($inventory_history),
                'users' => json_encode($users)
            );

            $this->load->view('form', $data);
        }
    }

    function _post_handler() {
        if ($this->input->post('btn_add_inventory')) {

            redirect('inventory/form');
        }
        if ($this->input->post('btn_upload_inventory')) {
            redirect('inventory/upload_inventory');
        }
        if ($this->input->post('btn_export_inventory')) {
            redirect('inventory/export_inventory');
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

    public function link_to_product() {
        $this->load->view('link_to_product');
    }
    
    public function one_to_one_product_inv(){
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
        
        $invlistdetail = $this->input->post('invlistdetail');
        $prodlistdetail = $this->input->post('prodlistdetail');
        
        $this->load->model('inventory/mdl_inventory_item');
        
        echo '<pre>'; print_r($invlistdetail); 
        print_r($prodlistdetail); exit;
         
        if($this->mdl_inventory_item->updateinvprodrelation($invlistdetail,$prodlistdetail)){
            $this->session->set_flashdata('custom_success', 'Successfully applied to product.');
            echo 'success'; exit;
        }
        $this->session->set_flashdata('custom_error', 'Error while applying.');
        echo 'fail'; exit;
    }
    
    public function get_one_to_one_prod_inv(){
        $this->load->model('inventory/mdl_inventory_item');
        $one_to_one_list = $this->mdl_inventory_item->get_one_to_one_prod_inv();
        echo json_encode($one_to_one_list); exit;
    }

}

?>
