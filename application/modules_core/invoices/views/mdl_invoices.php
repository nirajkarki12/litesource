<?php

(defined('BASEPATH')) OR exit('No direct script access allowed');

class Mdl_Invoices extends MY_Model {

    public $date_formats;
    public $stock_msg, $stock_color;
    public $product_line;

    public function __construct() {

        parent::__construct();

        $this->stock_msg = '';
        $this->stock_color = array();
        $this->product_line = array();

        $this->table_name = 'mcb_invoices';

        $this->primary_key = 'mcb_invoices.invoice_id';

        $this->order_by = 'FROM_UNIXTIME(mcb_invoices.invoice_date_entered) DESC, mcb_invoices.invoice_id DESC';

        $this->select_fields = "
		SQL_CALC_FOUND_ROWS
		mcb_invoices.*,
		IFNULL(length(mcb_invoices.invoice_notes), 0) invoice_has_notes,
		mcb_invoice_amounts.*,
		mcb_clients.client_active,
		IFNULL(mcb_clients.client_name, IF(mcb_invoices.client_id <> 0, '(deleted)', NULL)) client_name,
		mcb_clients.client_email_address,
        mcb_tax_rates.tax_rate_name,
		mcb_tax_rates.tax_rate_percent,
		CONCAT(FORMAT(mcb_tax_rates.tax_rate_percent, 0),'% ', mcb_tax_rates.tax_rate_name) tax_rate_percent_name,
		IFNULL(mcb_contacts.contact_name, IF(mcb_invoices.contact_id <> 0, '(deleted)', NULL)) contact_name,
		mcb_contacts.email_address AS contact_email_address,
		mcb_contacts.contact_active,
		IFNULL(prj.project_name, IF(mcb_invoices.project_id <> 0, '(deleted)', '-')) AS project_name,
        prj.project_specifier,	
		IFNULL(prj.project_active, 1) AS project_active,
		cg.client_group_name, 
		cg.client_group_discount_percent,
		q.invoice_number invoice_quote_number,
		mcb_users.username,
	    mcb_users.company_name AS from_company_name,
	    mcb_users.last_name AS from_last_name,
	    mcb_users.first_name AS from_first_name,
	    mcb_users.address AS from_address,
		mcb_users.address_2 AS from_address_2,
	    mcb_users.city AS from_city,
	    mcb_users.state AS from_state,
	    mcb_users.zip AS from_zip,
		mcb_users.country AS from_country,
	    mcb_users.phone_number AS from_phone_number,
		mcb_users.mobile_number AS from_mobile_number,
		mcb_users.email_address AS from_email_address,
		mcb_users.web_address AS from_web_address,
		mcb_users.tax_id_number AS from_tax_id_number,
		mcb_invoice_statuses.*,
        IF(mcb_invoices.invoice_status_id = 2, IF(mcb_invoices.invoice_due_date < UNIX_TIMESTAMP(), 1, 0), 0) AS invoice_is_overdue,
		(DATEDIFF(FROM_UNIXTIME(UNIX_TIMESTAMP()),FROM_UNIXTIME(mcb_invoices.invoice_due_date))) AS invoice_days_overdue";

        /*
          $user_custom_fields = $this->mdl_fields->get_object_fields(6);

          if ($user_custom_fields) {

          $this->select_fields .= ',';

          $ucf = array();

          foreach ($user_custom_fields as $user_custom_field) {

          $ucf[] = 'mcb_users.' . $user_custom_field->column_name;

          }

          $this->select_fields .= implode(',', $ucf);

          }
         */

        $this->joins = array(
            'mcb_invoice_statuses' => array(
                'mcb_invoice_statuses.invoice_status_id = mcb_invoices.invoice_status_id',
                'left'
            ),
            'mcb_users' => array(
                'mcb_users.user_id = mcb_invoices.user_id',
                'left'
            ),
            'mcb_invoice_amounts' => array(
                'mcb_invoice_amounts.invoice_id = mcb_invoices.invoice_id',
                'left'
            ),
            'mcb_clients' => array(
                'mcb_clients.client_id = mcb_invoices.client_id',
                'left'
            ),
            'mcb_tax_rates' => 'mcb_tax_rates.tax_rate_id = mcb_invoices.invoice_tax_rate_id',
            'mcb_contacts' => array(
                'mcb_contacts.contact_id = mcb_invoices.contact_id',
                'left'
            ),
            'mcb_projects AS prj' => array(
                'prj.project_id = mcb_invoices.project_id',
                'left'
            ),
            'mcb_client_groups AS cg' => array(
                'cg.client_group_id = mcb_invoices.invoice_client_group_id',
                'left'
            ),
            'mcb_invoices AS q' => array(
                'q.invoice_id = mcb_invoices.invoice_quote_id',
                'left'
            ),
        );

        $this->date_formats = array(
            'm/d/Y' => array(
                'key' => 'm/d/Y',
                'picker' => 'mm/dd/yy',
                'mask' => '99/99/9999',
                'dropdown' => 'mm/dd/yyyy'),
            'm/d/y' => array(
                'key' => 'm/d/y',
                'picker' => 'mm/dd/y',
                'mask' => '99/99/99',
                'dropdown' => 'mm/dd/yy'),
            'Y/m/d' => array(
                'key' => 'Y/m/d',
                'picker' => 'yy/mm/dd',
                'mask' => '9999/99/99',
                'dropdown' => 'yyyy/mm/dd'),
            'd/m/Y' => array(
                'key' => 'd/m/Y',
                'picker' => 'dd/mm/yy',
                'mask' => '99/99/9999',
                'dropdown' => 'dd/mm/yyyy'),
            'd/m/y' => array(
                'key' => 'd/m/y',
                'picker' => 'dd/mm/y',
                'mask' => '99/99/99',
                'dropdown' => 'dd/mm/yy'),
            'm-d-Y' => array(
                'key' => 'm-d-Y',
                'picker' => 'mm-dd-yy',
                'mask' => '99-99-9999',
                'dropdown' => 'mm-dd-yyyy'),
            'm-d-y' => array(
                'key' => 'm-d-y',
                'picker' => 'mm-dd-y',
                'mask' => '99-99-99',
                'dropdown' => 'mm-dd-yy'),
            'Y-m-d' => array(
                'key' => 'Y-m-d',
                'picker' => 'yy-mm-dd',
                'mask' => '9999-99-99',
                'dropdown' => 'yyyy-mm-dd'),
            'y-m-d' => array(
                'key' => 'y-m-d',
                'picker' => 'y-mm-dd',
                'mask' => '99-99-99',
                'dropdown' => 'yy-mm-dd'),
            'd.m.Y' => array(
                'key' => 'd.m.Y',
                'picker' => 'dd.mm.yy',
                'mask' => '99.99.9999',
                'dropdown' => 'dd.mm.yyyy'),
            'd.m.y' => array(
                'key' => 'd.m.y',
                'picker' => 'dd.mm.y',
                'mask' => '99.99.99',
                'dropdown' => 'dd.mm.yy')
        );
    }

