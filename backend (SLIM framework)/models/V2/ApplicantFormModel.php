<?php
namespace models\V2;

use Illuminate\Database\Eloquent\Model;

class ApplicantFormModel extends Model
{
    protected $table = 'applicant_form_document';

    protected $primaryKey = 'id';

    protected $fillable = [
        'applicant_id', 'detail_id', 'prod_id', 'bank_id', 'hash', 'mime_type', 'blob_data'
    ];

    public function applicantInfo()
    {
        return $this->belongsTo(ApplicantModel::class, 'applicant_id', 'id');
    }

    public function detailInfo()
    {
        return $this->belongsTo(ApplicantDetailModel::class, 'detail_id', 'id');
    }

    public function productInfo()
    {
        return $this->belongsTo(ApplicantModel::class, 'prod_id', 'prod_id');
    }

    public function bankInfo()
    {
        return $this->belongsTo(BankInfoModel::class, 'bank_id', 'bank_id');
    }

}