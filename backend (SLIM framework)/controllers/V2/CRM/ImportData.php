<?php

namespace controllers\V2\CRM;

use Carbon\Carbon;
use helpers\Responder;
use models\V2\ApplicantDetailModel as ApplicantDetail;
use models\V2\ProductPfModel as ProductPf;
use models\V2\BankInfoModel as BankInfo;
use models\V2\ApplicantModel as Applicant;
use models\V2\GfhDetailsModel as ApplicantGohalal;
use models\V2\TakafulGohalalModel as TakafulGohalal;
use models\V2\UploadGfhFileModel;
use models\V2\UploadGfhDisbursedFileModel;
use models\V2\UploadGfhPaymentFileModel;
use models\Common\CommonModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;
use helpers\MyKad;
use libraries\Model as OldModel;
use helpers\JWT;
use controllers\V2\GoHalal\GoHalalController;

/**
 * Class ImportData
 *
 * @package controllers\V2\CRM
 */
class ImportData extends CRMController
{

    protected $app;

    protected $fileData;

    public function __construct($app)
    {
        parent::__construct($app);
        $this->app = $app;

        $this->goHalalCont = new GoHalalController($app);
    }

    /**
     * Upload excel file and parsing User uploaded data
     *
     * @param Request $request
     * @param Response $response
     * @return Responder
     * @throws \Exception
     */
    public function upload(Request $request, Response $response)
    {
        if (!$this->validateToken()) {
            return Responder::error($response, "Invalid Token!", 401);
        }

        $uploadedFiles = $request->getUploadedFiles();

        if (empty($uploadedFiles['file'])) {
            return Responder::error($response, null, 422, "Invalid Excel File !");
        }

        $uploadedFile = $uploadedFiles['file'];

        $fileValidationResult = v::file()->oneOf(
            v::mimetype('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
            v::mimetype('application/vnd.ms-excel'))
            ->size(null, '1MB')
            ->validate($uploadedFile->file);

        if (!$fileValidationResult) {
            return Responder::error($response, null, 422, "File must be valid excel file and maximum 1MB size");
        }

        $excelFile = $this->moveUploadedFile($uploadedFile);
        try {
            $reader = IOFactory::createReaderForFile($excelFile);
            $reader->setReadDataOnly(true);

            $spreadsheet = $reader->load($excelFile);
            $data = $spreadsheet->getActiveSheet()->toArray();

            $allowedStatusCode = [
                1 => 'internal_reviewed',
                2 => 'verified_otp',
                3 => 'processed',
                4 => 'uncontactable',
                5 => 'not_interested',
                6 => 'approved',
                7 => 'rejected',
            ];

            $processedCount = 0;
            $processedApplicant = [];
            $failedCount = 0;
            $failedApplicant = [];

            foreach ($data as $index => $item) {
                if (count($item) != 25) {
                    return Responder::error($response, null, 422, "New Status Column Not Found!");
                }

                $appID = $item[0];
                $newStatus = $item[24];
                if ($item[24] == "New Status") {
                    continue;
                }

                if (isset($allowedStatusCode[$newStatus])) {
                    $applicant = Applicant::where('app_id', $appID);

                    if ($applicant->exists()) {
                        $applicantData = $applicant->first();

                        ApplicantDetail::where('applicantid', $applicantData->id)->update([
                            'status' => $allowedStatusCode[$newStatus],
                            'status_date' => Carbon::now()
                        ]);

                        $processedCount++;
                        array_push($processedApplicant, $appID);
                    } else {
                        $failedCount++;
                        array_push($failedApplicant, $appID);
                    }

                } else {
                    $failedCount++;
                    array_push($failedApplicant, $appID);
                }
            }

        } catch (Exception $e) {
            return Responder::error($response, "Internal Server Error, Please Try Again Later");
        } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            return Responder::error($response, "Internal Server Error, Please Try Again Later");
        }


        $result = [
            "processed" => [
                "total" => $processedCount,
                "applicant" => $processedApplicant
            ],
            "failed" => [
                "total" => $failedCount,
                "applicant" => $failedApplicant
            ]
        ];

        return Responder::success($response, $result);
    }

