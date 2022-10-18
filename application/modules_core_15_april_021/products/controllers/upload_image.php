<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class Upload_Image extends Admin_Controller {

	function index() {

		/* 25-APR-2011
		Not sure what the best way to do this is
		
		In the product form made sure that product_id and supplier_id were included in URI
		which also mean't we have to update the route in config/routes.php so that
		it always ends up in this controller's index function
		
		
		
		*/
		
		$supplier_id = uri_assoc('supplier_id');
		$product_id = uri_assoc('product_id');
		
		// there must be a simple way to just go back to the calling view?
		$redir = 'products/form/product_id/' . $product_id;
		
		if ($this->input->post('btn_upload')) {

            
			// *TODO need a helper of common API here
			$supplier_directory = str_pad($supplier_id, 3, "0", STR_PAD_LEFT);
			
			$config = array(
				'upload_path'	=>	'./uploads/suppliers/'. $supplier_directory . '/products/images/',
				'allowed_types'	=>	'gif|jpg|png',
				'max_size'		=>	'100',
				'max_width'		=>	'500',
				'max_height'	=>	'300'
			);

			$this->load->library('upload', $config);

			if (!$this->upload->do_upload()) {

				$data = array(
					'static_error'	=>	$this->upload->display_errors()
				);

				$this->load->view('upload_image', $data);

			}

			else {
				
				$upload_data = $this->upload->data();

				//$this->mdl_mcb_data->save('invoice_logo', $upload_data['file_name']);

				redirect($redir);

			}

		}
        else if ($this->input->post('btn_cancel')) {
			redirect($redir);
		}
		else {

            

			$this->load->view('upload_image');

		}

	}

	function delete() {

/*
		unlink('./uploads/invoice_logos/' . uri_assoc('invoice_logo', 4));

		if ($this->mdl_mcb_data->setting('invoice_logo') == uri_assoc('invoice_logo', 4)) {

			$this->mdl_mcb_data->delete('invoice_logo');

			$this->session->unset_userdata('invoice_logo');

		}
*/
		//redirect('products/form');

	}

}

?>