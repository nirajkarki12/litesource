<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class Addresses	 extends Admin_Controller {

	function __construct() {

		parent::__construct();

		$this->_post_handler();

		$this->load->model('mdl_addresses');
		
	}


	function form() {

                $order_id = uri_assoc('order_id');
                
		if ($this->mdl_addresses->validate()) {
			//record in history
                        $data = array(
                            'user_id' => $this->session->userdata('user_id'),
                            'order_id' => $order_id,
                            'created_date' => date('Y-m-d H:i:s'),
                            'order_history_data' => 'Delivery address changed.'
                        );
                        $this->db->insert('mcb_order_history', $data);
                        
			$this->mdl_addresses->save();
                        
			redirect($this->session->userdata('last_index'));
                        
		}

		else {
			
			$this->load->helper('form');

			if (!$_POST) {
				
				/*
				 * if address is associated with an order then only
				 * allow updates if the address is not setup to be used as a default
				 */	
				
				$order_id = uri_assoc('order_id');
				$copy_address = FALSE;
				
				if ($order_id) {
					$this->load->model('orders/mdl_orders');
					//$this->load->model('addresses/mdl_addresses');
					
					$order = $this->mdl_orders->get_by_id($order_id);
					$address_id = $order->order_address_id; 	
					$address = $this->mdl_addresses->get_by_id($address_id);
								
					$copy_address = ($address && ($address->address_defaultable != 0));
										
				}
				else {
				
					$address_id = uri_assoc('address_id');
				}
				
				$this->mdl_addresses->prep_validation($address_id);
				
				// reset address_id so that a new address record is created
				if ($copy_address) {
					$this->mdl_addresses->set_form_value('address_id', NULL);
				}
				

			}
			
			$this->load->helper('text');	

					
			$this->load->view('address_edit');

		}

	}


	
	function delete() {

		if (uri_assoc('address_id')) {

			$this->mdl_addresses->delete(uri_assoc('address_id'));

		}

		$this->redir->redirect('addresses');

	}
	
	function _post_handler() {

		
		if ($this->input->post('btn_add_address')) {

			redirect('address_edit');

		}

		elseif ($this->input->post('btn_cancel')) {

			redirect($this->session->userdata('last_index'));

		}

	}

}

?>