<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Dashboard_Widgets extends Admin_Controller {

	function total_balance() {

		if ($this->session->userdata('global_admin')) {

			$invoice_total_balance = $this->mdl_invoices->get_total_invoice_balance();

		}

		else {

			$invoice_total_balance = $this->mdl_invoices->get_total_invoice_balance($this->session->userdata('user_id'));

		}

		$data = array(
			'invoice_total_balance'	=>	$invoice_total_balance
		);

		$this->load->view('dashboard/sidebar_invoice_balance', $data);

	}

    function total_paid() {

        $this->load->model('payments/mdl_payments');

        if ($this->session->userdata('global_admin')) {

            $invoice_total_paid = $this->mdl_payments->get_total_paid();

        }

        else {

            $invoice_total_paid = $this->mdl_payments->get_total_paid(array('where'=>array('mcb_invoices.user_id'=>$this->session->userdata('user_id'))));

        }

        $data = array(
            'invoice_total_paid'    =>  $invoice_total_paid
        );

        $this->load->view('dashboard/sidebar_invoice_paid', $data);

    }

}

?>