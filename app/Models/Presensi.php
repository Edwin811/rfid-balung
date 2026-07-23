<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Presensi extends Model
{
    use HasFactory;

    protected $table = 'presensi';

    protected $fillable = [
        'pegawai_id',
        'tanggal',
        'jam_masuk',
        'jam_pulang',
        'tipe_verifikasi_masuk',
        'tipe_verifikasi_pulang',
        'status',
        'keterangan',
        'updated_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id')->withTrashed();
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