    public function get($params = NULL) {

        //$params['debug'] = TRUE;

        $invoices = parent::get($params);
        if (is_array($invoices)) {

            foreach ($invoices as $invoice) {

                $invoice = $this->set_invoice_additional($invoice, $params);
            }
        } else {

            $invoices = $this->set_invoice_additional($invoices, $params);
        }

        return $invoices;
    }

    public function search($params = NULL) {

        $params['select'] = "
			SQL_CALC_FOUND_ROWS
			mcb_invoices.*,
			mcb_clients.client_name,	
			mcb_contacts.contact_name,
			prj.project_name,
			q.invoice_number invoice_quote_number,
			mcb_invoice_statuses.*,
            IF(mcb_invoices.invoice_status_id = 2, IF(mcb_invoices.invoice_due_date < UNIX_TIMESTAMP(), 1, 0), 0) AS invoice_is_overdue,
		    (DATEDIFF(FROM_UNIXTIME(UNIX_TIMESTAMP()),FROM_UNIXTIME(mcb_invoices.invoice_due_date))) AS invoice_days_overdue";


        $this->db->join('mcb_clients', 'mcb_clients.client_id = mcb_invoices.client_id', 'left');
        $this->db->join('mcb_contacts', 'mcb_contacts.contact_id = mcb_invoices.contact_id', 'left');
        $this->db->join('mcb_projects AS prj', 'prj.project_id = mcb_invoices.project_id', 'left');
        $this->db->join('mcb_invoice_items', 'mcb_invoice_items.invoice_id = mcb_invoices.invoice_id');
        $this->db->join('mcb_invoice_statuses', 'mcb_invoice_statuses.invoice_status_id = mcb_invoices.invoice_status_id');
        $this->db->join('mcb_invoices AS q', 'q.invoice_id = mcb_invoices.invoice_quote_id', 'left');

        $params['group_by'] = 'invoice_id';

        parent::_prep_params($params);

        $invoices = $this->db->get($this->table_name)->result();

        //echo $this->db->last_query();

        return $invoices;
    }

    public function get_recent_open_quotes($limit = 10) {

        $params = array(
            'limit' => $limit,
            'where' => array(
                'invoice_status_type' => 1,
                'mcb_invoices.invoice_is_quote' => 1
            ),
            'having' => array(
                'invoice_is_overdue' => 0
            )
        );

        /*
         * 
         * Let everyone see all invoices
          if (!$this->session->userdata('global_admin')) {

          $params['where']['mcb_invoices.user_id'] = $this->session->userdata('user_id');

          }
         */
        return $this->get($params);
    }

    public function get_recent_open($limit = 10) {

        $params = array(
            'limit' => $limit,
            'where' => array(
                'invoice_status_type' => 1,
                'mcb_invoices.invoice_is_quote' => 0
            ),
            'having' => array(
                'invoice_is_overdue' => 0
            )
        );

        /*
         * 
         * Let everyone see all invoices
          if (!$this->session->userdata('global_admin')) {

          $params['where']['mcb_invoices.user_id'] = $this->session->userdata('user_id');

          }
         */
        return $this->get($params);
    }

    public function get_recent_pending($limit = 10) {

        $params = array(
            'limit' => $limit,
            'where' => array(
                'invoice_status_type' => 2,
                'mcb_invoices.invoice_is_quote' => 0
            )
        );

        /*
          if (!$this->session->userdata('global_admin')) {

          $params['where']['mcb_invoices.user_id'] = $this->session->userdata('user_id');

          }
         */
        return $this->get($params);
    }

    public function get_recent_closed($limit = 10) {

        $params = array(
            'limit' => $limit,
            'where' => array(
                'invoice_status_type' => 3,
                'mcb_invoices.invoice_is_quote' => 0
            )
        );

        /*
          if (!$this->session->userdata('global_admin')) {

          $params['where']['mcb_invoices.user_id'] = $this->session->userdata('user_id');

          }
         */

        return $this->get($params);
    }

