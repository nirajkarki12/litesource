<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class Mdl_Delivery_Docket_Items extends MY_Model {

	public function __construct() {

		parent::__construct();

		$this->table_name = 'mcb_delivery_docket_items';

		$this->primary_key = 'mcb_delivery_docket_items.docket_item_id';

		$this->select_fields = "
		SQL_CALC_FOUND_ROWS *";


	}

	public function validate() {

		//$this->form_validation->set_rules('product_id', $this->lang->line('product_id'));
		//$this->form_validation->set_rules('item_name', $this->lang->line('item_name'), 'required');
		//$this->form_validation->set_rules('item_type', $this->lang->line('item_type'));
		//$this->form_validation->set_rules('item_description', $this->lang->line('item_description'), 'required');
		$this->form_validation->set_rules('docket_item_qty', $this->lang->line('quantity'), 'required');
		//$this->form_validation->set_rules('item_supplier_price', $this->lang->line('unit_price'), 'required');

		return parent::validate($this);

	}

	public function db_array() {

		$db_array = parent::db_array();

		$db_array['docket_id'] = uri_assoc('docket_id', 4);

		return $db_array;

	}

	public function save($db_array, $docket_item_id = NULL) {

		/* Transform vars to standard number format */
		$db_array['docket_item_qty'] = standardize_number($db_array['docket_item_qty']);


		parent::save($db_array, $docket_item_id);


	}

	public function delete($docket_item_id) {

		$this->db->where('docket_item_id', $docket_item_id);

		$this->db->delete('mcb_delivery_docket_items');

		$this->session->set_flashdata('success_delete', TRUE);

	}

}

?>