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
}