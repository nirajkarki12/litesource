<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There area two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router what URI segments to use if those provided
| in the URL cannot be matched to a valid route.
|
*/

$route['quotes'] = "invoices/index/is_quote/1";

/*
 *  There is a quotes controller that will redirect to 'quotes'
 *  in order that this re-routing works
 *  We need the URL segment to say 'quotes' but if
 *  default_controller is invoices/index/is_quote/1 this is not the case. 
 */
$route['default_controller'] = "quotes/index";

$route['404_override'] = '';

$route['quotes/(:any)'] = 'invoices/$1/is_quote/1';
	
$route['products/upload_image/supplier_id/(:num)/product_id/(:num)'] = "products/upload_image";


/* End of file routes.php */
/* Location: ./application/config/routes.php */