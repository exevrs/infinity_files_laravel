<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

$lambdaX = function () {
    return response('ok', 200);

};

Route::get("/ping", $lambdaX);

Route::get('/', function () {
    return view('welcome');
});