<?php

(defined('BASEPATH')) OR exit('No direct script access allowed');

class Export_Inventory extends Admin_Controller {

    function index() {

        if ($this->input->post('btn_export_inventory')) {

            $this->do_export();
        } elseif ($this->input->post('btn_cancel')) {

            redirect($this->session->userdata('last_index'));
        } else {
            $data = array();
            $this->load->model('inventory/mdl_inventory_import');
            $data['suppliers'] = $this->mdl_inventory_import->getSuppliers();
            $this->load->view('export_inventory',$data);
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

        $this->load->model('mdl_inventory_import');

        $inventories = $this->mdl_inventory_import->export_updated_inventories($lastchanged);

        redirect($this->session->userdata('last_index'));
    }

}

?>