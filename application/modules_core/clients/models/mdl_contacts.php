<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class Mdl_Contacts extends MY_Model {

	public function __construct() {

		parent::__construct();

		$this->table_name = 'mcb_contacts';

		$this->select_fields = "SQL_CALC_FOUND_ROWS *";

		$this->primary_key = 'mcb_contacts.contact_id';

		$this->order_by = 'contact_name';

		$this->custom_fields = $this->mdl_fields->get_object_fields(4);

	}

	public function validate() {

		//$this->form_validation->set_rules('last_name', $this->lang->line('last_name'), 'required');
		//$this->form_validation->set_rules('first_name', $this->lang->line('first_name'), 'required');
		$this->form_validation->set_rules('contact_name', $this->lang->line('contact_name'), 'required');
		$this->form_validation->set_rules('contact_active', $this->lang->line('contact_active'));
		$this->form_validation->set_rules('address', $this->lang->line('street_address'));
		$this->form_validation->set_rules('address_2', $this->lang->line('street_address_2'));
		$this->form_validation->set_rules('city', $this->lang->line('city'));
		$this->form_validation->set_rules('state', $this->lang->line('state'));
		$this->form_validation->set_rules('zip', $this->lang->line('zip'));
		$this->form_validation->set_rules('country', $this->lang->line('country'));
		$this->form_validation->set_rules('phone_number', $this->lang->line('phone_number'));
		$this->form_validation->set_rules('fax_number', $this->lang->line('fax_number'));
		$this->form_validation->set_rules('mobile_number', $this->lang->line('mobile_number'));
		$this->form_validation->set_rules('email_address', $this->lang->line('email_address'));
		$this->form_validation->set_rules('web_address', $this->lang->line('web_address'));
		$this->form_validation->set_rules('notes', $this->lang->line('notes'));

		foreach ($this->custom_fields as $custom_field) {

			$this->form_validation->set_rules($custom_field->column_name, $custom_field->field_name);

		}

		return parent::validate();

	}

	
	function get_or_add_contact_by_name($client_id, $contact_name = '') {
		
		$contact_id = 0;
		/**
		 * Get the corresponding contact_id or create a new 
		 * contact for the client if the name can not be found
		 */
		if (isset($contact_name) && ($contact_name !== '') ) {
				
			
			$contact = $this->get_contact_by_name($client_id, $contact_name);
					
			if (isset($contact)) {;
				$contact_id = $contact->contact_id;
							
			}
			else {
				$contact_id = $this->add_client_contact($client_id, $contact_name);
				
			}
		}
		
		return $contact_id;
		
		
	}

	function add_client_contact($client_id, $contact_name) {
		$db_array = array(
			'client_id'		=>	$client_id,
			'contact_name'  =>  $contact_name,
		);   

		$this->db->insert($this->table_name, $db_array);

		return $this->db->insert_id();

	}
	
	function get_contact_by_name($client_id, $contact_name) {
		
				
		$this->db->where('client_id', $client_id);
		$this->db->where('TRIM(UCASE(contact_name))', trim(strtoupper($contact_name)));
		$this->db->select('contact_id');
		
		$query = $this->db->get($this->table_name);

		if ($query->num_rows() > 0) {
			return $query->row();;
		}
		
	}
	
	public function get_client_contacts($client_id) {

		/* setting id and value for jqueryui autocomplete */
		$params = array(
			'select'	=> "
				SQL_CALC_FOUND_ROWS
				contact_id AS id,
				contact_id,
				contact_name AS value,
				contact_name",
			
			'where'		=>	array(
				'client_id'			=>	$client_id,
				'contact_active'	=> 1
			),
			
			'order_by'	=> 'contact_name'
		);
		
		return $this->get($params);

	}
	
	public function delete($client_id, $contact_id) {

	
		$this->db->where('contact_id', $contact_id);
		$this->db->where('client_id', $client_id);
		
		$this->db->delete('mcb_contacts');

		$this->session->set_flashdata('success_delete', TRUE);

	}

	public function save() {


		$db_array = parent::db_array();

		$db_array['client_id'] = uri_assoc('client_id', 4);
		
		if (!$this->input->post('contact_active')) {

			$db_array['contact_active'] = 0;

		}

		$contact_id = uri_assoc('contact_id', 4);
		
		//parent::show($db_array);
		
		parent::save($db_array, $contact_id);

	}
	
}

?>