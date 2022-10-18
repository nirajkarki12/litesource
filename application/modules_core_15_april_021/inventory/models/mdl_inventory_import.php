<?php

(defined('BASEPATH')) OR exit('No direct script access allowed');

class Mdl_Inventory_Import extends MY_Model {

    public $_inventory_import_qty_history;

    public function __construct() {

        parent::__construct();
        $this->_inventory_import_qty_history = array();

        $this->table_name = 'mcb_inventory_import';

        $this->select_fields = "
		SQL_CALC_FOUND_ROWS
		mcb_inventory_import.*";

        $this->primary_key = 'mcb_inventory_import.id';
    }

    function process_inventory_data_file($source_file, $filename) {

//        ini_set('display_errors', 1);
//        ini_set('display_startup_errors', 1);
//        error_reporting(E_ALL);

        ini_set('max_execution_time', 30000);
        log_message('debug', 'Processing inventory data file ' . $source_file);

        //now clearing import table, rather keeping rollback feature for all past imports
        //$this->clear_import_table();
        $row = 1;
        $insert_rows = array();
        $error_str = '';
        $inventory_import_success_cnt = 0;
        $inventory_import_fail_cnt = 0;
        $imported_inventory_ids = '';
        $imported_supplier_ids = '';
        $num = 0;
        $updated_prod_array = array();
        $updated_inventory_str = '';

        $sku_arrs = array();
        $a_sku = $this->common_model->query_as_object("SELECT sku, inventory_id FROM `mcb_inventory_item`");
        foreach ($a_sku as $s) {
            $sku = $s->sku;
            $sku_arrs[$sku] = $s->inventory_id;
        }

        $pis = array();
        $product_inventory = $this->common_model->query_as_object("SELECT mcb_products_inventory.product_id, mcb_inventory_item.sku FROM `mcb_products_inventory` INNER JOIN mcb_inventory_item ON mcb_products_inventory.inventory_id = mcb_inventory_item.inventory_id;");
        foreach ($product_inventory as $pi) {
            $product_id = $pi->product_id;
            unset($pi->product_id);
            $pis[$product_id][] = $pi->sku;
        }

        if (($this->input->post('db_inventory_overwrite') == '1')) {
            $this->export_before_import();
            $this->delete_override_inventory($source_file);
        }

        if (($handle = fopen($source_file, "r")) !== FALSE) {

            $r_i_arr = array();
            while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
                $temp = array();
                foreach ($data as $d) {
                    $val = str_replace(array("\xB0", "\0", "\xBA", "\x86", "\x"), "", $d);
                    $temp[] = $val;
                }
                $data = $temp;
                $num = count($data);
                if (($row == 1)) {
                    if ($num >= 8) {
                        if ($data[0] == 'inventory_type' && $data[2] == 'supplier_name' && $data[3] == 'name' && $data[4] == 'description' && $data[11] == 'location') {
                            //if all true.. do nothing..
                        } else {
                            $error_str .= 'The CSV file is not of valid format. Please see an example export for the right format.<br>';
                            break;
                        }
                    }

                    for ($x = 10; $x <= count((array) $data); $x++) {
                        $r_i_matched = preg_match('/&?related_inv_.+?(&|$)$/', $data[$x]);
                        if ($r_i_matched == TRUE) {
                            $r_i_arr[$x] = $data[$x];
                        }
                    }
                }

                if ($row > 1) {

                    if ($num >= 10) {

                        $c_data = array();
                        $c_data = array(
                            'inventory_type' => trim($data[0]),
                            'sku' => $data[1],
                            'supplier_name' => trim($data[2]),
                            'name' => trim($data[3]),
                            'description' => trim(htmlentities($data[4], ENT_COMPAT, 'ISO-8859-1', true)),
                            'supplier_code' => $data[5],
                            'supplier_description' => trim(htmlentities($data[6], ENT_COMPAT, 'ISO-8859-1', true)),
                            'supplier_price' => trim(round((float) str_replace(array(',', '$'), '', $data[7]), 2)),
                            'base_price' => trim(round((float) str_replace(array(',', '$'), '', $data[8]), 2)),
                            'qty' => trim(floatval($data[9])),
                            'location' => trim($data[11]),
                            'category_name' => trim($data[12]),
                            'quote_status' => ( strcasecmp(trim($data[13]), 'Yes')== 0 ?'1':'0'),
                        );

                        $ri_count = count((array) $r_i_arr);
                        foreach ($r_i_arr as $key => $value) {
                            $c_data[$value] = $data[$key];
                        }

                        //getting supplier id from supplier_name
                        $supplier_id = $this->getSupplierIdFromName($c_data['supplier_name']);
                        if (!$supplier_id) {
                            $error_str .= "{$c_data['supplier_name']} does not exist. Creating {$c_data['supplier_name']} <br/>";
                            $supplier_id = $this->createClientAsSupplier($c_data['supplier_name']);
                            if ($supplier_id) {
                                $imported_supplier_ids .= $supplier_id . ',';
                            }
                        }

                        //get category id from category_name


                        $imported_category_ids = '';
                        if (empty(trim($c_data['category_name']))) {
                            $c_data['category_name'] = 'Uncategorized';
                        }
                        //if (!empty($c_data['category_name'])) {
                        $category_id = $this->getCategoryIdFromName($c_data['category_name']);
                        
                        if (!$category_id) {
                            $error_str .= "Category {$c_data['category_name']} does not exist. Creating {$c_data['category_name']} <br/>";
                            $category_id = $this->createCategory($c_data['category_name']);
                            /* if ($category_id) {
                              $imported_category_ids .= $category_id . ',';
                              } */
                        }
                        //}
                        //$duplicate_inventory = $this->is_duplicate_inventory($data[2], $supplier_id);
                        //$duplicate_inventory = $this->is_duplicate_inventory($data[3]);
                        $duplicate_inventory = $this->is_duplicate_sku($c_data['sku']);


                        if ((!$duplicate_inventory)) {
                            if (!$this->insert_import_table($data, $c_data)) {
                                $error_str .= 'Something went wrong while importing in line "' . $row . '". Please make sure your csv file is valid. <br/>';
                            } else {
                                if (!$supplier_id) {
                                    $error_str .= 'Could not create the client as supplier "' . $c_data['supplier_name'] . '" according to line ' . $row . ' in your csv file. Escaping this inventory row.<br/>';
                                    $inventory_import_fail_cnt++;
                                } else {
                                    $imported_supplier_ids .= $supplier_id . ',';
                                }
                                if ($supplier_id && $category_id) {
                                    //now adding the inventory
                                    if (($c_data['sku'] == '') || ($c_data['sku'] == 0)) {
                                        //add item and its stock 
                                        $inventory_id = $this->addNewInventoryItem($data, $supplier_id, $c_data, $category_id);
                                        if ($inventory_id) {
                                            $error_str .= 'Added "' . $c_data['name'] . '".<br/>';
                                            if ($c_data['inventory_type'] == 'Grouped') {

                                                $t_sku = "SKU-{$inventory_id}";
                                                $sku_arrs[$t_sku] = $inventory_id;
                                                $pro_i = $inventory_id;
                                                $csv_rel_invs_arr = array();


                                                if ($ri_count > 0) {
                                                    for ($x = 1; $x <= $ri_count; $x++) {
                                                        if (($c_data['related_inv_' . $x] != '') || ($c_data['related_inv_' . $x] != 0) || ($c_data['related_inv_' . $x] != NULL)) {
                                                            $c_sku = $c_data['related_inv_' . $x];
                                                            if (!isset($sku_arrs[$c_sku])) {
                                                                $error_str .= 'Inventory of "' . $c_data['name'] . '" sku "' . $c_sku . '" could not be found on line no ' . $row . ' from the csv file.<br/>';
                                                            } else {
                                                                $csv_rel_invs_arr[] = $c_sku;
                                                            }
                                                        }
                                                    }
                                                }

                                                if (count((array) $csv_rel_invs_arr) > 0) {
                                                    foreach ($csv_rel_invs_arr as $value) {
                                                        $inv_id = $sku_arrs[$value];
                                                        $this->common_model->insert('mcb_products_inventory_raw', array('product_id' => $pro_i, 'inventory_id' => $inv_id, 'inventory_qty' => '1'));
                                                    }
                                                }
                                            }

                                            $inventory_import_success_cnt++;
                                            $imported_inventory_ids .= $inventory_id . ',';
                                        } else {
                                            //var_dump($this->is_duplicate_inventory($data[1],$supplier_id),$this->db->last_query()); exit();
                                            $inventory_import_fail_cnt++;
                                            //$error_str .= 'Inventory "' . $c_data['name'] . '" could not be imported of line ' . $row . ' from the csv file.<br/>';
                                            $error_str .= 'Inventory "' . $c_data['name'] . '" could not be imported of line ' . $row . ' from the csv file (May be you are trying duplicate entry supplier and cat#).<br/>';
                                        }
                                    } else {
                                        $inventory_import_fail_cnt++;
                                        $error_str .= 'Inventory  of sku "' . $c_data['sku'] . '" does not exists.<br/>';
                                    }
                                }
                            }
                        } else {

                            $error_str .= 'Updated "' . $c_data['name'] . '".<br/>';
                            //$inventory_import_fail_cnt++;
                            //now going to update the inventory
                            if ($supplier_id) {
                                //update item and its stock 
                                if ($this->updateInventoryItemDetail($duplicate_inventory, $data, $supplier_id, $c_data)) {

                                    if ($c_data['inventory_type'] == 'Grouped') {
                                        $csv_rel_invs_arr = array();
                                        $pro_i = $duplicate_inventory->inventory_id;
                                        $c_rel_invs_arr = $pis[$pro_i];

                                        if (($ri_count) > 0) {
                                            for ($x = 1; $x <= $ri_count; $x++) {
                                                if (($c_data['related_inv_' . $x] != '') || ($c_data['related_inv_' . $x] != 0) || ($c_data['related_inv_' . $x] != NULL)) {
                                                    $c_sku = $c_data['related_inv_' . $x];
                                                    if (!isset($sku_arrs[$c_sku])) {
                                                        $error_str .= 'Inventory of "' . $c_data['name'] . '" sku "' . $c_sku . '" could not be found on line no ' . $row . ' from the csv file.<br/>';
                                                    } else {
                                                        $csv_rel_invs_arr[] = $c_sku;
                                                    }
                                                }
                                            }
                                        }
                                        $del_arr = array_diff($c_rel_invs_arr, $csv_rel_invs_arr);
                                        if (count((array) $del_arr) > 0) {
                                            foreach ($del_arr as $value) {
                                                $inv_id = $sku_arrs[$value];
                                                $this->common_model->delete('mcb_products_inventory_raw', array('product_id' => $pro_i, 'inventory_id' => $inv_id));
                                            }
                                        }
                                        $new_arr = array_diff($csv_rel_invs_arr, $c_rel_invs_arr);
                                        if (count((array) $new_arr) > 0) {
                                            foreach ($new_arr as $value) {
                                                $inv_id = $sku_arrs[$value];
                                                $this->common_model->insert('mcb_products_inventory_raw', array('product_id' => $pro_i, 'inventory_id' => $inv_id, 'inventory_qty' => '1'));
                                            }
                                        }
                                    }

                                    $updated_prod_array[$duplicate_inventory->inventory_id] = $duplicate_inventory;
                                    $updated_inventory_str .= $duplicate_inventory->inventory_id . ',';
                                    $inventory_import_success_cnt++;
                                } else {
                                    $error_str .= 'Inventory of "' . $c_data['name'] . '" sku "' . $c_data['sku'] . '" could not be imported of line no ' . $row . ' from the csv file.<br/>';
                                }
                            }
                        }
                    } else {
                        $error_str .= 'Error: The csv format is wrong in line "' . $row . '". Please export a sample and udpate it. <br/>';
                        $inventory_import_fail_cnt++;
                    }
                }
                $row++;
                $this->_row++;
            }
            fclose($handle);
        }
        $err_bkp = '';
        //if ($imported_inventory_ids != '' || $updated_inventory_str != '') {
        //add record for rollback, 
        $data = array(
            'imported_inventories_ids' => $this->db->escape($imported_inventory_ids),
            'updated_inventories_ids' => $this->db->escape($updated_inventory_str),
            'imported_supplier_ids' => $this->db->escape($imported_supplier_ids),
            'import_from_file_name' => $filename,
            'log' => '',
            'imported_at' => date('Y-m-d H:i:s')
        );

