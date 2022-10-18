<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class Suppliers extends Admin_Controller {

	function __construct() {

		parent::__construct();

		$this->_post_handler();

		$this->load->model('mdl_suppliers');

	}

	function index() {

		$this->load->helper('text');

		$this->redir->set_last_index();

		$params = array(
			'paginate'	=>	TRUE,
			'limit'		=>	$this->mdl_mcb_data->setting('results_per_page'),
			'page'		=>	uri_assoc('page')
		);

		if (uri_assoc('order_by') == 'name') {

			$params['order_by'] = 'supplier_short_name';

		}

		else {

			$params['order_by'] = 'supplier_sort_index';

		}
		
		$data = array(
			'suppliers'	=>	$this->mdl_suppliers->get($params),
			'order_by' => $params['order_by']
		);

		$this->load->view('index', $data);

	}

	function form() {

//		$this->load->module('mcb_language');

		if ($this->mdl_suppliers->validate()) {
			
			$this->mdl_suppliers->save();

			redirect($this->session->userdata('last_index'));

		}

		else {

			$this->load->helper('form');

			if (!$_POST AND uri_assoc('supplier_id')) {

				$this->mdl_suppliers->prep_validation(uri_assoc('supplier_id'));

			}

			// prepare list of active clients since one of these will be used for supplier details
			$this->load->model('clients/mdl_clients');

			$this->load->helper('text');

			$data = array(
				'clients' => $this->mdl_clients->get_active()
			);

			
			$this->load->view('form', $data);

		}

	}

	function details() {

		$this->redir->set_last_index();


		$this->load->model(
			array(
			'products/mdl_products',
			)
		);

		$supplier_params = array(
			'where'	=>	array(
				'mcb_suppliers.supplier_id'	=>	uri_assoc('supplier_id')
			)
		);


		$product_params = array(
			'where'	=>	array(
				'mcb_products.supplier_id'	=>	uri_assoc('supplier_id')
			),
			'set_supplier'	=>	TRUE
		);

		$supplier = $this->mdl_suppliers->get($supplier_params);

		$products = $this->mdl_products->get($product_params);
		
		if ($this->session->flashdata('tab_index')) {

			$tab_index = $this->session->flashdata('tab_index');

		}

		else {

			$tab_index = 0;

		}

		$data = array(
			'supplier'	=>	$supplier,
			'products'	=>	$products,
			'tab_index'	=>	$tab_index
		);

		$this->load->view('details', $data);

	}
	
	function delete() {

		if (uri_assoc('supplier_id')) {

			$this->mdl_suppliers->delete(uri_assoc('supplier_id'));

		}

		$this->redir->redirect('suppliers');

	}

	function get($params = NULL) {

		return $this->mdl_suppliers->get($params);

	}

	function _post_handler() {

		if ($this->input->post('btn_add_supplier')) {

			redirect('suppliers/form');

		}

		elseif ($this->input->post('btn_cancel')) {

			redirect($this->session->userdata('last_index'));

		}

	}


}

?>