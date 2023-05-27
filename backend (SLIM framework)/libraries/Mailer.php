<?php
namespace libraries;

use \PHPMailer as PHPMailer;

class Mailer extends PHPMailer{

	function __construct(){
		global $appConfig;
		
			//print_r($this);
			$this->isSMTP();
			$this->SMTPDebug = 0;
			$this->Debugoutput = 'html';
			$this->Host = $appConfig['emailHost'];
			$this->Port = $appConfig['emailPort'];
			$this->SMTPSecure = 'tls';
			$this->SMTPAuth = $appConfig['emailSMTPAuth'];
			$this->Username = $appConfig['emailUsername'];
			$this->Password = $appConfig['emailPassword'];
			$this->setFrom($appConfig['emailUsername'], $appConfig['emailFrom']);
			$this->addReplyTo($appConfig['emailUsername'], $appConfig['emailFrom']);
			$this->SMTPOptions = array(
								'ssl' => array(
								'verify_peer' => false,
								'verify_peer_name' => false,
								'allow_self_signed' => true
									)
			);
	
	}
	


}

?>