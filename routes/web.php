<?php

use Illuminate\Support\Facades\Route;
use JibayMcs\SurveyJs\Http\Controllers\SurveyFileController;

$guard = config('survey-js.file_upload.auth_guard');
$authMiddleware = $guard ? "auth:{$guard}" : 'auth';

Route::prefix('survey-js')
    ->middleware(['web', $authMiddleware])
    ->group(function () {
        Route::post('upload', [SurveyFileController::class, 'upload'])->name('survey-js.upload');
        Route::get('download', [SurveyFileController::class, 'download'])->name('survey-js.download');
        Route::delete('delete', [SurveyFileController::class, 'delete'])->name('survey-js.delete');
    });
