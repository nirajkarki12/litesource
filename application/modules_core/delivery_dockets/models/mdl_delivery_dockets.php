<?php

(defined('BASEPATH')) OR exit('No direct script access allowed');

class Mdl_Delivery_Dockets extends MY_Model {
    /*
      SELECT SQL_CALC_FOUND_ROWS
      mcb_delivery_dockets.*,

      con.contact_name,
      con.email_address AS contact_email_address,

      mcb_invoices.invoice_number,
      mcb_invoices.invoice_is_quote,
      mcb_invoices.invoice_client_order_number,
      mcb_clients.*,
      prj.project_id,
      prj.project_name,
      mcb_users.username,
      mcb_users.last_name AS from_last_name,
      mcb_users.first_name AS from_first_name,
      mcb_users.email_address AS from_email_address,
      mcb_users.mobile_number AS from_mobile_number,
      mcb_users.company_name AS from_company_name
      FROM
      mcb_delivery_dockets
      JOIN mcb_invoices ON mcb_invoices.invoice_id = mcb_delivery_dockets.invoice_id
      JOIN mcb_clients ON	mcb_clients.client_id = mcb_invoices.client_id
      LEFT JOIN mcb_contacts AS con ON con.contact_id = mcb_invoices.contact_id
      JOIN mcb_users ON mcb_users.user_id = mcb_invoices.user_id
      LEFT JOIN mcb_projects AS prj ON prj.project_id = mcb_invoices.project_id
     *
     */

    public function __construct() {

        parent::__construct();

        $this->table_name = 'mcb_delivery_dockets';

        $this->primary_key = 'mcb_delivery_dockets.docket_id';

        //$this->order_by = 'mcb_orders.order_date_entered DESC, mcb_orders.order_id';
        $this->order_by = 'FROM_UNIXTIME(mcb_delivery_dockets.docket_date_entered) DESC, mcb_invoices.invoice_number DESC';


        $this->select_fields = "
		SQL_CALC_FOUND_ROWS
		mcb_delivery_dockets.*,

		con.contact_name,
		con.email_address AS contact_email_address,

		mcb_invoices.invoice_number,
		mcb_invoices.invoice_is_quote,
		mcb_invoices.invoice_client_order_number,
        mcb_clients.*,
		prj.project_id,
		prj.project_name,
		mcb_users.username,
		mcb_users.last_name AS from_last_name,
	    mcb_users.first_name AS from_first_name,
		mcb_users.email_address AS from_email_address,
		mcb_users.mobile_number AS from_mobile_number,
		mcb_users.company_name AS from_company_name";

        $this->joins = array(
            'mcb_invoices' => 'mcb_invoices.invoice_id = mcb_delivery_dockets.invoice_id',
            'mcb_clients' => 'mcb_clients.client_id = mcb_invoices.client_id',
            'mcb_contacts AS con' => array(
                'con.contact_id = mcb_invoices.contact_id',
                'left'
            ),
            'mcb_users' => 'mcb_users.user_id = mcb_invoices.user_id',
            'mcb_projects AS prj' => array(
                'prj.project_id = mcb_invoices.project_id',
                'left'
            ),
        );


        $this->limit = $this->mdl_mcb_data->setting('results_per_page');
    }

    public function get($params = NULL) {

        //$params['debug'] = TRUE;

        $dockets = parent::get($params);

        return $dockets;
    }
    
    public function validate() {

        $this->form_validation->set_rules('invoice_id', $this->lang->line('invoice_id'));
        //$this->form_validation->set_rules('order_tax_rate_id', $this->lang->line('order_tax_rate_id'), 'required');
        $this->form_validation->set_rules('docket_date_entered', $this->lang->line('docket_date_entered'), 'required');


        return parent::validate($this);
    }

    public function validate_create() {

        $this->form_validation->set_rules('invoice_id', $this->lang->line('invoice_id'), 'required');
        $this->form_validation->set_rules('docket_date_entered', $this->lang->line('docket_date_entered'), 'required');

        return parent::validate($this);
    }

    public function get_latest_docket_address_id($invoice_id) {

        $this->db->select_max('docket_address_id', 'latest_address_id');
        $this->db->where('invoice_id', $invoice_id);
        $query = $this->db->get($this->table_name);

        if ($query->num_rows() == 01) {
            $row = $query->row();
            return $row->latest_address_id;
        }
    }

