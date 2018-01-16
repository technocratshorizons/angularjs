<?php

use Sendinblue\Mailin;

class Sms {
	
	function send($mobile,$msg) {
		
		$mailin = new Mailin('https://api.sendinblue.com/v2.0',SMS_KEY);
		$data = array( "to" => $mobile,
							"from" => "Mark",
							"text" => $msg,
							"web_url" => "http://example.com",
							"tag" => "Tag1",
							"type" => "transactional"
		);	
		try {
			return $mailin->send_sms($data);
		}
		catch (Exception $e) {
			log_message('error', $e->getMessage());
		}
		
	}
}	
?>