<?php

use Illuminate\Support\Facades\Route;

Route::group([ 'as' => 'fintreen.', 'prefix' => 'fintreen'], function() {
    Route::post('/calculate', [\Fintreen\Laravel\app\Http\Controllers\FintreenController::class,'calculateAction' ])->name('calculate');
    Route::post('/fintreen/webhook', [\Fintreen\Laravel\app\Http\Controllers\FintreenController::class,'webHookAction' ])->name('webhook');
});