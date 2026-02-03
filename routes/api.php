<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DisbursementApiController;
use App\Http\Controllers\Api\CollectionApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// External system integration APIs
// These endpoints receive data from other systems and insert into database

Route::prefix('finance')->group(function () {
    // Disbursements API - receive and store disbursement data
    Route::post('/disbursements/receive', [DisbursementApiController::class, 'receive'])->name('api.disbursements.receive');
    Route::get('/disbursements/status/{id}', [DisbursementApiController::class, 'status'])->name('api.disbursements.status');
    
    // Collections API - receive and store collection data
    Route::post('/collections/receive', [CollectionApiController::class, 'receive'])->name('api.collections.receive');
    Route::get('/collections/status/{id}', [CollectionApiController::class, 'status'])->name('api.collections.status');
});
