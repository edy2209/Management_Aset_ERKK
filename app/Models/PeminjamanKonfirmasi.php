<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class PeminjamanKonfirmasi extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'peminjaman_konfirmasi';

    protected $fillable = [
        'peminjaman_id',
        'user_id',
        'status',
        'catatan'
    ];

   public function peminjaman()
    {
        return $this->belongsTo(BarangPeminjaman::class, 'peminjaman_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}