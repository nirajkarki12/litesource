<?php

(defined('BASEPATH')) OR exit('No direct script access allowed');

class Mdl_Orders extends MY_Model {
    /*
     * The big Query
     * May need to sort out the join against order_items if it is doing a full scan
     *
      SELECT SQL_CALC_FOUND_ROWS
      mcb_orders.*,
      con.contact_name,
      con.email_address AS contact_email_address,
      tax_rate_percent,
      t.tax_rate_name,
      CONCAT(FORMAT(t.tax_rate_percent, 0),'% ', t.tax_rate_name) tax_rate_percent_name,
      FORMAT(order_sub_total, 2) order_sub_total,
      FORMAT(order_sub_total * IFNULL(t.tax_rate_percent,0)/100,2) tax_total,
      FORMAT(order_sub_total * (1 + IFNULL(t.tax_rate_percent,0)/100),2) order_total,
      IFNULL(mcb_invoices.invoice_number, '-') AS invoice_number,
      mcb_clients.*,
      mcb_currencies.*,
      prj.project_id,
      IFNULL(prj.project_name,'-') AS project_name,
      mcb_users.username,
      mcb_users.last_name AS from_last_name,
      mcb_users.first_name AS from_first_name,
      mcb_users.email_address AS from_email_address,
      mcb_users.mobile_number AS from_mobile_number,
      mcb_invoice_statuses.invoice_status AS order_status,
      mcb_invoice_statuses.invoice_status_type AS order_status_type
      FROM
      mcb_orders
      LEFT JOIN (SELECT order_id, SUM(item_qty * item_supplier_price) order_sub_total FROM `mcb_order_items` GROUP BY order_id) AS i ON i.order_id = mcb_orders.order_id
      JOIN mcb_clients ON	mcb_clients.client_id = mcb_orders.supplier_id
      LEFT JOIN mcb_contacts AS con ON con.contact_id = mcb_orders.contact_id
      JOIN mcb_invoice_statuses ON mcb_invoice_statuses.invoice_status_id = mcb_orders.order_status_id
      JOIN mcb_users ON mcb_users.user_id = mcb_orders.user_id
      LEFT JOIN mcb_invoices ON mcb_invoices.invoice_id = mcb_orders.invoice_id
      LEFT JOIN mcb_projects AS prj ON prj.project_id = mcb_invoices.project_id
      LEFT JOIN mcb_tax_rates AS t ON t.tax_rate_id = mcb_orders.order_tax_rate_id
      JOIN mcb_currencies ON mcb_currencies.currency_id = mcb_clients.client_currency_id
     *
     */

    //public $order_total = 

    public function __construct() {

        parent::__construct();
        $this->table_name = 'mcb_orders';
        $this->primary_key = 'mcb_orders.order_id';
        //$this->order_by = 'mcb_orders.order_date_entered DESC, mcb_orders.order_id';
        $this->order_by = 'FROM_UNIXTIME(mcb_orders.order_date_entered) DESC, mcb_orders.order_number DESC';
        $this->select_fields = "
		SQL_CALC_FOUND_ROWS
		mcb_orders.*,
		IFNULL(length(mcb_orders.order_notes), 0) order_has_notes,
		con.contact_name,
		con.email_address AS contact_email_address,
		tax_rate_percent,
		t.tax_rate_name,
		CONCAT(FORMAT(t.tax_rate_percent, 0),'% ', t.tax_rate_name) tax_rate_percent_name,
		FORMAT(order_sub_total, 2) order_sub_total,
		FORMAT(order_sub_total * IFNULL(t.tax_rate_percent,0)/100,2) tax_total,
		FORMAT(order_sub_total * (1 + IFNULL(t.tax_rate_percent,0)/100),2) order_total,
		mcb_invoices.invoice_number,
		mcb_invoices.invoice_is_quote,
        mcb_clients.*,
		mcb_currencies.*,
		prj.project_id,
		prj.project_name,
		mcb_users.username,
		mcb_users.last_name AS from_last_name,
	    mcb_users.first_name AS from_first_name,
		mcb_users.email_address AS from_email_address,
		mcb_users.mobile_number AS from_mobile_number,
		mcb_users.company_name AS from_company_name,
		mcb_invoice_statuses.invoice_status AS order_status,
	    mcb_invoice_statuses.invoice_status_type AS order_status_type";

        $this->joins = array(
            //'mcb_order_items AS i'	=>	array(
            '(SELECT order_id, SUM(item_qty * item_supplier_price) order_sub_total FROM `mcb_order_items` GROUP BY order_id) AS i' => array(
                'i.order_id = mcb_orders.order_id',
                'left'         //left join to allow for there being no order_items
            ),
            'mcb_clients' => 'mcb_clients.client_id = mcb_orders.supplier_id',
            'mcb_contacts AS con' => array(
                'con.contact_id = mcb_orders.contact_id',
                'left'
            ),
            'mcb_invoice_statuses' => 'mcb_invoice_statuses.invoice_status_id = mcb_orders.order_status_id',
            'mcb_users' => 'mcb_users.user_id = mcb_orders.user_id',
            'mcb_invoices' => array(
                'mcb_invoices.invoice_id = mcb_orders.invoice_id',
                'left'
            ),
            'mcb_projects AS prj' => array(
                'prj.project_id = mcb_orders.project_id',
                'left'
            ),
            'mcb_tax_rates AS t' => array(
                't.tax_rate_id = mcb_orders.order_tax_rate_id',
                'left'         //left join to allow for there being no order_items
            ),
            'mcb_currencies' => 'mcb_currencies.currency_id = mcb_clients.client_currency_id'
        );


        $this->limit = $this->mdl_mcb_data->setting('results_per_page');
    }

    public function get($params = NULL) {

        if (isset($params['where']['mcb_orders.order_id'])) {
            $order_id = $params['where']['mcb_orders.order_id'];
            $orderDetail = $this->get_Row('mcb_orders', array('order_id' => $order_id));
        }

        if ($orderDetail->is_inventory_supplier == '1' && isset($params['where']['mcb_orders.order_id'])) {
            $sql = "SELECT SQL_CALC_FOUND_ROWS mcb_orders.*, IFNULL(length(mcb_orders.order_notes), 0) "
                    . "order_has_notes, con.contact_name, con.email_address AS contact_email_address, "
                    . "tax_rate_percent, t.tax_rate_name, CONCAT(FORMAT(t.tax_rate_percent, 0), '% ', t.tax_rate_name) "
                    . "tax_rate_percent_name, FORMAT(order_sub_total, 2) order_sub_total, FORMAT(order_sub_total * IFNULL(t.tax_rate_percent, 0)/100, 2) "
                    . "tax_total, FORMAT(order_sub_total * (1 + IFNULL(t.tax_rate_percent, 0)/100), 2) order_total, mcb_invoices.invoice_number, "
                    . "mcb_invoices.invoice_is_quote, mcb_clients.*, mcb_currencies.*, prj.project_id, prj.project_name, mcb_users.username, "
                    . "mcb_users.last_name AS from_last_name, mcb_users.first_name AS from_first_name, mcb_users.email_address AS "
                    . "from_email_address, mcb_users.mobile_number AS from_mobile_number, mcb_users.company_name AS from_company_name, "
                    . "mcb_invoice_statuses.invoice_status AS order_status, mcb_invoice_statuses.invoice_status_type AS order_status_type "
                    . "FROM (mcb_orders) LEFT JOIN (SELECT order_id, SUM(item_qty * item_supplier_price) order_sub_total FROM `mcb_order_inventory_items` "
                    . "GROUP BY order_id) AS i ON i.order_id = mcb_orders.order_id JOIN mcb_clients ON "
                    . "mcb_clients.client_id = mcb_orders.supplier_id LEFT JOIN mcb_contacts AS con ON "
                    . "con.contact_id = mcb_orders.contact_id JOIN mcb_invoice_statuses ON "
                    . "mcb_invoice_statuses.invoice_status_id = mcb_orders.order_status_id "
                    . "JOIN mcb_users ON mcb_users.user_id = mcb_orders.user_id LEFT JOIN "
                    . "mcb_invoices ON mcb_invoices.invoice_id = mcb_orders.invoice_id LEFT JOIN mcb_projects AS prj ON "
                    . "prj.project_id = mcb_orders.project_id LEFT JOIN mcb_tax_rates AS t ON t.tax_rate_id = mcb_orders.order_tax_rate_id "
                    . "JOIN mcb_currencies ON mcb_currencies.currency_id = mcb_clients.client_currency_id WHERE `mcb_orders`.`order_id` = '" . $params['where']['mcb_orders.order_id'] . "' "
                    . "ORDER BY FROM_UNIXTIME(mcb_orders.order_date_entered) DESC, mcb_orders.order_number DESC";

            $q = $this->db->query($sql);
            $orders = $q->row();

//            echo "<pre>";
//            print_r($orders);
//            die;

            if ($orders->order_sub_total == '') {
//                $orders = parent::get($params);
//                $orders->is_pro_inv = TRUE;
            }
        } else {

            //$params['group_by'] = 'mcb_orders.order_id';
            //$params['debug'] = TRUE;
            $orders = parent::get($params);
        }
        return $orders;
    }

    /*
     * Sometimes we want raw data without joining to other tables
     */

