<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class Quotes extends Admin_Controller {

    /*
	 * This controller is only called directly from routes
	 * Needed so that default route will go to quotes and also
	 * URL will show as '/quotes' which is required
	 * so that menu system knows to highlight quotes tab
	 */
    function index() {
		
		redirect('quotes');
    }
}

?>
