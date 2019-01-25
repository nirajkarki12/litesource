<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class Mdl_Statements extends MY_Model {


    public function __construct() {

        parent::__construct();

        $this->table_name = 'mcb_invoices';

        $this->primary_key = 'mcb_invoices.invoice_id';

        $this->select_fields = "
			SQL_CALC_FOUND_ROWS
			c.client_name,
			invoice_number,
			DATE_FORMAT(FROM_UNIXTIME(invoice_due_date),'%d/%m/%Y') due_date,
			IF(invoice_due_date < UNIX_TIMESTAMP(), 1, 0) invoice_is_overdue,
			IF(invoice_status_id <> 3,
			    IF(invoice_due_date < UNIX_TIMESTAMP(),
			    DATEDIFF( FROM_UNIXTIME(UNIX_TIMESTAMP()), FROM_UNIXTIME(invoice_due_date)),
			    0), 0)
			AS invoice_days_overdue,
			p.project_name,
			invoice_client_order_number,
            a.invoice_total";

        $this->joins = array(

            'mcb_clients AS c' 		    =>	'c.client_id = mcb_invoices.client_id',
            'mcb_projects AS p'	        =>	array(
                'p.project_id = mcb_invoices.project_id',
                'LEFT'
            ),
            'mcb_invoice_amounts AS a'	=>	'a.invoice_id = mcb_invoices.invoice_id',
        );


        $this->order_by = 'client_name, invoice_due_date, mcb_invoices.invoice_id';

        /*
         * IF(invoice_status_id <> 3,
			    IF(mcb_invoices.invoice_due_date < UNIX_TIMESTAMP(),
			    DATEDIFF( FROM_UNIXTIME(UNIX_TIMESTAMP()), FROM_UNIXTIME(invoice_due_date))
			    0),
			  0) AS invoice_days_overdue,
         */

    }

    public function get_total($params = NULL) {

        $statements = parent::get($params);
        $total_owed = 0;

        if (is_array($statements)) {

            foreach ($statements as $statement) {
                $total_owed += $statement->invoice_total;

            }

        }

        else {

            $total_owed += $statements->invoice_total;

        }

        return $total_owed;

    }
    
    function get_delivery_for_Pdf($client_id) {
        
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
        
        $qry = 'SELECT i.client_id, i.project_id, i.invoice_number, i.invoice_id, i.invoice_client_order_number, '
                . 'd.docket_id, d.docket_number, d.docket_date_entered, d.invoice_sent, d.docket_delivery_status, d.price_with_tax, '
                . 'p.project_name, p.project_id '
                . 'FROM ((mcb_invoices as i '
                . 'INNER JOIN mcb_delivery_dockets as d ON i.invoice_id = d.invoice_id) '
                . 'LEFT JOIN mcb_projects as p ON i.project_id = p.project_id) '
                . 'WHERE (i.client_id = '.$client_id.' AND ((d.invoice_sent = "1") AND (d.paid_status != "1")) AND ((i.invoice_status_id != "3" AND i.smart_status = "1") OR (i.smart_status = "0")) ) '
                . 'ORDER BY d.docket_date_entered DESC';
        $r = $this->db->query($qry)->result();
        
        foreach ($r as $value) {
            $r3 =  $this->db->query($sqlToCalculatePrice.$value->docket_id)->result_array();
            $key = 'total_price_with_tax';
            $sum_with_tax = array_sum(array_column($r3,$key));    
            
            $docketPaymentHistory = $this->get_where_array('mcb_delivery_docket_payment', array('docket_id'=>$value->docket_id));
            $key2 = 'amount';
            $paid_amount = array_sum(array_column($docketPaymentHistory,$key2));
            
            $sum_with_tax = $sum_with_tax-$paid_amount;
            $this->update('mcb_delivery_dockets', array('docket_id'=>$value->docket_id), array('price_with_tax'=>$sum_with_tax));
        }
        $res = $this->db->query($qry)->result();
        return $res;
    }
    
    function update($table_name, $condition, $data) {
        
        $this->db->where($condition);
        $this->db->update($table_name, $data);
    }
    
    function get_row($tbl_name, $condition) {
        $this->db->where($condition);
        $q = $this->db->get($tbl_name);
        $Res = $q->row();
        return $Res;
    }
    
    function get_where($table_name, $condition = '', $order = '', $limit = '') {
        
        if ($order != '') {
            $this->db->order_by($order);
        }
        if ($limit != '') {
            $this->db->limit($limit);
        }
        if ($condition != '') {
            $this->db->where($condition);
        }
        return $this->db->get($table_name)->result();      
    }
    
    function get_where_array($table_name, $condition = '', $order = '', $limit = '') {
        
        if ($order != '') {
            $this->db->order_by($order);
        }
        if ($limit != '') {
            $this->db->limit($limit);
        }
        if ($condition != '') {
            $this->db->where($condition);
        }
        return $this->db->get($table_name)->result_array();      
    }
    
} 


