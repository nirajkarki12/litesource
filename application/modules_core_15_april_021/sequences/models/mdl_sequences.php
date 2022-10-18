<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class Mdl_Sequences extends MY_Model {

	public function __construct() {

		parent::__construct();

		$this->table_name = 'mcb_sequences';

		$this->primary_key = 'mcb_sequences.sequence_id';

		$this->select_fields = "
		SQL_CALC_FOUND_ROWS *";

		$this->limit = $this->mdl_mcb_data->setting('results_per_page');

	}

	public function validate() {

		$this->form_validation->set_rules('sequence_name', $this->lang->line('sequence_name'), 'required|max_length[50]');
		$this->form_validation->set_rules('sequence_next_value', $this->lang->line('sequence_next_value'), 'required|numeric');

		return parent::validate();

	}

	public function get_next_value($sequence_id) {

		$sequence = parent::get_by_id($sequence_id);
		
		$sequence_number = 0;
		
		if (isset($sequence)) {
			
			$sequence_number = $sequence->sequence_next_value;

			$this->db->set('sequence_next_value', $sequence_number + 1);
			$this->db->where('sequence_id', $sequence_id);
			$this->db->update($this->table_name);
		}
		
		return $sequence_number;
		
	}

}

?>