<?php

(defined('BASEPATH')) OR exit('No direct script access allowed');

class Upload_Inventory extends Admin_Controller {

    function index() {
        
        if ($this->input->post('btn_upload_inventory')) {
            $this->do_upload();
        } elseif ($this->input->post('download_sample_import')) {
            $this->load->model('mdl_inventory_import');
            $this->mdl_inventory_import->downloadSampleInventoryImportFile();
            
        } elseif ($this->input->post('btn_export_inventory')) {
//            $this->load->model('mdl_inventory_import');
//            $this->mdl_inventory_import->downloadSampleInventoryImportFile();
            $this->do_export();
            
        } elseif ($this->input->post('download_grouped_sample_import')) {
            $this->load->model('mdl_inventory_import');
            $this->mdl_inventory_import->downloadSampleGroupedInventoryImportFile();
            
        } elseif ($this->input->post('btn_cancel')) {

            redirect($this->session->userdata('last_index'));
        } else {
            $data = array();
            $this->load->model('inventory/mdl_inventory_import');
            $data['suppliers'] = $this->mdl_inventory_import->getSuppliers();
            $this->load->view('inventory_upload', $data);
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

    
    
    
    function do_upload() {
        $config = array(
            'upload_path' => './uploads/inventory/',
//            'upload_path' => 'uploads/inventory/',
            'allowed_types' => '*',
        );
        
        
        
        $this->load->library('upload', $config);

        
            
        
        if (!$this->upload->do_upload()) {
            $data = array(
                'static_error' => $this->upload->display_errors()
            );
            $this->load->view('inventory_upload', $data);
        } else {
            $upload_data = $this->upload->data();
            $this->load->model('mdl_inventory_import');
            $history_id = $this->mdl_inventory_import->process_inventory_data_file($upload_data['full_path'],$upload_data['file_name']);
            redirect('inventory?history_id='.$history_id);
        }
    }

}

?>
