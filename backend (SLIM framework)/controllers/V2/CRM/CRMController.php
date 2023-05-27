<?php

namespace controllers\V2\CRM;


use Carbon\Carbon;
use helpers\Cryptor;
use helpers\ElasticMail;
use helpers\JWT;
use models\Internal\ApplicantDocumentModel as ApplicantDocument;
use models\Internal\ApplicantModel as Applicant;
use models\V2\ApplicantFormModel as ApplicantForm;
use models\V2\UploadGfhFileModel;
use models\V2\UploadGfhDisbursedFileModel;
use models\V2\UploadGfhPaymentFileModel;
use models\User\GetAmlaModel;
use Slim\Http\UploadedFile;
use models\V2\BankInfoModel as BankInfo;

class CRMController
{
    /**
     * @var string User token
     */
    protected $token;

    /**
     * @var string|integer Bank ID
     */
    protected $bankID;

    /**
     * @var \Slim\App
     */
    protected $app;

    /**
     * @var String Base URL
     */
    protected $baseURI;


    /**
     * @var Cryptor
     */
    protected $crypto;

    /**
     * @var String File MimeType
     */
    protected $ICMimeType;
    

    /**
     * @var boolean Enable Curlec Emandate Option
     */
    protected $curlecEnable;

    /**
     * @var mixed Bank Info
     */
    protected $bankInfo;


    public function __construct($app)
    {
        global $appConfig;
        $this->baseURI = $appConfig['client_url'];
        $this->app = $app;
        $this->token = JWT::getTokenFromRequest($app->request);

        if ($appConfig['VERSION'] === 2) {

            if (ENVIRONMENT == "DEVELOPMENT") {
                $this->baseURI = $this->baseURI . '/av2/v2';
            }

            if (ENVIRONMENT == "LOCAL") {
                $this->baseURI = $this->baseURI . '/v2';
            }

        }

        $this->crypto = new Cryptor();
        $this->curlecEnable = (isset($appConfig['ENABLE_CURLEC']) ? $appConfig['ENABLE_CURLEC'] : false);
    }

    /**
     * Validate User Token
     *
     * @return bool
     */
    public function validateToken()
    {
        if (!$this->token) return false;

        $checkToken = \models\Misc\OauthToken::where('access_token', $this->token)->first();

        if (!$checkToken) return false;

        $tokenUser = \models\Misc\OauthUser::where('username', $checkToken->user_id)->first();
        $this->bankID = $tokenUser->bank_id;
        $this->bankInfo = BankInfo::where('bank_id', $this->bankID)->first();

        if ($this->bankInfo) {
            // Get curlec option in bank_info table. If null/empty or not exist, return default config in config.php file.
            $this->curlecEnable = (isset($this->bankInfo->curlec_enable) ? $this->bankInfo->curlec_enable : $this->curlecEnable);
        }


        return true;

    }

    /**
     * Get Bank ID Base On User Token
     *
     * @return int|string
     */
    public function getBankID()
    {
        return $this->bankID;
    }


    /**
     * Get grade color based on credit score
     *
     * @param int $credit_score Credit Score
     * @return string Color Hex Code
     */
    public static function gradeColor($credit_score)
    {
        switch (true) {
            case $credit_score >= 600:
                $color = '#39E028';//green
                break;

            case $credit_score >= 400 && $credit_score < 600:
                $color = '#F7FF00';//yellow
                break;

            case $credit_score <= 399:
                $color = '#C9C9C9'; //grey
                break;

            default:
                $color = '#fff';
                break;
        }

        return $color;
    }


    /**
     * Calculate Commitment
     *
     * @param int $personal_loan Personal Loan
     * @param int $vehicle_loan Vehicle Loan
     * @param int $house_loan House Loan
     * @param int $credit_card_loan Credit Card Loan
     * @param int $other_loan Other Loan
     * @return int
     */
    public static function calculateCommitment($personal_loan, $vehicle_loan, $house_loan, $credit_card_loan, $other_loan)
    {
        return $personal_loan + $vehicle_loan + $house_loan + $credit_card_loan + $other_loan;
    }


