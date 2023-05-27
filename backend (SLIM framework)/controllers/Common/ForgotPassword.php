<?php
namespace controllers\Common;

use models\Common\CommonModel;

class ForgotPassword
{
	public function process($app) {
		
		$this->app = $app;
		$db = $this->app->db;
		$request = $app->__get('request')->getParsedBody();
		$app->logger->addDebug("Process SignUpEmail: request",$request);
		$email = $request["email"];
		$password = $request["password"];

		$sql = "SELECT username FROM oauth_users WHERE username=?";
		$stmt = $db->prepare($sql);
		$exe = $stmt->Execute( array($email) );
		$row = $stmt->rowCount();
		
		if ($row == 0) {
			
			$dt = date('Y-m-d H:i:s');
			
			$common = new CommonModel;
			$metadata = $common->create_metadata();
			
			$sql = "INSERT INTO oauth_users (username, password) VALUES (?,?)";
			$stmt = $db->prepare($sql);
			$exe = $stmt->Execute( array($email, sha1($password) ));			
			if ($exe) {
				return array("signup_status"=>0);
			}
		}else{
			return array("signup_status"=>10);
		}
		
		return $this->app->appModel->processStatusHandler(false, 'Sorry, got error or record not found');
		
		
	}
	
	//FOR DEVELOPMET REQUEST
	function processemu($app){

		$request = $app->__get('request')->getParsedBody();
		$app->logger->addDebug("processemu ForgotPassword: request",$request != '' ? $request : []);

		return array("status" => 0);
	}
	
}
?>
