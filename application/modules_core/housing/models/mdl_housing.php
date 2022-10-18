<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class Mdl_Housing extends MY_Model {

	public function __construct() {
            parent::__construct();

            $this->primary_key = 'mcb_housings.housing_id';

            $this->table_name = 'mcb_housings';
	}

	public function validate() {
            $this->form_validation->set_rules('product_id', $this->lang->line('product_id'), 'required');
            $this->form_validation->set_rules('link_products', $this->lang->line('link_products'), 'required');
            return parent::validate();
	}

	
	public function get_by($where_cond) {
            return $this->get($where_cond);
	}

	public function get_housing_lists() {
            $qry_res = $this->db->query("SELECT 
                    h.housing_id, 
                    h.product_id,
                    h.notes,
                    h.link_products,
                    mii.name as product_name
            FROM `mcb_housings` AS h 
                LEFT JOIN mcb_inventory_item AS mii
                ON h.product_id = mii.inventory_id 
                WHERE is_arichved != '1'
                ORDER BY h.housing_id DESC");
            return $qry_res->result_array($qry_res);
	}

	public function get_products_by_query($search_query) {
		$qry_res = $this->db->query("SELECT 
	                inventory_id AS `id`,
	                name AS `text`
                    FROM `mcb_inventory_item`
	            WHERE is_arichved != '1' AND (name LIKE '" . $search_query . "%' OR description LIKE '" . $search_query . "%') LIMIT 50");
		return $qry_res->result_array($qry_res);
	}

	public function get_housing_by_id($id) {
		$sql = "SELECT 
	                mh.*,
	                mii.name AS item_name
                    FROM `mcb_housings` mh
                    INNER JOIN `mcb_inventory_item` mii ON mh.product_id = mii.inventory_id
	            WHERE mh.housing_id ='{$id}'";
		$qry_res = $this->db->query($sql);
		return $qry_res->row($qry_res);
	}

	public function delete($housing_id) {
            parent::delete(array('housing_id'=>$housing_id));
	}
        
        public function delete_by_product_id($product_id) {
            parent::delete(array('product_id'=>$product_id));
	}
	
	public function save() {
            //check if the housing_name already exists
            $housing_product_exists = $this->checkIfHousingExistsByProduct($this->input->post('product_id'));

            if(uri_assoc('housing_id') > 0 ){
                $housing_product_exists = uri_assoc('housing_id');
            }

            $product_id = $this->input->post('product_id');
            $link_products =  ($this->input->post('link_products') && is_array($this->input->post('link_products'))) ? $this->input->post('link_products') : [];

            if(in_array($product_id, $link_products)){// removing self referencing records
                $link_products = array_diff($link_products, [$product_id]);
            }

            $data = array(
                'product_id' => $product_id,
                'link_products' => implode(",", $link_products),
                'notes' => $this->input->post('notes'),
            );

            if ($housing_product_exists) {
                //gonna update
                $this->db->where('housing_id', $housing_product_exists);

                if ($this->db->update('mcb_housings', $data)) {
                    $this->session->set_flashdata('custom_success', 'Housing successfully updated.');
                } else {
                    $this->session->set_flashdata('custom_error', 'Error while updating housing. Please try again later.');
                }
            } else {
                if ($this->db->insert('mcb_housings', $data)) {
                    $this->session->set_flashdata('custom_success', 'Housing successfully added.');
                } else {
                    $this->session->set_flashdata('custom_error', 'Error while adding housing. Please try again later.');
                }
            }
	}
        
    public function bulk_save($product_id, $linked_products) {
        //check if the housing_name already exists
        $housing_product_exists = $this->checkIfHousingExistsByProduct($this->input->post('product_id'));

        $link_products =  ($linked_products && is_array($linked_products)) ? $linked_products : [];

        if(in_array($product_id, $link_products)){// removing self referencing records
            $link_products = array_diff($link_products, [$product_id]);
        }

        $data = array(
            'product_id' => $product_id,
            'link_products' => implode(",", $link_products),
        );

        if ($housing_product_exists) {
            //gonna update
            $this->db->where('housing_id', $housing_product_exists);
            $this->db->update('mcb_housings', $data);
        } else {
            $this->db->insert('mcb_housings', $data);
        }

    }

    public function get_item_housing($product_id) {
        $data = $this->common_model->query_as_object("SELECT 
                mh.housing_id, 
                mii.name AS item_name,
                mii.inventory_id AS product_id,
                mii.description AS item_description,
                mh.notes,
                mii.base_price as item_price
        FROM `mcb_housings` mh
        LEFT JOIN `mcb_inventory_item` AS mii ON FIND_IN_SET(mii.inventory_id, mh.link_products) > 0
            WHERE mh.product_id = '{$product_id}'");
        return $data;
    }

    public function getProductsByIds($product_ids) {
        $qry = "SELECT DISTINCT inventory_id AS product_id,
                    name AS item_name,
                    description AS item_description,
                    CASE WHEN use_length = '1' THEN '1' ELSE '' END AS item_length,
                    CASE WHEN use_length = '1' THEN base_price ELSE '0.00' END AS item_per_meter,
                    '1' as item_qty,
                    null as item_index,
                    null as invoice_id,
                    inventory_type,
                    base_price AS item_price 
                FROM mcb_inventory_item
                WHERE is_arichved != '1' AND inventory_id IN (" . $product_ids .")";
        $q = $this->db->query($qry);
        return $q->result();
    }

    private function checkIfHousingExistsByProduct($product_id) {
        //column index is there, client active or not doesn't mean anything
        $this->db->select('housing_id');
        $this->db->where('product_id', $product_id);
        $q = $this->db->get('mcb_housings');
        $row = $q->row();
        if (isset($row->housing_id) && (int) $row->housing_id > 0) {
            return $row->housing_id;
        }
        return FALSE;
    }
	
}

