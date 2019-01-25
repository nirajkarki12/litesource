<?php

(defined('BASEPATH')) OR exit('No direct script access allowed');

class Products extends Admin_Controller {

    function __construct() {

        parent::__construct();

        $this->_post_handler();

        $this->load->model('mdl_products');
    }

    function index() {
        
//        error_reporting(E_ALL);
//        ini_set('display_errors', 1);
//        echo $rrr;
        
        $this->load->helper('text');
        $this->redir->set_last_index();
        $this->load->model('clients/mdl_clients');
        
        $params = array(
            'paginate' => TRUE,
            'limit' => $this->mdl_mcb_data->setting('results_per_page'),
            'page' => uri_assoc('page'),
                //'where'		=>  array('client_active'=>1)
        );

        $order_by = uri_assoc('order_by');

        switch ($order_by) {
            case 'product_name':
                $params['order_by'] = 'product_name';
                break;
            case 'supplier':
                $params['order_by'] = 'supplier_name';
                break;
            case 'product_base_price':
                $params['order_by'] = 'product_base_price';
                break;
            case 'product_supplier_price':
                $params['order_by'] = 'product_supplier_price';
                break;
            default:
                $params['order_by'] = 'supplier_name, product_name';
        }

        $supplier_id = 0;

        if (uri_assoc('supplier_id')) {

            $supplier_id = uri_assoc('supplier_id');
        }



        /*
          $data = array(
          'products' => $this->mdl_products->get($params),
          'order_by' => $params['order_by']
          ); */
        $data = array(
            'parent_suppliers' => $this->mdl_clients->get_parent_suppliers(),
            'suppliers' => $this->mdl_clients->get_active_suppliers(),
            'supplier_with_product' => $this->mdl_clients->get_active_suppliers_with_product(),
            'supplier_id' => $supplier_id,
        );
        
        if ($this->input->get('history_id') != NULL && (int) $this->input->get('history_id') > 0) {
            $this->load->model('mdl_products_import');
            $data['import_log'] = $this->mdl_products_import->getLogMessage($this->input->get('history_id'));
        }

        $this->load->view('index', $data);
    }

    function form() {
        //error_reporting(E_ALL); ini_set('display_errors', 1);
        
        if ($this->mdl_products->validate()) {
            
            $p_id = uri_assoc('product_id');
//            if($this->input->post('supplier_id') == '0'){
//                $this->session->set_flashdata('custom_error', 'Please Select Supplier.');
//                redirect(site_url('products/form/product_id/'.$p_id));
//            }
            $duplicate = false;
            if(!uri_assoc('product_id')){
                //checking if duplicate name, show message
                // $duplicate = $this->mdl_products->checkDuplicate_product_supplier_code($this->input->post('product_supplier_code'));
            }
            
            if($duplicate){
                $this->session->set_flashdata('custom_error', 'Duplicate product name');
                redirect(site_url('products/form'));
            }
            $product_id = $this->mdl_products->save();
            $p_id = $product_id;
            if(uri_assoc('product_id')){
                $p_id = uri_assoc('product_id');
            }
            
            $this->load->model('inventory/mdl_inventory_item');
            $this->load->model('mdl_products_inventory');
            if ($this->input->post("deleteInventory"))  
                $this->mdl_products_inventory->delete();
            $this->mdl_products_inventory->save();
            
            $this->mdl_inventory_item->save_duplicate_product($p_id);
            
            if($this->input->post('product_duplicate') != NULL && $this->input->post('product_duplicate') == '1' && uri_assoc('product_id')){
                redirect(site_url('products/form/product_id/'.$p_id));
            }else{
                redirect(site_url('products'));
            }
            
            
        }

        else {

            $this->load->helper('form');

            if (!$_POST AND uri_assoc('product_id')) {
                $this->mdl_products->prep_validation(uri_assoc('product_id'));
            }
            $prod_id = uri_assoc('product_id');
            // prepare list of active suppliers since product belongs to a supplier
            $this->load->model('clients/mdl_clients');
            $this->load->model('inventory/mdl_inventory_item');

            $this->load->helper('text');
            $id = $this->mdl_products->form_value($prod_id);
            $inventory_items = $this->mdl_inventory_item->get_inventory_by_productId($prod_id);
            //echo '';
            //var_dump($inventory_items);
            //die('here');
            $all_inventory_item = $this->mdl_inventory_item->get_raw();
            $allsuppliers = $this->mdl_clients->get_active_suppliers();
            $suppliers = $this->mdl_clients->get_active_suppliers_with_product();

            $data = array(
                'allsuppliers' => $allsuppliers,
                'suppliers' => $suppliers,
                'suppliers_json' => json_encode($suppliers),
                'all_inventory_items' => $all_inventory_item,
                'inventory_items' => json_encode($inventory_items)
            );

            //getting product details if the product_id is there
            if (uri_assoc('product_id')) {
                $data['product_detail'] = $this->mdl_products->getProductDetail(uri_assoc('product_id'));
            }
            
            $this->load->view('form', $data);
        }
    }

