<?php
namespace models\User;

class GetApplicationSumModel extends UserModel
{
    function getApplicationSum($request)
    {
        $userGroupCond = "1";
        if ($request['usergroup'] != "admin") {
            $userGroupCond = "( bi.bank_id = :bank_id OR bi.code = :bank_id )";
        }
        $statusCond = "1";
		if($request['status'] == 'internal_reviewed') {
			$statusCond = " d.status in (:status1, :status2) ";
		} else if($request['status']){
            $statusCond = "( d.status = :app_status )" ;
		}

        $db = $this->dbGetInstance();

        $sql = "
            SELECT sum(a.amount) loan_amount 
            FROM applicant a
            INNER JOIN applicantdetail d on d.applicantid=a.id
            LEFT JOIN product pf on d.prod_id = pf.prod_id	
            LEFT JOIN bank bi on d.bank_id = bi.bank_id
            WHERE $userGroupCond 
            AND $statusCond
            AND a.credit_score IS NOT NULL
            AND d.check=1 
            ";
        $stmt = $db->prepare($sql);
        if ($request['usergroup'] != "admin") {
            $stmt->bindParam(":bank_id", $request['usergroup']);
        }
        if ($request['status'] == 'internal_reviewed') {
			$stmt->bindValue( ":status1", "internal_reviewed");
			$stmt->bindValue( ":status2", "cra_pass");
		} else if($request['status']){
            $stmt->bindParam(":app_status", $request['status']);
        }

        $stat = $stmt->Execute();

        $data = false;
        if ($stmt->execute()) {
            $data = $stmt->fetchAll();
        }

        return $data;
    }
}
?>
