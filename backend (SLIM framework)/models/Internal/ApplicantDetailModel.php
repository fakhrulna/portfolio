<?php

namespace models\Internal;

use Illuminate\Database\Eloquent\Model;

class ApplicantDetailModel extends Model
{
    protected $table = 'app_applicantdetail';

    protected $hidden = [
        'id', 'applicantid', 'bank_id', 'prod_id', 'check', 'create_date', 'status_date', 'logger', 'review_remark', 'review_date'
    ];

    protected $fillable = [
        'status'
    ];

    public $timestamps = false;
}