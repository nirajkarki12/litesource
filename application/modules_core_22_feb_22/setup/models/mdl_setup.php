<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class Mdl_Setup extends MY_Model {

    public $install_version = '0.9.9.5';

    var $upgrade_path;

    function __construct() {

        parent::__construct();

        $this->table_name = 'mcb_users';

        $this->primary_key = 'mcb_users.user_id';

        $this->upgrade_path = array(
            array(
                'from'		=>	'0.8',
                'to'		=>	'0.8.1',
                'function'	=>	'u081'
            ),
            array(
                'from'		=>	'0.8.1',
                'to'		=>	'0.8.2',
                'function'	=>	'u082'
            ),
            array(
                'from'		=>	'0.8.2',
                'to'		=>	'0.8.3',
                'function'	=>	'u083'
            ),
            array(
                'from'		=>	'0.8.3',
                'to'		=>	'0.8.4',
                'function'	=>	'u084'
            ),
            array(
                'from'		=>	'0.8.4',
                'to'		=>	'0.8.5',
                'function'	=>	'u085'
            ),
            array(
                'from'		=>	'0.8.5',
                'to'		=>	'0.8.6',
                'function'	=>	'u086'
            ),
            array(
                'from'		=>	'0.8.6',
                'to'		=>	'0.8.7',
                'function'	=>	'u087'
            ),
            array(
                'from'		=>	'0.8.7',
                'to'		=>	'0.8.8',
                'function'	=>	'u088'
            ),
            array(
                'from'		=>	'0.8.8',
                'to'		=>	'0.8.9',
                'function'	=>	'u089'
            ),
            array(
                'from'		=>	'0.8.9',
                'to'		=>	'0.8.9.1',
                'function'	=>	'u0891'
            ),
            array(
                'from'		=>	'0.8.9.1',
                'to'		=>	'0.9.0',
                'function'	=>	'u090'
            ),
            array(
                'from'		=>	'0.9.0',
                'to'		=>	'0.9.2',
                'function'	=>	'u092'
            ),
            array(
                'from'      =>  '0.9.2',
                'to'        =>  '0.9.2.1',
                'function'  =>  'u0921'
            ),
            array(
                'from'      =>  '0.9.2.1',
                'to'        =>  '0.9.3',
                'function'  =>  'u093'
            ),
            array(
                'from'      =>  '0.9.3',
                'to'        =>  '0.9.3.1',
                'function'  =>  'u0931'
            ),
            array(
                'from'      =>  '0.9.3.1',
                'to'        =>  '0.9.3.2',
                'function'  =>  'u0932'
            ),
            array(
                'from'      =>  '0.9.3.2',
                'to'        =>  '0.9.3.3',
                'function'  =>  'u0933'
            ),
            array(
                'from'      =>  '0.9.3.3',
                'to'        =>  '0.9.4',
                'function'  =>  'u094'
            ),
            array(
                'from'      =>  '0.9.4',
                'to'        =>  '0.9.4.1',
                'function'  =>  'u0941'
            ),
            array(
                'from'      =>  '0.9.4.1',
                'to'        =>  '0.9.4.2',
                'function'  =>  'u0942'
            ),
            array(
                'from'      =>  '0.9.4.2',
                'to'        =>  '0.9.4.3',
                'function'  =>  'u0943'
            ),
            array(
                'from'      =>  '0.9.4.3',
                'to'        =>  '0.9.4.4',
                'function'  =>  'u0944'
            ),
            array(
                'from'      =>  '0.9.4.4',
                'to'        =>  '0.9.4.5',
                'function'  =>  'u0945'
            ),
            array(
                'from'      =>  '0.9.4.5',
                'to'        =>  '0.9.4.6',
                'function'  =>  'u0946'
            ),
            array(
                'from'      =>  '0.9.4.6',
                'to'        =>  '0.9.4.7',
                'function'  =>  'u0947'
            ),
            array(
                'from'      =>  '0.9.4.7',
                'to'        =>  '0.9.4.8',
                'function'  =>  'u0948'
            ),
            array(
                'from'      =>  '0.9.4.8',
                'to'        =>  '0.9.4.9',
                'function'  =>  'u0949'
            ),
            array(
                'from'      =>  '0.9.4.9',
                'to'        =>  '0.9.5',
                'function'  =>  'u095'
            ),
            array(
                'from'      =>  '0.9.5',
                'to'        =>  '0.9.5.1',
                'function'  =>  'u0951'
            ),
            array(
                'from'      =>  '0.9.5.1',
                'to'        =>  '0.9.5.2',
                'function'  =>  'u0952'
            ),
            array(
                'from'      =>  '0.9.5.2',
                'to'        =>  '0.9.6',
                'function'  =>  'u096'
            ),
            array(
                'from'      =>  '0.9.6',
                'to'        =>  '0.9.7',
                'function'  =>  'u097'
            ),
            array(
                'from'      =>  '0.9.7',
                'to'        =>  '0.9.8',
                'function'  =>  'u098'
            ),
            array(
                'from'      =>  '0.9.8',
                'to'        =>  '0.9.9',
                'function'  =>  'u099'
            ),
            array(
                'from'      =>  '0.9.9',
                'to'        =>  '0.9.9.1',
                'function'  =>  'u0991'
            ),
            array(
                'from'      =>  '0.9.9.1',
                'to'        =>  '0.9.9.2',
                'function'  =>  'u0992'
            ),
            array(
                'from'      =>  '0.9.9.2',
                'to'        =>  '0.9.9.3',
                'function'  =>  'u0993'
            ),
            array(
                'from'      =>  '0.9.9.3',
                'to'        =>  '0.9.9.4',
                'function'  =>  'u0994'
            ),
            array(
                'from'      =>  '0.9.9.4',
                'to'        =>  '0.9.9.5',
                'function'  =>  'u0995'
            )
        );

        $this->load->model('mcb_modules/mdl_mcb_modules');

    }

    function validate_database() {

        $this->load->library('form_validation');

        $this->form_validation->set_error_delimiters('<div class="error">', '</div>');

        $this->form_validation->set_rules('hostname', $this->lang->line('database_server'), 'required');
        $this->form_validation->set_rules('database', $this->lang->line('database_name'), 'required');
        $this->form_validation->set_rules('username', $this->lang->line('database_username'), 'required');
        $this->form_validation->set_rules('password', $this->lang->line('database_password'), 'required');

        return parent::validate();

    }

    function validate() {

        $this->load->library('form_validation');

        $this->form_validation->set_error_delimiters('<div class="error">', '</div>');

        $this->form_validation->set_rules('first_name', $this->lang->line('first_name'), 'required');
        $this->form_validation->set_rules('last_name', $this->lang->line('last_name'), 'required');
        $this->form_validation->set_rules('username', $this->lang->line('username'), 'required');
        $this->form_validation->set_rules('password', $this->lang->line('password'), 'required');
        $this->form_validation->set_rules('passwordv', $this->lang->line('password_verify'), 'required|matches[password]');
        $this->form_validation->set_rules('address', $this->lang->line('street_address'));
        $this->form_validation->set_rules('address', $this->lang->line('street_address_2'));
        $this->form_validation->set_rules('city', $this->lang->line('city'));
        $this->form_validation->set_rules('state', $this->lang->line('state'));
        $this->form_validation->set_rules('zip', $this->lang->line('zip'));
        $this->form_validation->set_rules('country', $this->lang->line('country'));
        $this->form_validation->set_rules('phone_number', $this->lang->line('phone_number'));
        $this->form_validation->set_rules('fax_number', $this->lang->line('fax_number'));
        $this->form_validation->set_rules('mobile_number', $this->lang->line('mobile_number'));
        $this->form_validation->set_rules('email_address', $this->lang->line('email_address'));
        $this->form_validation->set_rules('web_address', $this->lang->line('web_address'));
        $this->form_validation->set_rules('company_name', $this->lang->line('company_name'));

        return parent::validate();

    }

    function db_array() {

        $db_array = parent::db_array();

        unset($db_array['passwordv']);

        $db_array['password'] = md5($db_array['password']);

        $db_array['global_admin'] = 1;

        return $db_array;

    }

    function db_install() {

        $return = array();

        $this->load->database();

        $this->db->db_debug = 0;

        if ($this->db_install_tables()) {

            $return[] = $this->lang->line('install_database_success');

        }

        else {

            $return[] = $this->lang->line('install_database_problem');

            return $return;

        }

        $db_array = parent::db_array();

        $db_array['password'] = md5($db_array['password']);

        $db_array['global_admin'] = 1;

        unset($db_array['passwordv']);

        if (parent::save($db_array, NULL, FALSE)) {

            $return[] = $this->lang->line('install_admin_account_success');

        }

        else {

            $return[] = $this->lang->line('install_admin_account_problem');

            return $return;

        }

        $return[] = $this->lang->line('installation_complete');

        $return[] = $this->lang->line('install_delete_folder');

        $return[] = APPPATH . 'modules_core/setup';

        $return[] = anchor('sessions/login', $this->lang->line('log_in'));

        return $return;

    }

    function db_install_tables() {

        foreach ($this->db_tables() as $query) {

            if (!$this->db->query($query)) {

                return FALSE;

            }

        }

        $this->mcb_data_prev();

        $this->mcb_data_085();

        $this->mcb_data_086();

        $this->mcb_data_087();

        $this->mcb_data_088();

        $this->mcb_data_089();

        $this->mcb_data_090();

        $this->mcb_data_092();

        $this->mcb_data_0942();

        $this->mcb_data_0944();

        $this->mdl_mcb_data->save('version', $this->install_version);

        return TRUE;

    }

    function db_tables() {

        return array(

            "CREATE TABLE `mcb_clients` (
                `client_id` int(11) NOT NULL AUTO_INCREMENT,
                `client_name` varchar(100) NOT NULL DEFAULT '',
                `client_long_name` varchar(255) NOT NULL DEFAULT '',
                `client_address` varchar(100) NOT NULL DEFAULT '',
                `client_address_2` varchar(100) NOT NULL DEFAULT '',
                `client_city` varchar(50) NOT NULL DEFAULT '',
                `client_state` varchar(50) NOT NULL DEFAULT '',
                `client_zip` varchar(10) NOT NULL DEFAULT '',
                `client_country` varchar(50) NOT NULL DEFAULT '',
                `client_phone_number` varchar(25) NOT NULL DEFAULT '',
                `client_fax_number` varchar(25) NOT NULL DEFAULT '',
                `client_mobile_number` varchar(25) NOT NULL DEFAULT '',
                `client_email_address` varchar(100) NOT NULL DEFAULT '',
                `client_web_address` varchar(255) NOT NULL DEFAULT '',
                `client_notes` longtext NULL DEFAULT NULL,
                `client_group_id` int(11) NOT NULL DEFAULT '0',
                `client_tax_id` varchar(25) NOT NULL DEFAULT '',
                `client_active` int(1) NOT NULL DEFAULT '1',
                `client_currency_id` INT(11) NULL COMMENT 'Currency client uses for buying/selling',
                `client_tax_rate_id` INT(11) NOT NULL DEFAULT '0',
                `client_is_supplier` int(1) NOT NULL DEFAULT '1' COMMENT '1 if client is only a supplier',
                `parent_client_id` INT(11) NULL COMMENT 'Allow for supplier product rebrand under parent name e.g. VueLite'
                PRIMARY KEY (`client_id`),
                UNIQUE KEY `uk_client_name` (`client_name`),
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

        "CREATE TABLE `mcb_client_data` (
            `mcb_client_data_id` int(11) NOT NULL AUTO_INCREMENT,
            `client_id` int(11) NOT NULL,
            `mcb_client_key` varchar(50) NOT NULL DEFAULT '',
            `mcb_client_value` varchar(100) NOT NULL DEFAULT '',
            PRIMARY KEY (`mcb_client_data_id`),
            KEY `client_id` (`client_id`),
            KEY `mcb_client_key` (`mcb_client_key`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

        "CREATE TABLE `mcb_client_groups` (
            `client_group_id` int(11) NOT NULL AUTO_INCREMENT,
            `client_group_name` varchar(255) NOT NULL DEFAULT '',
            `client_group_discount_percent` decimal(5,2) NOT NULL DEFAULT 0,
            PRIMARY KEY (`client_group_id`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;",

        "CREATE TABLE `mcb_contacts` (
            `contact_id` int(11) NOT NULL AUTO_INCREMENT,
            `client_id` int(11) NOT NULL,
            `contact_name` VARCHAR(100) NOT NULL DEFAULT '',
            `contact_active` int(1) NOT NULL DEFAULT '1',
            `first_name` varchar(50) NOT NULL DEFAULT '',
            `last_name` varchar(50) NOT NULL DEFAULT '',
            `address` varchar(100) NOT NULL DEFAULT '',
            `address_2` varchar(100) NOT NULL DEFAULT '',
            `city` varchar(50) NOT NULL DEFAULT '',
            `state` varchar(50) NOT NULL DEFAULT '',
            `zip` varchar(10) NOT NULL DEFAULT '',
            `country` varchar(50) NOT NULL DEFAULT '',
            `phone_number` varchar(25) NOT NULL DEFAULT '',
            `fax_number` varchar(25) NOT NULL DEFAULT '',
            `mobile_number` varchar(25) NOT NULL DEFAULT '',
            `email_address` varchar(100) NOT NULL DEFAULT '',
            `web_address` varchar(255) NOT NULL DEFAULT '',
            `notes` longtext NULL DEFAULT NULL,
            PRIMARY KEY (`contact_id`),
            KEY `client_id` (`client_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

        "CREATE TABLE `mcb_data` (
            `mcb_data_id` int(11) NOT NULL AUTO_INCREMENT,
            `mcb_key` varchar(50) NOT NULL DEFAULT '',
            `mcb_value` varchar(255) NULL DEFAULT '',
            PRIMARY KEY (`mcb_data_id`),
            UNIQUE KEY `mcb_data_key` (`mcb_key`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

        "CREATE TABLE `mcb_fields` (
            `field_id` int(11) NOT NULL AUTO_INCREMENT,
            `object_id` int(11) NOT NULL,
            `field_name` varchar(50) NOT NULL DEFAULT '',
            `field_index` int(11) NOT NULL,
            `column_name` varchar(25) NOT NULL DEFAULT '',
            PRIMARY KEY (`field_id`),
            KEY `object_id` (`object_id`),
            KEY `field_index` (`field_index`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;",

        "CREATE TABLE `mcb_invoices` (
            `invoice_id` int(11) NOT NULL AUTO_INCREMENT,
            `client_id` int(11) NOT NULL,
            `project_id` int(11) NULL COMMENT 'Project associated with the invoice - if there is one',
            `contact_id` int(11) NULL COMMENT 'Main contact at the client for this invoice/quote',
            `user_id` int(11) NOT NULL,
            `invoice_status_id` int(11) NOT NULL,
            `invoice_tax_rate_id` int(11) NOT NULL,
            `invoice_client_group_id` int(11) NOT NULL COMMENT 'Trade/Wholesale/Distributor discount used to calculate item prices',
            `invoice_date_entered` varchar(25) NOT NULL DEFAULT '',
            `invoice_number` varchar(50) NOT NULL DEFAULT '',
            `invoice_notes` longtext NULL DEFAULT NULL,
            `invoice_due_date` varchar(25) NOT NULL DEFAULT '',
            `invoice_is_quote` INT( 1 ) NOT NULL DEFAULT '0',
            `invoice_quote_id` int(11) NULL DEFAULT NULL COMMENT 'Reference to the originating quote',
            `invoice_group_id` int(11) NOT NULL,
            `invoice_client_order_number` VARCHAR(50),
            `invoice_payment_terms` VARCHAR(128),
            PRIMARY KEY (`invoice_id`),
            KEY `invoice_number` (`invoice_number`),
            KEY `client_id` (`client_id`),
            KEY `user_id` (`user_id`),
            KEY `invoice_status_id` (`invoice_status_id`),
            KEY `is_quote` (`invoice_is_quote`),
            KEY `invoice_group_id` (`invoice_group_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

        "CREATE TABLE `mcb_invoice_amounts` (
            `invoice_amount_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
            `invoice_id` INT NOT NULL ,
            `invoice_item_subtotal` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.00',
            `invoice_item_tax` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.00',
            `invoice_subtotal` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.00',
            `invoice_tax` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.00',
            `invoice_shipping` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.00',
            `invoice_discount` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.00',
            `invoice_paid` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.00',
            `invoice_total` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.00',
            `invoice_balance` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.00',
            INDEX ( `invoice_id` )
        ) ENGINE = MYISAM DEFAULT CHARSET=utf8;",

        "CREATE TABLE `mcb_invoice_groups` (
            `invoice_group_id` int(11) NOT NULL AUTO_INCREMENT,
            `invoice_group_name` varchar(50) NOT NULL DEFAULT '',
            `invoice_group_prefix` varchar(10) NOT NULL DEFAULT '',
            `invoice_group_next_id` int(11) NOT NULL,
            `invoice_group_left_pad` int(2) NOT NULL,
            `invoice_group_prefix_year` int(1) NOT NULL DEFAULT '0',
            `invoice_group_prefix_month` int(1) NOT NULL DEFAULT '0',
            PRIMARY KEY (`invoice_group_id`),
            KEY `invoice_group_next_id` (`invoice_group_next_id`),
            KEY `invoice_group_left_pad` (`invoice_group_left_pad`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;",

        "CREATE TABLE `mcb_invoice_items` (
            `invoice_item_id` int(11) NOT NULL AUTO_INCREMENT,
            `invoice_id` int(11) NOT NULL,
            `product_id` int(11) NOT NULL DEFAULT 0 COMMENT 'from mcb_products',
            `item_name` varchar(100) NOT NULL DEFAULT '',
            `item_type` varchar(32) NULL DEFAULT NULL COMMENT 'Free form entry specific per client',
            `item_description` varchar(500) NOT NULL DEFAULT '',
            `item_date` VARCHAR(14) NULL DEFAULT NULL,
            `item_qty` decimal(10,2) NOT NULL DEFAULT '0.00',
            `item_price` DECIMAL(12,4) NOT NULL DEFAULT '0.00' COMMENT 'Item price with enough precision to allow for calculations',
            `item_index` int(4) NULL COMMENT 'Allow items to be re-arranged for display',
            PRIMARY KEY (`invoice_item_id`),
            KEY `invoice_id` (`invoice_id`),
            KEY `product_id` (`product_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

        "CREATE TABLE `mcb_invoice_item_amounts` (
            `invoice_item_amount_id` int(11) NOT NULL AUTO_INCREMENT,
            `invoice_id` int(11) NOT NULL,
            `invoice_item_id` int(11) NOT NULL,
            `item_subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
            `item_tax` decimal(10,2) NOT NULL DEFAULT '0.00',
            `item_total` decimal(10,2) NOT NULL DEFAULT '0.00',
            PRIMARY KEY (`invoice_item_amount_id`),
            KEY `invoice_item_id` (`invoice_item_id`),
            KEY `invoice_id` (`invoice_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

        "CREATE TABLE `mcb_modules` (
            `module_id` int(11) NOT NULL AUTO_INCREMENT,
            `module_path` varchar(50) NOT NULL DEFAULT '',
            `module_name` varchar(50) NOT NULL DEFAULT '',
            `module_description` varchar(255) NOT NULL DEFAULT '',
            `module_enabled` int(1) NOT NULL DEFAULT '0',
            `module_author` varchar(50) NOT NULL DEFAULT '',
            `module_homepage` varchar(255) NOT NULL DEFAULT '',
            `module_version` varchar(25) NOT NULL DEFAULT '',
            `module_available_version` varchar(25) NOT NULL DEFAULT '',
            `module_config` longtext NULL DEFAULT NULL,
            `module_core` INT( 1 ) NOT NULL DEFAULT '0',
            `module_order` INT( 2 ) NOT NULL DEFAULT '99',
            PRIMARY KEY (`module_id`),
            KEY `module_order` (`module_order`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

        "CREATE TABLE `mcb_payments` (
            `payment_id` int(11) NOT NULL AUTO_INCREMENT,
            `invoice_id` int(11) NOT NULL,
            `payment_method_id` INT(11) NOT NULL,
            `payment_date` varchar(25) NOT NULL DEFAULT '',
            `payment_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
            `payment_note` longtext NULL DEFAULT NULL,
            PRIMARY KEY (`payment_id`),
            KEY `invoice_id` (`invoice_id`),
            KEY `payment_method_id` (`payment_method_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

        "CREATE TABLE `mcb_payment_methods` (
            `payment_method_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
            `payment_method` VARCHAR( 25 ) NOT NULL
        ) ENGINE = MYISAM ;",

        "INSERT INTO `mcb_payment_methods` (`payment_method`) VALUES
            ('" . $this->lang->line('cash') . "'),
            ('" . $this->lang->line('check') . "'),
            ('" . $this->lang->line('credit') . "');",

            "CREATE TABLE `mcb_tax_rates` (
                `tax_rate_id` int(11) NOT NULL AUTO_INCREMENT,
                `tax_rate_name` varchar(25) CHARACTER SET utf8 NOT NULL DEFAULT '',
                `tax_rate_percent` decimal(5,2) NOT NULL DEFAULT '0.00',
                PRIMARY KEY (`tax_rate_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

        "CREATE TABLE `mcb_users` (
            `user_id` int(11) NOT NULL AUTO_INCREMENT,
            `username` varchar(25) NOT NULL DEFAULT '',
            `password` varchar(50) NOT NULL DEFAULT '',
            `first_name` varchar(50) NOT NULL DEFAULT '',
            `last_name` varchar(50) NOT NULL DEFAULT '',
            `address` varchar(100) NOT NULL DEFAULT '',
            `address_2` varchar(100) NOT NULL DEFAULT '',
            `city` varchar(50) NOT NULL DEFAULT '',
            `state` varchar(50) NOT NULL DEFAULT '',
            `zip` varchar(10) NOT NULL DEFAULT '',
            `country` varchar(50) NOT NULL DEFAULT '',
            `phone_number` varchar(25) NOT NULL DEFAULT '',
            `fax_number` varchar(25) NOT NULL DEFAULT '',
            `mobile_number` varchar(25) NOT NULL DEFAULT '',
            `email_address` varchar(100) NOT NULL DEFAULT '',
            `web_address` varchar(255) NOT NULL DEFAULT '',
            `company_name` varchar(255) NOT NULL DEFAULT '',
            `last_login` varchar(25) NOT NULL DEFAULT '',
            `global_admin` int(1) NOT NULL DEFAULT '0',
            `tax_id_number` varchar(50) NOT NULL DEFAULT '',
            PRIMARY KEY (`user_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

        "CREATE TABLE `mcb_invoice_statuses` (
            `invoice_status_id` int(11) NOT NULL AUTO_INCREMENT,
            `invoice_status` varchar(255) NOT NULL DEFAULT '',
            `invoice_status_type` int(1) NOT NULL DEFAULT 1,
            PRIMARY KEY (`invoice_status_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

        "INSERT INTO `mcb_invoice_statuses` (`invoice_status_id`, `invoice_status`, `invoice_status_type`, `invoice_status_selectable`) VALUES
            (1, '" . $this->lang->line('open') . "', 1, 1),
            (2, '" . $this->lang->line('emailed') . "', 1, 1),
            (3, '" . $this->lang->line('closed') . "', 3, 1),
            (4, '" . $this->lang->line('overdue') . "', 1, 0);",

            "CREATE TABLE `mcb_invoice_history` (
                `invoice_history_id` int(11) NOT NULL AUTO_INCREMENT,
                `invoice_id` int(11) NOT NULL,
                `user_id` int(11) NOT NULL,
                `invoice_history_date` varchar(14) NOT NULL DEFAULT '',
                `invoice_history_data` longtext NULL DEFAULT NULL,
                PRIMARY KEY (`invoice_history_id`),
                KEY `user_id` (`user_id`),
                KEY `invoice_id` (`invoice_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

        "CREATE TABLE `mcb_tags` (
            `tag_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
            `tag` VARCHAR( 50 ) NOT NULL DEFAULT ''
        ) ENGINE = MYISAM DEFAULT CHARSET=utf8;",

        "CREATE TABLE `mcb_invoice_tags` (
            `invoice_tag_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
            `invoice_id` INT NOT NULL ,
            `tag_id` INT NOT NULL ,
            INDEX ( `invoice_id` , `tag_id` )
        ) ENGINE = MYISAM DEFAULT CHARSET=utf8;",

        "CREATE TABLE `mcb_products` (
            `product_id` int(11) NOT NULL AUTO_INCREMENT,
            `supplier_id` int(11) NOT NULL,
            `product_name` varchar(100) NOT NULL COMMENT 'Possibly product code from catalog',
            `product_description` varchar(500) NOT NULL DEFAULT '',
            `product_supplier_description` varchar(500) NOT NULL DEFAULT 'Description used on supplier orders',
            `product_base_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Price in AU dollars',
            `product_active` int(1) NOT NULL default 1 COMMENT '0 if product no longer to be sold',
            `product_sort_index` int(11) default 0 COMMENT 'Allow for custom sorting of products',
            `product_last_changed` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When product was created or last modified',
            PRIMARY KEY  (`product_id`),
            KEY `supplier_id` (`supplier_id`),
            UNIQUE KEY `uk_product_name` (`product_name`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

        "CREATE TABLE `mcb_projects` (
            `project_id` int(11) NOT NULL AUTO_INCREMENT,
            `project_name` varchar(100) NOT NULL,
            `project_specifier` varchar(100) NOT NULL DEFAULT '',
            `project_description` varchar(255) NOT NULL DEFAULT '',
            `project_active` int(1) NOT NULL default 1,
            PRIMARY KEY  (`project_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

        "CREATE TABLE `mcb_products_import` (
            `supplier_name` varchar(32) NOT NULL DEFAULT '',
            `product_name` varchar(100) NOT NULL,
            `product_description` varchar(500) NOT NULL DEFAULT '',
            `product_supplier_code` VARCHAR(100) NOT NULL DEFAULT '' COMMENT 'Suppliers Catalog #',
            `product_supplier_price` decimal(10,2) NOT NULL DEFAULT 0 COMMENT 'Price in suppliers currency',
            `product_base_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Wholesale price in AU dollars',
            `product_active` int(1) NOT NULL default 1 COMMENT '0 if product no longer to be sold',
            PRIMARY KEY  (`product_name`),
            KEY `supplier_short_name` (`supplier_short_name`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

        "CREATE TABLE `mcb_orders` (
            `order_id` int(11) NOT NULL AUTO_INCREMENT,
            `supplier_id` int(11) NOT NULL COMMENT 'Supplier that will supply the items in the order',
            `contact_id` int(11) NULL COMMENT 'Main contact at the supplier for this order',
            `invoice_id` int(11) NULL COMMENT 'Related quote - if any',
            `project_id` INT(11) NULL COMMENT 'Related project - if any',
            `order_number` varchar(50) NOT NULL DEFAULT '',
            `user_id` int(11) NOT NULL,
            `order_tax_rate_id` int(11) NOT NULL,
            `order_date_entered` varchar(25) NOT NULL DEFAULT '',
            `order_status_id` INT(11) NOT NULL,
            `order_address_id` INT(11) NOT NULL,
            `order_notes` longtext NULL DEFAULT NULL,
            PRIMARY KEY (`order_id`),
            KEY `idx_supplier_invoice_id` (`supplier_id`, `invoice_id`),
            KEY `idx_invoice_id` (`invoice_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

        "CREATE TABLE `mcb_order_items` (
            `order_item_id` int(11) NOT NULL AUTO_INCREMENT,
            `order_id` int(11) NOT NULL,
            `product_id` int(11) NOT NULL DEFAULT 0 COMMENT 'from mcb_products',
            `item_name` varchar(100) NOT NULL DEFAULT '',
            `item_type` VARCHAR(32) NULL DEFAULT '' AFTER `item_name`,
            `item_description` VARCHAR(500) NOT NULL DEFAULT '',
            `item_qty` decimal(10,2) NOT NULL DEFAULT '0.00',
            `item_supplier_price` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
            `item_index` int(4) NULL COMMENT 'Allow items to be re-arranged for display',
            PRIMARY KEY (`order_item_id`),
            KEY `idx_order_id` (`order_id`),
            KEY `idx_product_id` (`product_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

        "CREATE TABLE `mcb_currencies` (
            `currency_id` int(11) NOT NULL AUTO_INCREMENT,
            `currency_name` varchar(32) NOT NULL,
            `currency_code` char(3) NOT NULL DEFAULT '',
            `currency_symbol_left` varchar(3) NOT NULL DEFAULT '',
            `currency_symbol_right` varchar(3) NOT NULL DEFAULT '',
            PRIMARY KEY  (`currency_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

        "INSERT INTO `mcb_currencies` (`currency_name`, `currency_code`, `currency_symbol_left`, `currency_symbol_right`) VALUES
            ('AU Dollar', 'AUD', '$', ''),
            ('US Dollar', 'USD', '$', ''),
            ('UK Pound', 'GBP', '£', ''),
            ('NZ Dollar', 'NZD', '$', ''),
            ('Euro', 'EUR', '', ' €');",

            "CREATE TABLE `mcb_sequences` (
                `sequence_id` int(11) NOT NULL AUTO_INCREMENT,
                `sequence_name` varchar(50) NOT NULL DEFAULT '',
                `sequence_next_value` int(11) NOT NULL,
                PRIMARY KEY (`sequence_id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;",

        "INSERT INTO `mcb_sequences` (`sequence_name`, `sequence_next_value`) VALUES
            ('Quote/Invoice Numbers', 2000),
            ('Order Numbers', 2000),
            ('Delivery Docket Numbers', 1000);",

            "CREATE TABLE `mcb_addresses` (
                `address_id` int(11) NOT NULL AUTO_INCREMENT,
                `address_contact_name` varchar(50) NOT NULL,
                `address_street_address` varchar(100) NOT NULL DEFAULT '',
                `address_street_address_2` varchar(100) NOT NULL DEFAULT '',
                `address_city` varchar(50) NOT NULL DEFAULT '',
                `address_state` varchar(50) NOT NULL DEFAULT '',
                `address_postcode` varchar(10) NOT NULL DEFAULT '',
                `address_country` varchar(50) NOT NULL DEFAULT '',
                `address_defaultable` INT(1) NOT NULL DEFAULT 0 COMMENT 'Can be used as a default address',
                `address_active` INT(1) NOT NULL DEFAULT 1,
                PRIMARY KEY (`address_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

        "CREATE TABLE `mcb_quote_import` (
            `id` INT(11) NOT NULL,
            `quote_number` varchar(50) NOT NULL,
            `quote_date_entered` varchar(25) NOT NULL DEFAULT '',
            `project_name` varchar(100) NOT NULL DEFAULT '',
            `contact_details` varchar(100) NOT NULL DEFAULT '',
            `client_name` varchar(100) NULL,
            `contact_name` varchar(100) NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

        "CREATE TABLE `mcb_quote_items_import` (
            `id` INT(11) NOT NULL,
            `item_index` int(4) NOT NULL,
            `item_qty` decimal(10,2) NOT NULL DEFAULT '0.00',
            `item_name` varchar(100) NOT NULL,
            `item_type` varchar(32) NULL DEFAULT NULL,
            `item_description` varchar(500) NULL,
            `item_price` decimal(10,2) NOT NULL DEFAULT '0.00',
            PRIMARY KEY (`id`, `item_index`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

        "CREATE TABLE `mcb_datasheets` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `supplier_id` INT(11) NOT NULL,
            `datasheet_filename` varchar(100) NOT NULL,
            `datasheet_description` varchar(100) NOT NULL,
            `datasheet_last_changed` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_filename` (`datasheet_filename`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

        "CREATE TABLE `mcb_delivery_dockets` (
            `docket_id` INT(11) NOT NULL AUTO_INCREMENT,
            `docket_number` varchar(50) NOT NULL DEFAULT '',
            `invoice_id` INT(11) NOT NULL,
            `user_id` INT(11) NOT NULL,
            `docket_date_entered` varchar(25) NOT NULL,
            `docket_address_id` INT(11) NULL,
            PRIMARY KEY (`docket_id`),
            KEY `invoice_id` (`invoice_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

        "CREATE TABLE `mcb_delivery_docket_items` (
            `docket_item_id` int(11) NOT NULL AUTO_INCREMENT,
            `docket_id` int(11) NOT NULL,
            `invoice_item_id` int(11) NOT NULL COMMENT 'Orginal invoice item - can only change quantity delivered',
            `docket_item_qty` decimal(10,2) NOT NULL DEFAULT '0.00',
            `docket_item_index` int(4) NULL COMMENT 'Allow items to be re-arranged for display',
            PRIMARY KEY (`docket_item_id`),
            KEY `idx_docket_id` (`docket_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;",
    );

    }

    function db_upgrade() {

        $this->load->database();

        if ($this->mdl_mcb_data->get('version') >= '0.8') {

            $return = array();

            if ($this->mdl_mcb_data->get('version') <> $this->install_version) {

                foreach ($this->upgrade_path as $path) {

                    $app_version = $this->mdl_mcb_data->get('version');

                    if ($path['from'] == $app_version) {

                        if ($this->{$path['function']}()) {

                            $return[] = 'Upgrade from ' . $path['from'] . ' to ' . $path['to'] . ' successful<br />';

                        }

                        else {

                            $return[] = 'Upgrade from ' . $path['from'] . ' to ' . $path['to'] . ' FAILED. Script exiting.';

                            return $return;

                        }

                    }

                }

                $return[] = $this->lang->line('upgrade_complete');

                $return[] = $this->lang->line('install_delete_folder');

                $return[] = APPPATH . 'modules_core/setup';

                $return[] = anchor('sessions/login', $this->lang->line('log_in'));

                return $return;

            }

            else {

                $return[] = anchor('sessions/login', $this->lang->line('log_in'));

                $return[] = $this->lang->line('install_already_current');

                return $return;

            }

        }

        else {

            $return[] = 'You cannot upgrade your currently installed.  You must be on 0.8 before upgrading to this version.';

            return $return;

        }

    }

    function u081() {

        $this->mdl_mcb_data->save('version', '0.8.1');

        return TRUE;

    }

    function u082() {

        $queries = array(

            "CREATE TABLE `mcb_client_data` (
                `mcb_client_data_id` int(11) NOT NULL AUTO_INCREMENT,
                `client_id` int(11) NOT NULL,
                `mcb_client_key` varchar(50) NOT NULL DEFAULT '',
                `mcb_client_value` varchar(100) NOT NULL DEFAULT '',
                PRIMARY KEY (`mcb_client_data_id`),
                KEY `client_id` (`client_id`),
                KEY `mcb_client_key` (`mcb_client_key`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

        "ALTER TABLE `mcb_clients`
            DROP `username`,
            DROP `password`;"

        );

        if (!$this->run_queries($queries)) {

            return FALSE;

        }

        $this->mdl_mcb_data->save('version', '0.8.2');

        return TRUE;

    }

    function u083() {

        $this->mdl_mcb_data->save('version', '0.8.3');

        return TRUE;

    }

    function u084() {

        $this->mdl_mcb_data->save('version', '0.8.4');

        return TRUE;

    }

    function u085() {

        $queries = array(

            "ALTER TABLE `mcb_clients` ADD `client_active` INT( 1 ) NOT NULL DEFAULT '1'",

            "CREATE TABLE `mcb_payment_methods` (
                `payment_method_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                `payment_method` VARCHAR( 25 ) NOT NULL
            ) ENGINE = MYISAM ;",

            "INSERT INTO `mcb_payment_methods` (`payment_method`) VALUES
            ('" . $this->lang->line('cash') . "'),
            ('" . $this->lang->line('check') . "'),
            ('" . $this->lang->line('credit') . "');",

            "ALTER TABLE `mcb_payments` ADD `payment_method_id` INT NOT NULL AFTER `invoice_id` ,
            ADD INDEX ( `payment_method_id` )"

        );

        if (!$this->run_queries($queries)) {

            return FALSE;

        }

        $this->mcb_data_085();

        $this->mdl_mcb_data->save('version', '0.8.5');

        return TRUE;

    }

    function u086() {

        $queries = array(

            "ALTER TABLE `mcb_invoice_items` ADD `item_description` longtext NULL DEFAULT NULL AFTER `item_name`",

            "ALTER TABLE `mcb_invoice_stored_items` ADD `invoice_stored_description` longtext NULL DEFAULT NULL"

        );

        if (!$this->run_queries($queries)) {

            return FALSE;

        }

        $this->mcb_data_086();

        $this->mdl_mcb_data->save('version', '0.8.6');

        return TRUE;

    }

    function u087() {

        $queries = array(

            "ALTER TABLE `mcb_invoices` ADD `is_quote` INT( 1 ) NOT NULL DEFAULT '0'",

            "ALTER TABLE `mcb_invoice_amounts` ADD `invoice_shipping_amount` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.00' AFTER `invoice_taxed_amount` ,
            ADD `invoice_discount_amount` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.00' AFTER `invoice_shipping_amount`,
            ADD `invoice_grand_total_amount` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.00'"

        );

        if (!$this->run_queries($queries)) {

            return FALSE;

        }

        $this->mcb_data_087();

        $this->mdl_mcb_data->save('version', '0.8.7');

        $this->mdl_mcb_data->set_session_data();

        return TRUE;

    }

    function u088() {

        $queries = array(
            "ALTER TABLE `mcb_invoice_item_amounts` CHANGE `item_amount` `item_subtotal` DECIMAL( 10, 2 ) NOT NULL ,
            CHANGE `item_tax_amount` `item_tax` DECIMAL( 10, 2 ) NOT NULL ,
            CHANGE `item_taxed_amount` `item_total` DECIMAL( 10, 2 ) NOT NULL",

            "DROP TABLE `mcb_invoice_amounts`",

            "CREATE TABLE `mcb_invoice_amounts` (
                `invoice_amount_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                `invoice_id` INT NOT NULL ,
                `invoice_item_subtotal` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.00',
                `invoice_item_taxable` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.00',
                `invoice_item_tax` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.00',
                `invoice_subtotal` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.00',
                `invoice_tax` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.00',
                `invoice_shipping` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.00',
                `invoice_discount` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.00',
                `invoice_paid` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.00',
                `invoice_total` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.00',
                `invoice_balance` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.00',
                INDEX ( `invoice_id` )
            ) ENGINE = MYISAM ;",

            "ALTER TABLE `mcb_clients`
            CHANGE `address` `client_address` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
            CHANGE `address_2` `client_address_2` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
            CHANGE `city` `client_city` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
            CHANGE `state` `client_state` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
            CHANGE `zip` `client_zip` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
            CHANGE `country` `client_country` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
            CHANGE `phone_number` `client_phone_number` VARCHAR(25) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
            CHANGE `fax_number` `client_fax_number` VARCHAR(25) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
            CHANGE `mobile_number` `client_mobile_number` VARCHAR(25) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
            CHANGE `email_address` `client_email_address` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
            CHANGE `web_address` `client_web_address` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
            CHANGE `notes` `client_notes` LONGTEXT CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL",

            "ALTER TABLE `mcb_payments` CHANGE `amount` `payment_amount` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.00',
            CHANGE `note` `payment_note` LONGTEXT CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL",

            "ALTER TABLE `mcb_invoices` CHANGE `date_entered` `invoice_date_entered` VARCHAR( 25 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
            CHANGE `notes` `invoice_notes` LONGTEXT CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL ,
            CHANGE `due_date` `invoice_due_date` VARCHAR( 25 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
            CHANGE `is_quote` `invoice_is_quote` INT( 1 ) NOT NULL DEFAULT '0'"

        );

        if (!$this->run_queries($queries)) {

            return FALSE;

        }

        $this->load->model('invoices/mdl_invoice_amounts');

        $this->mdl_invoice_amounts->adjust();

        $this->mdl_mcb_data->save('version', '0.8.8');

        $this->mcb_data_088();

        return TRUE;

    }

    function u089() {

        $this->db->select('invoice_tax_rate_id, tax_item_option');

        $invoice_tax_rates = $this->db->get('mcb_invoice_tax_rates')->result();

        foreach ($invoice_tax_rates as $invoice_tax_rate) {

            $this->db->set('tax_rate_option', $invoice_tax_rate->tax_item_option);

            $this->db->where('invoice_tax_rate_id', $invoice_tax_rate->invoice_tax_rate_id);

            $this->db->update('mcb_invoice_tax_rates');

        }

        $queries = array(

            "ALTER TABLE `mcb_invoice_tax_rates` DROP `tax_item_option`",

            "CREATE TABLE `mcb_invoice_groups` (
                `invoice_group_id` int(11) NOT NULL AUTO_INCREMENT,
                `invoice_group_name` varchar(50) NOT NULL DEFAULT '',
                `invoice_group_prefix` varchar(10) NOT NULL DEFAULT '',
                `invoice_group_next_id` int(11) NOT NULL,
                `invoice_group_left_pad` int(2) NOT NULL,
                PRIMARY KEY (`invoice_group_id`),
                KEY `invoice_group_next_id` (`invoice_group_next_id`),
                KEY `invoice_group_left_pad` (`invoice_group_left_pad`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;",

            "ALTER TABLE `mcb_users` ADD `tax_id_number` VARCHAR( 50 ) NOT NULL DEFAULT ''",

            "ALTER TABLE `mcb_invoices` ADD `invoice_group_id` INT NOT NULL ,
            ADD INDEX ( `invoice_group_id` )"

        );

        if (!$this->run_queries($queries)) {

            return FALSE;

        }

        $this->load->model('invoices/mdl_invoice_amounts');

        $this->mdl_invoice_amounts->adjust();

        $this->mdl_mcb_data->save('version', '0.8.9');

        $this->mcb_data_089();

        return TRUE;

    }

    function u0891() {

        $queries = array(

            "ALTER TABLE `mcb_invoice_items` ADD `item_date` VARCHAR( 14 ) NOT NULL DEFAULT '' AFTER `item_description`",

            "ALTER TABLE `mcb_invoices` CHANGE `invoice_date_entered` `invoice_date_entered` VARCHAR( 14 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
            CHANGE `invoice_due_date` `invoice_due_date` VARCHAR( 14 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''",

            "ALTER TABLE `mcb_payments` CHANGE `payment_date` `payment_date` VARCHAR( 14 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''",

            "UPDATE mcb_invoice_items SET item_date = (SELECT invoice_date_entered FROM mcb_invoices WHERE mcb_invoices.invoice_id = mcb_invoice_items.invoice_id)"

        );

        if (!$this->run_queries($queries)) {

            return FALSE;

        }

        $this->mdl_mcb_data->save('version', '0.8.9.1');

        return TRUE;

    }

    function u090() {

        $queries = array(
            "CREATE TABLE `mcb_fields` (
                `field_id` int(11) NOT NULL AUTO_INCREMENT,
                `object_id` int(11) NOT NULL,
                `field_name` varchar(50) NOT NULL DEFAULT '',
                `field_index` int(11) NOT NULL,
                `column_name` varchar(25) NOT NULL DEFAULT '',
                PRIMARY KEY (`field_id`),
                KEY `object_id` (`object_id`),
                KEY `field_index` (`field_index`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;",

            "ALTER TABLE `mcb_modules` ADD `module_order` INT( 2 ) NOT NULL DEFAULT '99',
            ADD INDEX ( `module_order` )",

            "ALTER TABLE `mcb_invoice_groups` ADD `invoice_group_prefix_year` INT( 1 ) NOT NULL DEFAULT '0',
            ADD `invoice_group_prefix_month` INT( 1 ) NOT NULL DEFAULT '0'",

        );

        if (!$this->run_queries($queries)) {

            return FALSE;

        }

        if ($this->db->field_exists('invoice_stored_description', 'mcb_invoice_stored_items')) {

            $this->db->query("ALTER TABLE `mcb_invoice_stored_items` CHANGE `invoice_stored_description` `invoice_stored_item_description` LONGTEXT NOT NULL");

        }

        $this->mdl_mcb_data->save('version', '0.9.0');

        $this->mcb_data_090();

        return TRUE;

    }

    function u092() {

        $this->mdl_mcb_data->save('version', '0.9.2');

        return TRUE;

    }

    function u0921() {

        $queries = array(
            "ALTER TABLE `mcb_clients` CHANGE `client_name` `client_name` varchar(255) NOT NULL DEFAULT '' AFTER client_id",
            "ALTER TABLE `mcb_clients` CHANGE `client_country` `client_country` VARCHAR( 50 ) NOT NULL DEFAULT ''"
        );

        if (!$this->run_queries($queries)) {

            return FALSE;

        }

        $this->mdl_mcb_data->save('version', '0.9.2.1');

        return TRUE;

    }

    function u093() {

        $this->mdl_mcb_data->save('version', '0.9.3');

        return TRUE;

    }

    function u0931() {

        $this->mdl_mcb_data->save('version', '0.9.3.1');

        return TRUE;

    }

    function u0932() {

        $this->mdl_mcb_data->save('version', '0.9.3.2');

        return TRUE;

    }

    function u0933() {

        $this->mdl_mcb_data->save('version', '0.9.3.3');

        return TRUE;

    }

    function u094() {

        $this->mdl_mcb_data->save('version', '0.9.4');

        return TRUE;

    }

    function u0941() {

        $queries = array(
            "CREATE TABLE `mcb_client_groups` (
                `client_group_id` int(11) NOT NULL AUTO_INCREMENT,
                `client_group_name` varchar(255) NOT NULL DEFAULT '',
                `client_group_discount_percent` decimal(5,2) NOT NULL DEFAULT 0,
                PRIMARY KEY (`client_group_id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;",


            "ALTER TABLE `mcb_contacts` ADD `contact_name` VARCHAR(100) NOT NULL DEFAULT '' AFTER client_id",
                "ALTER TABLE `mcb_contacts` ADD `contact_active` int(1) NOT NULL DEFAULT '1' AFTER contact_name",
                "ALTER TABLE `mcb_clients` ADD `client_group_id` int(11) NOT NULL DEFAULT '0'",

                "CREATE TABLE `mcb_suppliers` (
                    `supplier_id` int(11) NOT NULL AUTO_INCREMENT,
                    `supplier_short_name` varchar(32) NOT NULL DEFAULT '',
                    `client_id` int(11) NOT NULL COMMENT 'Client link for contact/address details',
                    `supplier_description` varchar(255) NOT NULL DEFAULT '',
                    `supplier_sort_index` int(11) NOT NULL DEFAULT 0,
                    PRIMARY KEY (`supplier_id`),
                    UNIQUE KEY `uk_supplier_short_name` (`supplier_short_name`),
                    UNIQUE KEY `uk_client_id` (`client_id`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;",

            "CREATE TABLE `mcb_products` (
                `product_id` int(11) NOT NULL AUTO_INCREMENT,
                `supplier_id` int(11) NOT NULL,
                `product_name` varchar(100) NOT NULL COMMENT 'Possibly product code from catalog',
                `product_description` varchar(255) NOT NULL DEFAULT '',
                `product_base_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Price in AU dollars',
                `product_active` int(1) NOT NULL default 1 COMMENT '0 if product no longer to be sold',
                `product_sort_index` int(11) default 0 COMMENT 'Allow for custom sorting of products',
                `product_last_changed` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When product was created or last modified',
                PRIMARY KEY  (`product_id`),
                KEY `supplier_id` (`supplier_id`),
                UNIQUE KEY `uk_product_name` (`product_name`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

            "ALTER TABLE `mcb_invoices` ADD `contact_id` int(11) NULL COMMENT 'Main contact at the client for this invoice/quote' AFTER client_id ",
                "ALTER TABLE `mcb_invoices` ADD `invoice_tax_rate_id` int(11) NOT NULL DEFAULT '0' AFTER invoice_status_id ",
                "ALTER TABLE `mcb_invoices` ADD `invoice_quote_id` int(11) NULL DEFAULT NULL COMMENT 'Reference to the originating quote' AFTER invoice_is_quote ",
                "ALTER TABLE `mcb_invoices` ADD `invoice_items_sort_order` longtext NULL DEFAULT NULL COMMENT 'Comma separated list of invoice_item_id'",


                "ALTER TABLE `mcb_invoice_items` MODIFY `item_name` varchar(100) NOT NULL DEFAULT ''",
                "ALTER TABLE `mcb_invoice_items` MODIFY `item_description` varchar(255) NOT NULL DEFAULT ''",
                "ALTER TABLE `mcb_invoice_items` ADD `product_id` int(11) NULL DEFAULT '0' COMMENT 'from mcb_products' AFTER invoice_id ",
                "ALTER TABLE `mcb_invoice_items` ADD `item_type` varchar(32) COMMENT 'Free form entry specific per client' AFTER item_name ",
                "ALTER TABLE `mcb_invoice_items` DROP KEY  `tax_rate_id`",

                "ALTER TABLE `mcb_invoice_items` DROP `tax_rate_id`",
                "ALTER TABLE `mcb_invoice_items` DROP `is_taxable`",

                "ALTER TABLE `mcb_invoice_items` MODIFY `item_date` varchar(14) NULL",
                "ALTER TABLE `mcb_invoice_items` ADD `item_base_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Value from mcb_products table' AFTER item_qty",
                "ALTER TABLE `mcb_invoice_items` ADD `item_index` int(4) NULL COMMENT 'Allow items to be re-arranged for display'",

                "ALTER TABLE `mcb_invoice_amounts` DROP `invoice_item_taxable`"

            );

            if (!$this->run_queries($queries)) {

                return FALSE;

            }

            $this->mdl_mcb_data->save('version', '0.9.4.1');

            return TRUE;

    }

    function u0942() {

        $queries = array(

            "CREATE TABLE `mcb_projects` (
                `project_id` int(11) NOT NULL AUTO_INCREMENT,
                `project_name` varchar(100) NOT NULL,
                `project_description` varchar(255) NOT NULL DEFAULT '',
                `project_active` int(1) NOT NULL default 1,
                PRIMARY KEY  (`project_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

            "ALTER TABLE `mcb_invoices` ADD `project_id` int(11) NULL COMMENT 'Project associated with the invoice - if there is one' AFTER client_id",

                "DELETE FROM mcb_invoice_statuses WHERE invoice_status_type=2"



            );

            if (!$this->run_queries($queries)) {

                return FALSE;

            }

            $this->mdl_mcb_data->save('version', '0.9.4.2');

            return TRUE;

    }

    function u0943() {

        $queries = array(

            "CREATE TABLE `mcb_currencies` (
                `currency_id` int(11) NOT NULL AUTO_INCREMENT,
                `currency_name` varchar(32) NOT NULL,
                `currency_code` char(3) NOT NULL DEFAULT '',
                `currency_symbol` varchar(3) NOT NULL DEFAULT '',
                PRIMARY KEY  (`currency_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

            "INSERT INTO `mcb_currencies` (`currency_name`, `currency_code`, `currency_symbol`) VALUES
                ('AU Dollar', 'AUD', '$'),
                ('US Dollar', 'USD', '$'),
                ('UK Pound', 'GBP', '£'),
                ('NZ Dollar', 'NZD', '$'),
                ('Euro', 'EUR', '€');",

                "INSERT INTO `mcb_client_groups` (`client_group_id`, `client_group_name`, `client_group_discount_percent`) VALUES
                (1,'Trade',0.00),
                (2,'Wholesale',10.00),
                (3,'Distributor',20.00)",

                "ALTER TABLE `mcb_invoice_items` MODIFY `product_id` int(11) NOT NULL DEFAULT 0 COMMENT 'from mcb_products'",
                "CREATE INDEX `product_id` ON `mcb_invoice_items` (`product_id`)",

                "ALTER TABLE `mcb_products` MODIFY `product_description` varchar(500) NOT NULL DEFAULT ''",
                "ALTER TABLE `mcb_products` ADD `product_supplier_code` VARCHAR(100) NOT NULL DEFAULT '' COMMENT 'Suppliers Catalog #' AFTER product_description",
                "ALTER TABLE `mcb_products` ADD `product_supplier_price` decimal(10,2) NOT NULL DEFAULT 0 COMMENT 'Price in suppliers currency' AFTER product_supplier_code",
                "ALTER TABLE `mcb_products` ADD `product_last_changed` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When product was created or last modified'",

                "ALTER TABLE `mcb_clients` MODIFY `client_name` VARCHAR(32) NOT NULL",
                "CREATE UNIQUE INDEX `uk_client_name` ON `mcb_clients` (`client_name`)",
                "ALTER TABLE `mcb_clients` ADD `client_long_name` VARCHAR(255) NOT NULL DEFAULT '' AFTER client_name",
                "ALTER TABLE `mcb_clients` ADD `client_currency_id` INT(11) NULL COMMENT 'Currency client uses for buying/selling'",
                "ALTER TABLE `mcb_clients` ADD `client_is_supplier` int(1) NOT NULL DEFAULT '1' COMMENT '1 if client is only a supplier'",

                "CREATE TABLE `mcb_products_import` (
                    `supplier_name` varchar(32) NOT NULL DEFAULT '',
                    `product_name` varchar(100) NOT NULL,
                    `product_description` varchar(500) NOT NULL DEFAULT '',
                    `product_supplier_code` VARCHAR(100) NOT NULL DEFAULT '' COMMENT 'Suppliers Catalog #',
                    `product_supplier_price` decimal(10,2) NOT NULL DEFAULT 0 COMMENT 'Price in suppliers currency',
                    `product_base_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Wholesale price in AU dollars',
                    `product_active` int(1) NOT NULL default 1 COMMENT '0 if product no longer to be sold',
                    PRIMARY KEY  (`product_name`),
                    KEY `supplier_name` (`supplier_name`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8;"

        );

        if (!$this->run_queries($queries)) {

            return FALSE;

        }

        $this->mdl_mcb_data->save('version', '0.9.4.3');

        return TRUE;

    }

    function u0944() {

        $queries = array(

            "CREATE TABLE `mcb_orders` (
            `order_id` int(11) NOT NULL AUTO_INCREMENT,
            `supplier_id` int(11) NOT NULL COMMENT 'Supplier that will supply the items in the order',
            `contact_id` int(11) NULL COMMENT 'Main contact at the supplier for this order',
            `invoice_id` int(11) NULL COMMENT 'Related invoice - if any',
            `order_number` varchar(50) NOT NULL DEFAULT '',
            `user_id` int(11) NOT NULL,
            `order_tax_rate_id` int(11) NOT NULL,
            `order_date_entered` varchar(25) NOT NULL DEFAULT '',
            `order_status_id` INT(11) NOT NULL,
            `order_address_id` INT(11) NOT NULL,
            `order_notes` longtext NULL DEFAULT NULL,
            PRIMARY KEY (`order_id`),
            KEY `idx_supplier_invoice_id` (`supplier_id`, `invoice_id`),
            KEY `idx_invoice_id` (`invoice_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

                "ALTER TABLE `mcb_clients` ADD `client_tax_rate_id` INT(11) NOT NULL DEFAULT '0' AFTER `client_currency_id`",
                "ALTER TABLE `mcb_clients` ADD `supplier_local_name` varchar(50) NULL COMMENT 'Name to show on quotes/invoices for this suppliers products - e.g Many rebranded Vuelite'",

                "ALTER TABLE `mcb_invoice_items` MODIFY `item_description` varchar(500) NOT NULL DEFAULT ''",

                "CREATE TABLE `mcb_order_items` (
                    `order_item_id` int(11) NOT NULL AUTO_INCREMENT,
                    `order_id` int(11) NOT NULL,
                    `product_id` int(11) NOT NULL DEFAULT 0 COMMENT 'from mcb_products',
                    `item_name` varchar(100) NOT NULL DEFAULT '',
                    `item_description` VARCHAR(500) NOT NULL DEFAULT '',
                    `item_qty` decimal(10,2) NOT NULL DEFAULT '0.00',
                    `item_supplier_price` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
                    `item_index` int(4) NULL COMMENT 'Allow items to be re-arranged for display',
                    PRIMARY KEY (`order_item_id`),
                    KEY `idx_order_id` (`order_id`),
                    KEY `idx_product_id` (`product_id`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

                "DROP TABLE `mcb_currencies`;",

                "CREATE TABLE `mcb_currencies` (
                    `currency_id` int(11) NOT NULL AUTO_INCREMENT,
                    `currency_name` varchar(32) NOT NULL,
                    `currency_code` char(3) NOT NULL DEFAULT '',
                    `currency_symbol_left` varchar(3) NOT NULL DEFAULT '',
                    `currency_symbol_right` varchar(3) NOT NULL DEFAULT '',
                    PRIMARY KEY  (`currency_id`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

                "INSERT INTO `mcb_currencies` (`currency_name`, `currency_code`, `currency_symbol_left`, `currency_symbol_right`) VALUES
                ('AU Dollar', 'AUD', '$', ''),
                ('US Dollar', 'USD', '$', ''),
                ('UK Pound', 'GBP', '£', ''),
                ('NZ Dollar', 'NZD', '$', ''),
                ('Euro', 'EUR', '', ' €');",

                "CREATE TABLE `mcb_sequences` (
                    `sequence_id` int(11) NOT NULL AUTO_INCREMENT,
                    `sequence_name` varchar(50) NOT NULL DEFAULT '',
                    `sequence_next_value` int(11) NOT NULL,
                    PRIMARY KEY (`sequence_id`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;",

                "INSERT INTO `mcb_sequences` (`sequence_name`, `sequence_next_value`) VALUES
                ('Quote/Invoice Numbers', 2000),
                ('Order Numbers', 2000);",

                "CREATE TABLE `mcb_addresses` (
                    `address_id` int(11) NOT NULL AUTO_INCREMENT,
                    `address_contact_name` varchar(50) NOT NULL,
                    `address_street_address` varchar(100) NOT NULL DEFAULT '',
                    `address_street_address_2` varchar(100) NOT NULL DEFAULT '',
                    `address_city` varchar(50) NOT NULL DEFAULT '',
                    `address_state` varchar(50) NOT NULL DEFAULT '',
                    `address_postcode` varchar(10) NOT NULL DEFAULT '',
                    `address_country` varchar(50) NOT NULL DEFAULT '',
                    `address_defaultable` INT(1) NOT NULL DEFAULT 0 COMMENT 'Can be used as a default address',
                    `address_active` INT(1) NOT NULL DEFAULT 1,
                    PRIMARY KEY (`address_id`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8;"

        );

        if (!$this->run_queries($queries)) {

            return FALSE;

        }

        $this->mdl_mcb_data->save('version', '0.9.4.4');

        $this->mcb_data_0944();

        return TRUE;

    }


    function u0945() {

        $queries = array(

            "ALTER TABLE `mcb_order_items` ADD `item_type` VARCHAR(32) NULL DEFAULT '' AFTER `item_name`",

        );

        if (!$this->run_queries($queries)) {

            return FALSE;

        }

        $this->mdl_mcb_data->save('version', '0.9.4.5');

        //$this->mcb_data_0945();

        return TRUE;

    }

    function u0946() {

        $queries = array(

            "ALTER TABLE `mcb_invoices` ADD INDEX ( `invoice_number` )",

            "CREATE TABLE `mcb_quote_import` (
            `quote_number` varchar(50) NOT NULL,
            `quote_date_entered` varchar(25) NOT NULL DEFAULT '',
            `project_name` varchar(100) NOT NULL DEFAULT '',
            `contact_details` varchar(100) NOT NULL DEFAULT '',
            `client_name` varchar(100) NULL,
            `contact_name` varchar(100) NULL,
            PRIMARY KEY (`quote_number`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

                "CREATE TABLE `mcb_quote_items_import` (
                    `quote_number` varchar(50) NOT NULL,
                    `item_index` int(4) NOT NULL,
                    `item_qty` decimal(10,2) NOT NULL DEFAULT '0.00',
                    `item_name` varchar(100) NOT NULL,
                    `item_type` varchar(32) NULL DEFAULT NULL,
                    `item_description` varchar(500) NULL,
                    `item_price` decimal(10,2) NOT NULL DEFAULT '0.00',
                    PRIMARY KEY (`quote_number`, `item_index`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8;"

        );

        if (!$this->run_queries($queries)) {

            return FALSE;

        }

        $this->mdl_mcb_data->save('version', '0.9.4.6');

        return TRUE;


    }


    function u0947() {

        $queries = array(
            "ALTER TABLE `mcb_clients` MODIFY `client_name` varchar(100) NOT NULL;",
            "ALTER TABLE `mcb_clients` MODIFY `client_is_supplier` INT(1) NOT NULL DEFAULT 0;",

            "ALTER TABLE `mcb_quote_import` ADD `client_name` varchar(100) NULL;",
            "ALTER TABLE `mcb_quote_import` ADD `contact_name` varchar(100) NULL;",

        );

        if (!$this->run_queries($queries)) {

            return FALSE;

        }

        $this->mdl_mcb_data->save('version', '0.9.4.7');

        return TRUE;


    }

    function u0948() {

        $queries = array(
            "ALTER TABLE `mcb_clients` DROP `supplier_local_name`;",
            "ALTER TABLE `mcb_clients` ADD `parent_client_id` INT(11) NULL COMMENT 'Allow for supplier product rebrand under parent name e.g. VueLite';"

        );

        if (!$this->run_queries($queries)) {

            return FALSE;

        }

        $this->mdl_mcb_data->save('version', '0.9.4.8');

        return TRUE;


    }

    function u0949() {

         $queries = array(
            "ALTER TABLE `mcb_invoices` DROP `invoice_items_sort_order`;",
            "ALTER TABLE `mcb_invoices` ADD `invoice_client_group_id` INT(11) NOT NULL COMMENT 'Trade/Wholesale/Distributor discount used to calculate item prices' AFTER `invoice_tax_rate_id` ;",
            "ALTER TABLE `mcb_invoices` ADD `invoice_client_order_number` VARCHAR(50) NULL COMMENT '';",
            "ALTER TABLE `mcb_invoice_items` DROP item_base_price;",

        );


        if (!$this->run_queries($queries)) {

            return FALSE;

        }

        $this->mdl_mcb_data->save('version', '0.9.4.9');

        $this->mcb_data_0949();

        return TRUE;


    }


    function u095() {

         $queries = array(
            "ALTER TABLE `mcb_orders` ADD `project_id` INT(11) NOT NULL COMMENT 'Related project - if any' AFTER `invoice_id` ;",


        );


        if (!$this->run_queries($queries)) {

            return FALSE;

        }

        $this->mdl_mcb_data->save('version', '0.9.5');

        $this->mcb_data_095();

        return TRUE;


    }

    function u0951() {

        $queries = array(

            "ALTER TABLE `mcb_invoice_statuses` ADD `invoice_status_selectable` INT(1) NULL DEFAULT 1",
            "UPDATE `mcb_invoice_statuses` SET `invoice_status_selectable` = 1",
            "INSERT INTO `mcb_invoice_statuses` (`invoice_status_id`, `invoice_status`, `invoice_status_type`, `invoice_status_selectable`) VALUES
                (4, '" . $this->lang->line('overdue') . "', 1, 0);",

        );

        if (!$this->run_queries($queries)) {

            return FALSE;

        }

        $this->mdl_mcb_data->save('version', '0.9.5.1');

        //$this->mcb_data_0945();

        return TRUE;

    }

    function u0952() {

        $queries = array(

            "ALTER TABLE `mcb_products` ADD `product_supplier_description` VARCHAR(500) NOT NULL DEFAULT '' AFTER product_description",

        );

        if (!$this->run_queries($queries)) {

            return FALSE;

        }

        $this->mdl_mcb_data->save('version', '0.9.5.2');


        return TRUE;

    }

    function u096() {

        $queries = array(

            "CREATE TABLE `mcb_datasheets` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `supplier_id` INT(11) NOT NULL,
            `datasheet_filename` varchar(100) NOT NULL,
            `datasheet_description` varchar(100) NOT NULL,
            `datasheet_last_changed` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_filename` (`datasheet_filename`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

                "CREATE TABLE `mcb_delivery_dockets` (
                    `docket_id` INT(11) NOT NULL AUTO_INCREMENT,
                    `docket_number` varchar(50) NOT NULL DEFAULT '',
                    `invoice_id` INT(11) NOT NULL,
                    `user_id` INT(11) NOT NULL,
                    `docket_date_entered` varchar(25) NOT NULL,
                    `docket_address_id` INT(11) NULL,
                    PRIMARY KEY (`docket_id`),
                    KEY `invoice_id` (`invoice_id`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

                "CREATE TABLE `mcb_delivery_docket_items` (
                    `docket_item_id` int(11) NOT NULL AUTO_INCREMENT,
                    `docket_id` int(11) NOT NULL,
                    `invoice_item_id` int(11) NOT NULL COMMENT 'Orginal invoice item - can only change quantity delivered',
                    `docket_item_qty` decimal(10,2) NOT NULL DEFAULT '0.00',
                    `docket_item_index` int(4) NULL COMMENT 'Allow items to be re-arranged for display',
                    PRIMARY KEY (`docket_item_id`),
                    KEY `idx_docket_id` (`docket_id`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

                "INSERT INTO `mcb_sequences` (`sequence_id`, `sequence_name`, `sequence_next_value`) VALUES
                (3, 'Delivery Docket Numbers', 1000);",

        );

        if (!$this->run_queries($queries)) {

            return FALSE;

        }

        $this->mdl_mcb_data->save('version', '0.9.6');

        return TRUE;


    }

    function u097() {

        $queries = array(

            "ALTER TABLE `mcb_products_import` ADD `product_supplier_description` VARCHAR(500) NOT NULL DEFAULT '' AFTER product_description",

        );

        if (!$this->run_queries($queries)) {

            return FALSE;

        }

        $this->mdl_mcb_data->save('version', '0.9.7');


        return TRUE;

    }

    function u098() {

        $queries = array(

            "ALTER TABLE `mcb_invoices` ADD `invoice_payment_terms` VARCHAR(128) NULL",

        );

        if (!$this->run_queries($queries)) {

            return FALSE;

        }

        $this->mdl_mcb_data->save('version', '0.9.8');


        return TRUE;

    }

    function u099() {

        $queries = array(

            "ALTER TABLE `mcb_invoice_item_amounts` ADD `invoice_id` INT(11) NOT NULL AFTER invoice_item_amount_id;",
            "ALTER TABLE `mcb_invoice_item_amounts` ADD INDEX ( `invoice_id` );",

            "ALTER TABLE `mcb_orders` ADD `order_supplier_invoice_number` VARCHAR(50) NULL COMMENT 'Related to suppliers invoicing system'",
        );

        if (!$this->run_queries($queries)) {

            return FALSE;

        }

        $this->mdl_mcb_data->save('version', '0.9.9');
        $this->mcb_data_099();

        return TRUE;

    }

    function u0991() {

        $queries = array(

            "ALTER TABLE `mcb_projects` ADD `project_specifier` varchar(100) NOT NULL DEFAULT '' AFTER project_name;",

        );

        if (!$this->run_queries($queries)) {

            return FALSE;

        }

        $this->mdl_mcb_data->save('version', '0.9.9.1');


        return TRUE;

    }

    function u0992() {

        $queries = ["
            CREATE TABLE IF NOT EXISTS `mcb_inventory_item` (
            `inventory_id` int(11) NOT NULL AUTO_INCREMENT,
            `supplier_id` int(11) DEFAULT NULL,
            `name` varchar(255) DEFAULT NULL,
            `description` varchar(255) DEFAULT NULL,
            `base_price` varchar(255) DEFAULT NULL,
            `qty` decimal(11,2) DEFAULT NULL,
            `supplier_code` varchar(255) DEFAULT NULL,
            `supplier_description` varchar(255) DEFAULT NULL,
            `supplier_price` varchar(255) DEFAULT NULL,
            `location` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`inventory_id`),
            UNIQUE KEY `name` (`name`),
            KEY `supplier_id` (`supplier_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8"];

        if (!$this->run_queries($queries)) {

            return FALSE;

        }

        $this->mdl_mcb_data->save('version', '0.9.9.2');


        return TRUE;

    }

    function u0993() {

        $queries = ["CREATE TABLE IF NOT EXISTS `mcb_inventory_history` (
                     `inventory_id` int(11) DEFAULT NULL,
                     `history_qty` decimal(11,2) DEFAULT NULL,
                     `notes` text,
                     `user_id` int(11) DEFAULT NULL,
                     `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                     `inventory_history_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                     PRIMARY KEY (`inventory_history_id`),
                     KEY `Fk_Delete_Cascade` (`inventory_id`),
                     CONSTRAINT `Fk_Delete_Cascade` FOREIGN KEY (`inventory_id`) REFERENCES `mcb_inventory_item` (`inventory_id`) ON DELETE CASCADE
                    ) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;"];

        if (!$this->run_queries($queries)) {

            return FALSE;

        }

        $this->mdl_mcb_data->save('version', '0.9.9.3');


        return TRUE;

    }

    function u0994() {

        $queries = ["CREATE TABLE IF NOT EXISTS `mcb_inventory_import` (
                      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                      `supplier_id` int(11) DEFAULT NULL,
                      `name` varchar(255) DEFAULT NULL,
                      `description` varchar(255) DEFAULT NULL,
                      `base_price` varchar(255) DEFAULT NULL,
                      `qty` decimal(11,2) DEFAULT NULL,
                      `supplier_code` varchar(255) DEFAULT NULL,
                      `supplier_description` varchar(255) DEFAULT NULL,
                      `supplier_price` varchar(255) DEFAULT NULL,
                      `location` varchar(255) DEFAULT NULL,
                      PRIMARY KEY (`id`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"];

        if (!$this->run_queries($queries)) {

            return FALSE;

        }

        $this->mdl_mcb_data->save('version', '0.9.9.4');


        return TRUE;

    }
       function u0995() {

        $queries = ["CREATE TABLE `mcb_products_inventory` (
                    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                    `product_id` int(11) DEFAULT NULL,
                    `inventory_id` int(11) DEFAULT NULL,
                    PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;"];

        if (!$this->run_queries($queries)) {

            return FALSE;

        }

        $this->mdl_mcb_data->save('version', '0.9.9.5');


        return TRUE;

    }



    function mcb_data_prev() {

        $this->load->model('tax_rates/mdl_tax_rates');

        if (!$this->mdl_tax_rates->get()) {

            $db_array = array(
                'tax_rate_name'		=>	$this->lang->line('no_tax'),
                'tax_rate_percent'	=>	'0.00'
            );

            $this->db->insert('mcb_tax_rates', $db_array);

            $db_array = array(
                'tax_rate_name'		=>	'GST',
                'tax_rate_percent'	=>	'10.00'
            );

            $this->db->insert('mcb_tax_rates', $db_array);
        }

        $this->mdl_mcb_data->save('default_tax_rate_id', 2);
        $this->mdl_mcb_data->save('default_item_tax_rate_id', 2);
        $this->mdl_mcb_data->save('currency_symbol', '$');
        $this->mdl_mcb_data->save('dashboard_show_open_invoices', 'TRUE');
        $this->mdl_mcb_data->save('dashboard_show_closed_invoices', 'TRUE');
        $this->mdl_mcb_data->save('default_date_format', 'd/m/Y');
        $this->mdl_mcb_data->save('default_date_format_mask', '99/99/9999');
        $this->mdl_mcb_data->save('default_date_format_picker', 'dd/mm/yy');
        $this->mdl_mcb_data->save('default_invoice_template', 'default');
        $this->mdl_mcb_data->save('currency_symbol_placement', 'before');
        $this->mdl_mcb_data->save('invoices_due_after', '30');
        $this->mdl_mcb_data->save('pdf_plugin', 'dompdf');
        $this->mdl_mcb_data->save('email_protocol', 'php_mail_function');
        $this->mdl_mcb_data->save('dashboard_show_pending_invoices', 'TRUE');
        $this->mdl_mcb_data->save('default_open_status_id', 1);
        $this->mdl_mcb_data->save('default_closed_status_id', 3);
        $this->mdl_mcb_data->save('dashboard_show_open_quotes', 'TRUE');

        if (!$this->mdl_mcb_data->get('default_language')) {

            $this->mdl_mcb_data->save('default_language', 'english');

        }

        if (!$this->mdl_mcb_data->get('include_logo_on_invoice')) {

            $this->mdl_mcb_data->save('include_logo_on_invoice', 'FALSE');

        }

    }

    function mcb_data_085() {

        $this->mdl_mcb_data->save('dashboard_show_overdue_invoices', 'TRUE');

    }

    function mcb_data_086() {

        $this->mdl_mcb_data->save('decimal_taxes_num', 2);
        $this->mdl_mcb_data->save('default_receipt_template', 'default');

    }

    function mcb_data_087() {

        $this->mdl_mcb_data->save('dashboard_override', '');
        $this->mdl_mcb_data->save('decimal_symbol', '.');
        $this->mdl_mcb_data->save('thousands_separator', ',');

    }

    function mcb_data_088() {

        $this->mdl_mcb_data->save('default_quote_template', 'default_quote');

    }

    function mcb_data_089() {

        $this->mdl_mcb_data->delete('include_tax_id_invoice');
        $this->mdl_mcb_data->delete('tax_id_number');

        $this->db->query('UPDATE mcb_invoices SET invoice_number = invoice_id WHERE invoice_id > 0');

        $query = $this->db->query("SHOW TABLE STATUS LIKE 'mcb_invoices'");

        $auto_increment = $query->row()->Auto_increment;

        $db_array = array(
            'invoice_group_name'		=>	$this->lang->line('simple_increment'),
            'invoice_group_prefix'		=>	'',
            'invoice_group_next_id'		=>	$auto_increment,
            'invoice_group_left_pad'	=>	0
        );

        $this->db->insert('mcb_invoice_groups', $db_array);

    }

    function mcb_data_090() {

        $this->mdl_mcb_data->save('results_per_page', 15);
        $this->mdl_mcb_data->save('display_quantity_decimals', 1);

    }

    function mcb_data_092() {

        if (is_null($this->mdl_mcb_data->setting('default_invoice_group_id'))) {

            $this->mdl_mcb_data->save('default_invoice_group_id', 1);

        }

        if (is_null($this->mdl_mcb_data->setting('disable_invoice_audit_history'))) {

            $this->mdl_mcb_data->save('disable_invoice_audit_history', 0);

        }

        $this->mdl_mcb_modules->refresh();

    }

    function mcb_data_0942() {

        if (is_null($this->mdl_mcb_data->setting('disable_invoice_payments'))) {

            $this->mdl_mcb_data->save('disable_invoice_payments', 1);

        }


    }

    function mcb_data_0944() {

        if (is_null($this->mdl_mcb_data->setting('disable_delete_links'))) {

            $this->mdl_mcb_data->save('disable_delete_links', 1);

        }

        $this->mdl_mcb_data->save('default_invoice_template', 'default_invoice');
        $this->mdl_mcb_data->save('default_quote_template', 'default_quote');
        $this->mdl_mcb_data->save('default_order_template', 'default_order');


    }

    function mcb_data_0949() {


        $sql =
            "UPDATE mcb_invoices
               LEFT JOIN mcb_clients c ON c.client_id = mcb_invoices.client_id
                SET invoice_client_group_id = IFNULL(c.client_group_id,1)";

        $this->db->query($sql);
    }

    function mcb_data_095() {


        $sql =
            "UPDATE mcb_orders o
            JOIN mcb_invoices i ON i.invoice_id = o.invoice_id
            SET o.project_id = i.project_id";

        $this->db->query($sql);
    }

    function mcb_data_099() {


        $sql =
            "UPDATE mcb_invoice_item_amounts a
            JOIN mcb_invoice_items i ON i.invoice_item_id = a.invoice_item_id
            SET a.invoice_id = i.invoice_id";

        $this->db->query($sql);
    }

    function run_queries($queries) {

        foreach ($queries as $query) {

            if (!$this->db->query($query)) {

                return FALSE;

            }

        }

        return TRUE;

    }

}

?>
