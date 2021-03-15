<?php

/**
 * @Author : Arslan Hassan
 */
include('../includes/config.inc.php');
exit(json_encode(array("Alert"=>"This is deprectaed ! ")));
$request = $_REQUEST;

$file_name = $request['file_name'];
$file_directory = $request['file_directory'];
if (!$file_directory) {
    $file_directory = createDataFolders();
}
$video_id = $request['videoid'];

$tempFile = $_FILES['Filedata']['tmp_name'];
$targetFileName = $file_name . '.' . getExt($_FILES['Filedata']['name']);
$targetFile = TEMP_DIR . "/" . $targetFileName;

$max_file_size_in_bytes = config('max_upload_size') * 1024 * 1024;
$types = strtolower(config('allowed_types'));

//Checking filesize
$POST_MAX_SIZE = ini_get('post_max_size');
$unit = strtoupper(substr($POST_MAX_SIZE, -1));
$multiplier = ($unit == 'M' ? 1048576 : ($unit == 'K' ? 1024 : ($unit == 'G' ? 1073741824 : 1)));

if ((int) $_SERVER['CONTENT_LENGTH'] > $multiplier * (int) $POST_MAX_SIZE && $POST_MAX_SIZE)
{
    header("HTTP/1.1 500 Internal Server Error"); // This will trigger an uploadError event in SWFUpload
    upload_error("POST exceeded maximum allowed size.");
    exit(0);
}

//Checking uploading errors
$uploadErrors = array(
    0 => "There is no error, the file uploaded with success",
    1 => "The uploaded file exceeds the upload_max_filesize directive in php.ini",
    2 => "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form",
    3 => "The uploaded file was only partially uploaded",
    4 => "No file was uploaded",
    6 => "Missing a temporary folder"
);
if (!isset($_FILES['Filedata']))
{
    upload_error("No file was selected");
    exit(0);
}
else if (isset($_FILES['Filedata']["error"]) && $_FILES['Filedata']["error"] != 0)
{
    upload_error($uploadErrors[$_FILES['Filedata']["error"]]);
    exit(0);
}
else if (!isset($_FILES['Filedata']["tmp_name"]) || !@is_uploaded_file($_FILES['Filedata']["tmp_name"]))
{
    upload_error("Upload failed is_uploaded_file test.");
    exit(0);
}
else if (!isset($_FILES['Filedata']['name']))
{
    upload_error("File has no name.");
    exit(0);
}

//Check file size
$file_size = @filesize($_FILES['Filedata']["tmp_name"]);
if (!$file_size || $file_size > $max_file_size_in_bytes)
{
    upload_error("File exceeds the maximum allowed size");
    exit(0);
}


//Checking file type
$types_array = preg_replace('/,/', ' ', $types);
$types_array = explode(' ', $types_array);
$file_ext = strtolower(getExt($_FILES['Filedata']['name']));
if (!in_array($file_ext, $types_array))
{
    upload_error("Invalid file extension");
    exit(0);
}


move_uploaded_file($tempFile, $targetFile);
#exit($file_directory);
$Upload->add_conversion_queue($targetFileName, $file_directory);
            
//exec(php_path()." -q ".BASEDIR."/actions/video_convert.php &> /dev/null &");
if (stristr(PHP_OS, 'WIN'))
{
    exec(php_path() . " -q " . BASEDIR . "/actions/video_convert.php $targetFileName");
}
else
{
    exec(php_path()." -q ".BASEDIR."/actions/video_convert.php {$targetFileName} {$file_name} {$file_directory} {$logFile} > /dev/null &");
}

$status_array['success'] = 'yes';
$status_array['file_name'] = $file_name;

echo json_encode($status_array);

//function used to display error
function upload_error($error)
{
    echo json_encode(array("error" => $error));
}

?>