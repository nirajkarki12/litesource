<?php

(defined('BASEPATH')) OR exit('No direct script access allowed');

class Delivery_Dockets extends Admin_Controller {

    function __construct() {

        parent::__construct();

        $this->load->model('mdl_delivery_dockets');
        $this->load->model('invoices/mdl_invoices');
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


        $default_order_by = 'FROM_UNIXTIME(docket_date_entered) DESC, docket_number DESC';

        switch ($order_by) {

            case 'invoice':
                $params['order_by'] = 'invoice_number DESC, ';
                break;
            case 'client':
                $params['order_by'] = 'client_name,';
                break;
            case 'project':
                $params['order_by'] = 'project_name, ';
                break;
            case 'docket_number':
                $params['order_by'] = 'docket_number DESC, ';
                break;
            default:
                $params['order_by'] = '';
        }

        $params['order_by'] .= $default_order_by;

        $data = array(
            'dockets' => $this->mdl_delivery_dockets->get($params),
            'sort_links' => TRUE,
            'order_by' => $params['order_by']
        );

        $this->load->view('index', $data);
    }

    function delete() {

        $docket_id = uri_assoc('docket_id');

        if ($docket_id) {

            $this->mdl_delivery_dockets->delete($docket_id);
        }

        redirect($this->session->userdata('last_index'));
    }

    function edit() {

        $tab_index = ($this->session->flashdata('tab_index')) ? $this->session->flashdata('tab_index') : 0;

        $this->_post_handler();

        $this->redir->set_last_index();

        $this->load->helper('form');

        $docket_id = uri_assoc('docket_id');

        $this->load->model(
                array(
                    'clients/mdl_clients',
                    'clients/mdl_contacts',
                    'addresses/mdl_addresses',
                    'projects/mdl_projects',
                //'users/mdl_users'
                )
        );

        if (!$_POST AND $docket_id) {
            $params = array(
                'where' => array(
                    'mcb_delivery_dockets.docket_id' => $docket_id
                )
            );
            $docket = $this->mdl_delivery_dockets->get($params);
            if($docket == NULL){
                $docket = $this->mdl_delivery_dockets->get_Row('mcb_delivery_dockets', array('docket_id'=>$docket_id));
            }
        }
        
        if (!isset($docket)) {
            redirect('dashboard/record_not_found');
        }
        $this->load->helper('text');
        $this->mdl_addresses->prep_validation($docket->docket_address_id);
        //echo '<pre>'; print_r($docket->invoice_id); exit;
        $data = array(
            'docket' => $docket,
            'docket_items' => $this->mdl_delivery_dockets->get_docket_items($docket_id),
            'address' => $this->mdl_addresses->get_by_id($docket->docket_address_id),
            'tab_index' => $tab_index,
            'first_docket' => $this->mdl_delivery_dockets->check_if_first_docket($docket_id,$docket->invoice_id),
            'docket_payments' => $this->common_model->query_as_array('SELECT * FROM mcb_delivery_docket_payment WHERE docket_id = "'.$docket_id.'" ORDER BY id DESC'),
        );
        
//        echo "<pre>";
//        print_r($data);
//        die;
        
        $this->load->view('delivery_docket_edit', $data);
    }

    function get($params = NULL) {

        return $this->mdl_delivery_dockets->get($params);
    }

    function get_docket_items_JSON() {

        $docket_id = $this->input->post('docket_id');

        $data = array(
            'docket_items' => $this->mdl_delivery_dockets->get_docket_items($docket_id)
        );

        echo json_encode($data);
    }

    function update_docket_item() {

        $docket_id = $this->input->post('docket_id');
        $docket_item = json_decode($this->input->post('docket_item'));

        $data = array(
            'docket_item' => $this->mdl_delivery_dockets->update_docket_item($docket_id, $docket_item),
        );


        echo json_encode($data);
    }

    function generate_pdf() {

        $docket_id = uri_assoc('docket_id');

        $this->load->library('lib_output');

        $this->lib_output->pdf($docket_id, uri_assoc('docket_template'));
    }

    function generate_html() {

        $docket_id = uri_assoc('docket_id');

        $this->load->library('lib_output');


        $this->lib_output->html($docket_id, uri_assoc('docket_template'));
    }

