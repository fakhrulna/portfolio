<?php
namespace models\User;

class GetAffiliateDetailsModel extends UserModel
{
    function getAffiliate ($request)
    {
        $db = $this->dbGetInstance();
        $affiliate_id = @$request['affiliate_id']?\is_numeric($request['affiliate_id'])?$request['affiliate_id']:0:0;

        $sql = "SELECT logo_url aff_logo_url, css_class_name aff_css_classname FROM affWHERE affiliate_id = $affiliate_id";
        $stmt = $db->prepare($sql);
        $stat = $stmt->Execute();

        $data = false;
        if ($stmt->execute()){
            if ($stmt->rowCount() > 0) {
                $item = $stmt->fetch();
                $data['aff_logo_url'] = $item['aff_logo_url'];
                $data['aff_css_classname'] = $item['aff_css_classname'];
            } else {
                return $this->processStatusHandler(false, 'No Record Found');
            }
        }

        return $data;
    }
}
?>