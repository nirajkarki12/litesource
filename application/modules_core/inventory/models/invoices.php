<?php

(defined('BASEPATH')) OR exit('No direct script access allowed');

class Invoices extends Admin_Controller {

    function __construct() {

        parent::__construct();

        $this->load->model('mdl_invoices');
    }

    function index() {

        $this->_post_handler();

        $this->redir->set_last_index();

        $this->load->helper('text');

        $order_by = uri_assoc('order_by');

        $client_id = uri_assoc('client_id');

        $status = uri_assoc('status');

        $is_quote = uri_assoc('is_quote');

        $params = array(
            'paginate' => TRUE,
            'limit' => $this->mdl_mcb_data->setting('results_per_page'),
            'page' => uri_assoc('page'),
            'where' => array()
        );

        $params['where']['mcb_invoices.invoice_is_quote'] = ($is_quote) ? 1 : 0;

        /*
          if (!$this->session->userdata('global_admin')) $params['where']['mcb_invoices.user_id'] = $this->session->userdata('user_id');
         */
        if ($client_id) {

            $params['where']['mcb_invoices.client_id'] = $client_id;
        }

        if ($status) {

            $params['where']['invoice_status'] = $status;
        }

        switch ($order_by) {
            case 'invoice_id':
                $params['order_by'] = 'mcb_invoices.invoice_number DESC,';
                break;
            case 'client':
                $params['order_by'] = 'client_name,';
                break;
            case 'project':
                $params['order_by'] = 'project_name,';
                break;
            default:
                $params['order_by'] = '';
        }

        // always include date by default
        $params['order_by'] .= 'FROM_UNIXTIME(mcb_invoices.invoice_date_entered) DESC, mcb_invoices.invoice_id DESC';


        $invoices = $this->mdl_invoices->get($params);

        $this->load->model('templates/mdl_templates');
        $this->load->model('delivery_dockets/mdl_delivery_dockets');

        $data = array(
            'invoices' => $invoices,
            'sort_links' => TRUE,
            'dockets' => $this->mdl_delivery_dockets->get_invoice_dockets($invoices->invoice_id)
        );
        $this->load->view('index', $data);
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
                    //'invoice_groups'	=>	$this->mdl_invoice_groups->get()
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
        
        $tab_index = ($this->input->get('tab') != NULL)?$this->input->get('tab'):(($this->session->flashdata('tab_index')) ? $this->session->flashdata('tab_index') : 0);
        
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

        //log_message('INFO', 'Invoice tax rate :'.$invoice->tax_rate_name);

        if (!$invoice) {

            redirect('dashboard/record_not_found');
        }

        $data = array(
            'invoice' => $invoice,
            'internaldetail' => $this->mdl_invoices->getQuoteInternalFromInvoiceId($invoice->invoice_id),
            'payments' => $this->mdl_invoices->get_invoice_payments($invoice->invoice_id),
            'history' => $this->mdl_invoices->get_invoice_history($invoice->invoice_id),
            'invoice_items' => $this->mdl_invoices->get_invoice_items($invoice->invoice_id),
            //'invoice_tax_rates' =>  $this->mdl_invoices->get_invoice_tax_rates($invoice->invoice_id),
            'orders' => $this->mdl_orders->get_invoice_orders($invoice->invoice_id),
            'dockets' => $this->mdl_delivery_dockets->get_invoice_dockets($invoice->invoice_id),
            'tags' => $this->mdl_invoices->get_invoice_tags($invoice->invoice_id),
            'projects' => $this->mdl_projects->get_active(),
            'clients' => $this->mdl_clients->get_active(),
            'contacts' => $this->mdl_contacts->get_client_contacts($invoice->client_id),
            'tax_rates' => $this->mdl_tax_rates->get(),
            'client_groups' => $this->mdl_client_groups->get(),
            'invoice_statuses' => $this->mdl_invoice_statuses->get(),
            'tab_index' => $tab_index,
            'custom_fields' => $this->mdl_fields->get_object_fields(1),
            'users' => $this->mdl_users->get()
        );
        
        $this->load->view('invoice_edit', $data);
        
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
        if (isset($item->name) && $item->name) {
            $max = $this->mdl_inventory_item->isMaxOrderQty($item->item_name)[0]->max_qty;

            if (floatval($max) < floatval($item->item_qty) && $max != null) {
                $this->session->set_flashdata('custom_error', "Not enough " . $item->item_name . " item in inventory");
                echo json_encode("Not enough");
                return;
            }
        }
        // see if there is a corresponding product
        //$product = $this->mdl_products->get_product_by_name($item->item_name);
        //
		//if isset($item->product_id)
        $product_id = (isset($item->product_id) ? $item->product_id : 0);
        $item_indx = isset($item->item_index) ? $item->item_index : '';
        $new_item = $this->mdl_invoices->add_invoice_item($invoice_id, $product_id, $item->item_name, $item->item_description, $item->item_qty, $item->item_price, $item_indx);

        $data = array(
            'item' => $new_item,
            'invoice_amounts' => $this->mdl_invoices->get_invoice_amounts($invoice_id),
                //'invoice_item_amounts'	=> $this->mdl_invoices->get_invoice_item_amounts($new_item->invoice_item_id),
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
        $this->load->model("inventory/mdl_inventory_item");

        $invoice_id = $this->input->post('invoice_id');
        $item = json_decode($this->input->post('item'));
        //there must be product id to compare, we will not be using item_name, instead
        //each product has a unique product id
        $this->load->model('products/mdl_products');
//        $quantity_stock = array(
//            'color' => 'green',
//            'message' => 'New product.'
//        );
//        if (trim($item->item_name) != '') {
//            $product = $this->mdl_products->get_product_by_name($item->item_name);
//            //this is just a fallback approach temp fix which used through out used a standard: getting product detail from naem.
//            // The actual fix is in jquery ui
//            $prod_id = ($item->product_id && $item->product_id != 0) ? $item->product_id : ($product->product_id && $product->product_id != 0) ? $product->product_id : 0;
//            if ($prod_id != 0) {
//
//                //getting the quantity in stock
//                //this will check the same amount in each inventory items
//                //every inventory items should have that amount
//                //if there are no inventory items, it will be notified
//                //************2017-9-7********* Now the logic is changed. There is relation between number of inventory items to unit product
//                //$quantity_stock = $this->mdl_inventory_item->getStockQuantiy($prod_id, $item->item_qty, $item->item_name, $item);
//                //keeping the old logic
//                //            $max = $this->mdl_inventory_item->isMaxOrderQty($item->item_name)[0]->max_qty;
//                //            if (floatval($max) < floatval($item->item_qty) && $max != null) {
//                //                $this->session->set_flashdata('custom_error', "Not enough " . $item->item_name . " item in inventory");
//                //                echo json_encode("Not enough");
//                //                return;
//                //            }
//
////                if (!$quantity_stock['result']) {
////                    $this->session->set_flashdata('custom_error', $quantity_stock['message']);
////                    //echo '<pre>'; print_r($item); exit;
////                    //echo 'vejja'; exit;
////                    echo json_encode("Not enough");
////                    exit;
////                }
//            }
//        }
        $data = array(
            'item' => $this->mdl_invoices->update_invoice_item($invoice_id, $item),
            'invoice_amounts' => $this->mdl_invoices->get_invoice_amounts($invoice_id),
                //'invoice_item_amounts'	=> $this->mdl_invoices->get_invoice_item_amounts($item->invoice_item_id),
        );

        echo json_encode($data);
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

    function copy_invoice($create_new_quote, $redirect = TRUE) {

        $invoice_id = uri_assoc('invoice_id');

        $invoice_date_entered = format_date(time());

        $this->mdl_invoices->copy_invoice($invoice_id, $invoice_date_entered, $create_new_quote, $redirect);

        //redirect('quotes/edit/invoice_id/' . $invoice_id);


        /*
         *
          if (!$this->mdl_invoices->validate_copy_invoice()) {

          $this->load->model('mdl_invoice_groups');

          $data = array(
          'invoice_groups'	=>	$this->mdl_invoice_groups->get(),
          'invoice'			=>	$this->mdl_invoices->get_by_id($invoice_id)
          );

          $this->load->view('quote_to_invoice', $data);

          }

          else {

          $this->mdl_invoices->copy_invoice($invoice_id, $this->input->post('invoice_date_entered'), $this->input->post('invoice_group_id'), $create_new_quote);

          redirect('invoices/edit/invoice_id/' . $invoice_id);

          }
         */
    }

    function quote_to_orders_invoice() {
	
        $this->copy_invoice(0, FALSE);
        $this->quote_to_orders();
    }

    function quote_to_invoice() {

        $this->copy_invoice(0);
    }

    function quote_to_orders() {

        $this->session->set_flashdata('tab_index', 4);

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
            $order_date_entered = time();
            $finres = $this->mdl_orders->create_invoice_orders($invoice_id, $order_date_entered);
            if (!$finres['status']) {
                // if orders not created, must need to fix up some products that
                // don't yet have a supplier (supplier_id = -1)
                $this->session->set_flashdata('custom_error', 'Quote copied successfully.<br/>' . $finres['message']);
                redirect('products');
            }
        }
        $this->session->set_flashdata('custom_success', 'Quote copied successfully. <br/> Quote conversion successful.');
        redirect('quotes/edit/invoice_id/' . $invoice_id);
    }

    function copy_quote() {

        $this->copy_invoice(1);

        /*
          if ($this->input->post('btn_cancel')) {

          redirect('invoices');

          }

          if (!$this->mdl_invoices->validate_create()) {


          $this->load->model(array('clients/mdl_clients', 'clients/mdl_contacts'));

          $this->load->helper('text');
          $invoice_id = uri_assoc('invoice_id');

          $data = array(
          'clients'			=>	$this->mdl_clients->get_active(),
          'contacts'			=>	$this->mdl_contacts->get(),
          'invoice'			=>	$this->mdl_invoices->get_by_id($invoice_id)
          );

          $this->load->view('choose_client', $data);

          }

          else {

          $this->load->module('invoices/invoice_api');

          $package = array(
          'client_id'				=>	$this->input->post('client_id'),
          'contact_name'			=>	$this->input->post('contact_name'),
          'project_name'			=>	$this->input->post('project_name'),
          'invoice_date_entered'	=>	$this->input->post('invoice_date_entered'),
          'invoice_group_id'		=>	$this->input->post('invoice_group_id'),
          'invoice_is_quote'		=>	$this->input->post('invoice_is_quote')
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

            //'/invoice_template/' + invoice_template;

            redirect($email_url);
        }
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
    
    function get_invoices_JSON_invoice($params = NULL){
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
            //'tax_rates'			=>	$this->mdl_tax_rates->get(),
            //'client_groups'		=>	$this->mdl_client_groups->get(),
            'invoice_statuses' => $this->mdl_invoice_statuses->get(),
            //'users'             =>  $this->mdl_users->get(),
            'invoices' => $this->mdl_invoices->get_no_joins_invoice($is_quote, $limit, $offset)
        );


        echo json_encode($data);
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
            //'tax_rates'			=>	$this->mdl_tax_rates->get(),
            //'client_groups'		=>	$this->mdl_client_groups->get(),
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

}

?>