    public function get_recent_overdue($limit = 10) {

        $params = array(
            'limit' => $limit,
            'where' => array(
                'mcb_invoices.invoice_is_quote' => 0
            ),
            'having' => array(
                'invoice_is_overdue' => 1
            )
        );

        /*
          if (!$this->session->userdata('global_admin')) {

          $params['where']['mcb_invoices.user_id'] = $this->session->userdata('user_id');

          }
         */

        return $this->get($params);
    }

    public function get_overdue() {

        $params = array(
            'where' => array(
                'mcb_invoices.invoice_is_quote' => 0
            ),
            'having' => array(
                'invoice_is_overdue' => 1
            )
        );

        /*
          if (!$this->session->userdata('global_admin')) {

          $params['where']['mcb_invoices.user_id'] = $this->session->userdata('user_id');

          }
         */
        return $this->get($params);
    }

    /*
     * Sometimes we want raw data without joining to other tables
     */

    public function get_no_joins($is_quote, $limit = NULL, $offset = NULL) {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        $select = "
			SQL_CALC_FOUND_ROWS
			i.invoice_id id,
			i.client_id c,
			i.project_id p,
			i.contact_id ct,
			i.user_id u,
			i.invoice_number n,
			IF(i.invoice_status_id = 2, IF(i.invoice_due_date < UNIX_TIMESTAMP(), 4, i.invoice_status_id), i.invoice_status_id) s,
			i.invoice_tax_rate_id t,
			CONCAT('$', FORMAT(t.invoice_total,2)) a,
			i.invoice_client_group_id g,
			i.invoice_date_entered e,
			i.invoice_due_date d,
			i.invoice_is_quote q,
			i.invoice_quote_id qi,
			q.invoice_number qn,
			i.invoice_client_order_number po";

        $this->db->select($select, FALSE);
        $this->db->order_by('i.invoice_date_entered DESC, i.invoice_id DESC');
        $this->db->join('mcb_invoice_amounts AS t', 't.invoice_id = i.invoice_id');
        $this->db->join('mcb_invoices AS q', 'q.invoice_id = i.invoice_quote_id', 'left');
        $this->db->where('i.invoice_is_quote', $is_quote);

        if ($limit) {
            $this->db->limit($limit, $offset);
        }

        $query = $this->db->get('mcb_invoices AS i');
        $this->load->model('delivery_dockets/mdl_delivery_dockets');
        $result = $query->result();
        $fin = array();
        if (sizeof($result) > 0) {
            $cnt = 0;
            foreach ($result as $res) {
                
                //if($cnt > 10) break;
                
                $res->indent = 0;
                $res->parent = NULL;
                $fin[] = $res;
                $dockets = $this->mdl_delivery_dockets->get_invoice_dockets($res->id);
                //echo '<pre>'; print_r($dockets);
                if (sizeof($dockets) > 0) {
                    $params = array(
                        'where' => array(
                            'mcb_invoices.invoice_id' => $res->id
                        ),
                        'get_invoice_items' => TRUE,
                        'get_invoice_payments' => TRUE,
                        'get_invoice_tags' => TRUE
                    );
                    $this->load->model('invoices/mdl_invoices');
                    $invoice = $this->mdl_invoices->get($params);
                    foreach ($dockets as $docket) {
                        
                        $docket_items = $this->mdl_delivery_dockets->get_docket_items($docket->docket_id);
                        $temp = new stdClass();

                        $temp->indent = 1;
                        $temp->type = 'docket';
                        $temp->parent = $cnt;
                        $temp->id = $docket->docket_id;

                        $temp->s = ($docket->invoice_sent == '1') ? '6' : '7';
                        $temp->n = $res->n . '.' . $docket->docket_number;
                        $temp->e = $docket->docket_date_entered;
                        if ($docket->client_name) {
                            $temp->c = $docket->client_id;
                        }
                        $temp->p = $docket->project_id;
                        $temp->a = docket_invoice_total($invoice, $docket_items);
                        $cnt++;
                        $fin[] = $temp;
                    }
                }
                $cnt++;
            }
        }
        //exit;
        return $fin;
    }

    public function search_by_product($is_quote, $item_name, $item_description) {

        $this->db->select('mcb_invoices.invoice_id id', FALSE);
        $this->db->join('mcb_invoice_items', 'mcb_invoice_items.invoice_id = mcb_invoices.invoice_id');
        $this->db->where('mcb_invoices.invoice_is_quote', $is_quote);

        $this->db->group_by('id');

        if ($item_name) {

            $this->db->like('item_name', $item_name);
        }

        if ($item_description) {

            $this->db->like('item_description', $item_description);
        }


        $invoices = $this->db->get($this->table_name)->result();

        //echo $this->db->last_query();

        return $invoices;
    }

