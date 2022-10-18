<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class Mdl_Suppliers extends MY_Model {

	public function __construct() {

		parent::__construct();

		$this->table_name = 'mcb_suppliers';

		//join with linked client to get details etc
		$this->select_fields = "
		SQL_CALC_FOUND_ROWS
		mcb_suppliers.supplier_id,
        mcb_suppliers.supplier_short_name,
		mcb_suppliers.supplier_description,
		mcb_suppliers.supplier_sort_index,
		mcb_clients.client_name AS supplier_name,
		mcb_clients.*";

		$this->joins = array(
			'mcb_clients' =>	'mcb_clients.client_id = mcb_suppliers.client_id'
		);
		
		$this->primary_key = 'mcb_suppliers.supplier_id';

		$this->order_by = 'supplier_sort_index, supplier_short_name';


	}

	public function get_active() {

        //maybe should add a supplier_active property instead?
		$params = array(
			'where'	=>	array(
				'client_active'	=>	1
			)
		);

		return $this->get($params);

	}

	public function validate() {

		$this->form_validation->set_rules('client_id', $this->lang->line('client_id'));
        $this->form_validation->set_rules('supplier_short_name', $this->lang->line('supplier_short_name'), 'required');
		$this->form_validation->set_rules('supplier_description', $this->lang->line('supplier_description'), 'required');
		$this->form_validation->set_rules('supplier_sort_index', $this->lang->line('sort_index'));

		return parent::validate($this);

	}

	public function delete($supplier_id) {

		//$this->load->model('products/mdl_products');

		/* Delete the supplier record */
		parent::delete(array('supplier_id'=>$supplier_id));

		/*
		 * Delete any related products, but use the product model so records
		 * related to the product are also deleted
		*/

		/*
		$this->db->select('product_id');

		$this->db->where('supplier_id', $supplier_id);

		$products = $this->db->get('mcb_products')->result();

		foreach ($products as $product) {

			$this->mdl_products->delete($product->product_id);

		}
		*/

	}

	public function save() {

		$db_array = parent::db_array();

		parent::save($db_array, uri_assoc('supplier_id'));

	}

}

?>