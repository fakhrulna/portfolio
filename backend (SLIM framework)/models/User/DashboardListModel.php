<?php
namespace models\User;

class DashboardListModel extends UserModel
{
	public function getUserBankData( $userid )
	{
		$db = $this->dbGetInstance();
		
		$sql = "SELECT 
				application.*,
				product.*,
				bank.* 
				FROM application
				INNER JOIN product
				ON product.prod_id = application.prod_id
				INNER JOIN bank
				ON product.bank_id = bank.bank_id
				WHERE product.bank_id = (SELECT bank_id FROM oauth_users WHERE username = ?)
				";
		$stmt = $db->prepare($sql);

		if ($stmt->Execute( array($userid) )){
			$data = $stmt->fetchAll();
			return $data;
		}else{
			return $this->processStatusHandler(false, 'Error Has Occur when fetching the data');
		}
	}
	
}
?>