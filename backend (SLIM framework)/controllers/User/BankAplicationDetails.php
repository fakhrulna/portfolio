<?php
namespace controllers\User;

class BankAplicationDetails
{
	
	var $app= [];
	
	function process($app){
		$this->app = $app;
		$db = $this->app->db;
		
		$request = $this->app->__get('request')->getParsedBody();
		
		$this->app->logger->addDebug("Process BankAplicationDetails: request", $request);
		
		$detail = $this->getApplicantDetail( $request['id'] );
		
		if ($detail){
			return $detail;
		}else{
			return $this->app->appModel->processStatusHandler(false, false,'-100');
		}
	}
	
	function processemu($app){
		$this->app = $app;
		$db = $this->app->db;
		$request = $this->app->__get('request')->getParsedBody();
		$this->app->logger->addDebug("Process BankAplicationDetails: request", $request);
		$userDetail = $this->app->appModel->getUserDetails($this->app->token);
		
		$data['name'] = 'Fakhrul';
		$data['status'] = 'applied';
		$data['amount'] = '20000';
		$data['sector'] = 'private';
		$data['installment_max'] = '500';
		$data['tenure'] = '72';
		$data['publishedprofitrate'] = '5.55';
		$data['email'] = 'fakhrul@assidq.com';
		$data['phoneno'] = '';
		$data['bank'] = ["1","2","3"];
		$data['gross_salary'] = '4000';
		$data['personal_loan'] = '100';
		$data['vehicle_loan'] = '1000';
		$data['houseloan'] = '1200';

		return $data;
	}
	
	function getApplicantDetail( $id ){
		$db = $this->app->db;
		$sql = 'SELECT * FROM app_applicant WHERE id=?';
		$this->app->logger->addDebug("Method getApplicantDetail: var [SELECT * FROM app_applicant WHERE id=?] [$id]");
		$s = $db->prepare($sql);
		$s->execute(array($id));
		if($s->rowCount() > 0){
			$data = $s->fetchAll();
			unset($data[0]['app_key']);
			$gross_salary = $data[0]['gross_salary'];
			$personal_loan = $data[0]['personal_loan'];
			$vehicle_loan = $data[0]['vehicle_loan'];
			$house_loan = $data[0]['house_loan'];
			$totalexpences =  $personal_loan + $vehicle_loan + $house_loan;
			$this->app->logger->addDebug("Process getApplicantDetail:totalexpences $totalexpences");
			$dsr =  $this->app->appModel->getDSR($gross_salary, $totalexpences);
			$data[0]['dsr'] = $dsr;
			return $data[0];
		}else{
			return false;
		}
	}
}
?>