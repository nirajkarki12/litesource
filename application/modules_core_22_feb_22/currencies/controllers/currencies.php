<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class Currencies extends Admin_Controller {

	function __construct() {

		parent::__construct();

		$this->_post_handler();

		$this->load->model('mdl_currencies');

	}

	function index() {

		$this->redir->set_last_index();
		
		$params = array(
			'paginate'	=>	TRUE,
			'limit'		=>	$this->mdl_mcb_data->setting('results_per_page'),
			'page'		=>	uri_assoc('page')
		);


		$data = array(
			'currencies' =>	$this->mdl_currencies->get($params),
		);

		$this->load->view('index', $data);

	}

	function form() {

		if (!$this->mdl_currencies->validate()) {

			$this->load->helper('form');

			if (!$_POST AND uri_assoc('currency_id')) {

				$this->mdl_currencies->prep_validation(uri_assoc('currency_id'));

			}

			$this->load->view('form');

		}

		else {

			$this->mdl_currencies->save($this->mdl_currencies->db_array(), uri_assoc('currency_id'));

			$this->redir->redirect('currencies');

		}

	}

	function delete() {

		if (uri_assoc('currency_id')) {

			$this->mdl_currencies->delete(uri_assoc('currency_id'));

		}

		$this->redir->redirect('currencies');

	}

	function _post_handler() {

		if ($this->input->post('btn_add')) {

			redirect('currencies/form');

		}

		elseif ($this->input->post('btn_cancel')) {

			redirect('currencies/index');

		}

	}

}

?>