<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class Mdl_Currencies extends MY_Model {

	public function __construct() {

		parent::__construct();

		$this->table_name = 'mcb_currencies';

		$this->primary_key = 'mcb_currencies.currency_id';

		$this->select_fields = "
		SQL_CALC_FOUND_ROWS *";



	}

	public function validate() {

		$this->form_validation->set_rules('currency_name', $this->lang->line('currency_name'), 'required');
		$this->form_validation->set_rules('currency_code', $this->lang->line('currency_code'), 'required');
		$this->form_validation->set_rules('currency_symbol_left', $this->lang->line('currency_symbol_left'));
		$this->form_validation->set_rules('currency_symbol_left', $this->lang->line('currency_symbol_right'));
		
		return parent::validate();

	}
    
    
	public function delete($currency_id) {


		$this->db->where('currency_id', $currency_id);

		$this->db->delete($this->table_name);

		$this->session->set_flashdata('success_delete', TRUE);

		return TRUE;

		
		
	}


}

?>