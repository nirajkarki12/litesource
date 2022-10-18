<?php

(defined('BASEPATH')) OR exit('No direct script access allowed');

class Clients extends Admin_Controller {

    function __construct() {

        parent::__construct();

        $this->_post_handler();

        $this->load->model('mdl_clients');
       // $this->load->model('statements/mdl_statements');
    }

    function index() {


        $this->load->helper('text');

        $this->redir->set_last_index();

        $params = array(
            'paginate' => TRUE,
            'limit' => $this->mdl_mcb_data->setting('results_per_page'),
            'page' => uri_assoc('page')
        );

        if (uri_assoc('order_by') == 'client_id') {

            $params['order_by'] = 'client_id';
        } elseif (uri_assoc('order_by') == 'balance') {

            $params['order_by'] = 'client_total_balance DESC';
        } else {

            $params['order_by'] = 'client_name';
        }

        $data = array(
            'clients' => $this->mdl_clients->get($params)
        );
        
        if($_GET['download'] == 'yes'){
                    

        // output headers so that the file is downloaded rather than displayed
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=data.csv');

// create a file pointer connected to the output stream
        $output = fopen('php://output', 'w');

        // output the column headings
        fputcsv($output, array('Name', 'Group Name','Currency','Is Supplier','Total Due'));



// loop over the rows, outputting them
        foreach ($data['clients'] as $con){
            $temp = array(
                $con->client_name,
                $con->client_group_name,
                $con->currency_name,
                ($con->client_is_supplier == '1')?'Yes':'',
                $con->total_due,
            );
            fputcsv($output, $temp);
        }
                
            exit;
        }
        
        $this->load->view('index', $data);
    }

    function form() {

//		$this->load->model('mcb_data/mdl_mcb_client_data');
//
//		if (uri_assoc('client_id')) {
//
//			$this->mdl_mcb_client_data->set_session_data(uri_assoc('client_id'));
//
//		}
//
//		$this->load->module('mcb_language');

        if ($this->mdl_clients->validate()) {
            
            $this->mdl_clients->save();

            redirect($this->session->userdata('last_index'));
        } else {

            $this->load->helper('form');

            if (!$_POST AND uri_assoc('client_id')) {

                $this->mdl_clients->prep_validation(uri_assoc('client_id'));
            }

            $this->load->model('client_groups/mdl_client_groups');
            $this->load->model('currencies/mdl_currencies');
            $this->load->model('tax_rates/mdl_tax_rates');

            $data = array(
                'suppliers' => $this->mdl_clients->get_parent_suppliers(),
                'client_groups' => $this->mdl_client_groups->get(),
                'currencies' => $this->mdl_currencies->get(),
                'tax_rates' => $this->mdl_tax_rates->get(),
                'selected_tax_rate_id' => $this->mdl_mcb_data->setting('default_tax_rate_id')
            );

            $this->load->view('form', $data);
        }
    }

