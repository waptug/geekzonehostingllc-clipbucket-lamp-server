<?php
include('../includes/config.inc.php');
require_once(dirname(dirname(__FILE__)).'/includes/classes/sLog.php');
global $Cbucket,$cbvid,$Upload,$db,$eh;

if($_FILES['Filedata']){
    $mode = 'upload';
}
if($_POST['insertVideo']){
    $mode = 'insert_video';
}
if($_POST['getForm']){
    $mode = 'get_form';
}
if($_POST['updateVideo']=='yes'){
    $mode = 'update_video';
}

switch($mode)
{
    case 'insert_video':
        $title = getName($_POST['title']);
        if ($_POST['serverUrl'] && $_POST['serverUrl'] != "none") {
            $file_directory = date('Y/m/d');
        } else {
            $file_directory = createDataFolders();
        }

        $vidDetails = array(
            'title' => $title,
            'description' => $title,
            'tags' => genTags(str_replace(' ',', ',$title)),
            'category' => array($cbvid->get_default_cid()),
            'file_name' => $_POST['file_name'],
            'file_directory' => $file_directory,
            'userid' => userid(),
            'video_version' => '2.7'
        );

        $vid = $Upload->submit_upload($vidDetails);
        if(error()) {
            echo json_encode(array('error' => error('single')));
            exit();
        }

        // inserting into video views as well
        $query = 'INSERT INTO '.tbl('video_views').' (video_id, video_views, last_updated) VALUES('.$vid.',0,'.time().')';
        $db->Execute($query);

        if(error()) {
            echo json_encode(array('error' => error('single')));
        } else {
            echo json_encode(array('videoid' => $vid));
        }
        exit();

    case "get_form":
        $title 	= getName($_POST['title']);
        if(!$title){
            $title = $_POST['title'];
        }
        $desc = $_POST['desc'];
        $tags = $_POST['tags'];

        if(!$desc){
            $desc = $title;
        }
        if(!$tags){
            $tags = $title;
        }

        $vidDetails = array(
            'title'			=> $title,
            'description' 	=> $desc,
            'tags'			=> $tags,
            'category'		=> array($cbvid->get_default_cid())
        );

        assign("objId",$_POST['objId']);
        assign('input',$vidDetails);

        $vid = $_POST['vid'];
        assign('videoid',$vid);

        $videoFields = $Upload->load_video_fields($vidDetails);
        Template('blocks/upload/upload_form.html');
        break;

    case "upload":
        $config_for_mp4 = $Cbucket->configs['stay_mp4'];
        $ffmpegpath = $Cbucket->configs['ffmpegpath'];
        $extension = getExt( $_FILES['Filedata']['name']);

        #checking for if the right file is uploaded
        $content_type = get_mime_type($_FILES['Filedata']['tmp_name']);
        if ( $content_type != 'video')  {
            echo json_encode(array("status"=>"400","err"=>"Invalid Content"));
            exit();
        }

        $types = strtolower(config('allowed_video_types'));
        $supported_extensions = explode(',', $types);

        if (!in_array($extension, $supported_extensions)) {
            echo json_encode(array("status"=>"504","msg"=>"Invalid video extension"));
            exit();
        }

        $file_name	= time().RandomString(5);

        //Stay as it MP4 Module ..
        if($config_for_mp4 == "yes" && $extension == "mp4" ) {
            $tempFile = $_FILES['Filedata']['tmp_name'];
            $file_directory = date('Y/m/d');
            @mkdir(VIDEOS_DIR . '/' . $file_directory, 0777, true);
            $targetFileName = $file_name.'.'.getExt( $_FILES['Filedata']['name']);
            $targetFile = TEMP_DIR."/".$targetFileName;
            $ta = VIDEOS_DIR.'/'.$file_directory;
            logData(VIDEOS_DIR.'/'.$file_directory.$file_name,'ta');
            $orginal_file = VIDEOS_DIR.'/'.$file_directory.'/'.$file_name.'.'.getExt($_FILES['Filedata']['name']);

            move_uploaded_file($tempFile,VIDEOS_DIR.'/'.$file_directory.'/'.$file_name.'.'.getExt( $_FILES['Filedata']['name']));
            echo json_encode(array("success"=>"yes","file_name"=>$file_name, 'phpos' => PHP_OS , "extension"=>$extension));
            exit();
        }

        $tempFile = $_FILES['Filedata']['tmp_name'];
        $file_directory = date('Y/m/d');
        $targetFileName = $file_name.'.'.getExt( $_FILES['Filedata']['name']);
        $targetFile = TEMP_DIR."/".$targetFileName;
        createDataFolders(LOGS_DIR);
        $logFile = LOGS_DIR.'/'.$file_directory.'/'.$file_name.".log";

        $log = new SLog($logFile);
        $log->newSection("Pre-Check Configurations");
        $log->writeLine("File to be converted", 'Initializing File <strong>'.$file_name.'.mp4</strong> and pre checking configurations...', true);

        if( DEVELOPMENT_MODE ) {
            $hardware = shell_exec('lshw -short');
            if ($hardware){
                $log->writeLine("System hardware Information", $hardware, true);
            } else {
                $log->writeLine('System hardware Information', 'Unable log System hardware information, please install "lshw" ', true);
            }
        }

        logData('Checking Server configurations to start for filename : '.$file_name.'','checkpoints');

        $max_file_size_in_bytes = config('max_upload_size')*1024*1024;

        //Checking filesize
        $POST_MAX_SIZE = ini_get('post_max_size');
        $unit = strtoupper(substr($POST_MAX_SIZE, -1));
        $multiplier = ($unit == 'M' ? 1048576 : ($unit == 'K' ? 1024 : ($unit == 'G' ? 1073741824 : 1)));

        if ((int)$_SERVER['CONTENT_LENGTH'] > $multiplier*(int)$POST_MAX_SIZE && $POST_MAX_SIZE) {
            header("HTTP/1.1 500 Internal Server Error"); // This will trigger an uploadError event in SWFUpload
            upload_error("POST exceeded maximum allowed size.");
            exit(0);
        }

        //Checking uploading errors
        $uploadErrors = array(
            0=>"There is no error, the file uploaded with success",
            1=>"The uploaded file exceeds the upload_max_filesize directive in php.ini",
            2=>"The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form",
            3=>"The uploaded file was only partially uploaded",
            4=>"No file was uploaded",
            6=>"Missing a temporary folder",
            7=>"Failed to write file to disk",
            8=>"A PHP extension stopped the file upload. PHP does not provide a way to ascertain which extension caused the file upload to stop; examining the list of loaded extensions with phpinfo() may help"
        );
        if (!isset($_FILES['Filedata'])) {
            upload_error("No file was selected");
            exit(0);
        }
        if (isset($_FILES['Filedata']["error"]) && $_FILES['Filedata']["error"] != 0) {
            upload_error($uploadErrors[$_FILES['Filedata']["error"]]);
            exit(0);
        }
        if (!isset($_FILES['Filedata']["tmp_name"]) || !@is_uploaded_file($_FILES['Filedata']["tmp_name"])) {
            upload_error("Upload failed is_uploaded_file test.");
            exit(0);
        }
        if (!isset($_FILES['Filedata']['name'])) {
            upload_error("File has no name.");
            exit(0);
        }

        //Check file size
        $file_size = @filesize($_FILES['Filedata']["tmp_name"]);
        if (!$file_size || $file_size > $max_file_size_in_bytes) {
            upload_error("File exceeds the maximum allowed size") ;
            exit(0);
        }

        //Checking file type
        $types_array = preg_replace('/,/',' ',$types);
        $types_array = explode(' ',$types_array);
        $file_ext = strtolower(getExt($_FILES['Filedata']['name']));
        if(!in_array($file_ext,$types_array)) {
            upload_error("Invalid file extension");
            exit(0);
        }

        $moved = move_uploaded_file($tempFile,$targetFile);

        if ($moved){
            $log->writeLine('Temporary Uploading', 'File Uploaded to Temp directory successfully and video conversion file is being executed !', true);
        } else {
            $log->writeLine('Temporary Uploading', 'Went something wrong in moving the file in Temp directory!', true);
        }

        $Upload->add_conversion_queue($targetFileName);
        $quick_conv = config('quick_conv');
        $use_crons = config('use_crons');

        if($quick_conv=='yes' || $use_crons=='no')
        {
            if (stristr(PHP_OS, 'WIN')) {
                exec(php_path()." -q ".BASEDIR."/actions/video_convert.php $targetFileName");
            } elseif(stristr(PHP_OS, 'darwin')) {
                exec(php_path()." -q ".BASEDIR."/actions/video_convert.php $targetFileName </dev/null >/dev/null &");
            } else { // for ubuntu or linux
                exec(php_path()." -q ".BASEDIR."/actions/video_convert.php {$targetFileName} {$file_name} {$file_directory} {$logFile} > /dev/null &");
            }
        }

        $TempLogData = 'Video Converson File executed successfully with Target File > !'.$targetFileName;
        $log->writeLine('Video Conversion File Execution', $TempLogData, true);

        echo json_encode(array("success"=>"yes","file_name"=>$file_name, 'phpos' => PHP_OS));
        exit();

    case "update_video":
        $config_for_mp4 = $Cbucket->configs['stay_mp4'];
        $Upload->validate_video_upload_form();
        $data = get_video_details($_POST['videoid']);
        logData($_FILES,"MyFileMP4");
        $vid_file = VIDEOS_DIR.'/'.$data['file_directory'].'/'.get_video_file($data,false,false);

        if($config_for_mp4 == 'yes')
        {
            if($data['files_thumbs_path']!='')
            {
                $files_thumbs_path = $data['files_thumbs_path'];
                $serverApi = str_replace('/files/thumbs', '', $files_thumbs_path);
                $serverApi = $serverApi.'/actions/custom_thumb_upload.php';

                $file_thumb = $_FILES['vid_thumb']['tmp_name'][0];
                $postvars['mode'] = 'add';
                $postvars['file_thumb'] = "@".$file_thumb;
                $postvars['files_thumbs_path'] = $files_thumbs_path;
                $postvars['file_directory'] = $data['file_directory'];
                $postvars['file_name'] = $data['file_name'];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $serverApi);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postvars);
                /* Tell cURL to return the output */
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                 /* Tell cURL NOT to return the headers */
                curl_setopt($ch, CURLOPT_HEADER, false);
                $response = curl_exec($ch);
                /* Check HTTP Code */
                $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if(!$response){
                    e(lang($response),'w');
                } else if((int)($response)) {
                    e(lang(' remote upload successfully'),'m');
                    $query = "UPDATE " . tbl("video") . " SET file_thumbs_count = ".(int)($response)." WHERE videoid = ".$data['videoid'];
                    $db->Execute($query);
                    $data['file_thumbs_count'] = (int)($response);
                } else {
                    e(lang($response),'e');
                }
            }
        }

        $_POST['videoid'] = trim($_POST['videoid']);
        $_POST['title'] = mysql_clean($_POST['title']);
        $_POST['description'] = mysql_clean($_POST['description']);
        $_POST['duration'] = mysql_clean($_POST['duration']);

        if(empty($eh->get_error())) {
            $cbvid->update_video();
        }
        if(error()){
            echo json_encode(array('error'=>error('single')));
        } else {
            echo json_encode(array('msg'=>msg('single')));
        }
        exit();
}

//function used to display error
function upload_error($error)
{
    echo json_encode(array("error"=>$error));
}
