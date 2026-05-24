<?php

use App\Http\Controllers\Api\V1\ChatCompletionsController;
use App\Http\Controllers\Api\V1\ModelsController;
use App\Http\Middleware\AuthenticateToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(AuthenticateToken::class)->group(function () {
    Route::post('/v1/chat/completions', [ChatCompletionsController::class, 'store']);
    Route::get('/v1/models', [ModelsController::class, 'index']);
});
