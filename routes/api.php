<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AdmsController;

Route::post('/adms/attendance', [AdmsController::class, 'handleAttendance']);
Route::get('/adms/attendance', [AdmsController::class, 'handshake']);
Route::post('/adms/heartbeat', [AdmsController::class, 'heartbeat']);
Route::get('/adms/heartbeat', [AdmsController::class, 'heartbeat']);
Route::get('/adms/pegawai-list', [AdmsController::class, 'getPegawaiList']);
