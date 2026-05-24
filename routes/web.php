<?php

use App\Models\CompositeModule;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/admin/flow-editor/{compositeModule}', function (CompositeModule $compositeModule) {
        return view('flow-editor', ['compositeModule' => $compositeModule]);
    })->name('flow-editor');
});
