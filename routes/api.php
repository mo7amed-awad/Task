<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TagController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::controller(AuthController::class)->group(function(){
    Route::post('register','register');
    Route::post('login','login');
    Route::post('logout','logout')->middleware('auth:sanctum');
});


Route::controller(TagController::class)->middleware('auth:sanctum')->group(function () {
    Route::get('/tags', 'index');
    Route::post('/tags', 'store');
    Route::get('/tag/{id}', 'show');
    Route::put('/tags/{id}', 'update');
    Route::delete('/tags/{id}', 'destroy');
});