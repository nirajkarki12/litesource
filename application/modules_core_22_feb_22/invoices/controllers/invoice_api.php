<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Invoice_API extends Admin_Controller {

	function __construct() {

		parent::__construct();

	}

	function display_create_invoice() {

		$this->load->model('clients/mdl_clients');

		$this->load->model('invoices/mdl_invoice_groups');

		$data = array(
			'clients'			=>	$this->mdl_clients->get(),
			'invoice_groups'	=>	$this->mdl_invoice_groups->get()
		);

		$this->load->view('invoices/choose_client', $data);

	}

	function create_invoice($package) {

		/**
		 * $package requirements
		 * - client_id
		 * - contact_name
		 * - project_name
		 * - invoice_date_entered
		 * - invoice_is_quote
		 * - invoice_group_id
		 *
		 *
		 * $package optional
		 * - invoice_discount
		 * - invoice_shipping
		 * - invoice_items (array)
		 *
		 * $package['invoice_items'] requirements
		 * - item_name
		 * - item_description
		 * - item_qty
		 * - item_price
		 * - item_index
		 */

		if (!is_array($package)) {

			return FALSE;

		}

		$required_elements = array(
			'client_id',
			'client_group_id',
			'invoice_date_entered'
		);

		foreach ($required_elements as $req_el) {

			if (!isset($package[$req_el])) {

				return FALSE;

			}

		}

		extract($package);

		if (!isset($invoice_is_quote)) {

			$invoice_is_quote = 0;

		}
		
	
		$this->load->model('clients/mdl_contacts');
		
		$contact_id = $this->mdl_contacts->get_or_add_contact_by_name($client_id, $contact_name);
		
		$project_id = 0;
		/**
		 * Get the corresponding project_id or create a new 
		 * project if the name can not be found
		 */
		if (isset($project_name) && ($project_name !== '') ) {
				
			$this->load->model('projects/mdl_projects');
			
			$project = $this->mdl_projects->get_project_by_name($project_name);
					
			if (isset($project)) {;
				$project_id = $project->project_id;
							
			}
			else {
				$project_id = $this->mdl_projects->add_new_project($project_name);
			}
		}
		
		
		$this->load->model('invoices/mdl_invoices');
		
		$invoice_id = $this->mdl_invoices->save($client_id, $client_group_id, $project_id, $contact_id, $invoice_date_entered, $invoice_is_quote);

		if (isset($invoice_items)) {

			foreach ($invoice_items as $invoice_item) {

				unset($item_name, $item_description, $item_qty, $item_price, $item_index);

				extract($invoice_item);

				$this->mdl_invoices->add_invoice_item($invoice_id, $item_name, $item_description, $item_qty, $item_price, $item_index);

			}

		}

		if (isset($invoice_discount)) {

			$this->mdl_invoices->set_invoice_discount($invoice_id, $invoice_discount);

		}

		if (isset($invoice_shipping)) {

			$this->mdl_invoices->set_invoice_shipping($invoice_id, $invoice_shipping);

		}

		$this->adjust_invoice_amount($invoice_id);

		/**
		 *  Only set the invoice_number if not already set via a quote
		 *  i.e invoice will have same invoice_number as original quote
		 */
		if (!isset($invoice_number)) {
			
			$this->load->model('invoices/mdl_invoice_groups');
			$this->mdl_invoice_groups->adjust_invoice_number($invoice_id);
		}
		
		return $invoice_id;

	}

	function add_invoice_item($package) {

		if (!is_array($package)) {

			return FALSE;

		}

		extract($package);

		$required_elements = array(
			'invoice_id',
			'item_name',
			'item_description',
			'item_qty',
			'item_price'
		);

		foreach ($required_elements as $req_el) {

			if (!isset($package[$req_el])) {

				return FALSE;

			}

		}

		$this->load->model('invoices/mdl_invoices');

		$tax_rate_id = (isset($tax_rate_id) ? $tax_rate_id : 0);

		$is_taxable = (isset($is_taxable) ? $is_taxable : 0);

		$invoice_item_id = $this->mdl_invoices->add_invoice_item($invoice_id, $item_name, $item_description, $item_qty, $item_price, $item_index, $is_taxable, $tax_rate_id);

		return $invoice_item_id;

	}

	function add_invoice_discount($invoice_id, $invoice_discount) {

		$this->mdl_invoices->set_invoice_discount($invoice_id, $invoice_discount);

	}

	function add_invoice_shipping($invoice_id, $invoice_shipping) {

		$this->mdl_invoices->set_invoice_shipping($invoice_id, $invoice_shipping);

	}

	function adjust_invoice_amount($invoice_id) {

		$this->load->model('invoices/mdl_invoice_amounts');

		$this->mdl_invoice_amounts->adjust($invoice_id);

	}

}

?>