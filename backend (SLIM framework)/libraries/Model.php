<?php
namespace libraries;

use libraries\Mailer as Mailer;
use PDO;

class Model
{
    public $app = [];

    public function __construct($app = null)
    {
        $this->app = $app;
    }

    public function dbGetInstance()
    {
        global $appConfig;
        $pdo = new PDO("mysql:host=" . $appConfig['DBHOST'] . ";dbname=" . $appConfig['DBNAME'].';charset=UTF8', $appConfig['DBUSERNAME'], $appConfig['DBPASSWORD']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    }
    
    public function checkMethodExist($className)
    {
        $className = ltrim($className);
        $fileName = '';
        $nameSpace = '';

        if ($lastNsPos = strrpos($className, '\\')) {
            $nameSpace = substr($className, 0, $lastNsPos);
            $className = substr($className, $lastNsPos + 1);
            $fileName = str_replace('\\', DIRECTORY_SEPARATOR, $nameSpace) . DIRECTORY_SEPARATOR;
        }

        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
        
        if (is_readable($fileName)) {
            return true;
        } else {
            return false;
        }
    }
    
    public function checkUserAuth($token)
    {
        if ($token['user_id'] == null || $token['user_id'] == '') {
            return false;
        }
        return true;
    }
    public function getTokenInfo($token_string)
    {
        $db = $this->dbGetInstance();

        $sql = "SELECT * FROM oauth_access_tokens WHERE access_token = ?";
        $stmt = $db->prepare($sql);
        $stmt->Execute(array($token_string));

        $data = $stmt->fetchAll();
        return $data[0];
    }
    /**
    Returning process status to controller
    */
    public function processStatusHandler($status=true, $message=false, $code='')
    {
        $codemessage = $this->getSCodeMessage($code);
        $state = array('status'=>$status,
                        'status_code'=>$code,
                    'message'=>$codemessage != '' ? $codemessage : $message
                    );
        if ($message == false && $code == '') {
            unset($state['message']);
        }
        return $state;
    }
    
    
    public function sendEmail($name, $email, $subject, $content='')
    {
        $mail = new Mailer;
        $mail->addAddress($email);// Add a recipient
        $mail->isHTML(true);// Set email format to HTML

        $mail->Subject = $subject;
        $mail->Body    = $content;

        if (!$mail->send()) {
            return 'Mailer Error: ' . $mail->ErrorInfo;
        } else {
            return 'Message has been sent';
        }
    }

    public function getRealIpAddr()
    {
        if (php_sapi_name() == 'cli') {
            return '127.0.0.1';
        }

        $ip = '';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {   //check ip from share internet
            $ip=$_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {   //to check ip is pass from proxy
            $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip=$_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
    
    public function getProductSummary($products)
    {
        $arr =[];
        if ($products) {
            foreach ($products as $product) {
                if (!isset($arr[$product['bank_id']])) {
                    $arr[$product['bank_id']]['profitrate_list'][] = $product['profit_rate'];
                    $arr[$product['bank_id']]['tenure_list'][] = $product['max_tenure'];
                    $arr[$product['bank_id']]['prod_id_list'][] = $product['prod_id'];
                } else {
                    $arr[$product['bank_id']]['profitrate_list'][] = $product['profit_rate'];
                    $arr[$product['bank_id']]['tenure_list'][] = $product['max_tenure'];
                    $arr[$product['bank_id']]['prod_id_list'][] = $product['prod_id'];
                }
            }
            
            foreach ($arr as $key => $value) {
                $arr[$key]['MinProfRate'] = $this->getMinimumProfitRate($value['prod_id_list']);
            }
            $this->app->logger->addDebug("getProductSummary[]", $arr);
            return $arr;
        } else {
            return false;
        }
    }
    public function calculateInstallment($amt, $i, $term)
    {
        $int = $i/1200;
        $int1 = 1+$int;
        $r1 = pow($int1, $term);

        $pmt = $amt*($int*$r1)/($r1-1);

        return $pmt;
    }

    public function getMinimumProfitRate($list)
    {
        $db = $this->dbGetInstance();
        $string  = '';
        foreach ($list as $item) {
            $string .= "'$item',";
        }
        $string = rtrim($string, ',');
        //WHO KNOWS TO GET THE ID OF THE MINIMUM VALUE IS HARD YA
        $sql = "SELECT a.prod_id, a.profit_rate
					FROM product_pf a
					INNER JOIN (
						SELECT prod_id, MIN(profit_rate) profit_rate
						FROM product_pf WHERE prod_id IN($string)
					) b ON a.profit_rate = b.profit_rate AND a.profit_rate = b.profit_rate
					WHERE a.prod_id IN($string)";
        
        //$nsql = preg_replace('/\s\s+/', ' ', $sql);
        //$this->app->logger->addDebug("getMinimumProfitRate[$nsql]");
        $stmt = $db->prepare($sql);
        $stmt->Execute();

        $data = $stmt->fetchAll();
        $this->app->logger->addDebug("getMinimumProfitRate[]", $data);
        
        return $data[0];
    }

    public function compressImage($source_url, $destination_url, $quality)
    {
        $info = getimagesize($source_url);
    
        if ($info['mime'] == 'image/jpeg') {
            $image = imagecreatefromjpeg($source_url);
        } elseif ($info['mime'] == 'image/gif') {
            $image = imagecreatefromgif($source_url);
        } elseif ($info['mime'] == 'image/png') {
            $image = imagecreatefrompng($source_url);
        }
    
        //save file
        imagejpeg($image, $destination_url, $quality);
    
        //return destination file
        return $destination_url;
    }


    /**
    * Convert date to db date format
     * @param $date dd-mm-yyyy
     * @return yyyy-mm-dd
     */

    public function getLastApplicationId()
    {
        $db = $this->dbGetInstance();
        $sql = "SELECT MAX( id ) AS id FROM app_applicant";

        //die;
        $stmt = $db->prepare($sql);
        $stat = $stmt->Execute();
        $row = $stmt->fetchAll();
        if ($row[0]['id'] == '') {
            return 0;
        } else {
            return $row[0]['id'];
        }
    }
}