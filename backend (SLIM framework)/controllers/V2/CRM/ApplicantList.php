<?php

namespace controllers\V2\CRM;

use Carbon\Carbon;
use helpers\Responder;
use models\Internal\ApplicantDetailCcModel;
use models\V2\ApplicantDetailModel as ApplicantDetail;
use models\V2\ApplicantModel as Applicant;
use models\V2\ApplicantFormModel as ApplicantForm;
use models\V2\ApplicantCcCrmModel as ApplicantCc;
use models\V2\OauthUser;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Class ApplicantList
 *
 * @package controllers\V2\CRM
 */
class ApplicantList extends CRMController
{
    public function __construct($app)
    {
        parent::__construct($app);
    }

    /**
     * Listing Application For CRM Dashboard
     *
     * @param Request $request
     * @param Response $response
     * @return Responder
     */
    public function lists(Request $request, Response $response)
    {
        $this->bankID = $request->getAttribute("bank_id", null);
        $startDate = $request->getParam('start_date');
        $endDate = $request->getParam('end_date');
        $status = $request->getParam('status');
        $limit = $request->getParam('limit', 10);

        $data = null;

        if ($startDate && $endDate) {

            // Validate Input Date
            try {
                $startDate = new Carbon($startDate);
                $endDate = new Carbon($endDate);
            } catch (\Exception $e) {
                return Responder::error($response, 'Invalid date format!', 500);
            }

            $data = Applicant::applicantListForCRM($this->bankID, $status, $startDate, $endDate)->paginate($limit, ['*'], 'page', $request->getParam('page'));

        } else {
            if($status == 'verified_otp'){
                $data = Applicant::applicantListForCRM($this->bankID, $status)->paginate(5000, ['*'], 'page', $request->getParam('page'));
            }else{
                $data = Applicant::applicantListForCRM($this->bankID, $status)->paginate($limit, ['*'], 'page', $request->getParam('page'));
            }
        }

        if (!$data) {
            return Responder::error($response, "Data Not Found!", 404);
        }

        $data->map(function ($item, $key) {
            $total_commitment = self::calculateCommitment($item->personal_loan, $item->vehicle_loan, $item->house_loan, $item->credit_card_loan, $item->other_loan);
            $dsr = self::calculateDSR($total_commitment, $item->monthlyincome);
            $item->dsr = $dsr;
            $item->total_commitment = $total_commitment;
            $item->credit_grade_color = self::gradeColor($item->credit_score);
            $item->sla_aging = 0;

            if ($item->status == "internal_reviewed" || $item->app_status == "internal_reviewed") {
                $aging = $this->calculateSLA($item->review_date);
                $item->sla_aging = $aging;
            }

            return $item;
        });

        if($status == 'verified_otp'){
            $appkeys = [];
            foreach($data as $dt) {
                $appkeys[] = [
                    'app_id' => $dt->app_code,
                ];
            }
            $appkeys = array_values(array_unique(collect($appkeys)->pluck('app_id')->toArray()));

            $str = implode("','",$appkeys);
            $join = "'".$str."'";

            $query = DB::select(DB::raw("SELECT app_id FROM app_applicantdocument WHERE app_id IN ($join)"));
        
            $result = [];
            foreach($query as $q) {
                $result[] = $q->app_id;
            }
            $keydoc = array_values(array_unique($result));

            $datadoc = [];
            foreach($data as $dat){
                if(in_array($dat->app_code, $keydoc)){
                    continue;
                }else{
                    $datadoc[] = $dat;
                }
            }
            $pagi = new LengthAwarePaginator($datadoc, count($datadoc), $limit, null);
            $data = $pagi; // Pagination
        }


        return Responder::success($response, $data);

    }

    /**
     * Get Applicant Detail.
     *
     * @param Request $request
     * @param Response $response
     * @return Responder
     */
    public function details(Request $request, Response $response)
    {

        $route = $request->getAttribute('route');
        $appData = Applicant::getDetail($request->getParam('id'), $route->getName());
        if (!$appData || !$request->getParam('id')) {
            return Responder::error($response, 'Applicant Data Not Found', 404);
        }
        $total_commitment = self::calculateCommitment($appData->personal_loan, $appData->vehicle_loan, $appData->house_loan, $appData->credit_card_loan, $appData->other_loan);
        $dsr = self::calculateDSR($total_commitment, $appData->monthly_income);
        $appData->dsr = $dsr;
        $appData->total_commitment = $total_commitment;
        $appData->credit_grade_color = self::gradeColor($appData->credit_score);
        $hash = ApplicantForm::select('hash', 'mime_type')->where('detail_id', $appData->detail_id)->first();
        $appData->emandate = $this->curlecEnable;
        
        $appData->user_documents = null;
        $request = $request->getParsedBody();
        $userDocs = $this->checkDocument($appData->app_id, $request['doc'], $hash);

        if (count($userDocs)) {
            $appData->user_documents = $userDocs;
        }

        if (!$appData->industry) {
            $appData->industry = null;
        }

        $appData->amla_report = self::getAmlaReport($appData->id);

        return Responder::success($response, $appData);

    }

    /**
     * Change Applicant Status
     *
     * @param Request $request
     * @param Response $response
     * @return Responder
     */
    public function changeStatus(Request $request, Response $response)
    {
        $statusValidation = $this->app->validator->validate($request, [
            'status' => v::notEmpty()->in(['internal_reviewed', 'verified_otp', 'processed', 'uncontactable', 'not_interested', 'approved', 'rejected']),
            'detail_id' => v::notEmpty()->each(v::digit())
        ]);

        if ($statusValidation->failed()) {
            return Responder::error($response, null, 422, $statusValidation->getError());
        }


        $request = $request->getParsedBody();
        $detailID = array_unique($request['detail_id']);

        $newStatus = $request['status'];
        if ($request['status'] == 'verified_otp') {
            $newStatus = 'applied';
        }
        ApplicantDetail::whereIN('id', $detailID)->update([
            'status' => $newStatus,
            'status_date' => Carbon::now()
        ]);

        return Responder::success($response, "Applicant Status Updated!");

    }

    public function changePassword(Request $request, Response $response) {
        $request = $request->getParsedBody();
        $model = OauthUser::where('username', $request['username'])->first();

        //If current password different with password provided
        if($model->password != sha1($request['old_password'])){
            return Responder::error($response, null, 422, 'Your current password does not match with the password you provided.'); 
        }
        //If current password and new password are the same
        if($model->password == sha1($request['new_password'])){
            return Responder::error($response, null, 422, 'New Password cannot be same as your current password. Please choose a different password.'); 
        }
        //If new password and confirmation password different
        if($request['new_password'] != $request['confirm_password']){
            return Responder::error($response, null, 422, 'Your Confirmation Password does not match.'); 
        }

        $model->password = sha1($request['new_password']);
        $model->save();

        return Responder::success($response, "Password has been changed!");
    }
}
