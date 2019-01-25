<?php

(defined('BASEPATH')) OR exit('No direct script access allowed');

class Upload_Products extends Admin_Controller {

    function index() {
        if ($this->input->post('btn_upload_products')) {

            $this->do_upload();
        } elseif ($this->input->post('btn_cancel')) {

            redirect($this->session->userdata('last_index'));
        } elseif ($this->input->post('download_sample_import')) {
            $this->load->model('mdl_products_import');
            $this->mdl_products_import->downloadSampleProductImportFile();
            
        } else {
            $this->load->view('upload_products');
        }
    }

    function do_upload() {

        $config = array(
            'upload_path' => './uploads/products/',
            'allowed_types' => 'csv'
        );

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload()) {

            $data = array(
                'static_error' => $this->upload->display_errors()
            );

            $this->load->view('upload_products', $data);
        } else {

            $upload_data = $this->upload->data();
            $this->load->model('mdl_products_import');
            //importing csv file
            $history_id = $this->mdl_products_import->process_product_data_file($upload_data['full_path'],$upload_data['file_name']);
            if($history_id){
                redirect('products?history_id='.$history_id);
            }else{
                $this->session->set_flashdata('custom_success','Something went wrong while processing the file.');
                redirect('products');
            }
        }
    }

}

?>