<?php

(defined('BASEPATH')) OR exit('No direct script access allowed');

class Inventory extends Admin_Controller {

    function __construct() {

        parent::__construct();
        $this->_post_handler();

        $this->load->model('mdl_inventory_item');
        $this->load->model('mdl_inventory_history');
    }

    function index() {

        $this->load->helper('text');
        $this->redir->set_last_index();
        $params = array(
            'paginate' => TRUE,
            'limit' => $this->mdl_mcb_data->setting('results_per_page'),
            'page' => uri_assoc('page')
        );
        $default_order_by = 'supplier_id asc';
        if ($default_order_by != '') {
            $params['order_by'] = '';
            $params['order_by'] .= $default_order_by;
        }


        $data = array(
            'inventory' => $this->mdl_inventory_item->get($params),
            'sort_links' => TRUE,
            'order_by' => $params['order_by']
        );
        if ($this->input->get('history_id') != NULL && (int) $this->input->get('history_id') > 0) {
            $this->load->model('mdl_inventory_import');
            $data['import_log'] = $this->mdl_inventory_import->getLogMessage($this->input->get('history_id'));
        }

        $this->load->view('index', $data);
    }

    function ajax_update_inventory() {
        $item = json_decode($this->input->post('post_item'));
        $supplier_name = $item->supplier_name;

        $this->load->model('clients/mdl_clients');
        $supplier_id = $this->mdl_clients->get_by_name($supplier_name)->supplier_id;
        $inventory->inventory_id = $item->id;
        $inventory->name = $item->name;
        $inventory->supplier_code = $item->supplier_code;
        $inventory->supplier_description = $item->supplier_description;
        $inventory->description = $item->description;
        $inventory->base_price = $item->base_price;
        $inventory->location = $item->location;
        $inventory->supplier_id = $supplier_id;

        $qty = $item->qty;
        $this->load->model('mdl_inventory_item');
        $this->load->model('mdl_inventory_history');
        $this->mdl_inventory_history->ajax_insert($item->id, $qty);
        $result = $this->mdl_inventory_item->update_inventory($inventory);

        return $result;
    }

    function update_inv_rel() {
        error_reporting(E_ALL);
        ini_set('display_errors', '1');
        $inventory = json_decode($this->input->post('post_item'));
        $this->load->model('mdl_inventory_item');
        if ((float) $inventory->qty > 0) {
            $this->mdl_inventory_item->updateinventoryRel($inventory->ii, $inventory->qty, $inventory->pi);
        }
        echo TRUE;
        exit;
    }

    function get_inventory_JSON($params = NULL) {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        ini_set('memory_limit', '256M');
        $limit = $this->input->post('limit');
        $offset = $this->input->post('offset');
        $this->load->model('clients/mdl_clients');

        if ($this->input->get('show_archived') == TRUE) {
            $where = '';
            $data = array(
                'suppliers' => $this->mdl_clients->get_active_suppliers(),
                'inventory' => $this->mdl_inventory_item->get_raw($where, $limit, $offset)
            );
        } else if ($this->input->get('only_archived') == TRUE) {
            $where = 'WHERE i.is_arichved = "1"';
            $data = array(
                'suppliers' => $this->mdl_clients->get_active_suppliers(),
                'inventory' => $this->mdl_inventory_item->get_raw($where, $limit, $offset)
            );
        } else {
            $where = 'WHERE i.is_arichved != "1"';
            $data = array(
                'suppliers' => $this->mdl_clients->get_active_suppliers(),
                'inventory' => $this->mdl_inventory_item->get_raw($where, $limit, $offset)
            );
        }
        echo json_encode($data);
    }

