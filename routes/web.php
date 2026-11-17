<?php

use App\Http\Controllers\LogbookController;
use App\Http\Controllers\SlideController;
use App\Http\Middleware\LocalPreview;
use Illuminate\Support\Facades\Route;
Route::middleware(LocalPreview::class)->group(function () {
    Route::get('/', [LogbookController::class,'login'])->name('login');
    Route::post('/login', [LogbookController::class,'authenticate'])->middleware('throttle:20,1')->name('login.store');
    Route::post('/logout', [LogbookController::class,'logout'])->name('logout');
    Route::get('/projek', [LogbookController::class,'projects'])->name('projects');
    Route::get('/projek/{project}/lampiran/{section}/{index}',[LogbookController::class,'attachment'])->whereNumber(['section','index'])->name('attachment');
    Route::post('/projek/{project}/slides',[SlideController::class,'store'])->name('slides.store');
    Route::post('/projek/{project}/slides/{item}/pages',[SlideController::class,'addPages'])->name('slides.pages.store');
    Route::post('/projek/{project}/slides/{item}/pages/{index}/move',[SlideController::class,'movePage'])->whereNumber('index')->name('slides.pages.move');
    Route::get('/projek/{project}/slides/{item}/files/{index}',[SlideController::class,'file'])->whereNumber('index')->name('slides.file');
    Route::get('/projek/{project}/buku-log/pta1/{page}',[LogbookController::class,'show'])->whereIn('page',['schedule','meeting'])->name('pta1.document');
    Route::post('/projek/{project}/buku-log/pta1/{page}',[LogbookController::class,'save'])->whereIn('page',['schedule','meeting'])->name('pta1.document.save');
    Route::get('/projek/{project}/buku-log/pta2/{page}',[LogbookController::class,'show'])->whereIn('page',['schedule','meeting'])->name('pta2.document');
    Route::post('/projek/{project}/buku-log/pta2/{page}',[LogbookController::class,'save'])->whereIn('page',['schedule','meeting'])->name('pta2.document.save');
    Route::get('/projek/{project}/pta2/{page}',[LogbookController::class,'show'])->whereIn('page',['schedule','meeting'])->name('pta2.legacy');
    Route::post('/projek/{project}/pta2/{page}',[LogbookController::class,'save'])->whereIn('page',['schedule','meeting'])->name('pta2.legacy.save');
    Route::get('/projek/{project}/{page}',[LogbookController::class,'show'])->name('document');
    Route::post('/projek/{project}/{page}',[LogbookController::class,'save'])->name('document.save');
});
