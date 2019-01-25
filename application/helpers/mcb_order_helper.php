<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function order_pdf_script($order) {
	/*
	 * dompdf does not have access to variables in the script so
	 * we generate the script with the values ready for
	 * output on the pdf
	*/
	
		
	$header_text = '"Order: '.$order->order_number.'"';
	
	
	
	$pdf_script = '
	<script type="text/php">
	if ( isset($pdf) ) {
			
		// Open the object: all drawing commands will
		// go to the object instead of the current page
		//$header = $pdf->open_object();
		$font = Font_Metrics::get_font("verdana");;

		$w = $pdf->get_width();
		$h = $pdf->get_height();


		$margin = 0;
		$color = array(0,0,0);


		// Add a logo dimensions in pts
		$img_w = 115; 
		$img_h = 138; 
		$pdf->image(invoice_logo_file(), "png", ($w - $img_w - $margin), $margin, $img_w, $img_h);

		
		$size = 18;

		$text_height = Font_Metrics::get_font_height($font, $size);
		$text = '.$header_text.';  
		$margin = 16;
		$pdf->page_text($margin*2, $margin, $text, $font, $size, $color);

		// Close the object (stop capture)
		//$pdf->close_object();

		// Add the logo to first page. 
		//$pdf->add_object($header, "add");


		// Put line and page no on footer of every page
		$size = 8;

		$text_height = Font_Metrics::get_font_height($font, $size);

		$footer = $pdf->open_object();

		$margin = 16;

		// Draw a line along the bottom allowing room for footer text
		$y = $h - $text_height - $margin;
		$pdf->line($margin, $y, $w - $margin, $y, $color, 0.5);

		

		$text = "Page {PAGE_NUM} of {PAGE_COUNT}";  

		// align text right of page (assuming here no more than 9 pages)
		$text_width = Font_Metrics::get_text_width("Page 1 of 2 ", $font, $size);
		$pdf->page_text($w - $text_width - $margin, $y, $text, $font, $size, $color);
		
		$pdf->close_object();
		$pdf->add_object($footer, "all");
		
	}
	</script>';
	
	return $pdf_script;
	
}

function format_delivery_address($address) {
	
	$address_formatted = '';
	
	if ($address) {
		
		$address_formatted =  
		'<address class="address_info" style="font-style: normal;">'.
			$address->address_contact_name.'<br />'.
			$address->address_street_address.'<br />'.
			($address->address_street_address_2 ? $address->address_street_address_2.'<br />' : ''). 
			$address->address_city.'&nbsp;'.
			$address->address_state.'&nbsp;'.
			$address->address_postcode.'<br />'.
			($address->address_country ? $address->address_country.'<br />' : '').
		'</address>';
	}
	
	return $address_formatted; 
	

}

?>