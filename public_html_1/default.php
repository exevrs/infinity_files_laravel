<?php
ini_set('display_errors', 1);
$requestUri = $_SERVER['REQUEST_URI'];
$uriParts = parse_url($requestUri);
$path = $uriParts['path'];

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_SERVER['REQUEST_URI'] === '/ping') {
    header('Content-Type: application/json');
    $response = array('message' => 'pong');
    $jsonResponse = json_encode($response);
    echo $jsonResponse;
}elseif($_SERVER['REQUEST_METHOD'] === 'GET' && $path === '/api/files/find-all-folders-with-extension') {
    $extension = $_GET['extension'];
    header('Content-Type: application/json');
    echo findFoldersWithExtension($extension);
}elseif($_SERVER['REQUEST_METHOD'] === 'GET' && $path === '/api/files/list-files') {
    header('Content-Type: application/json');
    echo listFiles();
}elseif($_SERVER['REQUEST_METHOD'] === 'POST' && $path === '/api/files/upload') {
    header('Content-Type: application/json');
    echo pushfile();
}else {
    // Handle other routes or methods here
    header('HTTP/1.1 404 Not Found');
    echo '404 Not Found';
}

function findFoldersWithExtension($extension) {
    $serverPath = $_SERVER['DOCUMENT_ROOT'];
    $baseDirectory = $serverPath . '/static';
    $folders = array();
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($baseDirectory, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === $extension) {
            $folder = $file->getPath();
            $folder = str_replace($baseDirectory, '', $folder);
            $folder = str_replace('\/', '/', $folder);
            $folders[$folder] = true;
        }
    }
    return $jsonResponse = json_encode(array_keys($folders),JSON_UNESCAPED_SLASHES);
}
function listFiles(){
    $serverPath = $_SERVER['DOCUMENT_ROOT'];
    $baseDirectory = $serverPath . '/static';
    $files = array();
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($baseDirectory, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $filePath = $file->getPathname();
            $fileInfo = [
                'name' => json_encode(str_replace($baseDirectory, '', $filePath),JSON_UNESCAPED_SLASHES),
                'modified' => $file->getMTime()
            ];
            $files[] = $fileInfo;
        }
    }
    // return json_encode($files);
    return $jsonResponse = json_encode($files,JSON_UNESCAPED_SLASHES);
}
function pushfile() {
    $serverPath = $_SERVER['DOCUMENT_ROOT'];
    $code = $_POST['code'];
    $partition = $_POST['partition'];
    $destinationFile = $_POST['destination_file'];

    if ($code == 0) {
        $baseDirectory = $serverPath . '/static';
        $targetFolder = $baseDirectory . '/' . $partition;
        if (!is_dir($targetFolder)) {
            mkdir($targetFolder, 0777, true);
        }
        $uploadedFile = $_FILES['file'];
        $targetFilePath = $targetFolder . '/' . $destinationFile;

        $sourceFileHandle = fopen($uploadedFile['tmp_name'], 'rb');
        $targetFileHandle = fopen($targetFilePath, 'wb');

        if (stream_copy_to_stream($sourceFileHandle, $targetFileHandle)) {
            fclose($sourceFileHandle);
            fclose($targetFileHandle);
            $response = [
                'status' => 'success',
                'message' => 'File uploaded successfully.'
            ];
        } else {
            fclose($sourceFileHandle);
            fclose($targetFileHandle);
            $response = [
                'status' => 'error',
                'message' => 'File upload failed.'
            ];
        }
        return json_encode($response,JSON_UNESCAPED_SLASHES);
        // return $response;
    }
}
?> 