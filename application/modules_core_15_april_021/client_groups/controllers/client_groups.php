<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class Client_Groups extends Admin_Controller {

	function __construct() {

		parent::__construct();

		$this->_post_handler();

		$this->load->model('mdl_client_groups');

	}

	function index() {

		$this->redir->set_last_index();
		
		$params = array(
			'paginate'	=>	TRUE,
			'limit'		=>	$this->mdl_mcb_data->setting('results_per_page'),
			'page'		=>	uri_assoc('page')
		);


		$data = array(
			'client_groups' =>	$this->mdl_client_groups->get($params),
		);

		$this->load->view('index', $data);

	}

	function form() {

		if (!$this->mdl_client_groups->validate()) {

			$this->load->helper('form');

			if (!$_POST AND uri_assoc('client_group_id')) {

				$this->mdl_client_groups->prep_validation(uri_assoc('client_group_id'));

			}

			$this->load->view('form');

		}

		else {

			$this->mdl_client_groups->save($this->mdl_client_groups->db_array(), uri_assoc('client_group_id'));

			$this->redir->redirect('client_groups');

		}

	}

	function delete() {

		if (uri_assoc('client_group_id')) {

			$this->mdl_client_groups->delete(uri_assoc('client_group_id'));

		}

		$this->redir->redirect('client_groups');

	}

	function _post_handler() {

		if ($this->input->post('btn_add')) {

			redirect('client_groups/form');

		}

		elseif ($this->input->post('btn_cancel')) {

			redirect('client_groups/index');

		}

	}

}

?>