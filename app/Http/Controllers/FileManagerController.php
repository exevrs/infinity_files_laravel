<?php

namespace App\Http\Controllers;

use App\Models\Partition;
use App\Models\Statistic;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class FileManagerController extends Controller
{
    public function listFiles()
    {
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
    }

    public function getFile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'partition' => 'required',
            'file_name' => 'required',
        ]);

        if ($validator->fails()) {
            return response("Access denied", 403);
        }

        $data = $validator->validated();

        $partition = $data['partition'];
        $file_name = $data['file_name'];

        $path_name = "" . $partition . "/" . $file_name;

        $file = Storage::disk('local')->get($path_name);

        if ($file) {
            try {
                if (str_contains($file_name, "project.json")) {
                    $pattern = '/project-([^\/]+)/';

                    if (preg_match($pattern, $file_name, $matches)) {
                        $uid = $matches[1];
                        StatisticController::addStat($uid);
                    }
                }
            } catch (\Exception $ex) {
            }
            return response($file, 200);
        } else {
            return response("Not found", 404);
        }
    }

    public function getFileStreamed(Request $request)
    {
        $data = $request->validate([
            'partition' => 'required',
            'file_name' => 'required',
        ]);

        $partition = $data['partition'];
        $file_name = $data['file_name'];

        $path_name = "" . $partition . "/" . $file_name;

        $path = storage_path('app/' . $path_name);

        if (Storage::disk('local')->exists($path_name)) {
            return response()->streamDownload(function () use ($path) {
                $fd = fopen($path, 'rb');
                while (!feof($fd)) {
                    echo fread($fd, 2048);
                }
            }, $file_name);
        } else {
            return response("Not found", 404);
        }
    }

    public static function checkCode(string $partition_name, string $code)
    {
        $partition = Partition::where('name', $partition_name)->first();

        if ($partition->code == $code) {
            return true;
        }

        return false;
    }

    public function deleteFolder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'partition' => 'required',
            'path_name' => 'required',
            'code' => 'required',
        ]);

        if ($validator->fails()) {
            return response("Access denied", 403);
        }

        $data = $validator->validated();

        $partition = $data['partition'];
        $path_name = $data['path_name'];
        $code = $data['code'];

        if (!$this->checkCode($partition, $code)) {
            return response("Access denied", 403);
        };

        $path = "" . $partition . "/" . $path_name;

        if (Storage::disk('local')->exists($path)) {
            Storage::disk('local')->deleteDirectory($path);
            return response("Directory " . $path . " deleted", 200);
        } else {
            return response("Not found", 404);
        }
    }

    public function uploadFile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'partition' => 'required',
            'file_name' => 'required',
            'code' => 'required',
        ]);

        if ($validator->fails()) {
            return response("Access denied", 403);
        }

        $data = $validator->validated();

        $partition = $data['partition'];
        $file_name = $data['file_name'];
        $code = $data['code'];

        if (!$this->checkCode($partition, $code)) {
            return response("Access denied", 403);
        }

        $file = $request->file('file');

        Storage::disk('local')->putFileAs($partition, $file, $file_name);

        return response('File uploaded', 200);
    }
}
