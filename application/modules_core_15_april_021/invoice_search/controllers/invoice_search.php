<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class Invoice_Search extends Admin_Controller {

	function index() {

		$this->load->model('mdl_invoice_search');
		$quote_search = 0;
		
		if (uri_assoc('is_quote')) {
			$quote_search = 1;
		}
		
		if (!$this->mdl_invoice_search->validate()) {

			/*
			$this->load->model('clients/mdl_clients');

			$this->load->model('invoice_statuses/mdl_invoice_statuses');
`		
			$data = array(
				'clients'			=>	$this->mdl_clients->get(),
				'invoice_statuses'	=>	$this->mdl_invoice_statuses->get()
			);
			*/
			
			$data = array(
				'quote_search'		=> $quote_search
			);
			
			$this->load->view('search');

		}

		else {

			$params = array();
			
			$params['quote_search'] = $quote_search;
			$params['where']['mcb_invoices.invoice_is_quote'] = $quote_search;
			
			
			
			/*
			if (!$this->session->userdata('global_admin')) {

				$params['where']['mcb_invoices.user_id'] = $this->session->userdata('user_id');

			}
			*/
			
			/*
			if (!$this->input->post('include_quotes')) {

				$params['where']['mcb_invoices.invoice_is_quote'] = 0;

			}
			*/
			/*
			// Parse tags if posted 
			if ($this->input->post('tags')) {

				// Remove any apostrophes and trim 
				$tags = trim(str_replace("'", '', $this->input->post('tags')));

				
				//  Explode into an array and trim each individual element
				//  if comma separated tags are provided
				 

				if (strpos($tags, ',')) {

					$tags = explode(',', $tags);

					foreach ($tags as $key=>$tag) {

						$tags[$key] = trim($tag);

					}

					$tags = implode("','", $tags);

				}

				// Add the tag where $params array element 
				$params['where'][] = "mcb_invoices.invoice_id IN (SELECT invoice_id FROM mcb_invoice_tags WHERE tag_id IN (SELECT tag_id FROM mcb_tags WHERE tag IN('" . $tags . "')))";

			}
			*/
			
			/*
			// Add any clients if selected 
			if ($this->input->post('client_id')) {

				$params['where_in']['mcb_invoices.client_id'] = $this->input->post('client_id');

			}
			
			
			// Add any invoice statuses if selected 
			if ($this->input->post('invoice_status_id')) {

				$params['where_in']['mcb_invoices.invoice_status_id'] = $this->input->post('invoice_status_id');

			}

			// Add from date if provided 
			if ($this->input->post('from_date')) {

				$params['where']['mcb_invoices.invoice_date_entered >='] = strtotime(standardize_date($this->input->post('from_date')));

			}

			// Add to date if provided 
			if ($this->input->post('to_date')) {

				$params['where']['mcb_invoices.invoice_date_entered <='] = strtotime(standardize_date($this->input->post('to_date')));

			}

			// Add invoice id if provided 
			if ($this->input->post('invoice_number')) {

				$params['where'][] = "mcb_invoices.invoice_number LIKE '%" . $this->input->post('invoice_number') . "%'";

			}

			// Add amount if provided 
			if ($this->input->post('amount_operator') and check_clean_number($this->input->post('amount'))) {

				if ($this->input->post('amount_operator') <> '=') {

					$params['where']['mcb_invoice_amounts.invoice_total ' . $this->input->post('amount_operator')] = standardize_number($this->input->post('amount'));

				}

				else {

					$params['where']['mcb_invoice_amounts.invoice_total'] = standardize_number($this->input->post('amount'));

				}

			}
			*/
			
			if ($this->input->post('invoice_number')) {
				
				$params['where'][] = "mcb_invoices.invoice_number LIKE '" . $this->input->post('invoice_number') . "%'";

			}

			if ($this->input->post('contact_name')) {

				$params['like']['contact_name'] = $this->input->post('contact_name');

			}
			
			if ($this->input->post('client_name')) {

				$params['like']['client_name'] = $this->input->post('client_name');

			}
			
			if ($this->input->post('project_name')) {

				$params['like']['project_name'] = $this->input->post('project_name');

			}
			
			/* Setup product search parameters */
			if ($this->input->post('product_name')) {

				$params['like']['mcb_invoice_items.item_name'] = $this->input->post('product_name');

			}
			
			if ($this->input->post('product_description')) {

				$params['like']['mcb_invoice_items.item_description'] = $this->input->post('product_description');

			}
			
			if (!$params) {

				redirect('invoice_search');

			}

			/* Generate a simple hash value */
			$hash = md5(time());

			/* Stick this stuff in the users session data */
		
			$userdata = array(
				'search_hash'	=>	array(
					$hash	=>	$params
				)
			);
            
			
			$this->session->set_userdata($userdata);

			/* Redirect to display results */
			redirect('invoice_search/search_results/index/search_hash/' . $hash);

		}

	}

}

?>