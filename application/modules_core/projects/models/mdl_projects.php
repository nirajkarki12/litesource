<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class Mdl_Projects extends MY_Model {

	public function __construct() {

		parent::__construct();

		$this->table_name = 'mcb_projects';

		$this->primary_key = 'mcb_projects.project_id';

		$this->select_fields = "
			SQL_CALC_FOUND_ROWS 
			mcb_projects.*,
			IF(mcb_projects.project_active = 0, 'Inactive', 'Active') AS project_status";

		$this->order_by = 'project_name';

	}

	public function validate() {
	
		$this->form_validation->set_rules('project_active', $this->lang->line('project_active'));
		$this->form_validation->set_rules('project_name', $this->lang->line('project_name'), 'required');
		$this->form_validation->set_rules('project_specifier', $this->lang->line('project_specifier'));
		$this->form_validation->set_rules('project_description', $this->lang->line('project_description'));
	
		return parent::validate($this); 
		
	}

	public function get_active() {

		$params = array(
			'where'	=>	array(
				'project_active'	=>	1
			)
		);

		return $this->get($params);

	}
	
	public function search ($search_string)
	{
		
		$params = array(
			
		    'select'	=> "
		        SQL_CALC_FOUND_ROWS
		        mcb_projects.project_id AS id,
				mcb_projects.project_name AS value,
				mcb_projects.project_description AS description",
						
			'where'		=>	array(
				'project_active'	=>	1
			),
			
			'order_by'	=> 'project_name'
		);
		
		$this->db->limit(10);
		$this->db->like('project_name', $search_string);
		$this->db->or_like('project_description', $search_string);
		
		return $this->get($params);
		
		
			
	}
	
	function add_new_project($project_name) {
		
		$db_array = array(
			'project_name'  =>  $project_name,
			'project_active'	=>	1
		);   

		$this->db->insert($this->table_name, $db_array);

		return $this->db->insert_id();

	}
	
	function get_project_by_name($project_name) {
		

		$this->db->where('TRIM(UCASE(project_name))', trim(strtoupper($project_name)));
		$this->db->select('project_id');
		
		$query = $this->db->get($this->table_name);

		if ($query->num_rows() > 0) {
			return $query->row();;
		}
		
	}

	public function delete($project_id) {

	
		$this->db->where('project_id', $project_id);
		
		
		$this->db->delete($this->table_name);

		$this->session->set_flashdata('success_delete', TRUE);

	}
	
	public function save() {

		$db_array = parent::db_array();

		if (!$this->input->post('project_active')) {

			$db_array['project_active'] = 0;

		}

		parent::save($db_array, uri_assoc('project_id'));

	}
}

?>