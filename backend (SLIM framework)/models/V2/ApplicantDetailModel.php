<?php
namespace models\V2;

use Illuminate\Database\Eloquent\Model;

class ApplicantDetailModel extends Model
{
    protected $table = 'app_applicantdetail';

    protected $primaryKey = 'id';

    protected $hidden = [
        'id', 'applicantid', 'bank_id', 'prod_id', 'check', 'create_date', 'status_date', 'logger', 'review_remark', 'review_date', 'salvage'
    ];

    protected $fillable = ['applicantid', 'bank_id', 'applicantid', 'prod_id', 'status', 'check', 'status_date', 'review_date', 'salvage'];

    public $timestamps = false;

    public function bankInfo()
    {
        return $this->belongsTo(BankInfoModel::class, 'bank_id', 'bank_id');
    }

    public function productInfo()
    {
        return $this->belongsTo(ProductPfModel::class, 'prod_id', 'prod_id');
    }

    public function applicantInfo()
    {
        return $this->belongsTo(ApplicantModel::class, 'applicantid', 'id');
    }
}
