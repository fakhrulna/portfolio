<?php
namespace controllers\User;

class BankApplicationList
{
	function process($app){
		$this->app = $app;
		$db = $this->app->db;
		
		$app->logger->addDebug("Process BankApplicationList: request", $this->app->token);
		$sql = 'SELECT name, phone, bank_id FROM oauth_users WHERE username=?';
		$s = $db->prepare($sql);
		if ($s->execute(array($this->app->token['user_id']))){
			if($s->rowCount() > 0){
				$data = $s->fetchAll();
				//print_r($data);
				return array(	"name" => $data[0]['name'],
								"phone" => $data[0]['phone'],
								"bank" => $this->getBankInfo( $data[0]['bank_id'] )
				);
			}else{
				return $this->app->appModel->processStatusHandler(false, 'No Record Found');
			}
		}
	}
	
	function processemu($app){
		$this->app = $app;
		$db = $this->app->db;
		//$request = $this->app->__get('request')->getParsedBody();
		$this->app->logger->addDebug("Process BankApplicationList: request", $this->app->token);
		$userDetail = $this->app->appModel->getUserDetails($this->app->token);
		
		$data[0]['id'] = '1';
		$data[0]['name'] = 'Muhamad Ainuddin';
		$data[0]['image'] = null;
		$data[0]['gross_salary'] = '4000';
		$data[0]['amount'] = '10000';
		$data[0]['tenure'] = '72';
		$data[0]['sector'] = 'private';
		
		$data[1]['id'] = '2';
		$data[1]['name'] = 'Jamilah Harun';
		$data[1]['image'] = null;
		$data[1]['gross_salary'] = '3000';
		$data[1]['amount'] = '10000';
		$data[1]['tenure'] = '36';
		$data[1]['sector'] = 'glc';
		
		$data[2]['id'] = '3';
		$data[2]['name'] = 'Muhamad Ainuddin';
		$data[]['image'] = null;
		$data[2]['gross_salary'] = '3500';
		$data[2]['amount'] = '12000';
		$data[2]['tenure'] = '60';
		$data[2]['sector'] = 'government';
		
		return $data;
	}
	
	function getBankInfo( $bank_id ){
		$db = $this->app->db;
		$sql = 'SELECT * FROM bank_info WHERE bank_id=?';
		$s = $db->prepare($sql);
		$s->execute(array($bank_id));
		if($s->rowCount() > 0){
			$data = $s->fetchAll();
			return $data;
		}else{
			return false;
		}
	}
}
?>