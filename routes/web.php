<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Middleware\VerifyBotSecret;

Route::get('/', function () {
    return view('welcome');
});
Route::post('/webhook', [WebhookController::class, 'handle'])->middleware(VerifyBotSecret::class);
