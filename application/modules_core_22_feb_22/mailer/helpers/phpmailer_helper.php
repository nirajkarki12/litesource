<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

function phpmail_send($from, $to, $subject, $message, $attachment_path = NULL, $cc = NULL, $bcc = NULL, $reply_to = NULL) {

    require_once(APPPATH . 'modules_core/mailer/helpers/phpmailer/class.phpmailer.php');

    $CI =& get_instance();

    $mail = new PHPMailer();

    $mail->CharSet = 'UTF-8';

    $mail->IsHtml();

    if ($CI->mdl_mcb_data->setting('email_protocol') == 'smtp') {

        $mail->IsSMTP();

        $mail->SMTPAuth = true;

        if ($CI->mdl_mcb_data->setting('smtp_security')) {

            $mail->SMTPSecure = $CI->mdl_mcb_data->setting('smtp_security');

        }

        $mail->Host = $CI->mdl_mcb_data->setting('smtp_host');

        $mail->Port = $CI->mdl_mcb_data->setting('smtp_port');

        $mail->Username = $CI->mdl_mcb_data->setting('smtp_user');

        $mail->Password = $CI->mdl_mcb_data->setting('smtp_pass');

    }

    elseif ($CI->mdl_mcb_data->setting('email_protocol') == 'sendmail') {

        $mail->IsSendmail();

    }

	// set reply_to before from else From is added to reply_to too
	if ($reply_to) {
		
		if (is_array($reply_to)) {
        
			$mail->AddReplyTo($reply_to[0],$reply_to[1]);
		}
		
		else {
			
			$mail->AddReplyTo($reply_to);
			
		}
    }
	
    if (is_array($from)) {

        $mail->SetFrom($from[0], $from[1]);

    }

    else {

        $mail->SetFrom($from);

    }

	log_message('INFO', 'mail to sender is: '.$mail->Sender);
	
    $mail->Subject = $subject;

    $mail->Body = $message;

	if (is_array($to)) {
		
		$mail->AddAddress($to[0], $to[1]);
		
	}
	
    else {
		
		$mail->AddAddress($to);
	}
	
	
    if ($cc) {

        $mail->AddCC($cc);

    }

    if ($bcc) {

        $mail->AddBCC($bcc);
    }

    
	
	
    if ($attachment_path) {
		
        $mail->AddAttachment($attachment_path, '', 'base64', 'application/pdf');
				
    }

    if ($mail->Send()) {
		log_message('INFO', 'mail to sender is: '.$mail->Sender);
		
        if (isset($CI->load->_ci_classes['session'])) {

            $CI->session->set_flashdata('custom_success', $CI->lang->line('email_success'));

            return TRUE;

        }

    }

    else {

        if (isset($CI->this->load->_ci_classes['session'])) {

            $CI->session->set_flashdata('custom_error', $mail->ErrorInfo);

            return FALSE;

        }

    }
	
	
	

}

?>