    function details() {
        $this->_post_handler();


        $this->load->helper('text');

        if ($this->mdl_clients->validate()) {
            
            //echo '<pre>'; print_r($this->input->post()); exit;
            
            $this->mdl_clients->save();

            redirect($this->session->userdata('last_index'));
        } else {

            $this->load->helper('form');

            if (!$_POST AND uri_assoc('client_id')) {

                $this->mdl_clients->prep_validation(uri_assoc('client_id'));
            }

            $this->load->model(
                    array(
                        'client_groups/mdl_client_groups',
                        'currencies/mdl_currencies',
                        'tax_rates/mdl_tax_rates',
                        'invoices/mdl_invoices',
                        'orders/mdl_orders',
                        'mdl_contacts',
                    )
            );

            $client_id = uri_assoc('client_id');

            $client_params = array(
                'where' => array(
                    'mcb_clients.client_id' => $client_id
                )
            );

            $contact_params = array(
                'where' => array(
                    'mcb_contacts.client_id' => $client_id
                )
            );

            $invoice_params = array(
                'where' => array(
                    'mcb_invoices.client_id' => $client_id,
                    'mcb_invoices.invoice_is_quote' => 0
                )
            );
            
            $docket_params = array(
                'where' => array(
                    'mcb_invoices.client_id' => $client_id,
                    'mcb_invoices.invoice_is_quote' => 0
                )
            );

            $quote_params = array(
                'where' => array(
                    'mcb_invoices.client_id' => $client_id,
                    'mcb_invoices.invoice_is_quote' => 1
                )
            );


            $order_params = array(
                'where' => array(
                    'mcb_orders.supplier_id' => $client_id
                )
            );

            /*
              if (!$this->session->userdata('global_admin')) {

              $invoice_params['where']['mcb_invoices.user_id'] = $this->session->userdata('user_id');

              }
             */
            $client = $this->mdl_clients->get($client_params);

            $contacts = $this->mdl_contacts->get($contact_params);

            $invoices = $this->mdl_invoices->get($invoice_params);
            
            $quotes = $this->mdl_invoices->get($quote_params);
            
            $dockets = $this->mdl_invoices->getDocket($docket_params);
            
            if ($client->client_is_supplier) {
                // $orders = $this->mdl_orders->get($order_params);
                $orders = $this->mdl_clients->get_client_order($client_id);
            } else {

                $orders = array();
            }

            if ($this->session->flashdata('tab_index')) {

                $tab_index = $this->session->flashdata('tab_index');
            } else {

                $tab_index = 0;
            }

            $data = array(
                'client' => $client,
                'contacts' => $contacts,
                'suppliers' => $this->mdl_clients->get_parent_suppliers(),
                'client_groups' => $this->mdl_client_groups->get(),
                'currencies' => $this->mdl_currencies->get(),
                'tax_rates' => $this->mdl_tax_rates->get(),
                'quotes' => $quotes,
                'invoices' => $invoices,
                'dockets' => $dockets,
                'orders' => $orders,
                'tab_index' => $tab_index,
                'selected_tax_rate_id' => $client->client_tax_rate_id
            );
//            echo '<pre>';
//            print_r($data);exit;

            $this->load->view('details', $data);
        }
    }

    function ajax_get_contacts() {
        $this->load->model('clients/mdl_contacts');

        $client_id = $this->input->post('client_id');

        $client = $this->mdl_clients->get_by_id($client_id);

        $data = array(
            'client_id' => $client_id,
            'client_group_id' => $client->client_group_id,
            'contacts' => $this->mdl_contacts->get_client_contacts($client_id)
        );
        echo json_encode($data);
    }

    
    function delete_docket($c_id, $p1 = '', $docket_id = '') {
        
        if ($docket_id) {
            $this->mdl_clients->delete_docket($docket_id);
        }
        redirect(site_url('clients/details/client_id/'.$c_id));
    }
    
    
    function delete() {

        if (uri_assoc('client_id')) {

            $this->mdl_clients->delete(uri_assoc('client_id'));
        }


        $this->redir->redirect('clients');
    }

    function get($params = NULL) {

        return $this->mdl_clients->get($params);
    }

    function get_active_suppliers_JSON() {

        $data = array(
            'suppliers' => $this->mdl_clients->get_active_suppliers()
        );

        echo json_encode($data);
    }

    function get_suppliers_JSON() {

        $data = array(
            'suppliers' => $this->mdl_clients->get_parent_suppliers()
        );

        echo json_encode($data);
    }

