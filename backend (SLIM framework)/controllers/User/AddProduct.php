<?php
namespace controllers\User;

class AddProduct
{
	function process($app){
		$this->app = $app;
		$request = $this->app->__get('request')->getParsedBody();

		//TODO: Validation

		$product = $request['product'];
		$this->app->logger->addDebug("product:", $product);

		$appInfo = $app->appModel->addProduct($request);
	
		return array("app_id" => "abc");
			
		/*
		$appInfo = $app->appModel->addProduct($request);
		$appid = $appInfo['appid'];
		$app_key = $appInfo['app_key'];

		if($appid != ''){
				$prod_id = $request['prod_id'];
				$name = $request['name'];
				$phone = $request['phoneno'];
		
				$bankinfo = $app->appModel->getBankInfo($prod_id);

				$app->appModel->sendEmailToCust($request,$appid,$app_key);
				$app->appModel->sendEmailToBank($request);
				
				///SEND SMS TO CUSTOMER
				$msg = 'Hi '. $name .', 
				You have apply for product '.$bankinfo['producttitle'].' from '.$bankinfo['name'].'. 
				You will be contacted soon. 
				';
				$app->appModel->sendsms($phone,$msg);
				return array("app_id" => $appid);
				//return $app->appModel->getProductList($request);

		}else{
			//return $app->processStatusHandler(false, 'Error has occur while submitting application');
			//return $this->app->appModel->processStatusHandler(false, 'No Record Found');
		}
		*/
	}
	
	function processemu($app){
		$this->app = $app;
		$db = $this->app->db;
		$request = $this->app->__get('request')->getParsedBody();
		$this->app->logger->addDebug("Process AddProduct: request", $this->app->token);

		$product= $request['product'];
		$testvar = Array($product["name"]);

		$this->app->logger->addDebug("product:", $product);
		$this->app->logger->addDebug("bank:", $product["sectorList"]["list"][0]);
	
		$data['application']['new'] = '12';
		$data['application']['preapproved'] = '0';
		$data['application']['rejected'] = '0';
		
		$data['product']['active'] = '1';
		$data['product']['soon'] = '1';
		$data['product']['expired'] = '0';
		
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
