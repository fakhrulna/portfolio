<?php
namespace models\V2;

use Carbon\Carbon;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model;
use models\Internal\AppSmsGenModel;
use models\V2\ApplicantDetailModel;
use models\V2\GfhDetailsModel;

class ApplicantModel extends Model
{
    protected $table = 'app_applicant';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $hidden = [
        'tnc', 'tnc_metadata', 'tnc_text', 'pp_c'
    ];

    protected $fillable = [
        'credit_grade',
        'prob_default',
        'credit_score',
        'test_flag',
        'dropoff_notif_counter', 
        'dropoff_timestamp', 
        'dropoff_vip_counter',
        'dropoff_vip_timestamp'
    ];

    public function ghfDetails()
    {
        return $this->belongsTo(GfhDetailsModel::class, 'app_id', 'app_id');
    }

    public function appDetails()
    {
        return $this->belongsTo(ApplicantDetailModel::class, 'id', 'applicantid');
    }

    /**
     * Get list of review application
     *
     * @param null|string $startDate Start Date
     * @param null|string $endDate End Date
     * @return \Illuminate\Database\Query\Builder
     */
    public static function getReviewList($startDate = null, $endDate = null)
    {
        $data = DB::table('app_applicant AS a')
            ->join('app_applicantdetail AS d', 'd.applicantid', 'a.id')
            ->join('app_applicantdocument AS g', 'g.app_id', 'a.app_id')
            ->join('product_pf AS p', 'p.prod_id', 'd.prod_id')
            ->join('bank_info AS f', 'f.bank_id', 'p.bank_id')
            ->select('a.id', 'd.id as detail_id', 'a.app_id', 'a.NAME as name', 'a.email', 'a.phoneno', 'a.age_apply',
                'a.gross_salary', 'a.amount', 'a.tenure',
                'a.sector', 'a.ic_no', 'd.STATUS as status', 'f.bank_name', 'p.prod_id as product_id',
                'p.title as product_name', 'a.create_date', 'd.status', 'd.read_by_internal', 'a.is_verified_phone', 'a.flag_match', 'd.salvage', 'a.test_flag')
            ->where('d.CHECK', 1)
            ->whereIn('d.STATUS', ['applied', 'created'])
            ->whereIn('g.doc_type', ['ic_front', 'ic_back', 'payslip', 'epf'])
            ->orderBy('d.create_date', 'DESC') // Modify in IF-726 sort by create_date in app_applicantdetail table
            ->distinct();

        /* Date Range Filter */
        if ($startDate && $endDate && $data) {
            $startDate = new Carbon($startDate);
            $endDate = new Carbon($endDate);

            $data->where('a.create_date', '>=', $startDate->toDateTimeString())
                ->where('a.create_date', '<=', $endDate->endOfDay()->toDateTimeString());
        }

        return $data;

    }

    public static function getReviewListV3($startDate = null, $endDate = null)
    {
        $data = DB::table('app_applicant AS a')
            ->join('app_applicantdetail AS d', 'd.applicantid', 'a.id')
            // ->join('app_applicantdocument AS g', 'g.app_id', 'a.app_id')
            ->join('product_pf AS p', 'p.prod_id', 'd.prod_id')
            ->join('bank_info AS f', 'f.bank_id', 'p.bank_id')
            ->select('a.id', 'd.id as detail_id', 'a.app_id', 'a.NAME as name', 'a.email', 'a.phoneno', 'a.age_apply',
                'a.gross_salary', 'a.amount', 'a.tenure',
                'a.sector', 'a.ic_no', 'd.STATUS as status', 'f.bank_name', 'p.prod_id as product_id',
                'p.title as product_name', 'a.create_date', 'd.status', 'd.read_by_internal', 'a.is_verified_phone', 'a.flag_match', 'd.salvage', 'a.test_flag')
            ->where('d.CHECK', 1)
            ->whereIn('d.STATUS', ['applied', 'created'])
            // ->whereIn('g.doc_type', ['ic_front', 'ic_back', 'payslip', 'epf'])
            ->orderBy('d.create_date', 'DESC') // Modify in IF-726 sort by create_date in app_applicantdetail table
            ->distinct();

        /* Date Range Filter */
        if ($startDate && $endDate && $data) {
            $startDate = new Carbon($startDate);
            $endDate = new Carbon($endDate);

            $data->where('a.create_date', '>=', $startDate->toDateTimeString())
                ->where('a.create_date', '<=', $endDate->endOfDay()->toDateTimeString());
        }

        return $data;

    }

    /**
     * Get list of reviewed application
     *
     * @param null|string $startDate Start Date
     * @param null|string $endDate End Date
     * @return \Illuminate\Database\Query\Builder
     */
    public static function getReviewedList($startDate = null, $endDate = null, $old = false)
    {
        $data = DB::table('app_applicant AS a')
            ->join('app_applicantdetail AS d', 'd.applicantid', 'a.id')
            ->join('app_applicantdocument AS g', 'g.app_id', 'a.app_id')
            ->join('product_pf AS p', 'p.prod_id', 'd.prod_id')
            ->join('bank_info AS f', 'f.bank_id', 'p.bank_id')
            ->select('a.id', 'd.id as detail_id', 'a.app_id', 'a.NAME as name', 'a.email', 'a.phoneno', 'a.age_apply',
                'a.gross_salary', 'a.amount', 'a.tenure',
                'a.sector', 'a.ic_no', 'd.STATUS as status', 'f.bank_name', 'p.prod_id as product_id',
                'p.title as product_name', 'a.create_date', 'd.status', 'd.read_by_internal', 'a.is_verified_phone', 'a.flag_match', 'd.salvage', 'a.test_flag')
            ->where('d.CHECK', 1)
            ->whereIn('d.STATUS', ['internal_reviewed', 'internal_rejected', 'internal_kiv', 'cra_pass', 'cra_fail'])
            ->whereIn('g.doc_type', ['ic_front', 'ic_back', 'payslip', 'epf'])
            ->orderBy('d.create_date', 'DESC') // Modify in IF-726 sort by create_date in app_applicantdetail table
            ->distinct();

        /* Date Range Filter */
        if ($startDate && $endDate && $data) {
            $startDate = new Carbon($startDate);
            $endDate = new Carbon($endDate);

            $data->where('a.create_date', '>=', $startDate->toDateTimeString())
                ->where('a.create_date', '<=', $endDate->endOfDay()->toDateTimeString());
        }

        return $data;
    }

    public static function getReviewedListV3($startDate = null, $endDate = null, $old = false)
    {
        $data = DB::table('app_applicant AS a')
            ->join('app_applicantdetail AS d', 'd.applicantid', 'a.id')
            // ->join('app_applicantdocument AS g', 'g.app_id', 'a.app_id')
            ->join('product_pf AS p', 'p.prod_id', 'd.prod_id')
            ->join('bank_info AS f', 'f.bank_id', 'p.bank_id')
            ->select('a.id', 'd.id as detail_id', 'a.app_id', 'a.NAME as name', 'a.email', 'a.phoneno', 'a.age_apply',
                'a.gross_salary', 'a.amount', 'a.tenure',
                'a.sector', 'a.ic_no', 'd.STATUS as status', 'f.bank_name', 'p.prod_id as product_id',
                'p.title as product_name', 'a.create_date', 'd.status', 'd.read_by_internal', 'a.is_verified_phone', 'a.flag_match', 'd.salvage', 'a.test_flag')
            ->where('d.CHECK', 1)
            ->whereIn('d.STATUS', ['internal_reviewed', 'internal_rejected', 'internal_kiv', 'cra_pass', 'cra_fail'])
            // ->whereIn('g.doc_type', ['ic_front', 'ic_back', 'payslip', 'epf'])
            ->orderBy('d.create_date', 'DESC') // Modify in IF-726 sort by create_date in app_applicantdetail table
            ->distinct();

        /* Date Range Filter */
        if ($startDate && $endDate && $data) {
            $startDate = new Carbon($startDate);
            $endDate = new Carbon($endDate);

            $data->where('a.create_date', '>=', $startDate->toDateTimeString())
                ->where('a.create_date', '<=', $endDate->endOfDay()->toDateTimeString());
        }

        return $data;
    }