    function get_inventory_list() {
        $limit = $this->input->post('limit');
        $offset = $this->input->post('offset');
        $this->load->model('clients/mdl_clients');
        $where = "WHERE i.is_arichved != '1'";
        $inventory = $this->mdl_inventory_item->get_raw($where, $limit, $offset);
        $finInv = array();
        if (sizeof($inventory) > 0) {
            foreach ($inventory as $inv) {
                $inv->qty = '';
                $finInv[] = $inv;
            }
        }

        //echo '<pre>'; print_r($finInv); exit;

        $data = array(
            'suppliers' => $this->mdl_clients->get_active_suppliers(),
            'inventory' => $finInv
        );
        echo json_encode($data);
    }

    function get_unlinked_inventory_list() {
//        error_reporting(E_ALL);
//        ini_set('display_errors', 1);
        $limit = $this->input->post('limit');
        $offset = $this->input->post('offset');
        $this->load->model('clients/mdl_clients');
        $this->load->model('inventory/mdl_inventory_item');

        $inventory = $this->mdl_inventory_item->getUnlinedInventorylist($limit, $offset);

        $finInv = array();
        if (sizeof($inventory) > 0) {
            foreach ($inventory as $inv) {
                $inv->qty = 1;
                $finInv[] = $inv;
            }
        }

        $data = array(
            'suppliers' => $this->mdl_clients->get_active_suppliers(),
            'inventory' => $finInv
        );
        echo json_encode($data);
    }

    function ajax_from_quote_add() {
        //error_reporting(E_ALL); ini_set('display_errors', 1);
        
        $c = 0;
        $items = array();
        foreach ($_POST as $values) {
            $item = array(
                'name' => $_POST['name'][$c],
                'supplier_id' => $_POST['supplier_id'][$c],
                'description' => $_POST['description'][$c],
                'base_price' => $_POST['base_price'][$c],
                'supplier_price' => $_POST['supplier_price'][$c],
                'supplier_description' => $_POST['supplier_description'][$c],
                'use_length' => $_POST['use_length'][$c],
                'supplier_code' => $_POST['supplier_code'][$c],
                'invoice_id' => $_POST['invoice_id'][$c],
                'invoice_item_id' => $_POST['invoice_item_id'][$c],
                'i_action' => $_POST['i_action'][$c],
            );
            array_push($items, $item);
            $c++;
            if (count($values) == $c) {
                break;
            }
        }



        foreach ($items as $inventory) {
            $invoice_item_id = $inventory['invoice_item_id'];
            $invoice_id = $inventory['invoice_id'];
            
            unset($inventory['invoice_id']);
            unset($inventory['invoice_item_id']);
            
            if ($inventory['i_action'] == 'create') {
                unset($inventory['i_action']);
                
                $inventory_id = $this->common_model->insert('mcb_inventory_item', $inventory);
                if(!((float)$inventory_id > 0)){
                    echo json_encode(
                            array(
                                'status' => 'fail',
                                'detail' => 'Could not create inventory'
                            )
                    );
                    exit;
                }
                
                $sql = "UPDATE mcb_invoice_items SET product_id= '" . $inventory_id . "', item_name = '".$inventory['name']."', item_price = '".$inventory['base_price']."' WHERE invoice_item_id = '" . $invoice_item_id . "'";
                $this->common_model->just_query($sql);
                
            } elseif ($inventory['i_action'] == 'relate') {
                
                $rel_item_id = $inventory['name'];
                $sql = "UPDATE mcb_invoice_items SET product_id= '" . $rel_item_id . "' WHERE invoice_item_id = '" . $invoice_item_id . "'";
                $this->common_model->just_query($sql);
                
            } else {
                //do not add
            }
        }
        
        echo json_encode(
                array(
                    'status' => 'success',
                    'detail' => ''
                )
        );
        exit;
    }

