<?php

use App\Http\Controllers\PrescriptionPdfController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});



Route::get('/prescriptions/{prescription}/pdf', [PrescriptionPdfController::class, 'show'])
    ->name('prescriptions.pdf');