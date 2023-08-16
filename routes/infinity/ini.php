<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


$bake_projects_cache = function (Request $request) {

    $code = $request->query("code");

    if (getenv("INFINITY_FILES_API_KEY") == $code) {

        $partition = $request->query("partition");

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
                        "title" => $jsonData["title"],
                        "name" => $jsonData["name"],
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

        return response('ok', 200);
    } else {
        return response('Access denied', 403);
    }

};

//Posts
Route::put('/infinity/projects/make-cache', $bake_projects_cache);