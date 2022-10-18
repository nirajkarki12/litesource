<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

$config = array(
	'module_path'			=>	'client_center',
	'module_name'			=>	'Client Center',
	'module_description'	=>	'Allows clients to log in and view invoices.',
	'module_author'			=>	'Jesse Terry',
	'module_homepage'		=>	'http://www.myclientbase.com',
	'module_version'		=>	'0.9.3',
	'module_config'			=>	array(
		'settings_view'		=>	'client_center/admin_settings/display',
		'settings_save'		=>	'client_center/admin_settings/save'
	)
);

?>