<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

function invoice_item($item) {

    /* Invoice item name and description */
    return $item->item_description ? nl2br($item->item_name) . ' - ' . nl2br($item->item_description) : nl2br($item->item_name);
}

function invoice_item_name($item, $max_length = NULL) {

    $item_name = $item->item_name;
    $item_name = str_replace(array("<span>","</span>","<","/span>"),"",$item_name);
    if ($max_length) {
        $item_name = wordwrap($item_name, $max_length, "\n", true);
    }

    return nl2br($item_name);
}

function format_invoice_item_name($item_name, $max_length = NULL) {

    if ($max_length) {
        $item_name = wordwrap($item_name, $max_length, "\n", true);
    }

    return nl2br($item_name);
}

function invoice_item_type($item) {

    return nl2br($item->item_type);
}

function invoice_item_description($item) {

    return nl2br($item->item_description);
}

function invoice_item_has_tax($sum) {

    /* Returns true if invoice item has tax applied */
    if ($sum->tax_rate_sum > 0) {

        return TRUE;
    }

    return FALSE;
}

function invoice_item_qty($item) {

    global $CI;

    return format_qty($item->item_qty);
}

function invoice_item_tax($item) {

    /* Amount of item tax, formatted as currency */
    return display_currency($item->item_tax);
}

function invoice_item_tax_sum($sum) {

    /* Total amount of invoice item taxes, formatted as currency */
    return display_currency($sum->tax_rate_sum);
}

function invoice_item_tax_sum_name($sum) {

    /* For display purposes */
    return $sum->tax_rate_name . ' @ ' . $sum->tax_rate_percent . '%';
}

function invoice_item_total($item) {

    /* Amount of item + item tax, formatted as currency */
    return display_currency($item->item_total);
}

function invoice_item_unit_price($item) {

    /* Item price, formatted as currency */
    if ($item->item_price == 0) {
        return '';
    } else {
        
        if($item->item_price < '0'){
            return '-'.display_currency($item->item_price *(-1));
        }else{
            return display_currency($item->item_price);
        }
    }
}

function invoice_item_date($item) {

    return format_date($item->item_date);
}

function format_qty($qty) {

    if (!$qty || ($qty <= 0)) {

        return '&nbsp;';
    } else if (($qty - floor($qty)) < 0.01) {

        return number_format($qty);
    } else {

        return number_format($qty, 2);
    }

    //return ($CI->mdl_mcb_data->setting('display_quantity_decimals')) ? format_number($qty) : format_number($qty, TRUE, 0);
}

function invoice_itemlevel_subtotal($item) {

    /* Amount of item formatted as currency */
    if ($item->item_subtotal == 0) {
        return '';
    } else {
        if($item->item_subtotal < '0'){
            return '-'.display_currency($item->item_subtotal *(-1));
        } else {
            return display_currency($item->item_subtotal);
        }
    }
}

function docket_invoice_item_subtotal($docket_items, $purnum = FALSE) {
    $docket_total = 0;
    if (sizeof($docket_items) > 0) {
        foreach ($docket_items as $item) {
            $docket_total += $item->item_price * $item->docket_item_qty;
        }
    }
    if ($purnum)
        return $docket_total;

    return display_currency($docket_total);
}

function docket_invoice_itemtax($invoice, $docket_items, $purnum = FALSE) {
    $docket_total = docket_invoice_item_subtotal($docket_items, TRUE);
    if ($purnum)
        return $docket_total * $invoice->tax_rate_percent / 100;

    return display_currency($docket_total * $invoice->tax_rate_percent / 100);
}

function docket_paid_total($invoice, $docket_items, $docket_with_tax, $purnum = FALSE) {
    $docket_total = docket_invoice_item_subtotal($docket_items, TRUE);
    $tax_total = docket_invoice_itemtax($invoice, $docket_items, TRUE);
    $totaAmount = ($docket_total+$tax_total)-($docket_with_tax);
    if($totaAmount < '0'){
        return '+'.display_currency(trim($totaAmount,'-'));
    }
    if($purnum){
        return '-'.$totaAmount;
    }
    return '- '.display_currency($totaAmount);
}

function docket_paid_zero_ckeck($invoice, $docket_items, $docket_with_tax, $purnum = FALSE) {
    $docket_total = docket_invoice_item_subtotal($docket_items, TRUE);
    $tax_total = docket_invoice_itemtax($invoice, $docket_items, TRUE);
    $totaAmount = ($docket_total+$tax_total)-($docket_with_tax);
    if($totaAmount == 0){
        return TRUE;
    }
}

function docket_invoice_total($invoice, $docket_items, $purnum = FALSE) {
    $docket_total = docket_invoice_item_subtotal($docket_items, TRUE);
    $tax_total = docket_invoice_itemtax($invoice, $docket_items, TRUE);
    if($purnum)
        return $docket_total + $tax_total;
    return display_currency($docket_total + $tax_total);
}

function docket_invoice_total_amount($invoice, $docket_items, $docket_with_tax, $purnum = FALSE) {
    $docket_total = docket_invoice_item_subtotal($docket_items, TRUE);
    $tax_total = docket_invoice_itemtax($invoice, $docket_items, TRUE);
    $paidAmount = ($docket_total+$tax_total)-($docket_with_tax);
    $totaAmount = $docket_total + $tax_total;
//    if($paidAmount > 0){
        $totaAmount = $totaAmount - $paidAmount;
//    }
    if($purnum){
        return $totaAmount;
    }
    if($totaAmount < 0){
         return '-'.display_currency(trim($totaAmount, '-'));
//        return display_currency().'0';
    }else{
        return display_currency($totaAmount);
    }
    
}

?>