    function _post_handler() {

        if ($this->input->post('btn_submit_address')) {

            $this->session->set_flashdata('tab_index', 1);
            $this->load->model('addresses/mdl_addresses');

            if ($this->mdl_addresses->validate()) {

                $this->mdl_addresses->save();

                redirect($this->session->userdata('last_index'));
            }

            //redirect('addresses/form');
        } elseif ($this->input->post('btn_submit_options_general')) {

            $this->mdl_delivery_dockets->save();

            redirect($this->session->userdata('last_index'));
        } elseif ($this->input->post('btn_cancel')) {

            redirect($this->session->userdata('last_index'));
        } elseif ($this->input->post('btn_download_pdf')) {


            $download_url = 'delivery_dockets/generate_pdf/docket_id/' . uri_assoc('docket_id');

            redirect($download_url);
        } elseif ($this->input->post('btn_download_pick_list_pdf')) {


            $download_url = 'delivery_dockets/generate_pdf/docket_id/' . uri_assoc('docket_id') . '/docket_template/pick_list';

            redirect($download_url);
        }
    }

    public function generatedocketinvoice() {
        //error_reporting(E_ALL); ini_set('display_errors', 1);
        
        
        $docket_id = uri_assoc('docket_id');
        $invoice_id = $this->mdl_delivery_dockets->getInvoiceId($docket_id);
        if (!$invoice_id) {
            redirect($this->session->userdata('last_index'));
        }
        $data = array();
        $params = array(
            'where' => array(
                'mcb_invoices.invoice_id' => $invoice_id
            ),
            'get_invoice_items' => TRUE,
            'get_invoice_payments' => TRUE,
            'get_invoice_tags' => TRUE
        );
        $this->load->model('invoices/mdl_invoices');
        $data['invoice'] = $this->mdl_invoices->get($params);
        
        $invoice_template = 'default_docket_invoice';
        
        $this->load->library('lib_output');
        $data['docket'] = $this->lib_output->get_data($docket_id, $invoice_template);
        $data['bank_detail'] = json_decode($this->mdl_delivery_dockets->get_row('mcb_settings', array('setting_key'=>'banking_details'))->setting_value);
        $data['contacts'] = $this->common_model->get_all_as_object('mcb_contacts', array('client_id'=>$data['invoice']->client_id));
        
//        echo "<pre>";
//        print_r($data);
//        die;
        
        //filtering out the docket items which are 0 quantity
        $data['docket']['docket_items'] = $this->removeZeroQtyInventoryItems($data['docket']['docket_items']);
        
        $html = $this->load->view('invoices/invoice_templates/default_docket_invoice', $data, TRUE);
        //$this->load->view('invoices/invoice_templates/default_docket_invoice', $data);
        //exit;
        
//        echo $html;
//        exit;
        
        
        $filename = 'Invoice_' . $data['invoice']->invoice_number . '_' . $data['docket']['docket']->docket_number;
        $full_filename = 'uploads/temp/' . $filename . '.pdf';
        $this->load->helper($this->mdl_mcb_data->setting('pdf_plugin'));
        
        pdf_create($html, $filename, TRUE);
    }
    
    private function removeZeroQtyInventoryItems($items){
        $fin = array();
        if(sizeof($items) > 0){
            foreach($items as $item){
                
                if(  ($item->item_name == '') && ($item->item_description == '')  ){
                    $item->docket_item_qty = '';
                    $item->item_price = '';
                    $item->item_type = '';
                    $fin[] = $item;
                }else if((double)$item->docket_item_qty != 0){
                    $fin[] = $item;
                }
            }
        }
        return $fin;
    }

