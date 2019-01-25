<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');


function span_name_length_value($name, $length, $value) {
    if( ($name != '') && ($length > 0) ){
        $w = explode('<span>',$name);
        $f = $w[0];
        $w2 = explode('</span>',$w[1]);
        $l = $w2[1];
        if( ($l != '') || ($l != NULL) ){
            $name = $f.'<span>'.($length)*($value).'</span>'.$l;
        }
    }
    return $name;
}

function mm_to_span($name, $length, $value) {
    if( ($length > 0) && ( strpos($name, '{mm}') !== FALSE ) ){
        $name = str_replace('{mm}', '-<span>'.($length)*($value).'</span>mm', $name);
    }
    return $name;
}
