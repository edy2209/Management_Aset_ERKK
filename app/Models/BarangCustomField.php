<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangCustomField extends Model
{
    use HasFactory;

    protected $fillable = ['barang_id', 'key', 'value'];

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }
}