<?php

(defined('BASEPATH')) OR exit('No direct script access allowed');

class Lib_output {

    public $CI;

    function __construct() {

        $this->CI = & get_instance();
        $this->CI->load->model('users/mdl_users');
        //$this->CI->load->model('orders/mdl_orders');
    }
    
    function get_pdf_filename($order) {
        
        return $this->CI->lang->line('order') . '_' . $order->order_number;
    }
    
    function get_data($order_id, $template) {
        
        $params = array(
            'where' => array(
                'mcb_orders.order_id' => $order_id
            )
        );
        $order = $this->CI->mdl_orders->get($params);
        if($order->order_date_emailed > '1'){
            $order->order_date_entered = $order->order_date_emailed;
        }
        
        if (!$template) {
            $template = $this->CI->mdl_mcb_data->setting('default_order_template');
        }
        $info_user_id = $this->CI->mdl_mcb_data->setting('default_pdf_info_user_id');
        
        $data = array(
            'order' => $order,
            'order_items' => $this->CI->mdl_orders->get_order_items_list($order_id),
            'address' => $this->CI->mdl_orders->get_order_address($order->order_address_id),
            'user' => $this->CI->mdl_users->get_by_id($info_user_id),
            'order_template' => $template,
            'filename' => $this->get_pdf_filename($order)
        );
        return $data;
    }
    
    function html($order_id, $template) {
        $data = $this->get_data($order_id, $template);
        $this->CI->load->view('orders/order_templates/' . $data['order_template'], $data);
    }

    function pdf($order_id, $template) {
        $this->CI->load->helper($this->CI->mdl_mcb_data->setting('pdf_plugin'));
        $data = $this->get_data($order_id, $template);
        //echo '<pre>'; print_r($data); exit;
        $html = $this->CI->load->view('orders/order_templates/' . $data['order_template'], $data, TRUE);
        pdf_create($html, $data['filename'], TRUE);
    }

}

?>