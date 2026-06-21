<?php

use App\Http\Controllers\PrescriptionPdfController;
use App\Http\Controllers\QuotationPdfController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth'])->group(function () {
    Route::get('/prescriptions/{prescription}/pdf', [PrescriptionPdfController::class, 'show'])
        ->name('prescriptions.pdf');
    Route::get('/reports/appointments/pdf', [ReportController::class, 'appointmentsPdf'])
        ->name('reports.appointments.pdf');
    Route::get('/reports/clinical-record/{pet}/pdf', [ReportController::class, 'clinicalRecordPdf'])
        ->middleware(['auth'])
        ->name('reports.clinical-record.pdf');
    Route::get('/reports/upcoming-controls/pdf', [ReportController::class, 'upcomingControlsPdf'])
        ->middleware(['auth'])
        ->name('reports.upcoming-controls.pdf');

    Route::get('/reports/attended-patients/pdf', [ReportController::class, 'attendedPatientsPdf'])
        ->middleware(['auth'])
        ->name('reports.attended-patients.pdf');
    Route::get(
        '/quotations/{quotation}/pdf',
        [QuotationPdfController::class, 'generate']
    )->name('quotations.pdf');
});