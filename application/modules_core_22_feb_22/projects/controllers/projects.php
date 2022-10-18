<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class Projects extends Admin_Controller {

	function __construct() {

		parent::__construct();

		$this->_post_handler();

		$this->load->model('mdl_projects');

	}

	function index() {

		$this->load->helper('text');

		$this->redir->set_last_index();

		$params = array(
			'paginate'	=>	TRUE,
			'limit'		=>	$this->mdl_mcb_data->setting('results_per_page'),
			'page'		=>	uri_assoc('page')
		);
        $order_by = uri_assoc('order_by');

        switch ($order_by) {
			case 'project_id':
				$params['order_by'] = 'project_id';
				break;
			default:
				$params['order_by'] = 'project_name';
		}
        
		
		$data = array(
			'projects' => $this->mdl_projects->get($params),
			'order_by' => $params['order_by']
		);


		$this->load->view('index', $data);

	}

	function form() {


		if ($this->mdl_projects->validate()) {
			
			$this->mdl_projects->save();

			redirect($this->session->userdata('last_index'));

		}

		else {
			
			$this->load->helper('form');

			if (!$_POST AND uri_assoc('project_id')) {
				
				$this->mdl_projects->prep_validation(uri_assoc('project_id'));

			}
			
			$this->load->helper('text');

					
			$this->load->view('form');

		}

	}

	function details() {

		
		if ($this->mdl_projects->validate()) {
			
			$this->mdl_projects->save();

			redirect($this->session->userdata('last_index'));

		}

		else {
			
			$this->load->helper('form');
			$project_id = uri_assoc('project_id');
			
			if (!$_POST AND $project_id) {
				
				$this->mdl_projects->prep_validation($project_id);

			}
			
			$this->load->helper('text');


			$this->load->model(
				array(
					'invoices/mdl_invoices',
					'orders/mdl_orders',
				)
			);


			$project_params = array(
				'where'	=>	array(
					'mcb_projects.project_id'	=>	$project_id
				)
			);

			$invoice_params = array(
				'where'	=>	array(
					'mcb_invoices.project_id'		=>	$project_id,
					'mcb_invoices.invoice_is_quote' =>  0
				)
			);

			$quote_params = array(
				'where'	=>	array(
					'mcb_invoices.project_id'		=>	$project_id,
					'mcb_invoices.invoice_is_quote'	=>	1
				)
			);
			
			$order_params = array(
				'where'	=>	array(
					'mcb_orders.project_id'	=>	$project_id
				)
			);
			
			$project = $this->mdl_projects->get($project_params);
			$quotes = $this->mdl_invoices->get($quote_params);
			$invoices = $this->mdl_invoices->get($invoice_params);
			$orders = $this->mdl_orders->get($order_params);
                        
                        $invoices_edited = array();
                        foreach ($invoices as $invoice) {
                            $temp = $invoice;
                            $temp->invoice_status_id = $this->mdl_invoices->getInvoiceStatusId($invoice->invoice_id);
                            $temp->invoice_status = $this->common_model->get_row('mcb_invoice_statuses', array('invoice_status_id'=> ($temp->invoice_status_id) ))->invoice_status;
                            $invoices_edited[] = $temp;
                        }
                        
			if ($this->session->flashdata('tab_index')) {

				$tab_index = $this->session->flashdata('tab_index');

			}

			else {

				$tab_index = 0;

			}
                        //echo '<pre>'; print_r($invoices); exit;
			$data = array(
				'project'	=>	$project,
				'quotes'	=>	$quotes,
				'invoices'	=>	$invoices_edited,
				'orders'	=>	$orders,
				'tab_index'	=>	$tab_index
			);

			$this->load->view('details', $data);

		}
	}
	
	
	function delete() {

		if (uri_assoc('project_id')) {

			$this->mdl_projects->delete(uri_assoc('project_id'));

		}

		$this->redir->redirect('projects');

	}

	function get($params = NULL) {

		return $this->mdl_projects->get($params);

	}

	
	function ajax_project_autocomplete()
	{
		
		$this->load->model('projects/mdl_projects');
		$search_term = $this->input->post('term');
		
		
		$data = array(
			'search_results'	=>	$this->mdl_projects->search($search_term));
		
		
		echo json_encode($data);
	}
	
	
	
	function _post_handler() {

		if ($this->input->post('btn_add_project')) {

			redirect('projects/form');

		}

		elseif ($this->input->post('btn_cancel')) {

			redirect($this->session->userdata('last_index'));

		}



	}


}

?>