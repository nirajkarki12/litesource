<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class Housing extends Admin_Controller {

    function __construct() {

            parent::__construct();

            $this->_post_handler();

            $this->load->model('mdl_housing');
    $this->load->model('invoices/mdl_invoices');

    }

    public function index(){

            $this->redir->set_last_index();

            $this->load->view('index');
    }


    function form() {
            if ($this->mdl_housing->validate()) {

                    $this->mdl_housing->save();

                    redirect($this->session->userdata('last_index'));

            }else {

                    $this->load->helper('form');
                    $this->load->helper('text');

                    if (!$_POST AND uri_assoc('housing_id')) {
                            $housing_id = uri_assoc('housing_id');
                            $housing = $this->mdl_housing->get_housing_by_id($housing_id);

                            if($housing->link_products){
                                    $housing_link_products = $this->mdl_housing->get_item_housing($housing->product_id);
                            }
            // $this->mdl_housing->prep_validation(uri_assoc('housing_id'));
        }
                    $data = [
                            'housing' => $housing,
                            'housing_link_products' => $housing_link_products
                    ];

                    $this->load->view('housing_form', $data);
            }
    }


    function delete() {
            if (uri_assoc('housing_id')) {
                    $this->mdl_housing->delete(uri_assoc('housing_id'));
            }

            $this->redir->redirect('housing');

    }

    function _post_handler() {

        if ($this->input->post('btn_add_housing')) {
            redirect('housing/form');
        }elseif($this->input->post('btn_bulk_add_housing')){
            redirect('housing/bulk_link');
        }

    }

    function addHousingInvoiceItem() {
            if(isset($_POST['product_ids']) && isset($_POST['invoice_id']) && is_array($_POST['product_ids'])){
                $product_ids = implode(",", $_POST['product_ids']);
                $invoice_id = $_POST['invoice_id'];
                $item_index = $_POST['item_index'];
                $parent_index = $_POST['parent_index'];
                $position = $_POST['parent_index'];

                $item_qty = isset($_POST['item_qty']) ? $_POST['item_qty'] : null;

                $products = $this->mdl_housing->getProductsByIds($product_ids);
                $items = $this->mdl_invoices->get_invoice_items($invoice_id);

                $housing_added = [];
                if($products){

                    foreach ($products as $product) {
                        $parent_index++;

                        $product->invoice_id = $invoice_id;
                        $product->item_index = $parent_index;

                        if($item_qty) {
                            $product->item_qty = $item_qty;
                        }
                        if($product->inventory_type == '0') {
                            $product->product_id = '0';
                        }

                        $tmp_item = $this->mdl_invoices->add_new_invoice_item($product);

                        if($tmp_item){
                            array_push($housing_added, $tmp_item);
                        }
                    }
                }

                $total_housing_added = count($housing_added);
                foreach($items as $item) {
                    if( $item->item_index >= ( $position + $total_housing_added ) ) {
                        $item->item_index += count($housing_added);
                    }
                }

                $final_array = array_merge($items, $housing_added);
                
                usort($final_array, function($a, $b) {
                    return $a->item_index - $b->item_index;
                });

                foreach ($final_array as $i_item) {
                    $this->common_model->update('mcb_invoice_items', array('item_index' => $i_item->item_index), array('invoice_item_id' => $i_item->invoice_item_id));
                }

            $data = [
                'housing_added' => $final_array,
            ];

        echo json_encode($data);
            }

    }

    public function search_autocomplete(){
        $search_term = $this->input->post('term');
        $data = array('items' => $this->mdl_housing->get_products_by_query($search_term));        
        echo json_encode($data);
    }

    public function get_housing_JSON(){
        $housing_lists = $this->mdl_housing->get_housing_lists();
        $products = $this->common_model->query_as_object('SELECT inventory_id, name FROM mcb_inventory_item WHERE is_arichved != "1"');
        $new_products = [];
        foreach ($products as $prod) {
                $new_products[$prod->inventory_id] = $prod->name;
        }

        foreach ($housing_lists as $key => $housing) {
                if($housing['link_products']){
                    $temp = explode(",", $housing['link_products']);
                    $housing_lists[$key]['link_products'] = null;
                    if(is_array($temp)){
                        foreach ($temp as $value) {
                            if(isset($new_products[$value])){
                                $housing_lists[$key]['link_products'][] = "<a href='" .site_url('inventory/form/inventory_id') .'/' .$value ."'>" .$new_products[$value] ."</a>";
                            }
                        }
                        $housing_lists[$key]['link_products'] = implode(',',$housing_lists[$key]['link_products']);
                    }
                }
        }
        $data = array(
            'housings' => $housing_lists
        );
        echo json_encode($data);
    }
    
    function bulk_link() {
        $active_supplier_qry = "SELECT SQL_CALC_FOUND_ROWS mcb_clients.*, 
                            mcb_client_groups.client_group_name, mcb_clients.client_name value,
                            IFNULL(parent_suppliers.client_id, mcb_clients.client_id) supplier_id, 
                            IFNULL(parent_suppliers.client_name, mcb_clients.client_name) supplier_name, 
                            t.total_due, mcb_currencies.* 
                            FROM (mcb_clients) 
                            JOIN mcb_currencies ON mcb_currencies.currency_id = mcb_clients.client_currency_id 
                            LEFT JOIN mcb_clients AS parent_suppliers ON parent_suppliers.client_id = mcb_clients.parent_client_id 
                            LEFT JOIN mcb_client_groups ON mcb_client_groups.client_group_id = mcb_clients.client_group_id 
                            LEFT JOIN (SELECT c.client_id, SUM(a.invoice_total) total_due 
                            FROM mcb_invoices i JOIN mcb_clients c ON c.client_id = i.client_id 
                            JOIN mcb_invoice_amounts a ON a.invoice_id = i.invoice_id 
                            WHERE i.invoice_is_quote = 0 AND i.invoice_status_id = 2 
                            GROUP BY i.client_id) AS t ON t.client_id = mcb_clients.client_id 
                            WHERE `mcb_clients`.`client_active` = 1 AND `mcb_clients`.`client_is_supplier` = 1 
                            ORDER BY mcb_clients.client_name";
        $data['suppliers'] = json_encode($this->common_model->query_as_object($active_supplier_qry));
        $this->load->view('bulk_link', $data);
    }
    
    function get_all_housing_inventory() {
        $sql = "SELECT mii.inventory_id AS id, c.client_name AS supplier_name, "
                . "mii.name AS n, mii.supplier_code AS c, mii.supplier_id AS s, "
                . "(SELECT (CHAR_LENGTH(link_products) - CHAR_LENGTH(REPLACE(link_products, ',', '')) + 1)  FROM mcb_housings WHERE mcb_housings.product_id = mii.inventory_id LIMIT 1) AS inventorycount  "
                . "FROM `mcb_inventory_item` AS mii "
                . "left JOIN mcb_clients AS c ON c.client_id = mii.supplier_id "
                . "WHERE `mii`.`is_arichved` != '1'";
        
        $data = array(
            'inventory' => $this->common_model->query_as_object($sql),
        );
        echo json_encode($data);
    }
    
    function get_all_inventory() {
        $sql = "SELECT mii.inventory_id AS id, c.client_name AS supplier_name, "
                . "mii.name AS n, mii.supplier_code AS c, mii.supplier_id AS s "
                . "FROM `mcb_inventory_item` AS mii "
                . "left JOIN mcb_clients AS c ON c.client_id = mii.supplier_id "
                . "WHERE `mii`.`is_arichved` != '1'";
        
        $data = array(
            'inventory' => $this->common_model->query_as_object($sql),
        );
        echo json_encode($data);
    }
    
    public function updateprodrelation() {
        ini_set('max_execution_time', 3000);
        if (isset($_POST['product_ids']) && isset($_POST['link_products'])) {
            foreach($_POST['product_ids'] as $product_id){
                $this->mdl_housing->bulk_save($product_id, $_POST['link_products']);
            }
            $this->session->set_flashdata('custom_success', 'Housing successfully added.');
        }
        exit;
    }
    
    public function unlinkhousing() {
        ini_set('max_execution_time', 3000);
        if (isset($_POST['product_ids'])) {
            foreach($_POST['product_ids'] as $product_id){
                $this->mdl_housing->delete_by_product_id($product_id);
            }
            $this->session->set_flashdata('custom_success', 'Housing successfully added.');
        }
        exit;
    }
    
    function gethousingpop() {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        $product_id = $this->input->get('id');
        $inventories = $this->mdl_housing->get_item_housing($product_id);
        if (sizeof($inventories) > 0) {
            $popHtml = '<div class="inv-detail">';
            $popHtml .= '<h2>' . $this->input->post('name') . '</h2><br/>';

            $popHtml .= '<h3>Housing Products:</h3>';
            $popHtml .= '<table class="table table-bordered order-prod-list">';

            $popHtml .= '<thead>';
            $popHtml .= '<tr>';
            $popHtml .= '<td>S.N</td>';
            $popHtml .= '<td>Item Name</td>';
            $popHtml .= '</tr>';
            $popHtml .= '</thead>';
            $popHtml .= '<tbody>';
            $i = 1;
            foreach ($inventories as $inv) {
                $popHtml .= '<tr>';
                $popHtml .= '<td>' . $i . '</td>';
                $popHtml .= '<td><a href="' . site_url() .'inventory/form/inventory_id/' .$inv->product_id .'" target="_new">' . $inv->item_name . '</a></td>';
                $popHtml .= '</tr>';
                $i++;
            }

            $popHtml .= '</tbody></table></div>';
        }
        echo $popHtml; exit;
    }
    

}