        $this->db->insert('mcb_inventory_import_history', $data);
        $history_id = $this->db->insert_id();
        if ($history_id && $updated_inventory_str != '') {
            //make backup of what inventory detail was before the import for updated inventories so
            //that we can take the inventory to exactly that point where it was before import
            $err_bkp = $this->makeUpdateImportBackupForRollback($updated_prod_array, $history_id);
        }
        //}
        log_message('debug', 'Imported ' . $imported_inventory_ids . ' inventories.');
        $fin_msg = 'Total inventory lines in imported csv file: ' . ($row - 2) . '<br/>'; //1 header and another indexing from 1
        if ($inventory_import_success_cnt > 0) {
            $fin_msg .= 'Successfully imported ' . $inventory_import_success_cnt . ' inventories. <br/>';
        }
        if ($inventory_import_fail_cnt > 0) {
            $fin_msg .= 'Failed to import ' . $inventory_import_fail_cnt . ' inventories. <br/>';
        }
        if ($error_str != '') {
            $fin_msg .= '<div class="debug_text fa-plus fa"><span><img class="show expanddebug" src="' . base_url() . 'assets/slick/images/expand.gif"/></span><span><img class="hide collapsedebug" src="' . base_url() . '/assets/slick/images/collapse.gif"/></span>Import Log</div> <br/>';
            $fin_msg .= '<div class="debug_detail hide">' . $error_str . $err_bkp . '</div>';
        }
        //save the import log to database, session flash can not handle big debug message
        $this->updateInventoryImportLogMessage($history_id, $fin_msg);
        //saving inventory_import_history
        $this->updateInventoryImportHistory($history_id);
        return $history_id;
    }

    private function updateInventoryImportHistory($history_id) {
        if (sizeof($this->_inventory_import_qty_history) > 0) {
            foreach ($this->_inventory_import_qty_history as $history) {
                $history['history_id'] = $history_id;
                $this->db->insert('mcb_inventory_history', $history);
            }
        }
        return TRUE;
    }

    public function getLogMessage($historyid) {
        $this->db->select('log');
        $this->db->where('id', $historyid);
        $q = $this->db->get('mcb_inventory_import_history');
        $res = $q->row();
        if (isset($res->log))
            return $res->log;
        return FALSE;
    }

    private function updateInventoryImportLogMessage($history_id, $fin_msg) {
        $this->db->where('id', $history_id);
        $data = array(
            'log' => $fin_msg
        );
        return $this->db->update('mcb_inventory_import_history', $data);
    }

    private function makeUpdateImportBackupForRollback($updated_prod_array, $history_id) {


        $error_str = '';
        if (sizeof($updated_prod_array) > 0) {
            foreach ($updated_prod_array as $inventory_id => $inventory) {
                $data = array(
                    'inventory_history_id' => $history_id,
                    'inventory_id' => $inventory_id,
                    'supplier_id' => $inventory->supplier_id,
                    'name' => ( $inventory->name ),
                    'description' => ( $inventory->description ),
                    'base_price' => $inventory->base_price,
                    'qty' => $inventory->qty,
                    'supplier_code' => $inventory->supplier_code,
                    'supplier_description' => $inventory->supplier_description,
                    'supplier_price' => $inventory->supplier_price,
                    'location' => $inventory->location,
                    'inevntory_last_changed' => date('Y-m-d H:i:s'),
                );
                if (!$this->db->insert('mcb_inventory_update_history_backup', $data)) {
                    $error_str .= 'Error: Could not create backup for ' . $inventory->name . '<br/>';
                }
            }
        }
        if ($error_str != '')
            return $error_str;
        return FALSE;
    }

    private function updateInventoryItemDetail($duplicate_inventory, $data, $supplier_id, $c_data) {

        if ($data[0] == 'Part') {
            $data[0] = '0';
        } else if ($data[0] == 'Grouped') {
            $data[0] = '1';
        } else {
            return FALSE;
        }
        $inventory_id = $duplicate_inventory->inventory_id;



        if (count($data) >= 9) {

            $duplicate_inventory->supplier_id = $supplier_id;
            $data_i = array(
                'supplier_id' => $duplicate_inventory->supplier_id,
                'name' => ( $data[3] ),
                'description' => htmlentities($data[4], ENT_COMPAT, 'ISO-8859-1', true), //htmlspecialchars( $data[3] ),
                'supplier_code' => ( $data[5] ),
                'supplier_description' => htmlentities($data[6], ENT_COMPAT, 'ISO-8859-1', true), //htmlspecialchars( $data[5] ),
                'supplier_price' => round((float) str_replace(array(',', '$'), '', $data[7]), 2),
                'base_price' => round((float) str_replace(array(',', '$'), '', $data[8]), 2),
                'qty' => round((float) $data[9], 2),
                'location' => $data[11],
                'inevntory_last_changed' => date('Y-m-d H:i:s'),
                'inventory_type' => $data[0],
                'is_arichved' => '0',
                'quote_status'=> ( strcasecmp($data[13], 'Yes')== 0 ?'1':'0'),
            );
            if (isset($_POST['unaffect_qty'])) {
                if ($this->input->post('unaffect_qty') == '1') {
                    unset($data_i['qty']);
                }
            }
        }

        $pos = strpos($data_i['name'], '{mm}');
        if ($pos) {
            $data_i['use_length'] = '1';
        } else {
            $data_i['use_length'] = '0';
        }
        if ($duplicate_inventory->use_length == '1') {
            $data_i['use_length'] = '1';
        }


        $this->db->where(array('inventory_id' => $inventory_id));
        $res = $this->db->update('mcb_inventory_item', $data_i);


        $this->db->where(array('inventory_id' => $inventory_id));

        $totalPendingQuery = $this->db->query("SELECT coalesce(SUM(qty_pending),0)as qty_pending FROM mcb_item_stock WHERE inventory_id = $inventory_id");
        $totalPendingQty = $totalPendingQuery->result();


        $stock_qty = array(
            'inventory_id' => $inventory_id,
            'qty_pending' => -($totalPendingQty[0]->qty_pending),
            'qty_update_source' => 'csv-import'
        );
        $this->db->insert('mcb_item_stock', $stock_qty);

        if (count($data) >= 9) {
            $sign = ($data[9] >= $duplicate_inventory->qty) ? '+1' : '-1';
            $hq = ($sign) * (abs($data[9] - $duplicate_inventory->qty));


            if ($data[9] == $duplicate_inventory->qty) {
                $hq = (abs($data[9] - $duplicate_inventory->qty));
            }

            //save the history
            $history = array(
                'inventory_id' => $inventory_id,
                'history_qty' => $hq,
                'notes' => 'Import',
                'user_id' => $this->session->userdata('user_id'),
                'created_at' => date('Y-m-d H:i:s'),
            );

            $this->_inventory_import_qty_history[] = $history;
        }
        //$this->db->insert('mcb_inventory_history', $history);

        return $res;
    }

    private function addNewInventoryItem($data, $supplier_id, $c_data, $category_id) {

        if (trim(strtolower($data[0])) == trim(strtolower('Part'))) {
            $data[0] = '0';
        } elseif (trim(strtolower($data[0])) == trim(strtolower('Grouped'))) {
            $data[0] = '1';
        } else {
            return FALSE;
        }
        $dataI = array(
            'supplier_id' => $supplier_id,
            'name' => ( $data[3] ),
            'description' => htmlentities($data[4], ENT_COMPAT, 'ISO-8859-1', true), //htmlspecialchars( $data[3] ),
            'supplier_code' => ( $data[5] ),
            'supplier_description' => htmlentities($data[6], ENT_COMPAT, 'ISO-8859-1', true), //htmlspecialchars( $data[5] ),
            'supplier_price' => round((float) str_replace(array(',', '$'), '', $data[7]), 2),
            'base_price' => round((float) str_replace(array(',', '$'), '', $data[8]), 2),
            'qty' => ( floatval($data[9])),
            'location' => $data[11],
            'inventory_type' => $data[0],
            'is_arichved' => '0',
            'category_id' => $category_id,
            'quote_status'=> ( strcasecmp($data[13], 'Yes')== 0 ?'1':'0'),
        );

        //USES dataI 
        $pos = strpos($dataI['name'], '{mm}');
        if ($pos) {
            $dataI['use_length'] = '1';
        } else {
            $dataI['use_length'] = '0';
        }
//        echo '<pre>';
//        print_r($dataI);
//        echo '</pre>';
//        die;
        if ($this->db->insert('mcb_inventory_item', $dataI)) {
            $inventory_id = $this->db->insert_id();

            //---- adding sku id-------
            $sku_name = "SKU-{$inventory_id}";
            $this->common_model->update('mcb_inventory_item', array('sku' => $sku_name), array('inventory_id' => $inventory_id));

            $sign = '+1';
            //save the history
            $history = array(
                'inventory_id' => $inventory_id,
                'history_qty' => ($sign) * ( floatval($data[9])),
                'notes' => 'Import ',
                'user_id' => $this->session->userdata('user_id'),
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->_inventory_import_qty_history[] = $history;

            return $inventory_id;
        }
        //var_dump($this->db->last_query());	
        return FALSE;
    }

    /**
     * creates new client as supplier
     * @param type $client_name
     * @return boolean
     */
    private function createClientAsSupplier($client_name) {
        $data = array(
            'client_name' => trim($client_name),
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

    public function createCategory($category_name) {
        $data = array(
            'category_name' => $category_name
        );
        if ($this->db->insert('mcb_category', $data)) {
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
        $this->db->where('client_name', trim($supplier_name));
        //$this->db->where('client_active', '1');
        $q = $this->db->get('mcb_clients');
        if ($q->num_rows() > 0) {
            $res = $q->row();
            return $res->client_id;
        }
        return FALSE;
    }

    public function getCategoryIdFromName($category_name) {

        $this->db->where('category_name', trim($category_name));

        //    $this->db->where('category_deleted', 0);
        //$this->db->where('client_active', '1');
        $q = $this->db->get('mcb_category');
        if ($q->num_rows() > 0) {
            $res = $q->row();
            return $res->category_id;
        }
        return FALSE;
    }

    /**
     * checks if the inventory is duplicate by the inventory name
     * @param type $name
     * @return boolean
     */
    private function is_duplicate_inventory($name, $supplier_id = NULL) {
        $this->db->where('name', $name);
        if ($supplier_id != NULL) {
            $this->db->where('supplier_id', $supplier_id);
        }
        $q = $this->db->get('mcb_inventory_item');
        if ($q !== FALSE && $q->num_rows() > 0) {
            //var_dump($this->db->last_query()); 
            $res = $q->row();
            return $res;
        }
        return FALSE;
    }

    function is_duplicate_sku($sku) {

        $sku = str_replace(' ', '', $sku);

        $this->db->where('sku', $sku);
        $q = $this->db->get('mcb_inventory_item');


        if ($q !== FALSE && $q->num_rows() > 0) {
            //var_dump($this->db->last_query()); 
            $res = $q->row();


            return $res;
        }
        return FALSE;
    }

    private function insert_import_table($data, $c_data) {
        if (($c_data['inventory_type'] != 'Part') && ($c_data['inventory_type'] != 'Grouped')) {
            return 0;
        }
        $data = array(
            'supplier_name' => $c_data['supplier_name'],
            'name' => $c_data['name'],
            'description' => $c_data['description'],
            'supplier_code' => $c_data['supplier_code'],
            'supplier_description' => $c_data['supplier_description'],
            'supplier_price' => $c_data['supplier_price'],
            'base_price' => $c_data['base_price'],
            'qty' => $c_data['qty'],
            'location' => $c_data['location'],
            'imported_at' => date('Y-m-d H:i:s'),
        );
        return $this->db->insert('mcb_inventory_import', $data);
    }

    function clear_import_table() {

        log_message('debug', 'Truncating import table ' . $this->table_name);
        $this->db->query('TRUNCATE TABLE ' . $this->table_name);
    }

    function insert_new_inventory() {

        $this->db->query("SET NAMES 'utf8' COLLATE 'utf8_general_ci'");

        $sql = "INSERT
			   INTO mcb_inventory_item (supplier_id, name, description, base_price, qty, supplier_code, supplier_description, supplier_price, location)
			 SELECT imp.supplier_id, imp.name, imp.description, imp.base_price, imp.qty, imp.supplier_code, imp.supplier_description, imp.supplier_price, imp.location
			   FROM mcb_inventory_import AS imp";

        $this->db->query($sql);

        $ret = $this->db->affected_rows();

        if ($ret > 0) {
            log_message('debug', 'Created ' . $ret . ' new inventory item');
        }
    }

    function insert_new_inventory_history() {
        $this->db->query("SET NAMES 'utf8' COLLATE 'utf8_general_ci'");
        $sql = "
                INSERT
                    INTO mcb_inventory_history(inventory_id, history_qty, notes, user_id)
                    SELECT item.inventory_id, imp.qty, 'Import csv file','" . $this->session->userdata('user_id') . "'
                    FROM mcb_inventory_import as imp join mcb_inventory_item as item
                        on imp.name = item.name
                ";
        $this->db->query($sql);
        $ret = $this->db->affected_rows();

        if ($ret > 0) {
            log_message('debug', 'Created ' . $ret . ' new inventory history');
        }
    }

    public function delete($inventory_id) {

        parent::delete(array('inventory_id' => $inventory_id));
    }

    public function save() {

        $db_array = parent::db_array();
        parent::save($db_array, uri_assoc('inventory_id'));
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

    public function export_before_import() {

        $pis = array();
        $product_inventory = $this->common_model->query_as_object("SELECT mcb_products_inventory.product_id, mcb_inventory_item.sku FROM `mcb_products_inventory` INNER JOIN mcb_inventory_item ON mcb_products_inventory.inventory_id = mcb_inventory_item.inventory_id;");
        foreach ($product_inventory as $pi) {
            $product_id = $pi->product_id;
            unset($pi->product_id);
            $pis[$product_id][] = $pi->sku;
        }

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
        $lastchanged = new Datetime('2009-01-01 00:00');
        if (null !== $lastchanged_Ymd) {
            $lastchanged = $lastchanged->createFromFormat('YmdHi', $lastchanged_Ymd);
        }
        $lastchangedStr = $lastchanged->format('Y-m-d H:i');
        $download_file_name = 'Active_snapshot_' . date("d_m_Y_h_i_sa") . '.csv';
        $this->db->query("SET NAMES 'utf8' COLLATE 'utf8_general_ci'");
        $sql = "select i.*, IF( (i.inventory_type > 0), 'Grouped' , 'Part' ) AS i_type, "
                . "c.client_name as supplier_name from mcb_inventory_item as i "
                . "join mcb_clients as c on c.client_id = i.supplier_id "
                . "where i.is_arichved != '1' and i.inevntory_last_changed > '" . $lastchangedStr . "'" . $supplierCond;
        $q = $this->db->query($sql);

        $i_a = array();
        foreach ($q->result() as $inventory) {
            if ($inventory->inventory_type == '1') {
                $inv_id = $inventory->inventory_id;
                $i_a[] = count((array) $pis[$inv_id]);
            }
        }
        $max_rel_inv_count = max($i_a);

        $heading = array(
            'inventory_type',
            'sku',
            'supplier_name',
            'name',
            'description',
            'supplier_code',
            'supplier_description',
            'supplier_price',
            'base_price',
            'qty',
            'qty_on_order',
            'location',
        );

        if ($max_rel_inv_count > 0) {
            for ($index = 1; $index <= $max_rel_inv_count; $index++) {
                $rel_key = "related_inv_" . $index;
                $heading[] = $rel_key;
            }
        }

        $invs = array();
        foreach ($q->result() as $inventory) {
            $inv_id = $inventory->inventory_id;
            if ($max_rel_inv_count > 0) {
                for ($index = 1; $index <= $max_rel_inv_count; $index++) {

                    $is_key = ($index - 1);
                    $rel_key = "related_inv_" . $index;
                    $inventory->$rel_key = '';
                    if ($inventory->inventory_type == '1') {
                        if (isset($pis[$inv_id][$is_key])) {
                            $inventory->$rel_key = $pis[$inv_id][$is_key];
                        }
                    }
                }
            }
            $invs[] = $inventory;
        }

        //generating csv file
        $delimiter = ',';
        $enclosure = '"';

        // create a file pointer connected to the output stream
        $file = fopen('uploads/products_before_overwrite/' . $download_file_name, 'w');
        fputcsv($file, $heading, $delimiter, $enclosure);
        foreach ($invs as $inventory) {

            $line = array(
                prepare_csv_export_string($inventory->i_type),
                prepare_csv_export_string($inventory->supplier_name),
                prepare_csv_export_string($inventory->name),
                prepare_csv_export_string(str_replace("\n", " ", $inventory->description)),
                prepare_csv_export_string($inventory->supplier_code),
                prepare_csv_export_string($inventory->supplier_description),
                $inventory->supplier_price,
                $inventory->base_price,
                $inventory->qty,
                '',
                prepare_csv_export_string($inventory->location),
            );
            if ($max_rel_inv_count > 0) {
                for ($index = 1; $index <= $max_rel_inv_count; $index++) {
                    $rel_key = "related_inv_" . $index;
                    if ($inventory->inventory_type == '1') {
                        $line[] = prepare_csv_export_string($inventory->$rel_key);
                    }
                }
            }
            fputcsv($file, $line, $delimiter, $enclosure);
        }
    }

    public function export_updated_inventories($lastchanged_Ymd = null, $sort_by, $sort_order) {

        $pis = array();
        $product_inventory = $this->common_model->query_as_object("SELECT mcb_products_inventory.product_id, mcb_inventory_item.sku FROM `mcb_products_inventory` INNER JOIN mcb_inventory_item ON mcb_products_inventory.inventory_id = mcb_inventory_item.inventory_id;");
        foreach ($product_inventory as $pi) {
            $product_id = $pi->product_id;
            unset($pi->product_id);
            $pis[$product_id][] = $pi->sku;
        }

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
        $lastchanged = new Datetime('2009-01-01 00:00');
        if (null !== $lastchanged_Ymd) {
            $lastchanged = $lastchanged->createFromFormat('YmdHi', $lastchanged_Ymd);
        }

        $is_archived = "i.is_arichved != '1' and ";
        if ($this->input->post('export_include_archived') == '1') {
            $is_archived = "";
        }

        $i_type = "";
        if ($this->input->post('type') != 'all') {
            $i_type = "i.inventory_type = '{$this->input->post('type')}' AND ";
        }

        $lastchangedStr = $lastchanged->format('Y-m-d H:i');
        $download_file_name = $lastchanged->format('Ymd') . $supplierNames . '_inventory.csv';
        $this->db->query("SET NAMES 'utf8' COLLATE 'utf8_general_ci'");

        $sql = "SELECT i.*, IF( (i.inventory_type > 0), 'Grouped' , 'Part' ) AS i_type,
                i.quote_status, 
                c.client_name AS supplier_name,cat.category_name from mcb_inventory_item AS i 
                LEFT JOIN mcb_clients AS c ON c.client_id = i.supplier_id LEFT JOIN mcb_category AS cat ON i.category_id = cat.category_id
                WHERE  i.inventory_id > 0 AND {$i_type} 
                {$is_archived} i.inevntory_last_changed > '" . $lastchangedStr . "'" . $supplierCond . " ORDER BY " . $sort_by . " " . $sort_order;
        $q = $this->db->query($sql);

        $i_a = array();
        $max_rel_inv_count = 0;
        foreach ($q->result() as $inventory) {
            if ($inventory->inventory_type == '1') {
                $inv_id = $inventory->inventory_id;
                $i_a[] = count((array) $pis[$inv_id]);
            }
        }

        $max_rel_inv_count = !empty($i_a) ? max($i_a) : 0;

        $heading = array(
            'inventory_type',
            'sku',
            'supplier_name',
            'name',
            'description',
            'supplier_code',
            'supplier_description',
            'supplier_price',
            'base_price',
            'qty',
            'qty_on_order',
            'location',
            'category_name',
            'hide_in_quote'
        );

        if ($max_rel_inv_count > 0) {
            for ($index = 1; $index <= $max_rel_inv_count; $index++) {
                $rel_key = "related_inv_" . $index;
                $heading[] = $rel_key;
            }
        }

        $invs = array();
        foreach ($q->result() as $inventory) {
            $inv_id = $inventory->inventory_id;
            if($inventory->quote_status == '1') {
                $inventory->quote_status = 'Yes';
            } else {
                $inventory->quote_status = 'No';
            }
            if ($max_rel_inv_count > 0) {
                for ($index = 1; $index <= $max_rel_inv_count; $index++) {

                    $is_key = ($index - 1);
                    $rel_key = "related_inv_" . $index;
                    $inventory->$rel_key = '';
                    if ($inventory->inventory_type == '1') {
                        if (isset($pis[$inv_id][$is_key])) {
                            $inventory->$rel_key = $pis[$inv_id][$is_key];
                        }
                    }
                }
            }
            $invs[] = $inventory;
        }
//        echo '<pre>';
//        print_r($invs);
//        echo '</pre>';
//        die;
//
        //deleting inventories
        if ($this->input->post('delete_inventories') == '1') {
            $suppliers = $this->input->post('supplier_list');
            if(sizeof($suppliers) > 0){
                foreach($suppliers as $supplier){
                    
                    // deleting from mcb_inventory_item
                    $this->db->query("delete from mcb_inventory_item where supplier_id = '".$supplier."'");
                }
            }
        }
        // end deleting inventories


        $delimiter = ',';
        $enclosure = '"';
        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename="' . $download_file_name . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        $file = fopen('php://output', 'w');
        fputcsv($file, $heading, $delimiter, $enclosure);
        foreach ($invs as $inventory) {
            $line = array(
                prepare_csv_export_string($inventory->i_type),
                prepare_csv_export_string($inventory->sku),
                prepare_csv_export_string($inventory->supplier_name),
                prepare_csv_export_string($inventory->name),
                prepare_csv_export_string(str_replace("\n", " ", $inventory->description)),
                prepare_csv_export_string($inventory->supplier_code),
                prepare_csv_export_string($inventory->supplier_description),
                $inventory->supplier_price,
                $inventory->base_price,
                $inventory->qty,
                '',
                prepare_csv_export_string($inventory->location),
                prepare_csv_export_string($inventory->category_name),
                prepare_csv_export_string($inventory->quote_status),
            );

            if ($max_rel_inv_count > 0) {
                for ($index = 1; $index <= $max_rel_inv_count; $index++) {
                    $rel_key = "related_inv_" . $index;
                    if ($inventory->inventory_type == '1') {
                        $line[] = prepare_csv_export_string($inventory->$rel_key);
                    }
                }
            }
            fputcsv($file, $line, $delimiter, $enclosure);
        }
        exit();
    }

    /**
     * gets history of import
     * @return type
     */
    public function getImportHistory() {
        $this->db->where('active_status', '1');
        $this->db->order_by('id', 'DESC');
        $q = $this->db->get('mcb_inventory_import_history');
        return $q->result();
    }

    public function dorollback($historyid) {
        $this->db->where('id', $historyid);
        $this->db->where('active_status', '1');
        $q = $this->db->get('mcb_inventory_import_history');
        $res = $q->row();
        if ($q->num_rows() > 0) {
            $res1 = $this->rollbackInventories($res->imported_inventories_ids, $historyid);
            $res2 = $this->rollbackSuppliers($res->imported_supplier_ids);
            $res3 = $this->rollbackUpdatedInventories($res->updated_inventories_ids, $historyid);
            if ($res1 && $res2 && $res3) {
                return $this->deactiveHistory($historyid);
            }
        }
        return FALSE;
    }

    private function rollbackUpdatedInventories($updated_inventory_ids, $historyid) {
        if ($updated_inventory_ids != '') {
            $updated_inventory_ids = explode(',', $updated_inventory_ids);
            if (sizeof($updated_inventory_ids) > 0) {
                foreach ($updated_inventory_ids as $u_prd_id) {
                    $this->revertUpdateInventoryDetail($u_prd_id, $historyid);
                    $this->rollBackInventoryHistory($u_prd_id, $historyid);
                }
            }
        }

        return TRUE;
    }

    private function revertUpdateInventoryDetail($inv_id, $historyid) {
        //get detail from history backup
        $inventory = $this->inventoryDetailAtHistoryPoint($inv_id, $historyid);
        if ($inventory) {
            $this->db->where('inventory_id', $inv_id);
            $data = array(
                'supplier_id' => $inventory->supplier_id,
                'name' => $inventory->name,
                'description' => $inventory->description,
                'base_price' => $inventory->base_price,
                'qty' => $inventory->qty,
                'supplier_code' => $inventory->supplier_code,
                'supplier_description' => $inventory->supplier_description,
                'supplier_price' => $inventory->supplier_price,
                'location' => $inventory->location,
                'inevntory_last_changed' => date('Y-m-d H:i:s'),
            );

            return $this->db->update('mcb_inventory_item', $data);
        }
        return FALSE;
    }

    private function inventoryDetailAtHistoryPoint($inv_id, $historyid) {
        $where = array(
            'inventory_history_id' => $historyid,
            'inventory_id' => $inv_id
        );
        $this->db->where($where);
        $q = $this->db->get('mcb_inventory_update_history_backup');
        return $q->row();
    }

    private function deactiveHistory($historyid) {
        $this->db->where('id', $historyid);
        $data = array(
            'active_status' => '0'
        );
        return $this->db->update('mcb_inventory_import_history', $data);
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

    private function rollbackInventories($productItemids, $historyid = '') {
        if ($productItemids != '') {
            $productItemids = explode(',', $productItemids);
            if (sizeof($productItemids) > 0) {
                foreach ($productItemids as $pid) {
                    if ($pid != '') {
                        $this->deActivateInventory($pid);
                        //need to remove the history as well, otherwise confusing
                        $this->rollBackInventoryHistory($pid, $historyid);
                    }
                }
            }
        }
        return TRUE;
    }

    private function rollBackInventoryHistory($inventory_id, $historyid) {
        if ($historyid != '' && $historyid != 0) {
            $where = array(
                'history_id' => $historyid,
                'inventory_id' => $inventory_id
            );
            $this->db->where($where);
            return $this->db->delete('mcb_inventory_history');
        }
    }

    private function deActivateInventory($pid) {
        $this->db->where('inventory_id', $pid);
        return $this->db->delete('mcb_inventory_item');
    }

    public function getSuppliers() {
        $where = array(
            'client_active' => '1',
            'client_is_supplier' => '1'
        );
        $this->db->where($where);
        $this->db->order_by('client_name', 'ASC');
        $q = $this->db->get('mcb_clients');
        return $q->result();
    }

    public function downloadSampleInventoryImportFile() {

        $download_file_name = 'sample_import_inventory.csv';
        $examples = array(
            array(
                'Test Supplier', 'Inventory Item #1', 'Inventory Description', '300', '100', 'ITM1', 'Supplier Description', '100', 'Warehouse Location Name',
//                'Product Name(1)'
            ),
            array(
                'Test Supplier', 'Inventory Item #2', 'Inventory Description', '300', '100', 'ITM1', 'Supplier Description', '150', 'Warehouse Location Name',
//                'Product Name(2)'
            ),
            array(
                'Test Supplier', 'Inventory Item #3', 'Supplier Description', 'Inventory Description', '400', '100', 'ITM1', '100', 'Warehouse Location Name',
//                'Product Name(3)'
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
            'qty',
            'qty_on_order',
            'warehouse_location',
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

    public function downloadSampleGroupedInventoryImportFile() {

        $download_file_name = 'sample_grouped_import_inventory.csv';
        $examples = array(
            array(
                'Test Supplier', 'Inventory Item #1', 'Inventory Description', '300', 'Warehouse Location Name',
//                'Product Name(1)'
            ),
            array(
                'Test Supplier', 'Inventory Item #2', 'Inventory Description', '300', 'Warehouse Location Name',
//                'Product Name(2)'
            ),
            array(
                'Test Supplier', 'Inventory Item #3', 'Inventory Description', '400', 'Warehouse Location Name',
//                'Product Name(3)'
            ),
        );
        $heading = array(
            'supplier_name',
            'name',
            'description',
            'base_price',
            'location',
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
            );

            fputcsv($file, $line, $delimiter, $enclosure);
        }
        exit();
    }

    function delete_override_inventory($source_file1) {
        $row = 1;
        $error_str = '';
        $num = 0;
        $csv_inventory_part = '';
        $fileFormate = FALSE;

        if (($handle = fopen($source_file1, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
                $temp = array();
                foreach ($data as $d) {
                    $val = str_replace("\0", "", $d);
                    $temp[] = $val;
                }
                $data = $temp;
                $num = count($data);
                if (($row == 1)) {
                    if ($num >= 8) {
                        if ($data[0] == 'inventory_type' && $data[1] == 'supplier_name' && $data[2] == 'name' && $data[3] == 'description' && $data[4] == 'supplier_code' && $data[5] == 'supplier_description' && $data[6] == 'supplier_price' && $data[7] == 'base_price' && ($data[8] == 'qty' || $data[9] == 'qty_on_order') && $data[10] == 'location') {
                            $fileFormate = TRUE;
                        } else {
                            $$error_str .= 'The CSV file is not of valid format. Please see an example export for the right format.';
                            $fileFormate = FALSE;
                        }
                    }
                }
                $row++;
            }
            fclose($handle);
        }

        if (($fileFormate == TRUE) && ($row > 2)) {
            $all_inventories = $this->common_model->query_as_object('SELECT name FROM mcb_inventory_item WHERE inventory_type = "0"');
            foreach ($all_inventories as $value2) {
                $this->common_model->update('mcb_inventory_item', array('is_arichved' => '1'), array('name' => $value2->name, 'inventory_type' => '0'));
            }
        } else {
            return FALSE;
        }
    }

}

?>
