<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/create-folder', [FileController::class, 'createFolder']);

    Route::post('/upload', [FileController::class, 'upload']);

    Route::delete('/delete/{id}', [FileController::class, 'delete'])->where(['id' => '[\d]+']);

    Route::patch('/rename/{id}', [FileController::class, 'rename'])->where(['id' => '[\d]+']);

    Route::get('/download/{id}', [FileController::class, 'download'])->where(['id' => '[\d]+']);

    Route::get('/list', [FileController::class, 'list']);

    Route::get('/size/{folder?}', [FileController::class, 'size']);
});

Route::post('/auth/register', [AuthController::class, 'register']);

Route::post('/auth/login', [AuthController::class, 'login']);
