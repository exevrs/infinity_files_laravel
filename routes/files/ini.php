<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


$list_files = function () {
    $result = array();

    $disc = Storage::disk('local');

    foreach ($disc->allFiles() as $file) {

        $size = $disc->size($file);
        $lastModified = $disc->lastModified($file);

        $filePath = (string) $file;

        for ($i = 0; $i < strlen($filePath); $i++) {
            Log::info($filePath[$i]);
        }

        $filePath = str_replace("//", "x", $filePath);

        array_push($result, [
            "file_path" => $filePath,
            "size" => $size,
            "modified" => $lastModified
        ]);
    }

    return response([
        "files" => $result
    ], 200);
};

$get_file = function (Request $request) {

    $partition = $request->query("partition");
    $filename = $request->query("file_name");

    $file_to_name = "" . $partition . "/" . $filename;

    $file = Storage::disk('local')->get($file_to_name);

    if ($file) {
        return response($file, 200);
    } else {
        return response("Not found", 404);
    }
};

$get_file_response = function (Request $request) {
    $partition = $request->query("partition");
    $filename = $request->query("file_name");

    $file_to_name = "" . $partition . "/" . $filename;

    $path = storage_path('app/' . $file_to_name);

    if (Storage::disk('local')->exists($file_to_name)) {
        return response()->streamDownload(function () use ($path) {
            $fd = fopen($path, 'rb');
            while (!feof($fd)) {
                echo fread($fd, 2048);
            }
        }, $filename);
    } else {
        return response("Not found", 404);
    }
};

$delete_folder = function (Request $request) {
    $partition = $request->query("partition");
    $path = $request->query("pathname");

    $file_to_name = "" . $partition . "/" . $path;

    if (Storage::disk('local')->exists($file_to_name)) {
        Storage::disk('local')->deleteDirectory($file_to_name);
        return response("Directory " . $file_to_name . " deleted", 200);
    } else {
        return response("Not found", 404);
    }
};


$upload_file = function (Request $request) {

    $code = $request->query("code");


    if (getenv("INFINITY_FILES_API_KEY") == $code) {

        $partition = $request->query("partition");
        $filename = $request->query("file_name");
        $file = $request->file('file');

        Storage::disk('local')->putFileAs($partition, $file, $filename);

        return response('ok', 200);
    } else {
        return response('Access denied', 403);
    }
};


//Get
Route::get('/files/list-files', $list_files);
Route::get('/files/get-file', $get_file);
Route::get('/files/get-file-response', $get_file_response);


//Posts
Route::post('/files/upload-file', $upload_file);

//Delete
Route::delete('/files/delete-folder', $delete_folder);
