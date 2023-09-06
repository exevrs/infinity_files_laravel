<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    public function bakeCache(Request $request){
        $validator = Validator::make($request->all(), [
            'partition' => 'required',
            'code' => 'required',
        ]);

        if ($validator->fails()) {
            return response("Access denied", 403);
        }

        $data = $validator->validated();

        $partition = $data['partition'];        
        $code = $data['code'];

        if(!FileManagerController::checkCode($partition, $code)){
            return response("Access denied", 403);
        };

        $disc = Storage::disk('local');

        $results = [];

        foreach ($disc->directories($partition) as $directory) {

            $projectsJsonPath = ((string) $directory) . "/project.json";

            if ($disc->exists($projectsJsonPath)) {

                $jsonString = $disc->get($projectsJsonPath);

                $jsonData = json_decode($jsonString, true);

                if (json_last_error() !== null) {
                    array_push($results, [
                        "id" => $jsonData["id"],
                        "uid" => $jsonData["uid"],
                        "name" => $jsonData["name"],
                        "group" => isset($jsonData["group"]) ? $jsonData["group"] : "",
                        "version" => $jsonData["version"],
                        "updated_at" => $jsonData["updated_at"],
                        "uses_runtime_target" => $jsonData["uses_runtime_target"],
                    ]);
                } else {
                    Log::error("Something went wrong while json decoding");
                    return response('Something went wrong', 500);
                }
            }
        }

        Log::info("Making cache for " . $partition);

        $disc->put($partition . "/cache/projects.json", json_encode([
            "projects" => $results
        ]));

        return response('Cache baked', 200);
    }
}
