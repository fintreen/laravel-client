<?php

use Illuminate\Support\Facades\Route;

Route::post('/fintreen/calculate', [\App\Http\Controllers\FintreenController::class,'calculateAction' ])->name('fintreen-calculate');
//Route::post('/fintreen/transaction/create', [\App\Http\Controllers\FintreenController::class,'createTransactionAction' ])->name('fintreen-create');
//Route::match(['get', 'post'],'/fintreen/webhook', [\App\Http\Controllers\FintreenController::class,'webHookAction' ])->name('fintreen-webhook');