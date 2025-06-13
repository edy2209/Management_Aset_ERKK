<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\DB;
use App\Models\PeminjamanKonfirmasi;

class BarangPeminjaman extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'barang_peminjaman';

    protected $fillable = [
        'peminjam_id',
        'barang_id',
        'kbarang_id',
        'jumlah',
        'tanggal_pinjam',
        'tanggal_pengembalian',
        'status',
    ];


    public function peminjam()
    {
        return $this->belongsTo(Peminjam::class);
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    public function kbarang()
    {
        return $this->belongsTo(Kbarang::class);
    }
    public function konfirmasi()
    {
        return $this->hasOne(PeminjamanKonfirmasi::class, 'peminjaman_id');
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}