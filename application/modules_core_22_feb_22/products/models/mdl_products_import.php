<?php

(defined('BASEPATH')) OR exit('No direct script access allowed');

class Mdl_Products_Import extends MY_Model {

    protected $_row;

    public function __construct() {

        parent::__construct();

        $this->table_name = 'mcb_products_import';

        $this->select_fields = "
		SQL_CALC_FOUND_ROWS
		mcb_products_import.*";

        $this->primary_key = 'mcb_products_import.product_name';
        $this->_row = 1;
    }

    function process_product_data_file($source_file, $filename) {
        ini_set('max_execution_time', 30000);
        log_message('debug', 'Processing product data file ' . $source_file);

        //now clearing import table, rather keeping rollback feature for all past imports
        //$this->clear_import_table();
        $row = 1;
        $insert_rows = array();
        $error_str = '';
        $product_import_success_cnt = 0;
        $product_import_fail_cnt = 0;
        $imported_prod_ids = '';
        $imported_supplier_ids = '';
        $num = 0;
        $updated_prod_array = array();
        $updated_prod_str = '';
        
        if( $this->input->post('db_product_overwrite') == '1' ){
            $this->export_before_import();
            $this->delete_override_product($source_file);
        }
        
        if (($handle = fopen($source_file, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
                //if($row > 28000){
                    
                $temp = array();
                foreach($data as $d){
                    $val = str_replace("\0","",$d);
                    $temp[] = $val;
                }
                $data = $temp;
                
                if ($row == 1) {
                    //if ($data[1] != 'product_name' || $data[0] != 'supplier_name' || $data[2] != 'product_supplier_code' || $data[3] != 'product_description' || $data[4] != 'product_supplier_description' || $data[5] != 'stock_qty' || $data[6] != 'product_supplier_price' || $data[9] != 'warehouse_location') {
                    if ($data[1] != 'product_name' || $data[0] != 'supplier_name' || $data[2] != 'product_supplier_code' || $data[3] != 'product_description' || $data[4] != 'product_supplier_description' || $data[5] != 'product_supplier_price' || $data[6] != 'product_base_price') {
                        $error_str .= 'The CSV file is not of valid format. Please see an example export for the right format.';
                        break;
                    }
                }
//                if($row == 28370){
//                    continue;
//                }
//                if($row > 28369){
//                    echo '<pre>'; print_r($data);
//                }
                $num = count($data);
                if ($row > 1) {
                    if ($num >= 6) {
                        //echo 'here1...';
                        //preparing import array to mcb_products_import table
                        //0 supplier_name
                        //1 product_name
                        //2 product_supplier_code
                        //3 product_description
                        //4 product_supplier_description
                        //6 product_base_price
                        //5 product_supplier_price

                        $duplicate_product = $this->is_duplicate_product($data[1]);
                        //getting supplier id from supplier_name
                        //if the supplier name doesn't exist then, we will create new supplier and return its id
                        $supplier_id = $this->getSupplierIdFromName($data[0]);
                        if (!$supplier_id) {
                            //supplier doesn't exist, creating one
                            $error_str .= 'The supplier "' . $data[0] . '" does not exist - adding the new client as supplier.<br/>';
                            $supplier_id = $this->createClientAsSupplier($data[0]);
                            if ($supplier_id) {
                                $imported_supplier_ids .= $supplier_id . ',';
                            }
                        }
                        if (!$duplicate_product) {
                            if (!$this->insert_import_table($data)) {
                                $error_str .= 'Something went wrong while importing in line "' . $row . '". Please make sure your csv file is valid. <br/>';
                            } else {
                                if (!$supplier_id) {
                                    $error_str .= 'Could not create the client as supplier "' . $data[0] . '" according to line ' . $row . ' in your csv file. Escaping this product row.<br/>';
                                    $product_import_fail_cnt++;
                                } else {
                                    $imported_supplier_ids .= $supplier_id . ',';
                                }
                                if ($supplier_id) {
                                    //now adding the product
                                    $product_id = $this->addNewProduct($data, $supplier_id);
                                    if ($product_id) {
                                        $error_str .= 'Added: "' . $data[1] . '".<br/>';
                                        $product_import_success_cnt++;
                                        $imported_prod_ids .= $product_id . ',';
                                    } else {
                                        $product_import_fail_cnt++;
                                        $error_str .= $data[1] . '" could not be imported of line ' . $row . ' from the csv file.<br/>';
                                    }
                                }
                            }
                        } else {
                            $error_str .= 'Updated: "' . $data[1] . '".<br/>';
                            //echo 'Updated: "' . $data[1] . '".<br/>';
                            //$product_import_fail_cnt++;
                            //now going to update the product
                            if ($supplier_id) { //not going to proceed if problem with either identifying or creating supplier id
                                if ($this->updateProductDetail($duplicate_product->product_id, $data, $supplier_id)) {
                                    $updated_prod_array[$duplicate_product->product_id] = $duplicate_product;
                                    $updated_prod_str .= $duplicate_product->product_id . ',';
                                    $product_import_success_cnt++;
                                }
                            }
                        }
                    } else {
                        $error_str .= 'The csv format is wrong in line "' . $row . '". Please export a sample and udpate it. <br/>';
                        $product_import_fail_cnt++;
                    }
                }
//                if($row > 10000){
//                    print_r($error_str); exit;
//                }
                //}
                $row++;
                $this->_row++;
                //echo $row.'<br/>';
            }
            fclose($handle);
        }
        
        //echo 'came out of loop..'; exit;

        $err_bkp = '';
        $history_id = FALSE;
        //if ($imported_prod_ids != '' || $updated_prod_str != '' || $product_import_success_cnt > 0 || $product_import_fail_cnt > 0) {
            //add record for rollback, 
            $data = array(
                'imported_products' => $imported_prod_ids,
                'imported_supplier_ids' => $imported_supplier_ids,
                'import_from_file_name' => $filename,
                'updated_product_ids' => $updated_prod_str,
                'imported_at' => date('Y-m-d H:i:s')
            );
            $this->db->insert('mcb_product_import_history', $data);
            $history_id = $this->db->insert_id();
            if ($history_id && $updated_prod_str != '') {
                //make backup of what product detail was before the import for updated products so
                //that we can take the product to exactly that point where it was before import
                $err_bkp = $this->makeUpdateImportBackupForRollback($updated_prod_array, $history_id);
            }
        //}



        log_message('debug', 'Imported ' . $imported_prod_ids . ' products.');
        $total_lines = (($row - 2) > 0)?($row - 2):'Unknown';
        $fin_msg = 'Total product lines in imported csv file: ' . $total_lines . '<br/>'; //1 header and another indexing from 1
        if ($product_import_success_cnt > 0) {
            $fin_msg .= 'Successfully imported ' . $product_import_success_cnt . ' products. <br/>';
        }
        if ($product_import_fail_cnt > 0) {
            $fin_msg .= 'Failed to import ' . $product_import_fail_cnt . ' products. <br/>';
        }
        if ($error_str != '') {
            $fin_msg .= '<div class="debug_text fa-plus fa"><span><img class="show expanddebug" src="'.base_url().'assets/slick/images/expand.gif"/></span><span><img class="hide collapsedebug" src="'.base_url().'/assets/slick/images/collapse.gif"/></span>Import Log</div> <br/>';
            $fin_msg .= '<div class="debug_detail hide">'.$error_str . $err_bkp.'</div>';
        }
        
        //save the import log to database, session flash can not handle big debug message
        $this->updateProductImportLogMessage($history_id,$fin_msg);
        return $history_id;
    }
    
    public function getLogMessage($historyid){
        $this->db->select('log');
        $this->db->where('id',$historyid);
        $q = $this->db->get('mcb_product_import_history');
        $res = $q->row();
        if(isset($res->log))
            return $res->log;
        return FALSE;
    }
    
    private function updateProductImportLogMessage($history_id,$fin_msg){
        $this->db->where('id',$history_id);
        $data = array(
            'log' => $fin_msg
        );
        return $this->db->update('mcb_product_import_history',$data);
    }

    private function makeUpdateImportBackupForRollback($updated_prod_array, $history_id) {
        $error_str = '';
        if (sizeof($updated_prod_array) > 0) {
            foreach ($updated_prod_array as $product_id => $product) {
                $data = array(
                    'history_id' => $history_id,
                    'product_id' => $product_id,
                    'supplier_id' => $product->supplier_id,
                    'product_name' => $product->product_name,
                    'product_description' => $product->product_description,
                    'product_supplier_description' => $product->product_supplier_description,
                    'product_supplier_code' => $product->product_supplier_code,
                    'product_supplier_price' => $product->product_supplier_price,
                    'product_base_price' => $product->product_base_price,
                    'stock_qty' => $product->stock_qty,
                    'warehouse_location' => $product->warehouse_location,
                    'imported_at' => date('Y-m-d H:i:s'),
                );
                if (!$this->db->insert('mcb_products_update_history_backup', $data)) {
                    $error_str .= 'Error: Could not create backup for ' . $product->product_name . '<br/>';
                }
            }
        }
        if ($error_str != '')
            return $error_str;
        return FALSE;
    }

    private function updateProductDetail($product_id, $data, $supplier_id) {
        //0 supplier_name
        //1 product_name
        //2 product_supplier_code
        //3 product_description
        //4 product_supplier_description
        //5 stock_qty
        //6 product_supplier_price
        //9 warehouse_location
        $this->db->where('product_id', $product_id);
        $data = array(
            'supplier_id' => $supplier_id,
            'product_name' => $data[1],
            'product_description' => $data[3],
            'product_supplier_description' => $data[4],
            'product_supplier_code' => $data[2],
            'product_supplier_price' => $data[5],
            'product_base_price' => $data[6],
            //'warehouse_location' => $data[9],
            'product_last_changed' => date('Y-m-d H:i:s'),
        );
        return $this->db->update('mcb_products', $data);
    }

    private function addNewProduct($data, $supplier_id) {
        //0 supplier_name
        //1 product_name
        //2 product_supplier_code
        //3 product_description
        //4 product_supplier_description
        //5 stock_qty
        //6 product_supplier_price
        //9 warehouse_location
        $data = array(
            'supplier_id' => $supplier_id,
            'product_name' => $data[1],
            'product_description' => $data[3],
            'product_supplier_description' => $data[4],
            'product_supplier_code' => $data[2],
            'product_supplier_price' => $data[5],
            'product_base_price' => $data[6],
            //'warehouse_location' => $data[9],
            'product_last_changed' => date('Y-m-d H:i:s'),
        );
        if ($this->db->insert('mcb_products', $data)) {
            $product_id = $this->db->insert_id();
            $this->duplicateAsInventory($product_id, $data);
            return $product_id;
        }
        return FALSE;
    }
    
    
    function duplicateAsInventory($product_id, $product_data) {
        
        $data = array(
            'supplier_id' => $product_data['supplier_id'],
            'name' => $product_data['product_name'],
            'description' => $product_data['product_description'],
            'base_price' => $product_data['product_base_price'],
            'supplier_code' => $product_data['product_supplier_code'],
            'supplier_description' => $product_data['product_supplier_description'],
            'supplier_price' => $product_data['product_supplier_price'],
        );
        
        $invDetail = $this->common_model->get_row('mcb_inventory_item', array('name'=> $data['name']));
        if($invDetail != NULL ){
            $this->update('mcb_inventory_item', $data, array('inventory_id'=>($invDetail->inventory_id)));
            $c = array('product_id'=>$product_id, 'inventory_id'=>$invDetail->inventory_id);
            $r = $this->common_model->get_row('mcb_products_inventory', $c);
            if(($r == NULL) || ($r == '')){
                $this->common_model->insert('mcb_products_inventory', array('product_id'=>$product_id, 'inventory_id'=>$invDetail->inventory_id, 'inventory_qty'=>'1') );
            }
            
        } else {
            $iid = $this->common_model->insert('mcb_inventory_item', $data);
            $c = array('product_id'=>$product_id, 'inventory_id'=>$iid);
            $r = $this->common_model->get_row('mcb_products_inventory', $c);
            if(($r == NULL) || ($r == '')){
                $this->common_model->insert('mcb_products_inventory', array('product_id'=>$product_id, 'inventory_id'=>$iid, 'inventory_qty'=>'1') );
            }
        }
        
    }
    
    /**
     * creates new client as supplier
     * @param type $client_name
     * @return boolean
     */
    private function createClientAsSupplier($client_name) {
        $data = array(
            'client_name' => $client_name,
            'client_long_name' => '',
            'client_address' => '',
            'client_address_2' => '',
            'client_city' => '',
            'client_state' => '',
            'client_zip' => '',
            'client_country' => '',
            'client_phone_number' => '',
            'client_fax_number' => '',
            'client_mobile_number' => '',
            'client_email_address' => '',
            'client_web_address' => '',
            'client_tax_id' => '',
            'client_tax_rate_id' => '1',
            'client_is_supplier' => '1',
            'client_currency_id' => '1' //making AU doller default
        );
        if ($this->db->insert('mcb_clients', $data)) {
            return $this->db->insert_id();
        }
        return FALSE;
    }

    /**
     * checks of the supplier name is already there:
     * supplier is also a client with a special field there
     * @param type $supplier_name
     * @return boolean
     */
    private function getSupplierIdFromName($supplier_name) {
        $supplier_name = str_replace("\0","",$supplier_name);
        $this->db->where('client_name', $supplier_name);
        //$this->db->where('client_active', '1');
        $q = $this->db->get('mcb_clients');
        if ($q->num_rows() > 0) {
            $res = $q->row();
            return $res->client_id;
        }
        return FALSE;
    }

    /**
     * checks if the product is duplicate by the product name
     * @param type $product_name
     * @return boolean
     */
    private function is_duplicate_product($product_name) {
        $this->db->where('product_name', $product_name);
        $q = $this->db->get('mcb_products');
        if ($q->num_rows() > 0) {
            $res = $q->row();
            return $res;
        }
        return FALSE;
    }

    private function insert_import_table($data) {
        //0 supplier_name
        //1 product_name
        //2 product_supplier_code
        //3 product_description
        //4 product_supplier_description
        //5 stock_qty
        //6 product_supplier_price
        //9 warehouse_location
        $insert_row_import = array(
            'supplier_name' => $data[0],
            'product_name' => $data[1],
            'product_description' => trim($data[3], " "),
            'product_supplier_description' => trim($data[4], " "),
            'product_supplier_code' => $data[2],
            'product_supplier_price' => $data[5],
            'product_base_price' => $data[6],
            'product_active' => '1',
            'imported_at' => date('Y-m-d H:i:s')
        );
        return $this->db->insert('mcb_products_import', $insert_row_import);
    }

    function clear_import_table() {

        log_message('debug', 'Truncating import table ' . $this->table_name);
        $this->db->query('TRUNCATE TABLE ' . $this->table_name);
    }

    function insert_new_suppliers() {

        $sql = "INSERT INTO mcb_clients (client_name, client_is_supplier, client_currency_id, client_tax_rate_id)
        SELECT DISTINCT supplier_name, 1, 1, 1
          FROM mcb_products_import AS p
          LEFT JOIN mcb_clients AS c ON LOWER(c.client_name) = LOWER(p.supplier_name)
         WHERE c.client_name IS NULL";

        $this->db->query($sql);

        $ret = $this->db->affected_rows();

        if ($ret > 0) {
            log_message('debug', 'Created ' . $ret . ' new suppliers');
        }
    }

    private function getSupplierNameFromId($id) {
        $this->db->select('client_name');
        $this->db->where('client_id', $id);
        $q = $this->db->get('mcb_clients');
        $res = $q->row();
        if (isset($res->client_name))
            return $res->client_name;
        return FALSE;
    }

    /*
     * clean export in csv
     *
     */

    function export_updated_products($lastchanged_Ymd = null) {

        $suppliers = $this->input->post('supplier_list');
        $supplierCond = '';
        $supplierNames = '';
        if ($suppliers != NULL && sizeof($suppliers) > 0) {
            $supplierCond .= ' AND (';
            $i = 0;
            foreach ($suppliers as $supplier) {
                if ($i > 0) {
                    $supplierCond .= ' OR c.client_id = ' . $supplier;
                } else {
                    $supplierCond .= 'c.client_id = ' . $supplier;
                }
                $supplierNames .= '_' . $this->getSupplierNameFromId($supplier);
                $i++;
            }
            $supplierCond .= ')';
        }

        $this->db->query("SET NAMES 'utf8' COLLATE 'utf8_general_ci'");

        $sql = "SELECT client_name AS supplier_name,
                    product_name, product_supplier_code,
                    product_description, product_supplier_description,
                    product_supplier_price, product_base_price, product_last_changed
               FROM `mcb_products` p
              INNER JOIN mcb_clients c
                 ON c.client_id = p.supplier_id
              WHERE product_last_changed > ? " . $supplierCond;
        $sql .= "ORDER BY supplier_name, product_name";

        $lastchanged = new Datetime('2009-01-01 00:00');

        if (null !== $lastchanged_Ymd) {
            $lastchanged = $lastchanged->createFromFormat('YmdHi', $lastchanged_Ymd);
        }

        $lastchangedStr = $lastchanged->format('Y-m-d H:i');
        $download_file_name = $lastchanged->format('Ymd') . $supplierNames . '_products.csv';

        $q = $this->db->query($sql, array($lastchangedStr));

        $fields = $q->field_data();
        $heading = array(
            'supplier_name',
            'product_name',
            'product_supplier_code',
            'product_description',
            'product_supplier_description',
            'product_supplier_price',
            'product_base_price',
            're-order_qty',
            'qty_on_order',
            'warehouse_location'
        );

        //generating csv file
        $delimiter = ',';
        $enclosure = '"';

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename="' . $download_file_name . '"');

        // do not cache the file
        header('Pragma: no-cache');
        header('Expires: 0');

        // create a file pointer connected to the output stream
        $file = fopen('php://output', 'w');

        fputcsv($file, $heading, $delimiter, $enclosure);

        foreach ($q->result() as $product) {

            $line = array(
                prepare_csv_export_string($product->supplier_name),
                prepare_csv_export_string($product->product_name),
                prepare_csv_export_string($product->product_supplier_code),
                prepare_csv_export_string(str_replace("\n", " ", $product->product_description)),
                prepare_csv_export_string(str_replace("\n", " ", $product->product_supplier_description)),
                $product->product_supplier_price,
                $product->product_base_price,
                '',
                '',
                prepare_csv_export_string($product->warehouse_location),
            );

            fputcsv($file, $line, $delimiter, $enclosure);
        }
        exit();
    }
    
    public function downloadSampleProductImportFile(){
        
        $download_file_name = 'sample_import_products.csv';
        $examples = array(
            array(
                'Test Supplier','Product #1','PRODSUPPLIER','Product Description','Product Supplier Description','100','120','','','Warehouse Location Name'
            ),
            array(
                'Test Supplier','Product #2','PRODSUPPLIER','Product Description','Product Supplier Description','100','130','','','Warehouse Location Name'
            ),
            array(
                'Test Supplier','Product #3','PRODSUPPLIER','Product Description','Product Supplier Description','100','120','','','Warehouse Location Name'
            ),
        );
        $heading = array(
            'supplier_name',
            'product_name',
            'product_supplier_code',
            'product_description',
            'product_supplier_description',
            'product_supplier_price',
            'product_base_price',
            're-order_qty',
            'qty_on_order',
            'warehouse_location'
        );

        //generating csv file
        $delimiter = ',';
        $enclosure = '"';

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename="' . $download_file_name . '"');

        // do not cache the file
        header('Pragma: no-cache');
        header('Expires: 0');

        // create a file pointer connected to the output stream
        $file = fopen('php://output', 'w');

        fputcsv($file, $heading, $delimiter, $enclosure);

        foreach ($examples as $inventory) {
            $line = array(
                $inventory[0],
                $inventory[1],
                $inventory[2],
                $inventory[3],
                $inventory[4],
                $inventory[5],
                $inventory[6],
                $inventory[7],
                $inventory[8],
                $inventory[9],
            );

            fputcsv($file, $line, $delimiter, $enclosure);
        }
        exit();
    }

    function insert_new_products() {

        $this->db->query("SET NAMES 'utf8' COLLATE 'utf8_general_ci'");

        $sql = "INSERT 
			   INTO mcb_products (supplier_id, product_name, product_description, product_supplier_description, product_supplier_code, product_supplier_price, product_base_price, product_active, product_last_changed)
			 SELECT c.client_id AS supplier_id, imp.product_name, imp.product_description, imp.product_supplier_description, imp.product_supplier_code, imp.product_supplier_price, imp.product_base_price, imp.product_active, CURRENT_TIMESTAMP
			   FROM mcb_products_import AS imp
			   JOIN mcb_clients AS c ON LOWER(c.client_name) = LOWER(imp.supplier_name)
			   LEFT JOIN mcb_products AS p ON p.product_name = imp.product_name
			  WHERE p.product_name IS NULL";

        $this->db->query($sql);

        $ret = $this->db->affected_rows();

        if ($ret > 0) {
            log_message('debug', 'Created ' . $ret . ' new products');
        }
    }

    function update_existing_products() {

        $sql = "UPDATE mcb_products p
			  INNER JOIN mcb_products_import imp ON p.product_name = imp.product_name
			    SET p.product_base_price = imp.product_base_price,
				    p.product_supplier_price = imp.product_supplier_price,
				    p.product_supplier_code = imp.product_supplier_code,
				    p.product_description = imp.product_description,
				    p.product_supplier_description = imp.product_supplier_description,
					p.product_last_changed = CURRENT_TIMESTAMP
			  WHERE ((p.product_base_price <> imp.product_base_price)
				 OR (p.product_supplier_price <> imp.product_supplier_price)
				 OR (p.product_supplier_code <> imp.product_supplier_code)
				 OR (p.product_description <> imp.product_description)
				 OR (p.product_supplier_description <> imp.product_supplier_description))";

        $this->db->query($sql);

        $ret = $this->db->affected_rows();

        if ($ret > 0) {
            log_message('debug', 'Updated ' . $ret . ' products');
        }
    }

    public function delete($product_id) {

        //Can only delete product if never sold via invoicing 
        //$this->load->model('products/mdl_products');

        /* Delete the supplier record */
        parent::delete(array('product_id' => $product_id));

        /*
         * Delete any related products, but use the product model so records
         * related to the product are also deleted
         */

        /*
          $this->db->select('product_id');

          $this->db->where('supplier_id', $supplier_id);

          $products = $this->db->get('mcb_products')->result();

          foreach ($products as $product) {

          $this->mdl_products->delete($product->product_id);

          }
         */
    }

    public function save() {

        $db_array = parent::db_array();

        if (!$this->input->post('product_active')) {

            $db_array['product_active'] = 0;
        }
        parent::save($db_array, uri_assoc('product_id'));
    }

    /**
     * gets history of import
     * @return type
     */
    public function getImportHistory() {
        $this->db->where('active_status', '1');
        $this->db->order_by('id', 'DESC');
        $q = $this->db->get('mcb_product_import_history');
        return $q->result();
    }

    public function dorollback($historyid) {
        $this->db->where('id', $historyid);
        $this->db->where('active_status', '1');
        $q = $this->db->get('mcb_product_import_history');
        $res = $q->row();
        if ($q->num_rows() > 0) {
            $res1 = $this->rollbackProducts($res->imported_products);
            //let's not delete the supplier 
            //$res2 = $this->rollbackSuppliers($res->imported_supplier_ids);
            $res3 = $this->rollbackUpdatedProducts($res->updated_product_ids, $historyid);
            if ($res1 && $res3) {
                return $this->deactiveHistory($historyid);
            }
        }
        return FALSE;
    }

    private function rollbackUpdatedProducts($updated_product_ids, $historyid) {
        if ($updated_product_ids != '') {
            $updated_product_ids = explode(',', $updated_product_ids);
            if (sizeof($updated_product_ids) > 0) {
                foreach ($updated_product_ids as $u_prd_id) {
                    $this->revertUpdateProductDetail($u_prd_id, $historyid);
                }
            }
        }

        return TRUE;
    }

    private function revertUpdateProductDetail($prod_id, $historyid) {
        //get detail from history backup
        $product = $this->productDetailAtHistoryPoint($prod_id, $historyid);
        if ($product) {
            $this->db->where('product_id', $prod_id);
            $data = array(
                'supplier_id' => $product->supplier_id,
                'product_name' => $product->product_name,
                'product_description' => $product->product_description,
                'product_supplier_description' => $product->product_supplier_description,
                'product_supplier_code' => $product->product_supplier_code,
                'product_supplier_price' => $product->product_supplier_price,
                'product_base_price' => $product->product_base_price,
                'stock_qty' => $product->stock_qty,
                'warehouse_location' => $product->warehouse_location,
                'product_last_changed' => date('Y-m-d H:i:s'),
            );

            return $this->db->update('mcb_products', $data);
        }
        return FALSE;
    }

    private function productDetailAtHistoryPoint($prod_id, $historyid) {
        $where = array(
            'history_id' => $historyid,
            'product_id' => $prod_id
        );
        $this->db->where($where);
        $q = $this->db->get('mcb_products_update_history_backup');
        return $q->row();
    }

    private function deactiveHistory($historyid) {
        $this->db->where('id', $historyid);
        $data = array(
            'active_status' => '0'
        );
        return $this->db->update('mcb_product_import_history', $data);
    }

    private function rollbackSuppliers($supplierids) {
        if ($supplierids != '') {
            $supplierids = explode(',', $supplierids);
            if (sizeof($supplierids) > 0) {
                foreach ($supplierids as $pid) {
                    if ($pid != '') {
                        $this->deActivateSupplier($pid);
                    }
                }
            }
        }
        return TRUE;
    }

    private function deActivateSupplier($pid) {
        $this->db->where('client_id', $pid);
        $data = array(
            'client_active' => '0'
        );
        return $this->db->delete('mcb_clients');
    }

    private function rollbackProducts($productids) {
        if ($productids != '') {
            $productids = explode(',', $productids);
            if (sizeof($productids) > 0) {
                foreach ($productids as $pid) {
                    if ($pid != '') {
                        $this->deActivateProduct($pid);
                    }
                }
            }
        }
        return TRUE;
    }
    
    function delete_override_product($source_file1) {
        $row = 1;
        $error_str = '';
        $num = 0;
        $csv_products = '';
        $fileFormate = TRUE;
        
        if (($handle = fopen($source_file1, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
                $temp = array();
                foreach($data as $d){
                    $val = str_replace("\0","",$d);
                    $temp[] = $val;
                }
                $data = $temp;
                if ($row == 1) {
                    //if ($data[1] != 'product_name' || $data[0] != 'supplier_name' || $data[2] != 'product_supplier_code' || $data[3] != 'product_description' || $data[4] != 'product_supplier_description' || $data[5] != 'stock_qty' || $data[6] != 'product_supplier_price' || $data[9] != 'warehouse_location') {
                    if ($data[1] != 'product_name' || $data[0] != 'supplier_name' || $data[2] != 'product_supplier_code' || $data[3] != 'product_description' || $data[4] != 'product_supplier_description' || $data[5] != 'product_supplier_price' || $data[6] != 'product_base_price') {
                        $error_str .= 'The CSV file is not of valid format. Please see an example export for the right format.';
                        $fileFormate = FALSE;
                    }
                }
                $num = count($data);
                if ($row > 1) {
                    if ($num >= 6) {
                        $csv_products[] = $data[1];       
                    }
                }
                $row++;
            }
            fclose($handle);    
        }
        
        if(($fileFormate == TRUE) && ($row > 2) && ($csv_products != '')){
            $all_pro = $this->query('SELECT product_name FROM mcb_products'); 
            foreach ($all_pro as $value2) {
                $db_products[] = $value2->product_name;
            }
            
            foreach ($db_products as $db_pro_name) {    
                if (in_array($db_pro_name, $csv_products)){
                    $this->update('mcb_products', array('is_arichved'=>'0'), array('product_name'=>$db_pro_name));
                } else {
                    $this->update('mcb_products', array('is_arichved'=>'1'), array('product_name'=>$db_pro_name));
                }
            }
        } else {
            return FALSE;
        }
    }
    
    
    private function deActivateProduct($pid) {
        $this->db->where('product_id', $pid);
        return $this->db->delete('mcb_products');
    }
    
    function export_before_import($lastchanged_Ymd = null) {
        
        $supplierCond = '';    
        $this->db->query("SET NAMES 'utf8' COLLATE 'utf8_general_ci'");
        $sql = "SELECT client_name AS supplier_name,
                    product_name, product_supplier_code,
                    product_description, product_supplier_description,
                    product_supplier_price, product_base_price, product_last_changed
               FROM `mcb_products` p
              INNER JOIN mcb_clients c
                 ON c.client_id = p.supplier_id
              WHERE product_last_changed > ? " . $supplierCond;
        $sql .= "ORDER BY supplier_name, product_name";
        
        $lastchanged = new Datetime('2009-01-01 00:00');
        if (null !== $lastchanged_Ymd) {
            $lastchanged = $lastchanged->createFromFormat('YmdHi', $lastchanged_Ymd);
        }
        $lastchangedStr = $lastchanged->format('Y-m-d H:i');
        $download_file_name = 'products_created_at_'.date("d_m_Y_h_i_sa").'.csv';
        $q = $this->db->query($sql, array($lastchangedStr));
        $fields = $q->field_data();
        $heading = array(
            'supplier_name',
            'product_name',
            'product_supplier_code',
            'product_description',
            'product_supplier_description',
            'product_supplier_price',
            'product_base_price',
            're-order_qty',
            'qty_on_order',
            'warehouse_location'
        );

        //generating csv file
        $delimiter = ',';
        $enclosure = '"';
        // create a file pointer connected to the output stream
        $file = fopen('uploads/products_before_overwrite/'.$download_file_name, 'w');
        
        fputcsv($file, $heading, $delimiter, $enclosure);

        foreach ($q->result() as $product) {

            $line = array(
                prepare_csv_export_string($product->supplier_name),
                prepare_csv_export_string($product->product_name),
                prepare_csv_export_string($product->product_supplier_code),
                prepare_csv_export_string(str_replace("\n", " ", $product->product_description)),
                prepare_csv_export_string(str_replace("\n", " ", $product->product_supplier_description)),
                $product->product_supplier_price,
                $product->product_base_price,
                '',
                '',
                prepare_csv_export_string($product->warehouse_location),
            );
            fputcsv($file, $line, $delimiter, $enclosure);
        }
    }
    
    
    public function get_all($table_name, $condition = '', $order = '', $limit = '') {
        if ($order != '') {
            $this->db->order_by($order);
        }
        if ($limit != '') {
            $this->db->limit($limit);
        }
        if ($condition != '') {
            $this->db->where($condition);
        }
        $q = $this->db->get($table_name);
        $Res = $q->result();
        return $Res;
    }

    function query_array($qry) {
        $q = $this->db->query($qry);
        $Res = $q->result_array();
        return $Res;
    }
    
    
    function query($qry) {
        $q = $this->db->query($qry);
        $Res = $q->result();
        return $Res;
    }
    
    function delete_product($tbl_name, $condition) {
        $this->db->delete($tbl_name, $condition);
        return TRUE;
    }
    
    function update($tbl_name, $data, $condition) {
        
        $this->db->where($condition);
        $this->db->update($tbl_name, $data);
    }
}?>