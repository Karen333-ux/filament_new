<?php

use App\Http\Controllers\Auth\TelegramLoginController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/auth/telegram/callback', TelegramLoginController::class)
    ->name('telegram.login.callback');
