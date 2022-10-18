<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class Mdl_Invoices_Simple extends MY_Model {

    public function __construct() {

        parent::__construct();

        $this->table_name = 'mcb_invoices';

        $this->primary_key = 'mcb_invoices.invoice_id';

        $this->order_by = 'mcb_invoices.invoice_date_entered DESC, mcb_invoices.invoice_id DESC';

        $this->select_fields = "
		SQL_CALC_FOUND_ROWS
		mcb_invoices.*,
        mcb_clients.*,
		prj.project_name";

     
        $this->joins = array(
            'mcb_clients'			=>	'mcb_clients.client_id = mcb_invoices.client_id',
			'mcb_projects AS prj'	=>	array(
				'prj.project_id = mcb_invoices.project_id',
				'left'
			)
        );


    }

}

?>