<?php
(defined('BASEPATH')) OR exit('No direct script access allowed');
class Order_Mailer extends Admin_Controller {
    
    function __construct() {
        parent::__construct();
        $this->load->model(
                array(
                    'users/mdl_users',
                    'orders/mdl_orders',
                    'addresses/mdl_addresses',
                    'templates/mdl_templates',
                    'mdl_mailer'
                )
        );
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '16000M');
    }
    
    function form() {
        if ($this->input->post('btn_cancel')) {
            redirect('orders');
        }
        $order_id = uri_assoc('order_id', 4);
        if (!$order_id) {
            redirect($this->session->userdata('last_index'));
        }
        $params = array(
            'where' => array(
                'mcb_orders.order_id' => $order_id
            )
        );
        $order = $this->mdl_orders->get($params);
        if (!$this->mdl_mailer->validate_order_email()) {
            if (!$_POST) {
                $email_subject = ($order->project_id ? $order->project_name . ' - ' : '');
                $email_subject .= $this->lang->line('order');
                $email_subject .= ' #' . $order->order_number;
                if ($order->contact_id) {
                    $this->mdl_orders->set_form_value('email_to_name', $order->contact_name);
                    $this->mdl_orders->set_form_value('email_to_email', $order->contact_email_address);
                } else {
                    $this->mdl_orders->set_form_value('email_to_name', $order->client_name);
                    $this->mdl_orders->set_form_value('email_to_email', $order->client_email_address);
                }
                $email_from_name = $order->from_first_name . ' ' . $order->from_last_name;
                $email_body = "Purchase Order is attached for the above mentioned project \n\n";
                $email_body .= $email_from_name . "\n";
                $email_body .= $order->from_company_name . "\n";
                $email_body .= 'M: ' . $order->from_mobile_number . "\n";
                $this->mdl_orders->set_form_value('email_from_name', $email_from_name);
                $this->mdl_orders->set_form_value('email_from_email', $order->from_email_address);
                $this->mdl_orders->set_form_value('email_subject', $email_subject);
                $this->mdl_orders->set_form_value('email_body', $email_body);
                $this->mdl_orders->set_form_value('order_template', uri_assoc('order_template', 4));
                $email_bcc_litesource = 'orders@litesource.com.au';
                $this->mdl_orders->set_form_value('email_bcc', $email_bcc_litesource);
            }
            $data = array(
                'templates' => $this->mdl_templates->get('orders')
            );
            $this->load->view('order_mailer', $data);
        } else {
            $address = $this->mdl_addresses->get_by_id($order->order_address_id);
            $order_items = $this->mdl_orders->get_order_items($order_id);
            $order_template = $this->input->post('order_template');
            //$from_email = $this->input->post('email_from_email');
            $from_email = 'orders@litesource.com.au';
            $from_name = 'LiTEsource Orders';
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
                $email_body = ' ';
            }
            $this->mdl_mailer->email_order($order, $order_items, $address, $order_template, $from_email, $from_name, $to, $subject, $email_body, $email_cc, $email_bcc, $reply_to);
            //record in history
            $data = array(
                'user_id' => $this->session->userdata('user_id'),
                'order_id' => $order_id,
                'created_date' => date('Y-m-d H:i:s'),
                'order_history_data' => 'Order Email Sent.'
            );
            $this->db->insert('mcb_order_history', $data);
            redirect($this->session->userdata('last_index'));
        }
    }

}

?>