    public function save($client_id, $client_group_id, $project_id, $contact_id, $date_entered, $invoice_is_quote = 0, $strtotime = TRUE) {

        if ($strtotime) {

            $date_entered = strtotime(standardize_date($date_entered));
        }

        $invoice_due_date = $this->calculate_due_date($date_entered, $invoice_is_quote);

        $db_array = array(
            'client_id' => $client_id,
            'invoice_group_id' => 1,
            'invoice_client_group_id' => $client_group_id,
            'project_id' => $project_id,
            'contact_id' => $contact_id,
            'invoice_tax_rate_id' => $this->mdl_mcb_data->setting('default_tax_rate_id'),
            'invoice_date_entered' => $date_entered,
            'invoice_due_date' => $invoice_due_date,
            'user_id' => $this->session->userdata('user_id'),
            'invoice_status_id' => $this->mdl_mcb_data->setting('default_open_status_id'),
            'invoice_is_quote' => $invoice_is_quote
        );

        $this->db->insert($this->table_name, $db_array);

        $invoice_id = $this->db->insert_id();

        /*
         * Removed use of mcb_invoice_tax_rates
         * Single tax rate is now in mcb_invoices
         */
        /*
          $db_array = array(
          'invoice_id'        =>	$invoice_id,
          'tax_rate_id'       =>	$this->mdl_mcb_data->setting('default_tax_rate_id')
          );

          $default_tax_rate_option = $this->mdl_mcb_data->setting('default_tax_rate_option');

          if ($default_tax_rate_option) {

          $db_array['tax_rate_option'] = $default_tax_rate_option;

          }

          $this->db->insert('mcb_invoice_tax_rates', $db_array);
         */

        $this->save_invoice_history($invoice_id, $this->session->userdata('user_id'), $this->lang->line('created_invoice'));

        return $invoice_id;
    }

    public function modify_invoice_pricing($invoice_id, $old_client_group_id, $new_client_group_id) {

        $this->load->model('client_groups/mdl_client_groups');

        $old_client_group = $this->mdl_client_groups->get_by_id($old_client_group_id);
        $new_client_group = $this->mdl_client_groups->get_by_id($new_client_group_id);

        log_message('INFO', 'Change invoice: ' . $invoice_id . ' pricing from ' . $old_client_group->client_group_name . ' to ' . $new_client_group->client_group_name);

        $price_multiplier = (100 + $old_client_group->client_group_discount_percent) / (100 + $new_client_group->client_group_discount_percent);

        $sql = "
			UPDATE mcb_invoice_items
			   SET item_price = item_price * ?
			 WHERE invoice_id = ?";


        $query = $this->db->query($sql, array($price_multiplier, $invoice_id));


        $this->update_invoice_amounts($invoice_id);
    }

    public function reset_invoice_due_date($invoice_id) {

        $db_array = array(
            'invoice_due_date' => $this->calculate_due_date(time(), 0),
        );

        $this->save_invoice_db_array($invoice_id, $db_array);
    }

    public function save_invoice_db_array($invoice_id, $db_array) {


        $this->db->where('invoice_id', $invoice_id);
        $this->db->update($this->table_name, $db_array);
    }

    public function save_invoice_options($custom_fields = NULL) {


        $invoice_id = uri_assoc('invoice_id');

        $invoice = $this->get_by_id($invoice_id);

        // Can only save details if invoice is open
        if ($invoice->invoice_status_type == 1) {

            /*
             *  Need to update item prices if client pricing changed 
             *  (Was either changed directly or automatically if client was changed)
             */
            $old_client_group_id = $invoice->invoice_client_group_id;
            $new_client_group_id = $this->input->post('invoice_client_group_id');

            if ($old_client_group_id != $new_client_group_id) {
                $this->modify_invoice_pricing($invoice_id, $old_client_group_id, $new_client_group_id);
            }


            $db_array = array(
                'client_id' => $this->input->post('client_id'),
                'project_id' => $this->input->post('project_id'),
                'contact_id' => $this->input->post('contact_id'),
                'invoice_client_group_id' => $new_client_group_id,
                'invoice_date_entered' => strtotime(standardize_date($this->input->post('invoice_date_entered'))),
                'invoice_notes' => $this->input->post('invoice_notes'),
                'user_id' => $this->input->post('user_id'),
                'invoice_number' => $this->input->post('invoice_number'),
                'invoice_client_order_number' => $this->input->post('client_order_number'),
                'invoice_payment_terms' => $this->input->post('invoice_payment_terms')
            );

            if (is_numeric($this->input->post('invoice_tax_rate_id'))) {

                $db_array['invoice_tax_rate_id'] = $this->input->post('invoice_tax_rate_id');
            }

            if ($this->input->post('invoice_due_date')) {

                $db_array['invoice_due_date'] = strtotime(standardize_date($this->input->post('invoice_due_date')));
            }
        }

        if (is_numeric($this->input->post('invoice_status_id'))) {

            $db_array['invoice_status_id'] = $this->input->post('invoice_status_id');
        }


        $this->save_invoice_db_array($invoice_id, $db_array);

        $this->save_invoice_history($invoice_id, $this->session->userdata('user_id'), $this->lang->line('saved_invoice_options'));


        //updating the project specifier value
        $this->updateProjectSpecifier();

        $this->session->set_flashdata('custom_success', $this->lang->line('invoice_options_saved'));
    }

    private function updateProjectSpecifier() {

        $projectid = $this->input->post('project_id');
        $data = array(
            'project_specifier' => $this->input->post('project_specifier')
        );
        $this->db->where('project_id', $projectid);
        //update mcb_projects set project_specifier = '' where project_id = 
        return $this->db->update('mcb_projects', $data);
    }

    public function delete($invoice_id) {

        parent::delete(array('invoice_id' => $invoice_id));

        $this->db->where('invoice_id', $invoice_id);

        $this->db->delete(
                array(
                    'mcb_invoice_items',
                    'mcb_payments',
                    'mcb_invoice_amounts',
                    'mcb_invoice_item_amounts',
                    'mcb_invoice_history'
                )
        );

        //$this->db->query('DELETE FROM mcb_invoice_item_amounts WHERE invoice_item_id NOT IN (SELECT invoice_item_id FROM mcb_invoice_items)');
        //$this->db->query('DELETE FROM mcb_invoice_item_amounts WHERE invoice_item_id NOT IN (SELECT invoice_item_id FROM mcb_invoice_items)');


        $this->save_invoice_history($invoice_id, $this->session->userdata('user_id'), $this->lang->line('deleted_invoice'));
    }