    public function get_raw($limit = NULL, $offset = NULL) {

//        $limit = 50;
//        $offset = 50;

        $fin = array();
        if ($this->config->item('ORDERTO') == 'INVENTORYSUPPLIER') {
            //ia.invoice_total as total,
            $select = "SQL_CALC_FOUND_ROWS
			o.order_id id,
			o.order_date_entered e,
			o.supplier_id c,
			o.project_id p,
			o.contact_id ct,
			o.order_number n,
			o.order_status_id s,
			o.order_tax_rate_id t,
			o.invoice_id qi,
                        cr.currency_symbol_left,cr.currency_symbol_right,cr.currency_code as cur,
                        FORMAT(order_sub_total * (1 + IFNULL(t.tax_rate_percent, 0)/100), 2) as total,
                        FORMAT(order_sub_total_oi * (1 + IFNULL(t.tax_rate_percent, 0)/100), 2) as total_oi,
                        o.order_supplier_invoice_number as supplier_invoice_number,
                        o.is_inventory_supplier inv_sup,
			q.invoice_number qn";
            //SUM(i.item_qty * i.item_supplier_price) a";
            $this->db->select($select, FALSE);
            $this->db->order_by('o.order_date_entered DESC, o.order_id DESC');
            //$this->db->join('mcb_order_items AS i', 'i.order_id = o.order_id', 'left');
            $this->db->join('mcb_invoices AS q', 'q.invoice_id = o.invoice_id', 'left');
            $this->db->join('mcb_invoice_amounts AS ia', 'ia.invoice_id = o.invoice_id', 'left');
            $this->db->join('(SELECT mcb_orders.order_id, SUM(item_qty * item_supplier_price) order_sub_total from mcb_orders left join `mcb_order_inventory_items` on mcb_orders.order_id = mcb_order_inventory_items.order_id group by mcb_order_inventory_items.order_id) AS i', 'i.order_id = o.order_id', 'left');
            $this->db->join('(SELECT mcb_orders.order_id, SUM(item_qty * item_supplier_price) order_sub_total_oi from mcb_orders left join `mcb_order_items` on mcb_orders.order_id = mcb_order_items.order_id group by mcb_order_items.order_id) AS oi', 'oi.order_id = o.order_id', 'left');
            $this->db->join('mcb_tax_rates AS t', 't.tax_rate_id = o.order_tax_rate_id', 'left');
            $this->db->join('mcb_clients AS clnt', 'clnt.client_id = o.supplier_id', 'left');
            $this->db->join('mcb_currencies AS cr', 'cr.currency_id = clnt.client_currency_id', 'left');
            //$this->db->group_by('id');
            if ($limit) {
                $this->db->limit($limit, $offset);
            }
            $query = $this->db->get('mcb_orders AS o');
            $result = $query->result();
            if (sizeof($result) > 0) {
                foreach ($result as $res) {
                    $res->total = $this->formatMoney($res->total, $res->currency_symbol_left, $res->currency_symbol_right);
                    if ($res->inv_sup == '0') {
                        $res->total = $this->formatMoney($res->total_oi, $res->currency_symbol_left, $res->currency_symbol_right);
                    }
                    $fin[] = $res;
                }
            }

//            echo "<pre>";
//            print_r($fin);
//            die;
        } else {
            //ia.invoice_total as total,
            $select = "SQL_CALC_FOUND_ROWS
			o.order_id id,
			o.order_date_entered e,
			o.supplier_id c,
			o.project_id p,
			o.contact_id ct,
			o.order_number n,
			o.order_status_id s,
			o.order_tax_rate_id t,
			o.invoice_id qi,
                        cr.currency_symbol_left,cr.currency_symbol_right,cr.currency_code as cur,
                        FORMAT(order_sub_total * (1 + IFNULL(t.tax_rate_percent, 0)/100), 2) as total,
                        o.order_supplier_invoice_number as supplier_invoice_number,
			q.invoice_number qn";
            //SUM(i.item_qty * i.item_supplier_price) a";
            $this->db->select($select, FALSE);
            $this->db->order_by('o.order_date_entered DESC, o.order_id DESC');
            //$this->db->join('mcb_order_items AS i', 'i.order_id = o.order_id', 'left');
            $this->db->join('mcb_invoices AS q', 'q.invoice_id = o.invoice_id', 'left');
            $this->db->join('mcb_invoice_amounts AS ia', 'ia.invoice_id = o.invoice_id', 'left');
            $this->db->join('(SELECT mcb_orders.order_id, SUM(item_qty * item_supplier_price) order_sub_total from mcb_orders left join `mcb_order_items` on mcb_orders.order_id = mcb_order_items.order_id group by mcb_order_items.order_id) AS i', 'i.order_id = o.order_id', 'left');
            $this->db->join('mcb_tax_rates AS t', 't.tax_rate_id = o.order_tax_rate_id', 'left');
            $this->db->join('mcb_clients AS clnt', 'clnt.client_id = o.supplier_id', 'left');
            $this->db->join('mcb_currencies AS cr', 'cr.currency_id = clnt.client_currency_id', 'left');
            //$this->db->group_by('id');
            if ($limit) {
                $this->db->limit($limit, $offset);
            }
            $query = $this->db->get('mcb_orders AS o');
            $result = $query->result();
            if (sizeof($result) > 0) {
                foreach ($result as $res) {
                    $res->total = $this->formatMoney($res->total, $res->currency_symbol_left, $res->currency_symbol_right);
                    $fin[] = $res;
                }
            }
        }
        return $fin;
    }

    public function get_raw_($limit = NULL, $offset = NULL) {

        $select = "SQL_CALC_FOUND_ROWS
			o.order_id id,
			o.order_date_entered e,
			o.supplier_id c,
			o.project_id p,
			o.contact_id ct,
			o.order_number n,
			o.order_status_id s,
			o.order_tax_rate_id t,
			o.invoice_id qi,
                        ia.invoice_total as total,
                        o.order_supplier_invoice_number as supplier_invoice_number,
			q.invoice_number qn";
        //SUM(i.item_qty * i.item_supplier_price) a";

        $this->db->select($select, FALSE);
        $this->db->order_by('o.order_date_entered DESC, o.order_id DESC');
        //$this->db->join('mcb_order_items AS i', 'i.order_id = o.order_id', 'left');
        $this->db->join('mcb_invoices AS q', 'q.invoice_id = o.invoice_id', 'left');
        $this->db->join('mcb_invoice_amounts AS ia', 'ia.invoice_id = o.invoice_id', 'left');

        //$this->db->group_by('id');


        if ($limit) {
            $this->db->limit($limit, $offset);
        }

        $query = $this->db->get('mcb_orders AS o');
        $result = $query->result();
        $fin = array();
        if (sizeof($result) > 0) {
            foreach ($result as $res) {
                $res->total = $this->formatMoney($res->total);
                $fin[] = $res;
            }
        }
        //echo '<pre>'; print_r($fin); exit;
        return $fin;
    }

    public function formatMoney($total, $ls, $rs) {
        if ((int) $total <= 0) {
            $total = '0.00';
        }
        return $ls . $total . $rs;
    }

    function get_order_total($order_id) {
        $sql = "SELECT mcb_orders.order_id,"
                . "tax_rate_percent, t.tax_rate_name,"
                . "FORMAT(order_sub_total * (1 + IFNULL(t.tax_rate_percent, 0)/100), 2) order_total FROM (mcb_orders) "
                . "LEFT JOIN (SELECT order_id, SUM(item_qty * item_supplier_price) order_sub_total FROM `mcb_order_items` "
                . "GROUP BY order_id) AS i ON i.order_id = mcb_orders.order_id "
                . "LEFT JOIN mcb_tax_rates AS t ON t.tax_rate_id = mcb_orders.order_tax_rate_id "
                . "WHERE mcb_orders.order_id = '" . $order_id . "' ";
        $q = $this->db->query($sql);
        $orders = $q->row();
        return $orders;
    }

    public function validate() {


        //$this->form_validation->set_rules('supplier_id', $this->lang->line('supplier_id'), 'required');
        //$this->form_validation->set_rules('invoice_id', $this->lang->line('invoice_id'));
        $this->form_validation->set_rules('order_number', $this->lang->line('order_number'), 'required');
        //$this->form_validation->set_rules('order_tax_rate_id', $this->lang->line('order_tax_rate_id'), 'required');
        $this->form_validation->set_rules('order_date_entered', $this->lang->line('order_date_entered'), 'required');
        $this->form_validation->set_rules('order_status_id', $this->lang->line('order_status_id'), 'required');
        $this->form_validation->set_rules('order_notes', $this->lang->line('order_notes'));

        return parent::validate($this);
    }

    public function validate_create() {

        $this->form_validation->set_rules('supplier_id', $this->lang->line('supplier_id'), 'required');
        $this->form_validation->set_rules('order_date_entered', $this->lang->line('order_date_entered'), 'required');

        return parent::validate($this);
    }
    
    
    
    public function save_order_db_array($order_id, $db_array) {


        $this->db->where('order_id', $order_id);
        $this->db->update($this->table_name, $db_array);
    }

    public function save() {

        $order_id = uri_assoc('order_id');

        $order = $this->get_by_id($order_id);

        // Can only save details if order is open
        //if ($order->order_status_type == 1) {

        $db_array = array(
            'contact_id' => $this->input->post('contact_id'),
            'project_id' => $this->input->post('project_id'),
            'order_number' => $this->input->post('order_number'),
            'order_date_entered' => strtotime(standardize_date($this->input->post('order_date_entered'))),
            'order_notes' => $this->input->post('order_notes'),
            'order_supplier_invoice_number' => $this->input->post('order_supplier_invoice_number')
        );
        //}

        if (is_numeric($this->input->post('order_status_id'))) {

            $db_array['order_status_id'] = $this->input->post('order_status_id');
        }

        $this->save_order_db_array($order_id, $db_array);

        $this->session->set_flashdata('custom_success', $this->lang->line('order_options_saved'));
    }

    public function get_order_address($order_address_id) {

        $this->load->model('addresses/mdl_addresses');

        return $this->mdl_addresses->get_by_id($order_address_id);
    }

    public function save_delivery_address($order_id, $address_id) {


        $db_array = array(
            'order_address_id' => $address_id,
        );

        $this->db->where('order_id', $order_id);

        $this->db->update($this->table_name, $db_array);

        $this->session->set_flashdata('custom_success', $this->lang->line('order_address_saved'));
    }

    public function delete($order_id) {


        $orderDetail = $this->get_Row('mcb_orders', array('order_id' => $order_id));
        if ($orderDetail->is_inventory_supplier == '1') {
            $this->config->set_item('ORDERTO', 'INVENTORYSUPPLIER');
        }

        parent::delete(array('order_id' => $order_id));

        $this->db->where('order_id', $order_id);
        if ($this->config->item('ORDERTO') == 'INVENTORYSUPPLIER') {
            $this->db->delete('mcb_order_inventory_items');
        } else {
            $this->db->delete('mcb_order_items');
        }
    }

    public function get_order_currency($order_id) {

        $this->db->where('order_id', $order_id);

        $this->db->join('mcb_clients', 'mcb_clients.client_id = mcb_orders.supplier_id');
        $this->db->join('mcb_currencies', 'mcb_currencies.currency_id = mcb_clients.client_currency_id');

        $currency = $this->db->get('mcb_orders')->row();

        return $currency;
    }

    function get_inv_order_items($order_id) {

        $select_fields = "SQL_CALC_FOUND_ROWS
            oii.*,oii.item_name as inventory_name,
            FORMAT(SUM(oii.item_qty * oii.item_supplier_price),2) AS item_subtotal,
            ( IF(item_length>0, item_qty, item_qty) ) AS qty, 
            ( IF(item_length>0, 1, 0) ) AS is_length";
        $this->db->select($select_fields);
        $this->db->where('oii.order_id', $order_id);
        $this->db->order_by('oii.item_order,oii.item_index, oii.order_item_id');
        $this->db->group_by('oii.order_item_id');
        $this->db->from('mcb_order_inventory_items as oii');
        $this->db->join('mcb_inventory_item as ii', 'ii.inventory_id = oii.inventory_id', 'left');
        $q = $this->db->get();
        return $items = $q->result();
    }

