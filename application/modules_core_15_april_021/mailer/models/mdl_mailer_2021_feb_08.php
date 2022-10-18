<?php

(defined('BASEPATH')) OR exit('No direct script access allowed');

class Mdl_Mailer extends MY_Model {

    public function validate_invoice_email() {

        $this->form_validation->set_rules('invoice_template', $this->lang->line('invoice_template'), 'required');
        $this->form_validation->set_rules('email_from_name', $this->lang->line('from_name'), 'required');
        $this->form_validation->set_rules('email_from_email', $this->lang->line('from_email'), 'required|valid_email');
        $this->form_validation->set_rules('email_to_email', $this->lang->line('to'), 'required|valid_email');
        $this->form_validation->set_rules('email_subject', $this->lang->line('subject'), 'required|max_length[100]');
        $this->form_validation->set_rules('email_body', $this->lang->line('body'));

        return parent::validate();
    }

    public function validate_order_email() {

        $this->form_validation->set_rules('order_template', $this->lang->line('order_template'), 'required');
        $this->form_validation->set_rules('email_from_name', $this->lang->line('from_name'), 'required');
        $this->form_validation->set_rules('email_from_email', $this->lang->line('from_email'), 'required|valid_email');
        $this->form_validation->set_rules('email_to_email', $this->lang->line('to'), 'required|valid_email');
        $this->form_validation->set_rules('email_subject', $this->lang->line('subject'), 'required|max_length[100]');
        $this->form_validation->set_rules('email_body', $this->lang->line('body'));

        return parent::validate();
    }

    public function validate_invoice_overdue() {

        $this->form_validation->set_rules('invoice_template', $this->lang->line('invoice_template'), 'required');
        $this->form_validation->set_rules('email_from_name', $this->lang->line('from_name'), 'required');
        $this->form_validation->set_rules('email_from_email', $this->lang->line('from_email'), 'required|valid_email');
        $this->form_validation->set_rules('email_subject', $this->lang->line('subject'), 'required|max_length[100]');
        $this->form_validation->set_rules('email_body', $this->lang->line('body'));

        return parent::validate();
    }

    public function validate_payment_email() {

        $this->form_validation->set_rules('template', $this->lang->line('template'), 'required');
        $this->form_validation->set_rules('email_from_name', $this->lang->line('from_name'), 'required');
        $this->form_validation->set_rules('email_from_email', $this->lang->line('from_email'), 'required|valid_email');
        $this->form_validation->set_rules('email_to', $this->lang->line('to'), 'required|valid_email');
        $this->form_validation->set_rules('email_subject', $this->lang->line('subject'), 'required|max_length[100]');
        $this->form_validation->set_rules('email_body', $this->lang->line('body'));

        return parent::validate();
    }

    public function email_docket_invoice($invoice, $invoice_template, $update_due_date, $from_email, $from_name, $to, $subject, $email_body, $email_cc = NULL, $email_bcc = NULL, $reply_to = NULL,$docket, $default_contact = '') {
        $data = array();

        $this->load->model('invoices/mdl_invoices');

        $data['invoice'] = $invoice;
        $data['contacts'] = $this->common_model->get_all_as_object('mcb_contacts', array('client_id'=>$data['invoice']->client_id));
        $data['email_default_contact'] = $default_contact;
        
        $invoice_template = 'default_docket_invoice';

        $this->load->library('lib_output');
        $data['docket'] = $docket;
        $data['bank_detail'] = json_decode($this->mdl_mcb_data->get_row('mcb_settings', array('setting_key'=>'banking_details'))->setting_value);
        
        
        $html = $this->load->view('invoices/invoice_templates/default_docket_invoice', $data, TRUE);
        //$this->load->view('invoices/invoice_templates/default_docket_invoice', $data);
        //exit;
        
//        echo '<pre>';
//         print_r($data);
//        echo $html;
//        exit;
        
        
        $filename = 'Invoice_' . $data['invoice']->invoice_number . '.' . $data['docket']['docket']->docket_number;
        $full_filename = 'uploads/temp/' . $filename . '.pdf';

        $this->load->helper($this->mdl_mcb_data->setting('pdf_plugin'));

        pdf_create($html, $filename, FALSE);

        $this->load->helper('mailer/phpmailer');

        $email_body = nl2br($email_body);

        if (!$email_body) {

            $email_body = ' ';
        }
        
        if($_SERVER['SERVER_NAME'] == '188.166.225.109'){
            $this->mdl_invoices->delete_invoice_file($filename . '.pdf');
            $this->udpateDocketInvoiceSent($docket['docket']->docket_id);
        } else {
            
            if (phpmail_send( array($from_email, $from_name), $to, $subject, $email_body, $full_filename, $email_cc, $email_bcc, $reply_to)) {
                $this->mdl_invoices->delete_invoice_file($filename . '.pdf');
                $quote_or_invoice = $invoice->invoice_is_quote ? $this->lang->line('emailed_quotation') : $this->lang->line('emailed_invoice');
                $this->udpateDocketInvoiceSent($docket['docket']->docket_id);
                //need this to change to docket invoice history by creating a table
    //            $this->mdl_invoices->save_invoice_history($invoice->invoice_id, $this->session->userdata('user_id'), $quote_or_invoice . ' to ' . $to[0]);
    //            $db_array = array(
    //                'invoice_status_id' => 2,
    //            );
    //            $this->mdl_invoices->save_invoice_db_array($invoice->invoice_id, $db_array);
            } else {
                //die('could not sent..');
                //update invoice sent status
            }
        }
        
        
    }
    