    function get_clients_JSON() {

        $params = array(
            'order_by' => "n",
        );


        $params['select'] = "
			mcb_clients.client_id id,
			mcb_clients.client_name n,
			mcb_client_groups.client_group_name gn,
			mcb_clients.client_tax_rate_id tid,
			mcb_currencies.currency_code cur,
			parent_suppliers.client_name pn,
			IFNULL(t.total_due, 0) t,
			mcb_clients.client_is_supplier cis";

        $params['where'] = "mcb_clients.client_active = '1'";
        $this->load->model('tax_rates/mdl_tax_rates');
        $cl = $this->mdl_clients->get($params);
        $this->load->model('statements/mdl_statements');
        foreach ($cl as $value) {
            $re = $value;
            $params1 = array(
                'where'	=>	array(
                    'mcb_invoices.client_id'	=>  $value->id,
                    'invoice_is_quote'          =>  0,
                    'invoice_status_id'      =>  2,

                )
            );
            $total = $this->mdl_statements->get_total($params1);
            $delivery_docket = $this->mdl_statements->get_delivery_for_Pdf($value->id);
            $sum = 0;
            foreach ($delivery_docket as $value) {
                $sum+= ($value->price_with_tax - $value->paid_amount);
            }
           $re->dp = $sum;
            //$re->dp = $total;
//            $re->idp = $this->mdl_clients->getInvoiceAmount($value->id);
            $clientArr[] = $re;
        }
        $data = array(
            'tax_rates' => $this->mdl_tax_rates->get(),
            'clients' => $clientArr
        );
        echo json_encode($data);

    }

    function _post_handler() {

        if ($this->input->post('btn_add_client')) {

            redirect('clients/form');
        } elseif ($this->input->post('btn_cancel')) {

            redirect($this->session->userdata('last_index'));
        } elseif ($this->input->post('btn_add_contact')) {

            redirect('clients/contacts/details/client_id/' . uri_assoc('client_id'));
        } elseif ($this->input->post('btn_delete_client')) {
            redirect('clients/contacts/deleteclient/client_id/' . uri_assoc('client_id'));
        } elseif ($this->input->post('btn_statement_download_pdf')) {

            redirect('statements/generate_pdf/client_id/' . uri_assoc('client_id') . '/statement_template/default_statement');
        }
    }

    function dropdown_options() {

        $this->load->helper('text');

        $data = array(
            'clients' => $this->mdl_clients->get()
        );

        $this->load->view('dropdown_options', $data);
    }

    public function export_contacts() {
        $sql = "select con.contact_name, con.email_address, cli.client_name as company from mcb_contacts as con left join mcb_clients as cli on con.client_id = cli.client_id group by con.email_address order by con.contact_name ASC";
        $q = $this->db->query($sql);
        $result = $q->result();

        // output headers so that the file is downloaded rather than displayed
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=data.csv');

// create a file pointer connected to the output stream
        $output = fopen('php://output', 'w');

        // output the column headings
        fputcsv($output, array('Client Name', 'Company','Email Address'));



// loop over the rows, outputting them
        foreach ($result as $con){
            $temp = array(
                $con->contact_name,
                $con->company,
                $con->email_address,
            );
            fputcsv($output, $temp);
        }
                
            exit;
    }
    
        public function export_clients() {
        $sql = "select con.contact_name, con.email_address, cli.client_name as company from mcb_contacts as con left join mcb_clients as cli on con.client_id = cli.client_id group by con.email_address order by con.contact_name ASC";
        $q = $this->db->query($sql);
        $result = $q->result();

        // output headers so that the file is downloaded rather than displayed
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=data.csv');

// create a file pointer connected to the output stream
        $output = fopen('php://output', 'w');

        // output the column headings
        fputcsv($output, array('Client Name', 'Company','Email Address'));



// loop over the rows, outputting them
        foreach ($result as $con){
            $temp = array(
                $con->contact_name,
                $con->company,
                $con->email_address,
            );
            fputcsv($output, $temp);
        }
                
            exit;
    }
    
    
    
