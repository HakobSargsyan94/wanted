<?php

use App\Http\Controllers\ExcelImportController;
use App\Http\Controllers\RowController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/import', [ExcelImportController::class, 'import']);
Route::get('/rows', [RowController::class, 'index']);
