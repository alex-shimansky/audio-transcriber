<?php

use App\Http\Controllers\S3MultipartController;
use App\Http\Controllers\TranscriptionController;
use Illuminate\Support\Facades\Route;

Route::post('/s3/multipart/initiate', [S3MultipartController::class, 'initiate']);
Route::post('/s3/multipart/sign-part', [S3MultipartController::class, 'signPart']);
Route::post('/s3/multipart/complete', [S3MultipartController::class, 'complete']);
Route::post('/s3/multipart/abort', [S3MultipartController::class, 'abort']);

Route::get('/status/{id}', [TranscriptionController::class, 'status'])->name('transcription.status');