    function ajax_add() {
        $arr = array(
            'status' => FALSE,
            'msg' => 'Please fill up all fields.'
        );
        if ($this->mdl_inventory_item->validate() && $this->mdl_inventory_history->validate()) {
            $inv_id = $this->mdl_inventory_item->save();
            $this->mdl_inventory_history->save();
            $invoice_item_id = $this->input->post('invoice_item_id'); //if added from quote then update productid  with new productid
            if ($inv_id != NULL && $invoice_item_id != "") {
                $sql = "UPDATE mcb_invoice_items SET product_id=" . $inv_id . " WHERE invoice_item_id =" . $invoice_item_id . " ";
                $this->common_model->just_query($sql);
                $arr = array(
                    'status' => TRUE,
                    'msg' => 'success.'
                );
            }
        } else {
            $arr = array(
                'status' => FALSE,
                'msg' => 'Please fill up all fields.'
            );
        }
        echo json_encode($arr);
    }

    function form() {

        
                
        if ($this->mdl_inventory_item->validate() && $this->mdl_inventory_history->validate()) {

            //saving things in history
            $inventory_id = $this->input->post('inventory_id');
            if ($inventory_id != NULL) {
                $product_name = $this->input->post('product_name');
                if ($product_name != '') {

                    $product_id = $this->common_model->get_row('mcb_products', array('product_name' => $product_name))->product_id;
                    foreach ($this->input->post('deleteInventory') as $value) {
                        $this->common_model->delete('mcb_products_inventory_raw', array('product_id' => $product_id, 'inventory_id' => $value));
                    }
                    $selectedInventories = $this->input->post('selectedInventory');
                    $qtyInventories = $this->input->post('qtyInventory');
                    if (is_array($selectedInventories)) {
                        $i = 0;
                        foreach ($selectedInventories as $selectedInventory) {
                            $inventory_qty = $qtyInventories[$i] ? $qtyInventories[$i] : '1';
                            $array = array(
                                'product_id' => $product_id,
                                'inventory_id' => $selectedInventory,
                                'inventory_qty' => $inventory_qty
                            );
                            $this->common_model->insert('mcb_products_inventory_raw', $array);
                            $i++;
                        }
                    }
                }

                $new_qty_to_be_changed_to = $this->input->post('history_qty');
                if ($new_qty_to_be_changed_to != NULL) {
                    $old_qty = (float) $this->mdl_inventory_item->getCurrentQtyById($inventory_id);
                    $history_changes_qty = $old_qty + (float) $new_qty_to_be_changed_to;
                    $data = array(
                        'history_id' => '0',
                        'inventory_id' => $inventory_id,
                        'history_qty' => $this->input->post('history_qty'),
                        'notes' => $this->input->post('notes'),
                        'user_id' => $this->session->userdata('user_id'),
                        'created_at' => date('Y-m-d H:i:s')
                    );
                    $this->mdl_inventory_history->addHistoryItem($data);
                    $this->mdl_inventory_item->update('mcb_inventory_item', array('qty' => $history_changes_qty), array('inventory_id' => $inventory_id));
//                    echo "gggg";
//                    die;
                }
            }

            if (($this->input->post('use_length') == '1') && ($this->input->post('inventory_id') > '0')) {
                $inv_pro = $this->mdl_inventory_item->get_where('mcb_products_inventory_raw', array('inventory_id' => $this->input->post('inventory_id')));
                if ($inv_pro != NULL) {
                    $this->mdl_inventory_item->update('mcb_products_inventory_raw', array('inventory_qty' => '1'), array('inventory_id' => $this->input->post('inventory_id')));
                }
            }


            $inv_id = $this->mdl_inventory_item->save();

            $this->mdl_inventory_history->save();

            //update teh related invoice id so they can get supplier when they convert..
            $invoice_item_id = $this->input->post('invoice_item_id'); //if added from quote then update productid  with new productid

            if ($inventory_id != NULL && $invoice_item_id != "") {

                $sql = "UPDATE mcb_invoice_items SET product_id=" . $inventory_id . " WHERE invoice_item_id =" . $invoice_item_id . " ";
                $q = $this->db->query($sql);
            }

            $clean = $this->input->post("clean");
            if ($inventory_id == NULL) {

                if ($this->input->post('inventory_type') == '1' && $inv_id != "") { //if 1 redirect back so they can add relationship
                    if ($clean == '1') {
                        redirect(site_url('inventory/form/inventory_id/' . $inv_id . '/?clean=' . $clean));
                    } else {
                        redirect(site_url('inventory/form/inventory_id/' . $inv_id));
                    }
                } else {
                    if ($clean == '1') { //stay on page
                        redirect(site_url('inventory/form/inventory_id/' . $inv_id . '/?clean=' . $clean));
                    } else {
                        redirect($this->session->userdata('last_index'));
                    }
                }
            } else {
                
                if(isset($_POST['action_type']) && $_POST['action_type'] == 'continue'){
                    
                    $sql = "select inventory_id from mcb_inventory_item where supplier_id IS NULL order by inventory_id desc";
                    $query = $this->db->query($sql);
                    $res = $query->result();
                    if(sizeof($res) > 0){
                        $inv_lat = $res[0]->inventory_id;
                        redirect(site_url('inventory/form/inventory_id/' . $inv_lat));
                        exit;
                    }
                }
                
                // redirect($this->session->userdata('last_index'));
                if ($clean == '1') { //stay on page
                    redirect(site_url('inventory/form/inventory_id/' . $inventory_id . '/?clean=' . $clean));
                } else {
                    redirect(site_url('inventory/form/inventory_id/' . $inventory_id));
                }
            }
        } else {
            $this->load->helper('form');

            if (!$_POST AND uri_assoc('inventory_id')) {

                $this->mdl_inventory_item->prep_validation(uri_assoc('inventory_id'));
            }

            $this->load->model('clients/mdl_clients');
            $this->load->model('users/mdl_users');
            $id = $this->mdl_inventory_item->form_value(inventory_id);

            $inventory_as_row = $this->common_model->query_as_object("SELECT *, (IF(i.inventory_type = '1', (SELECT MIN(`mcb_inventory_item`.`qty`) FROM `mcb_inventory_item`
                        LEFT JOIN `mcb_products_inventory` ON `mcb_inventory_item`.`inventory_id` = `mcb_products_inventory`.`inventory_id`
                        WHERE `mcb_products_inventory`.`product_id` = i.inventory_id), i.qty)) as quantity 
                        FROM mcb_inventory_item AS i WHERE i.inventory_id='" . $id . "'")[0];

