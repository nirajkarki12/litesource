<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class Setup extends Admin_Controller {

    function index() {

    }

    function install() {

        $this->db->query(
            "CREATE TABLE IF NOT EXISTS `mcb_client_center` (
			`client_center_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`client_id` INT NOT NULL ,
			`username` VARCHAR( 100 ) NOT NULL ,
			`password` VARCHAR( 50 ) NOT NULL ,
			`last_login` VARCHAR( 20 ) NOT NULL DEFAULT '',
			INDEX ( `client_id` )
			) ENGINE = MYISAM ;"
        );


    }

    function uninstall() {

        $this->db->query(
            "DROP TABLE IF EXISTS `mcb_client_center`"
        );

    }

    function upgrade() {

        switch($this->mdl_mcb_modules->custom_modules['client_center']->module_version) {
            case '0.8.7':
                $this->u088();
                $this->u0881();
                $this->u089();
                $this->u090();
                $this->u092();
                $this->u093();
                break;
            case '0.8.8':
                $this->u0881();
                $this->u089();
                $this->u090();
                $this->u092();
                $this->u093();
                break;
            case '0.8.8.1':
                $this->u089();
                $this->u090();
                $this->u092();
                $this->u093();
                break;
            case '0.8.9':
                $this->u090();
                $this->u092();
                $this->u093();
                break;
            case '0.9.1':
                $this->u092();
                $this->u093();
                break;
            case '0.9.0':
                $this->u092();
                $this->u093();
                break;
            case '0.9.2':
                $this->u093();
                break;
        }

    }

    function u088() {

        $this->set_module_version('0.8.8');

    }

    function u0881() {

        $this->db->query("ALTER TABLE `mcb_client_center` CHANGE `last_login` `last_login` VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''");

        $this->set_module_version('0.8.8.1');

    }

    function u089() {

        $this->set_module_version('0.8.9');

    }

    function u090() {

        $this->set_module_version('0.9.0');

    }

    function u092() {

        $this->set_module_version('0.9.2');

    }

    function u093() {

        $this->set_module_version('0.9.3');

    }

    function set_module_version($module_version) {

        $this->db->set('module_version', $module_version);

        $this->db->where('module_path', 'client_center');

        $this->db->update('mcb_modules');

    }

}

?>