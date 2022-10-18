<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of common_model
 *
 * @author dines
 */
class common_model extends CI_Model{
    
    function insert($table_name, $data) {
        if ($this->db->insert($table_name, $data)) {
            return $this->db->insert_id();
        }
        return FALSE;
    }
    
    function update($table_name, $data, $condition) {
        
        $this->db->where($condition);
        if($this->db->update($table_name, $data)){
            return TRUE;
        }
        return FALSE;
    }
    
    function get_row($tbl_name, $condition) {
        $this->db->where($condition);    
        $result = $this->db->get($tbl_name);
        if ( $result !== FALSE && $result->num_rows() > 0) {
            return $result->row();
        }
        return FALSE;
    }
    
    public function get_all_as_object($table_name, $condition = '', $order = '', $limit = '') {
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
        $result = $q->result();
        if($result != NULL){
            return $result;
        }
        return FALSE;
    }
    
    function delete($table_name, $contition){
        $this -> db -> where($contition);
        if($this -> db -> delete($table_name)){
            return TRUE;
        }
        return FALSE;
    }
    
    function query_as_object($qry) {

        $q = $this->db->query($qry);
        $Res = $q->result();
        if($Res != NULL){
            return $Res;
        }
        return FALSE;
    }
    
    function query_as_array($qry) {
        $result = $this->db->query($qry);
        if ( $result !== FALSE && $result->num_rows() > 0) {
            return $result->result_array();
        }
        return FALSE;
    }
    
    public function query_as_row($qry) {
        $result = $this->db->query($qry);
        if ( $result !== FALSE && $result->num_rows() > 0) {
            return $result->row();
        }
        return FALSE;
    }
    
    function just_query($qry) {
        $this->db->query($qry);
    }
    
    function insert_test($table_name, $data) {
        if ($this->db->insert($table_name, $data)) {
            $this->db->insert_id();
            
        }
        echo $this->db->last_query();
//        return FALSE;
    }
}
