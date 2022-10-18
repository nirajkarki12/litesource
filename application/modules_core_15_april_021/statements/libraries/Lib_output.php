<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class Lib_output {

    public $CI;

    function __construct() {

        $this->CI =& get_instance();

        $this->CI->load->model('clients/mdl_clients');
        $this->CI->load->model('users/mdl_users');

    }

    function get_client($client_id) {

        $params = array(
            'where'	=>	array('mcb_clients.client_id'	=>  $client_id)
        );

        return $this->CI->mdl_clients->get($params);
    }

    function html($client_id, $template) {

        $params = array(
            'where'	=>	array(
                'mcb_invoices.client_id'	=>  $client_id,
                'invoice_is_quote'          =>  0,
                'invoice_status_id'         =>  2, //Emailed

            )
        );


        $statement = $this->CI->mdl_statements->get($params);


        //$info_user_id = $this->CI->mdl_mcb_data->setting('default_pdf_info_user_id');
        $info_user_id = $this->session->userdata('user_id');

        $data = array(
            'client'      =>    $this->get_client($client_id),
            'user'        =>	$this->CI->mdl_users->get_by_id($info_user_id),
            'statement'   =>	$statement,
            'date'        =>	time()
        );

        $this->CI->load->view('statements/statement_templates/' . $template, $data);

    }

    function pdf($client_id, $template, $user_id) {

        $this->CI->load->helper($this->CI->mdl_mcb_data->setting('pdf_plugin'));

        $params = array(
            'where'	=>	array(
                'mcb_invoices.client_id'	=>  $client_id,
                'invoice_is_quote'          =>  0,
                'invoice_status_id'      =>  2,

            )
        );


        //$info_user_id = $this->CI->mdl_mcb_data->setting('default_pdf_info_user_id');

        $statement = $this->CI->mdl_statements->get($params);
        $total_owed = $this->CI->mdl_statements->get_total($params);
        
        $deliveryDocket = $this->CI->mdl_statements->get_delivery_for_Pdf($client_id);
        $bank_detail = json_decode($this->CI->mdl_mcb_data->get_row('mcb_settings', array('setting_key'=>'banking_details'))->setting_value);
        
        $client = $this->get_client($client_id);
        $date = time();

        
        $data = array(
            'client'    =>  $client,
            'user'      =>  $this->CI->mdl_users->get_by_id($user_id),
            'statement' =>  $statement,
            'delivery_docket' => $deliveryDocket,
            'total_owed'=>  $total_owed,
            'date'      =>  $date,
            'bank_detail'      =>  $bank_detail,
        );

        $html = $this->CI->load->view('statements/statement_templates/' . $template, $data, TRUE);
        
        pdf_create($html, 'statement_' . strftime('%Y%m%d', $date) .'_' . $client->client_name, TRUE);

    }

}

?>