    public function finalizedelivery($c_id, $para = '', $docket_id) {
        
//        $docket_id = uri_assoc('docket_id');
        
        $docData = $this->mdl_clients->get_Row('mcb_delivery_dockets',array('docket_id'=>$docket_id));
        $invoice_id = $docData->invoice_id;
        $this->mdl_clients->update('mcb_delivery_dockets',array('docket_delivery_status'=>'1'),array('docket_id'=>$docket_id));
        
        $delivery_docket_items1 = $this->mdl_clients->get_Where('mcb_delivery_docket_items',array('docket_id'=>$docket_id));
        
        foreach ($delivery_docket_items1 as $dta) {   
            
            $invoice_items1 = $this->mdl_clients->get_Row('mcb_invoice_items',array('invoice_item_id'=>$dta['invoice_item_id']));
            $product_id = $invoice_items1->product_id;          
            $products_inventory1 = $this->mdl_clients->get_Where('mcb_products_inventory',array('product_id'=>$product_id));
            foreach ($products_inventory1 as $prodct_inmentry) {
                
                $quqntity = $prodct_inmentry['inventory_qty']*$dta['docket_item_qty'];
                
                $inventory_item1 = $this->mdl_clients->get_Row('mcb_inventory_item',array('inventory_id'=>$prodct_inmentry['inventory_id']));
                $inventory_item1->qty;
                $qty = $inventory_item1->qty - $quqntity;
                $this->mdl_clients->update('mcb_inventory_item',array('qty'=>$qty),array('inventory_id'=>$prodct_inmentry['inventory_id']));   
                
                //going to put history if the delivery is finalized
                // -ve $quqntity
                $data = array(
                    'history_id' => '0',
                    'inventory_id' => $prodct_inmentry['inventory_id'],
                    'history_qty' => '-'.$quqntity,
                    'notes' => 'Finalise Delivery <a href="'.site_url('delivery_dockets/edit/docket_id/'.$docket_id).'">Docket #'.$docData->docket_number.'</a>',
                    'user_id' => $this->session->userdata('user_id'),
                    'created_at' => date('Y-m-d H:i:s')
                );
                $this->db->insert('mcb_inventory_history',$data);
            }               
        }
        $this->session->set_flashdata('custom_success', 'Successfully Delevered.');
//        redirect($this->session->userdata('last_index'));
        $this->session->set_flashdata('tab_index', '4');
        redirect(site_url('clients/details/client_id/'.$c_id));
    }
    
    public function canceldelivery($c_id, $para = '', $docket_id) {
        
        
//        $docket_id = uri_assoc('docket_id');
        
        $this->mdl_clients->update('mcb_delivery_dockets',array('docket_delivery_status'=>'0'),array('docket_id'=>$docket_id));
        
        $docData = $this->mdl_clients->get_Row('mcb_delivery_dockets',array('docket_id'=>$docket_id));
        $invoice_id = $docData->invoice_id;
        
        $delivery_docket_items1 = $this->mdl_clients->get_Where('mcb_delivery_docket_items',array('docket_id'=>$docket_id));
        
        foreach ($delivery_docket_items1 as $dta) {   
            
            $invoice_items1 = $this->mdl_clients->get_Row('mcb_invoice_items',array('invoice_item_id'=>$dta['invoice_item_id']));
            $product_id = $invoice_items1->product_id;          
            $products_inventory1 = $this->mdl_clients->get_Where('mcb_products_inventory',array('product_id'=>$product_id));
            foreach ($products_inventory1 as $prodct_inmentry) {
                
                $quqntity = $prodct_inmentry['inventory_qty']*$dta['docket_item_qty'];
                
                $inventory_item1 = $this->mdl_clients->get_Row('mcb_inventory_item',array('inventory_id'=>$prodct_inmentry['inventory_id']));
                $inventory_item1->qty;
                $qty = $inventory_item1->qty + $quqntity;
                $this->mdl_clients->update('mcb_inventory_item',array('qty'=>$qty),array('inventory_id'=>$prodct_inmentry['inventory_id']));   
                
                //going to put history if the delivery is cancelled
                // +ve $quqntity
                $data = array(
                    'history_id' => '0',
                    'inventory_id' => $prodct_inmentry['inventory_id'],
                    'history_qty' => $quqntity,
                    'notes' => 'Canceled Delivery From <a href="'.site_url('delivery_dockets/edit/docket_id/'.$docket_id).'">Docket #'.$docData->docket_number.'</a>',
                    'user_id' => $this->session->userdata('user_id'),
                    'created_at' => date('Y-m-d H:i:s')
                );
                $this->db->insert('mcb_inventory_history',$data);
            }              
        }
        $this->session->set_flashdata('custom_success', 'Delevery Cancelled Successfully.');
//        redirect($this->session->userdata('last_index'));
        $this->session->set_flashdata('tab_index', '4');
        redirect(site_url('clients/details/client_id/'.$c_id));
    }
    
    
    
