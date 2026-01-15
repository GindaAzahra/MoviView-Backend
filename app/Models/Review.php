<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $table = 'reviews';
    protected $primaryKey = 'id_review';
    public $incrementing = false;
    protected $keyType = 'string'; 
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'id_review',
        'id_user',
        'id_movie',
        'rating',
        'review',
    ];
}
