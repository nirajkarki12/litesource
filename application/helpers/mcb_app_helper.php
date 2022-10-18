<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function application_title() {

    $CI =& get_instance();

    return ($CI->mdl_mcb_data->setting('application_title')) ? $CI->mdl_mcb_data->setting('application_title') : $CI->lang->line('myclientbase');

}

if (!function_exists('udpate_open_order_qty')) {

    function udpate_open_order_qty($inventory_id) {
        $sql = "select SUM(mcb_delivery_docket_items.docket_item_qty*mcb_products_inventory.inventory_qty) as t_qty
from mcb_delivery_docket_items left join mcb_delivery_dockets on mcb_delivery_docket_items.docket_id = mcb_delivery_dockets.docket_id 
LEFT join mcb_invoice_items on mcb_invoice_items.invoice_item_id = mcb_delivery_docket_items.invoice_item_id LEFT join 
mcb_products_inventory on mcb_products_inventory.product_id = mcb_invoice_items.product_id 
WHERE mcb_delivery_dockets.docket_delivery_status = '0' AND mcb_products_inventory.inventory_id = '".$inventory_id."'";
        $CI =& get_instance();
        $query = $CI->db->query($sql);
        $result = $query->row();
        
        $t_qty = $result->t_qty;
        if($t_qty){
            $q = "select count(*) as count from mcb_inventory_open_order_qty where inventory_id = '".$inventory_id."'";
            $res = $CI->db->query($q);
            $res = $res->row();
            if($res->count > 0){
                $sq = "update mcb_inventory_open_order_qty set qty = '".$t_qty."' where inventory_id = '".$inventory_id."'";
            }else{
                $sq = "insert into mcb_inventory_open_order_qty (qty,inventory_id) values ( '".$t_qty."', '".$inventory_id."')";
            }
            
            $CI->db->query($sq);
        }
        return TRUE;
    }

}

?>
