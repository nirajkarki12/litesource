<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class Order_Widgets extends Admin_Controller {

	function generate_dialog() {

		$this->load->model('templates/mdl_templates');

		$data = array(
			'templates'					=>	$this->mdl_templates->get('orders'),
			'default_order_template'	=>	'default' //$this->mdl_mcb_data->setting('default_order_template')
		);

		$this->load->view('orders/jquery_order_generate', $data);

	}

}

?>