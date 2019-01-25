<?php

(defined('BASEPATH')) OR exit('No direct script access allowed');

class Lib_output {

    public $CI;

    function __construct() {

        $this->CI = & get_instance();

        $this->CI->load->model('users/mdl_users');
    }

    function html($invoice_id, $invoice_template) {

        $data = $this->get_data($invoice_id, $invoice_template);

        $this->CI->load->view('invoices/invoice_templates/' . $data['invoice_template'], $data);
    }

    function pdf($invoice_id, $invoice_template) {

        $data = $this->get_data($invoice_id, $invoice_template);
        $invoice = $data['invoice'];
        
        $data['bank_detail'] = json_decode($this->CI->mdl_mcb_data->get_row('mcb_settings', array('setting_key'=>'banking_details'))->setting_value);
        
        $data['break_page_false'] = TRUE;
        $html = $this->CI->load->view('invoices/invoice_templates/' . $data['invoice_template'], $data, TRUE);
        
        $data['terms_and_condition'] = $this->CI->mdl_mcb_data->get_row('mcb_settings',array('setting_key'=>'terms_conditions'));
        
        $html_terms_condition = $this->CI->load->view('invoices/invoice_templates/litesource_terms', $data, TRUE);
        
        
        
       // echo "<pre>";
       // print_r($data);
       // die;
        
        $this->CI->load->helper($this->CI->mdl_mcb_data->setting('pdf_plugin'));
        // pdf_create($html, $data['filename'], TRUE);
        pdf_create_for_quote($html, $html_terms_condition, $data['filename'], TRUE);
    }

    function get_pdf_filename($invoice) {

        return ((!$invoice->invoice_is_quote) ? $this->CI->lang->line('invoice') : $this->CI->lang->line('quotation')) . '_' . $invoice->invoice_number;
    }

    function get_data($invoice_id, $invoice_template = NULL) {


        $invoice = $this->get_invoice($invoice_id);

        if (!$invoice_template) {

            if ($invoice->invoice_is_quote) {

                $invoice_template = $this->CI->mdl_mcb_data->setting('default_quote_template');
            } else {

                $invoice_template = $this->CI->mdl_mcb_data->setting('default_invoice_template');
            }
        }

        $info_user_id = $this->CI->mdl_mcb_data->setting('default_pdf_info_user_id');
        $data = array(
            'invoice' => $invoice,
            'user' => $this->CI->mdl_users->get_by_id($info_user_id),
            'invoice_template' => $invoice_template,
            'filename' => $this->get_pdf_filename($invoice)
        );

        return $data;
    }

    function get_data_invoice_docket($invoice_id, $invoice_template = NULL) {


        $invoice = $this->get_invoice($invoice_id);

        if (!$invoice_template) {

            if ($invoice->invoice_is_quote) {

                $invoice_template = $this->CI->mdl_mcb_data->setting('default_quote_template');
            } else {

                $invoice_template = $this->CI->mdl_mcb_data->setting('default_invoice_template');
            }
        }

        $info_user_id = $this->CI->mdl_mcb_data->setting('default_pdf_info_user_id');
        $data = array(
            'invoice' => $invoice,
            'user' => $this->CI->mdl_users->get_by_id($info_user_id),
            'invoice_template' => $invoice_template,
            'filename' => $this->get_pdf_filename($invoice)
        );

        return $data;
    }

    function get_invoice($invoice_id) {

        $params = array(
            'where' => array(
                'mcb_invoices.invoice_id' => $invoice_id
            ),
            'get_invoice_payments' => FALSE,
            'get_invoice_items' => TRUE,
            'get_invoice_tags' => TRUE
        );

        return $this->CI->mdl_invoices->get($params);
    }
    
    
    
    public function get_row($table_name, $condition) {
        $this->db->where($condition);
            return $this->db->get($table_name)->row();
    }
    
    public function query($qry){
        
        $res = $this->db->query($qry);
            return $res->result();
    }
    
    public function get_where($table_name, $condition = '', $order = '', $limit = '') {
        
        if ($order != '') {
            $this->db->order_by($order);
        }
        if ($limit != '') {
            $this->db->limit($limit);
        }
        if ($condition != '') {
            $this->db->where($condition);
        }
        return $this->db->get($table_name)->result();      
    }
    
    public function insert($tableName, $data) {
        $this->db->insert($tableName, $data);
        $rid = $this->db->insert_id();
        if($rid > 0){
            return $rid;
        } else {
            return FALSE;
        }
    }
    
    public function update($tbl_name, $condition, $data) {
        
        $this->db->where($condition);
        if ($this->db->update($tbl_name, $data)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    
    
}

?>