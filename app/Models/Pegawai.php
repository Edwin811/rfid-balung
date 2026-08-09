<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pegawai extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pegawai';

    protected $fillable = [
        'id_mesin',
        'uid_kartu',
        'jumlah_jari',
        'nama_lengkap',
        'jabatan',
        'status_kepegawaian',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function presensi(): HasMany
    {
        return $this->hasMany(Presensi::class, 'pegawai_id');
    }

    public function perizinan(): HasMany
    {
        return $this->hasMany(Perizinan::class, 'pegawai_id');
    }
}
