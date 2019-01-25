<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class Sessions extends Client_Center_Controller {

	function __construct() {

		parent::__construct();

	}

	function index() {

		redirect('sessions/login');

	}

	function login() {

		$this->load->helper('url');

		$this->load->model('mdl_client_sessions');

		if ($this->mdl_client_sessions->validate()) {

			$params = array(
				'where'	=>	array(
					'username'	=>	$this->input->post('username'),
					'password'	=>	$this->input->post('password')
				)
			);

			$this->load->model('mdl_client_center');

			$client = $this->mdl_client_center->get($params);

			if (count($client) == 1) {

				$client = $client[0];

				$this->db->set('last_login', time());

				$this->db->where('client_center_id', $client->client_center_id);
				
				$this->db->update('mcb_client_center');

				$session_data = array(
					'client_id'		=>	$client->client_id,
					'client_name'	=>	$client->client_name
				);

				$this->session->set_userdata($session_data);

				redirect('client_center');

			}

			else {

				$this->load->view('sessions/login');

			}

		}

		else {

			$this->load->view('sessions/login');

		}

	}

	function logout() {

		$this->load->helper('url');

		$this->session->sess_destroy();

		redirect('client_center/sessions/login');

	}

}

?>