    public function uploadGoHalal(Request $request, Response $response)
    {
        if (!$this->validateToken()) {
            return Responder::error($response, "Invalid Token!", 401);
        }

        $uploadedFiles = $request->getUploadedFiles();

        if (empty($uploadedFiles['file'])) {
            return Responder::error($response, null, 422, "Invalid Excel File !");
        }

        $uploadedFile = $uploadedFiles['file'];

        $fileValidationResult = v::file()->oneOf(
            v::mimetype('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
            v::mimetype('application/vnd.ms-excel'))
            ->size(null, '1MB')
            ->validate($uploadedFile->file);

        if (!$fileValidationResult) {
            return Responder::error($response, null, 422, "File must be valid excel file and maximum 1MB size");
        }

        $credit_house = $this->bankInfo->code;
        $excelFile = $this->moveUploadedGohalalFile($uploadedFile, $credit_house);
        sleep(1);
        return $this->runningUploadGoHalal($request, $response);
        // return Responder::success($response, $excelFile);
    }

    public function uploadGoHalalDisbursed(Request $request, Response $response)
    {
        if (!$this->validateToken()) {
            return Responder::error($response, "Invalid Token!", 401);
        }

        $uploadedFiles = $request->getUploadedFiles();

        if (empty($uploadedFiles['file'])) {
            return Responder::error($response, null, 422, "Invalid Excel File !");
        }

        $uploadedFile = $uploadedFiles['file'];

        $fileValidationResult = v::file()->oneOf(
            v::mimetype('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
            v::mimetype('application/vnd.ms-excel'))
            ->size(null, '1MB')
            ->validate($uploadedFile->file);

        if (!$fileValidationResult) {
            return Responder::error($response, null, 422, "File must be valid excel file and maximum 1MB size");
        }

        $credit_house = $this->bankInfo->code;
        $excelFile = $this->moveUploadedGohalalDisbursedFile($uploadedFile, $credit_house);
        return $this->runningUploadGoHalalDisbursed($excelFile->id, $request, $response);
    }

    public function uploadGoHalalPayment(Request $request, Response $response)
    {
        $internalUser = JWT::user($request);
        if (!$internalUser) {
            return Responder::error($response, "Token Invalid!", 401);
        }

        $uploadedFiles = $request->getUploadedFiles();

        if (empty($uploadedFiles['file'])) {
            return Responder::error($response, null, 422, "Invalid Excel File !");
        }

        $uploadedFile = $uploadedFiles['file'];

        $fileValidationResult = v::file()->oneOf(
            v::mimetype('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
            v::mimetype('application/vnd.ms-excel'))
            ->size(null, '1MB')
            ->validate($uploadedFile->file);

        if (!$fileValidationResult) {
            return Responder::error($response, null, 422, "File must be valid excel file and maximum 1MB size");
        }

        $credit_house = $this->bankInfo->code;
        $excelFile = $this->moveUploadedGohalalPaymentFile($uploadedFile);
        return $this->runningUploadGoHalalPayment($excelFile->id, $request, $response);
    }

    private function fieldValidation($valType, $val, $bankId) {
        if ($valType == 'notNull') {
            if ($val == "") {
                return false;
            } else if ($val == 0) {
                return true;
            }
            return $val? true: false;
        } else if ($valType == 'decimal') {
            return is_numeric($val);
        } else if ($valType == 'integer') {
            return is_numeric($val) && (floor($val) == $val);
        } else if ($valType == 'statusReviewed') {
            return strtolower($val) == 'reviewed';
        } else if ($valType == 'boolean') {
            return in_array(strtolower($val), ["1", "0", "y", "n", "yes", "no"]);
        } else if ($valType == 'postCode') {
            return is_numeric($val) && strlen($val)>=4 && strlen($val)<=5;
        } else if ($valType == 'mobileValid') {
            return is_numeric($val) && strlen($val)>=9 && strlen($val)<=12;
        } else if ($valType == 'salaryDateValid') {
            return is_numeric($val) && $val>=1 && $val<=31;
        } else if ($valType == 'sumCoveredValid') {
            return is_numeric($val) && in_array($val, [0, 10000, 20000, 30000, 40000]);
        } else if ($valType == 'emailValid') {
            return filter_var($val, FILTER_VALIDATE_EMAIL)? true: false;
        } else if ($valType == 'productValid') {
            $check = ProductPf::where('prod_id', $val)->where('bank_id', $bankId)->first();
            return $check? true : false;
        } else if ($valType == 'appIdValid') {
            if (!$val) {
                return true;
            }
            $check = Applicant::where('app_id', $val)->first();
            return $check? true : false;
        } else if ($valType == 'icValid') {
            if (!(is_numeric($val) && strlen($val)==12)) {
                return false;
            }
            return checkdate(substr($val,2,2), substr($val,4,2), "20".substr($val,0,2));
        }
    }

    public function runningUploadGoHalal(Request $request, Response $response)
    {
        // return Responder::error($response, $this->fileData, 401);
        $uploadModel = UploadGfhFileModel::where('is_running', 0)
        ->orderBy('file_date', 'asc')
        ->orderBy('seq', 'asc')
        ->first();
        $directory = './gohalal/inprocess';
        if (!$uploadModel) {
            $this->app->logger->addInfo("Running Excel Error: No data match");
            return Responder::error($response, null, 404, "No data match");
        }
        $credit_house = $uploadModel->credit_house;
        $bankInfo = BankInfo::where('code', $credit_house)->first();
        $filename = $uploadModel->file_upload;
        $excelFile = $directory . DIRECTORY_SEPARATOR . $filename;
        if (!file_exists($excelFile)) {
            $uploadModel->remark = "File not found";
            $uploadModel->is_running = 2;
            $uploadModel->save();
            $this->app->logger->addInfo("Running Excel Error: File not found");
            return Responder::error($response, null, 404, "File not found ");
        }
        try {
            $reader = IOFactory::createReaderForFile($excelFile);
            $reader->setReadDataOnly(true);

            $spreadsheet = $reader->load($excelFile);
            $data = $spreadsheet->getActiveSheet()->toArray();

            $processedCount = 0;
            $processedApplicant = [];
            $failedCount = 0;
            $failedApplicant = [];
            $i = 0;
            $excelColumn = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK'];
            $validations = [
                0 => ["field" => ['reference id', 'reference_id'], "message" => "reference_id", "validation" => ["notNull"]], // A
                1 => ["field" => ['app id', 'app_id', 'app_code', 'app code'], "message" => "app_id", "validation" => ["appIdValid"]], // B
                2 => ["field" => ['product id', 'product_id'], "message" => "product_id", "validation" => ["notNull", "productValid"]], // C
                3 => ["field" => ['name', 'full_name', 'full name', 'customer_name', 'customer name'], "message" => "customer_name", "validation" => ["notNull"]], // D
                4 => ["field" => ['ic', 'ic_no', 'ic no'], "message" => "ic_no", "validation" => ["notNull", "icValid"]], // E
                5 => ["field" => ['mobile no', 'mobile_no', 'phone_no', 'phone no'], "message" => "mobile_no", "validation" => ["notNull", "mobileValid"]], // F
                6 => ["field" => ['email', 'email_address', 'email address'], "message" => "email", "validation" => ["notNull", "emailValid"]], // G
                7 => ["field" => ['financing amount', 'financing_amount'], "message" => "financing_amount", "validation" => ["notNull", "decimal"]], // H
                8 => ["field" => ['profit rate', 'profit_rate'], "message" => "profit_rate", "validation" => ["notNull", "decimal"]], // I
                9 => ["field" => ['profit amount', 'profit_amount'], "message" => "profit_amount", "validation" => ["notNull", "decimal"]], // J
                10 => ["field" => ['monthly income', 'monthly_income'], "message" => "monthly_income", "validation" => ["notNull", "decimal"]], // K
                11 => ["field" => ['monthly installment', 'monthly_installment'], "message" => "monthly_installment", "validation" => ["notNull", "decimal"]], // L
                12 => ["field" => ['final installment', 'final_installment'], "message" => "final_installment", "validation" => ["notNull", "decimal"]], // M
                13 => ["field" => ['tenure', 'tenure'], "message" => "tenure", "validation" => ["notNull", "integer"]], // N
                14 => ["field" => ['salary date', 'salary_date'], "message" => "salary_date", "validation" => ["notNull", "salaryDateValid"]], // O
                15 => ["field" => ['first deduction date', 'first_deduction_date'], "message" => "first_deduction_date", "validation" => ["notNull"]], // P
                16 => ["field" => ['aml clearance', 'aml_clearance'], "message" => "aml_clearance", "validation" => ["notNull", "boolean"]],
                17 => ["field" => ['health declaration', 'health_declaration'], "message" => "health_declaration", "validation" => ["notNull", "boolean"]],
                18 => ["field" => ['pdpa declaration', 'pdpa_declaration'], "message" => "pdpa_declaration", "validation" => ["notNull", "boolean"]],
                19 => ["field" => ['e-mandate', 'e_mandate'], "message" => "e_mandate", "validation" => ["notNull"]],
                20 => ["field" => ['sum covered', 'sum_covered'], "message" => "sum_covered", "validation" => ["notNull", "decimal", "sumCoveredValid"]],
                21 => ["field" => ['application status', 'application_status', 'applications status', 'applications_status', 'status'], "message" => "applications_status", "validation" => ["notNull", "statusReviewed"]],
                22 => ["field" => ['address 1', 'address1'], "message" => "address1", "validation" => ["notNull"]],
                23 => ["field" => ['address 2', 'address2'], "message" => "address2", "validation" => ["notNull"]], 
                25 => ["field" => ['postal code', 'postal_code', 'post code', 'post_code'], "message" => "postal_code", "validation" => ["notNull", "postCode"]],
                26 => ["field" => ['city'], "message" => "city", "validation" => ["notNull"]],
                27 => ["field" => ['state'], "message" => "state", "validation" => ["notNull"]],
                28 => ["field" => ['nett disbursement amount', 'nett_disbursement_amount'], "message" => "nett_disbursement_amount", "validation" => ["notNull", "decimal"]],
            ];

            //duplicate reffID checking
            foreach ($data as $keydata => $itemdata) {
                if($keydata == 0 || $itemdata[0] == ' ' || $itemdata[0] == null){
                    continue;
                }
                $datas1[$keydata] = $itemdata[0];
            }
            $uarr = array_unique($datas1);
            $datadupli = array_diff($datas1, array_diff($uarr, array_diff_assoc($datas1, $uarr)));
            $datas = array_unique($datadupli);

            foreach ($data as $index => $item) {
                $remark = "";
                if ($i == 0) {
                    //check fields
                    foreach($validations as $key => $val) {
                        if (!in_array(strtolower(trim($item[$key])), $val["field"])) {
                            $uploadModel->remark = "Excel column ".($excelColumn[$key])." must be ".$val["message"]." : ". $item[$key];
                            $uploadModel->is_running = 2;
                            $uploadModel->save();
                            $this->app->logger->addInfo("Running Excel Error: Excel column ".($excelColumn[$key])." must be ".$val["message"]." : ". $item[$key]);
                            return Responder::error($response, null, 422, "Excel column ".($excelColumn[$key])." must be ".$val["message"]." : ". $item[$key]);
                        }
                    }
                    $i++;
                    continue; //baris 1 untuk header
                }
                $i++;
                if (count($item) <= 28) {
                    $uploadModel->remark = "Column Count not Match: ". count($item);
                    $uploadModel->is_running = 2;
                    $uploadModel->save();
                    $this->app->logger->addInfo("Running Excel Error: Column Count not Match!");
                    return Responder::error($response, null, 422, "Column Count not Match!");
                }
                $reffID = $item[0];
                $dataValid = true;
                if ($reffID && !in_array($reffID, $datas)) {
                    foreach($validations as $key => $val) {
                        foreach($val["validation"] as $itemsCheck) {
                            if (!$this->fieldValidation($itemsCheck, trim($item[$key]), $bankInfo->bank_id)) {
                                $remark = $val["message"]." (column ".($excelColumn[$key]).") must be ".$itemsCheck." : ". $item[$key];
                                $dataValid = false;
                                continue;
                            } 
                        }
                    }
                    if ($dataValid) {
                        $applicantData = ApplicantGohalal::where('app_id', $item[1])
                        ->where('reference_id', $item[0])
                        ->first();
                        if (!$applicantData) {
                            $applicantData = new ApplicantGohalal();
                        } 
                        $applicantData->reference_id = $item[0];
                        $applicantData->app_id = $item[1]? $item[1]: null;
                        $applicantData->product_id = $item[2];
                        $applicantData->profit_rate = $item[8];
                        $applicantData->profit_amount = $item[9];
                        $applicantData->monthly_installment = $item[11];
                        $applicantData->final_installment = $item[12];
                        $applicantData->salary_date = $item[14];
                        $applicantData->first_deduction_date = date("Y-m-d", strtotime($item[15]));
                        $applicantData->aml_clearance = in_array(strtoupper($item[16]), ['YES', 'Y', '1'])? 1 : 0;
                        $applicantData->health_declaration = in_array(strtoupper($item[17]), ['YES', 'Y', '1'])? 1 : 0;
                        $applicantData->pdpa_declaration = in_array(strtoupper($item[18]), ['YES', 'Y', '1'])? 1 : 0;
                        $applicantData->e_mandate = in_array(strtoupper($item[19]), ['YES', 'Y', '1'])? 1 : 0;
                        $applicantData->sum_covered = $item[20];
                        $applicantData->app_status = $item[21];
                        $applicantData->address1 = $item[22];
                        $applicantData->address2 = $item[23];
                        $applicantData->address3 = $item[24];
                        $applicantData->post_code = $item[25];
                        $applicantData->city = $item[26];
                        $applicantData->state = $item[27];
                        $applicantData->nett_disbursement_amount = $item[28];
                        $applicantData->wakalah_fee = is_null($item[29])? 0 : $item[29];
                        $applicantData->security_deposit = is_null($item[30])? 0 : $item[30];
                        $applicantData->tax = is_null($item[31])? 0 : $item[31];
                        $applicantData->stamp_duty = is_null($item[32])? 0 : $item[32];
                        $applicantData->age_apply = $this->calculateAgeIc($item[4]);
                        $applicantData->takaful_eligibility = $this->eligibilityCheck($applicantData->aml_clearance, $applicantData->health_declaration, $applicantData->age_apply, $item[10]);
                        $applicantData->gender = \substr(MyKad::gender($item[4]), 0, 1);
                        $score = TakafulGohalal::score($applicantData->age_apply, $applicantData->gender, $applicantData->sum_covered); 
                        if ($score && $applicantData->takaful_eligibility == 1) {
                            $applicantData->monthly_takaful_premium = $score->score;
                            $applicantData->takaful_plan = $score->plan;
                        }
                        try {
                            $applicantData->save(); 
                            $exist = false;
                            if ($item[1]) {
                                $cekApplicant = Applicant::where('app_id', $item[1])->first();
                                if ($cekApplicant) {
                                    $appApplicant = $cekApplicant;
                                    $exist = true;
                                } else {
                                    $failedCount++;
                                    array_push($failedApplicant, ['app_id' => $appID, 'reason' => 'App_ID not found']);
                                }
                            } else {
                                $appApplicant = new Applicant();
                                $appApplicant->src_channel = 'ghf';
                                $appApplicant->last_successful_page = 'completed';
                            }

                            if (!$applicantData->app_id) {
                                $oldModel = new OldModel();
                                $eventId = $oldModel->genEventId();
                                $appid = 'LA' . "_" . $eventId;
                                $app_key = md5('$' . $appid . '$' . date('YmdHis') . $appid);
                            } else {
                                $appid = $applicantData->app_id;
                                $app_key = md5('$' . $appid . '$' . date('YmdHis') . $appid);
                            }
                            if ($appApplicant) {
                                $appApplicant->app_id = $appid;
                                $appApplicant->app_key = $app_key;
                                $appApplicant->reference_id = $applicantData->reference_id;
                                $appApplicant->name = trim($item[3]);
                                $appApplicant->ic_type = 'new-ic';
                                $appApplicant->ic_no = $item[4];
                                $appApplicant->phoneno = $item[5];
                                $appApplicant->email = trim(strtolower($item[6]));
                                $appApplicant->amount = $item[7];
                                $appApplicant->gross_salary = $item[10];
                                $appApplicant->installment_max = $item[11];
                                $appApplicant->tenure = $item[13];
                                $appApplicant->states = $applicantData->state;
                                $appApplicant->credit_grade = 0;
                                $appApplicant->credit_score = 0;
                                $appApplicant->dob = $this->calculateDOB($item[4]);
                                $appApplicant->age_apply = $applicantData->age_apply;
                                $appApplicant->save();
                                $applicantData->app_id = $appApplicant->app_id;
                                $applicantData->lo_status = 1;
                                $applicantData->save();

                                if ($exist) {
                                    $appDetail = ApplicantDetail::where('applicantid', $appApplicant->id)->first();
                                    if (!$appDetail) {
                                        $appDetail = new ApplicantDetail();
                                        $appDetail->applicantid = $appApplicant->id;
                                    }
                                } else {
                                    $appDetail = new ApplicantDetail();
                                    $appDetail->applicantid = $appApplicant->id;
                                }
                                $appDetail->bank_id = $bankInfo->bank_id;
                                $appDetail->prod_id = $item[2];
                                $appDetail->status = 'ghf_reviewed';
                                $appDetail->save();
                            } 
                            // put emandate condition here
                            $productpf = ProductPf::where('prod_id',$item[2])->first();
                            //checking emandate enable at product level and checking emandate at per application level
                            if ($productpf->emandate_enable == 1 && $applicantData->e_mandate == 1) {
                                if ($this->goHalalCont->generateEmandateGohalal($appApplicant)) {
                                    $appDetail->status = 'ghf_pending_emandate1';
                                }
                            } else {
                                //note: if emandate at product level is 0/no, the application will always go here even emandate at app level is 1/yes
                                $appDetail->status = 'ghf_emandate1_skip';                                
                            }
                            $appDetail->save();
                        } catch (\Exception $e) {
                            if ($e->getCode() == 23000) {
                                $remark = 'Duplicate Reference ID';
                                array_push($failedApplicant, [$reffID => $remark]);
                                continue;
                            }
                        }
                        $processedCount++;
                        array_push($processedApplicant, $reffID);
                    } else {
                        $failedCount++;
                        array_push($failedApplicant, [$reffID => $remark]);
                    }
                }
            }

            if (!empty($datas)) {
                $dup['duplicate'] = $datas;
                return Responder::error($response, "uploaderror", 422, $dup);
            }

        } catch (Exception $e) {
            return Responder::error($response, "Internal Server Error, Please Try Again Later");
        } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            return Responder::error($response, "Internal Server Error, Please Try Again Later");
        }


        $result = [
            "processed" => [
                "total" => $processedCount,
                "applicant" => $processedApplicant
            ],
            "failed" => [
                "total" => $failedCount,
                "applicant" => $failedApplicant
            ]
        ];
        $uploadModel->remark = \json_encode($result);
        $uploadModel->is_running = 1;
        $uploadModel->save();
        $oldDirectory = './gohalal/upload/inprocess';
        $newDirectory = './gohalal/upload/history';
        if (copy($oldDirectory . DIRECTORY_SEPARATOR . $filename, $newDirectory . DIRECTORY_SEPARATOR . $filename)) {
            unlink($oldDirectory . DIRECTORY_SEPARATOR . $filename);
        }
        return Responder::success($response, $result);
    }

    public function calculateDOB($ic_no, $boolDetails = false)
    {
        if (strlen($ic_no) != 12) {
            return null; // Now $ic_no is free text
        }
        $year = substr($ic_no, 0, 2);
        if($year<=date('y')) {
            $year = '20'. $year;
        } else {
            $year = '19'. $year;
        }
        $month = substr($ic_no, 2, 2);
        $day = substr($ic_no, 4, 2);
        $dob = "$year-$month-$day";
        $validDate = strtotime($dob);
        if (!$validDate) {
            return null;
        }
        return $dob; // Now $ic_no is free text
    }

    public function calculateAgeIc($ic_no, $boolDetails = false)
    {
        if (strlen($ic_no) != 12) {
            return null; // Now $ic_no is free text
        }

        $year = substr($ic_no, 0, 2);
        $month = substr($ic_no, 2, 2);
        $day = substr($ic_no, 4, 2);
        if($year<=date('y')) {
            $year = '20'. $year;
        } else {
            $year = '19'. $year;
        }

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
        return null; // Now $ic_no is free text
    }

    public function eligibilityCheck($aml_clearance, $health_declaration, $age, $monthly_income) {
        if ($aml_clearance && $health_declaration && $age >= 19 && $age <= 55 && $monthly_income <= 5000) {
            return 1;
        } else {
            return 0;
        }
    }

    private function excelDateToDate($readDate){
        $phpexcepDate = $readDate-25569; //to offset to Unix epoch
        return strtotime("+$phpexcepDate days", mktime(0,0,0,1,1,1970));
    }
}