    function details() {

        $this->redir->set_last_index();

        /*
          $this->load->model(
          array(
          'products/mdl_products',
          )
          );
         */
        $product_params = array(
            'where' => array(
                'mcb_products.product_id' => uri_assoc('product_id')
            )
        );

        /*
          $product_params = array(
          'where'	=>	array(
          'mcb_products.supplier_id'	=>	uri_assoc('supplier_id')
          ),
          'set_supplier'	=>	TRUE
          );
         */

        $product = $this->mdl_products->get($product_params);

        if ($this->session->flashdata('tab_index')) {

            $tab_index = $this->session->flashdata('tab_index');
        } else {

            $tab_index = 0;
        }

        $data = array(
            'product' => $product,
            'tab_index' => $tab_index
        );

        $this->load->view('details', $data);
    }

    function delete() {

        if (uri_assoc('product_id')) {

            $this->mdl_products->delete(uri_assoc('product_id'));
        }

        $this->redir->redirect('products');
    }

    function get($params = NULL) {

        return $this->mdl_products->get($params);
    }

    function products_JSON_params() {

        $params = array(
            'order_by' => "mcb_clients.client_name, product_name",
        );

        $params['select'] = "
			mcb_products.product_id id,
			mcb_products.product_name n,
			mcb_products.product_supplier_code c,
			mcb_products.product_supplier_price b,
			mcb_products.product_base_price p,
			mcb_products.supplier_id s";


        //$params['debug'] = 1;

        return $params;
    }

    
    function m_m_link_product($params = NULL) {
        $this->load->model('clients/mdl_clients');
        $data = array(
            'suppliers' => $this->mdl_clients->get_active_suppliers(),
            'products' => $this->mdl_products->m_m_link_product(),
        );
        echo json_encode($data);
    }
    
    function get_products_JSON_link($params = NULL) {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        /*
         * SELECT mcb_products.product_id id,
          mcb_products.product_name n,
          mcb_products.product_supplier_code c,
          mcb_products.product_supplier_price b,
          mcb_products.product_base_price p,
          mcb_products.supplier_id s
          FROM mcb_products
          JOIN mcb_clients ON mcb_clients.client_id = mcb_products.supplier_id
          JOIN mcb_currencies ON mcb_currencies.currency_id = mcb_clients.client_currency_id
          LEFT JOIN  mcb_clients AS parent_suppliers ON parent_suppliers.client_id = mcb_clients.parent_client_id

         */
        $params = $this->products_JSON_params();

        $this->load->model('clients/mdl_clients');

        $where = '';
        if ($this->input->get('supplier_id') != NULL) {
            $where = " where mcb_products.is_arichved != '1' AND mcb_products.product_name != '' AND mcb_products.supplier_id='" . $this->input->get('supplier_id') . "'";
            $data = array(
                'suppliers' => $this->mdl_clients->get_active_suppliers(),
                //'suppliers'	=> $this->mdl_clients->get(),
                'products' => $this->mdl_products->getProductList($where)
            );
        } else {

            $data = array(
                'suppliers' => $this->mdl_clients->get_active_suppliers(),
                //'suppliers'	=> $this->mdl_clients->get(),
                //'products' => $this->mdl_products->get($params),
                'products' => $this->mdl_products->getAllProductsEvenWithZeroSupplierLink(),
            );
        }

        echo json_encode($data);
    }

