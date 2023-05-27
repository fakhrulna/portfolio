<?php
namespace models\User;

class GetAffiliateModel extends UserModel
{
    function getAffiliate ($request)
    {
        $db = $this->dbGetInstance();

        $sql = "SELECT * FROM aff WHERE affiliate_id = $request[affiliate_id]";
        $stmt = $db->prepare($sql);
        $stat = $stmt->Execute();

        $data = false;
        if ($stmt->execute()){
            $data = $stmt->fetchAll();
        }

        return $data;
    }
}
?>