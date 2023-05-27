<?php

namespace models\Cms;

use Illuminate\Database\Eloquent\Model;

class ArticleModel extends Model
{
    protected $table = 'article';

    protected $primaryKey = 'articleid';

    public $timestamps = false;

    protected $fillable = [
        'meta_title',
        'meta_description',
        'meta_keywords',
        'robot_meta_tag'
    ];

    protected $hidden = [
        'id'
    ];
}