    public function get_logos() {

        $this->load->helper('directory');

        return directory_map('./uploads/invoice_logos');
    }

    public function discount_client_invoice_price($invoice_id, $product_base_price) {

        $params = array(
            'where' => array(
                'mcb_invoices.invoice_id' => $invoice_id
            )
        );

        $price = $product_base_price;

        $invoice = $this->get($params);
        if ($invoice->client_group_discount_percent > 0) {
            $price = $price * (100.0) / (100.0 + ($invoice->client_group_discount_percent));
        }

        return $price;
    }

    public function add_invoice_item($invoice_id, $product_id, $item_name, $item_description, $item_qty, $item_price, $item_index = 9999, $item_date = NULL) {

        $item_date = ($item_date) ? strtotime(standardize_date($item_date)) : time();

        /*
         * 
         */
        if (!$product_id > 0) {

            if ($item_name !== '') {
                $this->load->model('products/mdl_products');
                $product = $this->mdl_products->get_product_by_name($item_name);

                if (isset($product)) {

                    $item_description = $product->product_description;
                    $item_price = $this->discount_client_invoice_price($invoice_id, $product->product_base_price);
                    $product_id = $product->product_id;

                    log_message('INFO', 'Found product id:' . $product_id . ' ' . $product->product_name . ' ' . $product->product_description);
                }
            }
            //get_product_by_name
        }

        $db_array = array(
            'invoice_id' => $invoice_id,
            'product_id' => $product_id,
            'item_name' => $item_name,
            'item_description' => $item_description,
            'item_qty' => $item_qty,
            'item_price' => $item_price,
            'item_index' => $item_index,
            'item_date' => $item_date
        );

        $this->db->insert('mcb_invoice_items', $db_array);

        $invoice_item_id = $this->db->insert_id();

        $this->update_invoice_amounts($invoice_id);


        return $this->get_invoice_item($invoice_item_id);
    }

    public function update_invoice_amounts($invoice_id) {
        $this->load->model('invoices/mdl_invoice_amounts');

        $this->mdl_invoice_amounts->adjust($invoice_id);
    }

    public function update_invoice_item($invoice_id, $item) {
        $this->product_line = $item;
        /*
         * 31-AUG-2011
         * See if the item_name has changed. If so, then do a lookup
         * of the description and price against the product table 
         * 
         */
        $invoice_item_id = $item->invoice_item_id;
        $this->db->where('invoice_item_id', $invoice_item_id);
        $old_item = $this->db->get('mcb_invoice_items')->row();
        $product_id = $item->product_id;

        $new_name = $item->item_name;
        $new_description = $item->item_description;
        $new_price = $item->item_price;
        if ($old_item->item_name !== $new_name) {


            if ($new_name !== '') {
                $this->load->model('products/mdl_products');
                $product = $this->mdl_products->get_product_by_name($new_name);

                if (isset($product)) {
                    //log_message('INFO', 'Found product '.$product->product_name.' '.$product->product_description);
                    $new_description = $product->product_description;

                    $product_id = $product->product_id;

                    $new_price = $this->discount_client_invoice_price($invoice_id, $product->product_base_price);

                    log_message('INFO', 'Name change for invoice item ' . $invoice_item_id . '(' . $old_item->item_name . ' -> ' . $new_name . ') product_id:' . $product_id);
                }
            }
        }

        log_message('INFO', 'update invoice item ' . $invoice_item_id . ' (product_id:' . $product_id . ')');

        $db_set = array(
            'item_type' => $item->item_type,
            'item_qty' => $item->item_qty,
            'item_name' => $new_name,
            'item_description' => $new_description,
            'item_price' => $new_price,
            'product_id' => $product_id,
        );

        $this->db->where('invoice_item_id', $invoice_item_id);
        $this->db->set($db_set);

        $this->db->update('mcb_invoice_items');

        $this->update_invoice_amounts($invoice_id);

        return $this->get_invoice_item($invoice_item_id);
    }

    public function delete_invoice_item($invoice_id, $invoice_item_id) {


        $this->db->where('invoice_item_id', $invoice_item_id);

        $this->db->delete('mcb_invoice_items');

        $this->db->where('invoice_item_id', $invoice_item_id);

        $this->db->delete('mcb_invoice_item_amounts');

        $this->update_invoice_amounts($invoice_id);
    }

    public function set_invoice_items_sort_order($invoice_id, $sort_order) {

        /*
          $this->db->where('invoice_id', $invoice_id);
          <<<<<<< HEAD

          $this->db->set('invoice_items_sort_order', $sort_order);

          =======

          $this->db->set('invoice_items_sort_order', $sort_order);

          >>>>>>> 6ecbeae737edae56ad105d89bcaa5a5547e8bbb3
          $this->db->update($this->table_name);
         */
        $sort = explode(",", $sort_order);


        $data = array();
        foreach ($sort as $k => $v) {
            array_push($data, array('invoice_item_id' => $v, 'item_index' => $k));
        }


        $this->db->update_batch('mcb_invoice_items', $data, 'invoice_item_id');
    }

