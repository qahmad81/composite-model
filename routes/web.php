<?php

use App\Models\CompositeModule;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('landing');

Route::get('/pages/{slug}', [PageController::class, 'show'])->name('page.show');

Route::middleware(['auth'])->group(function () {
    Route::get('/admin/flow-editor/{compositeModule}', function (CompositeModule $compositeModule) {
        return view('flow-editor', ['compositeModule' => $compositeModule]);
    })->name('flow-editor');
});
