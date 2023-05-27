<?php

namespace models\Internal;

use Illuminate\Database\Eloquent\Model;

class InternalUserModel extends Model
{
    protected $table = 'internal_users';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'full_name',
        'email',
        'password',
        'jwt_token',
    ];

    protected $hidden = [
        'password',
        'id'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Set Password Mutator
     * @param $value
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = password_hash($value, PASSWORD_DEFAULT);
    }
}