    public function get_docket_address($docket_address_id) {

        $this->load->model('addresses/mdl_addresses');
        return $this->mdl_addresses->get_by_id($docket_address_id);
        
    }

    public function save() {

        $docket_id = uri_assoc('docket_id');

        $docket_date_entered = strtotime(standardize_date($this->input->post('docket_date_entered')));
        $invoice_date = strtotime(standardize_date($this->input->post('invoice_date')));

        $db_array = array(
            'invoice_date'=>$invoice_date,
            'docket_due_date' => $invoice_date + ( 24 * 60 * 60 * 30),
        );
        if($docket_date_entered != NULL){
            $db_array['docket_date_entered'] = $docket_date_entered;
        }
        $this->db->where('docket_id', $docket_id);
        $this->db->update($this->table_name, $db_array);

        $this->session->set_flashdata('custom_success', $this->lang->line('docket_details_saved'));
    }

    public function save_delivery_address($docket_id, $address_id) {


        $db_array = array(
            'docket_address_id' => $address_id,
        );

        $this->db->where('docket_id', $docket_id);
        $this->db->update($this->table_name, $db_array);

        $this->session->set_flashdata('custom_success', $this->lang->line('docket_details_saved'));
    }

    public function delete($docket_id) {

        // delete child docket items and address

        parent::delete(array('docket_id' => $docket_id));


        $this->db->where('docket_id', $docket_id);
        $this->db->delete('mcb_delivery_docket_items');
    }
    
    
    function get_delvery_dockets_by_invoice_id($invoice_id) {
        
        $sql = "SELECT SQL_CALC_FOUND_ROWS
            mcb_delivery_dockets.*, con.contact_name, con.email_address AS contact_email_address, 
            mcb_invoices.invoice_number, mcb_invoices.invoice_is_quote, 
            mcb_invoices.invoice_client_order_number, mcb_clients.*, prj.project_id, prj.project_name,
            mcb_users.username, mcb_users.last_name AS from_last_name, mcb_users.first_name AS from_first_name, 
            mcb_users.email_address AS from_email_address, mcb_users.mobile_number AS from_mobile_number, 
            mcb_users.company_name AS from_company_name, 
            mcb_currencies.currency_name, mcb_currencies.currency_symbol_left, mcb_currencies.currency_symbol_right,
            (SELECT ROUND((mcb_delivery_dockets.price_with_tax - SUM(mcb_delivery_docket_payment.amount_entered)),2) FROM mcb_delivery_docket_payment 
            WHERE mcb_delivery_docket_payment.docket_id = mcb_delivery_dockets.docket_id)  AS with_tax_owing_amount  
            FROM (mcb_delivery_dockets)
            JOIN mcb_invoices ON mcb_invoices.invoice_id = mcb_delivery_dockets.invoice_id
            LEFT JOIN mcb_clients ON mcb_clients.client_id = mcb_invoices.client_id
            LEFT JOIN mcb_contacts AS con ON con.contact_id = mcb_invoices.contact_id
            JOIN mcb_users ON mcb_users.user_id = mcb_invoices.user_id
            LEFT JOIN mcb_projects AS prj ON prj.project_id = mcb_invoices.project_id
            LEFT JOIN mcb_currencies ON mcb_currencies.currency_id = mcb_clients.client_currency_id
            WHERE `mcb_invoices`.`invoice_id` = '".$invoice_id."' 
            ORDER BY FROM_UNIXTIME(mcb_delivery_dockets.docket_date_entered) DESC, mcb_invoices.invoice_number DESC";
        return $this->db->query($sql)->result();
    }
    
