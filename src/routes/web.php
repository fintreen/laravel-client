<?php

use Illuminate\Support\Facades\Route;

Route::group([ 'as' => 'fintreen.', 'prefix' => 'fintreen'], function() {
    Route::post('/calculate', [\Fintreen\Laravel\app\Http\Controllers\FintreenController::class,'calculateAction' ])->name('calculate');
});
//Route::post('/fintreen/transaction/create', [\App\Http\Controllers\FintreenController::class,'createTransactionAction' ])->name('fintreen-create');
//Route::match(['get', 'post'],'/fintreen/webhook', [\App\Http\Controllers\FintreenController::class,'webHookAction' ])->name('fintreen-webhook');