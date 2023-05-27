<?php

namespace models\Common;

use libraries\Model as Model;
use nusoap_client as nusoap_client;

class CommonModel extends Model
{
    public function __construct($app = null)
    {
        parent::__construct($app);
    }

    public function getApplicationDocuments($app_id, $returnList = false, $lang = false)
    {
        $db = $this->app->db;

        //$sql = "SELECT * FROM app_documenttype ORDER BY sequence ASC";

        $sql = "SELECT * FROM app_documenttype WHERE doc_type NOT IN ('ic_doc_front', 'ic_doc_back') ORDER BY sequence ASC";

        if (is_array($returnList)) {
            $arrList = sprintf("'%s'", implode("','", $returnList));
            $sql = "SELECT * FROM app_documenttype WHERE doc_type IN ($arrList) ORDER BY sequence ASC";
            //$this->app->logger->addDebug($sql);
        }

        $stmt = $db->prepare($sql);
        $stmt->Execute();
        $row = $stmt->rowCount();

        if ($row != 0) {
            $list = $stmt->fetchAll();

            foreach ($list as $key => $doc) {
                $file = $this->getFileInfo($app_id, $doc['doc_type']);
                //print_r($file);
                $doclist[$key]['doctype'] = $doc['doc_type'];
                //$doclist[$key]['caption']= $doc['caption'];
                $caption = 'caption';
                if ($lang) {
                    $caption = 'caption_' . $lang;
                }
                $doclist[$key]['caption'] = $doc[$caption];

                $doclist[$key]['sequence'] = $doc['sequence'];
                if ($file) {
                    $doclist[$key]['submitted'] = true;
                    $doclist[$key]['mime'] = $file['mime'];
                    $doclist[$key]['upload_metadata'] = $file['upload_metadata'];
                //$doclist[$key]['image']= 'data:'.$file['mime'].';base64,' . base64_encode($file['data']);
                } else {
                    $doclist[$key]['submitted'] = false;
                }
            }

            return $doclist;
        } else {
            return false;
        }
    }

    public function getFileInfo($app_id, $doc_type)
    {
        $db = $this->app->db;

        $sql = "SELECT * FROM app_applicantdocument WHERE app_id = ? AND doc_type = ? ORDER BY date_created DESC";

        $stmt = $db->prepare($sql);
        $stmt->Execute([$app_id, $doc_type]);
        $row = $stmt->rowCount();
        if ($row != 0) {
            $list = $stmt->fetchAll();
            return $list[0];
        } else {
            return false;
        }
    }

    /**
     * Calculate age base on malaysian ic number
     *
     * @param int $ic_no 850407035206
     * @param bool $boolDetails true for details
     * @return int $age
     **/
    public function calculateAgeIc($ic_no, $boolDetails = false)
    {
        if (strlen($ic_no) != 12) {
            return null; // Now $ic_no is free text
        }

        $year = substr($ic_no, 0, 2);
        $month = substr($ic_no, 2, 2);
        $day = substr($ic_no, 4, 2);

        $dob = "$year-$month-$day";
        $validDate = strtotime($dob);

        if (!$validDate) {
            return null;
        }

        $today = date("Y-m-d");
        $diff = date_diff(date_create($dob), date_create($today));
        if ($boolDetails) {
            return $diff->format('%y Years, %m Months, %d Days');
        }

        if ($diff) {
            return $diff->format('%y');
        }

        // return false;
        return null; // Now $ic_no is free text
    }

    /**
     * Validate the phone number format
     *
     * @return boolean / fixed number
     **/
    public function validatePhone($text)
    {
        if (strlen($text) < 9) {
            return false;
        }

        if (strpos("$text", "01") === 0) {
            return $text;
        }

        if (strpos("$text", "01") !== 1) {
            return false;
        }

        if (strpos("$text", "60") === 0) {
            return substr($text, 1);
        }

        return $text;
    }

    public function isKycDone($appid)
    {
        $db = $this->app->db;

        $sql = "SELECT * FROM app_kyc WHERE app_id = '$appid'";
        $stmt = $db->prepare($sql);
        $stmt->Execute();
        $row = $stmt->rowCount();
        if ($row != 0) {
            return true;
        }

        return false;
    }

    public function get_client_ip_server()
    {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        } else {
            $ipaddress = 'UNKNOWN';
        }

