<?php

use App\Http\Controllers\GrabRecordController;
use Illuminate\Support\Facades\Route;

Route::get('/grabs', [GrabRecordController::class, 'index'])->name('grab-record.index');
