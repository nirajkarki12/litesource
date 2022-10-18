<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class Mdl_Client_Groups extends MY_Model {

	public function __construct() {

		parent::__construct();

		$this->table_name = 'mcb_client_groups';

		$this->primary_key = 'mcb_client_groups.client_group_id';

		$this->select_fields = "
		SQL_CALC_FOUND_ROWS *";

		$this->order_by = 'client_group_discount_percent';


	}

	public function validate() {

		$this->form_validation->set_rules('client_group_name', $this->lang->line('client_group_name'), 'required');
		$this->form_validation->set_rules('client_group_discount_percent', $this->lang->line('client_group_discount_percent'), 'required');

		return parent::validate();

	}
    
    
	public function delete($client_group_id) {

		/*
		 * Before deleting a group, make sure no clients are assigned
		 */

		$this->db->where('client_group_id', $client_group_id);

		$query = $this->db->get('mcb_clients');

		if ($query->num_rows()) {

			$this->session->set_flashdata('custom_error', $this->lang->line('cannot_delete_client_group'));

			return FALSE;

		}
        /*
		elseif ($this->mdl_mcb_data->setting('default_client_group_id') == $client_group_id) {

			$this->session->set_flashdata('custom_error', $this->lang->line('cannot_delete_default_client_group'));

			return FALSE;

		}
        */
		else {

			$this->db->where('client_group_id', $client_group_id);

			$this->db->delete($this->table_name);

			$this->session->set_flashdata('success_delete', TRUE);

			return TRUE;

		}
		
	}


}

?>