    public function get_docket_items($docket_id) {
        $this->db->select('mcb_delivery_docket_items.invoice_item_id,d.invoice_id,d.docket_id,mcb_invoice_items.item_price,mcb_delivery_docket_items.docket_item_id, mcb_invoice_items.item_type, mcb_invoice_items.item_name, mcb_invoice_items.item_description, mcb_invoice_items.item_qty, mcb_delivery_docket_items.docket_item_qty,mcb_invoice_items.item_qty - mcb_delivery_docket_items.docket_item_qty AS delivered_item_qty');

        $this->db->where('mcb_delivery_docket_items.docket_id', $docket_id);
        $this->db->order_by('mcb_delivery_docket_items.docket_item_index');

        $this->db->join('mcb_invoice_items', 'mcb_invoice_items.invoice_item_id = mcb_delivery_docket_items.invoice_item_id', 'LEFT');
        $this->db->join('mcb_delivery_dockets as d', 'd.docket_id = mcb_delivery_docket_items.docket_id', 'LEFT');


        $items = $this->db->get('mcb_delivery_docket_items')->result();

        //echo '<pre>'; print_r($items); exit;

        $fin = array();
        if (sizeof($items) > 0) {
            foreach ($items as $item) {
                $item->already_supplied = $this->getAlreadySuppliedQty($item);
                $fin[] = $item;
            }
        }

        return $fin;
    }
    //to update amount on editing amount in invoice item
    public function updatedocket1($item){
            $total_amount = filter_var($item['invoice_amounts']->invoice_total, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $data = array('price_with_tax'=>$total_amount);
            $this->db->where('invoice_id',$item['item']['l_data']->invoice_id);
            $this->db->update('mcb_delivery_dockets',$data);
    }
    /**
     * we allow infinite number of dockets for an invoice
     */
    private function getAlreadySuppliedQty($item) {
        $sql = "select di.docket_item_qty,d.docket_id,d.docket_number from mcb_delivery_docket_items as di left join mcb_delivery_dockets as d on di.docket_id = d.docket_id where di.invoice_item_id = '" . $item->invoice_item_id . "'";
        $q = $this->db->query($sql);
        $results = $q->result();

        $already_supplied = 0;

        $msg = '<a data-color="yellow" data-effect="mfp-zoom-in" data-message=\'<div class="inv-detail">';
        $msg .= '<h2>' . $item->item_name . '</h2>';
        $msg .= '<br/><h3>Delivery Dockets:</h3><table class="table table-bordered order-prod-list">';
        $msg .= '<thead><tr><td>S.N</td><td>Docket Number</td><td>Supplied Qty</td></tr></thead><tbody>';
        $i = 1;
        if (sizeof($results) > 0) {
            foreach ($results as $res) {
                $msg .= '<tr><td>' . $i . '</td><td><a href="' . site_url('delivery_dockets/edit/docket_id/' . $res->docket_id) . '">' . $res->docket_number . '</a></td><td>' . $res->docket_item_qty . '</td></tr>';
                $i++;
                $already_supplied += $res->docket_item_qty;
            }
        } else {
            $msg .= '<tr><td colspan="3">There are no dockets for this item.</td></tr>';
        }
        $msg .= '</tbody></table></div>\' class="open-popup help yellow"><span>' . $already_supplied . '</span></a>';
        return $msg;
    }

    public function update_docket_item($docket_id, $docket_item) {


        //Query To Calculate Docket Price
        $sqlToCalculatePrice = 'SELECT di.invoice_item_id, di.docket_item_qty, di.docket_id, '
                . 'ii.item_price, ii.invoice_id, '
                . 'i.invoice_tax_rate_id, '
                . 'tx.tax_rate_name, tx.tax_rate_percent, '
                . '(ii.item_price*di.docket_item_qty) as total_price, '
                . '((ii.item_price*di.docket_item_qty)+(((ii.item_price*di.docket_item_qty)*(tx.tax_rate_percent))/100 )) as total_price_with_tax '
                . 'FROM (((mcb_delivery_docket_items as di '
                . 'LEFT JOIN mcb_invoice_items as ii ON di.invoice_item_id = ii.invoice_item_id) '
                . 'LEFT JOIN mcb_invoices as i ON ii.invoice_id = i.invoice_id) '
                . 'LEFT JOIN mcb_tax_rates as tx ON i.invoice_tax_rate_id = tx.tax_rate_id) '
                . 'WHERE di.docket_id = ';

        //only allow change to quantity
        $db_array = array(
            'docket_item_qty' => $docket_item->docket_item_qty,
        );
        $this->db->where('docket_item_id', $docket_item->docket_item_id);
        $this->db->update('mcb_delivery_docket_items', $db_array);
        $docket_item->already_supplied = $this->getAlreadySuppliedQty($docket_item);
        // ======================start==============
        $r3 = $this->db->query($sqlToCalculatePrice . $docket_id)->result_array();
        $key = 'total_price_with_tax';
        $sum_with_tax = array_sum(array_column($r3, $key));
        $this->db->where(array('docket_id' => $docket_id));
        $this->db->update('mcb_delivery_dockets', array('price_with_tax' => $sum_with_tax));
        // ======================end==============
        return $docket_item;
    }

    public function get_invoice_dockets($invoice_id) {


        $params = array(
            //'debug' =>	TRUE,
            'where' => array(
                'mcb_invoices.invoice_id' => $invoice_id
            )
        );

        $result = $this->get($params);
        return $result;
    }

    /*
     * See if the sum of docket_item_qty for all delivery dockets
     * against the invoice has already been met
     *
     */

    public function invoice_needs_delivery_docket($invoice_id) {

        $sql = "SELECT di.invoice_item_id, SUM(di.docket_item_qty) delivered_item_qty
           FROM mcb_delivery_dockets d
           JOIN mcb_delivery_docket_items di ON di.docket_id = d.docket_id
          WHERE d.invoice_id = ?
          GROUP BY di.invoice_item_id";

        $query = $this->db->query($sql, array($invoice_id));

        if ($query->num_rows() == 0) {

            return false;
        } else {
            $items = $query->result();

            foreach ($items as $item) {
                log_message('INFO', 'No supplier for invoice item ' . $item->item_name);
            }

            return true;
        }
    }

    public function create_invoice_docket($invoice_id, $docket_date_entered, $strtotime = TRUE) {


        //Query To Calculate Docket Price
        $sqlToCalculatePrice = 'SELECT di.invoice_item_id, di.docket_item_qty, di.docket_id, '
                . 'ii.item_price, ii.invoice_id, '
                . 'i.invoice_tax_rate_id, '
                . 'tx.tax_rate_name, tx.tax_rate_percent, '
                . '(ii.item_price*di.docket_item_qty) as total_price, '
                . '((ii.item_price*di.docket_item_qty)+(((ii.item_price*di.docket_item_qty)*(tx.tax_rate_percent))/100 )) as total_price_with_tax '
                . 'FROM (((mcb_delivery_docket_items as di '
                . 'LEFT JOIN mcb_invoice_items as ii ON di.invoice_item_id = ii.invoice_item_id) '
                . 'LEFT JOIN mcb_invoices as i ON ii.invoice_id = i.invoice_id) '
                . 'LEFT JOIN mcb_tax_rates as tx ON i.invoice_tax_rate_id = tx.tax_rate_id) '
                . 'WHERE di.docket_id = ';

        // Calculate undelivered quantities for each item
        $sql_udq = "SELECT ? docket_id, i.invoice_item_id, i.item_qty - IFNULL(delivered_item_qty,0) undelivered_item_qty, i.item_index
			   FROM mcb_invoice_items i
			   LEFT JOIN (
					SELECT di.invoice_item_id, SUM(di.docket_item_qty) delivered_item_qty
					  FROM mcb_delivery_dockets d
					  JOIN mcb_delivery_docket_items di ON di.docket_id = d.docket_id
					 WHERE d.invoice_id = ?
					 GROUP BY di.invoice_item_id) AS dq ON dq.invoice_item_id = i.invoice_item_id
			 WHERE i.invoice_id = ?
			   AND (  (i.item_qty > 0) OR ( (i.item_name = '') AND (i.item_length = '')  ) )";

        $sql_max_udq = "SELECT MAX(undelivered_item_qty) max_udq FROM ($sql_udq) udq";
        // note invoice_id fills in for docket_id
        $query = $this->db->query($sql_max_udq, array($invoice_id, $invoice_id, $invoice_id));
        $row = $query->row();
        $max_udq = $row->max_udq;
        $invoiceDetail = $this->get_Row('mcb_invoices', array('invoice_id'=>$invoice_id));
        
        // only continue if there are undelivered quantities
        
        if ($max_udq > 0) {
            if ($strtotime) {
                $docket_date_entered = strtotime(standardize_date($docket_date_entered));
            }
            $this->db->trans_start();
            $this->load->model('sequences/mdl_sequences');
            $db_array = array(
                'invoice_id' => $invoice_id,
                'docket_number' => $this->mdl_sequences->get_next_value(3),
                'docket_date_entered' => $docket_date_entered,
                'docket_due_date' => ($invoiceDetail->invoice_date_entered) + ( 24 * 60 * 60 * 30),
                'docket_address_id' => $this->get_latest_docket_address_id($invoice_id),
                'user_id' => $this->session->userdata('user_id'),
                'invoice_date'=>$invoiceDetail->invoice_date_entered
            );

            $this->db->insert($this->table_name, $db_array);
            $docket_id = $this->db->insert_id();

            $this->create_docket_items($docket_id, $invoice_id, $sql_udq);
            $r3 = $this->db->query($sqlToCalculatePrice . $docket_id)->result_array();
            $key = 'total_price_with_tax';
            $sum_with_tax = array_sum(array_column($r3, $key));
            $this->db->where(array('docket_id' => $docket_id));
            $this->db->update('mcb_delivery_dockets', array('price_with_tax' => $sum_with_tax));
            $this->db->trans_complete();
            return $docket_id;
        }
    }

    function create_docket_items($docket_id, $invoice_id, $sql_udq) {

        $sql = "INSERT
			   INTO mcb_delivery_docket_items (docket_id, invoice_item_id, docket_item_qty, docket_item_index)
     		 $sql_udq";

        $this->db->query($sql, array($docket_id, $invoice_id, $invoice_id));

        $ret = $this->db->affected_rows();

        if ($ret > 0) {
            log_message('debug', 'Docket: ' . $docket_id . ' created ' . $ret . ' new docket items');
        }
    }

    public function getInvoiceId($docket_id) {
        $this->db->where('docket_id', $docket_id);
        $q = $this->db->get('mcb_delivery_dockets');
        $res = $q->row();
        if ($res->invoice_id) {
            return $res->invoice_id;
        }
        return false;
    }

    public function check_if_first_docket($docket_id, $invoice_id) {
        $this->db->where('invoice_id', $invoice_id);
        $this->db->order_by('docket_date_entered', 'ASC');
        $q = $this->db->get('mcb_delivery_dockets');
        $result = $q->result();

        //if only one first docket
        if (sizeof($result) == 1) {
            return TRUE;
        }
        //if more
        if (sizeof($result) > 1 && $docket_id == $result[0]->docket_id) {
            return TRUE;
        }
        return FALSE;
    }
    
    function add_docket_amount($data) {
        
        # Starting Transaction
        $this->db->trans_begin(); 
        # Inserting data
        $this->db->insert('mcb_delivery_docket_payment', $data);
        if($this->db->affected_rows() > '0'){
            $this->db->trans_commit();
            $this->session->set_flashdata('custom_success', 'Payment has been successfully added.');
        } else {
            $this->db->trans_rollback();
            $this->session->set_flashdata('custom_error', 'An error has occurred.');
        }
    }
    
    function insert($tableName, $data) {
        $this->db->insert($tableName, $data);
        return $this->db->affected_rows();
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

    public function update($tbl_name, $data, $condition) {
        $this->load->database();
        $this->db->where($condition);
        $this->db->update($tbl_name, $data);
    }

    public function get_all($table_name, $condition = '', $order = '', $limit = '') {
        if ($order != '') {
            $this->db->order_by($order);
        }
        if ($limit != '') {
            $this->db->limit($limit);
        }
        if ($condition != '') {
            $this->db->where($condition);
        }
        $q = $this->db->get($table_name);
        $Res = $q->result();
        return $Res;
    }

    function query($qry) {

        $q = $this->db->query($qry);
        $Res = $q->result();
        return $Res;
    }

    function query_array($qry) {

        $q = $this->db->query($qry);
        $Res = $q->result_array();
        return $Res;
    }
    
    
    function insert_with_return_id($tableName, $data) {
        $this->db->insert($tableName, $data);
        $rid = $this->db->insert_id();
        if($rid > 0){
            return $rid;
        } else {
            return FALSE;
        }
    }

}

?>