    public function set_invoice_discount($invoice_id, $invoice_discount) {

        $this->db->where('invoice_id', $invoice_id);

        $this->db->set('invoice_discount', $invoice_discount);

        $this->db->update('mcb_invoice_amounts');

        $this->mdl_invoice_amounts->adjust($invoice_id);
    }

    public function set_invoice_shipping($invoice_id, $invoice_shipping) {

        $this->db->where('invoice_id', $invoice_id);

        $this->db->set('invoice_shipping', $invoice_shipping);

        $this->db->update('mcb_invoice_amounts');

        $this->mdl_invoice_amounts->adjust($invoice_id);
    }

    public function validate() {

        $this->form_validation->set_rules('client_id', $this->lang->line('client'), 'required');
        $this->form_validation->set_rules('user_id', $this->lang->line('created_by'), 'required');
        $this->form_validation->set_rules('invoice_date_entered', $this->lang->line('date_entered'), 'required');
        $this->form_validation->set_rules('invoice_date_closed', $this->lang->line('date_closed'));
        $this->form_validation->set_rules('invoice_number', $this->lang->line('invoice_number'), 'required');
        $this->form_validation->set_rules('invoice_notes', $this->lang->line('notes'));

        return parent::validate();
    }

    public function validate_create() {

        $this->form_validation->set_rules('invoice_date_entered', $this->lang->line('invoice_date'), 'required');
        $this->form_validation->set_rules('client_id', $this->lang->line('client'), 'required');
        //$this->form_validation->set_rules('invoice_group_id', $this->lang->line('invoice_group'), 'required');
        $this->form_validation->set_rules('invoice_is_quote', $this->lang->line('quote_only'));

        return parent::validate();
    }

    public function validate_copy_invoice() {

        $this->form_validation->set_rules('invoice_date_entered', $this->lang->line('invoice_date'), 'required');
        $this->form_validation->set_rules('invoice_group_id', $this->lang->line('invoice_group'), 'required');

        return parent::validate();
    }

    public function copy_invoice($invoice_id, $invoice_date_entered, $create_new_quote, $redirect) {

        $this->db->where($this->primary_key, $invoice_id);


        $query = $this->db->get($this->table_name);

        $db_array = $query->row_array();

        $invoice_is_quote = $db_array['invoice_is_quote'];
        $db_array['invoice_is_quote'] = $create_new_quote;
        $db_array['invoice_quote_id'] = $db_array['invoice_id'];
        $db_array['invoice_status_id'] = $this->mdl_mcb_data->setting('default_open_status_id');
        $db_array['user_id'] = $this->session->userdata('user_id');

        unset($db_array['invoice_id']);

        /*
         * When copying a quote will most likely be changing the client
         * so will reset it here ready for editing
         */
        if ($create_new_quote) {
            $db_array['client_id'] = 0;
            //unset($db_array['client_id']);
            unset($db_array['contact_id']);
            unset($db_array['invoice_notes']);
        }

        $db_array['invoice_date_entered'] = strtotime(standardize_date($invoice_date_entered));
        $db_array['invoice_due_date'] = $this->calculate_due_date($db_array['invoice_date_entered'], $create_new_quote);

        $this->db->insert($this->table_name, $db_array);

        $new_invoice_id = $this->db->insert_id();

        $this->load->model('invoices/mdl_invoice_groups');

        //if ($create_new_quote) {

        /*
         * 02-Mar-2012 always increment invoice number for any new/copy quote or invoice
         */
        $this->mdl_invoice_groups->adjust_invoice_number($new_invoice_id);

        //}

        $sql = "
			INSERT
              INTO mcb_invoice_items (invoice_id, product_id, item_name, item_type, item_description, item_qty, item_price, item_index)
            SELECT ?, product_id, item_name, item_type, item_description, item_qty, item_price, item_index
              FROM mcb_invoice_items
             WHERE invoice_id = ?";

        $this->db->query($sql, array($new_invoice_id, $invoice_id));

        $this->update_invoice_amounts($new_invoice_id);
        //$this->session->set_flashdata('custom_success', 'Quote copied successfully.<br/>');
        if ($redirect) {
            redirect('quotes/edit/invoice_id/' . $new_invoice_id);
        } else {
            return TRUE;
        }
    }

    public function delete_invoice_file($filename) {

        if (file_exists('uploads/temp/' . $filename))
            unlink('uploads/temp/' . $filename);
    }

    public function save_invoice_history($invoice_id, $user_id, $invoice_history_data) {

        if (!$this->mdl_mcb_data->setting('disable_invoice_audit_history')) {

            $db_array = array(
                'invoice_id' => $invoice_id,
                'user_id' => $user_id,
                'invoice_history_date' => time(),
                'invoice_history_data' => $invoice_history_data
            );

            $this->db->insert('mcb_invoice_history', $db_array);
        }
    }

    private function calculate_due_date($date_entered, $invoice_is_quote) {

        if ($invoice_is_quote) {

            return mktime(0, 0, 0, 12, 31, 9999);
        } else {

            return mktime(0, 0, 0, date("m", $date_entered), date("d", $date_entered) + $this->mdl_mcb_data->setting('invoices_due_after'), date("Y", $date_entered));
        }
    }