    public function senddocketinvoice() {
        error_reporting(E_ALL); ini_set('display_errors', 1);
        $docket_id = uri_assoc('docket_id');
        
        $invoice_id = $this->mdl_delivery_dockets->getInvoiceId($docket_id);        
        if (!$invoice_id) {
            redirect($this->session->userdata('last_index'));
        }
        
        if ($this->input->post('btn_cancel')) {

            redirect('invoices');
        }

        $params = array(
            'where' => array(
                'mcb_invoices.invoice_id' => $invoice_id
            ),
            'get_invoice_items' => TRUE,
            'get_invoice_payments' => TRUE,
            'get_invoice_tags' => TRUE
        );
        
        $this->load->model('invoices/mdl_invoices');
        $invoice_template = 'default_docket_invoice';
        $invoice = $this->mdl_invoices->get($params);
        $this->load->library('lib_output');
        $docket = $this->lib_output->get_data($docket_id, $invoice_template);
        
        $docket['docket_items'] = $this->removeZeroQtyInventoryItems($docket['docket_items']);
        
        $this->load->model('mailer/mdl_mailer');
        
        
        if (!$this->mdl_mailer->validate_invoice_email()) {
            if (!$_POST) {
                
                $email_subject = ($invoice->project_id ? $invoice->project_name . ' - ' : '');
                $email_subject .= (!$invoice->invoice_is_quote) ? $this->lang->line('invoice') : $this->lang->line('quotation');
                $email_subject .= ' #' . $invoice->invoice_number.'.'.$docket['docket']->docket_number;

                $email_body = (!$invoice->invoice_is_quote) ? $this->lang->line('invoice') : $this->lang->line('quotation');
                $email_body .= " is attached for the above mentioned project\n\n";

                $email_from_name = $invoice->from_first_name . ' ' . $invoice->from_last_name;

                $email_body .= $email_from_name . "\n";
                $email_body .= $invoice->from_company_name . "\n";
                $email_body .= 'M: ' . $invoice->from_mobile_number . "\n";
                        
                if ($invoice->contact_id) {

                    $this->mdl_invoices->set_form_value('email_to_name', $invoice->contact_name);
                    $this->mdl_invoices->set_form_value('email_to_email', $invoice->contact_email_address);
                } else {
                    $this->mdl_invoices->set_form_value('email_to_name', $invoice->client_name);
                    $this->mdl_invoices->set_form_value('email_to_email', $invoice->client_email_address);
                }

                $this->mdl_invoices->set_form_value('email_from_name', $email_from_name);
                $this->mdl_invoices->set_form_value('email_from_email', $invoice->from_email_address);
                $this->mdl_invoices->set_form_value('email_subject', $email_subject);
                $this->mdl_invoices->set_form_value('email_body', $email_body);
                $this->mdl_invoices->set_form_value('invoice_template', $invoice_template);

                $email_bcc_litesource = (($invoice->invoice_is_quote) ? 'quotes' : 'invoices') . '@litesource.com.au';

                $this->mdl_invoices->set_form_value('email_bcc', $email_bcc_litesource);
            }

            $data = array(
                'docket' => $docket,
                'invoice' => $invoice,
                'templates' => array($invoice_template)
            );
            $data['contacts'] = $this->common_model->get_all_as_object('mcb_contacts', array('client_id'=>$data['invoice']->client_id));
            
            $this->load->view('mailer/docket_invoice_mailer', $data);
        } else {

            $update_due_date = $this->input->post('update_invoice_due_date');

            $from_email = (($invoice->invoice_is_quote) ? 'quotes' : 'invoices') . '@litesource.com.au';
            $from_name = (($invoice->invoice_is_quote) ? 'LiTEsource Quotes' : 'LiTEsource Invoices');

            //$from_email = $this->input->post('email_from_email');
            //$from_name = $this->input->post('email_from_name');
            
            
            $to_name = $this->input->post('email_to_name');
            $to_email = $this->input->post('email_to_email');
            $to = array($to_email, $to_name);
            $subject = $this->input->post('email_subject');
            $email_body = $this->input->post('email_body');
            $email_cc = $this->input->post('email_cc');
            $email_bcc = $this->input->post('email_bcc');
            $reply_to_email = $this->input->post('email_from_email');
            $reply_to_name = $this->input->post('email_from_name');
            $reply_to = array($reply_to_email, $reply_to_name);
            
            if (!$email_body) {
                $email_body = 'See attached';
            }
            
            $email_to_contact = '';
            if($contact = $this->input->post('default_to_contact') != ''){
                $email_to_contact = $this->input->post('default_to_contact');
            }
            $this->mdl_mailer->email_docket_invoice($invoice, $invoice_template, $update_due_date, $from_email, $from_name, $to, $subject, $email_body, $email_cc, $email_bcc, $reply_to,$docket, $email_to_contact);
            $this->mdl_invoices->getInvoiceStatusId($invoice_id);
            redirect($this->session->userdata('last_index'));
        }
    }
    
//    
//    public function finalizedelivery() {
//        
//        $docket_id = uri_assoc('docket_id');
//        $docData = $this->mdl_delivery_dockets->get_Row('mcb_delivery_dockets',array('docket_id'=>$docket_id));
//        $invoice_id = $docData->invoice_id;
//        $this->mdl_delivery_dockets->update('mcb_delivery_dockets',array('docket_delivery_status'=>'1'),array('docket_id'=>$docket_id));
//        
//        $delivery_docket_items1 = $this->mdl_delivery_dockets->get_Where('mcb_delivery_docket_items',array('docket_id'=>$docket_id));
//        $this->load->helper('mcb_app');
//        foreach ($delivery_docket_items1 as $dta) {   
//            
//            $invoice_items1 = $this->mdl_delivery_dockets->get_Row('mcb_invoice_items',array('invoice_item_id'=>$dta['invoice_item_id']));
//            $product_id = $invoice_items1->product_id;          
//            $products_inventory1 = $this->mdl_delivery_dockets->get_Where('mcb_products_inventory',array('product_id'=>$product_id));
//            foreach ($products_inventory1 as $prodct_inmentry) {
//                
//                $quqntity = $prodct_inmentry['inventory_qty']*$dta['docket_item_qty'];
//                
//                $inventory_item1 = $this->mdl_delivery_dockets->get_Row('mcb_inventory_item',array('inventory_id'=>$prodct_inmentry['inventory_id']));
//                $inventory_item1->qty;
//                $qty = $inventory_item1->qty - $quqntity;
//                $this->mdl_delivery_dockets->update('mcb_inventory_item',array('qty'=>$qty),array('inventory_id'=>$prodct_inmentry['inventory_id'])); 
//                
//                udpate_open_order_qty($prodct_inmentry['inventory_id']);
//            }               
//        }
//        $this->session->set_flashdata('custom_success', 'Successfully Delevered.');
//        redirect($this->session->userdata('last_index'));
//        
//    }
//    
//    
//    
//    public function canceldelivery() {
//        
//        
//        $docket_id = uri_assoc('docket_id');
//        
//        $this->mdl_delivery_dockets->update('mcb_delivery_dockets',array('docket_delivery_status'=>'0'),array('docket_id'=>$docket_id));
//        
//        $docData = $this->mdl_delivery_dockets->get_Row('mcb_delivery_dockets',array('docket_id'=>$docket_id));
//        $invoice_id = $docData->invoice_id;
//        
//        $delivery_docket_items1 = $this->mdl_delivery_dockets->get_Where('mcb_delivery_docket_items',array('docket_id'=>$docket_id));
//        $this->load->helper('mcb_app');
//        foreach ($delivery_docket_items1 as $dta) {   
//            
//            $invoice_items1 = $this->mdl_delivery_dockets->get_Row('mcb_invoice_items',array('invoice_item_id'=>$dta['invoice_item_id']));
//            $product_id = $invoice_items1->product_id;          
//            $products_inventory1 = $this->mdl_delivery_dockets->get_Where('mcb_products_inventory',array('product_id'=>$product_id));
//            foreach ($products_inventory1 as $prodct_inmentry) {
//                
//                $quqntity = $prodct_inmentry['inventory_qty']*$dta['docket_item_qty'];
//                
//                $inventory_item1 = $this->mdl_delivery_dockets->get_Row('mcb_inventory_item',array('inventory_id'=>$prodct_inmentry['inventory_id']));
//                $inventory_item1->qty;
//                $qty = $inventory_item1->qty + $quqntity;
//                $this->mdl_delivery_dockets->update('mcb_inventory_item',array('qty'=>$qty),array('inventory_id'=>$prodct_inmentry['inventory_id']));  
//                
//                udpate_open_order_qty($prodct_inmentry['inventory_id']);
//            }
//        }
//        $this->session->set_flashdata('custom_success', 'Delevery Cancelled Successfully.');
//        redirect($this->session->userdata('last_index'));
//    }
//    
    
    
    
    
    public function finalizedelivery() {
        
        $docket_id = uri_assoc('docket_id');
        $docData = $this->mdl_delivery_dockets->get_Row('mcb_delivery_dockets',array('docket_id'=>$docket_id));
        $invoice_id = $docData->invoice_id;
        $invoice_number = $this->mdl_delivery_dockets->get_Row('mcb_invoices',array('invoice_id'=>$invoice_id))->invoice_number;
        
        $this->mdl_delivery_dockets->update('mcb_delivery_dockets',array('docket_delivery_status'=>'1'),array('docket_id'=>$docket_id));
        $this->mdl_invoices->getInvoiceStatusId($invoice_id);
        $delivery_docket_items1 = $this->mdl_delivery_dockets->get_Where('mcb_delivery_docket_items',array('docket_id'=>$docket_id));
        foreach ($delivery_docket_items1 as $dta) {   
            
            $invoice_items1 = $this->mdl_delivery_dockets->get_Row('mcb_invoice_items',array('invoice_item_id'=>$dta['invoice_item_id']));
            $product_id = $invoice_items1->product_id;     
            $item_length = $invoice_items1->item_length;
            $products_inventory1 = $this->mdl_delivery_dockets->get_Where('mcb_products_inventory',array('product_id'=>$product_id));
            $product_name = $this->mdl_delivery_dockets->get_Row('mcb_products',array('product_id'=>$product_id))->product_name;
            foreach ($products_inventory1 as $prodct_inmentry) {
                // $quqntity = $prodct_inmentry['inventory_qty']*$dta['docket_item_qty'];
                $quqntity = $prodct_inmentry['inventory_qty']*$dta['docket_item_qty'];
                $inventory_item1 = $this->mdl_delivery_dockets->get_Row('mcb_inventory_item',array('inventory_id'=>$prodct_inmentry['inventory_id']));
                if( (($inventory_item1->use_length) == '1') && ($item_length > '0')  ){
                    $quqntity = $quqntity * $item_length;
                    if($item_length == '1'){
                        $product_name = str_replace('{mm}', '-Per-Metre', $product_name);
                    }else{
                        $product_name = str_replace('{mm}', '-'.($item_length)*(1000).'mm', $product_name);
                    }
                }
                $inventory_item1->qty;
                $qty = $inventory_item1->qty - $quqntity;
                $opn_order_qty = $inventory_item1->open_order_qty + $quqntity;
                $this->mdl_delivery_dockets->update('mcb_inventory_item',array('qty'=>$qty, 'open_order_qty'=>$opn_order_qty),array('inventory_id'=>$prodct_inmentry['inventory_id']));   
                
                //going to put history if the delivery is finalized
                // -ve $quqntity
                $data = array(
                    'history_id' => '0',
                    'inventory_id' => $prodct_inmentry['inventory_id'],
                    'history_qty' => '-'.$quqntity,
                    'notes' => 'Finalised Delivery <a href="'.site_url('delivery_dockets/edit/docket_id/'.$docket_id).'">Docket '.$invoice_number.'.'.$docData->docket_number.' ('.$product_name.')</a>',
                    'user_id' => $this->session->userdata('user_id'),
                    'created_at' => date('Y-m-d H:i:s')
                );
                
                $this->db->insert('mcb_inventory_history',$data);
            }               
        }
        $this->session->set_flashdata('custom_success', 'Successfully delivered.');
        $this->session->set_flashdata('tab_index', '5');
        redirect(site_url('invoices/edit/invoice_id/'.$invoice_id));    
    }
    
