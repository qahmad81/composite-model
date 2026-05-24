<?php

use App\Http\Controllers\Api\V1\ChatCompletionsController;
use App\Http\Controllers\Api\V1\ModelsController;
use App\Http\Controllers\Api\FlowEditorController;
use App\Http\Middleware\AuthenticateToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(AuthenticateToken::class)->group(function () {
    Route::post('/v1/chat/completions', [ChatCompletionsController::class, 'store']);
    Route::get('/v1/models', [ModelsController::class, 'index']);
});

Route::prefix('flow-editor')->group(function () {
    Route::get('/{compositeModule}', [FlowEditorController::class, 'show']);
    Route::put('/{compositeModule}', [FlowEditorController::class, 'update']);
    Route::get('/{compositeModule}/models', [FlowEditorController::class, 'models']);
});
