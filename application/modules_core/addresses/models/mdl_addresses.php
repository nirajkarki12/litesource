<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class Mdl_Addresses extends MY_Model {

	public function __construct() {

		parent::__construct();

		$this->table_name = 'mcb_addresses';

		$this->select_fields = "SQL_CALC_FOUND_ROWS *";

		$this->primary_key = 'mcb_addresses.address_id';

		$this->order_by = 'address_contact_name';


	}

	public function validate() {

		$this->form_validation->set_rules('address_contact_name', $this->lang->line('contact_name'), 'required');
		$this->form_validation->set_rules('address_active', $this->lang->line('address_active'));
		$this->form_validation->set_rules('address_defaultable', $this->lang->line('address_defaultable'));
		$this->form_validation->set_rules('address_street_address', $this->lang->line('street_address'));
		$this->form_validation->set_rules('address_street_address_2', $this->lang->line('street_address_2'));
		$this->form_validation->set_rules('address_city', $this->lang->line('city'));
		$this->form_validation->set_rules('address_state', $this->lang->line('state'));
		$this->form_validation->set_rules('address_postcode', $this->lang->line('postcode'));
		$this->form_validation->set_rules('address_country', $this->lang->line('country'));

		return parent::validate();

	}

	
	public function get_active() {

		$params = array(
			'where'	=>	array(
				'address_active'	=>	1
			)
		);

		return $this->get($params);

	}

	public function get_default_addresses() {

		$params = array(
			'select'	=> '*, CONCAT(address_street_address, " ", address_street_address_2, " ", address_state, " ", address_postcode) AS address_formatted', 
			'where'		=>	array(
				'address_active'		=>	1,
				'address_defaultable'	=>	1
			)
		);

		return $this->get($params);

	}
	
	public function delete($address_id) {
	
		parent::delete(array('address_id'=>$address_id));

	}
	
	public function save() {

		$db_array = parent::db_array();
		
		
		if (!$this->input->post('address_active')) {

			$db_array['address_active'] = 0;

		}
		
		if (!$this->input->post('address_defaultable')) {

			$db_array['address_defaultable'] = 0;

		}
		
		$address_id = uri_assoc('address_id');
		
		if (!$address_id) {
			
			// possibly editing address associate with an order/delivery_docket so get address_id indirectly
			
			$address_id = $this->input->post('address_id');
			$order_id = uri_assoc('order_id');
			$docket_id = uri_assoc('docket_id');

		}
		
		parent::save($db_array, $address_id, !(isset($order_id) || isset($docket_id)));
		
		/*
		 *  I think this violates all that loose coupling mumbo jumbo...
		 *  But any ways, if the address was saved for an order
		 *  then need to make sure the order record is linked to this
		 *  address - which we're also assuming is an insert not an update
		 */
		if (!$address_id) {
			
			$address_id = $this->db->insert_id();

			if (isset($order_id)) {
				$this->load->model('orders/mdl_orders');
				$this->mdl_orders->save_delivery_address($order_id, $address_id);
			}

			elseif (isset($docket_id)) {
				$this->load->model('delivery_dockets/mdl_delivery_dockets');
				$this->mdl_delivery_dockets->save_delivery_address($docket_id, $address_id);

			}

		}
	

	}
	
}

?>