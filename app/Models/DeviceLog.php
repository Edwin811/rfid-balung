<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceLog extends Model
{
    use HasFactory;

    protected $table = 'device_logs';

    public $timestamps = false;

    protected $fillable = [
        'id_mesin_device',
        'timestamp_absen',
        'tipe_verifikasi',
        'raw_data',
        'is_processed',
        'created_at',
    ];

    protected $casts = [
        'timestamp_absen' => 'datetime',
        'raw_data' => 'array',
        'is_processed' => 'boolean',
        'created_at' => 'datetime',
    ];
}
