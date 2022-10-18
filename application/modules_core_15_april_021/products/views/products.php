<?php

(defined('BASEPATH')) OR exit('No direct script access allowed');

class Products extends Admin_Controller {

    function __construct() {

        parent::__construct();

        $this->_post_handler();

        $this->load->model('mdl_products');
    }

    function index() {

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
            'supplier_id' => $supplier_id,
        );
        

        if ($this->input->get('history_id') != NULL && (int) $this->input->get('history_id') > 0) {
            $this->load->model('mdl_products_import');
            $data['import_log'] = $this->mdl_products_import->getLogMessage($this->input->get('history_id'));
        }
            echo '<pre>';
            print_r($data);exit;
        $this->load->view('index', $data);
        
    }

    function form() {

        if ($this->mdl_products->validate()) {
            $this->mdl_products->save();
            $this->load->model('inventory/mdl_inventory_item');
            $this->load->model('mdl_products_inventory');
            if ($this->input->post("deleteInventory"))
                $this->mdl_products_inventory->delete();
            $this->mdl_products_inventory->save();
            $this->mdl_inventory_item->save_duplicate_products();
            redirect($this->session->userdata('last_index'));
        }

        else {

            $this->load->helper('form');

            if (!$_POST AND uri_assoc('product_id')) {
                $this->mdl_products->prep_validation(uri_assoc('product_id'));
            }

            // prepare list of active suppliers since product belongs to a supplier
            $this->load->model('clients/mdl_clients');
            $this->load->model('inventory/mdl_inventory_item');

            $this->load->helper('text');
            $id = $this->mdl_products->form_value(product_id);
            $inventory_items = $this->mdl_inventory_item->get_inventory_by_productId($id);
            //var_dump($inventory_items);
            //die('here');
            $all_inventory_item = $this->mdl_inventory_item->get_raw();
            $suppliers = $this->mdl_clients->get_active_suppliers_with_product();

            $data = array(
                'suppliers' => $suppliers,
                'suppliers_json' => json_encode($suppliers),
                'all_inventory_items' => $all_inventory_item,
                'inventory_items' => json_encode($inventory_items)
            );
            
            //getting product details if the product_id is there
            if(uri_assoc('product_id')){
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

    function get_products_JSON($params = NULL) {
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
            $where = " where mcb_products.supplier_id='" . $this->input->get('supplier_id') . "'";
            $data = array(
                'suppliers' => $this->mdl_clients->get_active_suppliers(),
                //'suppliers'	=> $this->mdl_clients->get(),
                'products' => $this->mdl_products->getProductList($where)
            );
        } else {
            $data = array(
                'suppliers' => $this->mdl_clients->get_active_suppliers(),
                //'suppliers'	=> $this->mdl_clients->get(),
                'products' => $this->mdl_products->get($params),
            );
        }

        echo json_encode($data);
    }

    function get_products_descriptions_JSON() {

        $product_ids = json_decode($this->input->post('products'));

        echo json_encode($this->mdl_products->get_product_descriptions($product_ids));
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

        if ($this->mdl_products->update_product($product) > 0) {

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

}

?>