    public function set_invoice_additional($invoice, $params = NULL) {

        if (isset($params['get_invoice_items'])) {

            $invoice->invoice_items = $this->get_invoice_items($invoice->invoice_id);
        }

        if (isset($params['get_invoice_payments']) && (!$this->mdl_mcb_data->setting('disable_invoice_payments'))) {

            $invoice->invoice_payments = $this->get_invoice_payments($invoice->invoice_id);
        }
        /*
          if (isset($params['get_invoice_tax_rates'])) {

          $invoice->invoice_tax_rates = $this->get_invoice_tax_rates($invoice->invoice_id);

          }


          if (isset($params['get_invoice_item_tax_sums'])) {

          $invoice->invoice_item_tax_sums = $this->get_invoice_item_tax_sums($invoice->invoice_id);

          }
         */

        if (isset($params['get_invoice_tags'])) {

            $invoice->invoice_tags = $this->get_invoice_tags($invoice->invoice_id);
        }

        return $invoice;
    }

    public function get_invoice_item($invoice_item_id) {


        $this->db->select('mcb_invoice_items.*, item_subtotal, item_tax, item_total');
        $this->db->where('mcb_invoice_items.invoice_item_id', $invoice_item_id);

        $this->db->join('mcb_invoice_item_amounts', 'mcb_invoice_item_amounts.invoice_item_id = mcb_invoice_items.invoice_item_id');

        $item = $this->db->get('mcb_invoice_items')->row();
        $item->stock_status = $this->getStockStatus($item);
        return $item;
    }

    private function getStockStatus($itemS) {
        //should be unique for each product
        $this->stock_msg = '';
        $this->stock_color = array();
        $popHtml = '<div class="inv-detail">';
        $popHtml .= '<h2>' . $itemS->item_name . '</h2><br/>';

        if ((int) $itemS->product_id > 0) {
            $popHtml .= '<div class="prod-link"><a href="' . site_url() . '/products/form/product_id/' . $itemS->product_id . '" target="_blank">Add Inventory | Edit Product</a></div>';
        }

        $popHtml .= '<h3>Inventory Items:</h3>';
        $popHtml .= '<table class="table table-bordered order-prod-list">';

        $popHtml .= '<thead>';
        $popHtml .= '<tr>';
        $popHtml .= '<td>S.N</td>';
        $popHtml .= '<td>Inventory Item Name</td>';
        $popHtml .= '<td>Qty</td>';
        $popHtml .= '<td>Required Qty</td>';
        $popHtml .= '</tr>';
        $popHtml .= '</thead>';
        $popHtml .= '<tbody>';
        //get the number of inventory items
        $this->load->model('inventory/mdl_inventory_item');
        $lesserQtyInventory = 999999999999999;
        if ((int) $itemS->product_id > 0) {
            $inventory_items = $this->mdl_inventory_item->getInventoryItems($itemS->product_id);
            //check if the particular invoice_item_id has already inserted qty which is quite more, going to reduct it assume
            $invoice_item_id_qty = $this->mdl_inventory_item->getInvoiceItemIdQtyAlredyInserted($itemS->invoice_item_id);
            $lesserQtyInventory = 999999999999999;
            if (sizeof($inventory_items) > 0) {
                $cnt = 1;
                foreach ($inventory_items as $item) {

                    $popHtml .= '<tr><td>' . $cnt . '</td>';
                    $popHtml .= '<td>' . $item->name . '</td>';
                    $popHtml .= '<td>' . floor($item->qty) . '</td>';
                    $popHtml .= '<td>' . $item->inventory_qty . '</td>';

                    $numProdFormed = (int) $item->qty / $item->inventory_qty;
                    if ($lesserQtyInventory > $numProdFormed) {
                        $lesserQtyInventory = $numProdFormed;
                    }
                    // {$item->qty: total_quantity_of_inventory_item} :: {$item_qty: user_submitted_number_of_products} :: {relation between unit product and that particular inventory item} 
                    if (((float) ($item->qty + $invoice_item_id_qty)) - ((float) $itemS->item_qty * $item->inventory_qty) < LOW_STOCK) {
                        $this->stock_msg .= '<p>' . $item->name . " stock quantity is low. Total available quantity is " . $item->qty . '</p>';
                        $this->stock_color[] = 'yellow';
                    } elseif (((float) $itemS->item_qty * $item->inventory_qty) > (float) ($item->qty + $invoice_item_id_qty)) {
                        $this->stock_msg .= "<p>Not enough " . $item->name . " inventory item for the product " . $item->item_name . '</p>';
                        $this->stock_color[] = 'red';
                    } else {
                        $this->stock_msg .= '<p>' . $item->name . " stock quantity is " . $item->qty . '</p>';
                        $this->stock_color[] = 'green';
                    }
                    $popHtml .= '</tr>';
                    $cnt++;
                }
            } else {
                $lesserQtyInventory = 0;
                $popHtml .= '<tr><td colspan="4">There are no inventory items.</td></tr>';
                $this->stock_msg .= '<p>There are no inventory items for the product ' . $itemS->item_name . '</p>';
                $this->stock_color[] = 'red';
            }
        } else {

            $popHtml .= '<tr><td colspan="4">There are no inventory items.</td></tr>';

            $this->stock_msg .= '<p>Product not defined ' . $itemS->item_name . '</p>';
            $this->stock_color[] = 'yellow';
        }
        $this->mdl_inventory_item->stock_color = $this->stock_color;
        $this->mdl_inventory_item->stock_msg = $this->stock_msg;

        $res = $this->mdl_inventory_item->prepareResult();

        $popHtml .= '</tbody></table></div>';

        $stkqty = ((float) $lesserQtyInventory > 0 && $lesserQtyInventory != '999999999999999') ? floor($lesserQtyInventory) : '0';
        return '<a data-color="' . $res['color'] . '" data-effect="mfp-zoom-in" data-message=\'' . $popHtml . '\' class="open-popup help ' . $res['color'] . '"><span>' . $stkqty . '</span></a>';
    }