    public function canceldelivery() {
        
        $docket_id = uri_assoc('docket_id');
        
        $docData = $this->mdl_delivery_dockets->get_Row('mcb_delivery_dockets',array('docket_id'=>$docket_id));
        $invoice_id = $docData->invoice_id;
        $invoice_number = $this->mdl_delivery_dockets->get_Row('mcb_invoices',array('invoice_id'=>$invoice_id))->invoice_number;
        
        $this->mdl_delivery_dockets->update('mcb_delivery_dockets',array('docket_delivery_status'=>'0'),array('docket_id'=>$docket_id));
        $this->mdl_invoices->getInvoiceStatusId($invoice_id);
        $delivery_docket_items1 = $this->mdl_delivery_dockets->get_Where('mcb_delivery_docket_items',array('docket_id'=>$docket_id));
        foreach ($delivery_docket_items1 as $dta) {
            
            $invoice_items1 = $this->mdl_delivery_dockets->get_Row('mcb_invoice_items',array('invoice_item_id'=>$dta['invoice_item_id']));
            $product_id = $invoice_items1->product_id;
            $item_length = $invoice_items1->item_length;
            $products_inventory1 = $this->mdl_delivery_dockets->get_Where('mcb_products_inventory',array('product_id'=>$product_id));
            $product_name = $this->mdl_delivery_dockets->get_Row('mcb_products',array('product_id'=>$product_id))->product_name;
            
            foreach ($products_inventory1 as $prodct_inmentry) {
                
                // $quqntity = $prodct_inmentry['inventory_qty']*$dta['docket_item_qty'];
                $quqntity = $prodct_inmentry['inventory_qty']*$dta['docket_item_qty'];
                
                $inventory_item1 = $this->mdl_delivery_dockets->get_Row('mcb_inventory_item',array('inventory_id'=>$prodct_inmentry['inventory_id']));
                if( (($inventory_item1->use_length) == '1') && ($item_length > '0') ){
                    $quqntity = $quqntity*$item_length;
                    if($item_length == '1'){
                        $product_name = str_replace('{mm}', '-Per-Metre', $product_name);
                    }else{
                        $product_name = str_replace('{mm}', '-'.($item_length)*(1000).'mm', $product_name);
                    }
                    
                }
                $inventory_item1->qty;
                $qty = $inventory_item1->qty + $quqntity;
                $opn_order_qty = $inventory_item1->open_order_qty - $quqntity;
                $this->mdl_delivery_dockets->update('mcb_inventory_item',array('qty'=>$qty, 'open_order_qty'=>$opn_order_qty),array('inventory_id'=>$prodct_inmentry['inventory_id']));   
                
                //going to put history if the delivery is cancelled
                // +ve $quqntity
                $data = array(
                    'history_id' => '0',
                    'inventory_id' => $prodct_inmentry['inventory_id'],
                    'history_qty' => $quqntity,
                    'notes' => 'Canceled Delivery <a href="'.site_url('delivery_dockets/edit/docket_id/'.$docket_id).'">Docket '.$invoice_number.'.'.$docData->docket_number.' ('.$product_name.')</a>',
                    'user_id' => $this->session->userdata('user_id'),
                    'created_at' => date('Y-m-d H:i:s')
                );
                $this->db->insert('mcb_inventory_history',$data);
            }              
        }
        $this->session->set_flashdata('custom_success', 'Delevery Cancelled Successfully.');
        $this->session->set_flashdata('tab_index', '5');
        redirect(site_url('invoices/edit/invoice_id/'.$invoice_id));
    }
    
    
    function do_paid() {
        $docket_id = uri_assoc('docket_id');
        $this->mdl_delivery_dockets->update('mcb_delivery_dockets',array('paid_status'=>'1'),array('docket_id'=>$docket_id));
        $this->docketPriceAdd();
        $invoice_id = $this->mdl_invoices->get_row('mcb_delivery_dockets', array('docket_id'=>$docket_id))->invoice_id;     
        $this->mdl_invoices->getInvoiceStatusId($invoice_id);
        // redirect($this->session->userdata('last_index'));
        $this->session->set_flashdata('tab_index', 5);
        redirect(site_url('invoices/edit/invoice_id/'.$invoice_id));    
    }
    
