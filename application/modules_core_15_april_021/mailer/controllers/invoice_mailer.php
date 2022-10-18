<?php

(defined('BASEPATH')) OR exit('No direct script access allowed');

class Invoice_Mailer extends Admin_Controller {

    function __construct() {

        parent::__construct();

        $this->load->model(
                array(
                    'users/mdl_users',
                    'invoices/mdl_invoices',
                    'templates/mdl_templates',
                    'mdl_mailer'
                )
        );
        
        ini_set('max_execution_time', 0);
        ini_set('memory_limit','16000M');
    }

    function form() {

        if ($this->input->post('btn_cancel')) {

            redirect('invoices');
        }

        $invoice_id = uri_assoc('invoice_id', 4);

        if (!$invoice_id) {

            redirect($this->session->userdata('last_index'));
        }

        $params = array(
            'where' => array(
                'mcb_invoices.invoice_id' => $invoice_id
            ),
            'get_invoice_items' => TRUE,
            'get_invoice_payments' => TRUE,
            'get_invoice_tags' => TRUE
        );

        $invoice = $this->mdl_invoices->get($params);

        if (!$this->mdl_mailer->validate_invoice_email()) {
            if (!$_POST) {
                $invoice_template = uri_assoc('invoice_template', 4);

                if (!$invoice_template) {

                    if ($invoice->invoice_is_quote) {

                        $invoice_template = $this->mdl_mcb_data->setting('default_quote_template');
                    } else {

                        $invoice_template = $this->mdl_mcb_data->setting('default_invoice_template');
                    }
                }

                $email_subject = ($invoice->project_id ? $invoice->project_name . ' - ' : '');
                $email_subject .= (!$invoice->invoice_is_quote) ? $this->lang->line('invoice') : $this->lang->line('quotation');
                $email_subject .= ' #' . $invoice->invoice_number;

                $email_body = (!$invoice->invoice_is_quote) ? $this->lang->line('invoice') : $this->lang->line('quotation');
                $email_body .= " is attached for the above mentioned project\n\n";

                $email_from_name = $invoice->from_first_name . ' ' . $invoice->from_last_name;

                $email_body .= $email_from_name . "\n";
                //$email_body .= $invoice->from_company_name . "\n";
                $email_body .= 'M: ' . $invoice->from_mobile_number . "\n";
                
                //------ new addition start-----
                $email_body .= "\n";
                //--- from_company_name static---
                $email_body .= 'LiTEsource and Controls'."\n";
                $email_body .= 'P: 02 9669 6976 (NSW)'. "\n";
                $email_body .= 'P: 03 8393 9361 (VIC)'. "\n";
                $email_body .= 'P: 07 3114 3466 (QLD)'. "\n";
                //------ new addition end-----
                
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
                'invoice' => $invoice,
                'templates' => $this->mdl_templates->get('invoices')
            );

            $this->load->view('invoice_mailer', $data);
        } else {

            $invoice_template = $this->input->post('invoice_template');

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


            $this->mdl_mailer->email_invoice($invoice, $invoice_template, $update_due_date, $from_email, $from_name, $to, $subject, $email_body, $email_cc, $email_bcc, $reply_to);

            redirect($this->session->userdata('last_index'));
        }
    }
    
    
    function invoice_quote_mail_form() {
        
        if ($this->input->post('btn_cancel')) {
            redirect('invoices');
        }

        $invoice_id = uri_assoc('invoice_id', 4);
        if (!$invoice_id) {
            redirect(site_url());
        }

        $params = array(
            'where' => array(
                'mcb_invoices.invoice_id' => $invoice_id
            ),
            'get_invoice_items' => TRUE,
            'get_invoice_payments' => TRUE,
            'get_invoice_tags' => TRUE
        );

        $invoice = $this->mdl_invoices->get($params);

        if (!$this->mdl_mailer->validate_invoice_email()) {
            if (!$_POST) {
                $invoice_template = uri_assoc('invoice_template', 4);

                if (!$invoice_template) {

                    if ($invoice->invoice_is_quote) {

                        $invoice_template = $this->mdl_mcb_data->setting('default_quote_template');
                    } else {

                        $invoice_template = $this->mdl_mcb_data->setting('default_invoice_template');
                    }
                }

                $email_subject = ($invoice->project_id ? $invoice->project_name . ' - ' : '');
                $email_subject .= (!$invoice->invoice_is_quote) ? $this->lang->line('invoice') : $this->lang->line('quotation');
                $email_subject .= ' #' . $invoice->invoice_number;
                $email_body = (!$invoice->invoice_is_quote) ? $this->lang->line('invoice') : $this->lang->line('quotation');
                $email_body .= " is attached for the above mentioned project\n\n";
                $email_from_name = $invoice->from_first_name . ' ' . $invoice->from_last_name;
                $email_body .= $email_from_name . "\n";
                //$email_body .= $invoice->from_company_name . "\n";
                $email_body .= 'M: ' . $invoice->from_mobile_number . "\n";
                //------ new addition start-----
                $email_body .= "\n";
                //--- from_company_name static---
                $email_body .= 'LiTEsource and Controls'."\n";
                $email_body .= 'P: 02 9669 6976 (NSW)'. "\n";
                $email_body .= 'P: 03 8393 9361 (VIC)'. "\n";
                $email_body .= 'P: 07 3114 3466 (QLD)'. "\n";
                //------ new addition end-----
                
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
                'invoice' => $invoice,
                'templates' => $this->mdl_templates->get('invoices')
            );

            $this->load->view('invoice_mailer', $data);
        } else {

            $invoice_template = $this->input->post('invoice_template');

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
            $this->mdl_mailer->email_invoice($invoice, $invoice_template, $update_due_date, $from_email, $from_name, $to, $subject, $email_body, $email_cc, $email_bcc, $reply_to);
            
            $red_url = $this->session->userdata('last_index');
            if(isset($_GET['type'])){
                $red_url = site_url("{$_GET['type']}/edit/invoice_id/".$invoice_id);
            }
            redirect($red_url);
        }  
    }
    
    
    function overdue() {

        if (!$this->mdl_mailer->validate_invoice_overdue()) {

            if (!$_POST) {

                $user = $this->mdl_users->get_by_id($this->session->userdata('user_id'));

                $this->mdl_mailer->set_form_value('email_from_name', $user->first_name . ' ' . $user->last_name);
                $this->mdl_mailer->set_form_value('email_from_email', $user->email_address);
                $this->mdl_mailer->set_form_value('email_subject', $this->lang->line('overdue_invoice_reminder'));
                $this->mdl_mailer->set_form_value('email_body', '');
            }

            $data = array(
                'invoices' => $this->mdl_invoices->get_overdue(),
                'templates' => $this->mdl_templates->get('invoices')
            );

            $this->load->view('invoice_overdue', $data);
        } else {

            if ($this->input->post('invoice_ids')) {

                $invoice_template = $this->input->post('invoice_template');
                $from_email = $this->input->post('email_from_email');
                $from_name = $this->input->post('email_from_name');
                $subject = $this->input->post('email_subject');
                $email_body = $this->input->post('email_body');
                $email_cc = $this->input->post('email_cc');
                $email_bcc = $this->input->post('email_bcc');
                $invoice_as_body = $this->input->post('invoice_as_body');
                $email_addresses = $this->input->post('email_address');

                foreach ($this->input->post('invoice_ids') as $invoice_id) {

                    $params = array(
                        'where' => array(
                            'mcb_invoices.invoice_id' => $invoice_id
                        ),
                        'get_invoice_items' => TRUE,
                        'get_invoice_tax_rates' => TRUE,
                        'get_invoice_payments' => TRUE,
                        'get_invoice_tags' => TRUE
                    );

                    $invoice = $this->mdl_invoices->get($params);

                    $to = $email_addresses[$invoice_id];

                    $this->mdl_mailer->email_invoice($invoice, $invoice_template, $from_email, $from_name, $to, $subject, $email_body, $invoice_as_body, $email_cc, $email_bcc);
                }
            }

            redirect($this->session->userdata('last_index'));
        }
    }

}

?>