            if ($inventory_as_row->inventory_type == '1') {
                $prod_id = $this->common_model->get_row('mcb_products', array('product_name' => $inventory_as_row->name))->product_id;
                $inventory_items = $this->mdl_inventory_item->get_inventory_by_productId($prod_id);
            }
//            error_reporting(E_ALL); ini_set('display_errors', 1);
            $inventory_history = $this->mdl_inventory_history->get_history_by_iventoryId($id);
            $this->load->helper('text');
            $suppliers = $this->mdl_clients->get_active_suppliers();
            $users = $this->mdl_users->get_raw();
            $data = array(
                'suppliers' => $suppliers,
                'suppliers_json' => json_encode($suppliers),
                'inventory_history' => json_encode($inventory_history),
                'users' => json_encode($users),
                'mcb_inventory_item' => $inventory_as_row,
                'all_inventory_items' => $this->common_model->query_as_object('SELECT * FROM mcb_inventory_item WHERE is_arichved != "1" AND inventory_type = "0"'),
                'inventory_items' => json_encode($inventory_items)
            );

//            echo '<pre>';
//            print_r($data);
//            exit;

            $this->load->view('form', $data);
        }
    }

    function _post_handler() {
        if ($this->input->post('btn_add_inventory')) {

            redirect('inventory/form');
        }
        if ($this->input->post('btn_upload_inventory')) {
            redirect('inventory/upload_inventory');
        }
        if ($this->input->post('btn_export_inventory')) {
            redirect('inventory/export_inventory');
        }
    }

    function delete() {

        if (uri_assoc('inventory_id')) {

            $this->mdl_inventory_item->delete(uri_assoc('inventory_id'));
        }

        $this->redir->redirect('inventory');
    }

    public function rollback() {
        $this->load->model('inventory/mdl_inventory_import');

        $data = array(
            'import_history' => $this->mdl_inventory_import->getImportHistory(),
        );


        $this->load->view('import_history', $data);
    }

    function product_inventory_duplication() {
        $this->load->view('product_inventory_duplication');
    }

    public function link_to_product() {
        $this->load->view('link_to_product');
    }

    public function one_to_one_product_inv() {
        $this->load->view('one_to_one_product_inv');
    }

    public function dorollback($historyid = FALSE) {
        $this->load->model('inventory/mdl_inventory_import');
        if ($historyid) {
            if ($this->mdl_inventory_import->dorollback($historyid)) {
                $this->session->set_flashdata('custom_success', 'Rollback successful.');
            } else {
                $this->session->set_flashdata('custom_error', 'Error while rolling back.');
            }
        }
        redirect('inventory/rollback');
    }

    public function updateinvprodrelation() {
        ini_set('max_execution_time', 3000);

        $invlistdetail = $this->input->post('id');
        $prodlistdetail = $this->input->post('pd');

        $this->load->model('inventory/mdl_inventory_item');

        if ($this->mdl_inventory_item->updateinvprodrelation($invlistdetail, $prodlistdetail)) {
            $this->session->set_flashdata('custom_success', 'Successfully applied to product.');
            echo 'success';
            exit;
        }
        $this->session->set_flashdata('custom_error', 'Error while applying.');
        echo 'fail';
        exit;
    }

    public function update_product_duplication() {
        ini_set('max_execution_time', 3000);
        $prolistdetail = $this->input->post('prolistdetail');
        foreach ($prolistdetail as $value) {
            $this->mdl_inventory_item->makeDuplicateProduct($value['id']);
        }
        $this->session->set_flashdata('custom_success', 'Successfully duplicated to inventory items.');
        echo 'success';
        exit;
    }

    public function get_one_to_one_prod_inv() {
        $this->load->model('inventory/mdl_inventory_item');
        $one_to_one_list = $this->mdl_inventory_item->get_one_to_one_prod_inv();
        echo json_encode($one_to_one_list);
        exit;
//        echo "<pre>";
//        echo print_r($one_to_one_list); exit;
    }

    function get_product_for_duplicate() {
        $this->load->model('inventory/mdl_inventory_item');
        $one_to_one_list = $this->mdl_inventory_item->get_product_for_duplicate();
        echo json_encode($one_to_one_list);
        exit;
//        echo "<pre>";
//        print_r($one_to_one_list);
//        die;   
    }

    public function product_inventory() {
        $this->load->view('product_inventory');
    }

    public function get_product_inventory() {
        $this->load->model('inventory/mdl_inventory_item');
        $get_product_inventory = $this->mdl_inventory_item->get_product_inventory();
        echo json_encode($get_product_inventory);
        exit;
    }

    public function update_inv_rel_pi() {
        //error_reporting(E_ALL); ini_set('display_errors', '1');
        $inventory = json_decode($this->input->post('post_item'));
        $this->load->model('mdl_inventory_item');
        if ((float) $inventory->qty > 0) {
            $this->mdl_inventory_item->updateinventoryRel($inventory->ii, $inventory->qty, $inventory->pi);
        }
        echo TRUE;
        exit;
    }

    public function open_o_qty() {
        error_reporting(E_ALL);
        ini_set('display_errors', '1');
        set_time_limit(0);

        if (isset($_GET['update'])) {
            $this->load->model('mdl_inventory_item');

            $limit = $_GET['limit'];
            $offset = $_GET['offset'];
            $where = '';
            $inventories = $this->mdl_inventory_item->get_raw($where, $limit, $offset);

            $this->load->helper('mcb_app');

            //echo '<pre>'; print_r($inventories); exit;

            if (sizeof($inventories) > 0) {
                foreach ($inventories as $inv) {
                    var_dump(udpate_open_order_qty($inv->id));
                    echo '<br/>';
                }
            }
            echo 'success';
        }
    }

    function doDuplicate() {
        $this->load->model('inventory/Mdl_Inventory_Item');

        $err_msg = "";
        $succ_msg = "";

        if ($_POST) {
            $arry = $this->input->post('inven_detail');
            foreach ($arry as $value) {
                $inv_detail = $this->common_model->get_row('mcb_inventory_item', array('inventory_id' => $value['id']));
                if ($inv_detail != NULL) {
                    $inv_detail->qty = '0.00';
                    $inv_detail->inventory_id = NULL;
                    $inv_detail->name = $inv_detail->name . '-copy';
                    $data = (array) $inv_detail;
                    $i_itemd = $this->common_model->insert('mcb_inventory_item', $data);
                    if ($i_itemd > 0) {
                        $succ_msg .= $value['name'] . " has been duplicated.<br>";
                    } else {
                        $err_msg .= $value['name'] . " couldn't not be duplicated.<br>";
                    }
                } else {
                    $err_msg .= $value['name'] . " couldn't not be duplicated.<br>";
                }
            }
        }
        $this->session->set_flashdata('custom_success', $succ_msg);
        $this->session->set_flashdata('custom_error', $err_msg);
        echo 'success';
        exit;
    }

    public function doArchive() {
        $this->load->model('inventory/Mdl_Inventory_Item');
        if ($_POST) {
            $arry = $this->input->post('inven_detail');
            foreach ($arry as $value) {

                $detail = $this->Mdl_Inventory_Item->get_Row('mcb_inventory_item', array('inventory_id' => $value['id']));

                if ($detail != '') {
                    $this->Mdl_Inventory_Item->update('mcb_inventory_item', array('is_arichved' => '1'), array('inventory_id' => $detail->inventory_id));
                }
            }
            $this->session->set_flashdata('custom_success', 'Successfully archived inventory.');
            echo 'success';
            exit;
        }
        $this->session->set_flashdata('custom_error', 'Error while applying.');
        echo 'fail';
        exit;
    }

    public function undoArchive() {
        $this->load->model('inventory/Mdl_Inventory_Item');
        if ($_POST) {
            $arry = $this->input->post('inven_detail');
            foreach ($arry as $value) {

                $detail = $this->Mdl_Inventory_Item->get_Row('mcb_inventory_item', array('inventory_id' => $value['id']));

                if ($detail != '') {
                    $this->Mdl_Inventory_Item->update('mcb_inventory_item', array('is_arichved' => '0'), array('inventory_id' => $detail->inventory_id));
                }
            }
            $this->session->set_flashdata('custom_success', 'Successfully un-arichved inventory.');
            echo 'success';
            exit;
        }
        $this->session->set_flashdata('custom_error', 'Error while applying.');
        echo 'fail';
        exit;
    }

    function jquery_inventory_by_supplier() {
        $supplier_id = uri_assoc('supplier_id');
        $search_term = $this->input->post('term');

        $data = array(
            'inventory' => $this->mdl_inventory_item->search_inventory_by_supplier($supplier_id, $search_term)
        );
        echo json_encode($data);
    }

    function getInvOpnOrdrQty() {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        $invoice_id = uri_assoc('inventory_id');
        $invnum = $this->input->post('invnum');

//        echo $invnum;
//        die;

        $this->load->model('delivery_dockets/mdl_delivery_dockets');
        $this->load->model('inventory/Mdl_Inventory_Item');
        // $dockets = $this->mdl_delivery_dockets->get_invoice_dockets($invoice_id);


        $inven = $this->Mdl_Inventory_Item->get_Row('mcb_inventory_item', array('inventory_id' => $invoice_id));
        if ($inven->supplier_code != '') {
            $sup_name = '(' . $inven->supplier_code . ')';
        } else {
            $sup_name = '';
        }


        $dockets = $this->mdl_inventory_item->get_opn_ordr_qty($invoice_id);


//        echo '<pre>';
//        print_r($dockets);
//        exit;



        if (sizeof($dockets) > 0) {
            $popHtml = '<div class="inv-detail">';
            $popHtml .= '<h4>' . $inven->name . '  ' . $sup_name . '</h4><br/>';

            $popHtml .= '<h3>Orders:</h3>';
            $popHtml .= '<table class="table table-bordered order-prod-list">';

            $popHtml .= '<thead>';
            $popHtml .= '<tr>';
            $popHtml .= '<td>S.N</td>';
            $popHtml .= '<td>Order #</td>';
            $popHtml .= '<td>Length</td>';
            $popHtml .= '<td>Quantity</td>';
            $popHtml .= '</tr>';
            $popHtml .= '</thead>';
            $popHtml .= '<tbody>';
            $i = 1;
            $sum = 0;
            foreach ($dockets as $inv) {

                $length = ($inv->item_length > '0') ? $inv->item_length : '';
                if ($length > '0') {
                    $inv->item_qty = $inv->item_qty * $length;
                }

                $popHtml .= '<tr>';
                $popHtml .= '<td>' . $i . '</td>';
                $popHtml .= '<td><a hinventory_id="' . site_url('orders/edit/order_id/' . $inv->order_id) . '">' . $inv->order_number . '</a></td>';
                $popHtml .= '<td>' . $length . '</td>';
                $popHtml .= '<td>' . $inv->item_qty . '</td>';

                $popHtml .= '</tr>';
                $i++;
                $sum += $inv->item_qty;
            }
            $popHtml .= '</tbody></table></div>';
            $popHtml .= '<div style="float: right;margin-right: 1%;">Total Quantity : ' . $sum . '</div>';
        } else {
            echo 'There are no open order quantity.';
            exit;
        }
        echo $popHtml;
        exit;
    }
    
    public function doInventoryDelete() {
        $this->load->model('inventory/Mdl_Inventory_Item');
        if ($_POST) {
            $arry = $this->input->post('inven_detail');
            foreach ($arry as $value) {
                $sql ="DELETE i_item, i_history, i_pro_inv_raw "
                        . "FROM mcb_inventory_item AS i_item "
                        . "LEFT JOIN mcb_inventory_history AS i_history ON i_history.inventory_id = i_item.inventory_id "
                        . "LEFT JOIN mcb_products_inventory_raw AS i_pro_inv_raw ON i_pro_inv_raw.inventory_id = i_item.inventory_id "
                        . "WHERE i_item.inventory_id = ".$value['id'].";";
                $this->common_model->just_query($sql);
            }
            $this->session->set_flashdata('custom_success', 'Successfully deleted inventory.');
            echo 'success';
            exit;
        }
        $this->session->set_flashdata('custom_error', 'Error while applying.');
        echo 'fail';
        exit;
    }
    public function product_inventory_relation() {
        $this->load->view('product_inventory_relation');
    }
    //clear link of inventory to product
    public function delinkproductinventoryinv() {
        ini_set('max_execution_time', 3000);
        $invlistdetail = $this->input->post('id');
        $this->load->model('inventory/mdl_inventory_item');
        if ($this->mdl_inventory_item->updateinvprodrelation1inv($invlistdetail)) {
            $this->session->set_flashdata('custom_success', 'Successfully delinked');
            echo 'success';
            exit;
        }
        $this->session->set_flashdata('custom_error', 'Error while delinking.');
        // echo 'fail';
        exit;
    }
    //clear link of product to inventory
    public function delinkproductinventorypro() {
        ini_set('max_execution_time', 3000);
        $prodlistdetail = $this->input->post('pd');
        $this->load->model('inventory/mdl_inventory_item');
        if ($this->mdl_inventory_item->updateinvprodrelation1pro($prodlistdetail)) {
            $this->session->set_flashdata('custom_success', 'Successfully delinked.');
            echo 'success';
            exit;
        }
        $this->session->set_flashdata('custom_error', 'Error while delinking.');
        // echo 'fail';
        exit;
    }

}

?>
