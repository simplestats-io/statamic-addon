<?php

use Illuminate\Support\Facades\Route;
use SimpleStatsIo\StatamicAddon\Http\Controllers\DashboardController;

Route::get('/simplestats', [DashboardController::class, 'index'])->name('simplestats.dashboard');
Route::get('/simplestats/all', [DashboardController::class, 'all'])->name('simplestats.all');
Route::get('/simplestats/grouped/{type}', [DashboardController::class, 'grouped'])->name('simplestats.grouped');
