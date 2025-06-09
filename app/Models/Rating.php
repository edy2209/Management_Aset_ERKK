<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'rating',
        'feedback',
    ];

    // Di model Rating.php
    public function user()
    {
        return $this->belongsTo(Peminjam::class, 'user_id');
    }

}