    private function udpateDocketInvoiceSent($docketid){
        $data = array(
            'invoice_sent' => '1'
        );
        $this->db->where('docket_id',$docketid);
        $this->db->update('mcb_delivery_dockets',$data);
        
        
        // ===================== this is for update invoice status===========================
        $docketid = $docket_id;
        $docData = $this->get_Row('mcb_delivery_dockets',array('docket_id'=>$docket_id));
        $invoice_id = $docData->invoice_id;
        $this->update('mcb_delivery_dockets',array('docket_delivery_status'=>'1'),array('docket_id'=>$docket_id));
        
        $docData1 = $this->get_Where('mcb_delivery_dockets',array('invoice_id'=>$invoice_id));
        $ds = 0;
        $is = 0;
        foreach ($docData1 as $dd) {
            $is += $dd['invoice_sent'];
            $ds += $dd['docket_delivery_status'];
        }
        if( (count($docData1) == $ds) && (count($docData1) == $is) ){
            $this->update('mcb_invoices',array('invoice_status_id'=>2),array('invoice_id'=>$invoice_id));
        } else {
            $this->update('mcb_invoices',array('invoice_status_id'=>1),array('invoice_id'=>$invoice_id));
        }
    }

    public function email_invoice($invoice, $invoice_template, $update_due_date, $from_email, $from_name, $to, $subject, $email_body, $email_cc = NULL, $email_bcc = NULL, $reply_to = NULL) {

        $this->load->library('invoices/lib_output');

        if ($update_due_date) {
            $this->mdl_invoices->reset_invoice_due_date($invoice->invoice_id);
        }

        $data = $this->lib_output->get_data($invoice->invoice_id, $invoice_template);
        $data['bank_detail'] = json_decode($this->mdl_mcb_data->get_row('mcb_settings', array('setting_key'=>'banking_details'))->setting_value);
        
        $data['break_page_false'] = TRUE;
        $html = $this->load->view('invoices/invoice_templates/' . $data['invoice_template'], $data, TRUE);
        
        $data['terms_and_condition'] = $this->get_Row('mcb_settings',array('setting_key'=>'terms_conditions'));
        $html_terms_condition = $this->load->view('invoices/invoice_templates/litesource_terms', $data, TRUE);
        
        $filename = $data['filename'];
        $full_filename = 'uploads/temp/' . $filename . '.pdf';

        $this->load->helper($this->mdl_mcb_data->setting('pdf_plugin'));
        pdf_create_for_quote($html, $html_terms_condition, $filename, FALSE);

        $this->load->helper('mailer/phpmailer');

        $email_body = nl2br($email_body);

        if (!$email_body) {

            $email_body = ' ';
        }

        
        ///// for localhost
        if( isset($_SERVER['HTTP_HOST'])  && ($_SERVER['HTTP_HOST'] == 'localhost') ){
            $this->mdl_invoices->delete_invoice_file($filename . '.pdf');
            $quote_or_invoice = $invoice->invoice_is_quote ? $this->lang->line('emailed_quotation') : $this->lang->line('emailed_invoice');
            $this->mdl_invoices->save_invoice_history($invoice->invoice_id, $this->session->userdata('user_id'), $quote_or_invoice . ' to ' . $to[0]);
            $db_array = array(
                'invoice_status_id' => 2,
            );
            if(($invoice->invoice_is_quote == '1') && ($invoice->invoice_date_emailed < '2') ){
                $db_array['invoice_date_emailed']= time();
            }
            $this->mdl_invoices->save_invoice_db_array($invoice->invoice_id, $db_array);
        }else{
        
            ///// for live
            if (phpmail_send(
                            array($from_email, $from_name), $to, $subject, $email_body, $full_filename, $email_cc, $email_bcc, $reply_to)) {

                $this->mdl_invoices->delete_invoice_file($filename . '.pdf');

                $quote_or_invoice = $invoice->invoice_is_quote ? $this->lang->line('emailed_quotation') : $this->lang->line('emailed_invoice');

                $this->mdl_invoices->save_invoice_history($invoice->invoice_id, $this->session->userdata('user_id'), $quote_or_invoice . ' to ' . $to[0]);

                $db_array = array(
                    'invoice_status_id' => 2,
                );
                if (($invoice->invoice_is_quote == '1') && ($invoice->invoice_date_emailed < '2')) {
                    $db_array['invoice_date_emailed'] = time();
                }
                $this->mdl_invoices->save_invoice_db_array($invoice->invoice_id, $db_array);
            }
        }
    }

