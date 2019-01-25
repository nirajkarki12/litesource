<?php

(defined('BASEPATH')) OR exit('No direct script access allowed');

class Lib_output {

    public $CI;

    function __construct() {

        $this->CI = & get_instance();

        $this->CI->load->model('users/mdl_users');
    }

    function get_pdf_filename($docket, $template) {

        return $template . '_' . $docket->docket_number . '.pdf';
    }

    function get_data($docket_id, $template) {

        $params = array(
            'where' => array(
                'mcb_delivery_dockets.docket_id' => $docket_id
            )
        );
        $docket = $this->CI->mdl_delivery_dockets->get($params);        
        if($docket == NULL){
            $docket = $this->CI->mdl_delivery_dockets->get_Row('mcb_delivery_dockets',array('docket_id'=>$docket_id));
        }
        $docket->price_with_tax = $docket->price_with_tax - $this->CI->common_model->query_as_object("SELECT ROUND((SUM(mcb_delivery_docket_payment.amount_entered)),2) AS with_tax_owing_amount FROM `mcb_delivery_docket_payment` WHERE `docket_id`='$docket_id'")[0]->with_tax_owing_amount;
//        echo '<pre>';
//        print_r($docket);
//        die;
        if (!$template) {
            $template = 'docket';
        }
        $info_user_id = $this->CI->mdl_mcb_data->setting('default_pdf_info_user_id');
        $data = array(
            'docket' => $docket,
            'docket_items' => $this->CI->mdl_delivery_dockets->get_docket_items($docket_id),
            'address' => $this->CI->mdl_delivery_dockets->get_docket_address($docket->docket_address_id),
            'user' => $this->CI->mdl_users->get_by_id($info_user_id),
            'template' => $template,
            'filename' => $this->get_pdf_filename($docket, $template)
        );
        return $data;
    }

    function html($docket_id, $template) {


        $data = $this->get_data($docket_id, $template);

        $this->CI->load->view('delivery_dockets/templates/' . $data['template'], $data);
    }

    function pdf($docket_id, $template) {

        $this->CI->load->helper($this->CI->mdl_mcb_data->setting('pdf_plugin'));

        $data = $this->get_data($docket_id, $template);

        $html = $this->CI->load->view('delivery_dockets/templates/' . $data['template'], $data, TRUE);

        pdf_create($html, $data['filename'], TRUE);
    }

}

?>