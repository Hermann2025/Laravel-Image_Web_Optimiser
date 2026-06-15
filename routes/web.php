<?php

use App\Http\Controllers\ImageOptimizerController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ImageOptimizerController::class, 'index'])->name('optimizer.index');
Route::get('/results/{sessionId}', [ImageOptimizerController::class, 'results'])->name('optimizer.results');