    public function get_invoice_items($invoice_id) {

        //$this->db->select('mcb_invoice_items.invoice_item_id, product_id, item_name, item_description, item_qty, item_price, item_subtotal, item_tax, item_total');

        $this->db->where('mcb_invoice_items.invoice_id', $invoice_id);

        $this->db->join('mcb_invoice_item_amounts', 'mcb_invoice_item_amounts.invoice_item_id = mcb_invoice_items.invoice_item_id');

        //$this->db->join('mcb_tax_rates', 'mcb_tax_rates.tax_rate_id = mcb_invoice_items.tax_rate_id', 'LEFT');


        $this->db->order_by('item_index, mcb_invoice_items.invoice_item_id');

        $this->db->order_by('item_index, mcb_invoice_items.invoice_item_id');

        $items = $this->db->get('mcb_invoice_items')->result();
        $fin = array();
        if (sizeof($items) > 0) {
            foreach ($items as $item) {
                $item->stock_status = $this->getStockStatus($item);
                $fin[] = $item;
            }
        }
        return $fin;
    }

    public function get_invoice_item_amounts($invoice_item_id) {

        $this->db->where('invoice_item_id', $invoice_item_id);
        $this->db->select('invoice_item_id, item_subtotal, item_tax, item_total');

        $query = $this->db->get('mcb_invoice_item_amounts');

        if ($query->num_rows() > 0) {

            return $query->row();
            ;
        }
    }

    public function get_invoice_amounts($invoice_id) {
        $this->db->where('invoice_id', $invoice_id);
        $this->db->select('invoice_item_subtotal, invoice_item_tax, invoice_total');

        $invoice_amounts = $this->db->get('mcb_invoice_amounts')->row();
        $invoice_amounts->invoice_item_subtotal = display_currency($invoice_amounts->invoice_item_subtotal);
        $invoice_amounts->invoice_item_tax = display_currency($invoice_amounts->invoice_item_tax);
        $invoice_amounts->invoice_total = display_currency($invoice_amounts->invoice_total);

        return $invoice_amounts;
    }

    public function get_invoice_payments($invoice_id) {

        $this->load->model('payments/mdl_payments');

        $params = array(
            'where' => array(
                'mcb_payments.invoice_id' => $invoice_id
            )
        );

        return $this->mdl_payments->get($params);
    }

    /*
      public function get_invoice_tax_rates($invoice_id) {

      $this->load->model('tax_rates/mdl_tax_rates');

      return $this->mdl_tax_rates->get_invoice_tax_rates($invoice_id);

      }

      public function get_invoice_item_tax_sums($invoice_id) {

      $this->db->select('tax_rate_name, tax_rate_percent, SUM(item_tax) AS tax_rate_sum');

      $this->db->group_by('mcb_tax_rates.tax_rate_id');

      $this->db->join('mcb_invoice_item_amounts', 'mcb_invoice_item_amounts.invoice_item_id = mcb_invoice_items.invoice_item_id');

      $this->db->join('mcb_tax_rates', 'mcb_tax_rates.tax_rate_id = mcb_invoice_items.tax_rate_id', 'LEFT');

      $this->db->where('mcb_invoice_items.invoice_id', $invoice_id);

      return $this->db->get('mcb_invoice_items')->result();


      }
     */

    public function get_invoice_tags($invoice_id) {

        if ($this->mdl_mcb_data->setting('version') >= '0.8') {

            $this->load->model('invoices/mdl_invoice_tags');

            return $this->mdl_invoice_tags->get_tags($invoice_id);
        }
    }

    public function get_invoice_history($invoice_id) {

        $this->load->model('invoices/mdl_invoice_history');

        $params = array(
            'where' => array(
                'mcb_invoice_history.invoice_id' => $invoice_id
            )
        );

        return $this->mdl_invoice_history->get($params);
    }

    public function get_total_invoice_balance($user_id = NULL) {

        $this->db->select('SUM(invoice_balance) AS total_invoice_balance');

        $this->db->join('mcb_invoices', 'mcb_invoices.invoice_id = mcb_invoice_amounts.invoice_id');

        $this->db->where('mcb_invoices.invoice_is_quote', 0);

        if ($user_id) {

            $this->db->where('mcb_invoices.user_id', $user_id);
        }

        return $this->db->get('mcb_invoice_amounts')->row()->total_invoice_balance;
    }

    public function getQuoteInternalFromInvoiceId($invoice_id) {
        $this->db->select('i.note,i.id,i.created_date,u.username');
        $where = array(
            'i.invoice_id' => $invoice_id,
            'i.status' => '1'
        );
        $this->db->where($where);
        $this->db->join('mcb_users as u', 'u.user_id = i.userid', 'left');
        $this->db->order_by('i.created_date', 'DESC');
        $q = $this->db->get('mcb_quote_internal as i');
        $result = $q->result();
        return $result;
    }

    public function addinternalnote($invoice_id) {
        $data = array(
            'invoice_id' => $invoice_id,
            'note' => $this->input->post('internalnotes'),
            'userid' => $this->session->userdata('user_id'),
            'created_date' => date('Y-m-d H:i:s')
        );
        return $this->db->insert('mcb_quote_internal', $data);
    }

    public function deleteinternalnote($noteid) {
        $this->db->where('id', $noteid);
        $data = array(
            'status' => '0'
        );
        return $this->db->update('mcb_quote_internal', $data);
    }

}

?>