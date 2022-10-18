<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

function span_name_length_value($name, $length, $value) {
    if (($name != '') && ($length > 0)) {
        $w = explode('<span>', $name);
        $f = $w[0];
        $w2 = explode('</span>', $w[1]);
        $l = $w2[1];
        if (($l != '') || ($l != NULL)) {
            
            
            if($length == '-1'){
                $name = $f . '-Per-Metre' . $l;
            }else{
                $name = $f . '<span>' . ($length) * ($value) . '</span>' . $l;
            }
            
            
        }
    }
    return $name;
}

function mm_to_span($name, $length, $value) {
    if (($length > 0 || $length == '-1') && ( strpos($name, '{mm}') !== FALSE )) {
        if($length == '-1'){
            $name = str_replace('{mm}', '-Per-Metre', $name);
        }else{
            $name = str_replace('{mm}', '-<span>' . ($length) * ($value) . '</span>mm', $name);
        }
    }
    return $name;
}


function span_to_mm($name, $length = 1, $value = 1000) {
    //echo trim($name);
    if (($name != '') && ((float) $length > 0 || $length == '-1')) {
        if (( strpos($name, '-Per-Metre') !== FALSE)) {
            $name = str_replace('-Per-Metre', '{mm}', $name);
        }elseif (( strpos($name, '{mm}') == FALSE)) {
            $name = str_replace('</span>mm', '</span>', $name);
        }
        
        if (( strpos($name, '-<span>') !== FALSE)) {
            $w = explode('-<span>', $name);
            $f = $w[0];
            $w2 = explode('</span>', $w[1]);
            $l = $w2[1];
            if (($l != '') || ($l != NULL)) {
                $name = $f . '{mm}'.$l;
            }else{
                $name = $f . '{mm}';
            }
        }
    }
    //echo trim($name);
    return trim($name);
}

function reverse_mm($name) {
    if (( strpos($name, 'mm') !== FALSE)) {
        $w = explode('mm', $name);
        if (strlen($w[0]) > 0) {
            $num = str_split($w[0]);
            
            for ($i = (sizeof($num) - 1); $i > 0; $i--) {
                $char = $num[$i];
                
                if(is_numeric($char)){
                    unset($num[$i]);
                }else{
                    break;
                }
            }
            
            
            if($num[sizeof($num) - 1] == '-'){
                $num[sizeof($num) - 1] = '{mm}';
            }
            //collect back
            $part1 = implode("",$num);
            //$part1 = str_replace('mm', '{mm}', $part1);
            $name = $part1.$w[1];
        }
    }

    return $name;
}

function remove_span($item_name) {

    return str_replace(array("<span>", "</span>", "<", "/span>"), "", $item_name);
}

function array_to_groupby($key, $data) {
    $array =  (array)$data;
    $temp_array = array(); 
    $i = 0; 
    $key_array = array(); 
    foreach($array as $val) { 
        $val = (array)$val;
        if (!in_array($val[$key], $key_array)) { 
            $key_array[$i] = $val[$key]; 
            $temp_array[$i] = $val; 
        } 
        $i++; 
    } 
    return $temp_array;
}


function docket_due_days($docket) {
    
    $ret_res = '';
    $docket_date = $docket->docket_date_entered;
    $due_date = $docket->docket_due_date;
    $current_date = time();
    $current_date=strtotime(date("Y-m-d",($current_date)));
    if (($docket->paid_status) == '1') {
        $ret_res = '-';
    } else {
            $due_date=date("Y-m-t",($due_date));
            $due_date=strtotime($due_date);
        if ((round(($due_date- $current_date) / (60 * 60 * 24))) >= 1) {
            $ret_res = round(($due_date - $current_date) / (60 * 60 * 24)).' days';
        } elseif (round(($current_date - $due_date) / (60 * 60 * 24)) >= 1) {
            
            if (($invoice->smart_status == '1') && ($invoice->invoice_status == 'Closed')) {
                $fc = '<br>(Force Closed)';
            } elseif($docket->invoice_sent != '1') {
                $fc = '';
            }else{
                $fc = '.';
            }

            if($fc == ''){
                $ret_res = '--';
            }else{
                $reday = round(($current_date - $due_date) / (60 * 60 * 24))." days{$fc}";
                $ret_res =  '<p style="color:red;margin: 0px;">Overdue: ' . $reday. '</p>';
            }
        } else {
            $ret_res = '<p style="color:red;">Last day</p>';
        }
    }
    return $ret_res;
}

function docket_owing_with_currency($docket) {
    
    $amount = '0.00';
    if( $docket->with_tax_owing_amount != '' ){ 
        if ($docket->with_tax_owing_amount < 0) {
            $amount = '-' . display_currency(trim($docket->with_tax_owing_amount, '-'));
        } else { 
            $amount = display_currency($docket->with_tax_owing_amount);
        } 
    }else{ 
        if ($docket->price_with_tax < 0) { 
            $amount = '-' . display_currency(trim($docket->price_with_tax, '-'));
        } else { 
            $amount = display_currency($docket->price_with_tax);
        }
    }
    return $amount;
}

function docket_owing_with_out_currency($docket) {
    $amount = '0.00';
    if( $docket->with_tax_owing_amount != '' ){ 
        if ($docket->with_tax_owing_amount < 0) {
            $amount = '-' . (trim($docket->with_tax_owing_amount, '-'));
        } else { 
            $amount = ($docket->with_tax_owing_amount);
        } 
    }else{ 
        if ($docket->price_with_tax < 0) { 
            $amount = '-' . (trim($docket->price_with_tax, '-'));
        } else { 
            $amount = ($docket->price_with_tax);
        }
    }
    return number_format($amount, 2, '.', '');
}

function tot_invc_owing_amnt_wit_curr($invoice, $docket_payment_amount) {
    
    $docket_payment_amount = (round($docket_payment_amount, 2));
    $invoice_amnt = (round($invoice->invoice_total, 2));
    $invc_o_amnt = $invoice_amnt-$docket_payment_amount;
    if($invc_o_amnt<0){
        $invc_o_amnt = '-' . display_currency(trim($invc_o_amnt, '-'));
    } else {
        $invc_o_amnt = display_currency($invc_o_amnt);
    }
    return $invc_o_amnt;
}