    /**
     * Calculate DSR
     *
     * @param int $commitment Total Commitment
     * @param int $monthly_income Monthly Income
     * @return false|float
     */
    public static function calculateDSR($commitment, $monthly_income)
    {
        if($monthly_income == 0){
            return 0;
        }
        return round($commitment / $monthly_income * 100, 2);
    }

    /**
     * Check applicant IC
     *
     * @param string $appid Applicant app_id eg: LA_xxxx
     * @param string $type IC_FRONT or IC_BANK
     * @return bool
     */
    public function checkIC($appid, $type)
    {
        $checkIC = ApplicantDocument::where('app_id', $appid)
            ->where('doc_type', $type);
        $icExist = $checkIC->exists();

        if ($icExist) {
            $icData = $checkIC->first();
            $this->ICMimeType = "image";

            if ($icData->mime === 'application/pdf') {
                $this->ICMimeType = "pdf";
            }
        }

        return $icExist;
    }

    /**
     * Check And Get Applicant Document
     *
     * @param string $appid Applicant app_id eg: LA_xxxx
     * @param null|array $docType List of document type
     * @return array|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection|ApplicantDocument[]
     */
    public function checkDocument($appid, $docType = null, $hash = null)
    {
        // generate dynamic url for document
        $link = "$this->baseURI/application/doc?id=";
        $linkprefill = "$this->baseURI/view-form?hash=";

        $returnData = [];
        // dd($hash);
        foreach ($docType as $index => $docItem) {
            $keyURL = "url";
            $keyMimeType = "mime";
            if($docItem == "prefilled_form"){
              if($hash){
                $returnData[$docItem][$keyURL] = $linkprefill.$hash->hash;
                $returnData[$docItem][$keyMimeType] = $hash->mime_type;
              }else{
                $returnData[$docItem][$keyURL] = null;
                $returnData[$docItem][$keyMimeType] = null;
              }
            }else{
              $checkDocument = ApplicantDocument::where('app_id', $appid)->where('doc_type', $docItem)
                ->select('id', 'mime')->get();
                if (!count($checkDocument)) {
                  $returnData[$docItem][$keyURL] = null;
                  $returnData[$docItem][$keyMimeType] = null;
                } else {
                  $checkDocument->map(function ($item, $key) use ($docItem, $keyURL, $keyMimeType, $link, &$returnData) {
                    $returnData[$docItem][$keyURL] = $link . $this->crypto->encrypt($item->id);
                    $returnData[$docItem][$keyMimeType] = $item->mime;
                    return $returnData;
                  });
                }
            }

        }
        return $returnData;
    }

    /**
     * Get Amla Report
     *
     * @param int $appId Applicant ID
     * @return array|null
     */
    public static function getAmlaReport($appId)
    {
        $appCode = Applicant::find($appId);
        if (!$appCode) {
            return null;
        }

        $appCode = $appCode->app_id;
        $amlaData = GetAmlaModel::where('app_id', $appCode)->first();

        if (!$amlaData) {
            return null;
        }

        $xml = simplexml_load_string($amlaData->response_data_3, "SimpleXMLElement", LIBXML_NOCDATA);
        $parseJson = json_decode(json_encode($xml), true);
        return (count($parseJson['cddi']['search_result']['profile_lists']) ? $parseJson['cddi']['search_result']['profile_lists'] : null);

    }

    /**
     * Move Uploaded File To Storage Folder
     *
     * @param UploadedFile $uploadedFile
     * @return string File Path
     * @throws \Exception
     */
    public function moveUploadedFile(UploadedFile $uploadedFile)
    {
        $directory = './storage/crm-upload';
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        $basename = bin2hex(random_bytes(8));
        $filename = sprintf('%s.%0.8s', $basename, $extension);

        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

        return "$directory/$filename";
    }

