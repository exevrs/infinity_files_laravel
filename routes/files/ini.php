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
        $filePath = str_replace("\\", "x", $filePath);

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

$get_file = function () {
    return response(Storage::disk('local')->get('Test1.txt'), 200);
};


$upload_file = function (Request $request) {

    $file = $request->file('file');
    $fileName = $file->getClientOriginalName();

    $path = Storage::disk('local')->putFileAs('uploads', $file, $fileName);


    return response('ok', 200);

};



function writeFileToStorage(string $fileName, string $fileContent)
{
    $path = Storage::disk('local')->put($fileName, $fileContent);
    return $path;
}


Route::get('/files/list-files', $list_files);
Route::get('/files/get-file', $get_file);


//Posts
Route::post('/files/upload-file', $upload_file);