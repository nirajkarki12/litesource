<?php

$config = array(
    'mcb_menu' => array(
        'quotes' => array(
            'title' => 'quotes',
            'href' => 'quotes/index',
        ),
        'orders' => array(
            'title' => 'orders',
            'href' => 'orders/index',
        ),
        'invoices' => array(
            'title' => 'invoices',
            'href' => 'invoices/index',
        ),
        'inventory' => array(
            'title' => 'inventory',
            'href' => 'inventory/index',
            'submenu' => array(
                'import_rollback' => array(
                    'title' => 'inventory_import_history',
                    'href' => 'inventory/rollback',
                    //'global_admin' => TRUE,
                ),
                'link_to_product' => array(
                    'title' => 'link_to_product',
                    'href' => 'inventory/link_to_product',
                    //'global_admin' => TRUE,
                ),
                'add_category' => array(
                    'title' => 'category_label',
                    'href' => 'inventory/add_category',
                        //'global_admin' => TRUE,
                ),
//                'one_to_one_product_inv' => array(
//                    'title' => 'one_to_one_product_inv',
//                    'href' => 'inventory/one_to_one_product_inv',
//                    //'global_admin' => TRUE,
//                ),
            )
        ),
        /*'products' => array(
            'title' => 'products',
            'href' => 'products',
            'submenu' => array(
                'import_rollback' => array(
                    'title' => 'product_import_history',
                    'href' => 'products/rollback',
                    //'global_admin' => TRUE,
                ),
            )
        ),*/
        'clients' => array(
            'title' => 'clients',
            'href' => 'clients/index',
        ),
        'projects' => array(
            'title' => 'projects',
            'href' => 'projects/index',
        ),
        /*
          'payments'  =>  array(
          'title'     =>  'payments',
          'href'      =>  'payments/index',
          'submenu'   =>  array(
          'payments/index'    =>  array(
          'title' =>  'view_payments',
          'href'  =>  'payments/index'
          ),
          'payments/form' =>  array(
          'title' =>  'enter_payment',
          'href'  =>  'payments/form'
          ),
          'payments/payment_methods'  =>  array(
          'title'         =>  'payment_methods',
          'href'          =>  'payments/payment_methods',
          'global_admin'  =>  TRUE
          ),
          'templates/index/type/payment_receipts' =>  array(
          'title'         =>  'receipt_templates',
          'href'          =>  'templates/index/type/payment_receipts',
          'global_admin'  =>  TRUE
          )
          )
          ),
         */
        'system' => array(
            'title' => 'system',
            'href' => 'settings',
            'global_admin' => TRUE,
            'submenu' => array(
                'settings' => array(
                    'title' => 'system_settings',
                    'href' => 'settings/index',
                    'global_admin' => TRUE,
                ),
                'users' => array(
                    'title' => 'user_accounts',
                    'href' => 'users/index',
                    'global_admin' => TRUE,
                ),
                'tax_rates' => array(
                    'title' => 'tax_rates',
                    'href' => 'tax_rates/index'
                ),
                'client_groups' => array(
                    'title' => 'client_groups',
                    'href' => 'client_groups/index'
                ),
                'invoice_statuses' => array(
                    'title' => 'invoice_statuses',
                    'href' => 'invoice_statuses/index',
                    'global_admin' => TRUE,
                ),
                'invoices/invoice_groups' => array(
                    'title' => 'invoice_groups',
                    'href' => 'invoices/invoice_groups'
                ),
                'mcb_modules' => array(
                    'title' => 'modules',
                    'href' => 'mcb_modules/index',
                    'global_admin' => TRUE,
                )
            )
        ),
        array(
            'title' => 'log_out',
            'href' => 'sessions/logout'
        )
    )
);
?>