    function getinventoriespop() {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        $product_id = $this->input->get('pid');
        $inventories = $this->mdl_products->getInventoriesofProduct($product_id);
        //echo '<pre>'; print_r($inventories); exit;
        if (sizeof($inventories) > 0) {
            $popHtml = '<div class="inv-detail">';
            $popHtml .= '<h2>' . $this->input->post('name') . '</h2><br/>';

            $popHtml .= '<h3>Inventory Items:</h3>';
            $popHtml .= '<table class="table table-bordered order-prod-list">';

            $popHtml .= '<thead>';
            $popHtml .= '<tr>';
            $popHtml .= '<td>S.N</td>';
            $popHtml .= '<td>Inventory Item Name</td>';
            $popHtml .= '<td>Product Relation Qty</td>';
            $popHtml .= '</tr>';
            $popHtml .= '</thead>';
            $popHtml .= '<tbody>';
            $i = 1;
            foreach ($inventories as $inv) {
                $popHtml .= '<tr>';
                $popHtml .= '<td>' . $i . '</td>';
                $popHtml .= '<td>' . $inv->name . '</td>';
                $popHtml .= '<td>' . $inv->inventory_qty . '</td>';
                $popHtml .= '</tr>';
                $i++;
            }

            $popHtml .= '</tbody></table></div>';
        }
        echo $popHtml; exit;
    }

    function get_products_JSON($params = NULL) {
        //error_reporting(E_ALL);
        //ini_set('display_errors', 1);
        /*
         * SELECT mcb_products.product_id id,
          mcb_products.product_name n,
          mcb_products.product_supplier_code c,
          mcb_products.product_supplier_price b,
          mcb_products.product_base_price p,
          mcb_products.supplier_id s
          FROM mcb_products
          JOIN mcb_clients ON mcb_clients.client_id = mcb_products.supplier_id
          JOIN mcb_currencies ON mcb_currencies.currency_id = mcb_clients.client_currency_id
          LEFT JOIN  mcb_clients AS parent_suppliers ON parent_suppliers.client_id = mcb_clients.parent_client_id

         */
        $params = $this->products_JSON_params();

        $this->load->model('clients/mdl_clients');

        $where = '';
        if ($this->input->get('supplier_id') != NULL) {
            $where = " where mcb_products.is_arichved != '1' AND mcb_products.supplier_id='" . $this->input->get('supplier_id') . "'";
            $data = array(
                'suppliers' => $this->mdl_clients->get_active_suppliers(),
                'products' => $this->mdl_products->getProductList($where)
            );
        } else if($this->input->get('show_archived') == TRUE){
            
            $where = "where mcb_products.product_name != ''";    
            $data = array(
                'suppliers' => $this->mdl_clients->get_active_suppliers(),
                'products' => $this->mdl_products->getAllProductsEvenWithZeroSupplier($where),
            );
           
        }else if($this->input->get('only_archived') == TRUE){
            $where = "where mcb_products.product_name != '' AND mcb_products.is_arichved = '1'";  
            $data = array(
                'suppliers' => $this->mdl_clients->get_active_suppliers(),
                'products' => $this->mdl_products->getAllProductsEvenWithZeroSupplier($where),
            );
        } else {
            $where = "where mcb_products.product_name != '' AND mcb_products.is_arichved != '1'";  
            $data = array(
                'suppliers' => $this->mdl_clients->get_active_suppliers(),
                'products' => $this->mdl_products->getAllProductsEvenWithZeroSupplier($where),
            );
        }

        echo json_encode($data);
    }

    function getUnlinkedProducts() {
        $this->load->model('clients/mdl_clients');
        $data = array(
            'products' => $this->mdl_products->getAllProductsUnlinked(),
        );
        echo json_encode($data);
    }

    function get_products_descriptions_JSON() {

        $product_ids = json_decode($this->input->post('products'));

        echo json_encode($this->mdl_products->get_product_descriptions($product_ids));
    }

    function ajax_update_product_supplier() {
        $item = json_decode($this->input->post('post_item'));

        $product->product_id = $item->id;
        $product->product_name = $item->n;
        $product->product_supplier_code = $item->c;
        $product->product_supplier_price = $item->b;
        $product->product_base_price = $item->p;
        $product->product_description = $item->d;
        $product->client_name = $item->supplier_name;



        if ($this->mdl_products->update_product_supplier($product) > 0) {

            $params = $this->products_JSON_params();

            $params['select'] .= ',mcb_products.product_description d';

            $params['return_row'] = TRUE;
            $params['where'] = array('mcb_products.product_id' => $product->product_id);

            $data = array(
                'product' => $this->mdl_products->get($params)
            );
            

            echo json_encode($data);
        }
    }

