<?php

use App\Http\Controllers\DocxController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/',[DocxController::class, 'index']);
Route::post('/ask-upload-document',[DocxController::class, 'AskAndUpload']);
Route::post('/delete-doc',[DocxController::class, 'delete']);
