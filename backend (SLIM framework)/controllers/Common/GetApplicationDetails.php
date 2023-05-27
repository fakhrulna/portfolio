<?php
namespace controllers\Common;

class GetApplicationDetails
{
	
	public function process($app){
		$db = $app->db;

		$request = $app->__get('request')->getParsedBody();
		$app->logger->addInfo("HIT CONTROLLER,,,".$request["guid"]);

		$userdata = $this->getAppDetails($app, $request["application_id"]);
		if($userdata){
                        $count=0;
                        foreach($userdata as $key => $applicant){
				$data[$count]["id"] = $applicant["id"];
                                $data[$count]["application_id"] = $applicant["app_id"];
                                $data[$count]["facility_id"] = "AGR20190816160508T3NIO0";
                                $data[$count]["application_received_ts"] = $applicant["create_date"];
                                $data[$count]["last_status_ts"] = $applicant["create_date"];
                                $data[$count]["status"] = "applied";
                                $data[$count]["applied_amount"] = $applicant["amount"];
                                $data[$count]["applied_tenure"] = $applicant["tenure"];
                                $data[$count]["purpose"] = $applicant["purpose"];
                                $data[$count]["approved_amount"] = "";
                                $data[$count]["approved_tenure"] = "";
                                $data[$count]["approved_profit_rate"] = "";
                                $data[$count]["bank_logo_url"] = "";
				$data[$count]["facility_name"] = "Personal Financing-i";
				$data[$count]["bank_name"] = "Financing Institution";
                                $count++;
                        }
                        return $data;
                }else{
                        return $this->processStatusHandler(false, 'No Record Found');
                }



		return array("HIT CONTROLLER [process]");
	}
	
	public function processemu($app){
		$request = $app->__get('request')->getParsedBody();
		$app->logger->addInfo("HIT CONTROLLER emuuuuu");
		 return array("HIT CONTROLLER.... [process]");
		return $app->appModel->getListOfArticles($request);
	}

	private function getAppDetails($app, $appId){
                $db = $app->db;

		$sql = "SELECT * FROM applicant WHERE app_id='$appId'

                                ";
                $stmt = $db->prepare($sql);

		$app->logger->addInfo("in here $appId");
                if ($stmt->execute()){
                        if($stmt->rowCount() > 0){
                                $data = $stmt->fetchAll();
                                return $data;
                        }
                        return false;
                }else{
                        return false;
                }
        }

	
}
?>
