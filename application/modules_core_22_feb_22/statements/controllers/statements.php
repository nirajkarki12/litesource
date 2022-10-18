<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class Statements extends Admin_Controller {

    function __construct() {

        parent::__construct();

        $this->load->model('mdl_statements');
    }

	function index() {

    }

    function generate_pdf() {
        $client_id = uri_assoc('client_id');

        $this->load->library('lib_output');

        $this->lib_output->pdf($client_id, uri_assoc('statement_template'), $this->session->userdata('user_id'));

    }

    function generate_html() {
        
        $client_id = uri_assoc('client_id');
        $this->load->library('lib_output');
        $this->lib_output->html($client_id, uri_assoc('statement_template'));
    }

}