    function get_pro_order_items($order_id) {

        $select_fields = "SQL_CALC_FOUND_ROWS
            mcb_order_items.*,
            ( IF(item_length>0, FORMAT(SUM(item_length * item_per_meter),2), FORMAT(SUM(item_qty * item_supplier_price),2)) ) AS item_subtotal, 
            ( IF(item_length>0, item_length, item_qty) ) AS qty, 
            ( IF(item_length>0, 1, 0) ) AS is_length";

        $this->db->select($select_fields);
        $this->db->where('order_id', $order_id);
        $this->db->order_by('item_order,item_index, order_item_id');
        $this->db->group_by('order_item_id');
        $items = $this->db->get('mcb_order_items')->result();

        return $items;
    }

    
    function get_order_items_list($order_id) {
        
        $is_inv_suplier = $this->get_Row('mcb_orders', array('order_id' => $order_id))->is_inventory_supplier;
        if($is_inv_suplier == '1'){
            $items = $this->get_inv_order_items($order_id);
        }else{
            $items = $this->get_pro_order_items($order_id);
        }
        
        $fin = array();
        if (sizeof($items) > 0) {
            foreach ($items as $item) {
                if ($item->is_edited == '1') {
                    $item->qty = $item->item_qty; //this is because admin has manually edited it
                    $item->item_subtotal = format_number($item->qty * $item->item_supplier_price);
                }
                $fin[] = $item;
            }
        }
        return $fin;
    }
    
    
    public function get_order_items($order_id) {

        $orderDetail = $this->get_Row('mcb_orders', array('order_id' => $order_id));
        
        if ($orderDetail->is_inventory_supplier == '1') {
            $select_fields = "
			SQL_CALC_FOUND_ROWS
			oii.*,oii.item_name as inventory_name,
			FORMAT(SUM(oii.item_qty * oii.item_supplier_price),2) AS item_subtotal,
                        ( IF(item_length>0, item_length, item_qty) ) AS qty, 
                        ( IF(item_length>0, 1, 0) ) AS is_length";

            $this->db->select($select_fields);

            $this->db->where('oii.order_id', $order_id);

            $this->db->order_by('oii.item_order,oii.item_index, oii.order_item_id');
            $this->db->group_by('oii.order_item_id');
            $this->db->from('mcb_order_inventory_items as oii');
            $this->db->join('mcb_inventory_item as ii', 'ii.inventory_id = oii.inventory_id', 'left');
            $q = $this->db->get();
            $items = $q->result();
        } else {
//            $select_fields = "
//			SQL_CALC_FOUND_ROWS
//			mcb_order_items.*,
//			FORMAT(SUM(item_qty * item_supplier_price),2) AS item_subtotal, 
//                        ( IF(item_length>0, item_length, item_qty) ) AS qty, 
//                        ( IF(item_length>0, 1, 0) ) AS is_length";

            $select_fields = "
			SQL_CALC_FOUND_ROWS
			mcb_order_items.*,
                        ( IF(item_length>0, FORMAT(SUM(item_length * item_per_meter),2), FORMAT(SUM(item_qty * item_supplier_price),2)) ) AS item_subtotal, 
                        ( IF(item_length>0, item_length, item_qty) ) AS qty, 
                        ( IF(item_length>0, 1, 0) ) AS is_length";

            $this->db->select($select_fields);
            $this->db->where('order_id', $order_id);
            $this->db->order_by('item_order,item_index, order_item_id');
            $this->db->group_by('order_item_id');
            $items = $this->db->get('mcb_order_items')->result();
        }

//        echo "<pre>";
//        print_r($items);
//        die;


        $fin = array();
        if (sizeof($items) > 0) {
            foreach ($items as $item) {
                if ($item->is_edited == '1') {
                    $item->qty = $item->item_qty; //this is because admin has manually edited it
                    $item->item_subtotal = format_number($item->qty * $item->item_supplier_price);
                }
                $fin[] = $item;
            }
        }

//        ======================= we can remove this any time ===================
//        foreach ($items as $value) {
//            if( (($value->qty)>0 ) &&  ( $value->is_length == '1' )  ){   
//                $this->update('mcb_order_items', array('item_qty'=>$value->qty), array('order_item_id'=>$value->order_item_id));
//            }
//        }
//        =======================================================================
        // the same inventory items can be a part of different product. lets hide this fact to the supplier and show as merged keeping this 
        //distinction in database though
        //lets just update the item_qty, others are same
        //echo '<pre>'; print_r($items); exit;
        //$items = $this->updateQtyForSameInventory($items);
        //echo '<pre>'; print_r($items); exit;
        return $fin;
    }

    public function get_order_item_detail($order_item_id) {
        $sql = "select oii.* from mcb_order_inventory_items as oii where oii.order_item_id = '" . $order_item_id . "'";
        $q = $this->db->query($sql);
        $result = $q->row();
        $result->total_qty = $this->getTotalQty($result->order_id, $result->inventory_id);
        return $result;
    }

    public function updateOrderItemInv() {
        //$this->removeOtherPartialOrderItem();
        $order_id = $this->input->post('order_id');
        if ($this->input->post('neworderitem') != NULL) {
            //adding order item
            $data = array(
                'order_id' => $order_id,
                'inventory_id' => $this->input->post('inventory_id'),
                'item_name' => $this->input->post('inventory_name'),
                'item_type' => $this->input->post('item_type'),
                'item_description' => $this->input->post('item_description'),
                'item_qty' => $this->input->post('item_qty'),
                'item_supplier_price' => $this->input->post('item_supplier_price')
            );
            return $this->db->insert('mcb_order_inventory_items', $data);
        } else {
            //update
            $order_item_id = $this->input->post('order_item_id');

            $inventory_id = $this->input->post('inventory_id');

            $where = array(
                'order_item_id' => $order_item_id,
                'order_id' => $order_id,
            );
            $this->db->where($where);

            $data = array(
                'item_name' => $this->input->post('inventory_name'),
                'item_type' => $this->input->post('item_type'),
                'item_description' => $this->input->post('item_description'),
                'item_qty' => $this->input->post('item_qty'),
                'item_supplier_price' => $this->input->post('item_supplier_price')
            );
            return $this->db->update('mcb_order_inventory_items', $data);
        }
    }

    private function removeOtherPartialOrderItem() {
        $order_item_id = $this->input->post('order_item_id');
        $order_id = $this->input->post('order_id');
        $inventory_id = $this->input->post('inventory_id');

        $sql = "delete from mcb_order_inventory_items where order_id = '" . $order_id . "' and inventory_id = '" . $inventory_id . "' and order_item_id != '" . $order_item_id . "'";
        $q = $this->db->query($sql);
        return TRUE;
    }

    public function getTotalQty($order_id, $inv_id) {
        $sql = "select sum(item_qty) as tq from mcb_order_inventory_items where order_id = '" . $order_id . "' and inventory_id = '" . $inv_id . "'";
        $q = $this->db->query($sql);
        $res = $q->row();
        return $res->tq;
    }

    public function updateQtyForSameInventory($items) {
        $fin = array();
        if (sizeof($items) > 0) {
            foreach ($items as $item) {
                if (array_key_exists($item->inventory_id, $fin)) {
                    $item->item_qty = $fin[$item->inventory_id]->item_qty + $item->item_qty;
                    $item->item_subtotal = $fin[$item->inventory_id]->item_subtotal + $item->item_subtotal;
                    $fin[$item->inventory_id] = $item;
                } else {
                    $fin[$item->inventory_id] = $item;
                }
            }
        }
        return $fin;
    }

    public function get_invoice_orders($invoice_id) {

        $sql0 = "SELECT * FROM  `mcb_orders` WHERE `invoice_id` =  '" . $invoice_id . "' AND `is_inventory_supplier` = '1'";
        $q0 = $this->db->query($sql0);
        $res0 = $q0->result();
        if ($res0 != NULL) {
            $this->config->set_item('ORDERTO', 'INVENTORYSUPPLIER');
        }

        if ($this->config->item('ORDERTO') == 'INVENTORYSUPPLIER') {
            $sql = "SELECT SQL_CALC_FOUND_ROWS mcb_orders.*, IFNULL(length(mcb_orders.order_notes), 0) order_has_notes, "
                    . "con.contact_name, con.email_address AS contact_email_address, tax_rate_percent, t.tax_rate_name, "
                    . "CONCAT(FORMAT(t.tax_rate_percent, 0), '% ', t.tax_rate_name) tax_rate_percent_name, "
                    . "FORMAT(order_sub_total, 2) order_sub_total, FORMAT(order_sub_total * IFNULL(t.tax_rate_percent, 0)/100, 2) "
                    . "tax_total, FORMAT(order_sub_total * (1 + IFNULL(t.tax_rate_percent, 0)/100), 2) order_total, "
                    . "mcb_invoices.invoice_number, mcb_invoices.invoice_is_quote, mcb_clients.*, mcb_currencies.*, prj.project_id, "
                    . "prj.project_name, mcb_users.username, mcb_users.last_name AS from_last_name, mcb_users.first_name AS "
                    . "from_first_name, mcb_users.email_address AS from_email_address, mcb_users.mobile_number AS from_mobile_number, "
                    . "mcb_users.company_name AS from_company_name, mcb_invoice_statuses.invoice_status AS order_status, "
                    . "mcb_invoice_statuses.invoice_status_type AS order_status_type FROM (mcb_orders) LEFT JOIN "
                    . "(SELECT order_id, SUM(item_qty * item_supplier_price) order_sub_total FROM `mcb_order_inventory_items` "
                    . "GROUP BY order_id) AS i ON i.order_id = mcb_orders.order_id LEFT JOIN mcb_clients ON "
                    . "mcb_clients.client_id = mcb_orders.supplier_id LEFT JOIN mcb_contacts AS con "
                    . "ON con.contact_id = mcb_orders.contact_id JOIN mcb_invoice_statuses ON "
                    . "mcb_invoice_statuses.invoice_status_id = mcb_orders.order_status_id LEFT JOIN "
                    . "mcb_users ON mcb_users.user_id = mcb_orders.user_id LEFT JOIN mcb_invoices "
                    . "ON mcb_invoices.invoice_id = mcb_orders.invoice_id LEFT JOIN mcb_projects AS prj "
                    . "ON prj.project_id = mcb_orders.project_id LEFT JOIN mcb_tax_rates AS t ON "
                    . "t.tax_rate_id = mcb_orders.order_tax_rate_id JOIN mcb_currencies ON "
                    . "mcb_currencies.currency_id = mcb_clients.client_currency_id WHERE "
                    . "`mcb_invoices`.`invoice_id` = '" . $invoice_id . "' ORDER BY FROM_UNIXTIME(mcb_orders.order_date_entered) DESC, "
                    . "mcb_orders.order_number DESC";
            $q = $this->db->query($sql);
            $res = $q->result();
        } else {
            $params = array(
                'where' => array(
                    'mcb_invoices.invoice_id' => $invoice_id
                )
            );

            $res = $this->get($params);
        }

//        echo "<pre>";
//        print_r($res);
//        die;

        return $res;
    }