    function do_paid() {
        $client_docket_id = uri_assoc('client_id');
        $client_docket = (explode("_",$client_docket_id));
        $client_id = $client_docket['0'];
        $docket_id = $client_docket['1'];
        
        $this->db->where(array('docket_id'=>$docket_id));
        $this->db->update('mcb_delivery_dockets', array('paid_status'=>'1'));
        $this->docketPriceAdd();
        
        $this->session->set_flashdata('tab_index', '4');
        redirect(site_url('clients/details/client_id/'.$client_id));
    }
    
    function do_unpaid() {
        $client_docket_id = uri_assoc('client_id');
        $client_docket = (explode("_",$client_docket_id));
        $client_id = $client_docket['0'];
        $docket_id = $client_docket['1'];
        
        $this->db->where(array('docket_id'=>$docket_id));
        $this->db->update('mcb_delivery_dockets', array('paid_status'=>'0'));
        $this->docketPriceAdd();
        
        $this->session->set_flashdata('tab_index', '4');
        redirect(site_url('clients/details/client_id/'.$client_id));
    }
    
    function docketPriceAdd() {
        $this->load->model('delivery_dockets/mdl_delivery_dockets');
        $allDelCocs = $this->mdl_delivery_dockets->get_Where('mcb_delivery_dockets',array('paid_status'=>'0'));
        foreach ($allDelCocs as $value) {
            
            $sql = 'SELECT di.invoice_item_id, di.docket_item_qty, di.docket_id, '
                    . 'ii.item_price, ii.invoice_id, '
                    . 'i.invoice_tax_rate_id, '
                    . 'tx.tax_rate_name, tx.tax_rate_percent, '
                    . '(ii.item_price*di.docket_item_qty) as total_price, '
                    . '((ii.item_price*di.docket_item_qty)+(((ii.item_price*di.docket_item_qty)*(tx.tax_rate_percent))/100 )) as total_price_with_tax '
                    . 'FROM (((mcb_delivery_docket_items as di '
                    . 'LEFT JOIN mcb_invoice_items as ii ON di.invoice_item_id = ii.invoice_item_id) '
                    . 'LEFT JOIN mcb_invoices as i ON ii.invoice_id = i.invoice_id) '
                    . 'LEFT JOIN mcb_tax_rates as tx ON i.invoice_tax_rate_id = tx.tax_rate_id) '
                    . 'WHERE di.docket_id = "'.$value['docket_id'].'" ';
            
            $r2 = $this->mdl_delivery_dockets->query_array($sql);
            $key = 'total_price_with_tax';
            $sum_with_tax = array_sum(array_column($r2,$key));    
            $this->mdl_delivery_dockets->update('mcb_delivery_dockets', array('price_with_tax'=>$sum_with_tax), array('docket_id'=>$value['docket_id']));
        }
        //echo "completed";
        //exit;
        return true;
        
    }
    
    function make_default_contact_email() {
        $client_id = $_GET['client_id'];
        $contact_id = $_GET['contact_id'];
        $this->common_model->update('mcb_contacts', array('is_default'=>'0'), array('client_id'=>$client_id));
        $this->common_model->update('mcb_contacts', array('is_default'=>'1'), array('contact_id'=>$contact_id));
        redirect($_GET['redirect_url']);
    }
    
    
    function remove_default_contact_email() {
        $client_id = $_GET['client_id'];
        $contact_id = $_GET['contact_id'];
        //$this->common_model->update('mcb_contacts', array('is_default'=>'0'), array('client_id'=>$client_id));
        $this->common_model->update('mcb_contacts', array('is_default'=>'0'), array('contact_id'=>$contact_id));
        redirect($_GET['redirect_url']);
    }
    
    function update_client_state() {
        $status = $this->common_model->update('mcb_clients', array('client_state'=> $_POST['client_state'] ), array('client_id'=> $_POST['client_id'] )); 
        $res_arr = array(
            'status'=>$status
        );
        echo json_encode($res_arr);
    }

}