        return $ipaddress;
    }

    public function create_metadata()
    {
        $arr['ip'] = $this->get_client_ip_server();
        $arr['browser'] = $_SERVER['HTTP_USER_AGENT'];
        $arr['datetime'] = date('d-m-Y H:i:s');

        return json_encode($arr);
    }

    /**
     * @param $applicant_id the applicant row id
     **/
    public function get_audit_details($applicant_id)
    {
        $db = $this->app->db;

        $sql = "SELECT name, ic_no, tnc_text, tnc_metadata, app_id, verification_code, verification_phone_dt, verification_phone_metadata 
				FROM app_applicant WHERE id = $applicant_id";
        $stmt = $db->prepare($sql);
        $stmt->Execute();
        $row = $stmt->rowCount();
        if ($row != 0) {
            return $stmt->fetchAll();
        }

        return false;
    }

    /**
     * @param $appid the app_id
     * @param $db the database object
     **/
    public function get_verification_code($appid, $db)
    {
        //$db = $this->app->db;

        $verification_code = mt_rand(100000, 999999);
        $row = [
            'app_id' => $appid,
            'verification_code' => $verification_code
        ];
        // $sql = "INSERT INTO app_sms_gen (app_id, verification_code) VALUES (?, ?)";
        $sql = "INSERT INTO app_sms_gen SET app_id=:app_id, verification_code=:verification_code";
        $stmt = $db->prepare($sql);
        // $exe = $stmt->Execute(array("$appid", $verification_code));
        $exe = $stmt->Execute($row);
        if (!$exe) {
            return false;
        }

        return $verification_code;
    }

    public static function convertIcToDob($ic_no)
    {
        $year = substr($ic_no, 0, 2);

        switch ($year) {
            case in_array($year, range(31, 99)):
                $year = '19' . $year;
                break;
            case in_array($year, range(00, 30)):
                $year = '20' . $year;
                break;
            default:
                $year = '19' . $year;
        }

        $month = substr($ic_no, 2, 2);
        $day = substr($ic_no, 4, 2);

        return "$day/$month/$year";
    }

    public static function gradeColor($credit_score)
    {
        switch (true) {
            case $credit_score >= 600:
                return '#39E028'; //green
                break;

            case $credit_score >= 400 && $credit_score < 600:
                return '#F7FF00'; //yellow
                break;

            case $credit_score <= 399:
                return '#C9C9C9'; //grey
                break;

            default:
                return '#fff';
                break;
        }

        return '#C9C9C9';
    }

    public static function maskingText($str, $startPos = 1, $len = 8)
    {
        $mask = preg_replace("/\S/", "*", $str);

        $mask = substr($mask, $startPos, $len);
        $str = substr_replace($str, $mask, $startPos, $len);

        return $str;
    }

    public static function verifyAppStatus($tokeninfo, $app_id, $dbConn)
    {
        $model = new \models\User\UserModel;
        $User = $model->getUserDetails($tokeninfo);
        $bank_id = $User['bank_id'];
        $userGroupCond = "1";
        if ($bank_id != 1) {
            $userGroupCond = "d.bank_id = '$bank_id'";
        }

        $sql = "SELECT d.status as app_status
				FROM app_applicant a, app_applicantdetail d 
				WHERE a.app_id = '$app_id' AND d.applicantid=a.id AND d.check=1 AND $userGroupCond";
        $stmt = $dbConn->prepare($sql);
        $stmt->execute();
        $count = $stmt->rowCount();

        $row['app_status'] = true;
        if ($count == 1) {
            $row = $stmt->fetch();
        }

        return $row['app_status'];
    }

    public static function cleanData(&$str)
    {
        // escape tab characters
        $str = preg_replace("/\t/", "\\t", $str);

        // escape new lines
        $str = preg_replace("/\r?\n/", "\\n", $str);

        // convert 't' and 'f' to boolean values
        if ($str == 't') {
            $str = 'TRUE';
        }
        if ($str == 'f') {
            $str = 'FALSE';
        }

        // force certain number/date formats to be imported as strings
        if (preg_match("/^0/", $str) || preg_match("/^\+?\d{8,}$/", $str) || preg_match("/^\d{4}.\d{1,2}.\d{1,2}/", $str)) {
            //$str = "'$str";
            $str = "<td style='mso-number-format:\@;'>$str</td>";
        }

        // escape fields that include double quotes
        if (strstr($str, '"')) {
            $str = '"' . str_replace('"', '""', $str) . '"';
        }

        //return $str;
    }

    public static function isIamMaker($userId, $db)
    {
        $sql = "SELECT userid FROM oauth_users WHERE userid=$userId AND last_name='Maker'";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $count = $stmt->rowCount();

        if ($count == 1) {
            return true;
        }

        return false;
    }

    public static function getApplicantStatus($applicationid, $db)
    {
        $statusVerificationId = false;

        /***********************************************
         * Check status on Assalam operation level
         * ID Verification Process by Maker
         ***********************************************/
        $sql = "SELECT a.id 
				FROM app_applicant a 
				WHERE (a.id = $applicationid AND a.is_maker_verified = 1) OR (a.id = $applicationid AND a.is_not_qualified = 1)";
        $stmtV = $db->prepare($sql);
        $stmtV->Execute();
        $rowV = $stmtV->rowCount();
        if ($rowV != 0) {
            $statusVerificationId = "Verification process";
        }

        /***********************************************
         * Check status on Assalam operation level
         * ID Verification Process by Checker - if applicant not qualified
         ***********************************************/
        $sql = "SELECT a.id  
				FROM app_applicant a 
				WHERE (a.id = $applicationid AND a.is_not_qualified_check = 1)";
        $stmtV = $db->prepare($sql);
        $stmtV->Execute();
        $rowV = $stmtV->rowCount();
        if ($rowV != 0) {
            $statusVerificationId = "Sorry you are not qualified for this application.";
        }

        /***********************************************
         * Check status on Assalam operation level
         * ID Verification Process by Checker - if applicant have process credit checking - qualified
         ***********************************************/
        $sql = "SELECT a.id  
				FROM app_applicant a 
				JOIN app_request_cbm cbm ON cbm.app_id = a.app_id 
				WHERE a.id = $applicationid";
        $stmtV = $db->prepare($sql);
        $stmtV->Execute();
        $rowV = $stmtV->rowCount();
        if ($rowV != 0) {
            $statusVerificationId = "Credit checking process";
        }

        /***********************************************
         * Check status on Assalam operation level
         * Credit checking success - auto publish to bank's dashboard
         ***********************************************/
        $sql = "SELECT a.id 
				FROM app_applicant a 
				WHERE a.id = $applicationid AND a.credit_grade IS NOT NULL";
        $stmtV = $db->prepare($sql);
        $stmtV->Execute();
        $rowV = $stmtV->rowCount();
        if ($rowV != 0) {
            $statusVerificationId = "Application Submitted";
        }

        return $statusVerificationId;
    }

    public function getApplicationDetailsByapp_id($app_id)
    {
        $db = $this->app->db;

        $sql = "SELECT * FROM app_applicant WHERE app_id LIKE '$app_id' ";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $row = $stmt->rowCount();

        if ($row != 0) {
            $list = $stmt->fetchAll();
            return $list[0];
        }

        return false;
    }

    public function getApplicationDetailsCCByapp_id($app_id)
    {
        $db = $this->app->db;

        $sql = "SELECT * FROM app_applicant_cc WHERE app_id LIKE '$app_id' ";

        $stmt = $db->prepare($sql);
        $stmt->execute();
        $row = $stmt->rowCount();

        if ($row != 0) {
            $list = $stmt->fetchAll();
            return $list[0];
        }

        return false;
    }

    public function sendsms($phone, $msg)
    {
        global $appConfig;
        // $phone = '0172915371';

        $SMS_ACCESS_ID = $appConfig['SMS_ACCESS_ID'];
        $SMS_ACCESS_TOKEN = $appConfig['SMS_ACCESS_TOKEN'];
        $SMS_ORIGINATOR_ID = $appConfig['SMS_ORIGINATOR_ID'];
        $SMSGW_URL = $appConfig['SMSGW_URL'];

        $po = [
            'access_id' => $SMS_ACCESS_ID,
            'access_token' => $SMS_ACCESS_TOKEN,
            'msg_no' => md5(date('dmYHis')),
            'originator_id' => $SMS_ORIGINATOR_ID,
            'msisdn' => '6' . $phone,
            'msg' => $msg
        ];

        $client = new nusoap_client($SMSGW_URL, true);

        $err = $client->getError();
        if ($err) {
            // Display the error
            $this->app->logger->addInfo("ERROR has Occur SMS $phone:: " . $err);
            // At this point, you know the call that follows will fail
        }

        $result = $client->call("SendMT", $po);

        if ($client->fault) {
            $this->app->logger->addInfo("ERROR has Occur SMS $phone:: ", $result);
        } else {
            // Check for errors
            $err = $client->getError();
            if ($err) {
                // Display the error
                $this->app->logger->addInfo("ERROR has Occur SMS $phone:: " . $err);
            } else {
                // Display the result
                $this->app->logger->addInfo("SMS Result $phone::", $result);
            }
        }
    }

    public function getValidateApplication($appid = '', $phone_no = '', $email_address = '')
    {
        $db = $this->app->db;

        if ($appid == '') {
            return $this->processStatusHandler(false, 'Missing : application_id');
        }
        if ($phone_no == '') {
            return $this->processStatusHandler(false, 'Missing : phone_no');
        }
        if ($email_address == '') {
            return $this->processStatusHandler(false, 'Missing : email_address');
        }

        $sql = "SELECT *
				FROM app_applicant
				WHERE app_applicant.app_id = ?
				AND app_applicant.email = ? 
				AND app_applicant.phoneno = ?
				";
        $stmt = $db->prepare($sql);
        $stmt->Execute([$appid, $email_address, $phone_no]);
        $row = $stmt->rowCount();

        if ($row == 1) {
            $data = $stmt->fetchAll();
            return $data[0];
        } else {
            return $this->app->appModel->processStatusHandler(false, 'Failed : Authentication.');
        }
    }
    public function goHalalSendSms($phone, $msg)
    {
        global $appConfig;

        $SMS_ACCESS_ID = $appConfig['SMS_ACCESS_ID'];
        $SMS_ACCESS_TOKEN = $appConfig['SMS_ACCESS_TOKEN'];
        // $SMS_ORIGINATOR_ID = "GOHALAL";
        $LO_SMS_ORIGINATOR_ID = $appConfig['LO_SMS_ORIGINATOR_ID'];
        $SMSGW_URL = $appConfig['SMSGW_URL'];


        $po = [
            'access_id' => $SMS_ACCESS_ID,
            'access_token' => $SMS_ACCESS_TOKEN,
            'msg_no' => md5(date('dmYHis')),
            'originator_id' => $LO_SMS_ORIGINATOR_ID,
            'msisdn' => '6' . $phone,
            'msg' => $msg
        ];

        $client = new nusoap_client($SMSGW_URL, true);

        $err = $client->getError();
        if ($err) {
            // Display the error
            $this->app->logger->addInfo("ERROR has Occur SMS $phone:: " . $err);
            // At this point, you know the call that follows will fail
        }

        $result = $client->call("SendMT", $po);

        if ($client->fault) {
            $this->app->logger->addInfo("ERROR has Occur SMS $phone:: ", $result);
        } else {
            // Check for errors
            $err = $client->getError();
            if ($err) {
                // Display the error
                $this->app->logger->addInfo("ERROR has Occur SMS $phone:: " . $err);
            } else {
                // Display the result
                $this->app->logger->addInfo("SMS Result $phone::", $result);
            }
        }
    }


    public function goHalalSendSmsResponse($phone, $msg)
    {
        global $appConfig;

        $SMS_ACCESS_ID = $appConfig['SMS_ACCESS_ID'];
        $SMS_ACCESS_TOKEN = $appConfig['SMS_ACCESS_TOKEN'];
        // $SMS_ORIGINATOR_ID = "GOHALAL";
        $LO_SMS_ORIGINATOR_ID = $appConfig['LO_SMS_ORIGINATOR_ID'];
        $SMSGW_URL = $appConfig['SMSGW_URL'];

        $po = [
            'access_id' => $SMS_ACCESS_ID,
            'access_token' => $SMS_ACCESS_TOKEN,
            'msg_no' => md5(date('dmYHis')),
            'originator_id' => $LO_SMS_ORIGINATOR_ID,
            'msisdn' => '6' . $phone,
            'msg' => $msg
        ];

        $client = new nusoap_client($SMSGW_URL, true);

        $err = $client->getError();
        if ($err) {
            // Display the error
            $this->app->logger->addInfo("ERROR has Occur SMS $phone:: " . $err);
            // At this point, you know the call that follows will fail
        }

        $result = $client->call("SendMT", $po);

        if ($client->fault) {
            $this->app->logger->addInfo("ERROR has Occur SMS $phone:: ", $result);
        } else {
            // Check for errors
            $err = $client->getError();
            if ($err) {
                // Display the error
                $this->app->logger->addInfo("ERROR has Occur SMS $phone:: " . $err);
            } else {
                // Display the result
                $this->app->logger->addInfo("SMS Result $phone::", $result);
            }
        }
    }
}
