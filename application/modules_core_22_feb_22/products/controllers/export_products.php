<?php

(defined('BASEPATH')) OR exit('No direct script access allowed');

class Export_Products extends Admin_Controller {

    function index() {

        if ($this->input->post('btn_export_products')) {

            $this->do_export();
        } elseif ($this->input->post('btn_cancel')) {

            redirect($this->session->userdata('last_index'));
        } else {
            $data = array();
            $this->load->model('inventory/mdl_inventory_import');
            $data['suppliers'] = $this->mdl_inventory_import->getSuppliers();
            $this->load->view('export_products',$data);
        }
    }

    function do_export() {
        $changed_since_date = $this->input->post('changed_since_date');
        $lastchanged = null; 
        if ('' !== $changed_since_date) {
            $date_format = $this->mdl_mcb_data->setting('default_date_format_picker');
            $lastchanged = DateTime::createFromFormat('d/m/Y', $changed_since_date);
            $lastchanged = $lastchanged->format('YmdHi');
        }
        $lastchanged = !$lastchanged ? null : $lastchanged;
        $this->load->model('mdl_products_import');
        $products = $this->mdl_products_import->export_updated_products($lastchanged);
        redirect($this->session->userdata('last_index'));
    }

}

?>