    private function create_invoice_orders_old($invoice_id, $order_date_entered) {

        /*
         * Need to handle the case where orders have already been created
         * i.e should only add the new orders/order_items
         */
        $sql = "INSERT INTO mcb_orders (invoice_id, supplier_id, order_number, user_id, order_tax_rate_id, order_date_entered, order_status_id)
                SELECT DISTINCT invoice_id, p.supplier_id, '0', ?, c.client_tax_rate_id, ?, 1
                FROM mcb_invoice_items i
                INNER JOIN mcb_products p ON p.product_id = i.product_id
                INNER JOIN mcb_clients c ON c.client_id = p.supplier_id
              WHERE i.invoice_id = ?";

        $user_id = $this->session->userdata('user_id');
        $this->db->query($sql, array($user_id, $order_date_entered, $invoice_id));

        $ret = $this->db->affected_rows();

        if ($ret > 0) {

            log_message('debug', 'Created ' . $ret . ' new orders');

            $this->create_order_items($invoice_id);
        }
    }

    public function update_invoice_item_product_ids($invoice_id) {

        log_message('INFO', 'Updating mcb_invoice_items.product_id for invoice: ' . $invoice_id);

        $sql = "UPDATE mcb_invoice_items i
			  INNER JOIN mcb_products p ON p.product_name = i.item_name
			    SET i.product_id = p.product_id
			  WHERE i.invoice_id = ?
			    AND i.product_id <> p.product_id";

        $this->db->query($sql, array($invoice_id));
    }

    /*
     * Create products that are in a quote but are not linked with a product
     * This happens when adhoc items are entered in a quote that do not come from mcb_products
     *
     * Ignore items that will not be supplied (qty <= 0)
     *
     */

    public function create_invoice_new_products($invoice_id) {


        // Ignore duplicate item_names (only the details from the first will be used)
        $sql = "INSERT IGNORE
		       INTO mcb_products (
		            supplier_id, product_name, product_description, product_supplier_description, product_supplier_code, product_base_price)
		     SELECT 0, item_name, item_description, item_description, item_name, item_price
			   FROM mcb_invoice_items i
			   LEFT JOIN mcb_products p ON p.product_name = i.item_name
			  WHERE invoice_id = ?
			    AND item_qty > 0
			    AND p.product_id IS NULL";


        $this->db->query($sql, array($invoice_id));


        return $this->db->affected_rows();
    }

