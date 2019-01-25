<?php

(defined('BASEPATH')) OR exit('No direct script access allowed');

class Mdl_Clients extends MY_Model {

    public function __construct() {

        parent::__construct();

        $this->table_name = 'mcb_clients';

        $this->select_fields = "
		SQL_CALC_FOUND_ROWS
		mcb_clients.*,
		mcb_client_groups.client_group_name,
		mcb_clients.client_name value,  /*for jqueryui autocomplete*/
		IFNULL(parent_suppliers.client_id, mcb_clients.client_id) supplier_id,
		IFNULL(parent_suppliers.client_name, mcb_clients.client_name) supplier_name,
		t.total_due,
		mcb_currencies.*";

        // subquery to get total amount owing for client - Emailed invoices only (which included Overdue calculation)
        $totals_subquery = "(SELECT c.client_id, SUM(a.invoice_total) total_due
               FROM mcb_invoices i
               JOIN mcb_clients c ON c.client_id = i.client_id
               JOIN mcb_invoice_amounts a ON a.invoice_id = i.invoice_id
              WHERE i.invoice_is_quote = 0
                AND i.invoice_status_id = 2
              GROUP BY i.client_id) AS t";


        /*
          (SELECT SUM(invoice_total) FROM mcb_invoice_amounts WHERE invoice_id IN (SELECT invoice_id FROM mcb_invoices WHERE client_id = join_client_id AND invoice_is_quote = 0)) AS client_total_invoice,
          IFNULL((SELECT SUM(payment_amount) FROM mcb_payments JOIN mcb_invoices ON mcb_invoices.invoice_id = mcb_payments.invoice_id WHERE mcb_invoices.client_id = mcb_clients.client_id AND invoice_is_quote = 0), 0.00) AS client_total_payment,
          (SELECT ROUND(client_total_invoice - client_total_payment, 2)) AS client_total_balance";
         */
        $this->joins = array(
            'mcb_currencies' => 'mcb_currencies.currency_id = mcb_clients.client_currency_id',
            'mcb_clients AS parent_suppliers' => array(
                'parent_suppliers.client_id = mcb_clients.parent_client_id',
                'left'
            ),
            'mcb_client_groups' => array(
                'mcb_client_groups.client_group_id = mcb_clients.client_group_id',
                'left'
            ),
            $totals_subquery => array(
                't.client_id = mcb_clients.client_id',
                'left'
            )
        );

        /*
          $this->joins = array(
          'mcb_client_groups' =>	'mcb_client_groups.client_group_id = mcb_clients.client_group_id'
          );
         */
        $this->primary_key = 'mcb_clients.client_id';

        $this->order_by = 'mcb_clients.client_name';