    public function moveUploadedGohalalFile(UploadedFile $uploadedFile, $credit_house)
    {
        $file_date1 = date('Y-m-d');
        $file_date2 = date('dmy');
        $uploadModel = UploadGfhFileModel::where('credit_house', $credit_house)
        ->where('file_date', $file_date1)
        ->orderBy('seq', 'desc')
        ->first();
        $seq = 1;
        if ($uploadModel) {
            $seq = $uploadModel->seq + 1;
        }
        $directory = './gohalal/inprocess';
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        
        $filename = sprintf('%s_Approved_List_%s_%02d.%s', $credit_house, $file_date2, $seq, $extension);
        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);
        $newUploadModel = new UploadGfhFileModel();
        $newUploadModel->seq = $seq;
        $newUploadModel->file_upload = $filename;
        $newUploadModel->credit_house = $credit_house;
        $newUploadModel->file_date = $file_date1;
        $newUploadModel->is_running = 0;
        $newUploadModel->save();
        $data['path'] = "$directory/$filename";
        $data['id'] = $newUploadModel->id;
        return $data;
    }

    public function moveUploadedGohalalDisbursedFile(UploadedFile $uploadedFile, $credit_house)
    {
        $file_date1 = date('Y-m-d');
        $file_date2 = date('dmy');
        $uploadModel = UploadGfhDisbursedFileModel::where('credit_house', $credit_house)
        ->where('file_date', $file_date1)
        ->orderBy('seq', 'desc')
        ->first();
        $seq = 1;
        if ($uploadModel) {
            $seq = $uploadModel->seq + 1;
        }
        $directory = './gohalal/inprocess';
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        
        $filename = sprintf('%s_Disbursed_List_%s_%02d.%s', $credit_house, $file_date2, $seq, $extension);
        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);
        $newUploadModel = new UploadGfhDisbursedFileModel();
        $newUploadModel->seq = $seq;
        $newUploadModel->file_upload = $filename;
        $newUploadModel->credit_house = $credit_house;
        $newUploadModel->file_date = $file_date1;
        $newUploadModel->is_running = 0;
        $newUploadModel->save();
        return $newUploadModel;
    }

    public function moveUploadedGohalalPaymentFile(UploadedFile $uploadedFile)
    {
        $file_date1 = date('Y-m-d');
        $file_date2 = date('dmy');
        $uploadModel = UploadGfhPaymentFileModel::where('file_date', $file_date1)
        ->orderBy('seq', 'desc')
        ->first();
        $seq = 1;
        if ($uploadModel) {
            $seq = $uploadModel->seq + 1;
        }
        $directory = './gohalal/inprocess';
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        
        $filename = sprintf('Payment_List_%s_%02d.%s', $file_date2, $seq, $extension);
        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);
        $newUploadModel = new UploadGfhPaymentFileModel();
        $newUploadModel->seq = $seq;
        $newUploadModel->file_upload = $filename;
        $newUploadModel->file_date = $file_date1;
        $newUploadModel->is_running = 0;
        $newUploadModel->save();
        return $newUploadModel;
    }

    /**
     * Calculate SLA
     *
     * @param string $reviewDate Applicant Internal Review Date
     * @return int
     */
    public function calculateSLA($reviewDate)
    {
        $now = Carbon::now();
        $reviewDate = new Carbon($reviewDate);
        $aging = $reviewDate->diffInDays($now);
        return $aging;
    }

    /**
     * @param string $bankName Bank Name
     * @param string $bankEmail Bank Email
     * @param int $aging Aging Day
     * @param string $expiryDate Expiry Date
     * @param string $applicantName Applicant Name
     * @return bool
     * @throws \Exception
     */
    public static function sendEmail($bankName, $bankEmail, $aging, $expiryDate, $applicantName)
    {
        $elasticMail = New ElasticMail();

        $subject = "Pending Applicant Reminder";

        if ($aging == 7) {
            $subject = "Pending Applicant Last Reminder";
        }


        return $elasticMail->send([
            'from' => 'sarah@assidq.com',
            'fromName' => 'Sarah from Assidq',
            'to' => $bankEmail,
            'subject' => $subject,
            'template' => "emailreminder",
            'merge_bank_name' => $bankName,
            'merge_agingday' => $aging,
            'merge_expirydate' => $expiryDate,
            'merge_applicant_name' => $applicantName
        ]);

    }


}
