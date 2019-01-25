<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$CI =& get_instance();

$CI->load->helper(array('mcb_invoice_amount', 'mcb_invoice_item', 'mcb_invoice_payment', 'mcb_numbers'));

/**
 * BEGIN COMPANY (FROM) SPECIFIC HELPERS
 */

function invoice_from_address($invoice) {

	/* Address invoice is from */
	return $invoice->from_address;

}

function invoice_from_address_2($invoice) {

	/* Address 2 invoice is from */
	return $invoice->from_address_2;

}

function invoice_from_city($invoice) {

	/* City invoice is from */
	return $invoice->from_city;

}

function invoice_from_city_state_zip($invoice) {

	/* City, state, zip invoice is from */
	return $invoice->from_city . ', ' . $invoice->from_state . ' ' . $invoice->from_zip;

}

function invoice_from_company_name($invoice) {

	/* Company name invoice is from */
	return $invoice->from_company_name;

}

function invoice_from_country($invoice) {

	/* Country invoice is from */
	return $invoice->from_country;

}

function invoice_from_email($invoice) {

	/* Email address invoice is from */
	return $invoice->from_email_address;

}

function invoice_from_name($invoice) {

	/* First + Last name invoice is from */
	return $invoice->from_first_name . ' ' . $invoice->from_last_name;

}

function invoice_from_phone_number($invoice) {

	/* Phone number invvoice is from */
	return $invoice->from_phone_number;

}

function invoice_from_mobile_number($invoice) {

	/* Phone number invvoice is from */
	return $invoice->from_mobile_number;

}

function invoice_from_state($invoice) {

	/* State invoice is from */
	return $invoice->from_state;

}

function invoice_from_web_address($invoice) {

	/* URL invoice is from */
	return $invoice->from_web_address;

}

function invoice_from_zip($invoice) {

	/* Zip code invoice is from */
	return $invoice->from_zip;

}

function invoice_from_zip_city($invoice) {

	/* Zip + City invoice is from */
	return $invoice->from_zip . ' ' . $invoice->from_city;

}

function invoice_tax_id($invoice) {

	global $CI;

	return ($invoice->from_tax_id_number) ? $address = $CI->lang->line('tax_id_number') . ": " . $invoice->from_tax_id_number : NULL;

}

/**
 * BEGIN CLIENT (TO) SPECIFIC HELPERS
 */

function invoice_to_address($invoice) {

	/* Client address */
	return $invoice->client_address;

}

function invoice_to_address_2($invoice) {

	/* Client address 2 */
	return $invoice->client_address_2;

}

function invoice_to_city($invoice) {

	/* Client city */
	return $invoice->client_city;

}

function invoice_to_city_state_zip($invoice) {

	/* Client city, state, zip */
	if ($invoice->client_city and $invoice->client_state) {

		return $invoice->client_city . ', ' . $invoice->client_state . ' ' . $invoice->client_zip;

	}

	else {

		return '';

	}

}

function invoice_to_client_name($invoice) {

	/* Client name */
	return $invoice->client_name;

}

function invoice_to_contact_name($invoice) {

	/* Contact name */
	return $invoice->contact_name;

}

function invoice_to_country($invoice) {

	/* Client country */
	return $invoice->client_country;

}

function invoice_to_email_address($invoice) {

    return $invoice->client_email_address;

}

function invoice_to_full_address($invoice) {

	global $CI;

	/* Client address, fully formatted */
	$address = $CI->lang->line('bill_to') . '<br />';

	$address .= invoice_to_client_name($invoice) . '<br />';

	if ($invoice->client_address) {

		$address .= $invoice->client_address . '<br />';

		if ($invoice->client_address_2) {

			$address .= $invoice->client_address_2 . '<br />';

		}

	}

	$address .= invoice_to_city_state_zip($invoice);

	return $address;

}

function invoice_to_mobile_number($invoice) {

	return $invoice->client_mobile_number;

}

function invoice_to_phone_number($invoice) {

	/* Client phone number */
	return $invoice->client_phone_number;

}

function invoice_to_fax_number($invoice) {

	/* Client fax number */
	return $invoice->client_fax_number;

}

function invoice_to_state($invoice) {

	/* Client state */
	return $invoice->client_state;

}

function invoice_to_zip($invoice) {

	/* Client zip code */
	return $invoice->client_zip;

}

function invoice_client_tax_id($invoice) {

	/* Tax ID of the client */
	global $CI;
	return ($invoice->client_tax_id) ? $address = $CI->lang->line('tax_id_number') . ": " . $invoice->client_tax_id : NULL;
	
}

/**
 * BEGIN INVOICE NON-AMOUNT HELPERS
 */

function invoice_id($invoice) {

	return $invoice->invoice_number;

}

function invoice_date_entered($invoice) {

	global $CI;

	/* Date the invoice was entered */
	return date($CI->mdl_mcb_data->setting('default_date_format'), $invoice->invoice_date_entered);

}