    function do_unpaid() {
        $docket_id = uri_assoc('docket_id');
        $this->mdl_delivery_dockets->update('mcb_delivery_dockets',array('paid_status'=>'0'),array('docket_id'=>$docket_id));
        $this->docketPriceAdd();
        $invoice_id = $this->mdl_invoices->get_row('mcb_delivery_dockets', array('docket_id'=>$docket_id))->invoice_id;        
        $this->mdl_invoices->getInvoiceStatusId($invoice_id);
        // redirect($this->session->userdata('last_index'));
        $this->session->set_flashdata('tab_index', 5);
        redirect(site_url('invoices/edit/invoice_id/'.$invoice_id));
    }
    
    function do_sent($docket_id, $clent_id = NULL) {
        
        $this->mdl_delivery_dockets->update('mcb_delivery_dockets',array('invoice_sent'=>'1'),array('docket_id'=>$docket_id));
        $invoice_id = $this->mdl_invoices->get_row('mcb_delivery_dockets', array('docket_id'=>$docket_id))->invoice_id;        
        $this->mdl_invoices->getInvoiceStatusId($invoice_id);
        
        if($clent_id != NULL){
            $this->session->set_flashdata('tab_index', 4);
            redirect(site_url('clients/details/client_id/'.$clent_id));
        }
        $this->session->set_flashdata('tab_index', 5);
        redirect(site_url('invoices/edit/invoice_id/'.$invoice_id));
    }
    function do_unsent($docket_id, $clent_id = NULL) {
        
        $this->mdl_delivery_dockets->update('mcb_delivery_dockets',array('invoice_sent'=>'0'),array('docket_id'=>$docket_id));
        $invoice_id = $this->mdl_invoices->get_row('mcb_delivery_dockets', array('docket_id'=>$docket_id))->invoice_id;        
        $this->mdl_invoices->getInvoiceStatusId($invoice_id);
        
        if($clent_id != NULL){
            $this->session->set_flashdata('tab_index', 4);
            redirect(site_url('clients/details/client_id/'.$clent_id));
        }
        $this->session->set_flashdata('tab_index', 5);
        redirect(site_url('invoices/edit/invoice_id/'.$invoice_id));
    }
    