    function ajax_update_product() {


        $item = json_decode($this->input->post('post_item'));


        $product->product_id = $item->id;
        $product->product_name = $item->n;
        $product->product_supplier_code = $item->c;
        $product->product_supplier_price = $item->b;
        $product->product_base_price = $item->p;
        $product->product_description = $item->d;
        $product->client_name = $item->client_name;
        
        $product->supplier_name = $item->supplier_name;
       

        if ($this->mdl_products->update_product($product) > 0) {

            $params = $this->products_JSON_params();

            $params['select'] .= ',mcb_products.product_description d';

            $params['return_row'] = TRUE;
            $params['where'] = array('mcb_products.product_id' => $product->product_id);

//            $data = array(
//                'product' => $this->mdl_products->get($params)
//            );
//            print_r($this->db->last_query()); exit;
            
            $data = array(
                'product' => $this->mdl_products->getProductDetailById($product->product_id)
            );
            
            //print_r($this->db->last_query()); exit;
            
            echo json_encode($data);
        }
    }

    function jquery_products_by_supplier() {

        $supplier_id = uri_assoc('supplier_id');

        $search_term = $this->input->post('term');


        $data = array(
            'products' => $this->mdl_products->search_by_supplier($supplier_id, $search_term)
        );

        echo json_encode($data);
    }

    function jquery_search_autocomplete() {

        $search_term = $this->input->post('term');


        $data = array(
            'search_results' => $this->mdl_products->getSearchResults($search_term));


        echo json_encode($data);
    }

    function jquery_product_data() {

        /* This function is only used to send JSON data back to a jquery function */
        $product = $this->mdl_products->get_product_by_name($this->input->post('product_name'));
        $invoice_item_id = $this->input->post('invoice_item_id');

        /*
          $array = array(
          'product_id'			=>  $product->product_id,
          'product_name'			=>	$product->product_name,
          'product_base_price'	=>	format_number($product->product_base_price, FALSE),
          'product_description'	=>	$product->product_description
          );
         */
        $data = array(
            'invoice_item_id' => $invoice_item_id,
            'product' => $product,
            'item_price' => $product->product_base_price
        );

        echo json_encode($array);
    }

    function _post_handler() {

        if ($this->input->post('btn_add_product')) {

            redirect('products/form');
        } else if ($this->input->post('btn_upload_products')) {

            redirect('products/upload_products');
        } else if ($this->input->post('btn_export_products')) {

            redirect('products/export_products');
        } elseif ($this->input->post('btn_cancel')) {

            redirect($this->session->userdata('last_index'));
        }
    }

    public function rollback() {
        $this->load->model('products/mdl_products_import');

        $data = array(
            'import_history' => $this->mdl_products_import->getImportHistory(),
        );

        $this->load->view('import_history', $data);
    }

    public function dorollback($historyid = FALSE) {
        $this->load->model('products/mdl_products_import');
        if ($historyid) {
            if ($this->mdl_products_import->dorollback($historyid)) {
                $this->session->set_flashdata('custom_success', 'Rollback successful.');
            } else {
                $this->session->set_flashdata('custom_error', 'Error while rolling back.');
            }
        }
        redirect('products/rollback');
    }

    public function doArchive() {
        $this->load->model('products/Mdl_Products');
        if($_POST){
            
            $arry = $this->input->post('prolistdetail');
            
            echo "<pre>";
            foreach ($arry as $value) {
                
                $detail = $this->Mdl_Products->get_Row('mcb_products', array('product_id'=>$value['id']) );
                
                if( $detail != '' ){
                    $this->Mdl_Products->update('mcb_products', array('is_arichved'=>'1'),  array('product_id'=>$detail->product_id) );
                }
            }
            $this->session->set_flashdata('custom_success', 'Successfully archived products.');
            echo 'success'; exit;
        }
        $this->session->set_flashdata('custom_error', 'Error while applying.');
        echo 'fail'; exit;
    }
    
    
    
    public function undoArchive() {
        $this->load->model('products/Mdl_Products');
        if($_POST){
            
            $arry = $this->input->post('prolistdetail');
            
            echo "<pre>";
            foreach ($arry as $value) {
                
                $detail = $this->Mdl_Products->get_Row('mcb_products', array('product_id'=>$value['id']) );
                
                if( $detail != '' ){
                    $this->Mdl_Products->update('mcb_products', array('is_arichved'=>'0'),  array('product_id'=>$detail->product_id) );
                }
            }
            $this->session->set_flashdata('custom_success', 'Successfully Un-archived products.');
            echo 'success'; exit;
        }
        $this->session->set_flashdata('custom_error', 'Error while applying.');
        echo 'fail'; exit;
    }
    
    
}

?>