    public function email_payment_receipt($invoice, $template, $from_email, $from_name, $to, $subject, $email_body, $receipt_as_body, $email_cc = NULL, $email_bcc = NULL) {

        $filename = 'receipt_' . $invoice->invoice_number;

        $full_filename = 'uploads/temp/' . $filename . '.pdf';

        $this->load->helper($this->mdl_mcb_data->setting('pdf_plugin'));

        $invoice_payments = $this->mdl_invoices->get_invoice_payments($invoice->invoice_id);

        $data = array(
            'invoice' => $invoice,
            'invoice_payments' => $invoice_payments
        );

        $html = $this->load->view('payments/receipt_templates/' . $template, $data, TRUE);

        pdf_create($html, $filename, FALSE);

        $this->load->helper('mailer/phpmailer');

        $email_body = ($receipt_as_body) ? nl2br($email_body) . $html : $email_body;

        if (!$email_body) {

            $email_body = ' ';
        }

        phpmail_send(
                array($from_email, $from_name), $to, $subject, $email_body, $full_filename, $email_cc, $email_bcc);

        $this->mdl_invoices->delete_invoice_file($filename . '.pdf');
    }

    public function email_order($order, $order_items, $address, $order_template, $from_email, $from_name, $to, $subject, $email_body, $email_cc = NULL, $email_bcc = NULL, $reply_to = NULL) {


        $this->load->library('orders/lib_output');

        $data = $this->lib_output->get_data($order->order_id, $order_template);

        $html = $this->load->view('orders/order_templates/' . $data['order_template'], $data, TRUE);

        $filename = $data['filename'];

        $full_filename = 'uploads/temp/' . $filename . '.pdf';

        $html = $this->load->view('orders/order_templates/' . $data['order_template'], $data, TRUE);

        $this->load->helper($this->mdl_mcb_data->setting('pdf_plugin'));
        pdf_create($html, $filename, FALSE);

        $this->load->helper('mailer/phpmailer');

        $email_body = nl2br($email_body);

        if (!$email_body) {

            $email_body = ' ';
        }

        if (phpmail_send(
                        array($from_email, $from_name), $to, $subject, $email_body, $full_filename, $email_cc, $email_bcc, $reply_to)) {

            $db_array = array(
                'order_status_id' => 2,
            );
            if(($order->order_date_emailed < '2')){
                $db_array['order_date_emailed']= time();
            }
            
            $this->mdl_orders->save_order_db_array($order->order_id, $db_array);
        }
    }
    
    public function get_Row($tbl_name, $id_edt) {

        $this->db->where($id_edt);
        $q = $this->db->get($tbl_name);
        $Res = $q->row();
        return $Res;
    }

    public function get_Where($tbl_name, $id_edt) {

        $this->db->where($id_edt);
        $q = $this->db->get($tbl_name);
        $Res = $q->result_array();
        return $Res;
    }

    public function update($tbl_name, $data, $condition) {

        $this->load->database();

        $this->db->where($condition);
        $this->db->update($tbl_name, $data);
    }

    public function get_all($table_name, $condition = '', $order = '', $limit = '') {
        if ($order != '') {
            $this->db->order_by($order);
        }
        if ($limit != '') {
            $this->db->limit($limit);
        }
        if ($condition != '') {
            $this->db->where($condition);
        }
        $q = $this->db->get($table_name);
        $Res = $q->result();
        return $Res;
    }

    function query($qry) {

        $q = $this->db->query($qry);
        $Res = $q->result();
        return $Res;
    }

    function query_array($qry) {

        $q = $this->db->query($qry);
        $Res = $q->result_array();
        return $Res;
    }

}

?>