    function docketPriceAdd() {
        
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
    
    function add_docket_amount_old() {
        
        $docket_id = $this->input->post('docket_id');
        $amount = $this->input->post('amount');
        $note = $this->input->post('note');
        $user_name = $this->input->post('user_name');
        $username = $this->mdl_delivery_dockets->get_Row('mcb_users',array('user_id'=>$this->session->userdata('user_id')))->username;
        $data = array(
            'docket_id' => $docket_id,
            'note' => $note,
            'amount' => $amount,
            'time' => time(),
            'user_name'=> $username
        );
        
        if( ($amount == '0') || ($amount == '0.0') || ($amount == '0.00') || ($amount == '') || ($amount == NULL) ){
            $this->session->set_flashdata('custom_error', 'Please set valid amount.');
        }else{
            
            $ddDetail = $this->mdl_delivery_dockets->get_Row('mcb_delivery_dockets', array('docket_id'=>$docket_id));
            if($ddDetail != NULL){
                $dd_new_amount = ($ddDetail->price_with_tax) - ($amount);
                $this->mdl_delivery_dockets->add_docket_amount($data, $dd_new_amount);
            }else{
                $this->session->set_flashdata('custom_error', 'Sorry Delivery Docket Does not exits.');
            }
        }
        $this->session->set_flashdata('tab_index', 3);
        redirect(site_url('delivery_dockets/edit/docket_id/'.$docket_id));
    }  
    
    function add_docket_amount() {
        
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
            $ddDetail = $this->common_model->get_row('mcb_delivery_dockets', array('docket_id'=>$docket_id));
            if(($ddDetail != NULL) || ($ddDetail !== FALSE)){
                $this->mdl_delivery_dockets->add_docket_amount($data);
            }else{
                $this->session->set_flashdata('custom_error', 'Delivery docket not found.');
            }
        }
        $this->session->set_flashdata('tab_index', 3);
        redirect(site_url('delivery_dockets/edit/docket_id/'.$docket_id));
    }  
    
    function create_Due_Date_One_Time_Run() {
        $allDelDoc = $this->mdl_delivery_dockets->get_all('mcb_delivery_dockets');
        foreach ($allDelDoc as $value) {
            $inv = $this->mdl_delivery_dockets->get_Row('mcb_invoices',array('invoice_id'=>$value->invoice_id));
            
            $uDate = $value->docket_date_entered + ( 24 * 60 * 60 *30);
            $this->mdl_delivery_dockets->update('mcb_delivery_dockets', 
                    array(
                        'docket_due_date'=>$uDate,
                        'invoice_date'=>$inv->invoice_date_entered
                    ), 
                    array('docket_id'=>$value->docket_id));
        }
        echo "success";
        exit;
    }
    
}

?>