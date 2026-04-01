<?php

use Illuminate\Support\Facades\Route;
use JibayMcs\SurveyJs\Http\Controllers\SurveyFileController;

Route::prefix('survey-js')->group(function () {
    Route::post('upload', [SurveyFileController::class, 'upload'])->name('survey-js.upload');
    Route::get('download', [SurveyFileController::class, 'download'])->name('survey-js.download');
    Route::delete('delete', [SurveyFileController::class, 'delete'])->name('survey-js.delete');
});
