<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $table = 'reviews';

    protected $primaryKey = 'id_review';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_review',
        'id_user',
        'id_movie',
        'rating',
        'review',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function movie()
    {
        return $this->belongsTo(Movie::class, 'id_movie', 'id_movie');
    }
}
