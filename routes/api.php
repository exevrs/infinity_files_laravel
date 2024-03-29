<?php

use App\Http\Controllers\FileManagerController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\StatisticController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

//Projects
Route::prefix('infinity/projects')->group(function () {
    //Put
    Route::put('make-cache', [ProjectController::class, 'bakeCache']);
});

//Files
Route::prefix('files')->group(function () {
    //Get
    Route::get('list-files', [FileManagerController::class, 'listFiles']);
    Route::get('get-file', [FileManagerController::class, 'getFile']);
    Route::get('get-file-streamed', [FileManagerController::class, 'getFileStreamed']);
    //Posts
    Route::post('upload-file', [FileManagerController::class, 'uploadFile']);
    //Delete
    Route::delete('delete-folder', [FileManagerController::class, 'deleteFolder']);
});

//Statistics
Route::prefix('statistics')->group(function () {
    Route::get('get-statistic', [StatisticController::class, 'getStat']);
});
