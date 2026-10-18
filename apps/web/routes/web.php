<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::get('/health', fn () => response()->json(['status' => 'ok']))->name('health');
