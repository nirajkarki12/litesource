<?php

(defined('BASEPATH')) OR exit('No direct script access allowed');

class Mdl_MCB_Data extends MY_Model {
    public $settings;

    public function get($key) {
        $this->db->select('mcb_value');
        $this->db->where('mcb_key', $key);
        $query = $this->db->get('mcb_data');
        if ($query->row()) {
            return $query->row()->mcb_value;
        } else {
            return NULL;
        }
    }

    public function save($key, $value) {
        if (!is_null($this->get($key))) {
            $this->db->where('mcb_key', $key);
            $db_array = array(
                'mcb_value' => $value
            );
            $this->db->update('mcb_data', $db_array);
        } else {

            $db_array = array(
                'mcb_key' => $key,
                'mcb_value' => $value
            );

            $this->db->insert('mcb_data', $db_array);
        }
    }

    public function delete($key) {
        $this->db->where('mcb_key', $key);
        $this->db->delete('mcb_data');
    }

    public function set_session_data() {

        $mcb_data = $this->db->get('mcb_data')->result();
        if (!isset($this->settings)) {
            $this->settings = new stdClass;
        }

        foreach ($mcb_data as $data) {
            $this->settings->{$data->mcb_key} = $data->mcb_value;
        }
    }

    public function set_application_title() {
        $this->settings->application_title = $this->get('application_title');
    }

    public function setting($key) {
        return (isset($this->settings->$key)) ? $this->settings->$key : NULL;
    }

    public function get_row($table_name, $condition) {
        $this->db->where($condition);
            return $this->db->get($table_name)->row();
    }
    
    public function query($qry){
        
        $res = $this->db->query($qry);
            return $res->result();
    }
    
    public function get_where($table_name, $condition = '', $order = '', $limit = '') {
        
        if ($order != '') {
            $this->db->order_by($order);
        }
        if ($limit != '') {
            $this->db->limit($limit);
        }
        if ($condition != '') {
            $this->db->where($condition);
        }
        return $this->db->get($table_name)->result();      
    }
    
    public function insert($tableName, $data) {
        $this->db->insert($tableName, $data);
        $rid = $this->db->insert_id();
        if($rid > 0){
            return $rid;
        } else {
            return FALSE;
        }
    }
    
    public function update($tbl_name, $condition, $data) {
        
        $this->db->where($condition);
        if ($this->db->update($tbl_name, $data)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
}

?>