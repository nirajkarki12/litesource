<?php

(defined('BASEPATH')) OR exit('No direct script access allowed');

class Settings extends Admin_Controller {

    function __construct() {
        parent::__construct();
        $this->_post_handler();
    }

    function index() {

        $core_modules = $this->mdl_mcb_modules->core_modules;
        $custom_modules = $this->mdl_mcb_modules->custom_modules;
        $core_tabs = array();
        $custom_tabs = array();
        foreach ($core_modules as $core_module) {
            if (isset($core_module->module_config['settings_view']) and isset($core_module->module_config['settings_save'])) {
                $core_tabs[] = array(
                    'path' => $core_module->module_path,
                    'title' => $core_module->module_name,
                    'settings_view' => $core_module->module_config['settings_view']
                );
            }
        }
        foreach ($custom_modules as $custom_module) {
            if ($custom_module->module_enabled and isset($custom_module->module_config['settings_view']) and isset($custom_module->module_config['settings_save'])) {
                $custom_tabs[] = array(
                    'path' => $custom_module->module_path,
                    'title' => $custom_module->module_name,
                    'settings_view' => $custom_module->module_config['settings_view']
                );
            }
        }
        $term_condition = $this->mdl_mcb_data->get_row('mcb_settings', array('setting_key'=>'terms_conditions'))->setting_value;
        $bank_detail = json_decode($this->mdl_mcb_data->get_row('mcb_settings', array('setting_key'=>'banking_details'))->setting_value);
        $data = array(
            'core_tabs' => $core_tabs,
            'custom_tabs' => $custom_tabs,
            'tab_index' => 0,
            'terms_conditions'=>$term_condition,
            'banking_details'=>$bank_detail
        );
        $this->load->view('settings', $data);
    }

    function optimize_db() {
        $this->load->dbutil();
        $this->dbutil->optimize_database();
        $this->session->set_flashdata('custom_success', $this->lang->line('database_optimized'));
        redirect('settings');
    }

    function _post_handler() {
        if ($this->input->post('btn_backup')) {
            $this->_db_backup();
        } elseif ($this->input->post('btn_save_settings')) {
            $this->_core_save();
            $this->_custom_save();
            $this->_term_condition_save();
            $this->_bank_detail();
            $this->mdl_mcb_data->set_session_data();
            $this->session->set_flashdata('custom_success', $this->lang->line('system_settings_saved'));
            redirect('settings');
        }
    }
    
    function _term_condition_save() {
        $this->mdl_mcb_data->update('mcb_settings', array('setting_key'=>'terms_conditions'), array('setting_value'=>$this->input->post('terms_conditions')));
    }
    
    function _bank_detail() {
        
        $data = array(
            'company_name'=>$this->input->post('company_name'),
            'abn'=>$this->input->post('abn'),
            'bank_rsb'=>$this->input->post('bank_rsb'),
            'acct'=>$this->input->post('acct'),
            'swift_code'=>$this->input->post('swift_code'),
        );
        $this->mdl_mcb_data->update('mcb_settings', array('setting_key'=>'banking_details'), array('setting_value'=>json_encode($data)));
    }
    
    function _core_save() {
        foreach ($this->mdl_mcb_modules->core_modules as $module) {
            if (isset($module->module_config['settings_save'])) {
                modules::run($module->module_config['settings_save']);
            }
        }
    }

    function _custom_save() {
        foreach ($this->mdl_mcb_modules->custom_modules as $module) {
            if ($module->module_enabled and isset($module->module_config['settings_save'])) {
                modules::run($module->module_config['settings_save']);
            }
        }
    }    
    
    function _db_backup() {
        $prefs = array(
            'format' => 'zip',
            'filename' => 'mcb_' . date('Y-m-d') . '.sql'
        );
        $this->load->library('db_backup');
        $this->db_backup->backup($prefs);
    }
}

?>