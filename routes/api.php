<?php

use App\Http\Controllers\ImageOptimizerController;
use Illuminate\Support\Facades\Route;

Route::post('/upload', [ImageOptimizerController::class, 'upload']);
Route::post('/optimize', [ImageOptimizerController::class, 'optimize']);
Route::get('/status/{sessionId}', [ImageOptimizerController::class, 'status']);
Route::get('/download/{id}', [ImageOptimizerController::class, 'download'])->name('api.download');
Route::get('/download-all/{sessionId}', [ImageOptimizerController::class, 'downloadAll'])->name('api.download-all');
Route::delete('/images/{id}', [ImageOptimizerController::class, 'delete']);
Route::delete('/session/{sessionId}', [ImageOptimizerController::class, 'deleteSession']);