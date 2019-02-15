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
