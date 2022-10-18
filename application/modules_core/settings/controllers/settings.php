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
        } elseif ($this->input->post('restore_database')) {
            $this->_db_restore();
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

        // ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
        $filename = "litesource_db_backup_" . date("Y-m-d") . "-".time();
        $filename_tar = $filename.".gz";
        $mime = "application/x-gzip";
        header( "Content-Type: " . $mime );
        header('Pragma: no-cache');
        header( 'Content-Disposition: attachment; filename="' . $filename_tar . '"' );
        $cmd = "mysqldump -u {$this->db->username} --password={$this->db->password} {$this->db->database} | gzip --best";
        // echo $cmd = "mysqldump -u {$this->db->username} --password={$this->db->password} {$this->db->database} | gzip --best";
        passthru($cmd);
        exit(0);
        
        // old code
        $prefs = array(
            'format' => 'zip',
            'filename' => 'mcb_' . date('Y-m-d') . '.sql'
        );
        $this->load->library('db_backup');
        $this->db_backup->backup($prefs);
    }

    function _db_restore(){
        // ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
        try{
            ini_set('max_execution_time', 30000);
            $config = array(
                'upload_path' => './uploads/db_temp',
                'allowed_types' => '*'
            );
    
            $this->load->library('upload', $config);
    
            if (! $this->upload->do_upload()) {
                $this->session->set_flashdata('custom_error', $this->upload->display_errors());
                redirect('settings');
            } else {
                $upload_data = $this->upload->data();
                $qry_file = substr($upload_data['file_name'], 0 , (strrpos($upload_data['file_name'], ".")));
                $file_path = $upload_data['file_path'];
                 echo $cmd2 = "cd {$file_path} && gzip -d ".$upload_data['file_name']." && 
                                sed -i 's/utf8mb4_0900_ai_ci/utf8_general_ci/g' {$qry_file} && 
                                sed -i 's/CHARSET=utf8mb4/CHARSET=utf8/g' {$qry_file} && 
                                mysql -u {$this->db->username} --password={$this->db->password} {$this->db->database} < ".$file_path.$qry_file." && 
                                rm {$qry_file}";
                
                exec($cmd2);
                // dd($cmd2);
                $this->session->set_flashdata('custom_success', "Successfully imported database: ".$upload_data['file_name']);
                redirect('settings');
            }
            $this->session->set_flashdata('custom_error', "Something is wrong. Try after some time");
            redirect('settings');
        }catch(Exception $ex){
            $this->session->set_flashdata('custom_error', $ex->getMessage());
            redirect('settings');
        }
        
    }
}

?>