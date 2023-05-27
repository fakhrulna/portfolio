<?php
namespace controllers\Common;

use models\Common\CommonModel;

class EmailVerification
{
	public function process($app) {
        //error_reporting(-1);
        //ini_set('display_errors', 1);
		
		$this->app = $app;
		$db = $this->app->db;
		$request = $app->__get('request')->getParsedBody();
		$app->logger->addDebug("Process CheckByKey: request",$request);
		$appid = $request["app_id"];		
		$key = $request["app_key"];
		
		$sql = "SELECT app_id FROM applicant WHERE app_applicant.app_id = ? AND (app_applicant.app_key = ?)";
		$stmt = $db->prepare($sql);
		$stmt->Execute( array($appid, $key) );
		$row = $stmt->rowCount();
		
		if($row == 1){
			$dt = date('Y-m-d H:i:s');
			
			$common = new CommonModel;
			$metadata = $common->create_metadata();
			
			$sql = "UPDATE applicant SET is_verified_email=?, verification_email_dt=?, verification_email_metadata=? WHERE app_id = ?";
			$stmt = $db->prepare($sql);
			$exe = $stmt->Execute( array(1, $dt, "$metadata", "$appid"));			
			if ($exe) {
				return array("status"=>true, "status_details"=>"Update is_verified_email success");
			}
		}
		
		return $this->app->appModel->processStatusHandler(false, 'Sorry, got error or record not found');
	}
}
?>