function invoice_due_date($invoice) {

	global $CI;

	/* Date the invoice is due */
	return date($CI->mdl_mcb_data->setting('default_date_format'), $invoice->invoice_due_date);

}

function invoice_payment_terms($invoice) {
    global $CI;
    $payment_terms = $invoice->invoice_payment_terms;
    if (!$payment_terms) {
        /* Date the invoice is due */
        $payment_terms = $CI->mdl_mcb_data->setting('invoices_due_after') . ' days';
        if ($invoice->invoice_due_date) {
            $payment_terms .= ' - Payment Due ' .
                date($CI->mdl_mcb_data->setting('default_date_format'), $invoice->invoice_due_date);
        }
    }
    return $payment_terms;
}


function invoice_payment_term($invoice) {
    global $CI;
    $payment_terms = $invoice->invoice_payment_terms;
    if (!$payment_terms) {
        /* Date the invoice is due */
        
        
        // - getting last day-----
        $_last_data_Formate = preg_replace('/d/', 't', $CI->mdl_mcb_data->setting('default_date_format'));
        
        $invoice_date = $invoice->invoice_date_entered;
        // date($_last_data_Formate, $invoice_date);
        
        $invoice_next_month = strtotime('+1 month', ($invoice_date));
        
        $date_1 = date_create(date('Y-m-d',$invoice->invoice_due_date));
        $date_2 = date_create(date('Y-m-t',$invoice->invoice_due_date));
        $days_fiff = date_diff($date_1, $date_2)->format(("%a"));
        
        // $payment_terms = $CI->mdl_mcb_data->setting('invoices_due_after')+$days_fiff . ' days';
        $payment_terms = '30 days EOM';
        if ($invoice->invoice_due_date) {
            $payment_terms .= ' - Payment Due ' .date($_last_data_Formate, $invoice_next_month);
        }
    }
    return $payment_terms;
}

function daysBetween($dt1, $dt2) {
    return date_diff(
        date_create($dt2),  
        date_create($dt1)
    )->format('%a');
}

function invoice_has_tax($invoice_tax_rate) {

	/* Returns TRUE if the invoice has tax applied */
	if (abs($invoice_tax_rate->tax_amount) > 0) {

		return TRUE;

	}

	return FALSE;

}

function invoice_logo_file() {

	global $CI;

	if ($CI->mdl_mcb_data->setting('include_logo_on_invoice') == 'TRUE' AND $CI->mdl_mcb_data->setting('invoice_logo')) {

		if ($CI->uri->segment(2) == 'generate_pdf') {

			return getcwd() . '/uploads/invoice_logos/' . $CI->mdl_mcb_data->setting('invoice_logo');

		}

		else {

			return base_url() . 'uploads/invoice_logos/' . $CI->mdl_mcb_data->setting('invoice_logo');

		}

	}

}

function invoice_logo() {

	global $CI;

	if ($CI->mdl_mcb_data->setting('include_logo_on_invoice') == 'TRUE' AND $CI->mdl_mcb_data->setting('invoice_logo')) {

		if ($CI->uri->segment(2) == 'generate_pdf') {

			return '<img src="' . getcwd() . '/uploads/invoice_logos/' . $CI->mdl_mcb_data->setting('invoice_logo') . '" />';

		}

		else {

			return '<img src="' . base_url() . 'uploads/invoice_logos/' . $CI->mdl_mcb_data->setting('invoice_logo') . '" />';

		}

	}

}

function invoice_notes($invoice) {

	return nl2br($invoice->invoice_notes);

}


function invoice_pdf_script($invoice) {
	/*
	 * dompdf does not have access to variables in the script so
	 * we generate the script with the values ready for
	 * output on the pdf
	*/
	
	if ($invoice->invoice_is_quote == 1) {
		
		$header_text = '"Quotation: '.$invoice->invoice_number.'"';
	}
	else {
		$header_text = '"Tax Invoice: '.$invoice->invoice_number.'"';
	}
	
	
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
		/*
		$img_w = 115; 
		$img_h = 138; 
		$pdf->image(invoice_logo_file(), "png", ($w - $img_w - $margin), $margin, $img_w, $img_h);
		*/
		
		$size = 28;

		/*
		$text_height = Font_Metrics::get_font_height($font, $size);
		$text = '.$header_text.';  
		$margin = 16;
		$pdf->page_text($margin*2, $margin, $text, $font, $size, $color);
		*/
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

function invoice_client_discount($invoice) {

	$invoice_client_discount = 'NO DISCOUNT';
	
	if ($invoice->client_group_discount_percent > 0) {
		$prefix_discount = 'PLUS '.$invoice->client_group_discount_percent;
	} else if ($invoice->client_group_discount_percent < 0) {
		$prefix_discount = 'LESS '.$invoice->client_group_discount_percent;
	}
	

	return $invoice_client_discount;
}


?>