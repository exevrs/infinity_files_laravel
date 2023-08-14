<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

require base_path('routes/files/ini.php');
require base_path('routes/infinity/ini.php');

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


$_ping = function () {
    return response("here",200);
};


Route::get("/ping", $_ping);


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});