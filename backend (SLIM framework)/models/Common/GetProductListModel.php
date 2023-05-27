<?php
namespace models\Common;

class GetProductListModel extends CommonModel
{
	public function getProductList($request)
	{
		$amount = $request["amount"];
		$tenure = $request["tenure"];
		
		$userdata = $this->getAvailableProduct($amount, $tenure);
		if($userdata){
			$count=0;
			foreach($userdata as $key => $applicant){
				$data[$count]["status_code"] = "0";
				$data[$count]["prod_id"] = $applicant["prod_id"];
				$data[$count]["title"] = $applicant["title"];
				$data[$count]["shortdesc"] = $applicant["shortdesc"];
				$data[$count]["profit_rate"] = $applicant["profit_rate"];
				$data[$count]["tn"] = $applicant["tn"];
				$data[$count]["bankid"] = $applicant["bank_id"];
				$data[$count]["bankname"] = $applicant["bank_name"];
				$data[$count]["max_fin_amt"] = $applicant["max_fin_amt"];
				$data[$count]["min_fin_amt"] = $applicant["min_fin_amt"];
				$data[$count]["min_tenure"] = $applicant["min_tenure"];
				$data[$count]["max_tenure"] = $applicant["max_tenure"];
				$count++;
			}
			return $data;
		}else{
			return $this->processStatusHandler(false, 'No Record Found');
		}
	}
	
	function getAvailableProduct($amount, $tenure)
	{
		$db = $this->dbGetInstance();

		$sql = "SELECT 
				product_pf.*,
				bank_info.* 
				FROM product_pf
				INNER JOIN bank_info
				ON product_pf.bank_id = bank_info.bank_id
				WHERE product_pf.min_fin_amt <= $amount AND product_pf.max_fin_amt >= $amount
				AND product_pf.min_tenure <= $tenure AND product_pf.max_tenure >= $tenure
				";
		$stmt = $db->prepare($sql);

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