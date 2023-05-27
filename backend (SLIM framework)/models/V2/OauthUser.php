<?php
namespace models\V2;

use Illuminate\Database\Eloquent\Model;

class OauthUser extends Model
{
    protected $table = 'oauth_users';
    protected $guarded = [];
    protected $primaryKey = 'userid';

    public $timestamps = false;
}