    /**
     * Get drop off applicant
     *
     * @param null $list
     * @param null $startDate
     * @param null $endDate
     * @return \Illuminate\Database\Query\Builder
     */
    public static function getDropOffList($list = null, $startDate = null, $endDate = null)
    {
        $data = null;

        switch ($list) {
            // All dropoff list.
            case 'all':
                $data = DB::table('app_applicant AS a')
                    ->leftJoin('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                    ->leftjoin('app_applicantdocument AS g', 'g.app_id', 'a.app_id')
                    ->leftjoin('product_pf AS p', 'p.prod_id', 'd.prod_id')
                    ->leftjoin('bank_info AS f', 'f.bank_id', 'p.bank_id')
                    ->select('a.id', 'd.id as detail_id', 'a.app_id', 'a.NAME as name', 'a.email', 'a.phoneno', 'a.is_verified_phone', 'a.age_apply',
                        'a.gross_salary', 'a.amount', 'a.tenure',
                        'a.sector', 'a.ic_no', 'd.STATUS as status', 'f.bank_name', 'p.prod_id as product_id',
                        'p.title as product_name', 'a.create_date', 'a.test_flag', 'd.salvage',
                        'a.dropoff_notif_counter')
                    ->where('a.is_verified_phone', false)
                    ->where('a.dropoff_notif_counter', 3)
                    ->orderBy('a.create_date', 'DESC')
                    ->groupBy('d.id');
                break;
            case 'unmatch':
                $data = DB::table('app_applicant AS a')
                    ->leftJoin('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                    ->leftjoin('app_applicantdocument AS g', 'g.app_id', 'a.app_id')
                    ->leftjoin('product_pf AS p', 'p.prod_id', 'd.prod_id')
                    ->leftjoin('bank_info AS f', 'f.bank_id', 'p.bank_id')
                    ->select('a.id', 'd.id as detail_id', 'a.app_id', 'a.NAME as name', 'a.email', 'a.phoneno', 'a.is_verified_phone', 'a.age_apply',
                        'a.gross_salary', 'a.amount', 'a.tenure',
                        'a.sector', 'a.ic_no', 'd.STATUS as status', 'f.bank_name', 'p.prod_id as product_id',
                        'p.title as product_name', 'a.create_date', 'a.test_flag', 'd.salvage')
                    ->where('a.is_verified_phone', false)
                    ->where('a.flag_match', 0)
                    ->orderBy('a.create_date', 'DESC')
                    ->groupBy('a.id');
                break;
            case 'uploaded-ic':
                $data = DB::table('app_applicant AS a')
                    ->leftJoin('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                    ->leftjoin('app_applicantdocument AS g', 'g.app_id', 'a.app_id')
                    ->leftjoin('product_pf AS p', 'p.prod_id', 'd.prod_id')
                    ->leftjoin('bank_info AS f', 'f.bank_id', 'p.bank_id')
                    ->select('a.id', 'd.id as detail_id', 'a.app_id', 'a.NAME as name', 'a.email', 'a.phoneno', 'a.is_verified_phone', 'a.age_apply',
                        'a.gross_salary', 'a.amount', 'a.tenure',
                        'a.sector', 'a.ic_no', 'd.STATUS as status', 'f.bank_name', 'p.prod_id as product_id',
                        'p.title as product_name', 'a.create_date', 'a.test_flag', 'd.salvage')
                    ->where('a.is_verified_phone', true)
                    ->whereIn('g.doc_type', ['ic_back', 'ic_front'])
                    ->orderBy('a.create_date', 'DESC')
                    ->groupBy('d.id');
                break;
            case 'no-ic':
                $data = DB::table('app_applicant AS a')
                    ->leftJoin('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                    ->leftjoin('app_applicantdocument AS g', 'g.app_id', 'a.app_id')
                    ->leftjoin('product_pf AS p', 'p.prod_id', 'd.prod_id')
                    ->leftjoin('bank_info AS f', 'f.bank_id', 'p.bank_id')
                    ->select('a.id', 'd.id as detail_id', 'a.app_id', 'a.NAME as name', 'a.email', 'a.phoneno', 'a.is_verified_phone', 'a.age_apply',
                        'a.gross_salary', 'a.amount', 'a.tenure',
                        'a.sector', 'a.ic_no', 'd.STATUS as status', 'f.bank_name', 'p.prod_id as product_id',
                        'p.title as product_name', 'a.create_date', 'a.test_flag', 'd.salvage')
                    ->where('a.is_verified_phone', true)
                    ->whereNull('g.id')
                    ->orderBy('a.create_date', 'DESC')
                    ->groupBy('d.id');
                break;
            case 'unverified':
                $sms = AppSmsGenModel::select('app_id')->distinct()->get();
                $appsId = [];
                foreach ($sms as $s) {
                    $appsId[] = $s->app_id;
                }
                $data = DB::table('app_applicant AS a')
                    ->leftJoin('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                    ->leftjoin('app_applicantdocument AS g', 'g.app_id', 'a.app_id')
                    ->leftjoin('product_pf AS p', 'p.prod_id', 'd.prod_id')
                    ->leftjoin('bank_info AS f', 'f.bank_id', 'p.bank_id')
                    ->select('a.id', 'd.id as detail_id', 'a.app_id', 'a.NAME as name', 'a.email', 'a.phoneno', 'a.is_verified_phone', 'a.age_apply',
                        'a.gross_salary', 'a.amount', 'a.tenure',
                        'a.sector', 'a.ic_no', 'd.STATUS as status', 'f.bank_name', 'p.prod_id as product_id',
                        'p.title as product_name', 'a.create_date', 'a.test_flag', 'd.salvage')
                    ->where('a.is_verified_phone', false)
                    ->whereIn('a.app_id', $appsId)
                    ->orderBy('a.create_date', 'DESC')
                    ->groupBy('d.id');
                break;
            default :
                $data = null;
                break;
        }

        /* Date Range Filter */
        if ($startDate && $endDate && $data) {
            $startDate = new Carbon($startDate);
            $endDate = new Carbon($endDate);
            $data->where('a.create_date', '>=', $startDate->toDateTimeString())
                ->where('a.create_date', '<=', $endDate->endOfDay()->toDateTimeString());
        }

        return $data;

    }

    /**
     * Get application detail (by app_applicantdetail IDs)
     *
     * @param int $id Detail ID or Applicant ID
     * @param null|string $type
     * @return Model|\Illuminate\Database\Query\Builder
     */
    public static function getDetail($id, $type = null)
    {
        $data = null;
        if (!$type) {
            return $data;
        }

        // Query condition by detail ID
        if ($type == 'by-detail-ID') {
            //update read_by_internal
            $update = ApplicantDetailModel::whereId($id)->update(['read_by_internal' => 1]);

            $data = DB::table('app_applicant AS a')
                ->leftJoin('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                ->leftJoin('bank_info as i', 'i.bank_id', 'd.bank_id')
                ->leftJoin('product_pf AS p', 'p.prod_id', 'd.prod_id')
                ->select('a.id as id', 'a.industry as industry', 'd.id as detail_id', 'a.app_id', 'a.name', 'a.email', 'a.phoneno', 'a.sector',
                    'a.amount',
                    'a.tenure', 'a.age_apply', 'i.bank_name', 'p.title as product', 'a.gross_salary', 'a.create_date',
                    'a.ic_no', 'a.employment_type', 'a.personal_loan', 'a.vehicle_loan', 'a.house_loan', 'a.credit_card_loan', 'a.other_loan', 'a.purpose', 'a.affiliate_id', DB::raw('DATE_FORMAT(a.affiliate_ts, "%d-%m-%Y") as affiliate_ts'),'a.campaign', 'a.is_verified_phone', 'a.is_verified_email', 'a.flag_match', 'a.states', 'a.ic_type', 'a.race', 'a.utm_source', 'a.utm_medium', 'a.utm_campaign', 'a.utm_term', 'a.utm_content', 'a.credit_grade', 'a.credit_score', 'a.bankrupt_count', 'a.legal_count', 'a.trade_count', 'a.cheque_count', 'a.special_attention', 'a.legal_action', 'a.industry', 'a.test_flag', 'd.salvage', 'a.postcode')
                ->where('d.id', $id)
                ->first();
        }

        // Query condition by Applicant ID
        if ($type == 'by-applicant-ID') {
            $data = DB::table('app_applicant AS a')
                ->leftJoin('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                ->leftJoin('bank_info as i', 'i.bank_id', 'd.bank_id')
                ->leftJoin('product_pf AS p', 'p.prod_id', 'd.prod_id')
                ->select('a.id as id', 'a.industry as industry', 'd.id as detail_id', 'a.app_id', 'a.name', 'a.email', 'a.phoneno', 'a.sector',
                    'a.amount',
                    'a.tenure', 'a.age_apply', 'i.bank_name', 'p.title as product', 'a.gross_salary', 'a.create_date',
                    'a.ic_no', 'a.employment_type', 'a.personal_loan', 'a.vehicle_loan', 'a.house_loan', 'a.credit_card_loan', 'a.other_loan', 'a.purpose', 'a.affiliate_id', DB::raw('DATE_FORMAT(a.affiliate_ts, "%d-%m-%Y") as affiliate_ts'),'a.campaign', 'a.is_verified_phone', 'a.is_verified_email', 'a.flag_match', 'a.states', 'a.ic_type', 'a.race', 'a.utm_source', 'a.utm_medium', 'a.utm_campaign', 'a.utm_term', 'a.utm_content', 'a.credit_grade', 'a.credit_score', 'a.bankrupt_count', 'a.legal_count', 'a.trade_count', 'a.cheque_count', 'a.special_attention', 'a.legal_action', 'a.industry', 'a.test_flag', 'd.salvage', 'a.postcode')
                ->where('a.id', $id)
                ->first();
        }

        // Query condition get applicant detail in CRM Dashboard
        if ($type == 'crm-app-detail') {
            $data = DB::table('app_applicant AS a')
                ->leftJoin('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                ->leftJoin('bank_info as i', 'i.bank_id', 'd.bank_id')
                ->leftJoin('product_pf AS p', 'p.prod_id', 'd.prod_id')
                ->select('a.id as id','a.industry as industry', 'd.id as detail_id', 'a.app_id', 'a.name', 'a.email', 'a.ic_no', 'a.phoneno as hp', 'a.sector as sector', 'a.amount as finamount',
                    'a.personal_loan as personal_loan', 'a.vehicle_loan as vehicle_loan', 'a.house_loan as house_loan', 'a.credit_card_loan', 'a.other_loan', 'a.installment_max',
                    'a.tenure as tenure', 'a.dsr', 'a.gross_salary as monthly_income', 'd.status as app_status', DB::raw('DATE_FORMAT(a.create_date, "%d-%m-%Y") as apply_date'),
                    'a.age_apply', 'a.credit_grade', 'a.credit_score', 'a.prob_default', 'a.key_influence', 'a.purpose', 'p.title as product_title', 'a.states',
                    'employment_type', 'industry', 'a.postcode')
                ->where('d.id', $id)
                ->first();
        }

        //Query condition for gohalal
        if ($type == 'by-gohalal-ID') {
            $data = null;
            $status = DB::table('app_applicant AS a')
                ->join('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                ->select('d.status as app_status', 'd.bank_id')
                ->where('d.id', $id)
                ->first();

            if ($status->app_status == 'ghf_tawarruq_no') {
                $data = DB::table('app_applicant AS a')
                ->join('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                ->join('bank_info as i', 'i.bank_id', 'd.bank_id')
                ->join('product_pf AS p', 'p.prod_id', 'd.prod_id')
                ->join('ghf_details AS g', 'g.app_id', 'a.app_id')
                ->join('ghf_do_trading AS gd', 'gd.ref_no', 'a.app_id')
                ->select('a.id', 'a.industry as industry', 'd.id as detail_id', 'a.app_id', 'a.reference_id', 
                    'a.name', 'a.email', 'a.phoneno', 'a.sector', 'gd.confirm_cancel', 'a.postcode', 
                    DB::raw('FORMAT(a.amount, 0) as amount'), 
                    'a.tenure', 'a.age_apply', 'i.bank_id', 'i.bank_name', 'p.title as product', 
                    DB::raw('FORMAT(a.gross_salary, 0) as gross_salary'), 
                    'a.create_date',
                    DB::raw('DATE_FORMAT(a.create_date, "%d %b %Y") as apply_date'),
                    'a.ic_no', 'a.employment_type', 'a.purpose', 'a.states', 'a.ic_type', 'd.status as app_status',
                    DB::raw("(case when d.status = 'ghf_fwd_rejected' then 'Rejected'
                    when d.status = 'ghf_fwd_approved' then 'Approved'
                    end
                    ) as uw_status"),
                    DB::raw("(case when d.status = 'ghf_tawarruq_complete' then 'Complete'
                    when d.status = 'ghf_tawarruq_in_progress' then 'In Progress'
                    end
                    ) as twr_status"),
                    'fwd_premium', 'i.fwd_code', 
                    'a.installment_max as installmentmax', 'a.src_channel',
                    'g.aml_clearance', 'g.pdpa_declaration', 'g.health_declaration', 'g.salary_date', 
                    'g.lo_status', 'g.reply_message', 'tawarruq_status', 'emandate1_status', 
                    'g.fwd_cert_no', 'g.fwd_premium', 'g.fwd_status', 
                    DB::raw('DATE_FORMAT(g.fwd_uw_ts, "%d %b %Y") as fwd_uw_ts'),
                    DB::raw('DATE_FORMAT(reply_timestamp, "%d %b %Y") as reply_timestamp'),
                    'g.takaful_eligibility', 
                    DB::raw('FORMAT(g.sum_covered, 0) as sum_covered'), 
                    'g.monthly_takaful_premium', 'g.fwd_cert_no', 'g.fwd_premium',
                    DB::raw('FORMAT(nett_disbursement_amount,2) as nett_disbursement_amount'),
                    DB::raw('FORMAT(disbursement_amount,2) as disbursement_amount'),
                    DB::raw('DATE_FORMAT(disbursement_date, "%d %b %Y") as disbursement_date'),
                    // DB::raw('DATE_FORMAT(first_deduction_date, "%d %b %Y") as first_deduction_date'),
                    DB::raw('DATE_FORMAT(tawarruq_ts, "%d %b %Y") as tawarruq_ts'),
                    DB::raw('DATE_FORMAT(emandate1_ts, "%d %b %Y") as emandate1_ts'),
                    DB::raw('DATE_FORMAT(dob, "%d %b %Y") as dob'),
                    DB::raw('DATE_FORMAT(fwd_uw_ts, "%d %b %Y") as fwd_uw_ts'),
                    'sedania_payment_status',
                    DB::raw('FORMAT(sedania_paid_amount,2) as sedania_paid_amount'),
                    DB::raw('DATE_FORMAT(sedania_payment_date, "%d %b %Y") as sedania_payment_date'),
                    DB::raw("(
                        CASE WHEN (d.status = 'uw_approved' or d.status = 'ghf_fwd_approved') and (adhoc_status is NULL or adhoc_status = '') then 'ACTIVE'
                        WHEN d.status <> 'uw_approved' and (adhoc_status is not NULL and adhoc_status <> '') then adhoc_status
                        ELSE '-' END
                    ) as policy_status"),
                    DB::raw("(
                        CASE WHEN (d.status = 'uw_approved' or d.status = 'ghf_fwd_approved') and (adhoc_status is NULL or adhoc_status = '') then DATE_FORMAT(disbursement_date, '%d-%m-%Y')
                        WHEN d.status <> 'uw_approved' and (adhoc_status is not NULL and adhoc_status <> '') then DATE_FORMAT(adhoc_ts, '%d-%m-%Y')
                        ELSE '-' END
                    ) as policy_date"),
                    'exceptional_status', 'exceptional_flag',
                    DB::raw('DATE_FORMAT(exceptional_ts, "%d %b %Y") as exceptional_ts'),
                    DB::raw('DATE_FORMAT(exceptional_validation_ts, "%d %b %Y") as exceptional_validation_ts'), DB::raw('DATE_FORMAT(g.first_deduction_date, "%d %b %Y") as first_deduction_date')
                    )
                ->where('d.id', $id)
                ->where('i.bank_id', $status->bank_id)
                ->first();
            } else {
                $data = DB::table('app_applicant AS a')
                ->join('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                ->join('bank_info as i', 'i.bank_id', 'd.bank_id')
                ->join('product_pf AS p', 'p.prod_id', 'd.prod_id')
                ->join('ghf_details AS g', 'g.app_id', 'a.app_id')
                // ->join('ghf_do_trading AS gd', 'gd.ref_no', 'a.app_id')
                ->select('a.id', 'a.industry as industry', 'd.id as detail_id', 'a.app_id', 'a.reference_id', 
                    'a.name', 'a.email', 'a.phoneno', 'a.sector', 'a.postcode', // 'gd.confirm_cancel',
                    DB::raw('FORMAT(a.amount, 0) as amount'), 
                    'a.tenure', 'a.age_apply', 'i.bank_id', 'i.bank_name', 'p.title as product', 
                    DB::raw('FORMAT(a.gross_salary, 0) as gross_salary'), 
                    'a.create_date',
                    DB::raw('DATE_FORMAT(a.create_date, "%d %b %Y") as apply_date'),
                    'a.ic_no', 'a.employment_type', 'a.purpose', 'a.states', 'a.ic_type', 'd.status as app_status',
                    DB::raw("(case when d.status = 'ghf_fwd_rejected' then 'Rejected'
                    when d.status = 'ghf_fwd_approved' then 'Approved'
                    end
                    ) as uw_status"),
                    DB::raw("(case when d.status = 'ghf_tawarruq_complete' then 'Complete'
                    when d.status = 'ghf_tawarruq_in_progress' then 'In Progress'
                    end
                    ) as twr_status"),
                    'fwd_premium', 'i.fwd_code', 
                    'a.installment_max as installmentmax', 'a.src_channel',
                    'g.aml_clearance', 'g.pdpa_declaration', 'g.health_declaration', 'g.salary_date', 
                    'g.lo_status', 'g.reply_message', 'tawarruq_status', 'emandate1_status', 
                    'g.fwd_cert_no', 'g.fwd_premium', 'g.fwd_status', 
                    DB::raw('DATE_FORMAT(g.fwd_uw_ts, "%d %b %Y") as fwd_uw_ts'),
                    DB::raw('DATE_FORMAT(reply_timestamp, "%d %b %Y") as reply_timestamp'),
                    'g.takaful_eligibility', 
                    DB::raw('FORMAT(g.sum_covered, 0) as sum_covered'), 
                    'g.monthly_takaful_premium', 'g.fwd_cert_no', 'g.fwd_premium',
                    DB::raw('FORMAT(nett_disbursement_amount,2) as nett_disbursement_amount'),
                    DB::raw('FORMAT(disbursement_amount,2) as disbursement_amount'),
                    DB::raw('DATE_FORMAT(disbursement_date, "%d %b %Y") as disbursement_date'),
                    // DB::raw('DATE_FORMAT(first_deduction_date, "%d %b %Y") as first_deduction_date'),
                    DB::raw('DATE_FORMAT(tawarruq_ts, "%d %b %Y") as tawarruq_ts'),
                    DB::raw('DATE_FORMAT(emandate1_ts, "%d %b %Y") as emandate1_ts'),
                    DB::raw('DATE_FORMAT(dob, "%d %b %Y") as dob'),
                    DB::raw('DATE_FORMAT(fwd_uw_ts, "%d %b %Y") as fwd_uw_ts'),
                    'sedania_payment_status',
                    DB::raw('FORMAT(sedania_paid_amount,2) as sedania_paid_amount'),
                    DB::raw('DATE_FORMAT(sedania_payment_date, "%d %b %Y") as sedania_payment_date'),
                    DB::raw("(
                        CASE WHEN (d.status = 'uw_approved' or d.status = 'ghf_fwd_approved') and (adhoc_status is NULL or adhoc_status = '') then 'ACTIVE'
                        WHEN d.status <> 'uw_approved' and (adhoc_status is not NULL and adhoc_status <> '') then adhoc_status
                        ELSE '-' END
                    ) as policy_status"),
                    DB::raw("(
                        CASE WHEN (d.status = 'uw_approved' or d.status = 'ghf_fwd_approved') and (adhoc_status is NULL or adhoc_status = '') then DATE_FORMAT(disbursement_date, '%d-%m-%Y')
                        WHEN d.status <> 'uw_approved' and (adhoc_status is not NULL and adhoc_status <> '') then DATE_FORMAT(adhoc_ts, '%d-%m-%Y')
                        ELSE '-' END
                    ) as policy_date"),
                    'exceptional_status', 'exceptional_flag',
                    DB::raw('DATE_FORMAT(exceptional_ts, "%d %b %Y") as exceptional_ts'),
                    DB::raw('DATE_FORMAT(exceptional_validation_ts, "%d %b %Y") as exceptional_validation_ts'), DB::raw('DATE_FORMAT(g.first_deduction_date, "%d %b %Y") as first_deduction_date')
                    )
                ->where('d.id', $id)
                ->where('i.bank_id', $status->bank_id)
                ->first();
            }
        }

        return $data;

    }

    public static function getGohalalDetail($id, $bankId)
    {
        $data = null;
        $appApplicant = ApplicantModel::where('app_id', $id)->first();
        $status = DB::table('app_applicant AS a')
            ->join('app_applicantdetail AS d', 'd.applicantid', 'a.id')
            ->select('d.status as app_status')
            ->where('a.app_id', $id)
            ->first();

        if ($status->app_status == 'ghf_tawarruq_no') {
            $data = DB::table('app_applicant AS a')
            ->join('app_applicantdetail AS d', 'd.applicantid', 'a.id')
            ->join('bank_info as i', 'i.bank_id', 'd.bank_id')
            ->join('product_pf AS p', 'p.prod_id', 'd.prod_id')
            ->join('ghf_details AS g', 'g.app_id', 'a.app_id')
            ->join('ghf_do_trading AS gd', 'gd.ref_no', 'a.app_id')
            ->select('a.id', 'a.industry as industry', 'd.id as detail_id', 'a.app_id', 'a.reference_id', 
                'a.name', 'a.email', 'a.phoneno', 'a.sector', 'gd.confirm_cancel', 'a.postcode', 
                DB::raw('FORMAT(a.amount, 0) as amount'), 
                'a.tenure', 'a.age_apply', 'i.bank_id', 'i.bank_name', 'p.title as product', 
                DB::raw('FORMAT(a.gross_salary, 0) as gross_salary'), 
                'a.create_date',
                DB::raw('DATE_FORMAT(a.create_date, "%d %b %Y") as apply_date'),
                'a.ic_no', 'a.employment_type', 'a.purpose', 'a.states', 'a.ic_type', 'd.status as app_status',
                DB::raw("(case when d.status = 'ghf_fwd_rejected' then 'Rejected'
                when d.status = 'ghf_fwd_approved' then 'Approved'
                end
                ) as uw_status"),
                DB::raw("(case when d.status = 'ghf_tawarruq_complete' then 'Complete'
                when d.status = 'ghf_tawarruq_in_progress' then 'In Progress'
                end
                ) as twr_status"),
                'fwd_premium', 'i.fwd_code', 
                'a.installment_max as installmentmax', 'a.src_channel',
                'g.aml_clearance', 'g.pdpa_declaration', 'g.health_declaration', 'g.salary_date', 
                'g.lo_status', 'g.reply_message', 'tawarruq_status', 'emandate1_status', 
                'g.fwd_cert_no', 'g.fwd_premium', 'g.fwd_status', 
                DB::raw('DATE_FORMAT(g.fwd_uw_ts, "%d %b %Y") as fwd_uw_ts'),
                DB::raw('DATE_FORMAT(reply_timestamp, "%d %b %Y") as reply_timestamp'),
                'g.takaful_eligibility', 
                DB::raw('FORMAT(g.sum_covered, 0) as sum_covered'), 
                'g.monthly_takaful_premium', 'g.fwd_cert_no', 'g.fwd_premium',
                DB::raw('FORMAT(nett_disbursement_amount,2) as nett_disbursement_amount'),
                DB::raw('FORMAT(disbursement_amount,2) as disbursement_amount'),
                DB::raw('DATE_FORMAT(disbursement_date, "%d %b %Y") as disbursement_date'),
                // DB::raw('DATE_FORMAT(first_deduction_date, "%d %b %Y") as first_deduction_date'),
                DB::raw('DATE_FORMAT(tawarruq_ts, "%d %b %Y") as tawarruq_ts'),
                DB::raw('DATE_FORMAT(emandate1_ts, "%d %b %Y") as emandate1_ts'),
                DB::raw('DATE_FORMAT(dob, "%d %b %Y") as dob'),
                DB::raw('DATE_FORMAT(fwd_uw_ts, "%d %b %Y") as fwd_uw_ts'),
                'sedania_payment_status',
                DB::raw('FORMAT(sedania_paid_amount,2) as sedania_paid_amount'),
                DB::raw('DATE_FORMAT(sedania_payment_date, "%d %b %Y") as sedania_payment_date'),
                DB::raw("(
                    CASE WHEN (d.status = 'uw_approved' or d.status = 'ghf_fwd_approved') and (adhoc_status is NULL or adhoc_status = '') then 'ACTIVE'
                    WHEN d.status <> 'uw_approved' and (adhoc_status is not NULL and adhoc_status <> '') then adhoc_status
                    ELSE '-' END
                ) as policy_status"),
                DB::raw("(
                    CASE WHEN (d.status = 'uw_approved' or d.status = 'ghf_fwd_approved') and (adhoc_status is NULL or adhoc_status = '') then DATE_FORMAT(disbursement_date, '%d-%m-%Y')
                    WHEN d.status <> 'uw_approved' and (adhoc_status is not NULL and adhoc_status <> '') then DATE_FORMAT(adhoc_ts, '%d-%m-%Y')
                    ELSE '-' END
                ) as policy_date"),
                'exceptional_status', 'exceptional_flag',
                DB::raw('DATE_FORMAT(exceptional_ts, "%d %b %Y") as exceptional_ts'),
                DB::raw('DATE_FORMAT(exceptional_validation_ts, "%d %b %Y") as exceptional_validation_ts'), DB::raw('DATE_FORMAT(g.first_deduction_date, "%d %b %Y") as first_deduction_date')
                )
            ->where('a.app_id', $id)
            ->where('i.bank_id', $bankId)
            ->first();
        } else {
            $data = DB::table('app_applicant AS a')
            ->join('app_applicantdetail AS d', 'd.applicantid', 'a.id')
            ->join('bank_info as i', 'i.bank_id', 'd.bank_id')
            ->join('product_pf AS p', 'p.prod_id', 'd.prod_id')
            ->join('ghf_details AS g', 'g.app_id', 'a.app_id')
            // ->join('ghf_do_trading AS gd', 'gd.ref_no', 'a.app_id')
            ->select('a.id', 'a.industry as industry', 'd.id as detail_id', 'a.app_id', 'a.reference_id', 
                'a.name', 'a.email', 'a.phoneno', 'a.sector', 'a.postcode', // 'gd.confirm_cancel',
                DB::raw('FORMAT(a.amount, 0) as amount'), 
                'a.tenure', 'a.age_apply', 'i.bank_id', 'i.bank_name', 'p.title as product', 
                DB::raw('FORMAT(a.gross_salary, 0) as gross_salary'), 
                'a.create_date',
                DB::raw('DATE_FORMAT(a.create_date, "%d %b %Y") as apply_date'),
                'a.ic_no', 'a.employment_type', 'a.purpose', 'a.states', 'a.ic_type', 'd.status as app_status',
                DB::raw("(case when d.status = 'ghf_fwd_rejected' then 'Rejected'
                when d.status = 'ghf_fwd_approved' then 'Approved'
                end
                ) as uw_status"),
                DB::raw("(case when d.status = 'ghf_tawarruq_complete' then 'Complete'
                when d.status = 'ghf_tawarruq_in_progress' then 'In Progress'
                end
                ) as twr_status"),
                'fwd_premium', 'i.fwd_code', 
                'a.installment_max as installmentmax', 'a.src_channel',
                'g.aml_clearance', 'g.pdpa_declaration', 'g.health_declaration', 'g.salary_date', 
                'g.lo_status', 'g.reply_message', 'tawarruq_status', 'emandate1_status', 
                'g.fwd_cert_no', 'g.fwd_premium', 'g.fwd_status', 
                DB::raw('DATE_FORMAT(g.fwd_uw_ts, "%d %b %Y") as fwd_uw_ts'),
                DB::raw('DATE_FORMAT(reply_timestamp, "%d %b %Y") as reply_timestamp'),
                'g.takaful_eligibility', 
                DB::raw('FORMAT(g.sum_covered, 0) as sum_covered'), 
                'g.monthly_takaful_premium', 'g.fwd_cert_no', 'g.fwd_premium',
                DB::raw('FORMAT(nett_disbursement_amount,2) as nett_disbursement_amount'),
                DB::raw('FORMAT(disbursement_amount,2) as disbursement_amount'),
                DB::raw('DATE_FORMAT(disbursement_date, "%d %b %Y") as disbursement_date'),
                // DB::raw('DATE_FORMAT(first_deduction_date, "%d %b %Y") as first_deduction_date'),
                DB::raw('DATE_FORMAT(tawarruq_ts, "%d %b %Y") as tawarruq_ts'),
                DB::raw('DATE_FORMAT(emandate1_ts, "%d %b %Y") as emandate1_ts'),
                DB::raw('DATE_FORMAT(dob, "%d %b %Y") as dob'),
                DB::raw('DATE_FORMAT(fwd_uw_ts, "%d %b %Y") as fwd_uw_ts'),
                'sedania_payment_status',
                DB::raw('FORMAT(sedania_paid_amount,2) as sedania_paid_amount'),
                DB::raw('DATE_FORMAT(sedania_payment_date, "%d %b %Y") as sedania_payment_date'),
                DB::raw("(
                    CASE WHEN (d.status = 'uw_approved' or d.status = 'ghf_fwd_approved') and (adhoc_status is NULL or adhoc_status = '') then 'ACTIVE'
                    WHEN d.status <> 'uw_approved' and (adhoc_status is not NULL and adhoc_status <> '') then adhoc_status
                    ELSE '-' END
                ) as policy_status"),
                DB::raw("(
                    CASE WHEN (d.status = 'uw_approved' or d.status = 'ghf_fwd_approved') and (adhoc_status is NULL or adhoc_status = '') then DATE_FORMAT(disbursement_date, '%d-%m-%Y')
                    WHEN d.status <> 'uw_approved' and (adhoc_status is not NULL and adhoc_status <> '') then DATE_FORMAT(adhoc_ts, '%d-%m-%Y')
                    ELSE '-' END
                ) as policy_date"),
                'exceptional_status', 'exceptional_flag',
                DB::raw('DATE_FORMAT(exceptional_ts, "%d %b %Y") as exceptional_ts'),
                DB::raw('DATE_FORMAT(exceptional_validation_ts, "%d %b %Y") as exceptional_validation_ts'), DB::raw('DATE_FORMAT(g.first_deduction_date, "%d %b %Y") as first_deduction_date')
                )
            ->where('a.app_id', $id)
            ->where('i.bank_id', $bankId)
            ->first();
        }

        return $data;

    }

    public static function getGohalalValidateDetail($id, $bankId = null)
    {
        $data = DB::table('app_applicant AS a')
            ->join('ghf_details AS g', 'g.app_id', 'a.app_id')
            ->select('a.app_id', 
                'a.name', 'a.ic_no', 'g.exceptional_status', 'g.validated_name', 'g.validated_ic', 'g.adhoc_status'
            )
            ->where('a.app_id', $id)
            // ->where('i.bank_id', $bankId)
            ->first();
        return $data;

    }

    /**
     * Export Listing To Excel
     *
     * @param $list
     * @param null $startDate
     * @param null $endDate
     * @param string $affiliateID
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Support\Collection|null
     */
    public static function exportData($list, $startDate = null, $endDate = null, $affiliateID = "all")
    {
        $data = null;
        switch ($list) {
            // Review List Applicant
            case 'review-list':
            case 'review':
                $data = DB::table('app_applicant AS a')
                    ->join('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                    ->join('app_applicantdocument AS g', 'g.app_id', 'a.app_id')
                    ->join('product_pf AS p', 'p.prod_id', 'd.prod_id')
                    ->join('bank_info AS f', 'f.bank_id', 'p.bank_id')
                    ->select('a.*', 'd.STATUS', 'f.bank_name', 'p.title as product')
                    ->where('d.CHECK', 1)
                    ->whereIn('d.STATUS', ['applied', 'created'])
                    ->whereIn('g.doc_type', ['ic_front', 'ic_back', 'payslip', 'epf'])
                    ->orderBy('a.create_date', 'DESC')
                    ->groupBy('d.id');
                break;
            // Reviewed List Applicant
            case 'reviewed-list':
            case 'reviewed':
                $data = DB::table('app_applicant AS a')
                    ->join('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                    ->join('app_applicantdocument AS g', 'g.app_id', 'a.app_id')
                    ->join('product_pf AS p', 'p.prod_id', 'd.prod_id')
                    ->join('bank_info AS f', 'f.bank_id', 'p.bank_id')
                    ->select('a.*', 'd.STATUS', 'f.bank_name', 'p.title as product')
                    ->where('d.CHECK', 1)
                    ->whereIn('d.STATUS', ['internal_reviewed', 'internal_rejected', 'internal_kiv', 'cra_pass', 'cra_fail'])
                    ->whereIn('g.doc_type', ['ic_front', 'ic_back', 'payslip', 'epf'])
                    ->orderBy('a.create_date', 'DESC')
                    ->groupBy('d.id');
                break;
            // Dropoff Applicant List
            case 'dropoff-list':
            case 'all-dropoff':
                $data = DB::table('app_applicant AS a')
                    ->leftJoin('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                    ->leftjoin('app_applicantdocument AS g', 'g.app_id', 'a.app_id')
                    ->leftjoin('product_pf AS p', 'p.prod_id', 'd.prod_id')
                    ->leftjoin('bank_info AS f', 'f.bank_id', 'p.bank_id')
                    ->select('a.*', 'd.STATUS', 'f.bank_name', 'p.title as product')
                    ->where('a.is_verified_phone', false)
                    ->orderBy('a.create_date', 'DESC')
                    ->groupBy('d.id');
                break;
            // Dropoff Applicant Not Entered OTP
            case 'dropoff-list-otp':
            case 'unverified':
                $sms = AppSmsGenModel::select('app_id')->distinct()->get();
                $appsId = [];
                foreach ($sms as $s) {
                    $appsId[] = $s->app_id;
                }
                $data = DB::table('app_applicant AS a')
                    ->leftJoin('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                    ->leftjoin('app_applicantdocument AS g', 'g.app_id', 'a.app_id')
                    ->leftjoin('product_pf AS p', 'p.prod_id', 'd.prod_id')
                    ->leftjoin('bank_info AS f', 'f.bank_id', 'p.bank_id')
                    ->select('a.*', 'd.STATUS', 'f.bank_name', 'p.title as product')
                    ->where('a.is_verified_phone', false)
                    ->whereIn('a.app_id', $appsId)
                    ->orderBy('a.create_date', 'DESC')
                    ->groupBy('d.id');
                break;
            // Dropoff Applicant List That No Matching Product
            case 'unmatched':
            case 'dropoff-unmatched':
                $data = DB::table('app_applicant AS a')
                    ->leftJoin('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                    ->leftjoin('app_applicantdocument AS g', 'g.app_id', 'a.app_id')
                    ->leftjoin('product_pf AS p', 'p.prod_id', 'd.prod_id')
                    ->leftjoin('bank_info AS f', 'f.bank_id', 'p.bank_id')
                    ->select('a.*', 'd.STATUS', 'f.bank_name', 'p.title as product')
                    ->where('a.is_verified_phone', false)
                    ->where('a.flag_match', 0)
                    ->orderBy('a.create_date', 'DESC')
                    ->distinct();
                break;
            // Dropoff Applicant List With Uploaded IC
            case 'ic':
            case 'dropoff-ic':
                $data = DB::table('app_applicant AS a')
                    ->leftJoin('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                    ->leftjoin('app_applicantdocument AS g', 'g.app_id', 'a.app_id')
                    ->leftjoin('product_pf AS p', 'p.prod_id', 'd.prod_id')
                    ->leftjoin('bank_info AS f', 'f.bank_id', 'p.bank_id')
                    ->select('a.*', 'd.STATUS', 'f.bank_name', 'p.title as product')
                    ->where('a.is_verified_phone', true)
                    ->whereIn('g.doc_type', ['ic_back', 'ic_front'])
                    ->orderBy('a.create_date', 'DESC')
                    ->groupBy('d.id');
                break;
            // Dropoff Applicant List That Not Uploaded IC
            case 'un-uploaded':
            case 'dropoff-noic':
                $data = DB::table('app_applicant AS a')
                    ->leftJoin('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                    ->leftjoin('app_applicantdocument AS g', 'g.app_id', 'a.app_id')
                    ->leftjoin('product_pf AS p', 'p.prod_id', 'd.prod_id')
                    ->leftjoin('bank_info AS f', 'f.bank_id', 'p.bank_id')
                    ->select('a.*', 'd.STATUS', 'f.bank_name', 'p.title as product')
                    ->where('a.is_verified_phone', true)
                    ->whereNull('g.id')
                    ->orderBy('a.create_date', 'DESC')
                    ->groupBy('d.id');
                break;
            // CRA Pass Applicant List
            case 'cra-pass':
                $data = DB::table('app_applicant AS a')
                    ->leftJoin('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                    ->leftJoin('app_applicantdocument AS g', 'g.app_id', 'a.app_id')
                    ->leftJoin('product_pf AS p', 'p.prod_id', 'd.prod_id')
                    ->leftJoin('bank_info AS f', 'f.bank_id', 'p.bank_id')
                    ->select('a.*', 'd.STATUS', 'f.bank_name', 'p.title as product')
                    ->where('d.CHECK', 1)
                    ->where('d.STATUS', 'cra_pass')
                    ->whereNotNull('g.doc_type')
                    ->orderBy('a.create_date', 'DESC')
                    ->groupBy('d.id');
                break;
            // CRA Fail Applicant List
            case 'cra-fail':
                $data = DB::table('app_applicant AS a')
                    ->leftJoin('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                    ->leftJoin('app_applicantdocument AS g', 'g.app_id', 'a.app_id')
                    ->leftJoin('product_pf AS p', 'p.prod_id', 'd.prod_id')
                    ->leftJoin('bank_info AS f', 'f.bank_id', 'p.bank_id')
                    ->select('a.*', 'd.STATUS', 'f.bank_name', 'p.title as product')
                    ->where('d.CHECK', 1)
                    ->where('d.STATUS', 'cra_fail')
                    ->whereNotNull('g.doc_type')
                    ->orderBy('a.create_date', 'DESC')
                    ->groupBy('d.id');
                break;
            case 'affiliate':
                $data = DB::table('app_applicant AS a')
                    ->leftJoin('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                    ->leftJoin('app_applicantdocument AS g', 'g.app_id', 'a.app_id')
                    ->leftJoin('product_pf AS p', 'p.prod_id', 'd.prod_id')
                    ->leftJoin('bank_info AS f', 'f.bank_id', 'p.bank_id')
                    ->select('a.*', 'd.STATUS', 'f.bank_name', 'p.title as product')
                    ->whereNotNull('a.affiliate_id')
                    ->whereNotIn('a.affiliate_id', ["", "null", "0"]) // Clean Up
                    ->orderBy('a.create_date', 'DESC')
                    ->groupBy('d.id');

                if ($affiliateID != "all") {
                    $data->where('a.affiliate_id', $affiliateID);
                }
            
                break;
            case 'new':
                $data = DB::table('app_applicant AS a')
                    ->join('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                    ->join('product_pf AS p', 'p.prod_id', 'd.prod_id')
                    ->join('bank_info AS f', 'f.bank_id', 'p.bank_id')
                    ->select('a.*', 'd.STATUS', 'f.bank_name', 'p.title as product')
                    ->whereIn('d.STATUS', ['cra_pass', 'cra_fail'])
                    // ->where('d.read_by_internal', 0)
                    ->orderBy('d.create_date', 'DESC');
                    // ->distinct();
                break;
            case 'submitted':
                $data = DB::table('app_applicant AS a')
                    ->join('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                    ->join('product_pf AS p', 'p.prod_id', 'd.prod_id')
                    ->join('bank_info AS f', 'f.bank_id', 'p.bank_id')
                    ->select('a.*', 'd.STATUS', 'f.bank_name', 'p.title as product')
                    ->where('d.STATUS', 'internal_reviewed')
                    ->orderBy('d.create_date', 'DESC');
                    // ->distinct();
                break;
            case 'unsuccessful':
                $data = DB::table('app_applicant AS a')
                    ->join('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                    ->join('product_pf AS p', 'p.prod_id', 'd.prod_id')
                    ->join('bank_info AS f', 'f.bank_id', 'p.bank_id')
                    ->select('a.*', 'd.STATUS', 'f.bank_name', 'p.title as product')
                    ->where('d.STATUS', 'internal_rejected')
                    ->orderBy('d.create_date', 'DESC');
                    // ->distinct();
                break;
            case 'dropoff-unverified':
                $data = DB::table('app_applicant AS a')
                    ->select('a.*')
                    ->where(DB::raw("(a.last_successful_page = 'personal_detail' OR 
                    (a.last_successful_page is null and a.create_date > '2020-07-20'))"), "1")
                    ->orderBy('a.create_date', 'DESC')
                    ->groupBy('a.id');

                break;
            case 'dropoff-unmatch':
                $data = DB::table('app_applicant AS a')
                    ->leftjoin('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                    ->leftjoin('product_pf AS p', 'p.prod_id', 'd.prod_id')
                    ->leftjoin('bank_info AS f', 'f.bank_id', 'p.bank_id')
                    ->select('a.*', 'd.STATUS', 'f.bank_name', 'p.title as product')
                    ->where('a.last_successful_page', 'job')
                    ->orderBy('a.create_date', 'DESC')
                    ->groupBy('a.id');
                break;
            case 'dropoff-verified':
                $data = DB::table('app_applicant AS a')
                    ->leftjoin('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                    ->leftjoin('product_pf AS p', 'p.prod_id', 'd.prod_id')
                    ->leftjoin('bank_info AS f', 'f.bank_id', 'p.bank_id')
                    ->select('a.*', 'd.STATUS', 'f.bank_name', 'p.title as product')
                    ->whereIn('a.last_successful_page', ['otp', 'product-search', 'product-submit'])
                    ->orderBy('a.create_date', 'DESC');
                    // ->distinct();
                break;
            case 'ghf_fwd_approved':
                $data = DB::table('app_applicant AS a')
                    ->leftjoin('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                    ->leftjoin('product_pf AS p', 'p.prod_id', 'd.prod_id')
                    ->leftjoin('bank_info AS f', 'f.bank_id', 'p.bank_id')
                    ->select('a.*', 'd.STATUS', 'f.bank_name', 'p.title as product')
                    ->where('d.status', 'ghf_fwd_approved')
                    ->orderBy('a.create_date', 'DESC');
                    // ->distinct();
                break;
            case 'exceptional':
                $data = DB::table('app_applicant AS a')
                    ->leftjoin('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                    ->leftjoin('product_pf AS p', 'p.prod_id', 'd.prod_id')
                    ->leftjoin('bank_info AS f', 'f.bank_id', 'p.bank_id')
                    ->select('a.*', 'd.STATUS', 'f.bank_name', 'p.title as product')
                    ->where('a.adhoc_status', 'exceptional')
                    ->orderBy('a.create_date', 'DESC');
                    // ->distinct();
                break;
            case 'ghf_fwd_rejected':
                $data = DB::table('app_applicant AS a')
                    ->leftjoin('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                    ->leftjoin('product_pf AS p', 'p.prod_id', 'd.prod_id')
                    ->leftjoin('bank_info AS f', 'f.bank_id', 'p.bank_id')
                    ->select('a.*', 'd.STATUS', 'f.bank_name', 'p.title as product')
                    ->where('d.status', 'ghf_fwd_rejected')
                    ->orderBy('a.create_date', 'DESC');
                    // ->distinct();
                break;
            case 'ghf_confirm_disbursement':
                $data = DB::table('app_applicant AS a')
                    ->leftjoin('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                    ->leftjoin('product_pf AS p', 'p.prod_id', 'd.prod_id')
                    ->leftjoin('bank_info AS f', 'f.bank_id', 'p.bank_id')
                    ->select('a.*', 'd.STATUS', 'f.bank_name', 'p.title as product')
                    ->where('d.status', 'ghf_confirm_disbursement')
                    ->orderBy('a.create_date', 'DESC');
                    // ->distinct();
                break;
            case 'ghf_emandate1_fail':
                $data = DB::table('app_applicant AS a')
                    ->leftjoin('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                    ->leftjoin('product_pf AS p', 'p.prod_id', 'd.prod_id')
                    ->leftjoin('bank_info AS f', 'f.bank_id', 'p.bank_id')
                    ->select('a.*', 'd.STATUS', 'f.bank_name', 'p.title as product')
                    ->where('d.status', 'ghf_emandate1_fail')
                    ->orderBy('a.create_date', 'DESC');
                    // ->distinct();
                break;
            case 'ghf_pending_emandate1':
                $data = DB::table('app_applicant AS a')
                    ->leftjoin('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                    ->leftjoin('product_pf AS p', 'p.prod_id', 'd.prod_id')
                    ->leftjoin('bank_info AS f', 'f.bank_id', 'p.bank_id')
                    ->select('a.*', 'd.STATUS', 'f.bank_name', 'p.title as product')
                    ->where('d.status', 'ghf_pending_emandate1')
                    ->orderBy('a.create_date', 'DESC');
                    // ->distinct();
                break;
            case 'ghf_tawarruq_no':
                $data = DB::table('app_applicant AS a')
                    ->leftjoin('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                    ->leftjoin('product_pf AS p', 'p.prod_id', 'd.prod_id')
                    ->leftjoin('bank_info AS f', 'f.bank_id', 'p.bank_id')
                    ->select('a.*', 'd.STATUS', 'f.bank_name', 'p.title as product')
                    ->where('d.status', 'ghf_tawarruq_no')
                    ->orderBy('a.create_date', 'DESC');
                    // ->distinct();
                break;
            case 'ghf_tawarruq_complete':
                $data = DB::table('app_applicant AS a')
                    ->leftjoin('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                    ->leftjoin('product_pf AS p', 'p.prod_id', 'd.prod_id')
                    ->leftjoin('bank_info AS f', 'f.bank_id', 'p.bank_id')
                    ->select('a.*', 'd.STATUS', 'f.bank_name', 'p.title as product')
                    ->where('d.status', 'ghf_tawarruq_complete')
                    ->orderBy('a.create_date', 'DESC');
                    // ->distinct();
                break;
            case 'ghf_tawarruq_in_progress':
                $data = DB::table('app_applicant AS a')
                    ->leftjoin('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                    ->leftjoin('product_pf AS p', 'p.prod_id', 'd.prod_id')
                    ->leftjoin('bank_info AS f', 'f.bank_id', 'p.bank_id')
                    ->select('a.*', 'd.STATUS', 'f.bank_name', 'p.title as product')
                    ->where('d.status', 'ghf_tawarruq_in_progress')
                    ->orderBy('a.create_date', 'DESC');
                    // ->distinct();
                break;
            case 'ghf_tawarruq_keep':
                $data = DB::table('app_applicant AS a')
                    ->leftjoin('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                    ->leftjoin('product_pf AS p', 'p.prod_id', 'd.prod_id')
                    ->leftjoin('bank_info AS f', 'f.bank_id', 'p.bank_id')
                    ->select('a.*', 'd.STATUS', 'f.bank_name', 'p.title as product')
                    ->where('d.status', 'ghf_tawarruq_keep')
                    ->orderBy('a.create_date', 'DESC');
                    // ->distinct();
                break;
            case 'all':
                $data = DB::table('app_applicant AS a')
                    ->join('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                    ->join('product_pf AS p', 'p.prod_id', 'd.prod_id')
                    ->join('bank_info AS f', 'f.bank_id', 'p.bank_id')
                    ->select('a.*', 'd.STATUS', 'f.bank_name', 'p.title as product')
                    ->orderBy('a.create_date', 'DESC');
                    // ->distinct();
                break;


            case 'vip-member':
                $data = DB::table('vip_member AS m')
                    ->leftJoin('app_applicant AS a', 'a.email', 'm.email')
                    ->leftJoin('app_applicant as a1', function ($join) {
                        $join->on('a.email', '=', 'a1.email')
                            ->whereRaw(DB::raw('a.create_date < a1.create_date'));
                    })->select('a.*')
                    ->addSelect(DB::raw('(case 
                        WHEN m.password IS NOT NULL THEN 1
                        else 0
                        end
                        ) as activation'))
                    ->addSelect(DB::raw('(case 
                        WHEN m.email IS NOT NULL THEN 1
                        else 0
                        end
                        ) as membership_type'))
                    ->whereNull('a1.email')
                    ->orderBy('a.create_date', 'DESC');
            break;
            case 'af-member':
                $data =  DB::table('af_member AS m')
                    ->leftJoin('vip_member AS v', 'v.email', 'm.email_address')
                    ->leftJoin('app_applicant AS a', 'm.email_address', 'a.email')
                    ->leftJoin('app_applicant as a1', function ($join) {
                        $join->on('a.email', '=', 'a1.email')
                            ->whereRaw(DB::raw('a.create_date < a1.create_date'));
                    })
                    ->select('a.*' )
                    ->addSelect(DB::raw('(case 
                         WHEN v.email IS NOT NULL THEN 1
                        else 0
                        end
                        ) as activation'))
                    ->addSelect(DB::raw('(case 
                        WHEN v.password IS NOT NULL THEN 1
                        else 0
                        end
                        ) as membership_type'))
                    ->orderBy('a.create_date', 'DESC')
                    ->whereNull('a1.email')
                    ->whereNull('v.email');
            break;
            default:
                return $data;
        }

        /* Date Range Filter */
        if ($startDate && $endDate && $data) {
            $startDate = new Carbon($startDate);
            $endDate = new Carbon($endDate);
            $data->where('a.create_date', '>=', $startDate)
                ->where('a.create_date', '<=', $endDate->endOfDay());
        }

        return $data->get();
    }

    /**
     * @param integer $bankID Bank ID
     * @param Carbon $startDate Start Date Filter
     * @param Carbon $endDate End Date Filter
     * @param string $status Status
     * @return \Illuminate\Database\Query\Builder
     */
    public static function applicantListForCRM($bankID, $status = 'applied', $startDate = null, $endDate = null)
    {
        $applications = DB::table('app_applicant AS a')
            ->leftjoin('app_applicantdetail AS d', 'd.applicantid', 'a.id')
            ->leftjoin('product_pf AS pf', 'pf.prod_id', 'd.prod_id')
            ->select('a.id as app_id', 'd.id as detail_id', 'a.app_id as app_code', 'a.name as appname', 'a.email as email', 'a.ic_no as ic_no', 'a.phoneno as hp',
                'a.personal_loan as personal_loan', 'a.vehicle_loan as vehicle_loan', 'a.house_loan as house_loan',
                'a.credit_card_loan', 'a.other_loan', 'a.installment_max as installmentmax', 'a.sector as sector',
                'a.amount as finamount', 'a.tenure as tenure', 'a.dsr', 'a.gross_salary as monthlyincome',
                'd.status as status', 'a.age_apply', 'a.credit_grade',
                'a.credit_score', 'a.prob_default', 'd.status as app_status', 'a.key_influence', 'a.purpose',
                DB::raw('DATE_FORMAT(a.create_date, "%d-%m-%Y") as apply_date'), 'pf.title as product_title', 'd.review_date')
            ->where('d.check', 1);

        if ($bankID != 1) {
            $applications->where('d.bank_id', $bankID);
        }
        if ($status == 'internal_reviewed') {
            $applications->where("d.status", "internal_reviewed");
            // $applications->whereNotNull('a.credit_grade');
        } else if ($status == 'verified_otp') {
            $applications->whereIn("d.status", ["applied", "verified_otp"]);
        } else {
            $applications->where('d.status', ($status ? $status : 'applied'));
            // $applications->whereNotNull('a.credit_grade');
        }

        if (!$status || $status == "internal_reviewed") {
            $applications->orderBy('a.create_date', 'DESC');
        } else {
            $applications->orderBy('d.status_date', 'DESC');
        }

        /* Date Range Filter */
        if ($startDate && $endDate && $applications) {
            $startDate = new Carbon($startDate);
            $endDate = new Carbon($endDate);
            $applications->where('a.create_date', '>=', $startDate->toDateTimeString())
                ->where('a.create_date', '<=', $endDate->endOfDay()->toDateTimeString());
        }

        return $applications->groupBy('d.id')->orderBy('a.create_date', 'DESC');
    }

    public static function applicantListForGohalal($bankID, $status = 'ghf_reviewed', $startDate = null, $endDate = null, $filter_status = null)
    {
        $applications = DB::table('app_applicant AS a')
            ->join('app_applicantdetail AS d', 'd.applicantid', 'a.id')
            ->join('ghf_details AS g', 'g.app_id', 'a.app_id')
            ->join('product_pf AS pf', 'pf.prod_id', 'd.prod_id')
            ->select('a.app_id', 'a.reference_id', 'a.name as appname', 
                'd.status as app_status', 'd.id as detail_id', 'd.bank_id',
                DB::raw('DATE_FORMAT(a.create_date, "%d-%m-%Y") as apply_date'),
                DB::raw("(
                    CASE WHEN (d.status = 'uw_approved' OR d.status = 'ghf_fwd_approved') and (adhoc_status is NULL or adhoc_status = '') then 'ACTIVE'
                    WHEN d.status <> 'uw_approved' and (adhoc_status is not NULL and adhoc_status <> '') then adhoc_status
                    ELSE '-' END
                ) as policy_status"),
                DB::raw("(
                    CASE WHEN (d.status = 'uw_approved' OR d.status = 'ghf_fwd_approved') and (adhoc_status is NULL or adhoc_status = '') then DATE_FORMAT(disbursement_date, '%d-%m-%Y')
                    WHEN d.status <> 'uw_approved' and (adhoc_status is not NULL and adhoc_status <> '') then DATE_FORMAT(adhoc_ts, '%d-%m-%Y')
                    ELSE '-' END
                ) as policy_date")
                );
            if ($bankID !== 1) {
                $applications->where('d.bank_id', $bankID);
            }
            $applications->groupBy('d.id')
            ->orderBy('a.create_date', 'DESC');

        if ($status == 'ghf_fwd_approved') {
            $applications->select('a.app_id', 'a.reference_id', 'a.name as appname', 
                'd.status as app_status', 'd.id as detail_id', 'd.bank_id',
                'fwd_status', 'adhoc_status', 'sedania_paid_amount', 'fwd_premium',
                DB::raw("(
                    CASE WHEN sedania_payment_status is null then 'PENDING' else sedania_payment_status end
                ) as sedania_payment_status"),
                DB::raw("(
                    CASE WHEN (d.status = 'uw_approved' OR d.status = 'ghf_fwd_approved') and (adhoc_status is NULL or adhoc_status = '') then 'ACTIVE'
                    WHEN d.status <> 'uw_approved' and (adhoc_status is not NULL and adhoc_status <> '') then adhoc_status
                    ELSE '-' END
                ) as policy_status"),
                DB::raw("(
                    CASE WHEN (d.status = 'uw_approved' OR d.status = 'ghf_fwd_approved') and (adhoc_status is NULL or adhoc_status = '') then DATE_FORMAT(disbursement_date, '%d-%m-%Y')
                    WHEN d.status <> 'uw_approved' and (adhoc_status is not NULL and adhoc_status <> '') then DATE_FORMAT(adhoc_ts, '%d-%m-%Y')
                    ELSE '-' END
                ) as policy_date"),
                DB::raw('DATE_FORMAT(a.create_date, "%d-%m-%Y") as apply_date'));
            if ($filter_status && $filter_status !== 'All') {
                $applications->where('sedania_payment_status', $filter_status);
            }
            $applications->where('d.status', $status);
        } else if ($status == 'ghf_fwd_rejected') {
            $applications->select('a.app_id', 'a.reference_id', 'a.name as appname', 
                'd.status as app_status', 'd.id as detail_id', 'd.bank_id',
                'fwd_status', 'adhoc_status', 'sedania_paid_amount', 'fwd_premium',
                DB::raw("(
                    CASE WHEN (d.status = 'uw_approved' OR d.status = 'ghf_fwd_approved') and (adhoc_status is NULL or adhoc_status = '') then 'ACTIVE'
                    WHEN d.status <> 'uw_approved' and (adhoc_status is not NULL and adhoc_status <> '') then adhoc_status
                    ELSE '-' END
                ) as policy_status"),
                DB::raw("(
                    CASE WHEN (d.status = 'uw_approved' OR d.status = 'ghf_fwd_approved') and (adhoc_status is NULL or adhoc_status = '') then DATE_FORMAT(disbursement_date, '%d-%m-%Y')
                    WHEN d.status <> 'uw_approved' and (adhoc_status is not NULL and adhoc_status <> '') then DATE_FORMAT(adhoc_ts, '%d-%m-%Y')
                    ELSE '-' END
                ) as policy_date"),
                DB::raw('DATE_FORMAT(a.create_date, "%d-%m-%Y") as apply_date'));
            $applications->where('d.status', $status);
        } else if ($status == 'exceptional') {
            $applications->select('a.app_id', 'a.reference_id', 'a.name as appname', 'ic_no', 
                'd.status as app_status', 'd.id as detail_id', 'd.bank_id',
                'fwd_status', 'adhoc_status', 'exceptional_status',
                DB::raw("(
                    CASE WHEN (d.status = 'uw_approved' OR d.status = 'ghf_fwd_approved') and (adhoc_status is NULL or adhoc_status = '') then 'ACTIVE'
                    WHEN d.status <> 'uw_approved' and (adhoc_status is not NULL and adhoc_status <> '') then adhoc_status
                    ELSE '-' END
                ) as policy_status"),
                DB::raw("(
                    CASE WHEN (d.status = 'uw_approved' OR d.status = 'ghf_fwd_approved') and (adhoc_status is NULL or adhoc_status = '') then DATE_FORMAT(disbursement_date, '%d-%m-%Y')
                    WHEN d.status <> 'uw_approved' and (adhoc_status is not NULL and adhoc_status <> '') then DATE_FORMAT(adhoc_ts, '%d-%m-%Y')
                    ELSE '-' END
                ) as policy_date"),
                DB::raw('DATE_FORMAT(a.create_date, "%d-%m-%Y") as apply_date'),
                DB::raw('DATE_FORMAT(exceptional_ts, "%d-%m-%Y") as exceptional_ts'));
            if ($filter_status && $filter_status !== 'All') {
                $applications->where('exceptional_status', $filter_status);
            }
            $applications->where('adhoc_status', 'exceptional');
        } else if ($status == 'ghf_confirm_disbursement') {
            $applications->whereIn('d.status', ['ghf_confirm_disbursement', 'ghf_fwd_approved', 'ghf_fwd_rejected', 'ghf_fwd_pending_review']);
        } else if ($status == 'ghf_tawarruq_no') {
            $applications->join('ghf_do_trading AS gdt', 'gdt.ref_no', 'a.app_id')
            ->select('a.app_id', 'a.reference_id', 'a.name as appname', 
                'd.status as app_status', 'd.id as detail_id', 'd.bank_id', 'gdt.confirm_cancel',
                DB::raw('DATE_FORMAT(a.create_date, "%d-%m-%Y") as apply_date'),
                DB::raw("(
                    CASE WHEN (d.status = 'uw_approved' OR d.status = 'ghf_fwd_approved') and (adhoc_status is NULL or adhoc_status = '') then 'ACTIVE'
                    WHEN d.status <> 'uw_approved' and (adhoc_status is not NULL and adhoc_status <> '') then adhoc_status
                    ELSE '-' END
                ) as policy_status"),
                DB::raw("(
                    CASE WHEN (d.status = 'uw_approved' OR d.status = 'ghf_fwd_approved') and (adhoc_status is NULL or adhoc_status = '') then DATE_FORMAT(disbursement_date, '%d-%m-%Y')
                    WHEN d.status <> 'uw_approved' and (adhoc_status is not NULL and adhoc_status <> '') then DATE_FORMAT(adhoc_ts, '%d-%m-%Y')
                    ELSE '-' END
                ) as policy_date")
                )->where('d.status', 'ghf_tawarruq_no');
        } else {
            $applications->where('d.status', $status);
        }

        /* Date Range Filter */
        if ($startDate && $endDate && $applications) {
            $startDate = new Carbon($startDate);
            $endDate = new Carbon($endDate);
            $applications->where('a.create_date', '>=', $startDate->toDateTimeString())
                ->where('a.create_date', '<=', $endDate->endOfDay()->toDateTimeString());
        }
        return $applications;
    }

    public static function dashboardForGohalal($bankID, $startDate = null, $endDate = null, $exceptional = null)
    {
        if ($exceptional) {
            $applications = DB::table('app_applicant AS a')
                ->join('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                ->join('ghf_details AS g', 'g.app_id', 'a.app_id')
                ->select('g.adhoc_status as status', 
                    DB::raw('COUNT(*) as count_app'), 
                    DB::raw('SUM(a.amount) as sum_amount'))
                ->where('d.bank_id', $bankID)
                // ->whereNotIn('d.status',['cra_pass','sla_breached','preapproved','applied','internal_kiv','internal_rejected','processing','created','reject','approved','cra_fail','verified_otp'])
                ->where('g.adhoc_status', 'exceptional')
                ->where('exceptional_flag', 1)
                ->groupBy('g.adhoc_status')
                ->get();
        } else {
            $applications = DB::table('app_applicant AS a')
                ->join('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                ->select('d.status', 
                    DB::raw('COUNT(*) as count_app'), 
                    DB::raw('SUM(a.amount) as sum_amount'))
                ->where('d.bank_id', $bankID)
                ->whereIn('d.status', ['internal_reviewed','rejected','processed','uncontactable','not_interested','ghf_pending_emandate1','ghf_emandate1_fail','ghf_tawarruq_in_progress','ghf_tawarruq_complete','ghf_tawarruq_no','ghf_tawarruq_keep','ghf_confirm_disbursement','ghf_fwd_approved','ghf_fwd_rejected'])
                ->groupBy('d.status')
                ->get();
        }
        

        /* Date Range Filter */
        if ($startDate && $endDate && $applications) {
            $startDate = new Carbon($startDate);
            $endDate = new Carbon($endDate);
            $applications->where('a.create_date', '>=', $startDate->toDateTimeString())
                ->where('a.create_date', '<=', $endDate->endOfDay()->toDateTimeString());
        }
        return $applications;
    }

    /**
     * Get list of reviewed application
     *
     * @param string $status CRA status (cra_pass, cra_fail)
     * @param null|string $startDate Start Date
     * @param null|string $endDate End Date
     * @return \Illuminate\Database\Query\Builder
     */

    public static function getCRAApplicantList($status, $startDate = null, $endDate = null)
    {

        $data = DB::table('app_applicant AS a')
            ->join('app_applicantdetail AS d', 'd.applicantid', 'a.id')
            ->join('app_applicantdocument AS g', 'g.app_id', 'a.app_id')
            ->join('product_pf AS p', 'p.prod_id', 'd.prod_id')
            ->join('bank_info AS f', 'f.bank_id', 'p.bank_id')
            ->select('a.id', 'd.id as detail_id', 'a.app_id', 'a.NAME as name', 'a.email', 'a.phoneno', 'a.age_apply',
                'a.gross_salary', 'a.amount', 'a.tenure',
                'a.sector', 'a.ic_no', 'd.STATUS as status', 'f.bank_name', 'p.prod_id as product_id',
                'p.title as product_name', 'a.create_date')
            ->where('d.CHECK', 1)
            ->where('d.STATUS', $status)
            ->whereNotNull('g.doc_type')
            ->orderBy('a.create_date', 'DESC')
            ->groupBy('d.id');

        /* Date Range Filter */
        if ($startDate && $endDate && $data) {
            $startDate = new Carbon($startDate);
            $endDate = new Carbon($endDate);

            $data->where('a.create_date', '>=', $startDate->toDateTimeString())
                ->where('a.create_date', '<=', $endDate->endOfDay()->toDateTimeString());
        }

        return $data;
    }

    public static function getApplicationDetailMulti($id)
    {
        return DB::table('app_applicantdetail AS a')
            ->leftJoin('product_pf AS c', 'c.prod_id', 'a.prod_id')
            ->leftJoin('bank_info AS b', 'b.bank_id', 'c.bank_id')
            // ->leftJoin('app_emandate AS e', 'e.applicant_id', 'a.applicantid')
            ->leftJoin('app_emandate AS e', function($join){
                $join->on('e.applicant_id', '=', 'a.applicantid')
                ->on('c.bank_id', 'e.bank_id');
            })

            ->select('a.prod_id', 'a.status', 'a.create_date', 'a.status_date', 'b.code AS bank_code', 'b.bank_name',
                'b.image', 'c.title', 'e.status AS curlec_status', 'e.generated_url', 'c.profit_rate', 'c.max_tenure', 'c.bank_id',
                'a.id as detail_id', 'c.upload_document as must_upload_form')
            ->where('a.applicantid', $id)
            ->get();
    }

    /**
     * @param integer $affiliateID Affiliate ID
     * @param Carbon $startDate Start Date Filter
     * @param Carbon $endDate End Date Filter
     * @param string $status Status
     * @return \Illuminate\Database\Query\Builder
     */
    public static function applicantListForAffiliate($affiliateID, $status = null, $startDate = null, $endDate = null)
    {

        $applications = DB::table('app_applicant AS a')
            ->leftjoin('app_applicantdetail AS d', 'd.applicantid', 'a.id')
            ->leftjoin('product_pf AS pf', 'pf.prod_id', 'd.prod_id')
            // IF-682
            ->select('a.app_id as app_code', 'a.name as appname', 
                'a.amount as finamount', 
                'd.status as status', 
                'd.status as app_status', 
                'pf.title as product_title')
            // END IF-682
            ->where('d.check', 1)
            // ->whereNotNull('a.credit_grade')
            ->orderBy('a.create_date', 'DESC');
        if ($affiliateID != 1) {
            $applications->where('a.affiliate_id', $affiliateID);
        }
        if ($status) {
            if ($status == 'internal_reviewed') {
                $applications->where("d.status", "internal_reviewed");
            } else {
                $applications->where('d.status', $status);
            }
        }

        /* Date Range Filter */
        if ($startDate && $endDate && $applications) {
            $startDate = new Carbon($startDate);
            $endDate = new Carbon($endDate);
            $applications->where('a.create_date', '>=', $startDate->toDateTimeString())
                ->where('a.create_date', '<=', $endDate->endOfDay()->toDateTimeString());
        }

        return $applications;
    }

    /**
     * @param string $searchBy Search Mode : by name, by IC No, by email, by phone or by App ID
     * @param string $keyword Keyword to search
     * @return \Illuminate\Database\Query\Builder|null
     */
    public static function searchApplicant($searchBy, $keyword)
    {
        $data = null;

        if ($searchBy && $keyword) {
            $data = DB::table('app_applicant AS a')
                ->join('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                ->join('app_applicantdocument AS g', 'g.app_id', 'a.app_id')
                ->join('product_pf AS p', 'p.prod_id', 'd.prod_id')
                ->join('bank_info AS f', 'f.bank_id', 'p.bank_id')
                ->select('a.id', 'd.id as detail_id', 'a.app_id', 'a.NAME as name', 'a.email', 'a.phoneno', 'a.age_apply', 'a.purpose',
                    'a.gross_salary', 'a.amount', 'a.tenure', 'a.sector', 'a.ic_no', 'd.STATUS as status', 'f.bank_name',
                    'p.prod_id as product_id', 'p.title as product_name', 'a.create_date', 'a.test_flag', 'd.salvage')
                ->where('d.CHECK', 1)
                ->whereIn('d.STATUS', ['applied', 'created'])
                ->whereIn('g.doc_type', ['ic_front', 'ic_back', 'payslip', 'epf'])
                ->orderBy('a.create_date', 'DESC')
                ->distinct();

            switch ($searchBy) {
                case 'byName':
                    $data->where('a.name', 'like', '%' . $keyword . '%');
                    break;
                case 'byIC':
                    $data->where('a.ic_no', 'like', '%' . $keyword . '%');
                    break;
                case 'byEmail':
                    $data->where('a.email', 'like', '%' . $keyword . '%');
                    break;
                case 'byPhone':
                    $data->where('a.phoneno', 'like', '%' . $keyword . '%');
                    break;
                case 'byAppId':
                    $data->where('a.app_id', 'like', '%' . $keyword . '%');
                    break;
                default:
                    $data = null;
                    break;
            }
        }

        return $data;
    }

    public static function searchApplicantReviewed($searchBy, $keyword)
    {
        $data = null;

        if ($searchBy && $keyword) {

            $data = DB::table('app_applicant AS a')
            ->join('app_applicantdetail AS d', 'd.applicantid', 'a.id')
            ->join('app_applicantdocument AS g', 'g.app_id', 'a.app_id')
            ->join('product_pf AS p', 'p.prod_id', 'd.prod_id')
            ->join('bank_info AS f', 'f.bank_id', 'p.bank_id')
            ->select('a.id', 'd.id as detail_id', 'a.app_id', 'a.NAME as name', 'a.email', 'a.phoneno', 'a.age_apply',
                'a.gross_salary', 'a.amount', 'a.tenure',
                'a.sector', 'a.ic_no', 'd.STATUS as status', 'f.bank_name', 'p.prod_id as product_id',
                'p.title as product_name', 'a.create_date', 'a.test_flag', 'd.salvage')
            ->where('d.CHECK', 1)
            ->whereIn('d.STATUS', ['internal_reviewed', 'cra_pass', 'cra_fail'])
            ->whereIn('g.doc_type', ['ic_front', 'ic_back', 'payslip', 'epf'])
            ->orderBy('a.create_date', 'DESC')
            ->groupBy('d.id');

            switch ($searchBy) {
                case 'byName':
                    $data->where('a.name', 'like', '%' . $keyword . '%');
                    break;
                case 'byIC':
                    $data->where('a.ic_no', 'like', '%' . $keyword . '%');
                    break;
                case 'byEmail':
                    $data->where('a.email', 'like', '%' . $keyword . '%');
                    break;
                case 'byPhone':
                    $data->where('a.phoneno', 'like', '%' . $keyword . '%');
                    break;
                case 'byAppId':
                    $data->where('a.app_id', 'like', '%' . $keyword . '%');
                    break;
                default:
                    $data = null;
                    break;
            }

        }

        return $data;
    }

    public static function searchDropoffWithIC($searchBy, $keyword)
    {
        $data = null;

        if ($searchBy && $keyword) {

            $data = DB::table('app_applicant AS a')
            ->leftJoin('app_applicantdetail AS d', 'd.applicantid', 'a.id')
            ->leftjoin('app_applicantdocument AS g', 'g.app_id', 'a.app_id')
            ->leftjoin('product_pf AS p', 'p.prod_id', 'd.prod_id')
            ->leftjoin('bank_info AS f', 'f.bank_id', 'p.bank_id')
            ->select('a.id', 'd.id as detail_id', 'a.app_id', 'a.NAME as name', 'a.email', 'a.phoneno', 'a.is_verified_phone', 'a.age_apply',
                'a.gross_salary', 'a.amount', 'a.tenure',
                'a.sector', 'a.ic_no', 'd.STATUS as status', 'f.bank_name', 'p.prod_id as product_id',
                'p.title as product_name', 'a.create_date', 'a.test_flag', 'd.salvage')
            ->where('a.is_verified_phone', true)
            ->whereIn('g.doc_type', ['ic_back', 'ic_front'])
            ->orderBy('a.create_date', 'DESC')
            ->groupBy('d.id');

            switch ($searchBy) {
                case 'byName':
                    $data->where('a.name', 'like', '%' . $keyword . '%');
                    break;
                case 'byIC':
                    $data->where('a.ic_no', 'like', '%' . $keyword . '%');
                    break;
                case 'byEmail':
                    $data->where('a.email', 'like', '%' . $keyword . '%');
                    break;
                case 'byPhone':
                    $data->where('a.phoneno', 'like', '%' . $keyword . '%');
                    break;
                case 'byAppId':
                    $data->where('a.app_id', 'like', '%' . $keyword . '%');
                    break;
                default:
                    $data = null;
                    break;
            }

        }

        return $data;
    }


    public static function searchDropoffWithoutIC($searchBy, $keyword)
    {
        $data = null;

        if ($searchBy && $keyword) {

            $data = DB::table('app_applicant AS a')
            ->leftJoin('app_applicantdetail AS d', 'd.applicantid', 'a.id')
            ->leftjoin('app_applicantdocument AS g', 'g.app_id', 'a.app_id')
            ->leftjoin('product_pf AS p', 'p.prod_id', 'd.prod_id')
            ->leftjoin('bank_info AS f', 'f.bank_id', 'p.bank_id')
            ->select('a.id', 'd.id as detail_id', 'a.app_id', 'a.NAME as name', 'a.email', 'a.phoneno', 'a.is_verified_phone', 'a.age_apply',
                'a.gross_salary', 'a.amount', 'a.tenure',
                'a.sector', 'a.ic_no', 'd.STATUS as status', 'f.bank_name', 'p.prod_id as product_id',
                'p.title as product_name', 'a.create_date', 'a.test_flag', 'd.salvage')
            ->where('a.is_verified_phone', true)
            ->whereNull('g.id')
            ->orderBy('a.create_date', 'DESC')
            ->groupBy('d.id');

            switch ($searchBy) {
                case 'byName':
                    $data->where('a.name', 'like', '%' . $keyword . '%');
                    break;
                case 'byIC':
                    $data->where('a.ic_no', 'like', '%' . $keyword . '%');
                    break;
                case 'byEmail':
                    $data->where('a.email', 'like', '%' . $keyword . '%');
                    break;
                case 'byPhone':
                    $data->where('a.phoneno', 'like', '%' . $keyword . '%');
                    break;
                case 'byAppId':
                    $data->where('a.app_id', 'like', '%' . $keyword . '%');
                    break;
                default:
                    $data = null;
                    break;
            }

        }

        return $data;
    }


    public static function searchDropoffUnmatched($searchBy, $keyword)
    {
        $data = null;

        if ($searchBy && $keyword) {

            $data = DB::table('app_applicant AS a')
            ->leftJoin('app_applicantdetail AS d', 'd.applicantid', 'a.id')
            ->leftjoin('app_applicantdocument AS g', 'g.app_id', 'a.app_id')
            ->leftjoin('product_pf AS p', 'p.prod_id', 'd.prod_id')
            ->leftjoin('bank_info AS f', 'f.bank_id', 'p.bank_id')
            ->select('a.id', 'd.id as detail_id', 'a.app_id', 'a.NAME as name', 'a.email', 'a.phoneno', 'a.is_verified_phone', 'a.age_apply',
                'a.gross_salary', 'a.amount', 'a.tenure',
                'a.sector', 'a.ic_no', 'd.STATUS as status', 'f.bank_name', 'p.prod_id as product_id',
                'p.title as product_name', 'a.create_date', 'a.test_flag', 'd.salvage')
            ->where('a.is_verified_phone', false)
            ->where('a.flag_match', 0)
            ->orderBy('a.create_date', 'DESC')
            ->groupBy('a.id');

            switch ($searchBy) {
                case 'byName':
                    $data->where('a.name', 'like', '%' . $keyword . '%');
                    break;
                case 'byIC':
                    $data->where('a.ic_no', 'like', '%' . $keyword . '%');
                    break;
                case 'byEmail':
                    $data->where('a.email', 'like', '%' . $keyword . '%');
                    break;
                case 'byPhone':
                    $data->where('a.phoneno', 'like', '%' . $keyword . '%');
                    break;
                case 'byAppId':
                    $data->where('a.app_id', 'like', '%' . $keyword . '%');
                    break;
                default:
                    $data = null;
                    break;
            }

        }

        return $data;
    }

    public static function searchDropoffUnverified($searchBy, $keyword)
    {
        $data = null;

        if ($searchBy && $keyword) {

            $sms = AppSmsGenModel::select('app_id')->distinct()->get();
            $appsId = [];
            foreach ($sms as $s) {
                $appsId[] = $s->app_id;
            }
            $data = DB::table('app_applicant AS a')
            ->leftJoin('app_applicantdetail AS d', 'd.applicantid', 'a.id')
            ->leftjoin('app_applicantdocument AS g', 'g.app_id', 'a.app_id')
            ->leftjoin('product_pf AS p', 'p.prod_id', 'd.prod_id')
            ->leftjoin('bank_info AS f', 'f.bank_id', 'p.bank_id')
            ->select('a.id', 'd.id as detail_id', 'a.app_id', 'a.NAME as name', 'a.email', 'a.phoneno', 'a.is_verified_phone', 'a.age_apply',
                'a.gross_salary', 'a.amount', 'a.tenure',
                'a.sector', 'a.ic_no', 'd.STATUS as status', 'f.bank_name', 'p.prod_id as product_id',
                'p.title as product_name', 'a.create_date', 'a.test_flag', 'd.salvage')
            ->where('a.is_verified_phone', false)
            ->whereIn('a.app_id', $appsId)
            ->orderBy('a.create_date', 'DESC')
            ->groupBy('d.id');

            switch ($searchBy) {
                case 'byName':
                    $data->where('a.name', 'like', '%' . $keyword . '%');
                    break;
                case 'byIC':
                    $data->where('a.ic_no', 'like', '%' . $keyword . '%');
                    break;
                case 'byEmail':
                    $data->where('a.email', 'like', '%' . $keyword . '%');
                    break;
                case 'byPhone':
                    $data->where('a.phoneno', 'like', '%' . $keyword . '%');
                    break;
                case 'byAppId':
                    $data->where('a.app_id', 'like', '%' . $keyword . '%');
                    break;
                default:
                    $data = null;
                    break;
            }

        }

        return $data;
    }

    public static function searchDropoffAll($searchBy, $keyword)
    {
        $data = null;

        if ($searchBy && $keyword) {

            $data = DB::table('app_applicant AS a')
            ->leftJoin('app_applicantdetail AS d', 'd.applicantid', 'a.id')
            ->leftjoin('app_applicantdocument AS g', 'g.app_id', 'a.app_id')
            ->leftjoin('product_pf AS p', 'p.prod_id', 'd.prod_id')
            ->leftjoin('bank_info AS f', 'f.bank_id', 'p.bank_id')
            ->select('a.id', 'd.id as detail_id', 'a.app_id', 'a.NAME as name', 'a.email', 'a.phoneno', 'a.is_verified_phone', 'a.age_apply',
                'a.gross_salary', 'a.amount', 'a.tenure',
                'a.sector', 'a.ic_no', 'd.STATUS as status', 'f.bank_name', 'p.prod_id as product_id',
                'p.title as product_name', 'a.create_date', 'a.test_flag', 'd.salvage')
            ->where('a.is_verified_phone', false)
            ->orderBy('a.create_date', 'DESC')
            ->groupBy('d.id');

            switch ($searchBy) {
                case 'byName':
                    $data->where('a.name', 'like', '%' . $keyword . '%');
                    break;
                case 'byIC':
                    $data->where('a.ic_no', 'like', '%' . $keyword . '%');
                    break;
                case 'byEmail':
                    $data->where('a.email', 'like', '%' . $keyword . '%');
                    break;
                case 'byPhone':
                    $data->where('a.phoneno', 'like', '%' . $keyword . '%');
                    break;
                case 'byAppId':
                    $data->where('a.app_id', 'like', '%' . $keyword . '%');
                    break;
                default:
                    $data = null;
                    break;
            }

        }

        return $data;
    }

    public function applyLoan($request, $response)
    {
        $request = $request->getParsedBody();

        // check facility
        $product = DB::table('product_pf AS a')
            ->leftjoin('bank_info AS bi', 'bi.bank_id', 'a.bank_id')
            ->select("*")
            ->where('prod_id', $request['facility_id'])
            ->first();
        if (!$product) {
            return [
                "result"  => false,
                "message" => [
                    'facility_id' => 'Sorry, Invalid Facility ID!',
                ],
                'code'    => 422,
            ];
        }

        $eventId  = $this->genEventId();
        $app_id   = 'LA' . "_" . $eventId;
        $genappid = $this->getLastApplicationId() + 1;
        $app_key  = md5('$' . $genappid . '$' . date('YmdHis') . $app_id);

        $name        = $request['applicant_name']; //ok added confirm
        $ic_no       = $request['ic'];
        $commonModel = new CommonModel();
        // TODO :: Mungkin bakal ada penyesuaian
        $age_apply = CommonModel::calculateAgeIc($ic_no);
        $phone     = CommonModel::validatePhone($request['phone']);
        if (!$phone) {
            return [
                "result"  => false,
                "message" => [
                    'phone' => 'Sorry, Invalid Phone Number!',
                ],
                'code'    => 422,
            ];
        }

        $now              = Carbon::now()->toDateTimeString();
        $amount           = $request['amount'];
        $sector           = $request['sector'];
        $installment_max  = 1000;
        $tenure           = $request['tenure'];
        $email            = $request['email'];
        $gross_salary     = $request['salary'];
        $personal_loan    = $request['personal_loan'] ? $request['personal_loan'] : 0;
        $vehicle_loan     = $request['vehicle_loan'] ? $request['vehicle_loan'] : 0;
        $house_loan       = $request['house_loan'] ? $request['house_loan'] : 0;
        $other_loan       = $request['other_loan'] ? $request['other_loan'] : 0;
        $credit_card_loan = $request['credit_card'] ? $request['credit_card'] : 0;
        $tnc              = $request['tnc'] ? 1 : 0;
        $tnc_text         = CommonModel::getTncText();
        // tambahan
        $employment_type = $request['employment_type'];
        $purpose         = @$request['purpose'];
        $race            = @$request['race'];
        $ic_type         = @$request['ic_type'];
        $states          = @$request['states'];
        $tnc_metadata    = CommonModel::createMetadata();
        $dsr             = @$request['dsr'];
        $affiliate_id    = @$request['affiliate_id'];
        $campaign        = @$request['campaign'];;

        // if (array_key_exists('aff_id', $request) && array_key_exists('campaign', $request)) {
        //     $affiliate_id = $request['aff_id'];
        //     $campaign     = $request['campaign'];
        // }

        $newData                   = new ApplicantModel();
        $newData->name             = $name;
        $newData->email            = $email;
        $newData->phoneno          = $phone;
        $newData->race             = @$race;
        $newData->ic_no            = $ic_no;
        $newData->ic_type          = $ic_type;
        $newData->age_apply        = $age_apply;
        $newData->amount           = $amount;
        $newData->sector           = $sector;
        $newData->installment_max  = $installment_max;
        $newData->tenure           = $tenure;
        $newData->gross_salary     = $gross_salary;
        $newData->personal_loan    = $personal_loan;
        $newData->vehicle_loan     = $vehicle_loan;
        $newData->house_loan       = $house_loan;
        $newData->dsr              = $dsr;
        $newData->tnc              = $tnc;
        $newData->tnc_text         = $tnc_text;
        $newData->tnc_metadata     = $tnc_metadata;
        $newData->tnc_text         = $tnc_text;
        $newData->app_id           = $app_id;
        $newData->app_key          = $app_key;
        $newData->create_date      = $now;
        $newData->employment_type  = $employment_type;
        $newData->purpose          = $purpose;
        $newData->other_loan       = $other_loan;
        $newData->credit_card_loan = $credit_card_loan;
        $newData->affiliate_id     = $affiliate_id;
        $newData->affiliate_ts     = $affiliate_ts;
        $newData->campaign         = $campaign;
        $newData->states           = $states;
        $newData->save();

        // TODO :: check _getAvailableProduct not applied for now

        //save detail
        $newDetailData               = new ApplicantDetailModel();
        $newDetailData->applicantid = $newData->id;
        $newDetailData->bank_id      = $product->bank_id;
        $newDetailData->prod_id      = $product->prod_id;
        $newDetailData->status       = 'applied';
        $newDetailData->save();

        // Calculate Installment
        $facilities                        = [];
        $mnthInstallment                   = round(CommonModel::calculateInstallment($amount, $product->profit_rate, $tenure), 2);
        $facilities['facility_id']         = $product->prod_id;
        $facilities['bank_name']           = $product->bank_name;
        $facilities['bank_logo_url']       = $product->image;
        $facilities['facility_name']       = $product->title;
        $facilities['monthly_installment'] = $mnthInstallment;
        $facilities['profit_rate']         = $product->profit_rate;
        $facilities['min_tenure']          = $product->min_tenure;
        $facilities['max_tenure']          = $product->max_tenure;
        $facilities['tenure']              = $tenure;

        //score
        $totalScore = 0;
        $salary     = (int)$gross_salary;

        // Tenure Scoring
        if ($product->min_tenure <= $tenure && $product->max_tenure >= $tenure) {
            $totalScore += 1;
        }

        // Salary Range 1500 - 3000 Scoring
        if ($salary >= 1500 && $salary <= 3000) {
            $totalScore += $product->income_filter_1;
        }

        // Salary Range 3000 - 5000 Scoring
        if ($salary >= 3001 && $salary <= 5000) {
            $totalScore += $product->income_filter_2;
        }

        // Salary Range 5000 - 8000 Scoring
        if ($salary >= 5001 and $salary <= 8000) {
            $totalScore += $product->income_filter_3;
        }

        // Salary Range > 8000 Scoring
        if ($salary >= 8001) {
            $totalScore += $product->income_filter_4;
        }

        $item->score                  = $totalScore;
        $facilities['matching_score'] = $totalScore;

        return [
            "result" =>
            [
                "applicant" => $newData,
                "facility" => $facilities,
            ],
        ];

    }

    public function genEventId()
    {
        $applications = DB::table('event_id_sequence')
            ->select('sequence', 'max')
            ->first();
        $seq     = $applications->sequence;
        $max     = $applications->max;
        $nextSeq = $seq + 1;

        $seq = str_pad($seq, 7, "0", STR_PAD_LEFT);

        $ts = $seq . date("Ymd");
        $ts = base_convert($ts, 10, 36);
        $ts = strtoupper($ts);

        if ($nextSeq >= $max) {
            $nextSeq = 1;
        }
        DB::table('event_id_sequence')
            ->update(['sequence' => $nextSeq]);

        return $ts;
    }

    public function getLastApplicationId()
    {
        $applications = DB::table('app_applicant')
            ->select('id')
            ->orderBy('id', 'DESC')
            ->first();
        $maxId = $applications->id;
        return $maxId;
    }

    public static function getBankStatusList($list = null, $startDate = null, $endDate = null)
    {
        $data = null;

        switch ($list) {
            // All dropoff list.
            case 'all':
                $data = DB::table('app_applicant AS a')
                    ->leftJoin('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                    ->leftjoin('app_applicantdocument AS g', 'g.app_id', 'a.app_id')
                    ->leftjoin('product_pf AS p', 'p.prod_id', 'd.prod_id')
                    ->leftjoin('bank_info AS f', 'f.bank_id', 'p.bank_id')
                    ->select('a.id', 'd.id as detail_id', 'a.app_id', 'a.NAME as name', 'a.email', 'a.phoneno', 'a.is_verified_phone', 'a.age_apply',
                        'a.gross_salary', 'a.amount', 'a.tenure',
                        'a.sector', 'a.ic_no', 'd.STATUS as status', 'f.bank_name', 'p.prod_id as product_id',
                        'p.title as product_name', 'a.create_date')
                    ->where('a.is_verified_phone', false)
                    ->orderBy('a.create_date', 'DESC')
                    ->groupBy('d.id');
                break;
            case 'approved':
                $data = DB::table('app_applicant AS a')
                    ->leftJoin('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                    ->leftjoin('app_applicantdocument AS g', 'g.app_id', 'a.app_id')
                    ->leftjoin('product_pf AS p', 'p.prod_id', 'd.prod_id')
                    ->leftjoin('bank_info AS f', 'f.bank_id', 'p.bank_id')
                    ->select('a.id', 'd.id as detail_id', 'a.app_id', 'a.NAME as name', 'a.email', 'a.phoneno', 'a.is_verified_phone', 'a.age_apply',
                        'a.gross_salary', 'a.amount', 'a.tenure',
                        'a.sector', 'a.ic_no', 'd.STATUS as status', 'f.bank_name', 'p.prod_id as product_id',
                        'p.title as product_name', 'a.create_date')
                    ->where('d.status', 'approved')
                    ->orderBy('a.create_date', 'DESC')
                    ->groupBy('a.id');
                break;
            case 'rejected':
                $data = DB::table('app_applicant AS a')
                    ->leftJoin('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                    ->leftjoin('app_applicantdocument AS g', 'g.app_id', 'a.app_id')
                    ->leftjoin('product_pf AS p', 'p.prod_id', 'd.prod_id')
                    ->leftjoin('bank_info AS f', 'f.bank_id', 'p.bank_id')
                    ->select('a.id', 'd.id as detail_id', 'a.app_id', 'a.NAME as name', 'a.email', 'a.phoneno', 'a.is_verified_phone', 'a.age_apply',
                        'a.gross_salary', 'a.amount', 'a.tenure',
                        'a.sector', 'a.ic_no', 'd.STATUS as status', 'f.bank_name', 'p.prod_id as product_id',
                        'p.title as product_name', 'a.create_date')
                    ->where('d.status', 'rejected')
                    ->orderBy('a.create_date', 'DESC')
                    ->groupBy('a.id');
                break;
            case 'cancelled':
                $data = DB::table('app_applicant AS a')
                    ->leftJoin('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                    ->leftjoin('app_applicantdocument AS g', 'g.app_id', 'a.app_id')
                    ->leftjoin('product_pf AS p', 'p.prod_id', 'd.prod_id')
                    ->leftjoin('bank_info AS f', 'f.bank_id', 'p.bank_id')
                    ->select('a.id', 'd.id as detail_id', 'a.app_id', 'a.NAME as name', 'a.email', 'a.phoneno', 'a.is_verified_phone', 'a.age_apply',
                        'a.gross_salary', 'a.amount', 'a.tenure',
                        'a.sector', 'a.ic_no', 'd.STATUS as status', 'f.bank_name', 'p.prod_id as product_id',
                        'p.title as product_name', 'a.create_date')
                    ->whereIn('d.STATUS', ['uncontactable', 'not_interested'])
                    ->orderBy('a.create_date', 'DESC')
                    ->groupBy('a.id');
                break;
            default :
                $data = null;
                break;
        }

        /* Date Range Filter */
        if ($startDate && $endDate && $data) {
            $startDate = new Carbon($startDate);
            $endDate = new Carbon($endDate);
            $data->where('a.create_date', '>=', $startDate->toDateTimeString())
                ->where('a.create_date', '<=', $endDate->endOfDay()->toDateTimeString());
        }

        return $data;

    }


    public static function searchGlobal($searchBy, $keyword)
    {
        $data = null;

        if ($searchBy && $keyword) {
            $data = DB::table('app_applicant AS a')
                ->join('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                // ->join('app_applicantdocument AS g', 'g.app_id', 'a.app_id')
                ->join('product_pf AS p', 'p.prod_id', 'd.prod_id')
                ->join('bank_info AS f', 'f.bank_id', 'p.bank_id')
                ->select('a.id', 'd.id as detail_id', 'a.app_id', 'a.NAME as name', 'a.email', 'a.phoneno', 'a.age_apply',
                    'a.gross_salary', 'a.amount', 'a.tenure', 'a.purpose',
                    'a.sector', 'a.ic_no', 'd.STATUS as status', 'f.bank_name',
                    'p.prod_id as product_id', 'p.title as product_name', 'a.create_date')
                ->orderBy('a.create_date', 'DESC')
                ->distinct();

            switch ($searchBy) {
                case 'byName':
                    $data->where('a.name', 'like', '%' . $keyword . '%');
                    break;
                case 'byIC':
                    $data->where('a.ic_no', 'like', '%' . $keyword . '%');
                    break;
                case 'byEmail':
                    $data->where('a.email', 'like', '%' . $keyword . '%');
                    break;
                case 'byPhone':
                    $data->where('a.phoneno', 'like', '%' . $keyword . '%');
                    break;
                case 'byAppId':
                    $data->where('a.app_id', 'like', '%' . $keyword . '%');
                    break;
                default:
                    $data = null;
                    break;
            }
        }

        return $data;
    }


    /**
     * Get Affiliate Applicant For Internal Dashboard
     * @param string $affiliateID
     * @param string $product
     * @param null $startDate
     * @param null $endDate
     * @return \Illuminate\Database\Query\Builder
     */
    public static function affiliateApplicantList($affiliateID = "all", $product = "PF", $startDate = null, $endDate = null)
    {

        $data = DB::table('app_applicant AS a')
            ->leftJoin('app_applicantdetail AS d', 'd.applicantid', 'a.id')
            ->leftJoin('app_applicantdocument AS g', 'g.app_id', 'a.app_id')
            ->leftJoin('product_pf AS p', 'p.prod_id', 'd.prod_id')
            ->leftJoin('bank_info AS f', 'f.bank_id', 'p.bank_id')
            ->select('a.id', 'd.id as detail_id', 'a.app_id', 'a.NAME as name', 'a.email', 'a.phoneno', 'a.age_apply',
                'a.gross_salary', 'a.amount', 'a.tenure', 'a.affiliate_id',
                'a.sector', 'a.ic_no', 'd.STATUS as status', 'f.bank_name', 'p.prod_id as product_id',
                'p.title as product_name', 'a.create_date', 'd.status', 'd.read_by_internal', 'a.is_verified_phone', 'a.flag_match')
            ->whereNotNull('a.affiliate_id')
            ->whereNotIn('a.affiliate_id', ["","null", "0"]) // Clean Up
            ->orderBy('a.create_date', 'DESC')
            ->groupBy('d.id');

        if($affiliateID != "all") {
            $data->where('a.affiliate_id', $affiliateID);
        }

        /* Date Range Filter */
        if ($startDate && $endDate && $data) {
            $startDate = new Carbon($startDate);
            $endDate = new Carbon($endDate);

            $data->where('a.create_date', '>=', $startDate->toDateTimeString())
                ->where('a.create_date', '<=', $endDate->endOfDay()->toDateTimeString());
        }

        return $data;
    }

    public static function getDropOffListNew($list = null, $startDate = null, $endDate = null)
    {
        $data = null;
        $sevendays = date('Y-m-d', strtotime('-7 days'));

        switch ($list) {
            // All dropoff list.
            case 'all':
                $data = DB::table('app_applicant AS a')
                    ->leftJoin('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                    ->leftjoin('app_applicantdocument AS g', 'g.app_id', 'a.app_id')
                    ->leftjoin('product_pf AS p', 'p.prod_id', 'd.prod_id')
                    ->leftjoin('bank_info AS f', 'f.bank_id', 'p.bank_id')
                    ->select('a.last_successful_page', 'a.id', 'd.id as detail_id', 'a.app_id', 'a.app_key' ,'a.NAME as name', 'a.email', 'a.phoneno', 'a.is_verified_phone', 'a.age_apply',
                        'a.gross_salary', 'a.amount', 'a.tenure',
                        'a.sector', 'a.ic_no', 'd.STATUS as status', 'f.bank_name', 'p.prod_id as product_id',
                        'p.title as product_name', 'a.create_date', 'a.dropoff_timestamp', 'a.dropoff_notif_counter')
                    ->whereIn('a.dropoff_notif_counter', [1, 2, 10])
                    ->whereRaw(DB::raw(
                        '((a.last_successful_page IN ("otp","personal_detail")) OR (a.last_successful_page IS NULL))'
                    ))
                    ->where('a.create_date','>=',$sevendays)
                    ->orderBy('a.create_date', 'DESC');
                    // ->groupBy('d.id');
                break;
            default :
                $data = null;
                break;
        }

        /* Date Range Filter */
        if ($startDate && $endDate && $data) {
            $startDate = new Carbon($startDate);
            $endDate = new Carbon($endDate);
            $data->where('a.create_date', '>=', $startDate->toDateTimeString())
                ->where('a.create_date', '<=', $endDate->endOfDay()->toDateTimeString());
        }

        return $data;

    }

    public static function getDropOffListNewTimestamp($list = null, $startDate = null, $endDate = null)
    {
        $data = null;
        $sevendays = date('Y-m-d', strtotime('-7 days'));

        switch ($list) {
            // All dropoff list.
            case 'all':
                $data = DB::table('app_applicant AS a')
                    ->leftJoin('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                    ->leftjoin('app_applicantdocument AS g', 'g.app_id', 'a.app_id')
                    ->leftjoin('product_pf AS p', 'p.prod_id', 'd.prod_id')
                    ->leftjoin('bank_info AS f', 'f.bank_id', 'p.bank_id')
                    ->select('a.last_successful_page', 'a.id', 'd.id as detail_id', 'a.app_id', 'a.app_key' ,'a.NAME as name', 'a.email', 'a.phoneno', 'a.is_verified_phone', 'a.age_apply',
                        'a.gross_salary', 'a.amount', 'a.tenure',
                        'a.sector', 'a.ic_no', 'd.STATUS as status', 'f.bank_name', 'p.prod_id as product_id',
                        'p.title as product_name', 'a.create_date', 'a.dropoff_timestamp', 'a.dropoff_notif_counter')
                    ->whereRaw(DB::raw(
                        '((a.last_successful_page IN ("otp", "personal_detail")) OR (a.last_successful_page IS NULL)) AND a.dropoff_timestamp IS NULL'
                    ))
                    ->where('a.create_date','>=',$sevendays)
                    ->orderBy('a.create_date', 'DESC');
                    // ->groupBy('d.id');
                break;
            default :
                $data = null;
                break;
        }

        /* Date Range Filter */
        if ($startDate && $endDate && $data) {
            $startDate = new Carbon($startDate);
            $endDate = new Carbon($endDate);
            $data->where('a.create_date', '>=', $startDate->toDateTimeString())
                ->where('a.create_date', '<=', $endDate->endOfDay()->toDateTimeString());
        }

        return $data;

    }

    public static function getDropOffListV3($list = null, $startDate = null, $endDate = null)
    {
        $data = null;

        switch ($list) {
            // All dropoff list.
            case 'all':
                $data = DB::table('app_applicant AS a')
                    ->leftJoin('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                    // ->leftjoin('app_applicantdocument AS g', 'g.app_id', 'a.app_id')
                    ->leftjoin('product_pf AS p', 'p.prod_id', 'd.prod_id')
                    ->leftjoin('bank_info AS f', 'f.bank_id', 'p.bank_id')
                    ->select('a.id', 'd.id as detail_id', 'a.app_id', 'a.NAME as name', 'a.email', 'a.phoneno', 'a.is_verified_phone', 'a.age_apply',
                        'a.gross_salary', 'a.amount', 'a.tenure',
                        'a.sector', 'a.ic_no', 'd.STATUS as status', 'f.bank_name', 'p.prod_id as product_id',
                        'p.title as product_name', 'a.create_date', 'a.test_flag', 'd.salvage')
                    ->where('a.is_verified_phone', false)
                    ->orderBy('a.create_date', 'DESC')
                    ->groupBy('d.id');
                break;
            case 'unmatch':
                $data = DB::table('app_applicant AS a')
                    ->leftJoin('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                    // ->leftjoin('app_applicantdocument AS g', 'g.app_id', 'a.app_id')
                    ->leftjoin('product_pf AS p', 'p.prod_id', 'd.prod_id')
                    ->leftjoin('bank_info AS f', 'f.bank_id', 'p.bank_id')
                    ->select('a.id', 'd.id as detail_id', 'a.app_id', 'a.NAME as name', 'a.email', 'a.phoneno', 'a.is_verified_phone', 'a.age_apply',
                        'a.gross_salary', 'a.amount', 'a.tenure',
                        'a.sector', 'a.ic_no', 'd.STATUS as status', 'f.bank_name', 'p.prod_id as product_id',
                        'p.title as product_name', 'a.create_date', 'a.test_flag', 'd.salvage', 'a.last_successful_page')
                    ->where('a.last_successful_page', 'job')
                    ->orderBy('a.create_date', 'DESC')
                    ->groupBy('a.id');
                break;
            case 'verified':
                $data = DB::table('app_applicant AS a')
                    ->leftJoin('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                    // ->leftjoin('app_applicantdocument AS g', 'g.app_id', 'a.app_id')
                    ->leftjoin('product_pf AS p', 'p.prod_id', 'd.prod_id')
                    ->leftjoin('bank_info AS f', 'f.bank_id', 'p.bank_id')
                    ->select('a.id', 'd.id as detail_id', 'a.app_id', 'a.NAME as name', 'a.email', 'a.phoneno', 'a.is_verified_phone', 'a.age_apply',
                        'a.gross_salary', 'a.amount', 'a.tenure',
                        'a.sector', 'a.ic_no', 'd.STATUS as status', 'f.bank_name', 'p.prod_id as product_id',
                        'p.title as product_name', 'a.create_date', 'a.test_flag', 'd.salvage', 'a.last_successful_page')
                    ->whereIn('a.last_successful_page', ['otp', 'product-search', 'product-submit'])
                    ->orderBy('a.create_date', 'DESC')
                    ->groupBy('a.id', 'd.id');
                break;
            case 'unverified':
                //unverified => baru sampai page B/C, belum memilih produk
                //tanggal dibatasi 10 August, saat dipakai pertama kali field `last_successful_page`
                $data = DB::table('app_applicant AS a')
                    // ->leftJoin('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                    // ->leftjoin('app_applicantdocument AS g', 'g.app_id', 'a.app_id')
                    // ->leftjoin('product_pf AS p', 'p.prod_id', 'd.prod_id')
                    // ->leftjoin('bank_info AS f', 'f.bank_id', 'p.bank_id')
                    ->select('a.id', 'a.app_id', 'a.NAME as name', 'a.email', 'a.phoneno', 'a.is_verified_phone', 'a.age_apply',
                        'a.gross_salary', 'a.amount', 'a.tenure',
                        'a.sector', 'a.ic_no',
                        'a.create_date', 'a.test_flag', 'a.last_successful_page')
                    ->where(DB::raw("(a.last_successful_page = 'personal_detail' OR 
                    (a.last_successful_page is null and a.create_date > '2020-07-20'))"), "1")
                    ->orderBy('a.create_date', 'DESC')
                    ->groupBy('a.id');
                break;
            default :
                $data = null;
                break;
        }

        /* Date Range Filter */
        if ($startDate && $endDate && $data) {
            $startDate = new Carbon($startDate);
            $endDate = new Carbon($endDate);
            $data->where('a.create_date', '>=', $startDate->toDateTimeString())
                ->where('a.create_date', '<=', $endDate->endOfDay()->toDateTimeString());
        }

        return $data;

    }

    public static function getApplicantListV3($list = null, $startDate = null, $endDate = null)
    {
        $data = null;

        switch($list){
            case 'new':
                $data = DB::table('app_applicant AS a')
                    ->join('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                    // ->join('app_applicantdocument AS g', 'g.app_id', 'a.app_id')
                    ->join('product_pf AS p', 'p.prod_id', 'd.prod_id')
                    ->join('bank_info AS f', 'f.bank_id', 'p.bank_id')
                    ->select('a.id', 'd.id as detail_id', 'a.app_id', 'a.NAME as name', 'a.email', 'a.phoneno', 'a.age_apply',
                        'a.gross_salary', 'a.amount', 'a.tenure',
                        'a.sector', 'a.ic_no', 'd.STATUS as status', 'f.bank_name', 'p.prod_id as product_id',
                        'p.title as product_name', 'a.create_date', 'd.status', 'd.read_by_internal', 'a.is_verified_phone', 'a.flag_match', 'd.salvage', 'a.test_flag')
                    ->whereIn('d.STATUS', ['cra_pass', 'cra_fail'])
                    // ->whereIn('g.doc_type', ['ic_front', 'ic_back', 'payslip', 'epf'])
                    ->orderBy('d.create_date', 'DESC') // Modify in IF-726 sort by create_date in app_applicantdetail table
                    ->distinct();
                break;
            case 'submitted':
                    $data = DB::table('app_applicant AS a')
                    ->join('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                    // ->join('app_applicantdocument AS g', 'g.app_id', 'a.app_id')
                    ->join('product_pf AS p', 'p.prod_id', 'd.prod_id')
                    ->join('bank_info AS f', 'f.bank_id', 'p.bank_id')
                    ->select('a.id', 'd.id as detail_id', 'a.app_id', 'a.NAME as name', 'a.email', 'a.phoneno', 'a.age_apply',
                        'a.gross_salary', 'a.amount', 'a.tenure',
                        'a.sector', 'a.ic_no', 'd.STATUS as status', 'f.bank_name', 'p.prod_id as product_id',
                        'p.title as product_name', 'a.create_date', 'd.status', 'd.read_by_internal', 'a.is_verified_phone', 'a.flag_match', 'd.salvage', 'a.test_flag')
                    ->where('d.STATUS', 'internal_reviewed')
                    // ->whereIn('g.doc_type', ['ic_front', 'ic_back', 'payslip', 'epf'])
                    ->orderBy('d.create_date', 'DESC') // Modify in IF-726 sort by create_date in app_applicantdetail table
                    ->distinct();
                break;
            case 'unsuccessful':
                    $data = DB::table('app_applicant AS a')
                    ->join('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                    // ->join('app_applicantdocument AS g', 'g.app_id', 'a.app_id')
                    ->join('product_pf AS p', 'p.prod_id', 'd.prod_id')
                    ->join('bank_info AS f', 'f.bank_id', 'p.bank_id')
                    ->select('a.id', 'd.id as detail_id', 'a.app_id', 'a.NAME as name', 'a.email', 'a.phoneno', 'a.age_apply',
                        'a.gross_salary', 'a.amount', 'a.tenure',
                        'a.sector', 'a.ic_no', 'd.STATUS as status', 'f.bank_name', 'p.prod_id as product_id',
                        'p.title as product_name', 'a.create_date', 'd.status', 'd.read_by_internal', 'a.is_verified_phone', 'a.flag_match', 'd.salvage', 'a.test_flag')
                    ->where('d.STATUS', 'internal_rejected')
                    // ->whereIn('g.doc_type', ['ic_front', 'ic_back', 'payslip', 'epf'])
                    ->orderBy('d.create_date', 'DESC') // Modify in IF-726 sort by create_date in app_applicantdetail table
                    ->distinct();
                break;
            default:
                $data = null;
                break;
        }        

        /* Date Range Filter */
        if ($startDate && $endDate && $data) {
            $startDate = new Carbon($startDate);
            $endDate = new Carbon($endDate);

            $data->where('a.create_date', '>=', $startDate->toDateTimeString())
                ->where('a.create_date', '<=', $endDate->endOfDay()->toDateTimeString());
        }

        return $data;

    }

    public static function getDropOffListVip($list = null, $startDate = null, $endDate = null)
    {
        $data = null;

        switch ($list) {
            // All dropoff list.
            case 'all':
                $vips = self::where('last_successful_page', 'completed')->select('email')->get();
                $emails = collect($vips)->pluck('email')->toArray();

                $viptable = DB::table('vip_member')
                            ->select('email')
                            ->whereIn('email', $emails)
                            ->get();
                $vipmember = collect($viptable)->pluck('email')->toArray();

                $data = DB::table('app_applicant AS a')
                    ->leftJoin('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                    ->leftjoin('app_applicantdocument AS g', 'g.app_id', 'a.app_id')
                    ->leftjoin('product_pf AS p', 'p.prod_id', 'd.prod_id')
                    ->leftjoin('bank_info AS f', 'f.bank_id', 'p.bank_id')
                    ->select('a.last_successful_page', 'a.id', 'd.id as detail_id', 'a.app_id', 'a.app_key' ,'a.NAME as name', 'a.email', 'a.phoneno', 'a.is_verified_phone', 'a.age_apply',
                        'a.gross_salary', 'a.amount', 'a.tenure',
                        'a.sector', 'a.ic_no', 'd.STATUS as status', 'f.bank_name', 'p.prod_id as product_id',
                        'p.title as product_name', 'a.create_date', 'a.dropoff_timestamp', 'a.dropoff_notif_counter')
                    ->where('a.last_successful_page', 'completed')
                    ->where('a.dropoff_vip_counter', 0)
                    ->whereNotIn('a.email', $vipmember)
                    // ->whereNull('a.dropoff_notif_counter')
                    // ->orWhereIn('a.dropoff_notif_counter', [1, 2, 10])
                    ->orderBy('a.create_date', 'DESC');
                    // ->groupBy('d.id');
                break;
            default :
                $data = null;
                break;
        }

        /* Date Range Filter */
        if ($startDate && $endDate && $data) {
            $startDate = new Carbon($startDate);
            $endDate = new Carbon($endDate);
            $data->where('a.create_date', '>=', $startDate->toDateTimeString())
                ->where('a.create_date', '<=', $endDate->endOfDay()->toDateTimeString());
        }

        return $data;

    }


    public static function vipMemberList($startDate = null, $endDate = null)
    {
        $data = DB::table('vip_member AS m')
            ->leftJoin('app_applicant AS a', 'a.email', 'm.email')
            ->leftJoin('app_applicant as a1', function ($join) {
                $join->on('a.email', '=', 'a1.email')
                    ->whereRaw(DB::raw('a.create_date < a1.create_date'));
            })
            ->select(
                'm.id',
                'm.email',
                'a.app_id',
                'a.NAME as name',
                'a.phoneno',
                'a.age_apply',
                'a.gross_salary',
                'a.amount',
                'a.tenure',
                'a.affiliate_id',
                'a.sector',
                'a.ic_no',
                'a.dob',
                'a.employment_type',
                'a.industry',
                'a.ic_no',
                'a.ic_type',
                'a.states',
                'a.last_successful_page',
                'a.create_date',
                'a.is_verified_phone',
                'a.flag_match',
                'a.create_date'
            )
            ->addSelect(DB::raw('(case 
                    WHEN m.password IS NOT NULL THEN 1
                    else 0
                    end
                    ) as activation'))
            ->addSelect(DB::raw('(case 
                    WHEN m.email IS NOT NULL THEN 1
                    else 0
                    end
                    ) as membership_type'))
            ->orderBy('a.create_date', 'DESC')
            ->whereNull('a1.email');
            

        /* Date Range Filter */
        if ($startDate && $endDate && $data) {
            $startDate = new Carbon($startDate);
            $endDate = new Carbon($endDate);

            $data->where('a.create_date', '>=', $startDate->toDateTimeString())
                ->where('a.create_date', '<=', $endDate->endOfDay()->toDateTimeString());
        }

        return $data;
    }


    public static function vipApplicantSearch($searchBy, $keyword, $startDate = null, $endDate = null)
    {
        $data = DB::table('vip_member AS v')
            ->join('app_applicant AS a', 'v.email', 'a.email')
            ->leftJoin('app_applicant as a1', function ($join) {
                $join->on('a.email', '=', 'a1.email')
                    ->whereRaw(DB::raw('a.create_date < a1.create_date'));
            })
            ->select(
                'a.id',
                'a.app_id',
                'a.NAME as name',
                'a.email',
                'a.phoneno',
                'a.age_apply',
                'a.gross_salary',
                'a.amount',
                'a.tenure',
                'a.affiliate_id',
                'a.sector',
                'a.ic_no',
                'a.dob',
                'a.employment_type',
                'a.industry',
                'a.ic_no',
                'a.ic_type',
                'a.states',
                'a.last_successful_page',
                'a.create_date',
                'a.is_verified_phone',
                'a.flag_match'
            )
            ->addSelect(DB::raw('(case 
                    WHEN v.password IS NOT NULL THEN 1
                    else 0
                    end
                    ) as activation'))
            ->addSelect(DB::raw('(case 
                    WHEN v.email IS NOT NULL THEN 1
                    else 0
                    end
                    ) as membership_type'))
            ->orderBy('a.create_date', 'DESC')
            ->whereNull('a1.email');
            

        /* Date Range Filter */
        if ($startDate && $endDate && $data) {
            $startDate = new Carbon($startDate);
            $endDate = new Carbon($endDate);

            $data->where('a.create_date', '>=', $startDate->toDateTimeString())
                ->where('a.create_date', '<=', $endDate->endOfDay()->toDateTimeString());
        }

        switch ($searchBy) {
            case 'byName':
                $data->where('a.name', 'like', '%' . $keyword . '%');
                break;
            case 'byIC':
                $data->where('a.ic_no', 'like', '%' . $keyword . '%');
                break;
            case 'byEmail':
                $data->where('a.email', 'like', '%' . $keyword . '%');
                break;
            case 'byPhone':
                $data->where('a.phoneno', 'like', '%' . $keyword . '%');
                break;
            case 'byAppId':
                $data->where('a.app_id', 'like', '%' . $keyword . '%');
                break;
            default:
                $data = null;
                break;
        }

        return $data;
    }

    public static function afMemberList($startDate = null, $endDate = null)
    {
        $data = DB::table('af_member AS m')
            ->leftJoin('vip_member AS v', 'v.email', 'm.email_address')
            ->leftJoin('app_applicant AS a', 'm.email_address', 'a.email')
            ->leftJoin('app_applicant as a1', function ($join) {
                $join->on('a.email', '=', 'a1.email')
                    ->whereRaw(DB::raw('a.create_date < a1.create_date'));
            })
            ->select(
                'a.id' ,
                'a.app_id',
                'm.email_address',
                'a.NAME as name',
                'a.phoneno',
                'a.age_apply',
                'a.gross_salary',
                'a.amount',
                'a.tenure',
                'a.affiliate_id',
                'a.sector',
                'a.dob',
                'a.employment_type',
                'a.industry',
                'a.ic_no',
                'a.ic_type',
                'a.states',
                'a.last_successful_page',
                'a.create_date',
                'a.is_verified_phone',
                'a.flag_match',
                'a.create_date'
            )
            ->addSelect(DB::raw('(case 
                    WHEN v.password IS NOT NULL THEN 1
                    else 0
                    end
                    ) as activation'))
            ->addSelect(DB::raw('(case 
                    WHEN v.email IS NOT NULL THEN 1
                    else 0
                    end
                    ) as membership_type'))
            ->whereNull('v.email')
            ->whereNull('a1.email')
            ->orderBy('a.create_date', 'DESC');
            

        /* Date Range Filter */
        if ($startDate && $endDate && $data) {
            $startDate = new Carbon($startDate);
            $endDate = new Carbon($endDate);

            $data->where('a.create_date', '>=', $startDate->toDateTimeString())
                ->where('a.create_date', '<=', $endDate->endOfDay()->toDateTimeString());
        }

        return $data;
    }


    public static function afApplicantSearch($searchBy, $keyword, $startDate = null, $endDate = null)
    {
        $data = DB::table('af_member AS m')
            ->leftJoin('vip_member AS v', 'v.email', 'm.email_address')
            ->leftJoin('app_applicant AS a', 'm.email_address', 'a.email')
            ->leftJoin('app_applicant as a1', function ($join) {
                $join->on('a.email', '=', 'a1.email')
                    ->whereRaw(DB::raw('a.create_date < a1.create_date'));
            })
            ->select(
                'a.id',
                'a.app_id',
                'm.email_address',
                'a.NAME as name',
                'a.phoneno',
                'a.age_apply',
                'a.gross_salary',
                'a.amount',
                'a.tenure',
                'a.affiliate_id',
                'a.sector',
                'a.dob',
                'a.employment_type',
                'a.industry',
                'a.ic_no',
                'a.ic_type',
                'a.states',
                'a.last_successful_page',
                'a.create_date',
                'a.is_verified_phone',
                'a.flag_match',
                'a.create_date'
            )
            ->addSelect(DB::raw('(case 
                    WHEN v.password IS NOT NULL THEN 1
                    else 0
                    end
                    ) as activation'))
            ->addSelect(DB::raw('(case 
                    WHEN v.email IS NOT NULL THEN 1
                    else 0
                    end
                    ) as membership_type'))
            ->whereNull('v.email')
            ->whereNull('a1.email')
            ->orderBy('a.create_date', 'DESC');
            // ->groupBy('d.applicantid');

        /* Date Range Filter */
        if ($startDate && $endDate && $data) {
            $startDate = new Carbon($startDate);
            $endDate = new Carbon($endDate);

            $data->where('a.create_date', '>=', $startDate->toDateTimeString())
                ->where('a.create_date', '<=', $endDate->endOfDay()->toDateTimeString());
        }

        switch ($searchBy) {
            case 'byName':
                $data->where('a.name', 'like', '%' . $keyword . '%');
                break;
            case 'byIC':
                $data->where('a.ic_no', 'like', '%' . $keyword . '%');
                break;
            case 'byEmail':
                $data->where('a.email', 'like', '%' . $keyword . '%');
                break;
            case 'byPhone':
                $data->where('a.phoneno', 'like', '%' . $keyword . '%');
                break;
            case 'byAppId':
                $data->where('a.app_id', 'like', '%' . $keyword . '%');
                break;
            default:
                $data = null;
                break;
        }

        return $data;
    }

    public static function globalSearchList($bankID, $src_id, $src_value, $startDate, $endDate)
    {
        $applications = DB::table('app_applicant AS a')
            ->join('app_applicantdetail AS d', 'd.applicantid', 'a.id')
            ->join('ghf_details AS g', 'g.app_id', 'a.app_id')
            ->join('product_pf AS pf', 'pf.prod_id', 'd.prod_id')
            ->select('a.app_id', 'a.reference_id', 'a.name as appname', 
                'd.status as app_status', 'd.id as detail_id', 'd.bank_id',
                DB::raw('DATE_FORMAT(a.create_date, "%d-%m-%Y") as apply_date'),
                DB::raw("(
                    CASE WHEN (d.status = 'uw_approved' OR d.status = 'ghf_fwd_approved') and (adhoc_status is NULL or adhoc_status = '') then 'ACTIVE'
                    WHEN d.status <> 'uw_approved' and (adhoc_status is not NULL and adhoc_status <> '') then adhoc_status
                    ELSE '-' END
                ) as policy_status"),
                DB::raw("(
                    CASE WHEN (d.status = 'uw_approved' OR d.status = 'ghf_fwd_approved') and (adhoc_status is NULL or adhoc_status = '') then DATE_FORMAT(disbursement_date, '%d-%m-%Y')
                    WHEN d.status <> 'uw_approved' and (adhoc_status is not NULL and adhoc_status <> '') then DATE_FORMAT(adhoc_ts, '%d-%m-%Y')
                    ELSE '-' END
                ) as policy_date")
                )
            ->groupBy('d.id')
            ->orderBy('a.create_date', 'DESC');

        if ($src_id == 'app_id') {
            $applications->where('a.app_id', $src_value);
        } else if ($src_id == 'reff_id') {
            $applications->where('g.reference_id', $src_value);
        } else if ($src_id == 'phone_no') {
            $applications->where('a.phoneno', $src_value);
        } else if ($src_id == 'name') {
            $applications->where('a.name', 'like', '%'.$src_value.'%');
        } else if ($src_id == 'no_ic') {
            $applications->where('a.ic_no', 'like', '%'.$src_value.'%');
        } else if ($src_id == 'email') {
            $applications->where('a.email', $src_value);
        } else {
            $applications->where('a.ic_no', 'NOTFOUND');
        }

        if ($bankID !== 1) {
            $applications->where('d.bank_id', $bankID);
        }
        /* Date Range Filter */
        if ($startDate && $endDate && $applications) {
            $startDate = new Carbon($startDate);
            $endDate = new Carbon($endDate);
            $applications->where('a.create_date', '>=', $startDate->toDateTimeString())
                ->where('a.create_date', '<=', $endDate->endOfDay()->toDateTimeString());
        }
        return $applications;
    }

    public static function getApplicantListGoHalal($list = null, $startDate = null, $endDate = null)
    {
        $data = null;
        if ($list == "exceptional") {
            $data = DB::table('app_applicant AS a')
                ->join('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                ->join('ghf_details AS g', 'g.app_id', 'a.app_id')
                ->join('product_pf AS p', 'p.prod_id', 'd.prod_id')
                ->join('bank_info AS f', 'f.bank_id', 'p.bank_id')
                ->select('a.id', 'd.id as detail_id', 'a.app_id', 'a.NAME as name', 'a.email', 'a.phoneno', 'a.age_apply',
                    'a.gross_salary', 'a.amount', 'a.tenure',
                    'a.sector', 'a.ic_no', 'd.STATUS as status', 'f.bank_name', 'p.prod_id as product_id',
                    'p.title as product_name', 'a.create_date', 'd.status', 'd.read_by_internal', 'a.is_verified_phone', 'a.flag_match', 'd.salvage', 'a.test_flag', 'g.takaful_eligibility')
                ->where('g.adhoc_status', $list)
                ->orderBy('d.create_date', 'DESC') // Modify in IF-726 sort by create_date in app_applicantdetail table
                ->distinct();
        } else if ($list == "ghf_confirm_disbursement") {
            $data = DB::table('app_applicant AS a')
                ->join('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                ->join('ghf_details AS g', 'g.app_id', 'a.app_id')
                ->join('product_pf AS p', 'p.prod_id', 'd.prod_id')
                ->join('bank_info AS f', 'f.bank_id', 'p.bank_id')
                ->select('a.id', 'd.id as detail_id', 'a.app_id', 'a.NAME as name', 'a.email', 'a.phoneno', 'a.age_apply',
                    'a.gross_salary', 'a.amount', 'a.tenure',
                    'a.sector', 'a.ic_no', 'd.STATUS as status', 'f.bank_name', 'p.prod_id as product_id',
                    'p.title as product_name', 'a.create_date', 'd.status', 'd.read_by_internal', 'a.is_verified_phone', 'a.flag_match', 'd.salvage', 'a.test_flag', 'g.takaful_eligibility')
                ->whereIn('d.status', ['ghf_confirm_disbursement','ghf_fwd_approved','ghf_fwd_rejected','ghf_fwd_pending_review'])
                ->orderBy('d.create_date', 'DESC') // Modify in IF-726 sort by create_date in app_applicantdetail table
                ->distinct();
        } else {                    
            $data = DB::table('app_applicant AS a')
                ->join('app_applicantdetail AS d', 'd.applicantid', 'a.id')
                ->join('ghf_details AS g', 'g.app_id', 'a.app_id')
                ->join('product_pf AS p', 'p.prod_id', 'd.prod_id')
                ->join('bank_info AS f', 'f.bank_id', 'p.bank_id')
                ->select('a.id', 'd.id as detail_id', 'a.app_id', 'a.NAME as name', 'a.email', 'a.phoneno', 'a.age_apply',
                    'a.gross_salary', 'a.amount', 'a.tenure',
                    'a.sector', 'a.ic_no', 'd.STATUS as status', 'f.bank_name', 'p.prod_id as product_id',
                    'p.title as product_name', 'a.create_date', 'd.status', 'd.read_by_internal', 'a.is_verified_phone', 'a.flag_match', 'd.salvage', 'a.test_flag', 'g.takaful_eligibility')
                ->where('d.status', $list)
                ->orderBy('d.create_date', 'DESC') // Modify in IF-726 sort by create_date in app_applicantdetail table
                ->distinct();
        }

        /* Date Range Filter */
        if ($startDate && $endDate && $data) {
            $startDate = new Carbon($startDate);
            $endDate = new Carbon($endDate);

            $data->where('a.create_date', '>=', $startDate->toDateTimeString())
                ->where('a.create_date', '<=', $endDate->endOfDay()->toDateTimeString());
        }

        return $data;

    }
}