        $this->custom_fields = $this->mdl_fields->get_object_fields(3);
    }

    public function get($params = NULL) {

        /*
         *  Always remove psuedo clients if no where clause specified
         *  (Note that when editing products we might want them)
         */
        if (!isset($params['where'])) {

            $params['where'] = array('mcb_clients.client_id >' => 0);
        }

        $clients = parent::get($params);

        if (is_array($clients)) {

            if ($this->mdl_mcb_data->setting('version') > '0.8.2') {

                if (isset($params['set_client_data'])) {

                    foreach ($clients as $client) {

                        $this->db->where('client_id', $client->client_id);

                        $mcb_client_data = $this->db->get('mcb_client_data')->result();

                        foreach ($mcb_client_data as $client_data) {

                            $client->{$client_data->mcb_client_key} = $client_data->mcb_client_value;
                        }
                    }
                }
            }
        } else {

            if ($this->mdl_mcb_data->setting('version') > '0.8.2') {

                if (isset($params['set_client_data'])) {

                    $this->db->where('client_id', $clients->client_id);

                    $mcb_client_data = $this->db->get('mcb_client_data')->result();

                    foreach ($mcb_client_data as $client_data) {

                        $clients->{$client_data->mcb_client_key} = $client_data->mcb_client_value;
                    }
                }
            }
        }

        return $clients;
    }

    public function get_by_name($client_name) {

        $params = array(
            'where' => array(
                'mcb_clients.client_name' => $client_name,
            ),
            'return_row' => TRUE
        );

        return $this->get($params);
    }

    public function get_active() {

        $params = array(
            'where' => array(
                'mcb_clients.client_id >' => 0,
                'mcb_clients.client_active' => 1,
            )
        );

        return $this->get($params);
    }

    public function get_active_suppliers() {


        $params = array(
            'where' => array(
                'mcb_clients.client_active' => 1,
                'mcb_clients.client_is_supplier' => 1
            )
        );

        $res = $this->get($params);
        return $res;
    }

    public function get_active_suppliers_with_product() {

        $sql = "SELECT SQL_CALC_FOUND_ROWS mcb_clients.*, mcb_client_groups.client_group_name, mcb_clients.client_name value, /*for jqueryui autocomplete*/ IFNULL(parent_suppliers.client_id, mcb_clients.client_id) supplier_id, IFNULL(parent_suppliers.client_name, mcb_clients.client_name) supplier_name, t.total_due, mcb_currencies.* FROM (mcb_clients) JOIN mcb_currencies ON mcb_currencies.currency_id = mcb_clients.client_currency_id LEFT JOIN mcb_clients AS parent_suppliers ON parent_suppliers.client_id = mcb_clients.parent_client_id LEFT JOIN mcb_client_groups ON mcb_client_groups.client_group_id = mcb_clients.client_group_id LEFT JOIN (SELECT c.client_id, SUM(a.invoice_total) total_due FROM mcb_invoices i JOIN mcb_clients c ON c.client_id = i.client_id JOIN mcb_invoice_amounts a ON a.invoice_id = i.invoice_id WHERE i.invoice_is_quote = 0 AND i.invoice_status_id = 2 GROUP BY i.client_id) AS t ON t.client_id = mcb_clients.client_id WHERE `mcb_clients`.`client_active` = 1 AND `mcb_clients`.`client_is_supplier` = 1 AND mcb_clients.client_id in (select distinct supplier_id from mcb_products as p where p.product_active = '1') ORDER BY mcb_clients.client_name";

        //$sql = "select * from mcb_clients as c where c.client_active = '1' and c.client_is_supplier = '1' and c.client_id in (select distinct supplier_id from mcb_products as p where p.product_active = '1')";

        $q = $this->db->query($sql);
        return $q->result();
    }

    public function get_parent_suppliers() {


        $params = array(
            'select' => "
				SQL_CALC_FOUND_ROWS
				DISTINCT
				IFNULL(parent_suppliers.client_id, mcb_clients.client_id) supplier_id,
                                IFNULL(parent_suppliers.show_in_product_page, mcb_clients.show_in_product_page) show_in_product_page,
		        IFNULL(parent_suppliers.client_name, mcb_clients.client_name) supplier_name",
            'order_by' => 'supplier_name',
            'where' => array(
                //'mcb_clients.client_active'	=>	1,
                'IFNULL(parent_suppliers.client_id, mcb_clients.client_id) >' => 0,
                'mcb_clients.client_is_supplier' => 1
            )
        );



        return $this->get($params);
    }

    public function validate() {

        $this->form_validation->set_rules('client_active', $this->lang->line('client_active'));
        $this->form_validation->set_rules('client_name', $this->lang->line('client_name'), 'required');
        $this->form_validation->set_rules('client_long_name', $this->lang->line('client_long_name'));
        $this->form_validation->set_rules('client_group_id', $this->lang->line('client_group_id'), 'required');
        $this->form_validation->set_rules('client_tax_id', $this->lang->line('tax_id_number'));
        $this->form_validation->set_rules('client_address', $this->lang->line('street_address'));
        $this->form_validation->set_rules('client_address_2', $this->lang->line('street_address_2'));
        $this->form_validation->set_rules('client_city', $this->lang->line('city'));
        $this->form_validation->set_rules('client_state', $this->lang->line('state'));
        $this->form_validation->set_rules('client_zip', $this->lang->line('zip'));
        $this->form_validation->set_rules('client_country', $this->lang->line('country'));
        $this->form_validation->set_rules('client_phone_number', $this->lang->line('phone_number'));
        $this->form_validation->set_rules('client_fax_number', $this->lang->line('fax_number'));
        $this->form_validation->set_rules('client_mobile_number', $this->lang->line('mobile_number'));
        $this->form_validation->set_rules('client_email_address', $this->lang->line('email_address'), 'valid_email');
        $this->form_validation->set_rules('client_web_address', $this->lang->line('web_address'));
        $this->form_validation->set_rules('client_notes', $this->lang->line('notes'));
        $this->form_validation->set_rules('client_currency_id', $this->lang->line('currency'));
        $this->form_validation->set_rules('client_tax_rate_id', $this->lang->line('tax_rate'));
        $this->form_validation->set_rules('client_is_supplier', $this->lang->line('client_is_supplier'));
        $this->form_validation->set_rules('parent_client_id', $this->lang->line('parent_client'));

        foreach ($this->custom_fields as $custom_field) {

            $this->form_validation->set_rules($custom_field->column_name, $custom_field->field_name);
        }

        return parent::validate($this);
    }

    public function delete($client_id) {


        /* Delete the client record */

        parent::delete(array('client_id' => $client_id));

        /* Delete any related contacts */

        $this->db->where('client_id', $client_id);

        $this->db->delete('mcb_contacts');
    }

    public function save() {

        $db_array = parent::db_array();
        $client_name = $this->input->post('client_name');

        if (!$this->input->post('client_active')) {

            $db_array['client_active'] = 0;
        }

        if ($this->input->post('parent_client_id') == 0) {

            $db_array['parent_client_id'] = null;

            //unset($db_array['parent_client_id']);
        }

        if (!$this->input->post('client_is_supplier')) {

            $db_array['client_is_supplier'] = 0;
        } else {
            $db_array['client_is_supplier'] = 1;
        }

        //check if the client_name already exists
        $client_name_exists = $this->checkIfClientExistsByName($client_name);
        
        if(uri_assoc('client_id') > 0 ){
            $client_name_exists = uri_assoc('client_id');
        }
        if ($client_name_exists) {
            //gonna update
            $this->db->where('client_id', $client_name_exists);
            $data = array(
                'client_active' => ($this->input->post('client_active') != NULL) ? '1' : '0',
                'client_name' => $this->input->post('client_name'),
                'parent_client_id' => $this->input->post('parent_client_id'),
                'client_long_name' => $this->input->post('client_long_name'),
                'client_group_id' => $this->input->post('client_group_id'),
                'client_currency_id' => $this->input->post('client_currency_id'),
                'client_tax_rate_id' => $this->input->post('client_tax_rate_id'),
                'client_tax_id' => $this->input->post('client_tax_id'),
                'client_address' => $this->input->post('client_address'),
                'client_address_2' => $this->input->post('client_address_2'),
                'client_city' => $this->input->post('client_city'),
                'client_state' => $this->input->post('client_state'),
                'client_zip' => $this->input->post('client_zip'),
                'client_country' => $this->input->post('client_country'),
                'client_phone_number' => $this->input->post('client_phone_number'),
                'client_fax_number' => $this->input->post('client_fax_number'),
                'client_mobile_number' => $this->input->post('client_mobile_number'),
                'client_email_address' => $this->input->post('client_email_address'),
                'client_web_address' => $this->input->post('client_web_address'),
                'client_notes' => $this->input->post('client_notes'),
                'client_is_supplier' => ($this->input->post('client_is_supplier') != NULL) ? '1' : '0',
                'show_in_product_page' => ($this->input->post('show_in_product_page') != NULL) ? '1' : '0'
            );
            if ($this->db->update('mcb_clients', $data)) {
                $this->session->set_flashdata('custom_success', 'Client successfully updated.');
            } else {
                $this->session->set_flashdata('custom_error', 'Error while updating client. Please try again later.');
            }
        } else {
            //gonna add
            $data = array(
                'client_active' => ($this->input->post('client_active') != NULL) ? '1' : '0',
                'client_name' => $this->input->post('client_name'),
                'parent_client_id' => $this->input->post('parent_client_id'),
                'client_long_name' => $this->input->post('client_long_name'),
                'client_group_id' => $this->input->post('client_group_id'),
                'client_currency_id' => $this->input->post('client_currency_id'),
                'client_tax_rate_id' => $this->input->post('client_tax_rate_id'),
                'client_tax_id' => $this->input->post('client_tax_id'),
                'client_address' => $this->input->post('client_address'),
                'client_address_2' => $this->input->post('client_address_2'),
                'client_city' => $this->input->post('client_city'),
                'client_state' => $this->input->post('client_state'),
                'client_zip' => $this->input->post('client_zip'),
                'client_country' => $this->input->post('client_country'),
                'client_phone_number' => $this->input->post('client_phone_number'),
                'client_fax_number' => $this->input->post('client_fax_number'),
                'client_mobile_number' => $this->input->post('client_mobile_number'),
                'client_email_address' => $this->input->post('client_email_address'),
                'client_web_address' => $this->input->post('client_web_address'),
                'client_notes' => $this->input->post('client_notes'),
            );
            if ($this->db->insert('mcb_clients', $data)) {
                $this->session->set_flashdata('custom_success', 'Client successfully added.');
            } else {
                $this->session->set_flashdata('custom_error', 'Error while adding client. Please try again later.');
            }
        }

        //parent::save($db_array, uri_assoc('client_id'));
    }

    private function checkIfClientExistsByName($client_name) {
        //column index is there, client active or not doesn't mean anything
        $this->db->select('client_id');
        $this->db->where('client_name', $client_name);
        $q = $this->db->get('mcb_clients');
        $row = $q->row();
        if (isset($row->client_id) && (int) $row->client_id > 0) {
            return $row->client_id;
        }
        return FALSE;
    }
    
    function getDocketAmount($client_id) {
        
        $qry = 'SELECT dd.price_with_tax, dd.invoice_id, i.client_id '
                . 'FROM (mcb_delivery_dockets as dd '
                . 'INNER JOIN mcb_invoices as i ON dd.invoice_id = i.invoice_id) '
                . 'WHERE (i.client_id = '.$client_id.') AND (dd.paid_status = "0") AND i.invoice_is_quote = "0" AND dd.invoice_sent = "1"';
        
        
//        if($client_id == '43'){
//            echo '<pre>'; print_r($qry); exit;
//        }
            
        $r = $this->db->query($qry)->result_array();
        $key = 'price_with_tax';
        $delAmt = array_sum(array_column($r,$key));
        return $delAmt;
    }
    
    function getInvoiceAmount($client_id){
        $sql = "select * from mcb_invoices as i where i.client_id = '".$client_id."' AND i.invoice_status_id >= 4 AND invoice_is_quote = '0'";
    }
    
    
    function get_client_order($client_id) {
        $sql = "SELECT SQL_CALC_FOUND_ROWS mcb_orders.*, "
                . "IFNULL(length(mcb_orders.order_notes), 0) order_has_notes, "
                . "con.contact_name, con.email_address AS contact_email_address, tax_rate_percent, t.tax_rate_name, "
                . "CONCAT(FORMAT(t.tax_rate_percent, 0), '% ', t.tax_rate_name) tax_rate_percent_name, "
                . "FORMAT(order_sub_total, 2) order_sub_total, FORMAT(order_sub_total * IFNULL(t.tax_rate_percent, 0)/100, 2) tax_total, "
                . "FORMAT(order_sub_total * (1 + IFNULL(t.tax_rate_percent, 0)/100), 2) order_total, mcb_invoices.invoice_number, "
                . "FORMAT(inv_order_sub_total, 2) inv_order_sub_total, FORMAT(inv_order_sub_total * IFNULL(t.tax_rate_percent, 0)/100, 2) inv_tax_total, "
                . "FORMAT(inv_order_sub_total * (1 + IFNULL(t.tax_rate_percent, 0)/100), 2) inv_order_total, "
                . "mcb_invoices.invoice_number, mcb_invoices.invoice_is_quote, mcb_clients.*, mcb_currencies.*, prj.project_id, prj.project_name, mcb_users.username, "
                . "mcb_users.last_name AS from_last_name, mcb_users.first_name AS from_first_name, mcb_users.email_address AS from_email_address, "
                . "mcb_users.mobile_number AS from_mobile_number, mcb_users.company_name AS from_company_name, "
                . "mcb_invoice_statuses.invoice_status AS order_status, mcb_invoice_statuses.invoice_status_type AS order_status_type "
                . "FROM (mcb_orders) "
                . "LEFT JOIN (SELECT order_id, SUM(item_qty * item_supplier_price) order_sub_total FROM `mcb_order_items` GROUP BY order_id) AS i ON i.order_id = mcb_orders.order_id "
                . "LEFT JOIN (SELECT order_id, SUM(item_qty * item_supplier_price) inv_order_sub_total FROM `mcb_order_inventory_items` GROUP BY order_id) AS i2 ON i2.order_id = mcb_orders.order_id "
                . "JOIN mcb_clients ON mcb_clients.client_id = mcb_orders.supplier_id "
                . "LEFT JOIN mcb_contacts AS con ON con.contact_id = mcb_orders.contact_id "
                . "JOIN mcb_invoice_statuses ON mcb_invoice_statuses.invoice_status_id = mcb_orders.order_status_id "
                . "JOIN mcb_users ON mcb_users.user_id = mcb_orders.user_id "
                . "LEFT JOIN mcb_invoices ON mcb_invoices.invoice_id = mcb_orders.invoice_id "
                . "LEFT JOIN mcb_projects AS prj ON prj.project_id = mcb_orders.project_id "
                . "LEFT JOIN mcb_tax_rates AS t ON t.tax_rate_id = mcb_orders.order_tax_rate_id "
                . "JOIN mcb_currencies ON mcb_currencies.currency_id = mcb_clients.client_currency_id "
                . "WHERE `mcb_orders`.`supplier_id` = '".$client_id."' GROUP BY mcb_orders.order_id "
                . "ORDER BY FROM_UNIXTIME(mcb_orders.order_date_entered) DESC, mcb_orders.order_number DESC";
        // echo $sql;
        $client_orders = $this->db->query($sql)->result();
        foreach ($client_orders as $value_order) {
            if($value_order->is_inventory_supplier == '1'){
                $value_order->order_total = $value_order->inv_order_total;
                $fin[] = $value_order; 
            } else {
                $fin[] = $value_order;
            }
        }
        return $fin;
    }
    
    public function deleteClient($client_id) {
        $this->db->where('client_id', $client_id);
        return $this->db->delete('mcb_clients');
    }

    public function delete_docket($docket_id) {

        // delete child docket items and address
        parent::delete(array('docket_id' => $docket_id));

        $this->db->where('docket_id', $docket_id);
        $this->db->delete('mcb_delivery_dockets');
    }

    public function get_Row($tbl_name, $id_edt) {

        $this->db->where($id_edt);
        $q = $this->db->get($tbl_name);
        $Res = $q->row();
        return $Res;
    }

    public function get_Where($tbl_name, $id_edt) {

        $this->db->where($id_edt);
        $q = $this->db->get($tbl_name);
        $Res = $q->result_array();
        return $Res;
    }

    public function update($tbl_name, $data, $id_update) {

        $this->load->database();

        $this->db->where($id_update);
        $this->db->update($tbl_name, $data);
    }

}

?>