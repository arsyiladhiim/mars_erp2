<?php

use App\Http\Controllers\Ai\ChatController;
use App\Http\Controllers\Document\ShareLinkController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/s/{token}', [ShareLinkController::class, 'show'])->name('share-links.show');

Route::middleware(['auth'])->prefix('ai')->name('ai.')->group(function () {
    Route::get('/', [ChatController::class, 'index'])->name('index');
    Route::get('/conversations/{conversation}', [ChatController::class, 'show'])->name('conversations.show');
    Route::post('/conversations', [ChatController::class, 'store'])->name('conversations.store');
    Route::post('/conversations/{conversation}/messages', [ChatController::class, 'streamMessage'])->name('messages.stream');
});