    public function missing_invoice_product_suppliers($invoice_id) {


        $sql = "SELECT i.item_name
			   FROM mcb_invoice_items AS i
			   JOIN mcb_products AS p ON p.product_id = i.product_id
			  WHERE invoice_id = ?
			    AND supplier_id = 0 AND item_name != '' AND item_description != ''";

        $query = $this->db->query($sql, array($invoice_id));
        //print_r($this->db->last_query()); exit;

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
    
    function get_invoice_clients($invoice_id) {
        $this->load->model('invoices/mdl_invoices');
        $invoice = $this->mdl_invoices->get_by_id($invoice_id);
        if($invoice == NULL){
            $contact_id = 0;
            if ($_POST['contact_name'] != '') {
                $this->load->model('clients/mdl_contacts');
                $contact_id = $this->mdl_contacts->get_or_add_contact_by_name($this->input->post('supplier_id'), $this->input->post('contact_name'));
            }
            $invoice_arr->supplier_id = $this->input->post('supplier_id');
            $invoice_arr->contact_name = $this->input->post('contact_name');
            $invoice_arr->project_id =  $this->input->post('project_id');
            $invoice_arr->contact_id = $contact_id;
            $invoice = $invoice_arr;
        }
        return $invoice;
    }
    
    public function create_supplier_order($supplier, $invoice_id ) {
        //----- get invoice details---------
        $invoice = $this->get_invoice_clients($invoice_id);
        $this->load->model('sequences/mdl_sequences');
        $db_array = array(
            'supplier_id' => $supplier->client_id,
            'contact_id' => $invoice->contact_id,
            'invoice_id' => $invoice_id,
            'project_id' => $invoice->project_id,
            'order_number' => $this->mdl_sequences->get_next_value(2),
            'order_tax_rate_id' => $supplier->client_tax_rate_id,
            'order_date_entered' => time(),
            'user_id' => $this->session->userdata('user_id'),
            'order_address_id' => $this->mdl_mcb_data->setting('default_order_address_id'),
            'order_status_id' => $this->mdl_mcb_data->setting('default_open_status_id'),
            'is_inventory_supplier' => '1',
            'inventory_id' => '0'
        );
        //---------save to database---------
        $this->db->trans_start();
        $this->db->insert($this->table_name, $db_array);
        $order_id = $this->db->insert_id();
        $this->db->trans_complete();
        return $order_id;
    }
    
    
    public function create_supplier_order_old($supplier_id, $contact_name, $invoice_id, $project_id, $date_entered, $strtotime = TRUE, $is_inventory_supplier, $is_new) {
        if ($strtotime) {
            $date_entered = strtotime(standardize_date($date_entered));
        }
        $this->load->model('clients/mdl_clients');
        $supplier = $this->mdl_clients->get_by_id($supplier_id);
        $contact_id = 0;
        if ($contact_name != '') {
            $this->load->model('clients/mdl_contacts');
            $contact_id = $this->mdl_contacts->get_or_add_contact_by_name($supplier_id, $contact_name);
        }
        $this->load->model('sequences/mdl_sequences');
        $db_array = array(
            'supplier_id' => $supplier_id,
            'contact_id' => $contact_id,
            'invoice_id' => $invoice_id,
            'project_id' => $project_id,
            'order_number' => $this->mdl_sequences->get_next_value(2),
            'order_tax_rate_id' => $supplier->client_tax_rate_id,
            'order_date_entered' => $date_entered,
            'user_id' => $this->session->userdata('user_id'),
            'order_address_id' => $this->mdl_mcb_data->setting('default_order_address_id'),
            'order_status_id' => $this->mdl_mcb_data->setting('default_open_status_id'),
            'is_inventory_supplier' => '1',
            'inventory_id' => '0'
        );
        // table: `mcb_orders`;
        $available_check = $this->check_existing_items_available_for_orders($invoice_id, $supplier_id);
        if ($is_new == TRUE) {
            $available_check = '1';
        }
        if ($available_check > 0) {
            $this->db->trans_start();
            $this->db->insert($this->table_name, $db_array);
            $order_id = $this->db->insert_id();
            $this->db->trans_complete();
        }

        //$this->save_invoice_history($invoice_id, $this->session->userdata('user_id'), $this->lang->line('created_invoice'));
        return $order_id;
    }
    
    function create_order_items($order_id, $supplier, $invoice_id, $all_inventories, $invoice_items) {
        
        $order_item_count = 0;
        //------ from inventory_item tables---------
        foreach ($all_inventories as $item) {
            $item = (object) $item;
            if ( $item->supplier_id == $supplier->client_id ) {
                if( ($item->invc_item_length > '0') && (strpos($item->name, '{mm}')) ){    
                    $item->name = mm_to_span($item->name,$item->invc_item_length,1000);
                }
                if( ($item->invc_item_length > '0') && (strpos($item->description, '{mm}')) ){
                    $item->description = mm_to_span($item->description,$item->invc_item_length,1000);
                }
                if( ($item->invc_item_length > '0') && (strpos($item->supplier_code, '{mm}')) ){    
                    $item->supplier_code = mm_to_span($item->supplier_code,$item->invc_item_length,1000);
                }
                if( ($item->invc_item_length > '0') && (strpos($item->supplier_description, '{mm}')) ){
                    $item->supplier_description = mm_to_span($item->supplier_description,$item->invc_item_length,1000);
                }
                //preparing array in put into mcb_order_inventory_items
                //this is the table that is going to be used
                $data = array(
                    'order_id' => $order_id,
                    'inventory_id' => $item->inventory_id,
                    'product_id' => $item->product_id,
                    'item_name' => $item->name,
                    'item_type' => $item->invc_item_type,
                    'item_description' => $item->description,
                    'item_qty' => $item->order_qty, //2 products = 4 inventory items
                    'item_supplier_price' => $item->supplier_price,
                    'item_supplier_code' => $item->supplier_code,
                    'item_supplier_description' => $item->supplier_description,
                    'item_index' => $item->invc_item_index,
                    'item_length' => $item->invc_item_length,
                    'item_per_meter' => $item->supplier_price
                );
                                
                //check if $i_item->product_id is dynamic, $i_item->invoice_item_id, if item_length > 0, 
                if ($data['item_length'] > '0') {
                    $data['item_supplier_price'] = $item->invc_item_length*$item->supplier_price;
                    $data['item_qty'] = $data['item_qty']/$data['item_length'];
                }
                
                // ---------insert into database-------
                $this->db->insert('mcb_order_inventory_items', $data);
                $i = $this->db->insert_id();
                
                if ($i > 0) {
                    //-------update open order quantity-------
                    $inv_itm = $this->get_Row('mcb_inventory_item', array('inventory_id' => $item->inventory_id));
                    $this->update('mcb_inventory_item', array('open_order_qty' => ($inv_itm->open_order_qty + $data['item_qty'] )), array('inventory_id' => $item->inventory_id));
                    $order_item_count++;
                    
                    //----record on history-------
                    if($item->available_qty < $data['item_qty'] ){
                        $data['item_qty'] = $item->available_qty;
                    }
                    if($data['item_qty'] > 0){                    
                        $h_data = array(
                            'user_id' => $this->session->userdata('user_id'),
                            'order_id' => $order_id,
                            'created_date' => date('Y-m-d H:i:s'),
                            'order_history_data' => 'Item <b>' . $data['item_name'] . '</b> had ' . $data['item_qty'] . ' in stock. So used ' . $data['item_qty'] . ' items for this order from stock.'
                        );
                        $this->db->insert('mcb_order_history', $h_data);
                    }
                }
            }
        }
        
        //record in history
        if($order_item_count > 0){
            $data = array(
                'user_id' => $this->session->userdata('user_id'),
                'order_id' => $order_id,
                'created_date' => date('Y-m-d H:i:s'),
                'order_history_data' => 'Order: ' . $order_id . ' created ' . $order_item_count . ' new order items'
            );
            $this->db->insert('mcb_order_history', $data);
            log_message('debug', 'Order: ' . $order_id . ' created ' . $order_item_count . ' new order items');
        }
    }
    
    
    public function create_invoice_orders($invoice_id, $all_inventories, $invoice_items) {
        
        $err_msg = [];
        $warning_msg = [];
        $msg_str = '';
        //---- prepare all suppliers--------
        $supplierids = array_unique(array_column($all_inventories, 'supplier_id'));
        $suppliers = array();
        foreach ($supplierids as $suplier_id) {
            $suppliers[] = $this->queryRow('SELECT client_id, client_name, client_tax_rate_id FROM mcb_clients WHERE client_id = "'.$suplier_id.'"');
        }
        
        //----- for each suppliers -------
        foreach ($suppliers as $supplier) {
            //-----create order-----
            //xxxxxxxxxxxxxxxxxxxxxxxxxxx
            $order_id = $this->create_supplier_order($supplier, $invoice_id);
            $all_orders[] = $order_id;
            //-----record in history-------
            $data = array(
                'user_id' => $this->session->userdata('user_id'),
                'order_id' => $order_id,
                'created_date' => date('Y-m-d H:i:s'),
                'order_history_data' => 'Created new order for supplier: ' . $supplier->client_name
            );
            $this->db->insert('mcb_order_history', $data);
            log_message('INFO', 'Created new order for supplier: ' . $supplier->client_name);
            //========create order items===========
            $this->create_order_items($order_id, $supplier, $invoice_id, $all_inventories, $invoice_items);
            
            //=============this is for messages==========
            $products = $this->get_order_items($order_id);
            $this->load->model('products/mdl_products_inventory');
            $this->load->model('inventory/mdl_inventory_history');
            foreach ($products as $product) {
                $inventories = $this->mdl_products_inventory->get_by_product_id($product->product_id);
                foreach ($inventories as $inventory) {
                    $result = $this->mdl_inventory_history->inventory_qty_order_deduct($inventory->inventory_id, "-" . ($inventory->inventory_qty * $product->item_qty), "Order # " . $order_id);
                    if ($result == 'Negative') {
                        $name = $this->mdl_inventory_item->get_by_id($inventory->inventory_id)[0]->name;
                        $err_msg[] = $name . " is negative inventory";
                    } elseif ($result == 'Low') {
                        $name = $this->mdl_inventory_item->get_by_id($inventory->inventory_id)[0]->name;
                        //$warning_msg[] = $name . " is low in inventory";
                    }
                }
            }
            if (count($err_msg) > 0) {
                $this->session->set_flashdata('order_error', $err_msg);
            }
            if (count($warning_msg) > 0) {
                $this->session->set_flashdata('order_warning', $warning_msg);
            }
        }
        $res = true;
        $finres = array(
            'status' => $res,
            'message' => $msg_str,
            'all_orders' => $all_orders
        );
        return $finres;
    }

    public function create_invoice_orders_leatest($invoice_id, $order_date_entered) {

        $invoice_items = $this->get_invoice_itemsbyid($invoice_id);
        $is_inventory_supplier = FALSE;
        foreach ($invoice_items as $value) {
            if ($value->product_dynamic == '1') {
                $is_inventory_supplier = TRUE;
            }
        }
        if ($is_inventory_supplier == TRUE) {
            $this->config->set_item('ORDERTO', 'INVENTORYSUPPLIER');
            $is_inv_sup = '1';
        } else {
            $is_inv_sup = '0';
        }

        $res = false;
        $all_orders = array();
        $this->update_invoice_item_product_ids($invoice_id);
        $this->load->model('inventory/mdl_inventory_item');
        $err_msg = [];
        $warning_msg = [];
        $msg_str = '';
//        $new_product_count = $this->create_invoice_new_products($invoice_id);
        $new_product_count = 0;
        // Need to check for 'new' products with unknown supplier
        // Will not create orders if such products exist
        // Need to re-direct user to product edit screens
        // where the details for new products can be completed
        if ($new_product_count > 0) {
            $msg_str .= 'Please update the products before converting to order. <a href="' . site_url() . '/quotes/edit/invoice_id/' . $invoice_id . '">Back to Quote</a>';
            // Update again to pick up the new product id's
            $this->update_invoice_item_product_ids($invoice_id);
        } else {
//            if ($this->missing_invoice_product_suppliers($invoice_id)) {
//                log_message('INFO', 'Some products have no supplier for invoice_id: ' . $invoice_id);
//                $msg_str .= 'Could not convert to order. Some products have no supplier for invoice_id: ' . $invoice_id . '</br>';
//            } else {
                $this->load->model('invoices/mdl_invoices');
                $invoice = $this->mdl_invoices->get_by_id($invoice_id);
                log_message('INFO', 'Creating orders for invoice: ' . $invoice_id);
                //we are changing the logic so that the orders now will be directly sent to Inventory
                if ($this->config->item('ORDERTO') == 'INVENTORYSUPPLIER') {
                    $sql = "SELECT DISTINCT i.invoice_id, ii.supplier_id, c.client_name
                        FROM mcb_invoice_items i
                        LEFT JOIN mcb_products_inventory AS pi ON pi.product_id = i.product_id
                        LEFT JOIN mcb_inventory_item AS ii ON pi.inventory_id = ii.inventory_id
                        LEFT JOIN mcb_clients c ON c.client_id = ii.supplier_id
                        LEFT JOIN mcb_products p ON p.product_id = i.product_id
                        WHERE i.invoice_id = ?
                        AND IF( p.product_dynamic >0, ( i.item_qty * i.item_length ), (i.item_qty) ) >0";
                } else {
                    $sql = "SELECT DISTINCT p.supplier_id, c.client_name
                        FROM mcb_invoice_items i
                        INNER JOIN mcb_products p ON p.product_id = i.product_id
                        INNER JOIN mcb_clients c ON c.client_id = p.supplier_id
                        WHERE i.invoice_id = ?
                        AND i.item_qty > 0";
                }
                $suppliers = $this->db->query($sql, array($invoice_id))->result();

//                echo "<pre>";
//                print_r($suppliers);
//                die;

                /*
                 * Maybe each client/supplier needs a default contact?
                 */
                foreach ($suppliers as $supplier) {

                    $order_id = $this->create_supplier_order($supplier->supplier_id, '', $invoice_id, $invoice->project_id, time(), FALSE, $is_inv_sup);
                    $all_orders[] = $order_id;


//                    echo "<pre>";
//                    print_r($order_id);
//                    die;
                    //record in history
                    $data = array(
                        'user_id' => $this->session->userdata('user_id'),
                        'order_id' => $order_id,
                        'created_date' => date('Y-m-d H:i:s'),
                        'order_history_data' => 'Created new order for supplier: ' . $supplier->client_name
                    );
                    $this->db->insert('mcb_order_history', $data);
                    log_message('INFO', 'Created new order for supplier: ' . $supplier->client_name);

                    $this->create_order_items($order_id, $invoice_id);

                    $products = $this->get_order_items($order_id);

                    $this->load->model('products/mdl_products_inventory');
                    $this->load->model('inventory/mdl_inventory_history');
                    foreach ($products as $product) {
                        $inventories = $this->mdl_products_inventory->get_by_product_id($product->product_id);
                        foreach ($inventories as $inventory) {
                            $result = $this->mdl_inventory_history->inventory_qty_order_deduct($inventory->inventory_id, "-" . ($inventory->inventory_qty * $product->item_qty), "Order # " . $db_array['order_id']);
                            if ($result == 'Negative') {
                                $name = $this->mdl_inventory_item->get_by_id($inventory->inventory_id)[0]->name;
                                $err_msg[] = $name . " is negative inventory";
                            } elseif ($result == 'Low') {
                                $name = $this->mdl_inventory_item->get_by_id($inventory->inventory_id)[0]->name;
//                                 $warning_msg[] = $name . " is low in inventory";
                            }
                        }
                    }

                    if (count($err_msg) > 0) {
                        $this->session->set_flashdata('order_error', $err_msg);
                    }
                    if (count($warning_msg) > 0) {
                        $this->session->set_flashdata('order_warning', $warning_msg);
                    }
                }
                $res = true;
//            }
        }
        $finres = array(
            'status' => $res,
            'message' => $msg_str,
            'all_orders' => $all_orders
        );
        return $finres;
    }

    function check_existing_items_available_for_orders($invoice_id, $supplier_id) {
        $c = 0;
//        $invDetail = $this->get_Row('mcb_inventory_item', array('inventory_id' => $inv_id));
        $invoice_items = $this->get_invoice_itemsbyid($invoice_id);
        $this->load->model('products/mdl_products');
        if ((sizeof($invoice_items) > 0)) {
            foreach ($invoice_items as $i_item) {
                $linked_invs = $this->get_related_invs($i_item->product_id);
                foreach ($linked_invs as $li) {

                    if ((sizeof($linked_invs) > 0) && ($li->supplier_id == $supplier_id )) {
                        $data = array(
                            'order_id' => $order_id,
                            'inventory_id' => $li->inventory_id,
                            'product_id' => $i_item->product_id,
                            'item_qty' => $i_item->item_qty * $li->inventory_qty, //2 products = 4 inventory items
                        );

                        if (($i_item->item_length > 0) && (($li->use_length > '0'))) {
                            $data['item_supplier_price'] = $i_item->product_supplier_price;
                            $data['item_qty'] = $i_item->item_qty * $li->inventory_qty * $i_item->item_length;
                        }

                        $min_product_stock_available = $this->getMinimumPossibleProduct($i_item->product_id);
                        if (($i_item->item_length > '0') && ($li->use_length > '0')) {
                            $update_qty = ($data['item_qty'] - ($min_product_stock_available / $i_item->item_length) >= 0) ? ($data['item_qty'] - ($min_product_stock_available / $i_item->item_length)) : 0;
                        } else {
                            $update_qty = ($data['item_qty'] - $min_product_stock_available >= 0) ? ($data['item_qty'] - $min_product_stock_available) : 0;
                        }
//                        echo $min_product_stock_available;
//                        echo "<br>";
//                        echo $data['item_qty'];
//                        echo "<br>";
//                        echo $update_qty;
//                        die;
//                        $update_qty = ($data['item_qty'] - $min_product_stock_available >= 0) ? ($data['item_qty'] - $min_product_stock_available) : 0;
                        if ($update_qty > '0') {
                            $c++;
                        }
                    }
                }
            }
        }
        return $c;
    }

    function create_order_items_old($order_id, $invoice_id = NULL) {

        $orderDetail = $this->get_Row('mcb_orders', array('order_id' => $order_id));
        if ($orderDetail->is_inventory_supplier == '1') {
            $this->config->set_item('ORDERTO', 'INVENTORYSUPPLIER');
        }

        if ($this->config->item('ORDERTO') == 'INVENTORYSUPPLIER') {
            //we need a logic here so that existing db structure works
            //we take items from invoice, get the inventories and put it in mcb_order_inventory_items
            $order_inv_items = array();
            $c = 0;
            if ($invoice_id) {
                $invoice_items = $this->get_invoice_itemsbyid($invoice_id);
                $this->load->model('products/mdl_products');
                if ((sizeof($invoice_items) > 0)) {
                    foreach ($invoice_items as $i_item) {
                        $linked_invs = $this->get_related_invs($i_item->product_id);
                        foreach ($linked_invs as $li) {
                            if ((sizeof($linked_invs) > 0) && ($li->supplier_id == $orderDetail->supplier_id )) {
                                if( ($i_item->item_length > '0') && (strpos($li->name, '{mm}')) ){    
                                    $li->name = mm_to_span($i_item->name, $i_item->item_length, 1000);
                                }
                                if( ($i_item->item_length > '0') && (strpos($li->description, '{mm}')) ){
                                    $li->description = mm_to_span($i_item->description, $i_item->item_length, 1000);
                                }
                                if( ($i_item->item_length > '0') && (strpos($li->supplier_code, '{mm}')) ){    
                                    $li->supplier_code = mm_to_span($i_item->supplier_code, $i_item->item_length, 1000);
                                }
                                if( ($i_item->item_length > '0') && (strpos($li->supplier_description, '{mm}')) ){
                                    $li->supplier_description = mm_to_span($i_item->supplier_description, $i_item->item_length, 1000);
                                }
                                //preparing array in put into mcb_order_inventory_items
                                //this is the table that is going to be used
                                $data = array(
                                    'order_id' => $order_id,
                                    'inventory_id' => $li->inventory_id,
                                    'product_id' => $i_item->product_id,
//                                    'item_name' => $i_item->item_name,
                                    'item_name' => $li->name,
                                    'item_type' => $i_item->item_type,
//                                    'item_description' => $i_item->item_description,
                                    'item_description' => $li->description,
                                    'item_qty' => $i_item->item_qty * $li->inventory_qty, //2 products = 4 inventory items
                                    'item_supplier_price' => $li->inventory_supplier_price,
                                    'item_supplier_code' => $li->supplier_code,
                                    'item_supplier_description' => $li->supplier_description,
                                    'item_index' => $i_item->item_index,
                                    'item_length' => $i_item->item_length,
                                    'item_per_meter' => $i_item->item_per_meter
                                );
                                //check if $i_item->product_id is dynamic, $i_item->invoice_item_id, if item_length > 0, 
                                if ($i_item->item_length > '0') {
                                    $data['item_supplier_price'] = $li->inventory_supplier_price*$i_item->item_length;
                                    $data['item_qty'] = ($i_item->item_qty * $li->inventory_qty);
                                }
                                $min_product_stock_available = $this->getMinimumPossibleProduct($i_item->product_id);
                                if (($i_item->item_length > '0') && ($li->use_length > '0')) {
                                    $update_qty = ($data['item_qty'] - ($min_product_stock_available / $i_item->item_length) >= 0) ? ($data['item_qty'] - ($min_product_stock_available / $i_item->item_length)) : 0;
                                    $open_order_qty = $update_qty * $i_item->item_length;
                                } else {
                                    $update_qty = ($data['item_qty'] - $min_product_stock_available >= 0) ? ($data['item_qty'] - $min_product_stock_available) : 0;
                                    $open_order_qty = $update_qty;
                                }
//                                echo '<pre>';
//                                echo $min_product_stock_available;
//                                echo '<br>';
//                                print_r($data['item_qty']);
//                                echo "<br>";
//                                echo $update_qty;
//                                print_r($data);
//                                die;
                                $order_inv_items[] = $li;
                                if ($update_qty > 0) {
                                    $this->db->insert('mcb_order_inventory_items', $data);
                                    $i = $this->db->insert_id();
                                    if ($i > 0) {
                                        $inv_itm = $this->get_Row('mcb_inventory_item', array('inventory_id' => $li->inventory_id));
                                        $this->update('mcb_inventory_item', array('open_order_qty' => ($inv_itm->open_order_qty + $open_order_qty )), array('inventory_id' => $li->inventory_id));
                                        $c++;
                                    }
                                }
                            }
                        }
                    }
                }
            }

//            echo "<pre>";
//            print_r($invoice_items);
//            print_r($order_inv_items);
//            die;

            $ret = $c;
        } else {
            $sql = "INSERT INTO mcb_order_items (order_id, product_id, item_name, item_type, item_description, item_qty, item_supplier_price, item_index, item_length, item_per_meter)
                    SELECT o.order_id, p.product_id, i.item_name, i.item_type, i.item_description, i.item_qty, p.product_supplier_price, i.item_index, i.item_length, i.item_per_meter
                    FROM mcb_orders o
                    INNER JOIN mcb_invoice_items i ON i.invoice_id = o.invoice_id
                    INNER JOIN mcb_products p ON p.product_id = i.product_id
                    WHERE o.order_id = ?
                    AND i.item_qty > 0
                    AND p.supplier_id = o.supplier_id";

            $this->db->query($sql, array($order_id));
            $ret = $this->db->affected_rows();
        }

        if ($ret > 0) {

            //going to implement the order_item qty update :: If there are some products in stock, we will use from stock
            $order_items = $this->get_order_itemsbyid($order_id);

//            echo "<pre>";
//            print_r($order_items);
//            die;

            if (sizeof($order_items) > 0) {
                foreach ($order_items as $o_item) {

                    $product_id = $o_item->product_id;
                    $min_product_stock_available = $this->getMinimumPossibleProduct($product_id);
                    $update_qty = ($o_item->item_qty - $min_product_stock_available >= 0) ? ($o_item->item_qty - $min_product_stock_available) : 0;
                    $used_qty = ($o_item->item_qty - $min_product_stock_available >= 0) ? $min_product_stock_available : ($min_product_stock_available - $o_item->item_qty);

                    if (($o_item->use_length == '1') && ($o_item->item_length > '0')) {
                        $min_product_stock_available = floor($min_product_stock_available / $o_item->item_length);
                        $update_qty = ($o_item->item_qty - $min_product_stock_available >= 0) ? ($o_item->item_qty - $min_product_stock_available) : 0;
                        $used_qty = ($o_item->item_qty - $min_product_stock_available >= 0) ? $min_product_stock_available : ($min_product_stock_available - $o_item->item_qty);
                    }

                    $o_data = array(
                        'item_qty' => $update_qty
                    );
                    $this->db->where('order_item_id', $o_item->order_item_id);
                    if ($this->config->item('ORDERTO') == 'INVENTORYSUPPLIER') {
                        $update = $this->db->update('mcb_order_inventory_items', $o_data);
                        if ($update) {
                            
                        }
                    } else {
                        $update = $this->db->update('mcb_order_items', $o_data);
                    }
                    if ($update) {
                        if ($used_qty > 0) {
                            //let show this update in history
                            $h_data = array(
                                'user_id' => $this->session->userdata('user_id'),
                                'order_id' => $order_id,
                                'created_date' => date('Y-m-d H:i:s'),
                                'order_history_data' => 'Item <b>' . $o_item->item_name . '</b> had ' . $used_qty . ' in stock. So used ' . $used_qty . ' items for this order from stock.'
                            );
                            $this->db->insert('mcb_order_history', $h_data);
                        }
                    }
                }
            }

            //record in history
            $data = array(
                'user_id' => $this->session->userdata('user_id'),
                'order_id' => $order_id,
                'created_date' => date('Y-m-d H:i:s'),
                'order_history_data' => 'Order: ' . $order_id . ' created ' . $ret . ' new order items'
            );
            $this->db->insert('mcb_order_history', $data);
            log_message('debug', 'Order: ' . $order_id . ' created ' . $ret . ' new order items');
        }
    }

    public function getMinimumPossibleProduct($product_id) {
        $min_poss_product = 0;
        $sql = "select FLOOR(mii.qty/mpi.inventory_qty) as rel "
                . "from mcb_products_inventory as mpi "
                . "left join mcb_inventory_item as mii on mpi.inventory_id = mii.inventory_id "
                . "where mpi.product_id = '" . $product_id . "'";
        $q = $this->db->query($sql);
        $result = $q->result_array();

        if (sizeof($result) > 0) {
            $numbers = array_column($result, 'rel');
            $min_poss_product = min($numbers);
        }
        if($min_poss_product < 0){
            $min_poss_product = 0;
        }
        return $min_poss_product;
    }

    public function updateOrder($orderid, $data) {
        $this->db->where('order_id', $orderid);
        return $this->db->update('mcb_orders', $data);
    }

    public function get_order_history($order_id) {
        $this->db->select('oh.*,u.username,o.order_number');
        $this->db->where('oh.order_id', $order_id);
        $this->db->from('mcb_order_history as oh');
        $this->db->join('mcb_users as u', 'u.user_id = oh.user_id', 'left');
        $this->db->join('mcb_orders as o', 'o.order_id = oh.order_id', 'left');
        $q = $this->db->get();
        return $q->result();
    }

    public function query($qry) {
        $q = $this->db->query($qry);
        return $q->result_array();
    }

    public function query_object($qry) {
        $q = $this->db->query($qry);
        $Res = $q->result();
        if($Res != NULL){
            return $Res;
        }
        return FALSE;
    }
    

    public function queryRow($qry) {
        $q = $this->db->query($qry);
        return $q->row();
    }

    public function get_Row($tbl_name, $condition) {

        $this->db->where($condition);
        $q = $this->db->get($tbl_name);
        $Res = $q->row();
        return $Res;
    }

    function get_Where($tbl_name, $condition) {

        $this->db->where($condition);
        $q = $this->db->get($tbl_name);
        $Res = $q->result_array();
        return $Res;
    }

    public function update($tbl_name, $data, $condition) {
        
        $this->db->where($condition);
        $this->db->update($tbl_name, $data);
    }
    
    
    function m_Delete($tbl_name, $condition) {
        
        $this->db->delete($tbl_name, $condition);
        return TRUE;
    }
    
    
    function insert($tableName, $data) {
        $this->db->insert($tableName, $data);
        $rid = $this->db->insert_id();
        if($rid > 0){
            return $rid;
        } else {
            return FALSE;
        }
    }
    
    public function get_order_itemsbyid($ordeid) {

        $orderDetail = $this->get_Row('mcb_orders', array('order_id' => $ordeid));

//        echo "<pre>";
//        print_r($orderDetail);
//        die;

        if ($orderDetail->is_inventory_supplier == '1') {
            $sql = "SELECT *, mii.use_length "
                    . "FROM mcb_order_inventory_items as moii "
                    . "LEFT JOIN mcb_inventory_item as mii ON moii.inventory_id = mii.inventory_id "
                    . "WHERE moii.order_id = '" . $ordeid . "'";
        } else {
            $sql = "SELECT *, mii.use_length "
                    . "FROM mcb_order_items as moi "
                    . "LEFT JOIN mcb_inventory_item as mii ON moi.inventory_id = mii.inventory_id "
                    . "WHERE moi.order_id = '" . $ordeid . "'";
        }
        $q = $this->db->query($sql);
        return $q->result();
    }

    public function getOrderNumber($orderid) {
        $this->db->select('order_number');
        $this->db->where('order_id', $orderid);
        $q = $this->db->get('mcb_orders');
        $res = $q->row();
        return $res->order_number;
    }

    public function get_invoice_itemsbyid($invoice_id) {

        $this->db->select('ii.*,p.supplier_id,p.product_supplier_price, p.product_dynamic');
        $this->db->where('ii.invoice_id', $invoice_id);
        $this->db->from('mcb_invoice_items as ii');
        $this->db->join('mcb_products as p', 'p.product_id = ii.product_id', 'left');
        $q = $this->db->get();
        //print_r($this->db->last_query()); exit;
        return $q->result();
    }

    public function get_related_invs($product_id) {
		//make sure the product ->inventory link is inventory group.. incase of relationships that shouldn't be there..
		
		 $sql = "Select pi.inventory_qty,ifnull(ii.qty,0) qty,pi.inventory_id,ii.supplier_id,ii.description,ii.supplier_price as inventory_supplier_price,ii.name,ii.use_length,ii.supplier_code,ii.supplier_description from mcb_products_inventory as pi inner join mcb_inventory_item as ii on ii.inventory_id = pi.inventory_id inner join mcb_inventory_item proi on proi.inventory_id=pi.product_id where pi.product_id= ".$product_id." and proi.inventory_type=1";
				/*
        $this->db->select('pi.inventory_qty,ii.qty,pi.inventory_id,ii.supplier_id,ii.description,'
                . 'ii.supplier_price as inventory_supplier_price,'
                . 'ii.name,ii.use_length,ii.supplier_code,ii.supplier_description');
        $this->db->where('pi.product_id', $product_id);
        $this->db->from('mcb_products_inventory as pi');
        $this->db->join('mcb_inventory_item as ii', 'ii.inventory_id = pi.inventory_id', 'inner');
        */
		
		$q = $this->db->query($sql);
	
		// if not related inventory check just inventory table
		if ($q->num_rows() == 0) {
			
			$sql ="SELECT 1 as inventory_qty, ifnull(qty,0) qty, inventory_id, supplier_id, description, supplier_price as inventory_supplier_price, name, use_length, supplier_code,supplier_description from mcb_inventory_item where inventory_id=".$product_id;
			
			$q = $this->db->query($sql);
			
		}
        return $q->result();
    }

    public function reportMissingProductAndInventoryForOrder($all_orders, $inventory_missing_qty, $products_missing_inventory) {
        if (sizeof($all_orders) > 0) {
            foreach ($all_orders as $order_id) {

                //update products with missing inventory
                if (sizeof($products_missing_inventory) > 0) {
                    foreach ($products_missing_inventory as $pid) {

                        $data = array(
                            'order_id' => $order_id,
                            'product_id' => $pid
                        );
                        $this->db->insert('mcb_order_products_missing_inv', $data);
                    }
                }

                //update inventory with missing qty
                if (sizeof($inventory_missing_qty) > 0) {
                    foreach ($inventory_missing_qty as $iid) {

                        $data = array(
                            'order_id' => $order_id,
                            'inventory_id' => $pid
                        );
                        $this->db->insert('mcb_order_inventory_null', $data);
                    }
                }
            }
        }
        return true;
    }

    public function order_products_missing_inv($order_id) {
        //$sql = "select mp.product_id,p.product_name from mcb_order_products_missing_inv as mp inner join mcb_products as p on mp.product_id = p.product_id where mp.order_id = '".$order_id."' and p.product_active = '1'";

        $orderDetail = $this->get_Row('mcb_orders', array('order_id' => $order_id));
        if ($orderDetail->is_inventory_supplier == '1') {
            $this->config->set_item('ORDERTO', 'INVENTORYSUPPLIER');
        }

        if ($this->config->item('ORDERTO') == 'INVENTORYSUPPLIER') {
            $sql = "select ii.product_id,p.product_name from mcb_order_inventory_items as ii inner join mcb_products as p on p.product_id = ii.product_id where ii.order_id = '" . $order_id . "' and p.product_active = '1'";
        } else {
            $sql = "select ii.product_id,p.product_name from mcb_order_items as ii inner join mcb_products as p on p.product_id = ii.product_id where ii.order_id = '" . $order_id . "' and p.product_active = '1'";
        }

        $q = $this->db->query($sql);
        $result = $q->result();

        $fin = array();

        if (sizeof($result) > 0) {
            foreach ($result as $res) {
                $linked_invs = $this->get_related_invs($res->product_id);
                if (sizeof($linked_invs) == 0) {
                    $fin[] = $res;
                }
            }
        }
        return $fin;
    }

    public function order_inventory_null($order_id) {
        $sql = "select mp.inventory_id,p.name as inventory_name from mcb_order_inventory_null as mp inner join mcb_inventory_item as p on mp.inventory_id = p.inventory_id where mp.order_id = '" . $order_id . "'";
        $q = $this->db->query($sql);
        return $q->result();
    }

    function getOrderMixedInventoryLengthStatus($order_id) {

        $is_mixed = FALSE;
        $orderItms = $this->get_Where('mcb_order_items', array('order_id' => $order_id));
        foreach ($orderItms as $value_order) {

            $qry = 'SELECT ii.inventory_id, ii.use_length, mcb_products_inventory.product_id '
                    . 'FROM mcb_inventory_item as ii '
                    . 'INNER JOIN mcb_products_inventory ON ii.inventory_id=mcb_products_inventory.inventory_id '
                    . 'WHERE mcb_products_inventory.product_id="' . $value_order['product_id'] . '"';
            $pro_inv = $this->query($qry);

            $lenCount = 0;
            foreach ($pro_inv as $value_p_i) {
                if ($value_p_i['use_length'] == '1') {
                    $lenCount++;
                }
            }
            if ((sizeof($pro_inv) != 0) && (sizeof($pro_inv) != ($lenCount)) && ($lenCount > 0)) {
                $is_mixed = TRUE;
            }
        }
        return $is_mixed;
    }

    
    function total_order_amount($order_id) {
        
        // $chk = $this->get_Row('mcb_order_inventory_items', array('order_id' => $order_id));
        $chk = $this->get_Row('mcb_orders', array('order_id' => $order_id))->is_inventory_supplier;
        if ($chk == '1') {
            $table_name = 'mcb_order_inventory_items';
        } else {
            $table_name = 'mcb_order_items';
        }
        
        $sql_order_amount = "SELECT SQL_CALC_FOUND_ROWS mcb_orders.*, "
                    . "tax_rate_percent, t.tax_rate_name, CONCAT(FORMAT(t.tax_rate_percent, 0), '% ', t.tax_rate_name) "
                    . "tax_rate_percent_name, FORMAT(order_sub_total, 2) order_sub_total, FORMAT(order_sub_total * IFNULL(t.tax_rate_percent, 0)/100, 2) "
                    . "tax_total, FORMAT(order_sub_total * (1 + IFNULL(t.tax_rate_percent, 0)/100), 2) order_total, mcb_invoices.invoice_number, "
                    . "mcb_invoices.invoice_is_quote, "
                    . "mcb_currencies.*, prj.project_id, prj.project_name, "
                    . "mcb_invoice_statuses.invoice_status AS order_status, mcb_invoice_statuses.invoice_status_type AS order_status_type "
                    . "FROM (mcb_orders) LEFT JOIN (SELECT order_id, SUM(item_qty * item_supplier_price) order_sub_total FROM `".$table_name."` "
                    . "GROUP BY order_id) AS i ON i.order_id = mcb_orders.order_id "
                    . "JOIN mcb_clients ON mcb_clients.client_id = mcb_orders.supplier_id "
                    . "LEFT JOIN mcb_contacts AS con ON con.contact_id = mcb_orders.contact_id "
                    . "JOIN mcb_invoice_statuses ON mcb_invoice_statuses.invoice_status_id = mcb_orders.order_status_id "
                    . "LEFT JOIN mcb_invoices ON mcb_invoices.invoice_id = mcb_orders.invoice_id LEFT JOIN mcb_projects AS prj ON "
                    . "prj.project_id = mcb_orders.project_id LEFT JOIN mcb_tax_rates AS t ON t.tax_rate_id = mcb_orders.order_tax_rate_id "
                    . "JOIN mcb_currencies ON mcb_currencies.currency_id = mcb_clients.client_currency_id WHERE `mcb_orders`.`order_id` = '" . $order_id . "' "
                    . "ORDER BY FROM_UNIXTIME(mcb_orders.order_date_entered) DESC, mcb_orders.order_number DESC";
            return $this->queryRow($sql_order_amount);
    }
    
    public function get_order_items_JSON($order_id) {
        
        // $chk = $this->get_Row('mcb_order_inventory_items', array('order_id' => $order_id));
        $chk = $this->get_Row('mcb_orders', array('order_id' => $order_id))->is_inventory_supplier;
        if ($chk == '1') {
            $sql_order_items = 'SELECT *, oii.order_item_id as id, '
                    . 'FORMAT((oii.item_qty * oii.item_supplier_price),2) AS item_subtotal, '
                    . 'oii.stock_status,'
                    . 'IF(oii.stock_status = "1", "Stock Out", "Stock In") AS stock_action, 1 as is_inv_sup,  '
                    . 'mcb_inventory_item.use_length as item_use_length, mcb_inventory_item.qty as item_available_qty '
                    . 'FROM mcb_order_inventory_items AS oii '
                    . 'LEFT JOIN mcb_inventory_item ON oii.inventory_id = mcb_inventory_item.inventory_id '
                    . 'WHERE oii.order_id = "' . $order_id . '" '
                    . 'ORDER BY oii.item_order';
            
        } else {
            
            $sql_order_items = 'SELECT *, oii.order_item_id as id, '
                    . 'FORMAT((oii.item_qty * oii.item_supplier_price),2) AS item_subtotal, '
                    . 'oii.stock_status, '
                    . 'IF(oii.stock_status = "1", "Stock Out", "Stock In") AS stock_action, 0 as is_inv_sup '
                    . 'FROM mcb_order_items AS oii '
                    . 'WHERE oii.order_id = "' . $order_id . '" '
                    . 'ORDER BY oii.item_order';
        }
        $fin->sql_order_items = $this->query_object($sql_order_items);
        $fin->sql_order_amount = $this->total_order_amount($order_id);
        return $fin;
    }

    function update_order_items_json($order_id, $items) {

        // $chk = $this->get_Row('mcb_order_inventory_items', array('order_id' => $order_id));
        $chk = $this->get_Row('mcb_orders', array('order_id' => $order_id))->is_inventory_supplier;
        if ($chk == 1) {
            $sql = 'SELECT *, FORMAT((oii.item_qty * oii.item_supplier_price),2) AS item_subtotal, '
                    . 'oii.order_item_id AS id, '
                    . 'oii.stock_status,'
                    . 'IF(oii.stock_status = "1", "Stock Out", "Stock In") AS stock_action, 1 as is_inv_sup,  '
                    . 'mcb_inventory_item.use_length as item_use_length '
                    . 'FROM mcb_order_inventory_items AS oii '
                    . 'LEFT JOIN mcb_inventory_item ON oii.inventory_id = mcb_inventory_item.inventory_id '
                    . 'WHERE oii.order_item_id = "' . $items->order_item_id . '"';
            $tableName = 'mcb_order_inventory_items';
        } else {
            $sql = 'SELECT *, FORMAT((oii.item_qty * oii.item_supplier_price),2) AS item_subtotal, '
                    . 'oii.order_item_id AS id, '
                    . 'oii.stock_status, '
                    . 'IF(oii.stock_status = "1", "Stock Out", "Stock In") AS stock_action, 0 as is_inv_sup '
                    . 'FROM mcb_order_items AS oii '
                    . 'WHERE oii.order_item_id = "' . $items->order_item_id . '"';
            $tableName = 'mcb_order_items';
        }
        
        $old_item = $this->common_model->get_row($tableName, array('order_item_id'=>$items->order_item_id));
        if($old_item->item_name != $items->item_name){
            if ($items->item_name !== '') {
                $db_item = $this->common_model->get_row('mcb_inventory_item', array('name'=>span_to_mm($items->item_name), 'supplier_id'=>$items->supplier_id));
                if(($db_item !== FALSE)){
                    $items->inventory_id = $db_item->inventory_id;
                    $items->item_name = $db_item->name;
                    $items->item_description = $db_item->description;
                    $items->item_supplier_price = $db_item->supplier_price;
                    $items->item_supplier_description = $db_item->supplier_description;
                    $items->item_supplier_code = $db_item->supplier_code;
                }
            }
        }
        
        
        
        
        
        if($items->use_length == '1'){
            
            
            if ($items->item_length > '0') {
                $items->item_supplier_price = $items->item_length*$items->item_per_meter;
            }
            
            if( ($items->item_length > '0') && (strpos($items->item_name, '{mm}')) ){    
                $items->item_name = mm_to_span($items->item_name, $items->item_length, 1000);
            }
            
            if( ($items->item_length > '0') && (strpos($items->item_description, '{mm}')) ){
                $items->item_description = mm_to_span($items->item_description, $items->item_length, 1000);
            }
            if( ($items->item_length > '0') && (strpos($items->item_supplier_code, '{mm}')) ){    
                $items->item_supplier_code = mm_to_span($items->item_supplier_code, $items->item_length, 1000);
            }
            
            if( ($items->item_length > '0') && (strpos($items->item_supplier_description, '{mm}')) ){
                $items->item_supplier_description = mm_to_span($items->item_supplier_description, $items->item_length, 1000);
            }
            
//            echo '<pre>';
//            print_r($db_item);
//            print_r($items);
//            exit;
            
            $items->item_supplier_code = span_to_mm($items->item_supplier_code);
            $items->item_supplier_code = mm_to_span($items->item_supplier_code, $items->item_length, 1000);
            
            
            $items->item_supplier_description = span_to_mm($items->item_supplier_description);
            $items->item_supplier_description = mm_to_span($items->item_supplier_description, $items->item_length, 1000);
//            $wid = explode('<span>',$items->item_supplier_description);
//            $fid = $wid[0];
//            $w2id = explode('</span>',$wid[1]);
//            $lid = $w2id[1];
//            if( ($lid != '') || ($lid != NULL) ){
//                $items->item_supplier_description = $fid.'<span>'.($items->item_length)*(1000).'</span>'.$lid;
//            }
            $items->item_name = span_to_mm($items->item_name);
            $items->item_name = mm_to_span($items->item_name, $items->item_length, 1000);
//            die;
//            $w = explode('<span>',$items->item_name);
//            $f = $w[0];
//            $w2 = explode('</span>',$w[1]);
//            $l = $w2[1];
//            if( ($l != '') || ($l != NULL) ){
//                $items->item_name = $f.'<span>'.($items->item_length)*(1000).'</span>'.$l;
//            }
            
            $items->item_description = span_to_mm($items->item_description);
            $items->item_description = mm_to_span($items->item_description, $items->item_length, 1000);
//            $wd = explode('<span>',$items->item_description);
//            $fd = $wd[0];
//            $w2d = explode('</span>',$wd[1]);
//            $ld = $w2d[1];
//            if( ($ld != '') || ($ld != NULL) ){
//                $items->item_description = $fd.'<span>'.($items->item_length)*(1000).'</span>'.$ld;
//            }
            
        }
        
//        echo '<pre>';
//        print_r($items);
//        exit;
        
        
        $itmeData = array(
            'order_item_id' => $items->order_item_id,
            'inventory_id' => $items->inventory_id,
            'item_qty' => $items->item_qty,
            'item_name' => $items->item_name,
            'item_description' => $items->item_description,
            'item_supplier_code' => $items->item_supplier_code,
            'item_supplier_description' => $items->item_supplier_description,
            'item_supplier_price' => $items->item_supplier_price,
            'item_type' => $items->item_type,
            'item_length' => $items->item_length,
            'item_per_meter' => $items->item_per_meter
        );
        $condition = array('order_item_id' => $items->order_item_id);
        $this->update($tableName, $itmeData, $condition);
        
        $result->updateData = $this->queryRow($sql);
        $result->order_amounts = $this->total_order_amount($order_id);
        return $result;
    }
    
    function addNewOrderItemJSON($order_id, $item) {
        
        $invDeta = $this->get_Row('mcb_inventory_item',array('name'=>trim($item->item_name)));
        if($invDeta != NULL){
            $item_name = $invDeta->name;
            $item_length = $invDeta->use_length;
            $item_description = $invDeta->description;
            $item->item_supplier_code = $invDeta->supplier_code;
            $item->item_supplier_description = $invDeta->supplier_description;
            $item_supplier_price = $invDeta->supplier_price;
            
            if( ($item_length > '0') && (strpos($item_name, '{mm}')) ){
                $item_name = mm_to_span($item_name, $item_length, 1000);
            }
            if( ($item_length > '0') && (strpos($item_description, '{mm}')) ){
                $item_description = mm_to_span($item_description, $item_length, 1000);
            }
            if( ($item_length > '0') && (strpos($item->item_supplier_code, '{mm}')) ){
                $item->item_supplier_code = mm_to_span($item->item_supplier_code, $item_length, 1000);
            }
            if( ($item_length > '0') && (strpos($item->item_supplier_description, '{mm}')) ){
                $item->item_supplier_description = mm_to_span($item->item_supplier_description, $item_length, 1000);
            }
            
        }else{
            $item_name = $item->item_name;
            $item_description = $item->item_description;
            $item_length = $item->item_length;
            $item->supplier_code = $item->item_supplier_code;
            $item->item_supplier_description = $item->item_supplier_description;
            $item_supplier_price = $item->item_supplier_price;
        }
        
        $data = array(
            'order_id' =>$order_id,
            'product_id' =>'0',
            'item_name' =>$item_name,
            'item_length' =>(isset($invDeta->use_length) && $invDeta->use_length > 0)?'1':'',
            'item_type' => '',
            'item_description' =>$item_description,
            'item_qty' =>'1',
            'item_per_meter' => $item_supplier_price,
            'item_supplier_code'=>$item->item_supplier_code,
            'item_supplier_description'=>$item->item_supplier_description,
            'item_supplier_price' =>$item_supplier_price,
            'item_index' => '1',
        );
        
        // $chk = $this->get_Row('mcb_order_inventory_items', array('order_id' => $order_id));
        $chk = $this->get_Row('mcb_orders', array('order_id' => $order_id))->is_inventory_supplier;
        if ($chk == '1') {
            $data['inventory_id'] = $invDeta->inventory_id;
            $use_len_qry = ', mcb_inventory_item.use_length as item_use_length ';
            $inv_relation = 'LEFT JOIN mcb_inventory_item ON oii.inventory_id = mcb_inventory_item.inventory_id ';
            $tableName = 'mcb_order_inventory_items';
        }else{
            $use_len_qry = '';
            $inv_relation = '';
            $tableName = 'mcb_order_items';   
        }
        
        $id = $this->insert($tableName,$data);
        
        $new_item = $this->mdl_orders->get_Row($tableName, array('order_item_id'=>$id));
        
        $sql = 'SELECT *, FORMAT((oii.item_qty * oii.item_supplier_price),2) AS item_subtotal, '
                . 'IF(oii.stock_status = "1", "Stock Out", "Stock In") AS stock_action, '
                . 'oii.order_item_id AS id, 1 as is_inv_sup '
                .$use_len_qry. 'FROM '.$tableName.' AS oii '
                . $inv_relation. 'WHERE oii.order_item_id = "' . $id . '"';
        
        $result->addedData = $this->queryRow($sql);
        $result->order_amounts = $this->total_order_amount($order_id);
        return $result;
        
    }
    
    function updateOrderItemPosition($order, $itemid) {
        
        $chkTbl = $this->get_row('mcb_order_inventory_items', array('order_item_id'=>$itemid));
        $this->db->where('order_item_id', $itemid);
        $data = array(
            'item_order' => $order
        );
        if($chkTbl != NULL){
            return $this->db->update('mcb_order_inventory_items', $data);
        }else{
            return $this->db->update('mcb_order_items', $data);
        }
    }
    
    public function getSearchInventoryNameResults($search_string, $supplier_id) {
        
        $qry = "SELECT mcb_inventory_item.inventory_id AS id, "
                . "mcb_inventory_item.supplier_price AS item_supplier_price, mcb_inventory_item.description AS description, qty, "
                . "mcb_inventory_item.name AS item_name, mcb_inventory_item.name AS label, mcb_inventory_item.description AS item_description "
                . "FROM mcb_inventory_item "
                . "WHERE (mcb_inventory_item.name LIKE '%".$search_string."%') AND mcb_inventory_item.supplier_id = '".$supplier_id."' and is_arichved='0' and inventory_type='0'"
                . "LIMIT 5";
        return $this->query_object($qry);
    }
    
}

?>
