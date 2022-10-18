<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

function pdf_create($html, $filename, $stream = TRUE) {
    
    require_once(APPPATH . 'helpers/mpdf/mpdf.php');
    $CI = & get_instance();
    //class mPDF ([$mode, $format, $default_font_size, $default_font, $margin_left, $margin_right, $margin_top, $margin_bottom , 
    //$margin_header , $margin_footer, $orientation ]]]]]])
    $mpdf = new mPDF('c', 'A4-L');
    $mpdf->SetAuthor('LiTEsource and Controls');
    $mpdf->WriteHTML($html);
    if ($stream) {
        $mpdf->Output($filename, 'I');
    } else {
        $mpdf->Output('./uploads/temp/' . $filename . '.pdf', 'F');
    }
}


function pdf_create_for_quote($html, $html_terms_condition, $filename, $stream = TRUE) {

    require_once(APPPATH . 'helpers/mpdf/mpdf.php');
    $CI = & get_instance();
    
    $mpdf = new mPDF('utf-8', array(297, 209));
    //$mpdf->SetAutoFont();
    $mpdf->SetAuthor('LiTEsource and Controls');
    $mpdf->WriteHTML($html);
    $mpdf->AddPage('A4');
    $mpdf->WriteHTML($html_terms_condition);
    
//    print_r($mpdf->Output($filename, 'I'));
//    die;
    
    if ($stream) {
        $mpdf->Output($filename, 'I');
    } else {
        $mpdf->Output('./uploads/temp/' . $filename . '.pdf', 'F');
    }
}

?>
