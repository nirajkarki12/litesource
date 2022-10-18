<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class Mdl_Category extends MY_Model {

	public function __construct() {

		parent::__construct();

		$this->table_name = 'mcb_category';

		$this->primary_key = 'mcb_category.category_id';

		$this->select_fields = "
			SQL_CALC_FOUND_ROWS 
			mcb_category.*,
			IF(mcb_category.category_status = 0, 'Inactive', 'Active') AS category_status_value";

		$this->order_by = 'category_id';


	}

	public function validate() {
	    $id = uri_assoc('category_id');

        if($id!=''){
            $original_value = $this->db->query("SELECT category_name FROM mcb_category WHERE category_id = ".$id)->row()->category_name ;
            if($this->input->post('category_name') != $original_value) {
                $is_unique =  '|callback_uniqueCategory';
            } else {
                $is_unique =  '';
            }
        }else{
            $is_unique =  '|callback_uniqueCategory';
        }


		$this->form_validation->set_rules('category_status','Status');
		$this->form_validation->set_rules('category_name', $this->lang->line('category_name'), 'required'.$is_unique);


		return parent::validate($this);
		
	}
    function uniqueCategory($category_name){
        $this->db->where('category_name', trim($category_name));

        $this->db->where('category_deleted', 0);
        $q = $this->db->get('mcb_category');
        if ($q->num_rows() > 0) {
            $this->form_validation->set_message('uniqueCategory', 'The %s  is not available');
            return FALSE;
        }
        return TRUE;
    }

	public function get_active_categories() {

		$params = array(
			'where'	=>	array(
				'category_status'	=>	1,
                'category_deleted'=> 0,
			)
		);

		return $this->get($params);

	}

	public function delete($project_id) {

        $this->db->set('category_deleted', '1', FALSE);
		$this->db->where('category_id', $project_id);
		
		
		$this->db->update($this->table_name);

		$this->session->set_flashdata('success_delete', TRUE);

	}
	
	public function save() {

		$db_array = parent::db_array();

		if (!$this->input->post('category_status')) {

			$db_array['category_status'] = 0;

		}


		parent::save($db_array, uri_assoc('category_id'));

	}
}

?>